<?php
/** A7.7.7 quick source verification; no Laravel boot and no DB. */
declare(strict_types=1);
$root = dirname(__DIR__);
$required = [
 'bootstrap/app.php',
 'app/Http/Middleware/EnsureCrmAuthenticated.php',
 'app/Http/Middleware/EnforceCrmRouteAuthorization.php',
 'app/Http/Middleware/PreventCrmResponseCaching.php',
 'app/Services/CrmLoginThrottlePolicy.php',
 'app/Services/CrmRouteAuthorizationPolicy.php',
 'app/Support/CrmCsvCellSanitizer.php',
 'app/Support/CrmPrivateFilePath.php',
 'app/Http/Controllers/ReportController.php',
 'app/Http/Controllers/SalesOrderController.php',
 'tests/Feature/CrmSecurityResponseBoundaryHttpTest.php',
 'tests/Feature/CrmRouteAuthorizationHttpTest.php',
 'tests/Feature/CrmLoginThrottleHttpIsolationTest.php',
 'tests/Feature/CrmSalesOrderCsvMultiBatchReliabilityTest.php',
 'tests/Feature/CrmSalesOrderPaginationSqliteIsolationTest.php',
 'tools/a777_apply_response_boundary_patch.php',
 'tools/a771_privacy_quick_check.php',
 'tools/a773_quick_check.php',
];
$checks = [];
foreach ($required as $relative) {
 $path = $root.'/'.$relative;
 $checks['File '.$relative] = is_file($path);
 if (!is_file($path)) continue;
 $output = [];$exitCode = 1;
 exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $output, $exitCode);
 $checks['Syntax '.$relative] = $exitCode === 0;
}
$source = (string) @file_get_contents($root.'/bootstrap/app.php');
$pos = [];
foreach (['Privacy'=>'PreventCrmResponseCaching::class', 'Auth'=>'EnsureCrmAuthenticated::class', 'RBAC'=>'EnforceCrmRouteAuthorization::class'] as $key=>$needle) {
 $pos[$key] = strpos($source, $needle);
}
$checks['Privacy wraps auth then RBAC'] = $pos['Privacy'] !== false && $pos['Auth'] !== false && $pos['RBAC'] !== false
 && $pos['Privacy'] < $pos['Auth'] && $pos['Auth'] < $pos['RBAC'];
$xml = (string) @file_get_contents($root.'/phpunit.xml');
$checks['PHPUnit APP_ENV testing'] = (bool) preg_match('/<env\s+name="APP_ENV"\s+value="testing"/', $xml);
$checks['PHPUnit SQLite memory database'] = (bool) preg_match('/<env\s+name="DB_CONNECTION"\s+value="sqlite"/', $xml)
 && (bool) preg_match('/<env\s+name="DB_DATABASE"\s+value=":memory:"/', $xml);
$checks['No cached runtime config (protects test isolation)'] = !is_file($root.'/bootstrap/cache/config.php');
$testSource = (string) @file_get_contents($root.'/tests/Feature/CrmSecurityResponseBoundaryHttpTest.php');
$checks['End-to-end error response tests included'] = str_contains($testSource, 'test_rbac_forbidden_response_still_has_no_store')
 && str_contains($testSource, 'test_guest_json_denial_still_has_no_store')
 && str_contains($testSource, 'test_inactive_user_redirect_still_has_no_store');
$pass=0;foreach($checks as $label=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;if($ok)$pass++;}
echo sprintf('A7.7.7 quick checks: %d/%d PASS; %d FAIL. NO DB.%s', $pass, count($checks), count($checks)-$pass, PHP_EOL);
exit($pass===count($checks)?0:1);
