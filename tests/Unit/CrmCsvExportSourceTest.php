<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Guard the actual export call sites, not merely the sanitizing helper. */
final class CrmCsvExportSourceTest extends TestCase
{
    public function test_invoice_export_uses_shared_sanitizer_without_changing_scope_policy(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ReportController.php');
        self::assertStringContainsString('fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5]));', $source);
        self::assertStringContainsString('$this->assertReportsView();', $source);
        self::assertStringContainsString('CrmInvoiceCsvPolicy::class', $source);
        self::assertStringContainsString('CrmReportingDataScope::class', $source);
    }

    public function test_sales_order_export_uses_shared_sanitizer(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/SalesOrderController.php');
        self::assertStringContainsString('fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10]));', $source);
        self::assertStringContainsString('foreach ($query->cursor() as $order)', $source);
    }
}
