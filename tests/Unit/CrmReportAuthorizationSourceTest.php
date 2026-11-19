<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Checks both report entrypoints invoke the same permission guard BEFORE validation. */
final class CrmReportAuthorizationSourceTest extends TestCase
{
    public function test_report_page_and_csv_are_both_guarded_before_queries(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ReportController.php');
        self::assertIsString($source);
        self::assertSame(2, substr_count($source, '$this->assertReportsView();'));
        self::assertStringContainsString("->canView(Auth::guard('web')->user(), 'reports')", $source);
        self::assertStringContainsString('abort_unless(', $source);
        self::assertStringContainsString('use App\\Services\\CrmNavigationAccess;', $source);

        foreach (['index' => 'View', 'exportInvoices' => 'StreamedResponse'] as $method => $returnType) {
            $start = strpos($source, 'public function '.$method.'(Request $request): '.$returnType);
            self::assertNotFalse($start, 'Missing Reports method '.$method);
            $body = substr($source, $start, 350);
            self::assertMatchesRegularExpression('/\\{\\s*\\$this->assertReportsView\\(\\);/', $body);
            self::assertLessThan(strpos($body, '$request->validate('), strpos($body, '$this->assertReportsView();'));
        }
    }

    public function test_report_formulas_and_read_only_queries_remain_present(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ReportController.php');
        self::assertStringContainsString("private const ISSUED = ['Sent', 'Paid'];", $source);
        self::assertStringContainsString('CrmReportingDataScope::class', $source);
        self::assertStringContainsString("status IN ('Sent','Paid')", $source);
        self::assertStringContainsString("status = 'Sent' AND due_date < ?", $source);
        self::assertStringContainsString("'text/csv; charset=UTF-8'", $source);
        self::assertStringNotContainsString('->delete(', $source);
        self::assertStringNotContainsString('->truncate(', $source);
    }
}
