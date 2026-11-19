<?php

namespace Tests\Unit;

use App\Services\CrmReportPrivacyPilot;
use PHPUnit\Framework\TestCase;

final class CrmReportPrivacyPilotPolicyTest extends TestCase
{
    public function test_both_flags_off_preserve_existing_surfaces(): void
    {
        $result = CrmReportPrivacyPilot::decide(false, false, false);
        self::assertTrue($result['customer_product_details']);
        self::assertTrue($result['invoice_csv']);
        self::assertFalse($result['detail_pilot']);
        self::assertFalse($result['csv_pilot']);
    }

    public function test_detail_pilot_hides_labels_and_csv_without_invoice_all(): void
    {
        foreach ([false, true] as $csvPilot) {
            $result = CrmReportPrivacyPilot::decide(true, $csvPilot, false);
            self::assertFalse($result['customer_product_details']);
            self::assertFalse($result['invoice_csv']);
        }
    }

    public function test_explicit_invoice_all_retains_detail_with_pilot_on(): void
    {
        $result = CrmReportPrivacyPilot::decide(true, false, true);
        self::assertTrue($result['customer_product_details']);
        self::assertTrue($result['invoice_csv']);
    }

    public function test_csv_only_pilot_does_not_pretend_to_hide_html_details(): void
    {
        $result = CrmReportPrivacyPilot::decide(false, true, false);
        self::assertTrue($result['customer_product_details']);
        self::assertFalse($result['invoice_csv']);
    }
}
