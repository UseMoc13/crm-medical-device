<?php
declare(strict_types=1);

namespace App\Support;

/** A7.8.1: pure UAT inventory, no DB/HTTP/filesystem mutations. */
final class CrmIntegrationUatManifest
{
    /** @return array<string,list<array{0:string,1:string}>> */
    public static function routeGroups(): array
    {
        return [
            "Core & Access" => [
                ['dashboard', 'GET'],
                ['login', 'GET'],
                ['login.attempt', 'POST'],
                ['logout', 'POST'],
                ['roles.index', 'GET'],
                ['users.index', 'GET'],
            ],
            "Customer & Lead" => [
                ['customers.index', 'GET'],
                ['contacts.index', 'GET'],
                ['leads.index', 'GET'],
                ['opportunities.index', 'GET'],
            ],
            "Product & Inventory" => [
                ['products.index', 'GET'],
                ['warehouses.index', 'GET'],
                ['inventories.index', 'GET'],
                ['stock-movements.index', 'GET'],
                ['serial-numbers.index', 'GET'],
            ],
            "Sales CRM" => [
                ['quotations.index', 'GET'],
                ['quotations.change-status', 'POST'],
                ['sales_orders.index', 'GET'],
                ['sales_orders.from_quotation', 'POST'],
                ['sales-orders.export', 'GET'],
            ],
            "Delivery & Installation" => [
                ['deliveries.index', 'GET'],
                ['deliveries.change-status', 'PATCH'],
                ['deliveries.items.store', 'POST'],
                ['installations.index', 'GET'],
                ['installations.change-status', 'PATCH'],
                ['installations.warranties.activate', 'POST'],
            ],
            "Warranty & Service" => [
                ['warranties.index', 'GET'],
                ['service-tickets.index', 'GET'],
                ['service-tickets.change-status', 'PATCH'],
                ['service-tickets.parts.store', 'POST'],
                ['service-tickets.work-logs.store', 'POST'],
                ['maintenance.index', 'GET'],
                ['maintenance.change-status', 'PATCH'],
                ['maintenance.records.store', 'POST'],
                ['service-history.index', 'GET'],
            ],
            "Billing & Reporting" => [
                ['contracts.index', 'GET'],
                ['contracts.download', 'GET'],
                ['invoices.index', 'GET'],
                ['invoices.status', 'PATCH'],
                ['reports.index', 'GET'],
                ['reports.export.invoices', 'GET'],
            ],
            "Announcements" => [
                ['announcements.index', 'GET'],
                ['announcement-management.index', 'GET'],
                ['announcement-management.store', 'POST'],
            ],
        ];
    }

    /** @return array<string,list<string>> */
    public static function schemaGroups(): array
    {
        return [
            "Core & Access" => [
                'roles', 'users', 'user_roles', 'permissions',
                'role_permissions',
            ],
            "Customer & Lead" => [
                'customers', 'contacts', 'leads', 'opportunities',
            ],
            "Product & Inventory" => [
                'product_categories', 'brands', 'products', 'warehouses',
                'inventories', 'stock_movements', 'serial_numbers',
            ],
            "Sales CRM" => [
                'quotations', 'quotation_items', 'sales_orders', 'sales_order_items',
            ],
            "Delivery & Installation" => [
                'deliveries', 'delivery_items', 'installations', 'installation_checklists',
            ],
            "Warranty & Service" => [
                'warranties', 'service_tickets', 'maintenance_schedules', 'maintenance_records',
            ],
            "Billing & Reporting" => [
                'contracts', 'invoices', 'invoice_items',
            ],
            "Announcements" => [
                'announcements', 'announcement_attachments', 'announcement_media',
            ],
        ];
    }

    /** @return list<array{0:string,1:string,2:string}> */
    public static function relationshipHints(): array
    {
        return [
            ['opportunities', 'customer_id', 'customers'],
            ['quotations', 'opportunity_id', 'opportunities'],
            ['sales_orders', 'quotation_id', 'quotations'],
            ['deliveries', 'sales_order_id', 'sales_orders'],
            ['installations', 'delivery_id', 'deliveries'],
            ['warranties', 'serial_number_id', 'serial_numbers'],
            ['invoices', 'sales_order_id', 'sales_orders'],
        ];
    }
}
