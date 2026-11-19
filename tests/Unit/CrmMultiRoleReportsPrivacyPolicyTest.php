<?php

namespace Tests\Unit;

use App\Services\CrmMultiRoleReportsPrivacyAudit;
use PHPUnit\Framework\TestCase;

final class CrmMultiRoleReportsPrivacyPolicyTest extends TestCase
{
    /** @return list<array{permission_name:string,data_scope:?string}> */
    private function reportGrants(): array
    {
        return [
            ['permission_name'=>'reporting.access','data_scope'=>null],
            ['permission_name'=>'crm_page.reports.view','data_scope'=>null],
        ];
    }

    /** @return list<array{role_name:string,is_system:bool}> */
    private function roles(): array
    {
        return [
            ['role_name'=>'Finance','is_system'=>false],
            ['role_name'=>'Custom Billing','is_system'=>false],
        ];
    }

    public function test_report_grants_across_roles_do_not_implicitly_grant_invoice_kpis(): void
    {
        $actual = CrmMultiRoleReportsPrivacyAudit::assess($this->roles(), $this->reportGrants(), false, false);
        self::assertTrue($actual['reports_page']);
        self::assertTrue($actual['csv_export']);
        self::assertFalse($actual['dashboard_invoice_kpis']);
        self::assertSame('missing', $actual['invoice_scope']);
        self::assertContains('reports_without_invoice_kpi', $actual['review_flags']);
        self::assertContains('csv_invoice_customer_details', $actual['review_flags']);
        self::assertFalse($actual['per_user_access_verified']);
    }

    public function test_explicit_invoice_all_grant_from_second_role_qualifies_but_still_requires_privacy_review(): void
    {
        $grants = [...$this->reportGrants(),
            ['permission_name'=>'billing.access','data_scope'=>null],
            ['permission_name'=>'crm_page.invoices.view','data_scope'=>'all'],
        ];
        $actual = CrmMultiRoleReportsPrivacyAudit::assess($this->roles(), $grants, false, true);
        self::assertTrue($actual['reports_page']);
        self::assertTrue($actual['csv_export']);
        self::assertTrue($actual['dashboard_invoice_kpis']);
        self::assertSame('all', $actual['invoice_scope']);
        self::assertContains('reports_customer_product_labels', $actual['review_flags']);
        self::assertFalse($actual['numeric_parity_verified']);
    }

    public function test_partial_scope_and_unknown_scope_fail_closed_even_with_other_grants(): void
    {
        $base = [...$this->reportGrants(),
            ['permission_name'=>'billing.access','data_scope'=>null],
            ['permission_name'=>'crm_page.invoices.view','data_scope'=>'own'],
        ];
        $actual = CrmMultiRoleReportsPrivacyAudit::assess($this->roles(), $base, false, true);
        self::assertFalse($actual['csv_export']);
        self::assertFalse($actual['dashboard_invoice_kpis']);
        self::assertSame('restricted', $actual['invoice_scope']);
        $invalid = CrmMultiRoleReportsPrivacyAudit::assess($this->roles(), [...$base,
            ['permission_name'=>'crm_page.invoices.view','data_scope'=>'all'],
            ['permission_name'=>'crm_page.invoices.view','data_scope'=>'made-up'],
        ], false, true);
        self::assertFalse($invalid['csv_export']);
        self::assertSame('restricted', $invalid['invoice_scope']);
    }

    public function test_system_super_admin_only_and_downstream_scope_review(): void
    {
        $fake = CrmMultiRoleReportsPrivacyAudit::assess([
            ['role_name'=>'Super Admin','is_system'=>false],
        ], [], false, true);
        self::assertFalse($fake['super_admin_bypass']);
        self::assertFalse($fake['reports_page']);

        $real = CrmMultiRoleReportsPrivacyAudit::assess([
            ['role_name'=>'Finance','is_system'=>false],
            ['role_name'=>'Super Admin','is_system'=>true],
        ], [], true, true);
        self::assertTrue($real['super_admin_bypass']);
        self::assertTrue($real['csv_export']);
        self::assertContains('opt_in_report_row_scope', $real['review_flags']);
    }
}
