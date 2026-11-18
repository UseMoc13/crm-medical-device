<?php

namespace App\Console\Commands;

use App\Services\CrmHistoricalRemediationReview;
use Illuminate\Console\Command;

/** A7.5.2.2c read-only assessment; never applies a correction. */
final class ReviewCrmHistoricalRemediation extends Command
{
    protected $signature = 'crm:integration:remediation-review
        {--json : Machine-readable review without IDs by default}
        {--details : Local-only evidence including IDs and raw legacy statuses}
        {--limit=10 : Maximum evidence records per finding (1-20)}';

    protected $description = 'Review evidence and manual decisions for legacy CRM integration anomalies; no writes.';

    public function handle(CrmHistoricalRemediationReview $review): int
    {
        $details = (bool) $this->option('details');
        if ($details && !app()->environment('local')) {
            $this->error('Detailed evidence requires APP_ENV=local.');
            return self::FAILURE;
        }
        $limit = (string) $this->option('limit');
        if (!ctype_digit($limit) || (int) $limit < 1 || (int) $limit > 20) {
            $this->error('--limit must be an integer from 1 to 20.');
            return self::FAILURE;
        }
        $report = $review->run($details, (int) $limit);
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->info('A7.5.2.2c CRM Historical Remediation Review [READ-ONLY]');
            $summary = $report['summary'];
            $this->line('Rules: '.$summary['checked'].' | Flagged: '.$summary['flagged_rules']
                .' | Skipped: '.$summary['skipped'].' | Errors: '.$summary['errors']
                .' | Shared Delivery: '.($summary['shared_delivery_records'] ?? 'unknown')
                .' | Auto-corrections: 0');
            foreach ($report['cases'] as $case) {
                $this->line('['.strtoupper($case['status']).'] '.$case['id']
                    .' | '.$case['priority'].' | '.$case['decision'].' | findings: '.($case['count'] ?? 'n/a'));
                if ($case['status'] === 'flagged') $this->line('  '.$case['guidance']);
                if (isset($case['missing_schema'])) $this->line('  Missing schema: '.implode(', ', $case['missing_schema']));
                if (isset($case['error_type'])) $this->line('  Error type: '.$case['error_type']);
                if ($details) {
                    foreach ($case['evidence'] ?? [] as $entry) {
                        $this->line('  '.json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                    }
                    if ($case['details_limited'] ?? false) $this->warn('  Evidence truncated; increase --limit up to 20.');
                }
            }
            $this->line($report['next_step']);
        }
        return $report['summary']['skipped'] || $report['summary']['errors'] ? self::FAILURE : self::SUCCESS;
    }
}
