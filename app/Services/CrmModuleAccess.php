<?php

namespace App\Services;

/**
 * A6.1 route guard mapping. No routes are rewritten. Add new route prefixes
 * here as soon as new CRM modules are introduced.
 * Unlisted routes retain A4 auth-only until route inventory is completed.
 */
final class CrmModuleAccess
{
    private const ROUTE_PREFIXES = [
        'customers.' => 'customer_lead.access',
        'contacts.' => 'customer_lead.access',
        'leads.' => 'customer_lead.access',
        'activities.' => 'customer_lead.access',
        'opportunities.' => 'sales_crm.access',
        'quotations.' => 'sales_crm.access',
        'sales-orders.' => 'sales_crm.access',
        'sales_orders.' => 'sales_crm.access',
        'contracts.' => 'sales_crm.access',
        'products.' => 'catalog.access',
        'product-categories.' => 'catalog.access',
        'brands.' => 'catalog.access',
        'warehouses.' => 'inventory.access',
        'inventories.' => 'inventory.access',
        'stock-movements.' => 'inventory.access',
        'serial-numbers.' => 'inventory.access',
        'deliveries.' => 'delivery.access',
        'installations.' => 'installation.access',
        'warranties.' => 'service.access',
        'service-tickets.' => 'service.access',
        'service-history.' => 'service.access',
        'maintenance.' => 'service.access',
        'invoices.' => 'billing.access',
        'reports.' => 'reporting.access',
        'announcement-management.' => 'announcements.manage',
    ];

    public static function permissionForRoute(string $name): ?string
    {
        foreach (self::ROUTE_PREFIXES as $prefix => $permission) {
            if (str_starts_with($name, $prefix)) {
                // Read-only catalogue for Sales, Technician and Delivery.
                if ($permission === 'catalog.access' && in_array(substr($name, strrpos($name, '.') + 1), ['create', 'store', 'edit', 'update', 'destroy'], true)) {
                    return 'catalog.manage';
                }
                return $permission;
            }
        }
        return null;
    }
}
