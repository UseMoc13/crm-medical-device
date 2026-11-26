<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CrmViewAction;
use PHPUnit\Framework\TestCase;

/** Pure route catalogue checks; no Laravel bootstrap, DB, sessions or writes. */
final class CrmViewActionRouteGuardTest extends TestCase
{
    public function test_crm_crud_and_workflow_routes_are_covered(): void
    {
        foreach ([
            'customers.index', 'products.create', 'products.edit', 'products.destroy',
            'deliveries.change-status', 'installations.checklists.update',
            'service-tickets.work-logs.store', 'maintenance.records.destroy',
            'invoices.status', 'reports.export.invoices',
        ] as $route) {
            self::assertTrue(CrmViewAction::guarded($route), $route);
        }
    }

    public function test_protected_admin_links_are_covered(): void
    {
        foreach (['roles.index', 'users.create', 'branches.destroy', 'teams.store', 'organization.index'] as $route) {
            self::assertTrue(CrmViewAction::guarded($route), $route);
        }
    }

    public function test_unknown_and_independent_workspace_routes_not_falsely_granted(): void
    {
        foreach (['', 'dashboard', 'login', 'todos.update', 'notes.destroy', 'calendar.store', 'unknown.delete'] as $route) {
            self::assertFalse(CrmViewAction::guarded($route), $route);
        }
    }
}
