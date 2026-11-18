<?php

namespace App\Console\Commands;

use App\Services\CrmKpiSourceReadiness;
use Illuminate\Console\Command;

final class AuditCrmKpiSources extends Command
{
    protected $signature = 'crm:reporting:baseline {--json : JSON without business records} {--strict : Return nonzero unless all sources are available and reviews clear}';
    protected $description = 'A7.6.1 read-only KPI definition/source readiness check';

    public function handle(CrmKpiSourceReadiness $readiness): int
    {
        $report = $readiness->run();
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } else {
            $s = $report['summary'];
            $this->line('A7.6.1 CRM KPI Source Readiness [READ-ONLY]');
            $this->line('Status: '.$report['status'].' | Sources: '.$s['metric_sources'].' | Available: '.$s['source_available'].' | Missing: '.$s['missing_schema'].' | Query errors: '.$s['query_errors'].' | Review: '.$s['source_reviews']);
            foreach ($report['metrics'] as $metric) {
                $this->line('['.strtoupper($metric['status']).'] '.$metric['id'] . ($metric['missing_schema'] ? ' | missing: '.implode(', ', $metric['missing_schema']) : ''));
            }
            foreach ($report['source_reviews'] as $review) {
                if ($review['status'] === 'review' || $review['status'] === 'source_unavailable') {
                    $this->line('[SOURCE '.strtoupper($review['status']).'] '.$review['id'].' ('.$review['severity'].') | '.$review['action']);
                }
            }
            $this->line($report['note']);
        }
        if (in_array($report['status'], ['BLOCKED', 'INCOMPLETE'], true)) return self::FAILURE;
        return $this->option('strict') && $report['status'] !== 'SOURCE_READY' ? self::FAILURE : self::SUCCESS;
    }
}
