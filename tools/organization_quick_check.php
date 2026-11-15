<?php
/**
 * A7.4.5 - Safe Organization Quick Check (READ-ONLY)
 * Run: php tools/organization_quick_check.php
 * It does not insert/update/delete records. Invalid controller requests are
 * deliberately rejected before any write; report discrepancies instead.
 */

declare(strict_types=1);

use App\Http\Controllers\OrganizationController;
use App\Http\Middleware\EnsureCrmOrganizationAdmin;
use App\Models\Role;
use App\Models\User;
use App\Services\CrmOrganizationHierarchy;
use App\Services\CrmPermissionResolver;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

$root = dirname(__DIR__);
if (!is_file($root . '/vendor/autoload.php') || !is_file($root . '/bootstrap/app.php')) {
    fwrite(STDERR, "Run this script from the Laravel project root, after extracting into tools/.\n");
    exit(2);
}
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$passed = 0;
$failed = 0;
$skipped = 0;
$checks = 0;
$emit = static function (string $status, string $label, string $detail = '') use (&$passed, &$failed, &$skipped, &$checks): void {
    ++$checks;
    if ($status === 'PASS') ++$passed;
    elseif ($status === 'FAIL') ++$failed;
    else ++$skipped;
    echo '[' . $status . '] ' . $label . ($detail !== '' ? ' — ' . $detail : '') . PHP_EOL;
};

