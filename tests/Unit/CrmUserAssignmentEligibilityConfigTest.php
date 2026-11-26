<?php

namespace Tests\Unit;

use App\Services\CrmUserAssignmentEligibility;
use Tests\TestCase;

final class CrmUserAssignmentEligibilityConfigTest extends TestCase
{
    public function test_technician_role_aliases_include_demo_and_real_names(): void
    {
        $roles = app(CrmUserAssignmentEligibility::class)->rolesFor('technician');
        self::assertContains('technician', $roles);
        self::assertContains('uat technician', $roles);
        self::assertContains('field service engineer', $roles);
        self::assertNotContains('sales owner', $roles);
    }

    public function test_sales_role_aliases_include_demo_and_real_names(): void
    {
        $roles = app(CrmUserAssignmentEligibility::class)->rolesFor('sales');
        self::assertContains('sales', $roles);
        self::assertContains('uat sales owner', $roles);
        self::assertContains('sales executive', $roles);
        self::assertNotContains('technician', $roles);
    }

    public function test_unknown_purpose_fails_closed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        app(CrmUserAssignmentEligibility::class)->rolesFor('any_user');
    }
}
