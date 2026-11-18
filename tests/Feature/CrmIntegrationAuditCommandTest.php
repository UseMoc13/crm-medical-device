<?php

namespace Tests\Feature;

use App\Console\Commands\AuditCrmIntegration;
use App\Services\CrmIntegrationAudit;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** No factories, truncate, transactions or seeders: this test is read-only. */
final class CrmIntegrationAuditCommandTest extends TestCase
{
    public function test_command_is_registered_and_json_summary_has_no_personal_data_fields(): void
    {
        $this->assertInstanceOf(AuditCrmIntegration::class, app(AuditCrmIntegration::class));
        $this->assertTrue(array_key_exists('crm:integration:audit', Artisan::all()));

        // Connection failures should fail the test rather than silently mark
        // the environment clean. Missing modules are explicitly reported SKIP.
        $exit = Artisan::call('crm:integration:audit', ['--json'=>true]);
        // Artisan::output() fetches and clears the console output buffer.
        // Read it once; fetching again would return an empty string.
        $output = trim(Artisan::output());
        $this->assertSame(0, $exit, $output);
        $this->assertNotSame('', $output, 'Audit --json must emit a nonempty report.');
        $report = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($report['read_only']);
        $this->assertSame('A7.5.1', $report['stage']);
        $this->assertCount(count(app(CrmIntegrationAudit::class)->definitions()), $report['checks']);
        $this->assertSame(0, $report['summary']['errors']);
        foreach ($report['checks'] as $check) {
            $this->assertArrayNotHasKey('sample_ids', $check);
        }
    }

    public function test_audit_issues_select_queries_only(): void
    {
        $unsafe = [];
        DB::listen(static function ($event) use (&$unsafe): void {
            $sql = ltrim($event->sql);
            if (!preg_match('/^(select|with|show|pragma|explain)\b/i', $sql)) $unsafe[] = $sql;
        });
        app(CrmIntegrationAudit::class)->run(false);
        $this->assertSame([], $unsafe, 'Audit emitted non-read SQL');
    }
}
