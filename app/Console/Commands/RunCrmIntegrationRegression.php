<?php

namespace App\Console\Commands;

use App\Services\CrmIntegrationRegressionGate;
use Illuminate\Console\Command;

final class RunCrmIntegrationRegression extends Command
{
    protected $signature = 'crm:integration:regression
        {--json : Sanitized machine-readable summary}
        {--strict : Treat historical findings (WATCH) as nonzero exit}';

    protected $description = 'Read-only cross-module regression gate: audit, evidence parity, CRM notifications and audit prerequisites.';

    public function handle(CrmIntegrationRegressionGate $gate): int
    {
        $report = $gate->run();
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->info('A7.5.4.1 CRM Integration Regression Gate [READ-ONLY]');
            $summary = $report['summary'];
            $this->line('Status: '.$report['status'].' | Audit '.$summary['audit_checks'].' checks | OK '.$summary['audit_passed']
                .' | Flagged '.$summary['audit_flagged'].' | Skipped '.$summary['skipped_across_reports']
                .' | Query errors '.$summary['query_errors_across_reports']);
            $this->line('Critical '.$summary['critical_findings'].' | Review '.$summary['review_findings']
                .' | Shared Delivery '.($summary['shared_delivery_records'] ?? 'unknown')
                .' | Prerequisites missing '.$summary['missing_prerequisites']
                .' | Scanner disagreements '.$summary['inconsistent_rules']);
            foreach ($report['targets'] as $target) {
                $this->line('['.strtoupper($target['status']).'] '.$target['rule'].' ('.$target['count'].')');
            }
            foreach (['missing_schema','missing_routes','missing_audit_events','missing_audit_subjects','inconsistent_rules'] as $field) {
                if ($report[$field]) $this->warn($field.': '.implode(', ', $report[$field]));
            }
            $this->line('No record IDs, customer data, SQL, corrections or database writes. Manual browser test remains required.');
        }
        return in_array($report['status'], ['BLOCKED','INCOMPLETE'], true)
            || ($this->option('strict') && $report['status'] !== 'READY')
            ? self::FAILURE : self::SUCCESS;
    }
}
