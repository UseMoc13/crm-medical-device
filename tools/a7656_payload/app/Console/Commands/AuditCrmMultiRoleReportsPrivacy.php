<?php

namespace App\Console\Commands;

use App\Services\CrmMultiRoleReportsPrivacyAudit;
use Illuminate\Console\Command;

/** Read-only, hypothetical role-combination preview: NEVER reads user memberships. */
final class AuditCrmMultiRoleReportsPrivacy extends Command
{
    protected $signature = 'crm:reporting:multi-role-preview
        {--roles= : Comma-separated existing role names (2 to 4); NOT actual user assignments}
        {--json : JSON-only output}';

    protected $description = 'Preview hypothetical multi-role Reports/CSV entitlements and privacy surfaces; metadata only';

    public function handle(CrmMultiRoleReportsPrivacyAudit $audit): int
    {
        $raw = $this->option('roles');
        if (!is_string($raw) || trim($raw) === '') {
            $this->error('Specify 2 to 4 role names: --roles=Finance,Management');
            return self::FAILURE;
        }
        $result = $audit->run(array_map('trim', explode(',', $raw)));
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        } else {
            $this->line('A7.6.5.6 Multi-Role Reports Privacy Preview [POLICY METADATA ONLY; READ-ONLY]');
            $this->line('Status: '.$result['status'].' | Roles: '.implode(' + ', $result['roles'] ?? []));
            $assessment = $result['effective_access'] ?? null;
            if ($assessment) {
                $this->line('Reports '.($assessment['reports_page'] ? 'YES' : 'NO')
                    .' | CSV '.($assessment['csv_export'] ? 'YES' : 'NO')
                    .' | Dashboard Invoice KPI '.($assessment['dashboard_invoice_kpis'] ? 'YES' : 'NO')
                    .' | Invoice scope '.$assessment['invoice_scope']);
                foreach ($assessment['review_flags'] as $flag) $this->warn('[REVIEW] '.$flag);
                $this->line('CSV strict pilot: '.($result['csv_pilot_enabled'] ? 'ON' : 'OFF'));
            }
            $this->line('No user memberships, business records, invoice amounts, or customer identities read.');
        }
        return in_array($result['status'], ['REVIEW_REQUIRED', 'NO_REPORTS_ACCESS'], true)
            ? self::SUCCESS : self::FAILURE;
    }
}
