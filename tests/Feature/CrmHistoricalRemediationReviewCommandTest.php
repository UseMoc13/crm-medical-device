<?php

namespace Tests\Feature;

use App\Services\CrmHistoricalRemediationReview;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** No migrate, truncate, seed, rollback or mutation of operational CRM tables. */
final class CrmHistoricalRemediationReviewCommandTest extends TestCase
{
    public function test_safe_json_exposes_only_manual_actions_and_no_record_ids(): void
    {
        $this->assertArrayHasKey('crm:integration:remediation-review', Artisan::all());
        $exit = Artisan::call('crm:integration:remediation-review', ['--json' => true]);
        $output = trim(Artisan::output()); // read once; reading again clears output
        $this->assertNotSame('', $output);
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('A7.5.2.2c', $report['stage']);
        $this->assertTrue($report['read_only']);
        $this->assertCount(4, $report['cases']);
        $this->assertSame(0, $report['summary']['automatic_corrections']);
        $this->assertSame(0, $report['summary']['errors'], $output);
        $skipped = array_values(array_filter($report['cases'], static fn ($c) => $c['status'] === 'skipped'));
        $this->assertCount(count($skipped), array_filter($report['cases'], static fn ($c) => $c['status'] === 'skipped'));
        $this->assertSame(count($skipped), $report['summary']['skipped']);
        foreach ($report['cases'] as $case) {
            $this->assertFalse($case['auto_apply_allowed'] ?? true);
            $this->assertArrayNotHasKey('evidence', $case);
            $this->assertArrayNotHasKey('record_id', $case);
            $this->assertArrayNotHasKey('delivery_id', $case);
        }
        foreach ($skipped as $case) {
            $this->assertNotEmpty($case['missing_schema'] ?? []);
            foreach ($case['missing_schema'] as $dependency) {
                $parts = explode('.', $dependency, 2);
                if (count($parts) === 1) {
                    $this->assertFalse(Schema::hasTable($parts[0]), 'Missing table actually exists: '.$dependency);
                } else {
                    $this->assertTrue(Schema::hasTable($parts[0]));
                    $this->assertFalse(Schema::hasColumn($parts[0], $parts[1]));
                }
            }
        }
        $this->assertSame($skipped ? 1 : 0, $exit, $output);
        $this->assertStringNotContainsString('"delivery_id":', $output);
        $this->assertStringNotContainsString('"record_id":', $output);
        $this->assertStringNotContainsString('"customer_name":', $output);
        $this->assertStringNotContainsString('"serial_number":', $output);
    }

    public function test_report_is_read_only_at_sql_query_boundary(): void
    {
        $unsafe = [];
        DB::listen(static function ($query) use (&$unsafe): void {
            if (!preg_match('/^\s*(select|with|show|pragma|explain)\b/i', $query->sql)) $unsafe[] = $query->sql;
        });
        app(CrmHistoricalRemediationReview::class)->run(false);
        $this->assertSame([], $unsafe, 'Review issued non-read SQL');
    }
}
