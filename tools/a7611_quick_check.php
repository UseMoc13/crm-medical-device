<?php
/** A7.6.1.1 static regression checks: no database connection necessary. */
$root = dirname(__DIR__);
$testFile = $root . '/tests/Unit/CrmKpiSourceCatalogTest.php';
$service = $root . '/app/Services/CrmKpiSourceReadiness.php';
$installer = __DIR__ . '/install_a7611_kpi_catalog_test_fix.php';
$checks = [];
$check = static function (string $name, bool $pass) use (&$checks): void {
    $checks[] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$test = is_file($testFile) ? file_get_contents($testFile) : '';
$src = is_file($service) ? file_get_contents($service) : '';
$fix = is_file($installer) ? file_get_contents($installer) : '';
$pattern = '/^[a-z_]+\.[a-z_][a-z0-9_]*$/';
$check('KPI catalog test installed', is_file($testFile));
$check('KPI catalog service retained', is_file($service));
$check('Fixed regex accepts numeric metric suffix', str_contains($test, "'/^[a-z_]+\\.[a-z_][a-z0-9_]*$/'"));
$check('Old restrictive regex absent', !str_contains($test, "'/^[a-z_]+\\.[a-z_]+$/'"));
$check('Numeric metric matches policy', preg_match($pattern, 'warranties.expiring_30_days') === 1);
$check('Other metric matches policy', preg_match($pattern, 'service_tickets.total') === 1);
$check('Uppercase and hyphen rejected', preg_match($pattern, 'Invoices.Paid') === 0 && preg_match($pattern, 'invoices.paid-value') === 0);
$check('Other assertions retained', str_contains($test, 'assertStringStartsWith') && str_contains($test, 'assertStringEndsWith') && str_contains($test, 'assertNotEmpty'));
$check('Read-only source test retained', str_contains($test, 'test_baseline_never_reads_raw_business_values_or_writes'));
$check('Installer has guarded fingerprint', str_contains($fix, '$originalSha') && str_contains($fix, '$patchedSha'));
$check('Installer backs up file', str_contains($fix, 'a7611_backups') && str_contains($fix, 'copy($destination, $backupFile)'));
$check('Installer does not reference schema mutations', !preg_match('/Artisan::call|Schema::|DB::|migrate|truncate\s*\(/', $fix));
$php = PHP_BINARY;
foreach ([$testFile, $installer, __FILE__] as $path) {
    $result = 1;
    exec(escapeshellarg($php) . ' -l ' . escapeshellarg($path) . ' 2>&1', $lines, $result);
    $check('PHP syntax: ' . basename($path), $result === 0);
}
$passed = count(array_filter($checks));
echo PHP_EOL . 'A7.6.1.1 static: ' . $passed . '/' . count($checks) . ' PASS, ' . (count($checks) - $passed) . ' FAIL.' . PHP_EOL;
exit($passed === count($checks) ? 0 : 1);