try {
    echo "A7.4.5 Organization Quick Check | READ-ONLY | " . date('Y-m-d H:i:s') . PHP_EOL;
    echo "Connection: " . DB::getDriverName() . ' | Database: ' . DB::connection()->getDatabaseName() . PHP_EOL . PHP_EOL;
    if (DB::getDriverName() !== 'pgsql') {
        $emit('FAIL', 'Database connection', 'Expected PostgreSQL; refusing to continue.');
        exit(1);
    }
    $dbOK = Schema::hasColumn('roles', 'parent_role_id')
        && Schema::hasTable('user_reporting_lines')
        && Schema::hasColumn('user_reporting_lines', 'manager_user_id');
    $emit($dbOK ? 'PASS' : 'FAIL', 'Organization migration/schema', $dbOK ? 'Role parent and reporting lines found' : 'Missing schema; check migration status');
    if (!$dbOK) exit(1);

    $routes = [
        'organization.index' => ['GET', 'OrganizationController@index'],
        'organization.roles.parent' => ['PATCH', 'OrganizationController@updateRoleParent'],
        'organization.users.manager' => ['PATCH', 'OrganizationController@updateUserManager'],
    ];
    $routeOK = true;
    foreach ($routes as $name => [$method, $action]) {
        $route = Route::getRoutes()->getByName($name);
        $middleware = $route ? $route->gatherMiddleware() : [];
        $ok = $route && in_array($method, $route->methods(), true)
            && str_contains($route->getActionName(), $action)
            && in_array(\App\Http\Middleware\EnsureCrmOrganizationAdmin::class, $middleware, true)
            && in_array(\App\Http\Middleware\EnsureCrmAuthenticated::class, $middleware, true);
        $routeOK = $routeOK && $ok;
        $emit($ok ? 'PASS' : 'FAIL', 'Route ' . $name, $ok ? 'Method/controller/middleware OK' : 'Missing or misconfigured');
    }

    $roles = Role::query()->get();
    $users = User::query()->with('roles')->get();
    $hierarchy = app(CrmOrganizationHierarchy::class);
    $roleCycleOK = true;
    foreach ($roles as $role) {
        try {
            $hierarchy->ancestorRoleIds((string) $role->getKey());
        } catch (ValidationException $e) {
            $roleCycleOK = false;
            break;
        }
    }
    $emit($roleCycleOK ? 'PASS' : 'FAIL', 'Existing role hierarchy cycles', $roleCycleOK ? (string)$roles->count() . ' roles scanned' : 'Circular or overly deep role chain found');

    $lines = DB::table('user_reporting_lines')->pluck('manager_user_id', 'user_id')->all();
    $reportCycleOK = true;
    foreach ($lines as $employeeId => $managerId) {
        try {
            $hierarchy->assertNoUserCycle((string) $employeeId, (string) $managerId);
        } catch (ValidationException $e) {
            $reportCycleOK = false;
            break;
        }
    }
    $emit($reportCycleOK ? 'PASS' : 'FAIL', 'Existing reporting cycles', $reportCycleOK ? count($lines) . ' reporting lines scanned' : 'Circular reporting chain found');

    $userMap = $users->keyBy(fn (User $u) => (string) $u->getKey());
    $reportEligibleOK = true;
    foreach ($lines as $employeeId => $managerId) {
        $employee = $userMap->get((string)$employeeId);
        $manager = $userMap->get((string)$managerId);
        if (!$employee || !$manager || !$hierarchy->isEligibleManager($employee, $manager)) {
            $reportEligibleOK = false;
            break;
        }
    }
    $emit($reportEligibleOK ? 'PASS' : 'FAIL', 'Existing manager eligibility', count($lines) ? 'Same branch and higher role validated' : 'No reporting lines configured yet');

    $resolver = app(CrmPermissionResolver::class);
    $superAdmin = $users->first(fn (User $u) => strtolower((string)$u->status) === 'active' && $resolver->isSuperAdmin($u));
    $ordinary = $users->first(fn (User $u) => strtolower((string)$u->status) === 'active' && !$resolver->isSuperAdmin($u));
    $guard = app(EnsureCrmOrganizationAdmin::class);

    $middlewareTest = static function (User $actor) use ($guard): int {
        $req = Request::create('/organization', 'GET');
        $req->setUserResolver(static fn ($which = null) => $actor);
        try {
            $response = $guard->handle($req, static fn ($r) => response('ALLOW', 200));
            return $response->getStatusCode();
        } catch (HttpExceptionInterface $e) {
            return $e->getStatusCode();
        }
    };
    if ($superAdmin) {
        $status = $middlewareTest($superAdmin);
        $emit($status === 200 ? 'PASS' : 'FAIL', 'Super Admin middleware access', 'HTTP ' . $status);
    } else {
        $emit('SKIP', 'Super Admin middleware access', 'No active Super Admin found');
    }
    if ($ordinary) {
        $status = $middlewareTest($ordinary);
        $emit($status === 403 ? 'PASS' : 'FAIL', 'Non-admin middleware denied', 'HTTP ' . $status);
    } else {
        $emit('SKIP', 'Non-admin middleware denied', 'No active non-admin found');
    }

    $normalRole = $roles->first(fn (Role $r) => !$r->is_system && strcasecmp((string)$r->role_name, 'Super Admin') !== 0);
    if ($superAdmin && $normalRole) {
        // This invalid request always throws before update/audit writes.
        Auth::guard('web')->setUser($superAdmin);
        $req = Request::create('/organization/roles/' . $normalRole->getKey() . '/parent', 'PATCH', [
            'parent_role_id' => (string)$normalRole->getKey(),
        ]);
        try {
            app(OrganizationController::class)->updateRoleParent($req, $normalRole, app(CrmOrganizationHierarchy::class));
            $emit('FAIL', 'Self-parent role validation', 'Unexpectedly accepted');
        } catch (ValidationException $e) {
            $emit(array_key_exists('parent_role_id', $e->errors()) ? 'PASS' : 'FAIL', 'Self-parent role validation', 'Invalid assignment rejected');
        }
    } else {
        $emit('SKIP', 'Self-parent role validation', 'Need Super Admin and an ordinary role');
    }

    if ($superAdmin && $ordinary) {
        Auth::guard('web')->setUser($superAdmin);
        $req = Request::create('/organization/users/' . $ordinary->getKey() . '/manager', 'PATCH', [
            'manager_user_id' => (string)$ordinary->getKey(),
        ]);
        try {
            app(OrganizationController::class)->updateUserManager($req, $ordinary, app(CrmOrganizationHierarchy::class));
            $emit('FAIL', 'Self-manager user validation', 'Unexpectedly accepted');
        } catch (ValidationException $e) {
            $emit(array_key_exists('manager_user_id', $e->errors()) ? 'PASS' : 'FAIL', 'Self-manager user validation', 'Invalid assignment rejected');
        }
    } else {
        $emit('SKIP', 'Self-manager user validation', 'Need Super Admin and an active employee');
    }

    $crossBranchPair = null;
    foreach ($users as $employee) {
        foreach ($users as $manager) {
            if ($employee->getKey() !== $manager->getKey()
                && $employee->branch_id && $manager->branch_id
                && (string)$employee->branch_id !== (string)$manager->branch_id) {
                $crossBranchPair = [$employee, $manager];
                break 2;
            }
        }
    }
    if ($crossBranchPair) {
        $eligible = $hierarchy->isEligibleManager($crossBranchPair[0], $crossBranchPair[1]);
        $emit(!$eligible ? 'PASS' : 'FAIL', 'Cross-branch manager restriction', $eligible ? 'Incorrectly allowed' : 'Different branches rejected');
    } else {
        $emit('SKIP', 'Cross-branch manager restriction', 'No two users with different branches exist');
    }

    // Existing parent relationship provides a known cycle without DB mutations.
    $configured = $roles->first(function (Role $r) use ($roles): bool {
        $parent = $roles->firstWhere('role_id', $r->parent_role_id);
        return $r->parent_role_id && $parent && !$r->is_system && !$parent->is_system
            && strcasecmp((string)$parent->role_name, 'Super Admin') !== 0;
    });
    if ($superAdmin && $configured) {
        $parent = $roles->firstWhere('role_id', $configured->parent_role_id);
        Auth::guard('web')->setUser($superAdmin);
        $req = Request::create('/organization/roles/' . $parent->getKey() . '/parent', 'PATCH', [
            'parent_role_id' => (string)$configured->getKey(),
        ]);
        try {
            app(OrganizationController::class)->updateRoleParent($req, $parent, app(CrmOrganizationHierarchy::class));
            $emit('FAIL', 'Circular role relation validation', 'Unexpectedly accepted');
        } catch (ValidationException $e) {
            $emit(array_key_exists('parent_role_id', $e->errors()) ? 'PASS' : 'FAIL', 'Circular role relation validation', 'Reverse parent assignment rejected');
        }
    } else {
        $emit('SKIP', 'Circular role relation validation', 'Configure one ordinary parent-child role pair first');
    }

    echo PHP_EOL . "Result: {$passed} PASS / {$failed} FAIL / {$skipped} SKIP ({$checks} checks)." . PHP_EOL;
    echo "No records were inserted, updated, or deleted.\n";
    echo "NOTE: This does not test successful persistence, full browser login, or UI. Verify those once in /organization.\n";
    exit($failed > 0 ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "No writes are intentionally made by this script.\n");
    exit(2);
}
