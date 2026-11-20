<?php
/** A7.8.3: enumerates evidence, NOT UAT approvals. No DB or HTTP actions. */
declare(strict_types=1);
namespace App\Support;

final class CrmUatWorkflowCoverage
{
    /** @return array<string,array{name:string,gate:string,routes:list<array{0:string,1:string}>,evidence:list<string>,note:string}> */
    public static function scenarios(): array
    {
        return [
            'UAT-01' => [
                'name' => 'Auth/RBAC guest boundaries',
                'gate' => 'NORMAL',
                'routes' => [['customers.index', 'GET'], ['reports.index', 'GET'], ['reports.export.invoices', 'GET']],
                'evidence' => ['tests/Feature/CrmSecurityResponseBoundaryHttpTest.php'],
                'note' => 'Guest redirect/401 and cache privacy; manual acceptance pending.',
            ],
            'UAT-02' => [
                'name' => 'RBAC direct write denial',
                'gate' => 'NORMAL',
                'routes' => [['products.store', 'POST'], ['deliveries.change-status', 'PATCH']],
                'evidence' => ['tests/Feature/CrmRouteAuthorizationHttpTest.php'],
                'note' => 'Read-only role must not mutate business data.',
            ],
            'UAT-03' => [
                'name' => 'Customer-to-Lead CRUD',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['customers.store', 'POST'], ['contacts.store', 'POST'], ['leads.store', 'POST'], ['opportunities.store', 'POST']],
                'evidence' => ['tests/Feature/CrmScopedKpiIsolatedTest.php'],
                'note' => 'Actual linked CRUD and invalid-ID validation require staging UAT.',
            ],
            'UAT-04' => [
                'name' => 'Quotation-to-Sales Order',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['quotations.change-status', 'POST'], ['sales_orders.from_quotation', 'POST']],
                'evidence' => ['tests/Unit/CrmIntegrationUatManifestTest.php'],
                'note' => 'Approval, conversion, duplicate rejection require staging UAT.',
            ],
            'UAT-05' => [
                'name' => 'Inventory and stock ledger',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['stock-movements.store', 'POST'], ['inventories.index', 'GET']],
                'evidence' => ['tests/Unit/CrmDeliveryCompletionGuardTest.php'],
                'note' => 'End-to-end quantity, correction and stock underflow require staging UAT.',
            ],
            'UAT-06' => [
                'name' => 'Delivery dispatch and completion',
                'gate' => 'NORMAL',
                'routes' => [['deliveries.change-status', 'PATCH'], ['deliveries.items.store', 'POST']],
                'evidence' => ['tests/Feature/CrmDeliveryDispatchPostconditionIsolationTest.php'],
                'note' => 'Isolated stock postcondition is not full HTTP workflow UAT.',
            ],
            'UAT-07' => [
                'name' => 'Installation and checklist',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['installations.change-status', 'PATCH'], ['installations.checklists.update', 'PATCH']],
                'evidence' => ['tests/Unit/CrmIntegrationUatManifestTest.php'],
                'note' => 'Full installation and checklist state transitions need staging UAT.',
            ],
            'UAT-08' => [
                'name' => 'Warranty activation',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['installations.warranties.activate', 'POST'], ['warranties.index', 'GET']],
                'evidence' => ['tests/Unit/CrmIntegrationUatManifestTest.php'],
                'note' => 'Duplicate activation and serial linkage need staging UAT.',
            ],
            'UAT-09' => [
                'name' => 'Service ticket lifecycle',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['service-tickets.change-status', 'PATCH'], ['service-tickets.work-logs.store', 'POST']],
                'evidence' => ['tests/Feature/CrmStatusNotificationPrivacyTest.php'],
                'note' => 'Ticket/parts/work logs require business-state UAT.',
            ],
            'UAT-10' => [
                'name' => 'Maintenance lifecycle',
                'gate' => 'MANUAL_REQUIRED',
                'routes' => [['maintenance.change-status', 'PATCH'], ['maintenance.records.store', 'POST']],
                'evidence' => ['tests/Unit/CrmIntegrationUatManifestTest.php'],
                'note' => 'Actual maintenance scheduling and records require staging UAT.',
            ],
            'UAT-11' => [
                'name' => 'Invoice and financial reports',
                'gate' => 'NORMAL',
                'routes' => [['invoices.status', 'PATCH'], ['reports.export.invoices', 'GET']],
                'evidence' => ['tests/Feature/CrmStagingInvoiceParitySqliteTest.php', 'tests/Unit/CrmCsvExportSourceTest.php'],
                'note' => 'Report privacy pilot approval remains separate release gate.',
            ],
            'UAT-12' => [
                'name' => 'Private contract files',
                'gate' => 'NORMAL',
                'routes' => [['contracts.download', 'GET']],
                'evidence' => ['tests/Unit/CrmPrivateFilePathTest.php', 'tests/Unit/CrmPrivateFileAccessSourceTest.php'],
                'note' => 'Role-specific authorized and denied real downloads still need staging UAT.',
            ],
            'UAT-13' => [
                'name' => 'Announcement direct public assets',
                'gate' => 'HOLD_POLICY',
                'routes' => [['announcement-management.store', 'POST'], ['announcements.show', 'GET']],
                'evidence' => ['tests/Unit/CrmAnnouncementExposurePolicyTest.php', 'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php'],
                'note' => 'HOLD: decide and implement visibility policy for public-disk attachments.',
            ],
            'UAT-14' => [
                'name' => 'Multi-role dashboard/report privacy',
                'gate' => 'HOLD_POLICY',
                'routes' => [['dashboard', 'GET'], ['reports.index', 'GET']],
                'evidence' => ['tests/Feature/CrmDashboardViewerIsolationTest.php', 'tests/Feature/CrmReportAggregatePrivacyIsolationTest.php'],
                'note' => 'HOLD: opt-in report financial privacy requires owner/stakeholder decision.',
            ],
            'UAT-15' => [
                'name' => 'Pagination and large CSV export',
                'gate' => 'NORMAL',
                'routes' => [['sales_orders.index', 'GET'], ['sales-orders.export', 'GET']],
                'evidence' => ['tests/Feature/CrmSalesOrderCsvMultiBatchReliabilityTest.php', 'tests/Feature/CrmSalesOrderPaginationSqliteIsolationTest.php'],
                'note' => 'Isolated fixtures pass; staging dataset and concurrent export behavior remain unverified.',
            ],
        ];
    }

    /** Aggregates route/fixture coverage without ever claiming UAT PASS. */
    public static function decision(int $routeGaps, int $evidenceGaps): string
    {
        if ($routeGaps < 0 || $evidenceGaps < 0) {
            throw new \InvalidArgumentException('Counts must not be negative.');
        }
        if ($routeGaps > 0 || $evidenceGaps > 0) return 'HOLD_PREFLIGHT_GAPS';
        return 'WORKFLOW_BASELINE_READY_UAT_NOT_EXECUTED';
    }
}
