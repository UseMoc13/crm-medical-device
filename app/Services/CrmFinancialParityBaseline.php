<?php

namespace App\Services;

/**
 * A7.6.5.2 — Source-contract only. Reads PHP source, not business tables.
 * Consistent formula definitions DO NOT prove identical row visibility or amounts.
 */
final class CrmFinancialParityBaseline
{
    private const SOURCES = [
        'reports' => 'app/Http/Controllers/ReportController.php',
        'dashboard_kpis' => 'app/Services/CrmScopedKpiQuery.php',
        'report_scope' => 'app/Services/CrmReportingDataScope.php',
        'catalog' => 'app/Services/CrmKpiSourceReadiness.php',
    ];

    public function run(): array
    {
        $sources = [];
        foreach (self::SOURCES as $key => $file) {
            $path = base_path($file);
            $sources[$key] = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        }
        $r = $sources['reports'];
        $k = $sources['dashboard_kpis'];
        $s = $sources['report_scope'];
        $c = $sources['catalog'];
        $has = static fn ($src, string $text): bool => is_string($src) && str_contains($src, $text);
        $checks = [
            'reports_source_available' => is_string($r),
            'scoped_kpi_source_available' => is_string($k),
            'report_scope_source_available' => is_string($s),
            'catalog_source_available' => is_string($c),
            'reports_page_and_csv_both_guarded' => is_string($r) && substr_count($r, '$this->assertReportsView();') === 2,
            'reports_use_shared_scope_for_finance' => $has($r, "CrmReportingDataScope::class)->invoices(DB::table('invoices'))")
                && $has($r, "->invoices(DB::table('invoices as i')"),
            'invoice_issued_statuses_agree' => $has($r, "status IN ('Sent','Paid')")
                && $has($k, "status IN ('Sent','Paid')"),
            'invoice_paid_statuses_agree' => $has($r, "status = 'Paid'")
                && $has($k, "status = 'Paid'"),
            'invoice_outstanding_statuses_agree' => $has($r, "status = 'Sent'")
                && $has($k, "status = 'Sent'"),
            'invoice_overdue_statuses_agree' => $has($r, "status = 'Sent' AND due_date < ?")
                && $has($k, "status = 'Sent' AND due_date < ?"),
            'invoice_date_period_agrees' => $has($r, "whereDate('invoice_date', '>=',")
                && $has($k, "whereDate('invoice_date', '>=',")
                && $has($r, "whereDate('invoice_date', '<=',")
                && $has($k, "whereDate('invoice_date', '<=',"),
            'dashboard_requires_explicit_view_grant' => $has($k, 'authorizeMetric($actor, $table, $permission)')
                && $has($k, "return 'restricted';"),
            'all_four_kpis_in_catalog' => is_string($c) &&
                count(array_filter(['invoices.invoiced', 'invoices.paid', 'invoices.outstanding', 'invoices.overdue'],
                    static fn ($id) => str_contains($c, "'".$id."' =>"))) === 4,
        ];
        $missing = array_keys(array_filter($checks, static fn (bool $ok): bool => !$ok));
        $findings = [
            [
                'id' => 'reports_vs_invoice_entitlements', 'severity' => 'high', 'state' => 'policy_review',
                'detail' => 'Reports permission is independent of Invoices View/data-scope permission. A Reports-authorized viewer may have no comparable Dashboard invoice KPI.',
            ],
            [
                'id' => 'opt_in_report_rows_vs_explicit_dashboard_scope', 'severity' => 'high', 'state' => 'policy_review',
                'detail' => 'Reports filters Invoice rows through opt-in downstream Sales scope; Dashboard requires explicit Invoice data-scope grants. Same formula does not imply same visible population.',
            ],
        ];
        $status = $missing ? 'SOURCE_DRIFT' : 'REVIEW_REQUIRED';
        return [
            'stage' => 'A7.6.5.2', 'read_only' => true, 'business_rows_read' => false,
            'status' => $status,
            'summary' => [
                'source_checks' => count($checks),
                'passed' => count($checks) - count($missing),
                'failed' => count($missing),
                'policy_reviews' => count($findings),
            ],
            'checks' => array_map(static fn ($id, $ok) => ['id' => $id, 'status' => $ok ? 'pass' : 'fail'], array_keys($checks), array_values($checks)),
            'findings' => $findings,
            'scope_flags' => [
                'customers_opt_in' => (bool) config('crm_data_scope.customers_enabled', false),
                'opportunities_opt_in' => (bool) config('crm_data_scope.opportunities_enabled', false),
            ],
            'notes' => [
                'Dashboard defaults to start of the current year through today; Reports can choose a different inclusive invoice_date period.',
                'Paid is grouped by invoice_date, not a payment date. Legacy unpaid/Draft/Cancelled are excluded from issued totals.',
                'This is a source-contract diagnostic, not an authenticated, transactional, or numeric comparison on real CRM data.',
            ],
        ];
    }
}
