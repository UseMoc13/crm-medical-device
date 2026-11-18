<?php

namespace Tests\Unit;

use App\Services\CrmKpiSourceReadiness;
use PHPUnit\Framework\TestCase;

final class CrmKpiSourceCatalogTest extends TestCase
{
    public function test_sources_are_unique_explicit_and_permitted(): void
    {
        $metrics = CrmKpiSourceReadiness::METRICS;
        self::assertGreaterThanOrEqual(10, count($metrics));
        self::assertCount(count(array_unique(array_keys($metrics))), $metrics);
        foreach ($metrics as $id => $metric) {
            self::assertMatchesRegularExpression('/^[a-z_]+\.[a-z_]+$/', $id);
            self::assertNotEmpty($metric['columns']);
            self::assertContains($metric['kind'], ['snapshot', 'period']);
            self::assertStringStartsWith('crm_page.', $metric['permission']);
            self::assertStringEndsWith('.view', $metric['permission']);
            self::assertNotEmpty($metric['semantics']);
        }
    }

    public function test_baseline_never_reads_raw_business_values_or_writes(): void
    {
        $source = file_get_contents(__DIR__.'/../../app/Services/CrmKpiSourceReadiness.php');
        self::assertStringContainsString('Schema::hasTable', $source);
        self::assertStringContainsString('Schema::hasColumn', $source);
        self::assertStringNotContainsString('DB::table(', $source);
        self::assertDoesNotMatchRegularExpression('/->(?:insert|update|delete|truncate|create)\s*\(/i', $source);
        self::assertArrayHasKey('dashboard.activity_preview', CrmKpiSourceReadiness::SOURCE_REVIEWS);
        self::assertArrayHasKey('reports.controller_filename_case', CrmKpiSourceReadiness::SOURCE_REVIEWS);
    }
}
