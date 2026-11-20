<?php
/**
 * A7.7.4.3 — local-only PostgreSQL read-only count-based inspection.
 * No filenames, URLs, UUIDs, or customer data are shown.
 * No data/schema/filesystem mutations. Requires Laravel project root.
 */
declare(strict_types=1);

use App\Support\CrmAnnouncementExposurePolicy;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$root = dirname(__DIR__);
if (PHP_SAPI !== 'cli' || count($argv) !== 1 || !is_file($root.'/artisan') ||
    !is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "[STOP] Run as: php tools/a7743_announcement_exposure.php (from Laravel root).\n");
    exit(2);
}

require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] For safety, set APP_ENV=local in your authorized local environment.\n");
    exit(2);
}
if (DB::connection()->getDriverName() !== 'pgsql') {
    fwrite(STDERR, "[STOP] This inspection requires your local PostgreSQL CRM database.\n");
    exit(2);
}

$required = [
    'announcements' => ['announcement_id','status','published_at','expires_at','thumbnail_path'],
    'announcement_attachments' => ['announcement_id','file_path'],
    'announcement_media' => ['announcement_id','file_path'],
];
foreach ($required as $table => $columns) {
    if (!Schema::hasTable($table)) {
        fwrite(STDERR, "[STOP] Required table is not present: {$table}. No changes made.\n");
        exit(2);
    }
    foreach ($columns as $column) {
        if (!Schema::hasColumn($table, $column)) {
            fwrite(STDERR, "[STOP] Required schema column is missing: {$table}.{$column}.\n");
            exit(2);
        }
    }
}

$now = now();
$notVisible = static function (Builder $q) use ($now): void {
    $q->whereNull('a.status')
        ->orWhere('a.status', '!=', 'published')
        ->orWhereNull('a.published_at')
        ->orWhere('a.published_at', '>', $now)
        ->orWhere(static fn (Builder $expired) => $expired
            ->whereNotNull('a.expires_at')
            ->where('a.expires_at', '<=', $now));
};

$measure = static function (Builder $query) use ($notVisible): array {
    $total = (int) (clone $query)->count();
    $hidden = (int) (clone $query)->where($notVisible)->count();
    return ['referenced' => $total, 'not_visible' => $hidden];
};

DB::beginTransaction();
try {
    // PostgreSQL guarantees that every query below is read-only.
    DB::statement('SET TRANSACTION READ ONLY');
    $thumbnail = $measure(DB::table('announcements as a')
        ->whereNotNull('a.thumbnail_path')->where('a.thumbnail_path', '!=', ''));
    $media = $measure(DB::table('announcement_media as asset')
        ->join('announcements as a', 'a.announcement_id','=','asset.announcement_id')
        ->whereNotNull('asset.file_path')->where('asset.file_path', '!=',''));
    $attachments = $measure(DB::table('announcement_attachments as asset')
        ->join('announcements as a', 'a.announcement_id','=','asset.announcement_id')
        ->whereNotNull('asset.file_path')->where('asset.file_path', '!=',''));
    DB::rollBack();
} catch (\Throwable $e) {
    DB::rollBack();
    fwrite(STDERR, '[STOP] Read-only inspection could not finish: '.class_basename($e).".\n");
    exit(1);
}

$total = $thumbnail['referenced'] + $media['referenced'] + $attachments['referenced'];
$hidden = $thumbnail['not_visible'] + $media['not_visible'] + $attachments['not_visible'];
// This detects a local web-accessible path, but cannot prove how the web server serves it.
$publicLink = is_dir(public_path('storage'));
$decision = CrmAnnouncementExposurePolicy::decide($total, $hidden, $publicLink);

echo "A7.7.4.3 Announcement Public Attachment Exposure [LOCAL, READ-ONLY]\n";
foreach (['thumbnail'=>$thumbnail, 'media'=>$media, 'attachments'=>$attachments] as $name=>$counts) {
    echo sprintf("[%s] public_disk_references=%d | not_currently_visible=%d\n",
        strtoupper($name), $counts['referenced'], $counts['not_visible']);
}
echo 'public_storage_path_detected='.($publicLink?'YES':'NO')." (NOT proof of external HTTP reachability)\n";
echo "decision={$decision}\n";
echo "[IMPORTANT] Assets stored on the public disk may be served directly, independent of the announcement page's publication check.\n";
echo "[SAFE] No filenames, URLs, IDs or personal records printed; PostgreSQL READ ONLY; no file access or writes.\n";
