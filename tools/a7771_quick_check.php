<?php
/** A7.7.7.1 test-only regression compatibility check; no Laravel boot, no DB. */
declare(strict_types=1);
$root = dirname(__DIR__);
$tests = [
    'tests/Unit/CrmCsvExportSourceTest.php',
    'tests/Unit/CrmReportAuthorizationSourceTest.php',
];
$passed = 0;
$failed = 0;
$check = static function (bool $ok, string $label) use (&$passed, &$failed): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
    if ($ok) ++$passed; else ++$failed;
};
foreach ($tests as $path) {
    $full = $root.'/'.$path;
    $check(is_file($full), 'File '.$path);
    if (!is_file($full)) continue;
    $lines = [];
    $exitCode = 1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1', $lines, $exitCode);
    $check($exitCode === 0, 'Syntax '.$path);
}
$csv = @file_get_contents($root.'/tests/Unit/CrmCsvExportSourceTest.php') ?: '';
$report = @file_get_contents($root.'/tests/Unit/CrmReportAuthorizationSourceTest.php') ?: '';
$check(str_contains($csv, "lazy(200)") && !str_contains($csv, "assertStringContainsString('foreach (\$query->cursor()"), 'CSV regression expects bounded lazy batches');
$check(str_contains($csv, 'CrmCsvCellSanitizer::row'), 'CSV formula guard still asserted');
$check(str_contains($report, "private function assertReportsView(): void") && str_contains($report, "public function exportInvoices(Request \$request): StreamedResponse"), 'Reports tests use whole-method boundaries');
$check(str_contains($report, 'assertLessThan($validatePos, $guardPos') && str_contains($report, 'assertLessThan($queryPos, $guardPos'), 'Reports test retains guard-before-validation-and-SQL ordering');
$check(str_contains($report, 'CrmReportingDataScope::class'), 'Reports data scope retained in test');
echo "A7.7.7.1 quick checks: {$passed}/".($passed+$failed)." PASS; {$failed} FAIL. No DB.\n";
exit($failed === 0 ? 0 : 1);
