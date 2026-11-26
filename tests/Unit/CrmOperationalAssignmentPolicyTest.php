<?php

namespace Tests\Unit;

use App\Services\CrmOperationalAssignmentPolicy as Policy;
use PHPUnit\Framework\TestCase;

final class CrmOperationalAssignmentPolicyTest extends TestCase
{
    public function test_only_three_workflow_tables_can_be_reassigned_by_cli(): void
    {
        self::assertSame(['service_tickets','maintenance_schedules','installations'], Policy::mutableTables());
        self::assertSame('service_ticket_id', Policy::key('service_tickets'));
        self::assertSame('maintenance_schedule_id', Policy::key('maintenance_schedules'));
        self::assertSame('installation_id', Policy::key('installations'));
        self::assertNull(Policy::key('maintenance_records'));
        self::assertNull(Policy::key('audit_logs'));
    }

    public function test_active_operational_states_are_writable(): void
    {
        foreach (['open', 'in_progress', 'on_hold'] as $state) {
            self::assertTrue(Policy::mutable('service_tickets', $state), $state);
        }
        foreach (['Scheduled', 'In Progress', ' scheduled '] as $state) {
            self::assertTrue(Policy::mutable('maintenance_schedules', $state), $state);
            self::assertTrue(Policy::mutable('installations', $state), $state);
        }
    }

    public function test_finalized_and_historical_records_are_immutable(): void
    {
        foreach (['resolved','closed','cancelled','garbage'] as $state) {
            self::assertFalse(Policy::mutable('service_tickets', $state));
        }
        foreach (['Completed','Cancelled', 'Unknown'] as $state) {
            self::assertFalse(Policy::mutable('maintenance_schedules', $state));
            self::assertFalse(Policy::mutable('installations', $state));
        }
        self::assertFalse(Policy::mutable('maintenance_records','In Progress'));
        self::assertFalse(Policy::mutable('service_tickets', null));
        self::assertSame('HISTORICAL_PERFORMER_DO_NOT_REWRITE', Policy::classification('maintenance_records',null));
        self::assertSame('OPERATIONAL_REVIEW', Policy::classification('service_tickets','open'));
        self::assertSame('HISTORICAL_FINALIZED_DO_NOT_REWRITE', Policy::classification('installations','Completed'));
    }
}
