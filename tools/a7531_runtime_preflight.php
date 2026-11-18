<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$checks = [];
$test = static function (string $name, bool $pass) use (&$checks): void {
    $checks[] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$trail = app(\App\Services\WorkspaceAuditTrail::class);
$test('Workspace audit service resolves', $trail instanceof \App\Services\WorkspaceAuditTrail);
foreach (['delivery', 'installation', 'invoice'] as $subject) {
    $test("Audit event: {$subject}.status_changed", in_array($subject . '.status_changed', \App\Services\WorkspaceAuditTrail::EVENTS, true));
    $test("Audit subject: {$subject}", in_array($subject, \App\Services\WorkspaceAuditTrail::SUBJECTS, true));
}
$test('Workspace audit ledger exists', \Illuminate\Support\Facades\Schema::hasTable('workspace_audit_events'));
$test('Delivery table exists', \Illuminate\Support\Facades\Schema::hasTable('deliveries'));
$test('Installation table exists', \Illuminate\Support\Facades\Schema::hasTable('installations'));
$test('Invoice table exists', \Illuminate\Support\Facades\Schema::hasTable('invoices'));
$test('CRM observer class available', class_exists(\App\Observers\CrmCrossModuleStatusAuditObserver::class));

$passed = count(array_filter($checks));
echo "\nA7.5.3.1 runtime: {$passed}/" . count($checks) . ' PASS. Read-only preflight.' . PHP_EOL;
exit($passed === count($checks) ? 0 : 1);
