<?php

namespace Tests\Unit;

use App\Services\CrmWorkLogAuthorPolicy;
use PHPUnit\Framework\TestCase;

final class CrmWorkLogAuthorPolicyTest extends TestCase
{
    public function test_active_assigned_eligible_technician_can_write_in_progress(): void
    {
        self::assertTrue(CrmWorkLogAuthorPolicy::permits(true, true, true, true, false, 'in_progress'));
        self::assertTrue(CrmWorkLogAuthorPolicy::permits(true, true, true, true, false, 'on_hold'));
    }

    public function test_wrong_staff_inactive_and_revoked_technicians_are_denied(): void
    {
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, true, false, true, false, 'in_progress'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, true, true, false, false, 'in_progress'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(false, true, true, true, false, 'in_progress'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, false, true, true, false, 'in_progress'));
    }

    public function test_admin_notes_are_attributed_but_require_active_open_work(): void
    {
        self::assertTrue(CrmWorkLogAuthorPolicy::permits(true, true, false, false, true, 'in_progress'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, false, false, false, true, 'in_progress'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(false, true, false, false, true, 'on_hold'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, true, false, false, true, 'resolved'));
        self::assertFalse(CrmWorkLogAuthorPolicy::permits(true, true, true, true, false, 'closed'));
    }
}
