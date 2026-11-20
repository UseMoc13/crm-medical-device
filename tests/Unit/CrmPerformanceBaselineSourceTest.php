<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CrmPerformanceBaselineSourceTest extends TestCase
{
    public function test_baseline_does_not_analyze_or_mutate_business_records(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/tools/a7751_pg_baseline.php');
        self::assertStringContainsString("SET TRANSACTION READ ONLY", $source);
        self::assertStringContainsString('EXPLAIN (FORMAT JSON)', $source);
        self::assertStringNotContainsString('EXPLAIN (ANALYZE', $source);
        self::assertDoesNotMatchRegularExpression('/\$connection->(?:insert|update|delete|unprepared)\s*\(/i', $source);
        self::assertStringContainsString("'--run'", $source);
    }
}
