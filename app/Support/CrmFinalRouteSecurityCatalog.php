<?php

declare(strict_types=1);

namespace App\Support;

/**
 * A7.10.1/2: independent, explicit expectations for particularly sensitive
 * HTTP routes. These are tests of the existing RBAC mapping, NOT permissions.
 * No database access and no business-state writes.
 */
final class CrmFinalRouteSecurityCatalog
{
    /** @return array<string,array{method:string,permissions:list<string>,super_admin:bool,independent_admin:bool}> */
    public static function criticalRoutes(): array
    {
        $ordinary = static fn (string $method, array $permissions): array => [
            'method' => $method,
            'permissions' => $permissions,
            'super_admin' => false,
            'independent_admin' => false,
        ];
        $admin = static fn (string $method): array => [
            'method' => $method, 'permissions' => [], 'super_admin' => true, 'independent_admin' => false,
        ];
        $independent = static fn (string $method): array => [
            'method' => $method, 'permissions' => [], 'super_admin' => false, 'independent_admin' => true,
        ];
        return [
            'products.store' => $ordinary('POST', ['catalog.manage', 'crm_page.products.create']),
            'sales_orders.from_quotation' => $ordinary('POST', ['sales_crm.access', 'crm_page.sales_orders.create']),
            'quotations.change-status' => $ordinary('POST', ['sales_crm.access', 'crm_page.quotations.update']),
            'deliveries.items.store' => $ordinary('POST', ['delivery.access', 'crm_page.deliveries.create']),
            'deliveries.change-status' => $ordinary('PATCH', ['delivery.access', 'crm_page.deliveries.update', 'crm_workflow.deliveries.status']),
            'installations.checklists.update' => $ordinary('PATCH', ['installation.access', 'crm_page.installations.update', 'crm_workflow.installations.checklist']),
            'installations.warranties.activate' => $ordinary('POST', ['installation.access', 'crm_page.installations.update', 'crm_workflow.installations.activate_warranty']),
            'service-tickets.work-logs.store' => $ordinary('POST', ['service.access', 'crm_page.service_tickets.create']),
            'service-tickets.change-status' => $ordinary('PATCH', ['service.access', 'crm_page.service_tickets.update', 'crm_workflow.service_tickets.status']),
            'service-tickets.parts.store' => $ordinary('POST', ['service.access', 'crm_page.service_tickets.create', 'crm_workflow.service_tickets.parts_create']),
            'maintenance.records.store' => $ordinary('POST', ['service.access', 'crm_page.maintenance.create']),
            'maintenance.records.update' => $ordinary('PUT', ['service.access', 'crm_page.maintenance.update']),
            'maintenance.change-status' => $ordinary('PATCH', ['service.access', 'crm_page.maintenance.update', 'crm_workflow.maintenance.status']),
            'invoices.status' => $ordinary('PATCH', ['billing.access', 'crm_page.invoices.update', 'crm_workflow.invoices.status']),
            'reports.export.invoices' => $ordinary('GET', ['reporting.access', 'crm_page.reports.view']),
            'roles.store' => $admin('POST'),
            'users.update' => $admin('PUT'),
            'branches.update' => $admin('PUT'),
            'organization.users.manager' => $independent('PATCH'),
            'teams.store' => $independent('POST'),
        ];
    }
}
