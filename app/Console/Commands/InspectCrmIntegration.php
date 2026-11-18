<?php

namespace App\Console\Commands;

use App\Services\CrmIntegrationInvestigation;
use Illuminate\Console\Command;

/** Read-only A7.5.2.1 targeted investigation. */
final class InspectCrmIntegration extends Command
{
    protected $signature = 'crm:integration:inspect
        {--json : Machine-readable report}
        {--details : Include local record IDs, raw statuses and warehouse/product aggregates (APP_ENV=local only)}
        {--limit=10 : Maximum detailed records per finding (1-20)}';

    protected $description = 'Inspect flagged integration rules without changing CRM data.';

    public function handle(CrmIntegrationInvestigation $investigation): int
    {
        $details = (bool) $this->option('details');
        if ($details && !app()->environment('local')) {
            $this->error('Detailed diagnostics require APP_ENV=local; do not expose operational IDs on deployed systems.');
            return self::FAILURE;
        }
        $limitValue = (string) $this->option('limit');
        if (!ctype_digit($limitValue) || (int) $limitValue < 1 || (int) $limitValue > 20) {
            $this->error('--limit must be an integer between 1 and 20.');
            return self::FAILURE;
        }
        $report = $investigation->run($details, (int) $limitValue);
        if ((bool) $this->option('json')) {
            $this->line(json_encode($report, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        } else {
            $this->info('A7.5.2.1 Targeted CRM Integration Investigation [READ-ONLY]');
            $this->line('Flagged rules: '.$report['summary']['flagged_rules'].' | Skipped: '.$report['summary']['skipped'].' | Query errors: '.$report['summary']['errors'].' | Shared Delivery records: '.($report['summary']['overlapping_delivery_count'] ?? 'unknown'));
            foreach ($report['checks'] as $check) {
                $this->line('['.strtoupper($check['status']).'] '.$check['id'].' — '.($check['count'] ?? 'n/a').' finding(s)');
                if (isset($check['missing_schema'])) $this->line('  Missing schema: '.implode(', ', $check['missing_schema']));
                if (isset($check['error_type'])) $this->line('  Error category: '.$check['error_type']);
                if ($details) {
                    foreach ($check['details'] ?? [] as $item) {
                        // JSON escapes control characters in untrusted status text.
                        $this->line('  '.json_encode($item, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
                    }
                    if ($check['details_limited'] ?? false) $this->warn('  Details truncated. Increase --limit up to 20.');
                }
            }
            $this->line('No INSERT/UPDATE/DELETE performed. Never repair stock, serials or statuses solely from this report.');
        }
        return $report['summary']['errors'] || $report['summary']['skipped'] ? self::FAILURE : self::SUCCESS;
    }
}
