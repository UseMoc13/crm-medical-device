<?php

namespace App\Console\Commands;

use App\Services\CrmIntegrationAudit;
use Illuminate\Console\Command;

/** Explicitly read-only A7.5 integration health audit. */
final class AuditCrmIntegration extends Command
{
    protected $signature = 'crm:integration:audit
        {--json : Emit machine-readable report}
        {--strict : Exit with an error if any critical findings, skipped checks, or query errors exist}
        {--samples : Include up to five record UUIDs per flagged check (local environment only)}';

    protected $description = 'Audit cross-module CRM data consistency without modifying any data.';

    public function handle(CrmIntegrationAudit $audit): int
    {
        $withSamples = (bool) $this->option('samples');
        if ($withSamples && !app()->environment('local')) {
            $this->error('Record UUID samples are restricted to APP_ENV=local. Omit --samples.');
            return self::FAILURE;
        }

        $report = $audit->run($withSamples);
        $s = $report['summary'];

        if ((bool) $this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $this->info('A7.5.1 CRM Cross-Module Integration Audit [READ-ONLY]');
            $this->line('Checks: '.$s['total_checks'].' | OK: '.$s['passed']
                .' | Flagged: '.$s['flagged'].' | Skipped: '.$s['skipped'].' | Query errors: '.$s['errors']);
            $this->line('Critical findings: '.$s['critical_findings'].' | Review findings: '.$s['review_findings']);
            foreach ($report['checks'] as $check) {
                $tag = match ($check['status']) {
                    'ok' => '[OK]', 'flagged' => '[REVIEW]', 'skipped' => '[SKIP]',
                    default => '[ERROR]',
                };
                $message = $tag.' '.strtoupper($check['severity']).' '.$check['id']
                    .' — '.$check['label'];
                if ($check['status'] === 'flagged') $message .= ' ('.$check['count'].' row(s))';
                if ($check['status'] === 'skipped') $message .= ' (missing: '.implode(', ',$check['missing_schema']).')';
                if ($check['status'] === 'error') $message .= ' ('.$check['error_type'].')';
                $this->line($message);
                if (!empty($check['sample_ids'])) $this->line('    Sample UUIDs: '.implode(', ',$check['sample_ids']));
            }
            $this->newLine();
            $this->line('No database writes were performed. REVIEW findings are investigative signals, not proof of corruption.');
            if ($s['skipped'] > 0) $this->warn('Skipped checks require schema review; do not interpret as a clean audit.');
        }

        if ($s['errors'] > 0) return self::FAILURE;
        if ((bool) $this->option('strict') && ($s['critical_findings'] > 0 || $s['skipped'] > 0)) {
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
