<?php

namespace App\Services;

/** Role editor catalogue aligned with existing A6.1 route groups. */
final class CrmRoleAccessCatalog
{
    public const ACTIONS = ['view', 'create', 'update', 'delete'];
    public const PAGES = [
        'customers' => ['label' => 'Customers', 'group' => 'customer_lead', 'group_label' => 'Customer & Lead', 'base' => 'customer_lead.access', 'prefixes' => ['customers.']],
        'contacts' => ['label' => 'Contacts', 'group' => 'customer_lead', 'group_label' => 'Customer & Lead', 'base' => 'customer_lead.access', 'prefixes' => ['contacts.']],
        'leads' => ['label' => 'Leads', 'group' => 'customer_lead', 'group_label' => 'Customer & Lead', 'base' => 'customer_lead.access', 'prefixes' => ['leads.']],
        'activities' => ['label' => 'Activities', 'group' => 'customer_lead', 'group_label' => 'Customer & Lead', 'base' => 'customer_lead.access', 'prefixes' => ['activities.']],
        'opportunities' => ['label' => 'Opportunities', 'group' => 'sales_crm', 'group_label' => 'Sales CRM', 'base' => 'sales_crm.access', 'prefixes' => ['opportunities.']],
        'quotations' => ['label' => 'Quotations', 'group' => 'sales_crm', 'group_label' => 'Sales CRM', 'base' => 'sales_crm.access', 'prefixes' => ['quotations.']],
        'sales_orders' => ['label' => 'Sales Orders', 'group' => 'sales_crm', 'group_label' => 'Sales CRM', 'base' => 'sales_crm.access', 'prefixes' => ['sales-orders.', 'sales_orders.']],
        'contracts' => ['label' => 'Contracts', 'group' => 'sales_crm', 'group_label' => 'Sales CRM', 'base' => 'sales_crm.access', 'prefixes' => ['contracts.']],
        'products' => ['label' => 'Products', 'group' => 'catalog', 'group_label' => 'Product Catalog', 'base' => 'catalog.access', 'prefixes' => ['products.']],
        'product_categories' => ['label' => 'Product Categories', 'group' => 'catalog', 'group_label' => 'Product Catalog', 'base' => 'catalog.access', 'prefixes' => ['product-categories.']],
        'brands' => ['label' => 'Brands', 'group' => 'catalog', 'group_label' => 'Product Catalog', 'base' => 'catalog.access', 'prefixes' => ['brands.']],
        'warehouses' => ['label' => 'Warehouses', 'group' => 'inventory', 'group_label' => 'Product & Inventory', 'base' => 'inventory.access', 'prefixes' => ['warehouses.']],
        'inventories' => ['label' => 'Inventories', 'group' => 'inventory', 'group_label' => 'Product & Inventory', 'base' => 'inventory.access', 'prefixes' => ['inventories.']],
        'stock_movements' => ['label' => 'Stock Movements', 'group' => 'inventory', 'group_label' => 'Product & Inventory', 'base' => 'inventory.access', 'prefixes' => ['stock-movements.']],
        'serial_numbers' => ['label' => 'Serial Numbers', 'group' => 'inventory', 'group_label' => 'Product & Inventory', 'base' => 'inventory.access', 'prefixes' => ['serial-numbers.']],
        'deliveries' => ['label' => 'Deliveries', 'group' => 'delivery', 'group_label' => 'Delivery & Installation', 'base' => 'delivery.access', 'prefixes' => ['deliveries.']],
        'installations' => ['label' => 'Installations', 'group' => 'delivery', 'group_label' => 'Delivery & Installation', 'base' => 'installation.access', 'prefixes' => ['installations.']],
        'warranties' => ['label' => 'Warranties', 'group' => 'service', 'group_label' => 'Service & Maintenance', 'base' => 'service.access', 'prefixes' => ['warranties.']],
        'service_tickets' => ['label' => 'Service Tickets', 'group' => 'service', 'group_label' => 'Service & Maintenance', 'base' => 'service.access', 'prefixes' => ['service-tickets.']],
        'maintenance' => ['label' => 'Maintenance', 'group' => 'service', 'group_label' => 'Service & Maintenance', 'base' => 'service.access', 'prefixes' => ['maintenance.']],
        'service_history' => ['label' => 'Service History', 'group' => 'service', 'group_label' => 'Service & Maintenance', 'base' => 'service.access', 'prefixes' => ['service-history.']],
        'invoices' => ['label' => 'Invoices', 'group' => 'billing', 'group_label' => 'Billing', 'base' => 'billing.access', 'prefixes' => ['invoices.']],
        'reports' => ['label' => 'Reports', 'group' => 'reporting', 'group_label' => 'Reporting', 'base' => 'reporting.access', 'prefixes' => ['reports.']],
        'announcement_management' => ['label' => 'Announcement Management', 'group' => 'announcements', 'group_label' => 'Administration', 'base' => 'announcements.manage', 'prefixes' => ['announcement-management.']],
    ];

    public static function key(string $page, string $action): string
    {
        return 'crm_page.' . $page . '.' . $action;
    }

    public static function forRoute(string $name): ?string
    {
        foreach (self::PAGES as $page => $config) {
            foreach ($config['prefixes'] as $prefix) {
                if (! str_starts_with($name, $prefix)) continue;
                $method = substr($name, strlen($prefix));
                // Special nested POST endpoints must follow their actual CRUD intent,
                // rather than falling through to update. Keep this list explicit.
                $exactActions = [
                    'sales_orders.from_quotation' => 'create',
                    'opportunities.items.create' => 'create',
                    'opportunities.items.store' => 'create',
                    'opportunities.items.edit' => 'update',
                    'opportunities.items.update' => 'update',
                    'opportunities.items.destroy' => 'delete',
                    'stock_movements.correction.create' => 'create',
                    'stock_movements.correction.store' => 'create',
                    'deliveries.items.store' => 'create',
                    'deliveries.items.destroy' => 'delete',
                    'maintenance.records.create' => 'create',
                    'maintenance.records.store' => 'create',
                    'maintenance.records.edit' => 'update',
                    'maintenance.records.update' => 'update',
                    'maintenance.records.destroy' => 'delete',
                ];
                // Convert the route name to the canonical catalogue slug when
                // route aliases use dashes, e.g. sales-orders.*.
                $canonical = $page . '.' . $method;
                if (isset($exactActions[$canonical])) {
                    return self::key($page, $exactActions[$canonical]);
                }
                if ($method === '') return null;
                $read = ['index', 'show', 'items', 'download', 'export', 'export.invoices'];
                $create = ['create', 'store'];
                $delete = ['destroy'];
                // Explicit read-only groups always remain read-only.
                if (in_array($page, ['service_history', 'reports'], true)) return self::key($page, 'view');
                if (in_array($method, $read, true) || str_starts_with($method, 'export.')) $action = 'view';
                elseif (in_array($method, $create, true) || str_ends_with($method, '.create') || str_ends_with($method, '.store')) $action = 'create';
                elseif (in_array($method, $delete, true) || str_ends_with($method, '.destroy')) $action = 'delete';
                // Remaining named workflow endpoints require update permission as a minimum.
                else $action = 'update';
                return self::key($page, $action);
            }
        }
        return null;
    }

    /** A7.10.6: presets live in a single safe catalogue. */
    public static function template(string $name): array
    {
        return CrmRoleTemplateCatalog::pages($name);
    }

    public static function templateForRole(string $roleName): string
    {
        return CrmRoleTemplateCatalog::forRoleName($roleName);
    }

}
