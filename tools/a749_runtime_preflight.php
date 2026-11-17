<?php
/** A7.4.9 database/route read-only check. Does not write or reset CRM records. */
declare(strict_types=1);
$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "Laravel vendor/autoload.php missing. Run in the project root.\n"); exit(1);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

$checks = 0; $fails = 0; $warns = 0;
$check = function (string $label, bool $ok, bool $warnOnly = false) use (&$checks,&$fails,&$warns) {
    $checks++; echo ($ok?'[PASS] ':($warnOnly?'[WARN] ':'[FAIL] ')).$label.PHP_EOL;
    if (!$ok) { if ($warnOnly) $warns++; else $fails++; }
};
try {
    $check('Review history table exists', Schema::hasTable('workspace_task_reviews'));
    foreach (['task_id','assignment_id','actor_user_id','decision','note','created_at'] as $column) {
        $check('History column '.$column, Schema::hasColumn('workspace_task_reviews', $column));
    }
    foreach (['review_status','reviewed_by_user_id','reviewed_at','review_note'] as $column) {
        $check('Assignment column '.$column, Schema::hasColumn('workspace_task_assignments', $column));
    }
    $r = Route::getRoutes()->getByName('todos.assignments.review');
    $check('POST route todos.assignments.review', $r !== null && in_array('POST', $r->methods(), true));
    if (Schema::hasTable('workspace_task_assignments') &&
        Schema::hasColumn('workspace_task_assignments','review_status')) {
        $bad = DB::table('workspace_task_assignments')
            ->whereNotIn('review_status', ['not_submitted','pending_review','approved','revision_requested','legacy_accepted'])->count();
        $check('Assignment review statuses valid (invalid '.$bad.')', $bad === 0);
        $bad = DB::table('workspace_task_assignments')
            ->where('review_status','approved')->where('status','!=','completed')->count();
        $check('Approved submissions are completed (invalid '.$bad.')', $bad === 0);
        $bad = DB::table('workspace_task_assignments')
            ->where('review_status','pending_review')->where('status','!=','completed')->count();
        $check('Pending review submissions are completed (invalid '.$bad.')', $bad === 0);
        $bad = DB::table('workspace_task_assignments')
            ->where('review_status','revision_requested')->where('status','!=','assigned')->count();
        $check('Revision requested returns assignment to assigned (invalid '.$bad.')', $bad === 0);
    }
} catch (Throwable $e) {
    $check('Database reachable and A7.4.9 migration complete', false);
    // Intentionally omit exception message: connection info can contain secrets.
}
echo "\nA7.4.9 runtime: ".($checks-$fails-$warns)."/{$checks} PASS; {$fails} FAIL; {$warns} WARN.\n";
echo "Read-only. Use HTTP tests to verify authorization and transitions.\n";
exit($fails ? 1 : 0);
