<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * A7.6.5.6 — hypothetical role-combination policy preview.
 * Queries ONLY roles, permissions and role_permissions. Never reads users, user_roles,
 * invoices, invoice_items, customers, sales orders or business aggregates.
 * A combination is NOT evidence that an actual user holds those roles.
 */
final class CrmMultiRoleReportsPrivacyAudit
{
    private const GRANT_NAMES = [
        'reporting.access', 'crm_page.reports.view',
        'billing.access', 'crm_page.invoices.view',
    ];

    /**
     * @param list<array{role_name:string,is_system:bool}> $roles
     * @param list<array{permission_name:string,data_scope:?string}> $grants
     * @return array<string,mixed>
     */
    public static function assess(array $roles, array $grants, bool $downstreamScope, bool $csvPilot): array
    {
        $super = false;
        foreach ($roles as $role) {
            if (($role['is_system'] ?? false) === true
                && strtolower(trim((string) ($role['role_name'] ?? ''))) === 'super admin') {
                $super = true;
            }
        }
        $decision = CrmFinancialRoleEntitlementAudit::assessRole($super, $grants, $downstreamScope, $csvPilot);

        $reviews = [];
        if ($decision['reports_page']) {
            // Confirmed source surfaces: top-customers, top-products, and detailed CSV.
            $reviews[] = 'reports_customer_product_labels';
            if (!$decision['dashboard_invoice_kpis']) $reviews[] = 'reports_without_invoice_kpi';
            if ($decision['csv_export']) $reviews[] = 'csv_invoice_customer_details';
            if ($downstreamScope) $reviews[] = 'opt_in_report_row_scope';
        }

        return [
            'reports_page' => $decision['reports_page'],
            'csv_export' => $decision['csv_export'],
            'dashboard_invoice_kpis' => $decision['dashboard_invoice_kpis'],
            'invoice_scope' => $decision['invoice_scope'],
            'policy_decision' => $decision['review_code'],
            'super_admin_bypass' => $super,
            'review_flags' => $reviews,
            'customer_product_label_surface' => $decision['reports_page'],
            'detailed_invoice_csv_surface' => $decision['csv_export'],
            'per_user_access_verified' => false,
            'numeric_parity_verified' => false,
        ];
    }

    /** @param list<string> $requestedRoles @return array<string,mixed> */
    public function run(array $requestedRoles): array
    {
        $names = [];
        foreach ($requestedRoles as $role) {
            $role = trim($role);
            if ($role === '' || mb_strlen($role) > 80) return $this->blocked('INVALID_SELECTION');
            $key = mb_strtolower($role);
            if (isset($names[$key])) return $this->blocked('DUPLICATE_ROLE');
            $names[$key] = $role;
        }
        if (count($names) < 2 || count($names) > 4) return $this->blocked('INVALID_ROLE_COUNT');

        $required = [
            'roles' => ['role_id', 'role_name', 'is_system'],
            'permissions' => ['permission_id', 'permission_name'],
            'role_permissions' => ['role_id', 'permission_id', 'data_scope'],
        ];
        try {
            foreach ($required as $table => $columns) {
                if (!Schema::hasTable($table)) return $this->blocked('SCHEMA_INCOMPLETE');
                foreach ($columns as $column) {
                    if (!Schema::hasColumn($table, $column)) return $this->blocked('SCHEMA_INCOMPLETE');
                }
            }
            $rows = DB::table('roles')->whereIn(DB::raw('LOWER(role_name)'), array_keys($names))
                ->select('role_id', 'role_name', 'is_system')->get();
            if ($rows->count() !== count($names)) return $this->blocked('ROLE_NOT_FOUND');

            $ids = $rows->pluck('role_id')->all();
            $permissions = DB::table('role_permissions as rp')
                ->join('permissions as p', 'p.permission_id', '=', 'rp.permission_id')
                ->whereIn('rp.role_id', $ids)
                ->whereIn('p.permission_name', self::GRANT_NAMES)
                ->select('p.permission_name', 'rp.data_scope')->get();
        } catch (Throwable) {
            // Do not disclose SQL errors or database structures.
            return $this->blocked('READ_FAILED');
        }

        $roleDetails = [];
        foreach ($rows as $r) $roleDetails[] = [
            'role_name' => $this->safeLabel((string) $r->role_name),
            'is_system' => in_array($r->is_system, [true, 1, '1', 't', 'true'], true),
        ];
        usort($roleDetails, static fn (array $a, array $b): int => strcmp($a['role_name'], $b['role_name']));

        $grants = [];
        foreach ($permissions as $p) $grants[] = [
            'permission_name' => (string) $p->permission_name,
            'data_scope' => $p->data_scope === null ? null : (string) $p->data_scope,
        ];
        $downstream = (bool) config('crm_data_scope.customers_enabled', false)
            || (bool) config('crm_data_scope.opportunities_enabled', false);
        $pilot = (bool) config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
        $assessment = self::assess($roleDetails, $grants, $downstream, $pilot);

        $source = $this->sourceContracts();
        if (!$source['matches']) return [
            ...$this->blocked('SOURCE_DRIFT'),
            'source_contracts' => $source,
        ];
        return [
            'stage' => 'A7.6.5.6',
            'status' => $assessment['reports_page'] ? 'REVIEW_REQUIRED' : 'NO_REPORTS_ACCESS',
            'read_only' => true,
            'policy_metadata_only' => true,
            'actual_user_memberships_checked' => false,
            'user_rows_read' => false,
            'business_rows_read' => false,
            'roles' => array_column($roleDetails, 'role_name'),
            'csv_pilot_enabled' => $pilot,
            'downstream_sales_scope_enabled' => $downstream,
            'source_contracts' => $source,
            'effective_access' => $assessment,
            'caveat' => 'Hypothetical UNION of selected role grants only. Not a real user authorization, numeric parity, or row-level privacy certification.',
        ];
    }

    /** @return array{matches:bool,report_labels:bool,csv_details:bool,downstream_opt_in:bool} */
    private function sourceContracts(): array
    {
        $report = @file_get_contents(app_path('Http/Controllers/ReportController.php')) ?: '';
        $scope = @file_get_contents(app_path('Services/CrmReportingDataScope.php')) ?: '';
        $labels = str_contains($report, '$topCustomers') && str_contains($report, '$topProducts')
            && str_contains($report, "'c.customer_id', 'c.customer_name'")
            && str_contains($report, "'p.product_id', 'p.product_name'");
        $csv = str_contains($report, "'i.invoice_number'")
            && str_contains($report, "'c.customer_name'")
            && str_contains($report, "'i.total_amount'")
            && str_contains($report, 'function exportInvoices(');
        $optin = str_contains($scope, 'if (! $this->salesScopeEnabled())')
            && str_contains($scope, 'return $query;');
        return [
            'matches' => $labels && $csv && $optin,
            'report_labels' => $labels, 'csv_details' => $csv, 'downstream_opt_in' => $optin,
        ];
    }

    private function blocked(string $status): array
    {
        return [
            'stage' => 'A7.6.5.6', 'status' => $status, 'read_only' => true,
            'policy_metadata_only' => true, 'user_rows_read' => false,
            'business_rows_read' => false, 'actual_user_memberships_checked' => false,
            'roles' => [], 'effective_access' => null,
        ];
    }

    private function safeLabel(string $name): string
    {
        $stripped = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        return mb_substr($stripped, 0, 80);
    }
}
