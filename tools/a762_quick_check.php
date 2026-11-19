<?php
/** Static gate, no Laravel boot or DB reads. */
$root = dirname(__DIR__);
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[$label] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL;
};
$paths = [
    'app/Services/CrmScopedKpiQuery.php',
    'tests/Unit/CrmScopedKpiPolicyTest.php',
    'tests/Feature/CrmScopedKpiIsolatedTest.php',
];
foreach ($paths as $file) $check('File '.$file, is_file($root.'/'.$file));
$service = is_file($root.'/'.$paths[0]) ? file_get_contents($root.'/'.$paths[0]) : '';
$test = is_file($root.'/'.$paths[2]) ? file_get_contents($root.'/'.$paths[2]) : '';
$check('Service requires authenticated web actor', str_contains($service, "Auth::guard('web')->user()"));
$check('Service imports A7.6.1 KPI contracts', str_contains($service, 'CrmKpiSourceReadiness::METRICS'));
$check('Module-base and view permissions checked', str_contains($service, 'CrmRoleAccessCatalog::PAGES') && str_contains($service, '$this->permissions->allows'));
$check('Null/unsupported scope fails closed', str_contains($service, "return 'restricted';") && str_contains($service, "['all', 'own', 'assigned', 'branch']"));
$check('Supported subjects have explicit ownership roles', str_contains($service, "'customers' => ['own', 'branch']") && str_contains($service, "'installations' => ['assigned', 'branch']"));
$check('Unscoped modules require all', str_contains($service, 'self::SCOPED_SUBJECTS[$table] ?? []'));
$check('No financial value returned to restricted caller', str_contains($service, "['status' => 'restricted']") && str_contains($service, "'value' => (string) " . '$finance[$id]' ));
$check('Period and as-of input used', str_contains($service, "->whereDate('invoice_date'") && str_contains($service, '[$asOfDate]'));
$check('Invoice issued statuses unchanged', str_contains($service, "status IN ('Sent','Paid')") && !str_contains($service, "status IN ('Sent','Paid','unpaid')"));
$check('Money SUM stays string', str_contains($service, '(string) ($totals->invoiced'));
$check('No CRUD mutations in query service', !preg_match('/->\s*(insert|update|delete|truncate|increment|decrement|create|save|upsert)\s*\(/i', $service));
$check('Isolated test enforces memory DB', str_contains($test, "'database' => ':memory:'"));
$check('Isolated test uses explicit schema connection', str_contains($test, "Schema::connection('a762_isolated')->create"));
$check('Test restores default and clears schema cache', str_contains($test, 'DB::setDefaultConnection($oldDefault)') && str_contains($test, "Schema::clearResolvedInstance('db.schema')"));
$check('Test checks exclusion of legacy unpaid', str_contains($test, "['unpaid', '2026-11-03'"));
$check('Test includes non-write SQL listener', str_contains($test, 'DB::listen'));
$check('Installer guarded on source hash', str_contains(file_get_contents(__DIR__.'/install_a762_scoped_kpi.php'), "hash_file('sha256', " . '$src)'));
foreach (array_merge($paths, ['tools/install_a762_scoped_kpi.php', 'tools/a762_quick_check.php', 'tools/a762_runtime_preflight.php']) as $relative) {
    $p = $root.'/'.$relative;
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($p).' 2>&1';
    exec($command, $out, $code); unset($out);
    $check('PHP syntax '.$relative, $code === 0);
}
$ok = count(array_filter($checks));$total=count($checks);
echo PHP_EOL.'A7.6.2 static: '.$ok.'/'.$total.' PASS, '.($total-$ok).' FAIL.'.PHP_EOL;
exit($ok === $total ? 0 : 1);
