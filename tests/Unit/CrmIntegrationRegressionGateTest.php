<?php

namespace Tests\Unit;

use App\Services\CrmIntegrationRegressionGate;
use PHPUnit\Framework\TestCase;

final class CrmIntegrationRegressionGateTest extends TestCase
{
    private function input(): array
    {
        return [
            'audit'=>['total_checks'=>37,'passed'=>37,'flagged'=>0,'skipped'=>0,'errors'=>0,'critical_findings'=>0,'review_findings'=>0],
            'investigation'=>['skipped'=>0,'errors'=>0,'overlapping_delivery_count'=>0],
            'review'=>['skipped'=>0,'errors'=>0,'automatic_corrections'=>0],
            'missing_schema'=>[],'missing_routes'=>[],'missing_events'=>[],'missing_subjects'=>[],'inconsistent_rules'=>[],
        ];
    }

    public function test_ready_requires_no_errors_skips_or_findings(): void
    {
        $this->assertSame('READY', CrmIntegrationRegressionGate::assess($this->input())['status']);
    }

    public function test_historical_findings_are_watch_not_failure(): void
    {
        $input=$this->input(); $input['audit']['flagged']=4; $input['audit']['passed']=33;
        $input['audit']['critical_findings']=2; $input['audit']['review_findings']=2;
        $result=CrmIntegrationRegressionGate::assess($input);
        $this->assertSame('WATCH',$result['status']);
        $this->assertSame(2,$result['critical_findings']);
    }

    public function test_absent_schema_or_skips_never_count_as_ready(): void
    {
        $input=$this->input(); $input['missing_schema']=['workspace_notifications'];
        $this->assertSame('INCOMPLETE',CrmIntegrationRegressionGate::assess($input)['status']);
        $input=$this->input(); $input['investigation']['skipped']=4;
        $this->assertSame('INCOMPLETE',CrmIntegrationRegressionGate::assess($input)['status']);
    }

    public function test_query_errors_or_scanner_disagreement_block_release(): void
    {
        $input=$this->input(); $input['review']['errors']=1;
        $this->assertSame('BLOCKED',CrmIntegrationRegressionGate::assess($input)['status']);
        $input=$this->input(); $input['inconsistent_rules']=['installation.unknown_status'];
        $this->assertSame('BLOCKED',CrmIntegrationRegressionGate::assess($input)['status']);
    }

    public function test_gate_source_excludes_database_mutations_and_details(): void
    {
        $source=file_get_contents(__DIR__.'/../../app/Services/CrmIntegrationRegressionGate.php');
        $this->assertStringContainsString('->run(false)', $source);
        $this->assertStringNotContainsString('->run(true)', $source);
        $this->assertDoesNotMatchRegularExpression('/\b(?:insert|update|delete|truncate|drop|migrate|seed)\s*\(/i', $source);
    }
}
