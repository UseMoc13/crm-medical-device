<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

/**
 * A7.6.1: read-only KPI source contract and readiness inspection.
 * Does not execute business aggregates or expose record values.
 * Source observations are evidence for review, not proof of data exposure.
 */
final class CrmKpiSourceReadiness
{
    /** @var array<string, array{label:string,table:string,columns:list<string>,kind:string,semantics:string,permission:string}> */
    public const METRICS = [
        'customers.total' => [
            'label' => 'Total Customers', 'table' => 'customers', 'columns' => ['customer_id'],
            'kind' => 'snapshot', 'semantics' => 'Count current customers visible to the viewer.', 'permission' => 'crm_page.customers.view',
        ],
        'leads.active' => [
            'label' => 'Active Leads', 'table' => 'leads', 'columns' => ['lead_id', 'status'],
            'kind' => 'snapshot', 'semantics' => 'Count leads not in closed, converted or lost; define NULL and case handling.', 'permission' => 'crm_page.leads.view',
        ],
        'opportunities.total' => [
            'label' => 'Total Opportunities', 'table' => 'opportunities', 'columns' => ['opportunity_id'],
            'kind' => 'snapshot', 'semantics' => 'Count all visible opportunities; not an active-only metric.', 'permission' => 'crm_page.opportunities.view',
        ],
        'service_tickets.total' => [
            'label' => 'Service Tickets', 'table' => 'service_tickets', 'columns' => ['service_ticket_id'],
            'kind' => 'snapshot', 'semantics' => 'Count tickets visible to the viewer; no automatic date filter.', 'permission' => 'crm_page.service_tickets.view',
        ],
        'deliveries.in_transit' => [
            'label' => 'Deliveries in Transit', 'table' => 'deliveries', 'columns' => ['delivery_id', 'status'],
            'kind' => 'snapshot', 'semantics' => 'Count status in_transit; distinguish historical exceptions.', 'permission' => 'crm_page.deliveries.view',
        ],
        'installations.in_progress' => [
            'label' => 'Installations in Progress', 'table' => 'installations', 'columns' => ['installation_id', 'status'],
            'kind' => 'snapshot', 'semantics' => 'Count In Progress; keep legacy-case status flagged rather than rewriting.', 'permission' => 'crm_page.installations.view',
        ],
        'inventory.below_minimum' => [
            'label' => 'Inventory Below Minimum', 'table' => 'inventories', 'columns' => ['inventory_id', 'quantity', 'minimum_stock'],
            'kind' => 'snapshot', 'semantics' => 'Count product/warehouse rows where quantity < minimum_stock; clarify NULL minimum.', 'permission' => 'crm_page.inventories.view',
        ],
        'invoices.invoiced' => [
            'label' => 'Total Invoiced', 'table' => 'invoices', 'columns' => ['invoice_id', 'invoice_date', 'status', 'total_amount'],
            'kind' => 'period', 'semantics' => 'SUM(total_amount) of Sent and Paid by invoice_date.', 'permission' => 'crm_page.invoices.view',
        ],
        'invoices.paid' => [
            'label' => 'Paid Invoice Amount', 'table' => 'invoices', 'columns' => ['invoice_id', 'invoice_date', 'status', 'total_amount'],
            'kind' => 'period', 'semantics' => 'SUM(total_amount) of Paid by invoice_date; not payment/receipt date.', 'permission' => 'crm_page.invoices.view',
        ],
        'invoices.outstanding' => [
            'label' => 'Outstanding Invoice Amount', 'table' => 'invoices', 'columns' => ['invoice_id', 'invoice_date', 'status', 'total_amount'],
            'kind' => 'period', 'semantics' => 'SUM(total_amount) of Sent by invoice_date.', 'permission' => 'crm_page.invoices.view',
        ],
        'invoices.overdue' => [
            'label' => 'Overdue Invoice Amount', 'table' => 'invoices', 'columns' => ['invoice_id', 'invoice_date', 'due_date', 'status', 'total_amount'],
            'kind' => 'period', 'semantics' => 'SUM of Sent invoices with due_date before today; filtered by invoice_date.', 'permission' => 'crm_page.invoices.view',
        ],
        'warranties.expiring_30_days' => [
            'label' => 'Warranties Expiring in 30 Days', 'table' => 'warranties', 'columns' => ['warranty_id', 'status', 'end_date'],
            'kind' => 'snapshot', 'semantics' => 'Active warranty end_date between today and today+30 days, inclusive.', 'permission' => 'crm_page.warranties.view',
        ],
    ];

