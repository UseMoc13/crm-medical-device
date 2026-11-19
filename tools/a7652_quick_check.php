<?php
/** A7.6.5.2 static checks. Do not connect to any database. */
$root = dirname(__DIR__);
$checks = [];
$ok = static function (string $name, bool $pass) use (&$checks): void {
    $checks[$name] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ').$name."\n";
};
$files = [
    'app/Services/CrmFinancialParityBaseline.php',
    'app/Console/Commands/AuditCrmFinancialParity.php',
    'tests/Unit/CrmFinancialParityContractTest.php',
    'tests/Feature/CrmFinancialParityBaselineCommandTest.php',
    'tools/install_a7652_parity_baseline.php',
    'tools/a7652_quick_check.php',
    'tools/a7652_runtime_preflight.php',
];
foreach ($files as $rel) {
    $path = $root.'/'.$rel;
    $ok('File '.$rel, is_file($path));
    if (is_file($path)) {
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $lines, $code);
        $ok('PHP syntax '.$rel, $code === 0);
        unset($lines);
    }
}
$source = file_get_contents($root.'/app/Services/CrmFinancialParityBaseline.php');
$command = file_get_contents($root.'/app/Console/Commands/AuditCrmFinancialParity.php');
$test = file_get_contents($root.'/tests/Feature/CrmFinancialParityBaselineCommandTest.php');
$ok('Source-only data access', str_contains($source, 'file_get_contents(') && !str_contains($source, 'use Illuminate\\Support\\Facades\\DB;') && !str_contains($source, 'DB::statement('));
$ok('Explicit REPORT vs Invoice scope risk', str_contains($source, 'reports_vs_invoice_entitlements'));
$ok('Explicit opt-in vs grant risk', str_contains($source, 'opt_in_report_rows_vs_explicit_dashboard_scope'));
$ok('Financial formula comparisons', str_contains($source, 'invoice_issued_statuses_agree') && str_contains($source, 'invoice_overdue_statuses_agree'));
$ok('Separate source drift from policy review', str_contains($source, 'SOURCE_DRIFT') && str_contains($source, 'REVIEW_REQUIRED'));
$ok('JSON and strict options', str_contains($command, '--json') && str_contains($command, '--strict'));
$ok('Command test no-business-SQL assertion', str_contains($test, 'DB::listen') && str_contains($test, 'assertSame([], $executed)'));
$ok('Installer collision guard', str_contains(file_get_contents($root.'/tools/install_a7652_parity_baseline.php'), 'Refusing local file conflict'));
$passed = count(array_filter($checks));
echo 'A7.6.5.2 static: '.$passed.'/'.count($checks).' PASS, '.(count($checks)-$passed).' FAIL.'."\n";
exit($passed === count($checks) ? 0 : 1);
