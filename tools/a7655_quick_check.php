<?php
/** A7.6.5.5 static validator; never bootstraps or queries the CRM database. */
declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    'app/Services/CrmStagingInvoiceParity.php',
    'app/Console/Commands/AuditCrmStagingInvoiceParity.php',
    'config/crm_financial_parity.php',
    'tests/Unit/CrmStagingInvoiceParityGuardTest.php',
    'tests/Unit/CrmStagingInvoiceParitySourceTest.php',
    'tests/Feature/CrmStagingInvoiceParitySqliteTest.php',
    'tools/install_a7655_staging_parity.php',
    'tools/a7655_quick_check.php',
    'tools/a7655_runtime_preflight.php',
];
$pass = 0;
$fail = 0;
$check = static function (bool $ok, string $label) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n";
};
foreach ($paths as $rel) {
    $abs = $root . '/' . $rel;
    $check(is_file($abs), 'File ' . $rel);
    if (is_file($abs)) {
        $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($abs) . ' 2>&1';
        exec($cmd, $lines, $code);
        $check($code === 0, 'PHP syntax ' . $rel);
        unset($lines);
    }
}
$service = @file_get_contents($root . '/app/Services/CrmStagingInvoiceParity.php') ?: '';
$command = @file_get_contents($root . '/app/Console/Commands/AuditCrmStagingInvoiceParity.php') ?: '';
$config = @file_get_contents($root . '/config/crm_financial_parity.php') ?: '';
$fixture = @file_get_contents($root . '/tests/Feature/CrmStagingInvoiceParitySqliteTest.php') ?: '';
$installer = @file_get_contents($root . '/tools/install_a7655_staging_parity.php') ?: '';
$check(str_contains($config, "env('CRM_A7655_STAGING_PARITY_ENABLED', false)"), 'Opt-in defaults OFF');
$check(str_contains($command, "app()->environment() !== 'staging'"), 'Only staging environment allowed');
$check(str_contains($command, "'pgsql'" ) === false && str_contains($service, "if (\$driver !== 'pgsql')"), 'Driver enforced by policy gate');
$check(str_contains($command, "--confirm-staging"), 'Explicit CLI confirmation required');
$check(str_contains($command, 'SET TRANSACTION READ ONLY'), 'PostgreSQL server read-only transaction enforced');
$check(str_contains($command, 'rollBack()'), 'Read-only transaction rolled back');
$check(str_contains($command, 'CrmFinancialParityBaseline'), 'Existing formula source audit reused');
$check(str_contains($command, 'salesScopeEnabled()'), 'Opt-in row scope blocks unscoped comparison');
$check(str_contains($service, "FILTER (WHERE status IN ('Sent','Paid'))"), 'Report FILTER aggregate retained');
$check(str_contains($service, "CASE WHEN status IN ('Sent','Paid')"), 'Dashboard CASE aggregate retained');
$check(str_contains($service, "->leftJoin('sales_orders as so'"), 'CSV join shape compared');
$check(str_contains($service, 'csv_left_join_preserves_invoice_rows'), 'CSV join cardinality checked');
$check(str_contains($service, 'authenticated_viewer_parity_verified') && str_contains($service, '=> false'), 'Does not claim per-user parity');
$check(!str_contains($service, "'amounts' =>") && !str_contains($service, "'customer_name' =>"), 'No amounts or names in result envelope');
$check(str_contains($fixture, "':memory:'") && str_contains($fixture, "'testing'"), 'Fixture isolated in disposable SQLite');
$check(str_contains($installer, "fopen(\$target, 'x')"), 'Additive-only exclusive file creation');
$check(str_contains($installer, "['--dry-run']"), 'Installer supports dry-run');
$check(str_contains($command, 'return $this->blocked('), 'Command fail-closed');
echo "A7.6.5.5 static: {$pass} PASS, {$fail} FAIL.\n";
exit($fail ? 1 : 0);
