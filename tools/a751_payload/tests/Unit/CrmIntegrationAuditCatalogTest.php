<?php

namespace Tests\Unit;

use App\Services\CrmIntegrationAudit;
use Tests\TestCase;

final class CrmIntegrationAuditCatalogTest extends TestCase
{
    public function test_rules_have_unique_keys_explicit_schema_and_only_supported_severities(): void
    {
        $rules = app(CrmIntegrationAudit::class)->definitions();
        $this->assertGreaterThanOrEqual(25, count($rules));
        $this->assertSame(count($rules), count(array_unique(array_column($rules, 'id'))));
        foreach ($rules as $rule) {
            $this->assertContains($rule['severity'], ['critical','review']);
            $this->assertNotEmpty($rule['requires']);
            $this->assertInstanceOf(\Closure::class, $rule['query']);
            $this->assertMatchesRegularExpression('/^[a-z_]+\.[a-z_]+$/', $rule['id']);
        }
    }

    public function test_summary_separates_critical_review_skipped_and_execution_errors(): void
    {
        $result = CrmIntegrationAudit::summarize([
            ['status'=>'ok','severity'=>'critical','count'=>0],
            ['status'=>'flagged','severity'=>'critical','count'=>3],
            ['status'=>'flagged','severity'=>'review','count'=>2],
            ['status'=>'skipped','severity'=>'critical','count'=>0],
            ['status'=>'error','severity'=>'review','count'=>0],
        ]);
        $this->assertSame([
            'total_checks'=>5,'passed'=>1,'flagged'=>2,'skipped'=>1,'errors'=>1,
            'critical_findings'=>3,'review_findings'=>2,
        ], $result);
    }

    public function test_audit_source_has_no_sql_write_invocation(): void
    {
        $source = file_get_contents(app_path('Services/CrmIntegrationAudit.php'));
        $this->assertIsString($source);
        foreach (['->insert(', '->update(', '->delete(', '->truncate(', '->upsert(',
                  'DB::statement(', 'DB::unprepared(', 'Schema::create(', 'Schema::table('] as $operation) {
            $this->assertStringNotContainsString($operation, $source);
        }
    }
}
