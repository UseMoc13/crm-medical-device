<?php
$root = dirname(__DIR__);
$pass = 0;
$fail = 0;
function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n";
    $ok ? $pass++ : $fail++;
}
$files = [
    'app/Models/WorkspaceAuditEvent.php',
    'app/Services/WorkspaceAuditAccess.php',
    'app/Services/WorkspaceAuditTrail.php',
    'app/Observers/WorkspaceAuditObserver.php',
    'app/Http/Controllers/WorkspaceAuditController.php',
    'database/migrations/2026_11_18_010000_create_workspace_audit_events.php',
    'routes/workspace_audit.php',
    'resources/views/workspace-audit/index.blade.php',
    'resources/views/workspace-audit/_style.blade.php',
    'tests/Feature/WorkspaceAuditHttpTest.php',
];
foreach ($files as $f) check('File ' . $f, is_file($root . '/' . $f));
$read = fn ($f) => is_file($root . '/' . $f) ? (string)file_get_contents($root . '/' . $f) : '';
$p = $read('app/Providers/AppServiceProvider.php');
$s = $read('resources/views/components/sidebar.blade.php');
$r = $read('routes/web.php');
$a = $read('app/Services/WorkspaceAuditAccess.php');
$t = $read('app/Services/WorkspaceAuditTrail.php');
$o = $read('app/Observers/WorkspaceAuditObserver.php');
$c = $read('app/Http/Controllers/WorkspaceAuditController.php');
$w = $read('resources/views/workspace-audit/index.blade.php');
check('Audit route included exactly once', substr_count($r, "require __DIR__ . '/workspace_audit.php';") === 1);
check('Audit sidebar link once', substr_count($s, "route('workspace-audit.index')") === 1);
check('Sidebar not visible without authorized role', str_contains($s, "WorkspaceAuditAccess::class)->canView(auth("));
check('Audit observers registered five times', substr_count($p,'WorkspaceAuditObserver::class') === 5);
check('Legacy notifications observer preserved', str_contains($p,'WorkspaceTaskReviewNotificationObserver::class'));
check('Super Admin uses centralized resolver', str_contains($a,'CrmPermissionResolver::class') && str_contains($a,'isSuperAdmin'));
check('Explicit permission required', str_contains($a,'workspace_audit.view'));
check('View authorization enforced on server', str_contains($c,'assertView($request->user'));
check('No write API routes', !str_contains($read('routes/workspace_audit.php'), 'Route::post') && !str_contains($read('routes/workspace_audit.php'), 'Route::delete'));
check('Audit events allowlisted', str_contains($t,'public const EVENTS') && str_contains($t,'private const FIELDS'));
check('Sensitive free text not logged', !str_contains($o,"['note']") && !str_contains($o,"['body']") && !str_contains($o,"['password']"));
check('Details escaped in Blade', str_contains($w,'{{ json_encode('));
check('Transactions preserve rollback semantics', !str_contains($o,'afterCommit') && str_contains($o,'WorkspaceAuditTrail::class'));
check('One audit module stylesheet', substr_count($w,"@include('workspace-audit._style')") === 1);
check('Audit migration separate from legacy audit_logs', str_contains($read('database/migrations/2026_11_18_010000_create_workspace_audit_events.php'), "Schema::create('workspace_audit_events'"));
echo "\nA7.4.12.1 static: {$pass}/" . ($pass+$fail) . " PASS; {$fail} FAIL.\n";
exit($fail ? 1 : 0);
