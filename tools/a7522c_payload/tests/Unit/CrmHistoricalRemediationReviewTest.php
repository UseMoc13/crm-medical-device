<?php

namespace Tests\Unit;

use App\Services\CrmHistoricalRemediationReview;
use App\Services\CrmIntegrationInvestigation;
use Tests\TestCase;

final class CrmHistoricalRemediationReviewTest extends TestCase
{
    public function test_every_target_has_an_explicit_manual_policy_and_no_auto_apply(): void
    {
        $policies = CrmHistoricalRemediationReview::policies();
        $this->assertSame(CrmIntegrationInvestigation::TARGETS, array_keys($policies));
        $this->assertCount(4, $policies);
        foreach ($policies as $policy) {
            $this->assertContains($policy['priority'], ['high', 'medium']);
            $this->assertNotEmpty($policy['decision']);
            $this->assertNotEmpty($policy['guidance']);
        }
        $this->assertSame('business_meaning_required', $policies['billing.unknown_status']['decision']);
        $this->assertStringContainsString('Never guess', $policies['billing.unknown_status']['guidance']);
    }

    public function test_review_code_contains_no_database_mutation_operations(): void
    {
        $path = app_path('Services/CrmHistoricalRemediationReview.php');
        $content = (string) file_get_contents($path);
        // Call sites, not prose, must not mutate data.
        $this->assertDoesNotMatchRegularExpression('/\-\>(?:insert|update|delete|truncate|upsert|increment|decrement)\s*\(/i', $content);
        $this->assertDoesNotMatchRegularExpression('/DB::(?:statement|unprepared|insert|update|delete)\s*\(/i', $content);
        $this->assertStringContainsString("'auto_apply_allowed' => false", $content);
    }
}
