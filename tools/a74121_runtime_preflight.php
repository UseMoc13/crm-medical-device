<?php
/** Read-only route and schema verification, no writes to production data. */
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass = 0; $fail = 0;
$check = function (string $label, bool $ok) use (&$pass, &$fail): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n";
    $ok ? $pass++ : $fail++;
};
$check('Audit table exists', Illuminate\Support\Facades\Schema::hasTable('workspace_audit_events'));
foreach (['audit_event_id','actor_user_id','event_type','subject_type','subject_id','details','created_at'] as $column) {
    $check('Audit column ' . $column, Illuminate\Support\Facades\Schema::hasColumn('workspace_audit_events', $column));
}
$check('Explicit audit permission seeded', Illuminate\Support\Facades\DB::table('permissions')->where('permission_name','workspace_audit.view')->exists());
$route = Illuminate\Support\Facades\Route::getRoutes()->getByName('workspace-audit.index');
$check('Audit index route GET', $route !== null && in_array('GET', $route->methods(), true));
$check('Audit index has no POST', $route !== null && !in_array('POST', $route->methods(), true));
$check('Audit table readable', Illuminate\Support\Facades\DB::table('workspace_audit_events')->limit(1)->count() >= 0);
echo "\nA7.4.12.1 runtime: {$pass}/" . ($pass+$fail) . " PASS; {$fail} FAIL. Read-only.\n";
exit($fail ? 1 : 0);
