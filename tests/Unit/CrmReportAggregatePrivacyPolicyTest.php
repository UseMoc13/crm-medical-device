<?php

namespace Tests\Unit;

use App\Services\CrmReportAggregatePrivacyPilot;
use App\Services\CrmScopedKpiQuery;
use PHPUnit\Framework\TestCase;

final class CrmReportAggregatePrivacyPolicyTest extends TestCase
{
    public function test_disabled_pilot_preserves_all_existing_report_groups(): void
    {
        $decision = CrmReportAggregatePrivacyPilot::decide(false, []);
        self::assertFalse($decision['enabled']);
        foreach (['financial', 'operations', 'pipeline', 'warranty'] as $group) {
            self::assertTrue($decision[$group]);
        }
    }

    public function test_enabled_pilot_denies_unspecified_groups_and_does_not_relabel_denial_as_zero(): void
    {
        $decision = CrmReportAggregatePrivacyPilot::decide(true, ['financial' => true]);
        self::assertTrue($decision['enabled']);
        self::assertTrue($decision['financial']);
        self::assertFalse($decision['operations']);
        self::assertFalse($decision['pipeline']);
        self::assertFalse($decision['warranty']);
        self::assertSame('restricted', $decision['pipeline'] ? 'visible' : 'restricted');
    }

    public function test_invoice_and_other_unsupported_partial_scopes_cannot_be_promoted_to_all(): void
    {
        foreach (['invoices','maintenance_schedules','warranties','service_tickets','opportunities'] as $table) {
            foreach ([[], ['own'], ['assigned'], ['branch'], ['unknown'], ['all','unknown']] as $grants) {
                self::assertNotSame('all', CrmScopedKpiQuery::scopeDecision($table, $grants));
            }
            self::assertSame('all', CrmScopedKpiQuery::scopeDecision($table, ['all']));
        }
    }
}
