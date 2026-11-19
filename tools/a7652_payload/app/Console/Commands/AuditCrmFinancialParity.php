<?php

namespace App\Console\Commands;

use App\Services\CrmFinancialParityBaseline;
use Illuminate\Console\Command;

final class AuditCrmFinancialParity extends Command
{
    protected $signature = 'crm:reporting:parity-baseline {--json : Output a sanitized JSON report} {--strict : Fail unless sources and policy are fully aligned}';
    protected $description = 'A7.6.5.2 read-only Dashboard/Reports financial KPI source and scope-parity assessment';

    public function handle(CrmFinancialParityBaseline $audit): int
    {
        $result = $audit->run();
        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
        } else {
            $s = $result['summary'];
            $this->line('A7.6.5.2 CRM Financial KPI & Scope Parity [SOURCE-ONLY; READ-ONLY]');
            $this->line('Status: '.$result['status'].' | Checks '.$s['passed'].'/'.$s['source_checks'].' | Failed '.$s['failed'].' | Policy reviews '.$s['policy_reviews']);
            foreach ($result['checks'] as $check) $this->line('['.strtoupper($check['status']).'] '.$check['id']);
            foreach ($result['findings'] as $finding) $this->line('[REVIEW] '.$finding['id'].' ('.$finding['severity'].') | '.$finding['detail']);
            $this->line('No business rows, identifiers, amounts, or customer details were read.');
        }
        if ($result['status'] === 'SOURCE_DRIFT') return self::FAILURE;
        return ($this->option('strict') && $result['status'] !== 'READY') ? self::FAILURE : self::SUCCESS;
    }
}
