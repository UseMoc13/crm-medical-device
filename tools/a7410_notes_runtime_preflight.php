<?php
/** A7.4.10 read-only DB/route inspection. Never modifies CRM records. */
declare(strict_types=1);
$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "[FAIL] Run from an installed Laravel CRM application.\n");
    exit(1);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

$count = 0; $failed = 0;
$check = function (string $name, bool $pass) use (&$count, &$failed): void {
    $count++;
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    if (!$pass) $failed++;
};
try {
    $check('Personal notes table', Schema::hasTable('workspace_personal_notes'));
    foreach (['note_id','owner_user_id','title','body','is_pinned','archived_at','remind_at','task_id','calendar_event_id','activity_id'] as $col) {
        $check('Personal notes column '.$col, Schema::hasColumn('workspace_personal_notes', $col));
    }
    $check('Activities table', Schema::hasTable('activities'));
    $check('Personal calendar table', Schema::hasTable('workspace_calendar_events'));
} catch (Throwable $e) {
    $check('Database/schema connection', false);
}
foreach ([
    'notes.index'=>'GET', 'notes.create'=>'GET', 'notes.store'=>'POST',
    'notes.show'=>'GET', 'notes.edit'=>'GET', 'notes.update'=>'PUT',
    'notes.destroy'=>'DELETE','notes.pin'=>'PATCH','notes.archive'=>'PATCH','notes.restore'=>'PATCH',
    'calendar.index'=>'GET', 'activities.show'=>'GET',
] as $name=>$method) {
    $route = Route::getRoutes()->getByName($name);
    $check("Route {$name} [{$method}]", $route !== null && in_array($method, $route->methods(), true));
}
echo "\nA7.4.10 runtime: ".($count-$failed)."/{$count} PASS, {$failed} FAIL.\nRead-only: browser access and HTTP policy tests are separate.\n";
exit($failed ? 1 : 0);
