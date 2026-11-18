<?php
/** A7.5.1.2.1 static checks; does not boot Laravel or touch the database. */
declare(strict_types=1);
$root = dirname(__DIR__);
$results = [];
$check = static function (string $label, bool $ok) use (&$results): void {
    $results[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$runtimePath = $root . '/tools/a751_runtime_preflight.php';
$testPath = $root . '/tests/Feature/CrmIntegrationAuditCommandTest.php';
$runtime = is_file($runtimePath) ? file_get_contents($runtimePath) : '';
$test = is_file($testPath) ? file_get_contents($testPath) : '';
$check('Runtime preflight exists', $runtime !== '');
$check('HTTP command test exists', $test !== '');
$check('Command check uses global array_key_exists', str_contains($runtime, "array_key_exists('crm:integration:audit', \Illuminate\Support\Facades\Artisan::all())"));
$check('No Facades\\array_key_exists function call', !str_contains($runtime, '\Illuminate\Support\Facades\array_key_exists'));
$check('Command registration checked without Artisan::has', !str_contains($runtime, 'Artisan::has(') && !str_contains($test, 'Artisan::has('));
$check('JSON output captured once', substr_count(preg_replace('/\/\/[^\n]*/', '', $test), 'Artisan::output()') === 1 && str_contains($test, '$output = trim(Artisan::output());'));
$check('JSON parser uses captured output', str_contains($test, 'json_decode($output, true, 512, JSON_THROW_ON_ERROR)'));
$check('Exit assertion does not consume output', str_contains($test, '$this->assertSame(0, $exit, $output);'));
$check('Non-empty command JSON required', str_contains($test, 'assertNotSame(\'\', $output'));
$check('Read-only query monitoring retained', str_contains($test, 'DB::listen(') && str_contains($test, 'app(CrmIntegrationAudit::class)->run(false)'));
$check('Sensitive fields excluded from JSON report', str_contains($test, "assertArrayNotHasKey('sample_ids',"));
$check('Runtime schema checks retained', str_contains($runtime, 'Schema::hasTable($table)'));
$total = count($results); $passed = count(array_filter($results));
echo "\nA7.5.1.2.1 static: {$passed}/{$total} PASS, " . ($total - $passed) . " FAIL.\n";
exit($total === $passed ? 0 : 1);
