<?php
/** A7.11.2 safe source/config preflight. No Laravel bootstrap, SQL, or application writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
$failures = [];
function gate(bool $condition, string $name): void {
    global $failures;
    echo '['.($condition ? 'PASS' : 'BLOCK').'] '.$name.PHP_EOL;
    if (!$condition) $failures[] = $name;
}
$required = [
 'artisan', 'phpunit.xml', 'tools/a7108_full_audit.php', 'tools/a7108_navigation_fix.php',
 'tools/a7108_js_regression.cjs', 'tools/a781_quick_check.php',
 'tools/a783_quick_check.php', 'tools/a783_workflow_preflight.php',
 'tools/a784_validate_uat_ledger.php', 'docs/uat/a784_uat_execution_template.json',
 'tests/Feature/CrmRouteAuthorizationHttpTest.php',
 'tests/Feature/CrmDeliveryDispatchPostconditionIsolationTest.php',
 'tests/Feature/WorkspacePersonalNoteHttpTest.php',
 'resources/views/deliveries/show.blade.php',
 'resources/views/installations/show.blade.php',
 'resources/views/maintenance/_workflow.blade.php',
];
foreach ($required as $p) gate(is_file($root.'/'.$p), 'Required source '.$p);
$xmlPath = $root.'/phpunit.xml';
$xml = is_file($xmlPath) ? (string) file_get_contents($xmlPath) : '';
// Require exact isolation contracts in PHPUnit XML, not .env values.
foreach (['APP_ENV'=>'testing', 'DB_CONNECTION'=>'sqlite', 'DB_DATABASE'=>':memory:', 'DB_URL'=>''] as $key=>$val) {
  $pattern = '/<env\s+name="'.preg_quote($key,'/').'"\s+value="'.preg_quote($val,'/').'"\s*\/>/';
  gate((bool) preg_match($pattern, $xml), 'PHPUnit isolation '.$key);
}
gate(!is_file($root.'/resources/views/stock-movements/edit.blade.php'), 'A7.11.1 obsolete stock edit view removed');
gate(is_file($root.'/tests/Feature/WorkspacePersonalNoteHttpTest.php'), 'A7.11.0 personal notes fixture present');
$docs = is_file($root.'/docs/A781_UAT_MATRIX.md') ? (string) file_get_contents($root.'/docs/A781_UAT_MATRIX.md') : '';
for ($i=1;$i<=15;$i++) gate(str_contains($docs, sprintf('UAT-%02d',$i)), sprintf('Scenario UAT-%02d described',$i));
echo '[RESULT] '.($failures ? 'A7112_PREFLIGHT_BLOCKED failures='.count($failures) : 'A7112_PREFLIGHT_READY manual_uat_not_executed').PHP_EOL;
exit($failures ? 2 : 0);
