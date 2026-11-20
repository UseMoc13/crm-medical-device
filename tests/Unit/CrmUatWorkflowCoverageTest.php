<?php
declare(strict_types=1);
namespace Tests\Unit;
use App\Support\CrmUatWorkflowCoverage;
use PHPUnit\Framework\TestCase;

/** Pure metadata assertions; no Laravel bootstrap or business database. */
final class CrmUatWorkflowCoverageTest extends TestCase
{
    public function test_all_fifteen_scenarios_have_fixed_routes_and_evidence(): void
    {
        $cases=CrmUatWorkflowCoverage::scenarios();
        self::assertCount(15,$cases);
        for($i=1;$i<=15;$i++){
            $id=sprintf('UAT-%02d',$i);
            self::assertArrayHasKey($id,$cases);
            self::assertNotEmpty($cases[$id]['routes']);
            self::assertNotEmpty($cases[$id]['evidence']);
            foreach($cases[$id]['routes'] as [$name,$method]){
                self::assertNotSame('',$name);
                self::assertContains($method,['GET','POST','PATCH','PUT','DELETE']);
            }
        }
    }
    public function test_core_workflow_methods_and_policy_holds_cannot_be_relabeled_pass(): void
    {
        $cases=CrmUatWorkflowCoverage::scenarios();
        self::assertContains(['deliveries.change-status','PATCH'],$cases['UAT-06']['routes']);
        self::assertContains(['installations.change-status','PATCH'],$cases['UAT-07']['routes']);
        self::assertContains(['invoices.status','PATCH'],$cases['UAT-11']['routes']);
        self::assertContains(['installations.warranties.activate','POST'],$cases['UAT-08']['routes']);
        self::assertSame('HOLD_POLICY',$cases['UAT-13']['gate']);
        self::assertSame('HOLD_POLICY',$cases['UAT-14']['gate']);
        self::assertSame('WORKFLOW_BASELINE_READY_UAT_NOT_EXECUTED',CrmUatWorkflowCoverage::decision(0,0));
        self::assertSame('HOLD_PREFLIGHT_GAPS',CrmUatWorkflowCoverage::decision(1,0));
        self::assertSame('HOLD_PREFLIGHT_GAPS',CrmUatWorkflowCoverage::decision(0,1));
    }
}
