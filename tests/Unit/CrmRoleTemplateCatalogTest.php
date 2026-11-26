<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CrmRoleAccessCatalog as Pages;
use App\Services\CrmRoleTemplateCatalog as Templates;
use App\Services\CrmWorkflowAccess as Workflows;
use PHPUnit\Framework\TestCase;

/** Pure template checks, no migrations / database / HTTP. */
final class CrmRoleTemplateCatalogTest extends TestCase
{
    public function test_presets_include_legacy_ids_and_new_operational_roles(): void
    {
        foreach (['admin','sales','warehouse','delivery_staff','technician','finance','management',
            'customer_service','sales_support','sales_manager','lead_specialist','catalog_manager',
            'warehouse_operator','inventory_controller','logistics_coordinator','installation_coordinator',
            'service_coordinator','warranty_coordinator','billing_clerk','finance_manager',
            'qa_auditor','crm_readonly'] as $slug) {
            self::assertContains($slug, Templates::keys());
            self::assertNotEmpty(Templates::labels()[$slug]);
            self::assertNotEmpty(Templates::descriptions()[$slug]);
        }
    }

    public function test_all_page_grants_are_catalogued_unique_and_depend_on_view(): void
    {
        foreach (Templates::allPages() as $preset => $pages) {
            self::assertNotEmpty($pages, $preset);
            foreach ($pages as $page => $actions) {
                self::assertArrayHasKey($page, Pages::PAGES, $preset.' '.$page);
                self::assertSame(array_values(array_unique($actions)), $actions);
                self::assertContains('view', $actions, $preset.' '.$page);
                foreach ($actions as $action) self::assertContains($action, Pages::ACTIONS);
            }
        }
    }

    public function test_workflows_are_explicit_catalogued_and_supported_by_page_actions(): void
    {
        foreach (Templates::keys() as $preset) {
            $pageAccess = Templates::pages($preset);
            $allowed = Workflows::permittedByPages($pageAccess);
            self::assertSame(array_values(array_unique(Templates::workflows($preset))), Templates::workflows($preset));
            foreach (Templates::workflows($preset) as $workflow) {
                self::assertArrayHasKey($workflow, Workflows::ACTIONS, $preset.' '.$workflow);
                self::assertContains($workflow, $allowed, $preset.' '.$workflow);
            }
        }
    }

    public function test_billing_clerk_has_no_payment_status_and_warehouse_operator_cannot_delete(): void
    {
        self::assertSame(['view','create','update'], Templates::pages('billing_clerk')['invoices']);
        self::assertNotContains('invoices.status', Templates::workflows('billing_clerk'));
        self::assertNotContains('delete', Templates::pages('warehouse_operator')['stock_movements']);
        self::assertNotContains('delete', Templates::pages('customer_service')['service_tickets']);
    }

    public function test_readonly_and_auditor_never_have_writes_or_workflow_permissions(): void
    {
        foreach (['management','qa_auditor','crm_readonly'] as $key) {
            foreach (Templates::pages($key) as $actions) self::assertSame(['view'], $actions);
            self::assertSame([], Templates::workflows($key));
        }
        self::assertArrayNotHasKey('reports', Templates::pages('crm_readonly'));
        self::assertArrayNotHasKey('invoices', Templates::pages('crm_readonly'));
        self::assertArrayNotHasKey('announcement_management', Templates::pages('crm_readonly'));
    }

    public function test_legacy_admin_preset_does_not_claim_super_admin_rights(): void
    {
        self::assertSame(['announcement_management'=>['view','create','update','delete']], Templates::pages('admin'));
        self::assertSame([], Templates::workflows('admin'));
    }

    public function test_role_name_inference_is_exact_and_unknown_presets_fail_closed(): void
    {
        self::assertSame('sales', Templates::forRoleName('Sales Executive'));
        self::assertSame('customer_service', Templates::forRoleName('Customer Service'));
        self::assertSame('technician', Templates::forRoleName('Technician'));
        self::assertSame('', Templates::forRoleName('UAT Customer Service'));
        self::assertSame('', Templates::forRoleName('Super Admin'));
        self::assertSame([], Templates::pages('unrecognized'));
        self::assertSame([], Templates::workflows('unrecognized'));
    }
}