    /** Source-level review indicators based on actual files, not database records. */
    public const SOURCE_REVIEWS = [
        'dashboard.global_counts' => [
            'file' => 'app/Http/Controllers/DashboardController.php',
            'needle' => "private function countTable(string \$table)",
            'severity' => 'high',
            'action' => 'Apply page permission and row scope before rendering dashboard metrics.',
        ],
        'dashboard.activity_preview' => [
            'file' => 'app/Http/Controllers/DashboardController.php',
            'needle' => 'DB::' . "table('activities')",
            'severity' => 'high',
            'action' => 'Apply Activity viewer permission and ownership/collaboration scope before showing recent activity text.',
        ],
        'dashboard.placeholder_sales' => [
            'file' => 'resources/views/dashboard/index.blade.php',
            'needle' => 'Sales data will appear here.',
            'severity' => 'low',
            'action' => 'Replace Sales placeholder only after permission-scoped KPI queries are implemented.',
        ],
        'reports.controller_filename_case' => [
            'file' => 'app/Http/Controllers/Reportcontroller.php',
            'needle' => 'class ReportController',
            'severity' => 'high',
            'action' => 'Check exact ReportController.php casing for Linux PSR-4 autoload; rename using case-safe Git steps if needed.',
        ],
        'dashboard.announcement_publish_in_view' => [
            'file' => 'resources/views/dashboard/index.blade.php',
            'needle' => 'Announcement::publishDueScheduled();',
            'severity' => 'medium',
            'action' => 'Review scheduled publishing side effect in read-only dashboard rendering.',
        ],
    ];

    public function run(): array
    {
        $checks = [];
        $tables = [];
        $columns = [];
        foreach (self::METRICS as $id => $metric) {
            $missing = [];
            try {
                $table = $metric['table'];
                if (!array_key_exists($table, $tables)) {
                    $tables[$table] = Schema::hasTable($table);
                }
                if (!$tables[$table]) {
                    $missing[] = $table;
                } else {
                    foreach ($metric['columns'] as $col) {
                        $key = $table . '.' . $col;
                        if (!array_key_exists($key, $columns)) {
                            $columns[$key] = Schema::hasColumn($table, $col);
                        }
                        if (!$columns[$key]) $missing[] = $key;
                    }
                }
                $checks[] = [
                    'id' => $id, 'label' => $metric['label'], 'kind' => $metric['kind'],
                    'permission' => $metric['permission'], 'status' => $missing ? 'missing_schema' : 'source_available',
                    'missing_schema' => $missing,
                ];
            } catch (\Throwable $exception) {
                // Never place SQL, connection details, or record data in public diagnostics.
                $checks[] = [
                    'id' => $id, 'label' => $metric['label'], 'kind' => $metric['kind'],
                    'permission' => $metric['permission'], 'status' => 'query_error',
                    'missing_schema' => [],
                ];
            }
        }

        $reviews = [];
        foreach (self::SOURCE_REVIEWS as $id => $review) {
            // On case-insensitive Windows, is_file('ReportController.php') may
            // succeed even when Git tracks 'Reportcontroller.php'. Inspect exact
            // directory entry to prevent a Linux-only autoload regression.
            if ($id === 'reports.controller_filename_case') {
                $names = is_dir(base_path('app/Http/Controllers'))
                    ? scandir(base_path('app/Http/Controllers')) : false;
                $status = $names === false ? 'source_unavailable'
                    : (in_array('ReportController.php', $names, true) ? 'not_detected'
                    : (in_array('Reportcontroller.php', $names, true) ? 'review' : 'source_unavailable'));
            } else {
                $path = base_path($review['file']);
                $content = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
                $status = $content === false ? 'source_unavailable'
                    : (str_contains($content, $review['needle']) ? 'review' : 'not_detected');
            }
            $reviews[] = [
                'id' => $id, 'status' => $status,
                'severity' => $review['severity'], 'action' => $review['action'],
            ];
        }

        $available = count(array_filter($checks, fn (array $x) => $x['status'] === 'source_available'));
        $missing = count(array_filter($checks, fn (array $x) => $x['status'] === 'missing_schema'));
        $errors = count(array_filter($checks, fn (array $x) => $x['status'] === 'query_error'));
        $risks = count(array_filter($reviews, fn (array $x) => $x['status'] === 'review'));
        $unknownSources = count(array_filter($reviews, fn (array $x) => $x['status'] === 'source_unavailable'));
        $status = $errors ? 'BLOCKED' : ($missing || $unknownSources ? 'INCOMPLETE' : ($risks ? 'WATCH' : 'SOURCE_READY'));

        return [
            'stage' => 'A7.6.1', 'read_only' => true, 'aggregate_values_included' => false,
            'status' => $status,
            'summary' => [
                'metric_sources' => count($checks), 'source_available' => $available,
                'missing_schema' => $missing, 'query_errors' => $errors,
                'source_reviews' => $risks, 'source_unavailable' => $unknownSources,
            ],
            'metrics' => $checks,
            'source_reviews' => $reviews,
            'note' => 'SOURCE_READY does not prove role security or KPI accuracy. No business aggregates or records were read.',
        ];
    }
}
