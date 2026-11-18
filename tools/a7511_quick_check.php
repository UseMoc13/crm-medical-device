<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$checks=[];
$check=static function (string $label, bool $ok) use (&$checks): void {
    $checks[]=$ok;
    echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
};
$runtime=$root.'/tools/a751_runtime_preflight.php';
$test=$root.'/tests/Feature/CrmIntegrationAuditCommandTest.php';
foreach ([$runtime,$test] as $file) {
    $check('File '.str_replace($root.'/', '', $file), is_file($file));
}
if (!is_file($runtime) || !is_file($test)) exit(1);
foreach ([$runtime,$test] as $file) {
    $s=file_get_contents($file);
    $label=str_replace($root.'/', '', $file);
    $check($label.' uses Kernel::all command catalog', str_contains($s,"array_key_exists('crm:integration:audit', Artisan::all())"));
    $check($label.' no longer calls unsupported Artisan::has()', !str_contains($s, 'Artisan::has('));
    $output=[];
    $exit=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $output, $exit);
    $check($label.' PHP syntax', $exit===0);
}
$testSource=file_get_contents($test);
$check('Original audit JSON privacy assertions retained', str_contains($testSource, "assertArrayNotHasKey('sample_ids'"));
$check('Original read-only database test retained', str_contains($testSource, 'test_audit_issues_select_queries_only'));
$check('No audit or application implementation files changed in patch',
    is_file(__DIR__.'/a7511_payload/tools/a751_runtime_preflight.php')
    && is_file(__DIR__.'/a7511_payload/tests/Feature/CrmIntegrationAuditCommandTest.php')
    && !is_dir(__DIR__.'/a7511_payload/app'));
$n=count(array_filter($checks)); $total=count($checks);
echo "\nA7.5.1.1 static: {$n}/{$total} PASS".($n===$total?', 0 FAIL':', '.($total-$n).' FAIL').".\n";
exit($n===$total?0:1);
