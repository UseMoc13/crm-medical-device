<?php

namespace Tests\Unit;

use App\Services\CrmFinancialRoleEntitlementAudit;
use App\Services\CrmScopedKpiQuery;
use PHPUnit\Framework\TestCase;

final class CrmInvoiceCsvPilotPolicyTest extends TestCase
{
    private function grant(string $name, ?string $scope = null): array
    {
        return ['permission_name' => $name, 'data_scope' => $scope];
    }

    public function test_pilot_distinguishes_report_page_from_detailed_invoice_csv(): void
    {
        $report = [$this->grant('reporting.access'), $this->grant('crm_page.reports.view')];
        $legacy = CrmFinancialRoleEntitlementAudit::assessRole(false, $report, false);
        self::assertTrue($legacy['reports_page']);
        self::assertTrue($legacy['csv_export']);

        $pilot = CrmFinancialRoleEntitlementAudit::assessRole(false, $report, false, true);
        self::assertTrue($pilot['reports_page']);
        self::assertFalse($pilot['csv_export']);
        self::assertFalse($pilot['dashboard_invoice_kpis']);
        self::assertSame('reports_only_csv_restricted', $pilot['review_code']);
    }

    public function test_csv_requires_invoice_view_base_and_all_scope_in_pilot(): void
    {
        $r = [$this->grant('reporting.access'), $this->grant('crm_page.reports.view')];
        foreach ([null, 'own', 'branch', 'assigned', 'unknown'] as $scope) {
            $grants = [...$r, $this->grant('billing.access'), $this->grant('crm_page.invoices.view', $scope)];
            self::assertFalse(CrmFinancialRoleEntitlementAudit::assessRole(false, $grants, false, true)['csv_export']);
        }
        $all = [...$r, $this->grant('billing.access'), $this->grant('crm_page.invoices.view', 'all')];
        self::assertTrue(CrmFinancialRoleEntitlementAudit::assessRole(false, $all, false, true)['csv_export']);
        self::assertSame('all', CrmScopedKpiQuery::scopeDecision('invoices', ['own', 'all']));
        self::assertSame('restricted', CrmScopedKpiQuery::scopeDecision('invoices', ['all', 'unknown']));
        self::assertFalse(CrmFinancialRoleEntitlementAudit::assessRole(false, [
            ...$r, $this->grant('crm_page.invoices.view', 'all'),
        ], false, true)['csv_export']);
    }

    public function test_super_admin_bypass_and_downstream_review_remain_explicit(): void
    {
        $super = CrmFinancialRoleEntitlementAudit::assessRole(true, [], true, true);
        self::assertTrue($super['csv_export']);
        self::assertTrue($super['dashboard_invoice_kpis']);
        self::assertSame('super_admin_bypass', $super['review_code']);
        $all = [$this->grant('reporting.access'), $this->grant('crm_page.reports.view'),
            $this->grant('billing.access'), $this->grant('crm_page.invoices.view', 'all')];
        $scoped = CrmFinancialRoleEntitlementAudit::assessRole(false, $all, true, true);
        self::assertTrue($scoped['csv_export']);
        self::assertSame('downstream_sales_scope_may_differ', $scoped['review_code']);
    }
}
