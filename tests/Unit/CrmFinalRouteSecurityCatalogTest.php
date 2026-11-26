<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CrmRouteAuthorizationPolicy as Policy;
use App\Support\CrmFinalRouteSecurityCatalog as Catalog;
use PHPUnit\Framework\TestCase;

/** Independent expected RBAC contract; no DB, HTTP, migrations, or fixtures. */
final class CrmFinalRouteSecurityCatalogTest extends TestCase
{
    public function test_sensitive_routes_preserve_exact_permissions(): void
    {
        $contracts = Catalog::criticalRoutes();
        self::assertGreaterThanOrEqual(20, count($contracts));
        foreach ($contracts as $name => $expect) {
            if ($expect['independent_admin']) {
                self::assertFalse(Policy::forRoute($name)['managed'], $name);
                continue;
            }
            $actual = Policy::forRoute($name);
            self::assertTrue($actual['managed'], $name);
            self::assertSame($expect['super_admin'], $actual['super_admin'], $name);
            self::assertSame($expect['permissions'], $actual['permissions'], $name);
        }
    }

    public function test_work_log_still_requires_service_page_permission_without_becoming_admin_only(): void
    {
        self::assertSame([
            'managed' => true, 'super_admin' => false,
            'permissions' => ['service.access', 'crm_page.service_tickets.create'],
        ], Policy::forRoute('service-tickets.work-logs.store'));
    }

    public function test_admin_route_is_distinct_from_technician_workflow_route(): void
    {
        self::assertTrue(Catalog::criticalRoutes()['roles.store']['super_admin']);
        self::assertFalse(Catalog::criticalRoutes()['service-tickets.work-logs.store']['super_admin']);
        self::assertSame('PATCH', Catalog::criticalRoutes()['maintenance.change-status']['method']);
    }
}
