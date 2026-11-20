<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CrmUatExecutionLedger as Ledger;
use PHPUnit\Framework\TestCase;

/** Pure manual-register validation. No CRM database, no HTTP, no writes. */
final class CrmUatExecutionLedgerTest extends TestCase
{
    public function test_template_has_all_fifteen_unexecuted_cases_and_holds(): void
    {
        $template = Ledger::template();
        self::assertCount(15, $template['scenarios']);
        self::assertSame('HOLD', $template['policies']['announcement_public_assets']['status']);
        self::assertSame('HOLD', $template['policies']['reports_privacy_pilot']['status']);
        $outcome = Ledger::evaluate($template);
        self::assertSame('UAT_NOT_EXECUTED', $outcome['decision']);
        self::assertSame(15, $outcome['not_run']);
        self::assertFalse($outcome['production_release_approved']);
    }

    public function test_manual_pass_needs_real_evidence_and_confirmed_disposable_database(): void
    {
        $template = Ledger::template();
        $template['scenarios']['UAT-01']['status'] = 'PASS';
        $this->expectException(\InvalidArgumentException::class);
        Ledger::evaluate($template);
    }

    public function test_policy_hold_cannot_be_silently_reclassified_as_pass(): void
    {
        $template = Ledger::template();
        $template['scenarios']['UAT-13']['status'] = 'PASS';
        $template['scenarios']['UAT-13']['role'] = 'Administrator';
        $template['scenarios']['UAT-13']['observed_result'] = 'Confirmed';
        $template['scenarios']['UAT-13']['evidence_reference'] = 'EVIDENCE-13';
        $this->expectException(\InvalidArgumentException::class);
        Ledger::evaluate($template);
    }

    public function test_failure_and_blocked_are_never_auto_approved(): void
    {
        $template = Ledger::template();
        $template['scenarios']['UAT-03']['status'] = 'BLOCKED';
        $template['scenarios']['UAT-03']['observed_result'] = 'Pending fixture';
        self::assertSame('UAT_BLOCKED', Ledger::evaluate($template)['decision']);
        $template['scenarios']['UAT-03'] = [
            'status'=>'FAIL','role'=>'Sales','expected_result'=>'Customer-to-Lead CRUD',
            'observed_result'=>'Validation failure','evidence_reference'=>'', 'defect_reference'=>'DEFECT-3',
        ];
        $template['environment'] = 'LOCAL_DISPOSABLE';
        $template['dataset_reference'] = 'A79-fixture';
        $template['backup_reference'] = 'snapshot-001';
        $outcome = Ledger::evaluate($template);
        self::assertSame('UAT_FAILED_NEEDS_RETEST', $outcome['decision']);
        self::assertFalse($outcome['production_release_approved']);
    }

    public function test_all_manual_passes_still_require_external_release_signoff(): void
    {
        $template = Ledger::template();
        $template['environment'] = 'STAGING_DISPOSABLE';
        $template['dataset_reference'] = 'A79-fixture';
        $template['backup_reference'] = 'snapshot-001';
        foreach ($template['scenarios'] as $id => &$row) {
            $row['status'] = 'PASS';
            $row['role'] = 'UAT tester';
            $row['observed_result'] = 'Expected condition observed';
            $row['evidence_reference'] = 'EVIDENCE-'.$id;
        }
        unset($row);
        foreach ($template['policies'] as &$decision) {
            $decision['status'] = 'APPROVAL_RECORDED';
            $decision['approval_reference'] = 'POLICY-APPROVAL-RECORD';
        }
        unset($decision);
        $outcome = Ledger::evaluate($template);
        self::assertSame(15, $outcome['passed']);
        self::assertSame(0, $outcome['policy_holds']);
        self::assertSame('READY_FOR_EXTERNAL_SIGNOFF_NOT_RELEASE_APPROVED', $outcome['decision']);
        self::assertFalse($outcome['production_release_approved']);
    }
}
