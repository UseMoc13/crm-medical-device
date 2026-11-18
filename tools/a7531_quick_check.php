<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$test = static function (string $name, bool $pass) use (&$checks): void {
    $checks[] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$read = static fn (string $name): string => (string) @file_get_contents($root . '/' . $name);
$trail = $read('app/Services/WorkspaceAuditTrail.php');
$provider = $read('app/Providers/AppServiceProvider.php');
$invoice = $read('app/Http/Controllers/InvoiceController.php');
$observer = $read('app/Observers/CrmCrossModuleStatusAuditObserver.php');
$start = strpos($invoice, 'public function updateStatus(');
$end = $start === false ? false : strpos($invoice, 'public function destroy(', $start);
$method = ($start !== false && $end !== false) ? substr($invoice, $start, $end - $start) : '';

foreach ([
    'app/Services/WorkspaceAuditTrail.php',
    'app/Providers/AppServiceProvider.php',
    'app/Http/Controllers/InvoiceController.php',
    'app/Observers/CrmCrossModuleStatusAuditObserver.php',
    'tests/Unit/CrmCrossModuleStatusAuditSourceTest.php',
    'tests/Feature/CrmCrossModuleStatusAuditTransactionTest.php',
] as $file) $test('File: ' . $file, is_file($root . '/' . $file));

$test('Delivery event allowed once', substr_count($trail, "'delivery.status_changed'") === 1);
$test('Installation event allowed once', substr_count($trail, "'installation.status_changed'") === 1);
$test('Invoice event allowed once', substr_count($trail, "'invoice.status_changed'") === 1);
$test('Three CRM audit subjects allowed', str_contains($trail, "'role', 'delivery', 'installation', 'invoice'"));
$test('Status from/to metadata already allowlisted', str_contains($trail, "'status_from'") && str_contains($trail, "'status_to'"));
$test('Two observers registered once', substr_count($provider, 'CrmCrossModuleStatusAuditObserver::class') === 2);
$test('Observer status-only changes', str_contains($observer, "wasChanged('status')") && !str_contains($observer, "'notes' =>"));
$test('Invoice audit inside locked status method', $method !== '' && str_contains($method, 'CrmInvoiceRowGuard::class') && str_contains($method, 'DB::transaction(') && substr_count($method, "'invoice.status_changed'") === 1);
$test('Invoice audit before transaction return', strpos($method, "'invoice.status_changed'") !== false && strpos($method, 'return null;') !== false && strpos($method, "'invoice.status_changed'") < strpos($method, 'return null;'));
$test('No duplicate invoice audit calls', substr_count($invoice, "'invoice.status_changed'") === 1);
$test('Previous workspace audit observer retained', str_contains($provider, 'WorkspaceAuditObserver::class'));
$test('Previous extended workspace audit observer retained', str_contains($provider, 'WorkspaceAuditCoverageObserver::class'));
$test('Previous CRM delivery completion guard retained', is_file($root . '/app/Services/CrmDeliveryCompletionGuard.php'));
$test('Previous CRM invoice row guard retained', is_file($root . '/app/Services/CrmInvoiceRowGuard.php'));

$files = [
    'app/Services/WorkspaceAuditTrail.php',
    'app/Providers/AppServiceProvider.php',
    'app/Http/Controllers/InvoiceController.php',
    'app/Observers/CrmCrossModuleStatusAuditObserver.php',
    'tests/Unit/CrmCrossModuleStatusAuditSourceTest.php',
    'tests/Feature/CrmCrossModuleStatusAuditTransactionTest.php',
    'tools/install_a7531_crm_status_audit.php',
];
$syntax = true;
foreach ($files as $file) {
    if (!is_file($root . '/' . $file)) { $syntax = false; continue; }
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1', $output, $exit);
    if ($exit !== 0) $syntax = false;
    $output = [];
}
$test('PHP syntax for all seven files', $syntax);
$passed = count(array_filter($checks));
echo "\nA7.5.3.1 static: {$passed}/" . count($checks) . ' PASS, ' . (count($checks) - $passed) . " FAIL.\n";
exit($passed === count($checks) ? 0 : 1);
