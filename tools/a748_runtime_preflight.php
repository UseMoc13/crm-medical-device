<?php
/**
 * A7.4.8.1 read-only runtime preflight for the installed Laravel CRM.
 * Never alters data, never creates sample records, never calls migrate.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php') || !is_file($root.'/bootstrap/app.php')) {
    fwrite(STDERR, "[FAIL] Run this script in a complete CRM Laravel installation.\n");
    exit(1);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

$checks = 0;
$failures = 0;
$warnings = 0;
$check = function (string $name, bool $ok, bool $strict = true) use (&$checks, &$failures, &$warnings): void {
    $checks++;
    echo ($ok ? '[PASS] ' : ($strict ? '[FAIL] ' : '[WARN] ')).$name.PHP_EOL;
    if (!$ok) { if ($strict) $failures++; else $warnings++; }
};

foreach ([
    'workspace_collaboration_members' => ['member_id','activity_id','task_id','user_id','access_role'],
    'workspace_collaboration_entries' => ['entry_id','activity_id','task_id','author_user_id','entry_type','body'],
    'workspace_tasks' => ['task_id','owner_user_id','visibility'],
    'workspace_task_assignments' => ['assignment_id','task_id','assigned_user_id'],
    'workspace_task_submission_files' => ['file_id','assignment_id','stored_path'],
    'activities' => ['activity_id','user_id'],
] as $table => $cols) {
    try {
        $exists = Schema::hasTable($table);
        $check('Database table '.$table, $exists);
        if ($exists) foreach ($cols as $col) $check("Column {$table}.{$col}", Schema::hasColumn($table, $col));
    } catch (Throwable $e) {
        $check('Database '.$table.' is reachable', false);
        // Avoid printing credentials or DSNs from database exceptions.
    }
}

$expected = [
    'activities.shared' => 'GET',
    'activities.show' => 'GET',
    'activities.collaboration.manage' => 'GET',
    'activities.collaboration.add' => 'POST',
    'activities.collaboration.remove' => 'DELETE',
    'activities.collaboration.entry' => 'POST',
    'todos.shared' => 'GET',
    'todos.show' => 'GET',
    'todos.collaboration.manage' => 'GET',
    'todos.collaboration.add' => 'POST',
    'todos.collaboration.remove' => 'DELETE',
    'todos.collaboration.entry' => 'POST',
    'todos.submission.files.download' => 'GET',
    'todos.submission.complete' => 'PATCH',
];
foreach ($expected as $name => $method) {
    $route = Route::getRoutes()->getByName($name);
    $check("Route {$name} [{$method}]", $route !== null && in_array($method, $route->methods(), true));
}

try {
    $affected = (int) DB::table('workspace_collaboration_members as m')
        ->join('workspace_tasks as t','t.task_id','=','m.task_id')
        ->where('t.visibility','personal')->count();
    $check('No active collaborators on Personal TO-DO (found '.$affected.')', $affected === 0);
} catch (Throwable $e) {
    $check('Personal TO-DO collaboration consistency query', false);
}

try {
    $invalid = (int) DB::table('workspace_collaboration_members')->where(function ($query) {
        $query->where(function ($q) { $q->whereNull('activity_id')->whereNull('task_id'); })
            ->orWhere(function ($q) { $q->whereNotNull('activity_id')->whereNotNull('task_id'); });
    })->count();
    $check('No malformed membership target pairs (found '.$invalid.')', $invalid === 0);
} catch (Throwable $e) {
    $check('Membership target consistency query', false);
}

try {
    $invalid = (int) DB::table('workspace_collaboration_members')
        ->whereNotIn('access_role', ['viewer','contributor'])->count();
    $check('Membership role allowlist valid (found '.$invalid.' invalid)', $invalid === 0);
} catch (Throwable $e) {
    $check('Membership role validation query', false);
}

$base = storage_path('app');
$check('Private Laravel storage directory configured', is_dir($base), false);

// Never print database names, hostnames, emails, UUIDs, or any stored record content.
echo "\nRuntime preflight: ".($checks-$failures-$warnings)."/{$checks} PASS; {$failures} FAIL; {$warnings} WARN.\n";
echo "Read-only checks; POST/PATCH/DELETE authorization and browser flows require separate tests.\n";
exit($failures ? 1 : 0);
