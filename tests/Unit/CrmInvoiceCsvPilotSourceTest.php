<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CrmInvoiceCsvPilotSourceTest extends TestCase
{
    public function test_default_off_guard_is_after_reports_gate_before_validation(): void
    {
        $root = dirname(__DIR__, 2);
        $config = file_get_contents($root.'/config/crm_reporting.php');
        $controller = file_get_contents($root.'/app/Http/Controllers/ReportController.php');
        self::assertStringContainsString("env('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', false)", $config);
        self::assertSame(2, substr_count($controller, '$this->assertReportsView();'));
        $start = strpos($controller, 'public function exportInvoices(Request $request): StreamedResponse');
        self::assertNotFalse($start);
        $csv = substr($controller, $start, 1000);
        $reports = strpos($csv, '$this->assertReportsView();');
        $pilot = strpos($csv, "config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)");
        $validate = strpos($csv, '$request->validate([');
        self::assertNotFalse($reports);
        self::assertNotFalse($pilot);
        self::assertNotFalse($validate);
        self::assertLessThan($pilot, $reports);
        self::assertLessThan($validate, $pilot);
        self::assertStringContainsString('CrmInvoiceCsvPolicy::class', $csv);
        self::assertStringContainsString("status IN ('Sent','Paid')", $controller);
        self::assertStringContainsString('CrmReportingDataScope::class', $controller);
    }

    public function test_role_audit_does_not_claim_csv_for_restricted_roles_when_enabled(): void
    {
        $root = dirname(__DIR__, 2);
        $service = file_get_contents($root.'/app/Services/CrmFinancialRoleEntitlementAudit.php');
        $command = file_get_contents($root.'/app/Console/Commands/AuditCrmFinancialRoleEntitlements.php');
        self::assertStringContainsString('reports_without_invoice_kpi', $service);
        self::assertStringContainsString('csv_pilot_enabled', $service);
        self::assertStringContainsString('($role[\'csv_export\'] ? \'YES\' : \'NO\')', $command);
        self::assertStringNotContainsString("' | Reports YES | CSV YES", $command);
    }
}
