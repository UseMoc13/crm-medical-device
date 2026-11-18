<?php
$root = dirname(__DIR__);
$checks = [];
$pass = function (string $description, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
};
$paths = [
    'app/Services/CrmKpiSourceReadiness.php',
    'app/Console/Commands/AuditCrmKpiSources.php',
    'tests/Unit/CrmKpiSourceCatalogTest.php',
    'tests/Feature/CrmKpiSourceBaselineCommandTest.php',
];
foreach ($paths as $relative) $pass('File '.$relative, is_file($root.'/'.$relative));
$service = is_file($root.'/'.$paths[0]) ? file_get_contents($root.'/'.$paths[0]) : '';
$command = is_file($root.'/'.$paths[1]) ? file_get_contents($root.'/'.$paths[1]) : '';
$installer = file_get_contents(__DIR__.'/install_a761_kpi_source_audit.php');
$pass('Metric source catalog defined', str_contains($service, 'public const METRICS'));
$pass('Role view permission catalog', str_contains($service, "'permission' => 'crm_page."));
$pass('Schema table readiness', str_contains($service, 'Schema::hasTable('));
$pass('Schema column readiness', str_contains($service, 'Schema::hasColumn('));
$pass('No raw business aggregation query', !str_contains($service, 'DB::table('));
$pass('No write/query mutations', preg_match('/->(?:insert|update|delete|truncate|create)\s*\(/i', $service) === 0);
$pass('Security review counts', str_contains($service, 'dashboard.global_counts'));
$pass('Security review Activity preview', str_contains($service, 'dashboard.activity_preview'));
$pass('Detect Linux filename case mismatch', str_contains($service, 'reports.controller_filename_case') && str_contains($service, 'scandir('));
$pass('Review dashboard placeholder', str_contains($service, 'dashboard.placeholder_sales'));
$pass('Review view publishing side effect', str_contains($service, 'dashboard.announcement_publish_in_view'));
$pass('Source unavailable not READY', str_contains($service, "'source_unavailable'"));
$pass('Schema missing not READY', str_contains($service, "'INCOMPLETE'"));
$pass('JSON + strict supported', str_contains($command, '{--json') && str_contains($command, '{--strict'));
$pass('Summary has no monetary values', str_contains($service, "'aggregate_values_included' => false"));
$pass('Installer refuses collisions', str_contains($installer, 'Refusing conflicting local file'));
$pass('Installer dry run', str_contains($installer, '--dry-run'));
$pass('Installer only adds files', str_contains($installer, "if (\$kind !== 'add') continue;") && str_contains($installer, 'copy($src, $dst)'));
foreach (array_merge($paths, ['tools/install_a761_kpi_source_audit.php','tools/a761_quick_check.php','tools/a761_runtime_preflight.php']) as $relative) {
    $file = $root.'/'.$relative;
    $result = is_file($file) ? shell_exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1') : '';
    $pass('PHP syntax '.$relative, str_contains((string) $result, 'No syntax errors detected'));
}
$total = count($checks); $okCount = count(array_filter($checks));
echo PHP_EOL.'A7.6.1 static: '.$okCount.'/'.$total.' PASS, '.($total-$okCount).' FAIL.'.PHP_EOL;
exit($total === $okCount ? 0 : 1);
