<?php

namespace Tests\Unit;

use App\Services\CrmRouteAuthorizationPolicy;
use PHPUnit\Framework\TestCase;

final class BranchRouteAuthorizationPolicyTest extends TestCase
{
    public function test_all_branch_resource_endpoints_are_super_admin_only(): void
    {
        foreach (['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'] as $method) {
            $policy = CrmRouteAuthorizationPolicy::forRoute('branches.' . $method);
            self::assertTrue($policy['managed'], 'branches.' . $method . ' must be managed');
            self::assertTrue($policy['super_admin'], 'branches.' . $method . ' must be Super Admin-only');
            self::assertSame([], $policy['permissions']);
        }
    }
}
