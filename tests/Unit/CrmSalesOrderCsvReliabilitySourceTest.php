<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Additional source-level safety gate; HTTP/SQLite behavior is tested separately. */
final class CrmSalesOrderCsvReliabilitySourceTest extends TestCase
{
    public function test_export_remains_batched_ordered_and_formula_safe(): void
    {
        $file = dirname(__DIR__, 2).'/app/Http/Controllers/SalesOrderController.php';
        $code = (string) file_get_contents($file);
        self::assertStringContainsString("SalesOrder::with(['quotation.opportunity.customer'])", $code);
        self::assertStringContainsString("foreach (\$query->orderByDesc('sales_order_id')->lazy(200) as \$order)", $code);
        self::assertStringContainsString('CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10])', $code);
        self::assertStringContainsString('$orders = $query->limit(2000)->get();', $code);
    }
}
