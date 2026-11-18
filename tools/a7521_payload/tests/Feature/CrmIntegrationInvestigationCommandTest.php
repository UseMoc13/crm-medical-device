<?php

namespace Tests\Feature;

use App\Services\CrmIntegrationInvestigation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** No transaction, factory, mutation or seeding: inspect existing CRM data only. */
final class CrmIntegrationInvestigationCommandTest extends TestCase
{
    public function test_safe_json_summary_has_four_rules_and_no_record_ids(): void
    {
        $this->assertArrayHasKey('crm:integration:inspect', Artisan::all());
        $exit = Artisan::call('crm:integration:inspect', ['--json' => true]);
        $output = trim(Artisan::output()); // Artisan output is consumed on read
        $this->assertSame(0, $exit, $output);
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($report['read_only']);
        $this->assertSame('A7.5.2.1', $report['stage']);
        $this->assertCount(4, $report['checks']);
        $this->assertSame(0, $report['summary']['errors']);
        $this->assertSame(CrmIntegrationInvestigation::TARGETS, array_column($report['checks'], 'id'));
        foreach ($report['checks'] as $check) {
            $this->assertArrayNotHasKey('details', $check);
            $this->assertArrayNotHasKey('sample_ids', $check);
        }
        $this->assertStringNotContainsString('serial_number', $output);
        $this->assertStringNotContainsString('customer_name', $output);
    }

    public function test_scan_issues_no_database_writes(): void
    {
        $unsafe = [];
        DB::listen(static function ($event) use (&$unsafe) {
            if (!preg_match('/^\s*(select|with|show|pragma|explain)\b/i', $event->sql)) $unsafe[] = $event->sql;
        });
        app(CrmIntegrationInvestigation::class)->run(false);
        $this->assertSame([], $unsafe);
    }
}
