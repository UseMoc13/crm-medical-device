<?php

namespace Tests\Unit;

use App\Services\CrmFinancialRoleEntitlementAudit;
use PHPUnit\Framework\TestCase;

final class CrmFinancialRoleEntitlementPolicyTest extends TestCase
{
    private function grant(string $name, ?string $scope = null): array
    {
        return ['permission_name' => $name, 'data_scope' => $scope];
    }

    public function test_reports_only_role_can_export_but_has_no_dashboard_invoice_kpi(): void
    {
        $decision = CrmFinancialRoleEntitlementAudit::assessRole(false, [
            $this->grant('reporting.access'), $this->grant('crm_page.reports.view'),
        ], false);
        self::assertTrue($decision['reports_page']);
        self::assertTrue($decision['csv_export']);
        self::assertFalse($decision['dashboard_invoice_kpis']);
        self::assertSame('missing', $decision['invoice_scope']);
        self::assertSame('reports_csv_without_invoice_kpi', $decision['review_code']);
    }

    public function test_invoice_all_grant_is_required_for_kpi_parity_candidate(): void
    {
        $base = [$this->grant('reporting.access'), $this->grant('crm_page.reports.view'),
            $this->grant('billing.access')];
        foreach ([null, 'own', 'assigned', 'branch', 'unsupported'] as $scope) {
            $grants = [...$base, $this->grant('crm_page.invoices.view', $scope)];
            $decision = CrmFinancialRoleEntitlementAudit::assessRole(false, $grants, false);
            self::assertFalse($decision['dashboard_invoice_kpis']);
            self::assertSame('reports_csv_without_invoice_kpi', $decision['review_code']);
        }
        $all = [...$base, $this->grant('crm_page.invoices.view', 'all')];
        $eligible = CrmFinancialRoleEntitlementAudit::assessRole(false, $all, false);
        self::assertTrue($eligible['dashboard_invoice_kpis']);
        self::assertSame('eligible_for_numeric_comparison', $eligible['review_code']);
        self::assertSame('downstream_sales_scope_may_differ',
            CrmFinancialRoleEntitlementAudit::assessRole(false, $all, true)['review_code']);
    }

    public function test_only_actual_system_super_admin_bypasses_grants(): void
    {
        $empty = CrmFinancialRoleEntitlementAudit::assessRole(false, [], false);
        self::assertFalse($empty['reports_page']);
        self::assertFalse($empty['dashboard_invoice_kpis']);
        $admin = CrmFinancialRoleEntitlementAudit::assessRole(true, [], true);
        self::assertTrue($admin['reports_page']);
        self::assertTrue($admin['dashboard_invoice_kpis']);
        self::assertSame('super_admin_bypass', $admin['review_code']);
    }
}
