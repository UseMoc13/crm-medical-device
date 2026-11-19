<?php

namespace Tests\Unit;

use App\Services\CrmFinancialParityBaseline;
use Tests\TestCase;

final class CrmStagingInvoiceParitySourceTest extends TestCase
{
    public function test_reports_and_dashboard_source_contracts_have_not_drifted(): void
    {
        $result = app(CrmFinancialParityBaseline::class)->run();
        self::assertSame(13, $result['summary']['source_checks']);
        self::assertSame(13, $result['summary']['passed']);
        self::assertSame(0, $result['summary']['failed']);
        self::assertSame('REVIEW_REQUIRED', $result['status']);
        // Explicitly refuses to call this permission parity.
        self::assertSame(2, $result['summary']['policy_reviews']);
    }

    public function test_staging_command_is_blocked_by_default_without_db_reads(): void
    {
        config()->set('crm_financial_parity.staging_parity_enabled', false);
        $queries = [];
        \Illuminate\Support\Facades\DB::listen(static function ($event) use (&$queries) {
            $queries[] = $event->sql;
        });
        $exit = \Illuminate\Support\Facades\Artisan::call('crm:reporting:staging-parity', [
            '--confirm-staging' => true,
        ]);
        self::assertSame(2, $exit);
        self::assertSame([], $queries, 'Blocked command must not access the CRM database.');
    }
}
