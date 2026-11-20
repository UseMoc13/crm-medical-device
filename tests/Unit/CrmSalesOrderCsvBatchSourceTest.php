<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Code-level regression checks; no Laravel boot and no DB. */
final class CrmSalesOrderCsvBatchSourceTest extends TestCase
{
    private function controller(): string
    {
        $path = dirname(__DIR__, 2) . '/app/Http/Controllers/SalesOrderController.php';
        return (string) file_get_contents($path);
    }

    public function test_sales_order_csv_batches_eager_loaded_relations(): void
    {
        $source = $this->controller();
        self::assertStringContainsString("SalesOrder::with(['quotation.opportunity.customer'])", $source);
        self::assertStringContainsString("foreach (\$query->orderByDesc('sales_order_id')->lazy(200) as \$order) {", $source);
        self::assertStringNotContainsString('foreach ($query->cursor() as $order) {', $source);
    }

    public function test_csv_security_and_pdf_export_remain_unchanged(): void
    {
        $source = $this->controller();
        self::assertStringContainsString('CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10])', $source);
        self::assertStringContainsString("'format' => ['required', 'in:pdf,csv']", $source);
        self::assertStringContainsString('$orders = $query->limit(2000)->get();', $source);
    }
}
