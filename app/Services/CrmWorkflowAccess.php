<?php

namespace App\Services;

/**
 * A6.8: specifically inventoried named CRM workflow routes.
 * These action grants are required IN ADDITION to the A6.1 module and A6.2
 * page-action grants. Unknown routes are not silently classified here.
 */
final class CrmWorkflowAccess
{
    public const ACTIONS = [
        'deliveries.status' => [
            'label' => 'Change Delivery Status', 'group' => 'Delivery & Installation',
            'page' => 'deliveries', 'action' => 'update',
            'routes' => ['deliveries.change-status'],
        ],
        'installations.status' => [
            'label' => 'Change Installation Status', 'group' => 'Delivery & Installation',
            'page' => 'installations', 'action' => 'update',
            'routes' => ['installations.change-status'],
        ],
        'installations.checklist' => [
            'label' => 'Update Installation Checklist', 'group' => 'Delivery & Installation',
            'page' => 'installations', 'action' => 'update',
            'routes' => ['installations.checklists.update'],
        ],
        'installations.activate_warranty' => [
            'label' => 'Activate Warranty from Installation', 'group' => 'Delivery & Installation',
            'page' => 'installations', 'action' => 'update',
            'routes' => ['installations.warranties.activate'],
        ],
        'service_tickets.status' => [
            'label' => 'Change Service Ticket Status', 'group' => 'Service & Maintenance',
            'page' => 'service_tickets', 'action' => 'update',
            'routes' => ['service-tickets.change-status'],
        ],
        'service_tickets.parts_create' => [
            'label' => 'Add Service Ticket Parts', 'group' => 'Service & Maintenance',
            'page' => 'service_tickets', 'action' => 'create',
            'routes' => ['service-tickets.parts.store'],
        ],
        'service_tickets.parts_update' => [
            'label' => 'Edit Service Ticket Parts', 'group' => 'Service & Maintenance',
            'page' => 'service_tickets', 'action' => 'update',
            'routes' => ['service-tickets.parts.update'],
        ],
        'service_tickets.parts_delete' => [
            'label' => 'Remove Service Ticket Parts', 'group' => 'Service & Maintenance',
            'page' => 'service_tickets', 'action' => 'delete',
            'routes' => ['service-tickets.parts.destroy'],
        ],
        'maintenance.status' => [
            'label' => 'Change Maintenance Status', 'group' => 'Service & Maintenance',
            'page' => 'maintenance', 'action' => 'update',
            'routes' => ['maintenance.change-status'],
        ],
        'invoices.status' => [
            'label' => 'Change Invoice Status', 'group' => 'Billing',
            'page' => 'invoices', 'action' => 'update',
            'routes' => ['invoices.status'],
        ],
    ];

    public static function key(string $slug): string
    {
        return 'crm_workflow.' . $slug;
    }

    public static function forRoute(string $routeName): ?string
    {
        foreach (self::ACTIONS as $slug => $action) {
            if (in_array($routeName, $action['routes'], true)) {
                return self::key($slug);
            }
        }
        return null;
    }

    /** @return array<int, string> */
    public static function permittedByPages(array $pageAccess): array
    {
        $allowed = [];
        foreach (self::ACTIONS as $slug => $action) {
            if (in_array($action['action'], $pageAccess[$action['page']] ?? [], true)) {
                $allowed[] = $slug;
            }
        }
        return $allowed;
    }
}
