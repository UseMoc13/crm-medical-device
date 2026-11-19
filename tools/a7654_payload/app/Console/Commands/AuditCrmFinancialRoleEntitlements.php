<?php

namespace App\Console\Commands;

use App\Services\CrmFinancialRoleEntitlementAudit;
use Illuminate\Console\Command;

final class AuditCrmFinancialRoleEntitlements extends Command
{
    protected $signature = 'crm:reporting:role-entitlements {--json : Print JSON (no business/user rows)} {--strict : Return nonzero unless eligible for numeric comparison}';
    protected $description = 'A7.6.5.3 read-only Reports/CSV vs Dashboard Invoice single-role entitlement preview';

    public function handle(CrmFinancialRoleEntitlementAudit $audit): int
    {
        $result = $audit->run();
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $s = $result['summary'];
            $this->line('A7.6.5.3 Financial Role Entitlement Review [POLICY METADATA ONLY; READ-ONLY]');
            $this->line('Status: '.$result['status'].' | Roles '.$s['roles_checked']
                .' | Reports enabled '.$s['reports_capable_roles']
                .' | Reports/no Invoice KPI '.$s['reports_without_invoice_kpi']
                .' | CSV/Invoice KPI mismatch '.$s['csv_without_invoice_kpi']
                .' | CSV strict pilot '.($result['csv_pilot_enabled'] ? 'ON' : 'OFF')
                .' | Downstream scope review '.$s['downstream_scope_review']);
            foreach ($result['single_role_preview'] as $role) {
                if (!$role['reports_page']) continue;
                $this->line('['.$role['review_code'].'] '.$role['role']
                    .' | Reports YES | CSV '.($role['csv_export'] ? 'YES' : 'NO').' | Dashboard Invoice KPI '
                    .($role['dashboard_invoice_kpis'] ? 'YES' : 'NO')
                    .' | Invoice scope '.$role['invoice_scope']);
            }
            if (!empty($result['schema_missing'])) $this->line('Missing policy schema: '.implode(', ', $result['schema_missing']));
            $this->line('No user rows, invoices, amounts or customer records read. Multi-role users not evaluated.');
        }
        if (in_array($result['status'], ['BLOCKED', 'INCOMPLETE'], true)) return self::FAILURE;
        return ($this->option('strict') && $result['status'] !== 'ELIGIBLE_FOR_NUMERIC_COMPARISON')
            ? self::FAILURE : self::SUCCESS;
    }
}
