<?php

namespace Tests\Feature;

use App\Services\CrmKpiSourceReadiness;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CrmKpiSourceBaselineCommandTest extends TestCase
{
    public function test_json_contract_reports_schema_readiness_without_record_data(): void
    {
        self::assertArrayHasKey('crm:reporting:baseline', Artisan::all());
        $exit = Artisan::call('crm:reporting:baseline', ['--json' => true]);
        $output = trim(Artisan::output()); // Consume exactly once.
        self::assertNotSame('', $output);
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertTrue($report['read_only']);
        self::assertFalse($report['aggregate_values_included']);
        self::assertSame('A7.6.1', $report['stage']);
        self::assertCount(count(CrmKpiSourceReadiness::METRICS), $report['metrics']);
        self::assertSame(0, $report['summary']['query_errors'], 'Database schema failures must fail this test.');
        self::assertSame(0, $report['summary']['source_unavailable'], 'Review source files must be inspectable.');
        self::assertSame($report['summary']['missing_schema'] ? 1 : 0, $exit);
        foreach ($report['metrics'] as $metric) {
            self::assertArrayNotHasKey('count', $metric);
            self::assertArrayNotHasKey('amount', $metric);
            self::assertArrayNotHasKey('record_id', $metric);
        }
        self::assertStringNotContainsString('customer_name', $output);
        self::assertStringNotContainsString('email', $output);
    }

    public function test_baseline_performs_only_schema_reads(): void
    {
        $unsafe = [];
        DB::listen(static function ($event) use (&$unsafe) {
            if (!preg_match('/^\s*(select|with|show|pragma|explain)\b/i', $event->sql)) $unsafe[] = $event->sql;
        });
        app(CrmKpiSourceReadiness::class)->run();
        self::assertSame([], $unsafe, 'A7.6.1 used non-read SQL');
    }
}
