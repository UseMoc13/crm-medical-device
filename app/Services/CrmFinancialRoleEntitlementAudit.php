<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * A7.6.5.3: a READ-ONLY single-role entitlement preview, not an access grant.
 * Inspects role permission metadata only; never queries users or business data.
 * Multi-role user grants, Super Admin membership, actual monetary parity,
 * and record-level report visibility require separate verification.
 */
final class CrmFinancialRoleEntitlementAudit
{
    private const PERMISSIONS = [
        'reporting.access', 'crm_page.reports.view',
        'billing.access', 'crm_page.invoices.view',
    ];

    /**
     * Model the exact two-permission Reports guard and the A7.6.2 Invoice KPI
     * scope decision for a single standalone role. Never assume null scope=all.
     *
     * @param list<array{permission_name:string,data_scope:?string}> $grants
     * @return array{reports_page:bool,csv_export:bool,dashboard_invoice_kpis:bool,invoice_scope:string,review_code:string}
     */
    public static function assessRole(bool $superAdmin, array $grants, bool $downstreamScope, bool $csvPilotEnabled = false): array
    {
        if ($superAdmin) {
            return [
                'reports_page' => true, 'csv_export' => true,
                'dashboard_invoice_kpis' => true, 'invoice_scope' => 'all',
                'review_code' => 'super_admin_bypass',
            ];
        }

        $permissions = [];
        $invoiceScopes = [];
        foreach ($grants as $grant) {
            $key = (string) ($grant['permission_name'] ?? '');
            if (!in_array($key, self::PERMISSIONS, true)) continue;
            $permissions[$key] = true;
            if ($key === 'crm_page.invoices.view' && $grant['data_scope'] !== null) {
                $invoiceScopes[] = trim((string) $grant['data_scope']);
            }
        }

        $reports = isset($permissions['reporting.access'], $permissions['crm_page.reports.view']);
        $invoiceAllowed = isset($permissions['billing.access'], $permissions['crm_page.invoices.view']);
        $decision = CrmScopedKpiQuery::scopeDecision('invoices', $invoiceScopes);
        $kpi = $invoiceAllowed && $decision === 'all';
        $scopeLabel = $invoiceScopes === [] ? 'missing' : $decision;
        $csv = $reports && (!$csvPilotEnabled || $kpi);
        $review = !$reports ? 'no_reports_grant'
            : (!$kpi ? ($csvPilotEnabled ? 'reports_only_csv_restricted' : 'reports_csv_without_invoice_kpi')
                : ($downstreamScope ? 'downstream_sales_scope_may_differ' : 'eligible_for_numeric_comparison'));

        return [
            'reports_page' => $reports, 'csv_export' => $csv,
            'dashboard_invoice_kpis' => $kpi, 'invoice_scope' => $scopeLabel,
            'review_code' => $review,
        ];
    }

    /** @return array<string,mixed> */
    public function run(): array
    {
        $tables = [
            'roles' => ['role_id', 'role_name', 'is_system'],
            'permissions' => ['permission_id', 'permission_name'],
            'role_permissions' => ['role_id', 'permission_id', 'data_scope'],
        ];

        try {
            $missing = [];
            foreach ($tables as $table => $columns) {
                if (!Schema::hasTable($table)) {
                    $missing[] = $table;
                    continue;
                }
                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) $missing[] = $table.'.'.$column;
                }
            }
            if ($missing !== []) return $this->emptyResult('INCOMPLETE', $missing);

            $roles = DB::table('roles')->select('role_id', 'role_name', 'is_system')
                ->orderBy('role_name')->orderBy('role_id')->get();
            $grantRows = DB::table('role_permissions as rp')
                ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
                ->whereIn('p.permission_name', self::PERMISSIONS)
                ->select('rp.role_id', 'p.permission_name', 'rp.data_scope')->get()
                ->groupBy('role_id');
        } catch (Throwable) {
            // Do not expose SQL errors or schema information from the connection.
            return $this->emptyResult('BLOCKED', []);
        }

        $salesScope = (bool) config('crm_data_scope.customers_enabled', false)
            || (bool) config('crm_data_scope.opportunities_enabled', false);
        $csvPilotEnabled = (bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $results = [];
        $reportsCount = $reportsWithoutKpi = $csvWithoutKpi = $eligible = $downstream = 0;
        foreach ($roles as $role) {
            $grants = [];
            foreach ($grantRows->get($role->role_id, collect()) as $g) {
                $grants[] = ['permission_name' => (string) $g->permission_name,
                    'data_scope' => $g->data_scope === null ? null : (string) $g->data_scope];
            }
            $super = (bool) $role->is_system && strtolower(trim((string) $role->role_name)) === 'super admin';
            $assessment = self::assessRole($super, $grants, $salesScope, $csvPilotEnabled);
            if ($assessment['reports_page']) $reportsCount++;
            if ($assessment['reports_page'] && !$assessment['dashboard_invoice_kpis']) $reportsWithoutKpi++;
            if ($assessment['csv_export'] && !$assessment['dashboard_invoice_kpis']) $csvWithoutKpi++;
            if ($assessment['review_code'] === 'eligible_for_numeric_comparison') $eligible++;
            if ($assessment['review_code'] === 'downstream_sales_scope_may_differ') $downstream++;

            $results[] = [
                'role' => $this->safeLabel((string) $role->role_name),
                ...$assessment,
            ];
        }
        $status = ($reportsCount > 0 && $reportsWithoutKpi === 0 && $downstream === 0)
            ? 'ELIGIBLE_FOR_NUMERIC_COMPARISON' : 'REVIEW_REQUIRED';
        return [
            'stage' => 'A7.6.5.3', 'read_only' => true,
            'policy_metadata_only' => true, 'user_rows_read' => false,
            'business_rows_read' => false, 'status' => $status,
            'summary' => [
                'roles_checked' => count($results), 'reports_capable_roles' => $reportsCount,
                'reports_without_invoice_kpi' => $reportsWithoutKpi,
                'csv_without_invoice_kpi' => $csvWithoutKpi,
                'eligible_for_numeric_comparison' => $eligible,
                'downstream_scope_review' => $downstream,
            ],
            'downstream_sales_scope_enabled' => $salesScope,
            'csv_pilot_enabled' => $csvPilotEnabled,
            'single_role_preview' => $results,
            'caveat' => 'Single-role grants only; multi-role unions and record-level access are not evaluated. No financial values compared.',
        ];
    }

    private function emptyResult(string $status, array $missing): array
    {
        return [
            'stage' => 'A7.6.5.3', 'read_only' => true, 'policy_metadata_only' => true,
            'user_rows_read' => false, 'business_rows_read' => false,
            'status' => $status, 'schema_missing' => $missing,
            'summary' => [
                'roles_checked' => 0, 'reports_capable_roles' => 0,
                'reports_without_invoice_kpi' => 0,
                'csv_without_invoice_kpi' => 0,
                'eligible_for_numeric_comparison' => 0, 'downstream_scope_review' => 0,
            ],
            'csv_pilot_enabled' => (bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false),
            'single_role_preview' => [],
            'caveat' => 'No completeness or parity claim can be made without the required policy schema.',
        ];
    }

    private function safeLabel(string $name): string
    {
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
        return mb_substr($name, 0, 80);
    }
}
