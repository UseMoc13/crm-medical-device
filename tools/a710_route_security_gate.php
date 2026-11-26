<?php

declare(strict_types=1);

/** A7.10.1/2 route/authorization preflight: NO business database queries, HTTP calls or mutations. */
$root = dirname(__DIR__);
if (array_slice($argv, 1) !== ['--routes']) {
    fwrite(STDERR, "Usage: php tools/a710_route_security_gate.php --routes\n");
    exit(2);
}
if (!is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "[FAIL] composer vendor/autoload.php is missing.\n"); exit(2);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\CrmFinalRouteSecurityCatalog as Catalog;
use App\Services\CrmRouteAuthorizationPolicy as Policy;
use App\Services\CrmModuleAccess;
use App\Services\CrmRoleAccessCatalog;
use App\Http\Middleware\EnforceCrmRouteAuthorization;
use App\Http\Middleware\EnsureCrmAuthenticated;
use App\Http\Middleware\PreventCrmResponseCaching;
use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use Illuminate\Support\Facades\Route;

if (!app()->environment('local')) {
    fwrite(STDERR, "[STOP] Only APP_ENV=local is supported by this local preflight.\n"); exit(2);
}

$failures = 0;
$warnings = 0;
$checks = 0;
$web = $app['router']->getMiddlewareGroups()['web'] ?? [];
foreach ([EnsureCrmAuthenticated::class, EnforceCrmRouteAuthorization::class, PreventCrmResponseCaching::class] as $guard) {
    $checks++;
    if (!in_array($guard, $web, true)) {
        echo '[FAIL] Global web middleware missing '.$guard.PHP_EOL; ++$failures;
    } else {
        echo '[PASS] Global web middleware '.$guard.PHP_EOL;
    }
}

$named = [];
foreach (Route::getRoutes() as $route) {
    $name = $route->getName();
    if (!is_string($name) || $name === '') continue;
    $named[$name][] = $route;
}
foreach (Catalog::criticalRoutes() as $name => $expect) {
    ++$checks;
    $routes = $named[$name] ?? [];
    if (count($routes) !== 1) {
        echo sprintf('[FAIL] %s route occurrences=%d (expected 1)', $name, count($routes)).PHP_EOL;
        ++$failures; continue;
    }
    $route = $routes[0];
    if (!in_array($expect['method'], $route->methods(), true)) {
        echo '[FAIL] '.$name.' expected HTTP '.$expect['method'].PHP_EOL;
        ++$failures; continue;
    }
    $policy = Policy::forRoute($name);
    if ($expect['independent_admin']) {
        // Outside the page catalog; deliberately enforced by its own middleware.
        $ownMiddleware = $route->gatherMiddleware();
        if ($policy['managed'] || !in_array(EnsureCrmOrganizationAdmin::class, $ownMiddleware, true)) {
            echo '[FAIL] '.$name.' independent organization admin guard mismatch'.PHP_EOL;
            ++$failures; continue;
        }
    } elseif (!$policy['managed'] || $policy['super_admin'] !== $expect['super_admin']
        || $policy['permissions'] !== $expect['permissions']) {
        echo '[FAIL] '.$name.' route permission contract changed; manual review required'.PHP_EOL;
        ++$failures; continue;
    }
    echo '[PASS] '.$name.' ['.$expect['method'].']'.PHP_EOL;
}

$managed = 0;
$incomplete = 0;
$independentMutations = [];
foreach ($named as $name => $routes) {
    foreach ($routes as $route) {
        $policy = Policy::forRoute($name);
        $catalogued = CrmModuleAccess::permissionForRoute($name) !== null
            || CrmRoleAccessCatalog::forRoute($name) !== null;
        if ($catalogued) {
            $managed++;
            if (!$policy['managed'] || (!$policy['super_admin'] && $policy['permissions'] === [])) {
                ++$incomplete;
                echo '[FAIL] Catalogued route missing complete module/page permissions: '.$name.PHP_EOL;
            }
        }
        $method = array_intersect($route->methods(), ['POST','PUT','PATCH','DELETE']);
        if ($method && !$policy['managed']
            && !in_array(EnsureCrmOrganizationAdmin::class, $route->gatherMiddleware(), true)) {
            $independentMutations[$name] = true;
        }
    }
}
$failures += $incomplete;
$warnings += count($independentMutations);
echo '[SUMMARY] critical_checks='.$checks.' managed_routes='.$managed
    .' catalog_incomplete='.$incomplete.' independent_write_routes='.count($independentMutations)
    .' failures='.$failures.PHP_EOL;
if ($independentMutations) {
    echo '[REVIEW] Independent write endpoints use their own guards (not verified by this route gate): '
        .implode(', ', array_keys($independentMutations)).PHP_EOL;
}
echo '[NOTE] Browser UAT, data scoping, independent workspace guards and two policy holds remain separate.' . PHP_EOL;
echo '[SAFE] No database queries, business requests, writes, or role grants.' . PHP_EOL;
echo '[RESULT] '.($failures === 0 ? 'A710_ROUTE_SECURITY_CONTRACT_READY' : 'A710_ROUTE_SECURITY_CONTRACT_FAILED').PHP_EOL;
exit($failures === 0 ? 0 : 1);
