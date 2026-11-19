<?php
/** A7.6.3.1 static verification; no DB connections. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$name.PHP_EOL;
};
$servicePath = $root.'/app/Services/CrmDashboardViewData.php';
$fixturePath = $root.'/tests/Feature/CrmDashboardViewerIsolationTest.php';
$preflightPath = $root.'/tools/a763_runtime_preflight.php';
foreach ([$servicePath, $fixturePath, $preflightPath] as $path) $check('File '.$path, is_file($path));
if (count(array_filter($checks)) !== count($checks)) exit(1);
$s = file_get_contents($servicePath);
$t = file_get_contents($fixturePath);
$p = file_get_contents($preflightPath);
$check('Runtime Activity schema uses subject', str_contains($s, "['activity_id', 'user_id', 'subject', 'description', 'created_at']"));
$check('Dashboard SELECT aliases subject as title', str_contains($s, "'subject as title'"));
$check('No nonexistent title column required', !str_contains($s, "'user_id', 'title', 'description'"));
$check('Fixture creates real subject column', str_contains($t, '$t->string(\'subject\')' ) && !str_contains($t, '$t->string(\'title\')' ));
$check('Fixture inserts subject values', substr_count($t, "'subject'=>") === 4 && !str_contains($t, "'title'=>"));
$check('Fixture explicitly rejects title schema', str_contains($t, "assertFalse(Schema::hasColumn('activities', 'title'))"));
$check('Fixture retains title alias assertion', str_contains($t, "recentActivities']->pluck('title')"));
$check('Viewer ownership and collaborator rules unchanged', str_contains($s,"where('activities.user_id', \$viewer)") && str_contains($s,"workspace_collaboration_members.user_id"));
$check('Activity permission enforced', str_contains($s, "CrmRoleAccessCatalog::key('activities', 'view')"));
$check('No SQL writes in read model', !preg_match('/\b(?:insert|update|delete|truncate|create)\s*\(/i', $s));
$check('Preflight checks subject and description', str_contains($p, "Schema::hasColumn('activities','subject')") && str_contains($p, "Schema::hasColumn('activities','description')"));
$check('Preflight does not expect title', !str_contains($p,"Schema::hasColumn('activities','title')"));
$check('Preflight still checks all readiness items', str_contains($p, "Dashboard view compiles as named view") && str_contains($p, 'KPI contracts still contain 12 metrics'));
$check('No schema mutations in preflight', !preg_match('/\b(?:insert|update|delete|truncate|create)\s*\(/i',$p));
$check('Installer is guarded and makes backup', str_contains(file_get_contents(__DIR__.'/install_a7631_activity_schema_fix.php'), "['before']") && str_contains(file_get_contents(__DIR__.'/install_a7631_activity_schema_fix.php'),'a7631_backups'));
foreach ([$servicePath,$fixturePath,$preflightPath,__DIR__.'/install_a7631_activity_schema_fix.php',__FILE__] as $path) {
    $cmd = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1';
    $lines=[]; $code=1;
    exec($cmd, $lines, $code);
    $check('PHP syntax '.basename($path), $code===0);
}
$passing = count(array_filter($checks));
echo PHP_EOL.'A7.6.3.1 static: '.$passing.'/'.count($checks).' PASS; '.(count($checks)-$passing).' FAIL.'.PHP_EOL;
exit($passing===count($checks) ? 0 : 1);
