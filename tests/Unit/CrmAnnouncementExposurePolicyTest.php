<?php

namespace Tests\Unit;

use App\Support\CrmAnnouncementExposurePolicy;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CrmAnnouncementExposurePolicyTest extends TestCase
{
    public function test_hidden_references_with_public_storage_link_are_held(): void
    {
        self::assertSame('HOLD_HIDDEN_PUBLIC_DISK_REFERENCES', CrmAnnouncementExposurePolicy::decide(12, 1, true));
    }

    public function test_absent_public_link_does_not_prove_absence_of_risk(): void
    {
        self::assertSame('REVIEW_HIDDEN_REFERENCES_PUBLIC_SERVING_NOT_VERIFIED', CrmAnnouncementExposurePolicy::decide(12, 1, false));
    }

    public function test_published_only_references_still_require_visibility_policy(): void
    {
        self::assertSame('REVIEW_PUBLIC_ASSET_VISIBILITY_POLICY', CrmAnnouncementExposurePolicy::decide(2, 0, true));
    }

    public function test_no_references_reports_no_current_stored_assets(): void
    {
        self::assertSame('NO_CURRENT_STORED_ASSET_REFERENCES', CrmAnnouncementExposurePolicy::decide(0, 0, true));
    }

    public function test_inconsistent_counts_fail_closed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CrmAnnouncementExposurePolicy::decide(1, 2, true);
    }
}
