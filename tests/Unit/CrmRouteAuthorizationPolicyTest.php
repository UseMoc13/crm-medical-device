<?php

namespace Tests\Unit;

use App\Services\CrmRouteAuthorizationPolicy as Policy;
use PHPUnit\Framework\TestCase;

/** Pure catalogue tests: no DB, no Laravel bootstrap. */
final class CrmRouteAuthorizationPolicyTest extends TestCase
{
    public function test_module_and_page_view_required_together(): void
    {
        self::assertSame([
            'managed' => true, 'super_admin' => false,
            'permissions' => ['catalog.access', 'crm_page.products.view'],
        ], Policy::forRoute('products.index'));
    }

    public function test_catalog_write_requires_manage_and_page_create(): void
    {
        self::assertSame(['catalog.manage', 'crm_page.products.create'],
            Policy::forRoute('products.store')['permissions']);
    }

    public function test_nested_delivery_create_and_delete_are_distinct(): void
    {
        self::assertSame(['delivery.access', 'crm_page.deliveries.create'],
            Policy::forRoute('deliveries.items.store')['permissions']);
        self::assertSame(['delivery.access', 'crm_page.deliveries.delete'],
            Policy::forRoute('deliveries.items.destroy')['permissions']);
    }

    public function test_delivery_status_requires_third_workflow_permission(): void
    {
        self::assertSame([
            'delivery.access', 'crm_page.deliveries.update', 'crm_workflow.deliveries.status',
        ], Policy::forRoute('deliveries.change-status')['permissions']);
    }

    public function test_invoice_status_and_report_export_have_the_correct_actions(): void
    {
        self::assertSame([
            'billing.access', 'crm_page.invoices.update', 'crm_workflow.invoices.status',
        ], Policy::forRoute('invoices.status')['permissions']);
        self::assertSame(['reporting.access', 'crm_page.reports.view'],
            Policy::forRoute('reports.export.invoices')['permissions']);
    }

    public function test_role_and_user_management_are_super_admin_only(): void
    {
        foreach (['roles.index', 'roles.store', 'roles.update', 'users.index', 'users.destroy'] as $name) {
            self::assertSame(['managed' => true, 'super_admin' => true, 'permissions' => []],
                Policy::forRoute($name));
        }
    }

    public function test_workspace_login_and_dashboard_are_not_reclassified(): void
    {
        foreach ([null, '', 'login', 'login.attempt', 'logout', 'dashboard', 'notes.index', 'todos.index', 'announcements.index'] as $name) {
            self::assertSame(['managed' => false, 'super_admin' => false, 'permissions' => []],
                Policy::forRoute($name));
        }
    }

    public function test_known_prefix_without_catalogued_action_is_fail_closed(): void
    {
        self::assertSame(['managed' => true, 'super_admin' => false, 'permissions' => []],
            Policy::forRoute('products.'));
    }

    public function test_sales_order_nested_create_preserves_special_mapping(): void
    {
        self::assertSame(['sales_crm.access', 'crm_page.sales_orders.create'],
            Policy::forRoute('sales_orders.from_quotation')['permissions']);
    }
}
