<?php

namespace Tests\Feature;

use App\Services\CrmIntegrationRegressionGate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** No RefreshDatabase, migration, seeder or business-data mutations. */
final class CrmIntegrationRegressionCommandTest extends TestCase
{
    public function test_json_contract_has_no_record_ids_and_correct_exit(): void
    {
        $this->assertArrayHasKey('crm:integration:regression', Artisan::all());
        $exit=Artisan::call('crm:integration:regression', ['--json'=>true]);
        $output=trim(Artisan::output()); // consuming buffer; retrieve ONCE
        $this->assertNotSame('', $output);
        $report=json_decode($output,true,512,JSON_THROW_ON_ERROR);
        $this->assertTrue($report['read_only']);
        $this->assertSame('A7.5.4.1',$report['stage']);
        $this->assertContains($report['status'],['READY','WATCH','INCOMPLETE','BLOCKED']);
        $this->assertCount(4,$report['targets']);
        $this->assertSame(0,$report['historical_fixes_applied']);
        $this->assertArrayNotHasKey('details', $report);
        $this->assertStringNotContainsString('"customer_name":', $output);
        $this->assertStringNotContainsString('"delivery_id":', $output);
        $this->assertStringNotContainsString('"record_id":', $output);
        $this->assertSame(in_array($report['status'],['BLOCKED','INCOMPLETE'],true) ? 1 : 0,$exit,$output);
    }

    public function test_gate_emits_only_read_queries(): void
    {
        $unsafe=[];
        DB::listen(static function ($event) use (&$unsafe) {
            if (!preg_match('/^\s*(select|with|show|pragma|explain)\b/i',$event->sql)) $unsafe[]=$event->sql;
        });
        app(CrmIntegrationRegressionGate::class)->run();
        $this->assertSame([], $unsafe, 'Regression must never mutate database.');
    }
}
