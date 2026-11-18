<?php

namespace Tests\Feature;

use App\Services\CrmIntegrationInvestigation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Read-only regression test. Never migrates, seeds or mutates the CRM database. */
final class CrmIntegrationInvestigationCommandTest extends TestCase
{
    public function test_safe_json_summary_has_four_rules_and_no_record_ids(): void
    {
        $this->assertArrayHasKey('crm:integration:inspect', Artisan::all());

        $exit = Artisan::call('crm:integration:inspect', ['--json' => true]);
        // Reading Artisan::output() consumes the buffer. Always capture it once.
        $output = trim(Artisan::output());
        $this->assertNotSame('', $output, 'Investigation --json must return a report.');
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);

        $this->assertTrue($report['read_only']);
        $this->assertSame('A7.5.2.1', $report['stage']);
        $this->assertCount(4, $report['checks']);
        $this->assertSame(CrmIntegrationInvestigation::TARGETS, array_column($report['checks'], 'id'));
        $this->assertSame(4, $report['summary']['checked']);
        $this->assertSame(0, $report['summary']['errors'], 'Query errors must fail this test: '.$output);

        // Test databases can intentionally omit CRM module tables. A real SKIP
        // returns exit=1 by design, so a missing schema must not masquerade as
        // a clean audit (exit=0). Verify each reported dependency is truly absent.
        $skipped = array_values(array_filter(
            $report['checks'], static fn (array $check): bool => $check['status'] === 'skipped'
        ));
        $this->assertSame(count($skipped), $report['summary']['skipped']);
        foreach ($report['checks'] as $check) {
            $this->assertContains($check['status'], ['ok', 'flagged', 'skipped']);
            $this->assertArrayNotHasKey('details', $check);
            $this->assertArrayNotHasKey('sample_ids', $check);
            $this->assertArrayNotHasKey('entity_id', $check);
            $this->assertArrayNotHasKey('record_id', $check);
        }
        foreach ($skipped as $check) {
            $this->assertNotEmpty($check['missing_schema'] ?? [], 'SKIP requires a missing dependency.');
            foreach ($check['missing_schema'] as $dependency) {
                $parts = explode('.', $dependency, 2);
                if (count($parts) === 1) {
                    $this->assertFalse(Schema::hasTable($parts[0]), 'Reported missing table exists: '.$dependency);
                } else {
                    $this->assertTrue(Schema::hasTable($parts[0]), 'Reported missing column must have an existing table: '.$dependency);
                    $this->assertFalse(Schema::hasColumn($parts[0], $parts[1]), 'Reported missing column exists: '.$dependency);
                }
            }
        }
        $this->assertSame($skipped === [] ? 0 : 1, $exit, 'Unexpected command exit status. '.$output);

        // Schema identifiers such as "serial_numbers" are public structural
        // information, not a serial number value. Disallow actual data fields.
        $this->assertStringNotContainsString('"serial_number":', $output);
        $this->assertStringNotContainsString('"customer_name":', $output);
        $this->assertStringNotContainsString('"delivery_id":', $output);
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
