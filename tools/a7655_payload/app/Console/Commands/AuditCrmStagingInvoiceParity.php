<?php

namespace App\Console\Commands;

use App\Services\CrmFinancialParityBaseline;
use App\Services\CrmReportingDataScope;
use App\Services\CrmStagingInvoiceParity;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Read-only, explicitly opted-in staging SQL diagnostic; never runs on production/local. */
final class AuditCrmStagingInvoiceParity extends Command
{
    protected $signature = 'crm:reporting:staging-parity
        {--confirm-staging : Acknowledge an aggregate read of the connected STAGING database}
        {--from= : Invoice period start (YYYY-MM-DD), inclusive}
        {--to= : Invoice period end (YYYY-MM-DD), inclusive}
        {--as-of= : Overdue comparison date (YYYY-MM-DD)}
        {--json : Print metadata and match flags only}';

    protected $description = 'Staging-only read-only comparison of Report FILTER, Dashboard CASE and CSV left-join financial formulas';

    public function handle(CrmStagingInvoiceParity $parity, CrmFinancialParityBaseline $source): int
    {
        $from = (string) ($this->option('from') ?: CarbonImmutable::today()->startOfYear()->toDateString());
        $to = (string) ($this->option('to') ?: CarbonImmutable::today()->toDateString());
        $asOf = (string) ($this->option('as-of') ?: CarbonImmutable::today()->toDateString());

        if (!CrmStagingInvoiceParity::validDate($from)
            || !CrmStagingInvoiceParity::validDate($to)
            || !CrmStagingInvoiceParity::validDate($asOf)
            || $from > $to) {
            return $this->blocked(['Invalid date range: use YYYY-MM-DD, from <= to.']);
        }

        // First check environment/explicit consent; do not even resolve DB in the usual OFF state.
        if (app()->environment() !== 'staging'
            || !config('crm_financial_parity.staging_parity_enabled', false)
            || !$this->option('confirm-staging')) {
            return $this->blocked([
                'Require APP_ENV=staging, CRM_A7655_STAGING_PARITY_ENABLED=true and --confirm-staging.',
            ]);
        }

        // Source drift or active downstream row scoping makes this unscoped check misleading.
        $contract = $source->run(); // PHP source only, no CRM business queries.
        $driver = DB::connection()->getDriverName();
        $decision = CrmStagingInvoiceParity::decision(
            app()->environment(),
            $driver,
            (bool) config('crm_financial_parity.staging_parity_enabled', false),
            (bool) $this->option('confirm-staging'),
            ($contract['summary']['failed'] ?? -1) === 0,
            app(CrmReportingDataScope::class)->salesScopeEnabled(),
        );
        if (!$decision['allowed']) return $this->blocked($decision['reasons']);

        $db = DB::connection();
        if ($db->transactionLevel() !== 0) {
            return $this->blocked(['Existing DB transaction detected; cannot guarantee a separate READ ONLY transaction.']);
        }

        $started = false;
        try {
            $db->beginTransaction();
            $started = true;
            $db->statement('SET TRANSACTION READ ONLY'); // PostgreSQL server-side write prohibition.
            $result = $parity->compare($db, $from, $to, $asOf);
        } catch (Throwable $e) {
            // Do not print DB exception text (may contain queries/bindings).
            return $this->blocked(['Staging SQL check failed. Verify schema/permissions without sharing DB errors.']);
        } finally {
            if ($started && $db->transactionLevel() > 0) $db->rollBack();
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $summary = $result['summary'];
            $this->line('A7.6.5.5 STAGING SQL Invoice Formula Parity [READ ONLY; NO AMOUNTS]');
            $this->line('Status: ' . $result['status'] . ' | Checks ' . $summary['passed'] . '/' . $summary['total']
                . ' | Failed ' . $summary['failed']);
            foreach ($result['checks'] as $item) {
                $this->line('[' . strtoupper($item['status']) . '] ' . $item['id']);
            }
            $this->warn('This does NOT verify per-user row scope, Report authorization, or CSV file contents.');
        }
        return $result['status'] === 'UNSCOPED_FORMULA_PARITY' ? self::SUCCESS : self::FAILURE;
    }

    /** Fail closed. No business data is queried or disclosed in blocked responses. */
    private function blocked(array $reasons): int
    {
        if ($this->option('json')) {
            $this->line(json_encode(['stage' => 'A7.6.5.5', 'status' => 'BLOCKED', 'read_only' => true,
                'reasons' => $reasons], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->warn('A7.6.5.5 staging parity BLOCKED (no business rows read).');
            foreach ($reasons as $reason) $this->line('[BLOCKED] ' . $reason);
        }
        return self::INVALID;
    }
}
