<?php
/** A7.7.3: static checks only. NO Laravel boot, NO DB. */
declare(strict_types=1);
$root = dirname(__DIR__);
$required = [
    'bootstrap/app.php',
    'app/Services/CrmModuleAccess.php',
    'app/Services/CrmRoleAccessCatalog.php',
    'app/Services/CrmWorkflowAccess.php',
    'app/Services/CrmPermissionResolver.php',
    'app/Services/CrmRouteAuthorizationPolicy.php',
    'app/Http/Middleware/EnforceCrmRouteAuthorization.php',
    'app/Http/Middleware/EnsureCrmAuthenticated.php',
    'app/Http/Middleware/PreventCrmResponseCaching.php',
    'tests/Unit/CrmRouteAuthorizationPolicyTest.php',
    'tests/Feature/CrmRouteAuthorizationHttpTest.php',
];
$checks = [];
foreach ($required as $path) {
    $file = $root . '/' . $path;
    $checks['File '.$path] = is_file($file);
    if (is_file($file)) {
        $output = []; $code = 1;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $output, $code);
        $checks['PHP syntax '.$path] = $code === 0;
    }
}
$bootstrap = @file_get_contents($root.'/bootstrap/app.php') ?: '';
$auth = strpos($bootstrap, '\\App\\Http\\Middleware\\EnsureCrmAuthenticated::class');
$rbac = strpos($bootstrap, '\\App\\Http\\Middleware\\EnforceCrmRouteAuthorization::class');
$cache = strpos($bootstrap, '\\App\\Http\\Middleware\\PreventCrmResponseCaching::class');
$checks['Middleware: privacy wraps auth then RBAC'] = $auth !== false && $rbac !== false && $cache !== false && $cache < $auth && $auth < $rbac;
$policy = @file_get_contents($root.'/app/Services/CrmRouteAuthorizationPolicy.php') ?: '';
$guard = @file_get_contents($root.'/app/Http/Middleware/EnforceCrmRouteAuthorization.php') ?: '';
$checks['Module, page, workflow catalogs reused'] = str_contains($policy, 'CrmModuleAccess::permissionForRoute')
    && str_contains($policy, 'CrmRoleAccessCatalog::forRoute') && str_contains($policy, 'CrmWorkflowAccess::forRoute');
$checks['Admin endpoints explicitly protected'] = str_contains($policy, "roles|users") && str_contains($guard, 'isSuperAdmin');
$checks['Active account checked'] = str_contains($guard, "'active'");
$checks['Deny before next/controller'] = strpos($guard, 'abort_unless(') < strpos($guard, 'return $next($request);', strpos($guard, 'abort_unless('));
$checks['No permission grants or database mutation calls'] = preg_match('/(?:DB::|->)(?:insert|update|delete|upsert|truncate|sync|attach|detach)\s*\(/i', $guard.$policy) !== 1;
$passed=0;
foreach ($checks as $label=>$ok) {echo ($ok ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL; if ($ok) $passed++;}
echo "A7.7.3 static: {$passed}/".count($checks)." PASS; ".(count($checks)-$passed)." FAIL. No DB.\n";
exit($passed===count($checks)?0:1);
