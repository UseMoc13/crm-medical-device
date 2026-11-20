<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/** Verify that Reports page and CSV authorization precedes validation and SQL. */
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

        // The CSV method gained privacy checks before validation. Never infer
        // safety from an arbitrary 350-character slice of a method body.
        $sections = [
            [
                'name' => 'index',
                'start' => 'public function index(Request $request): View',
                'end' => 'public function exportInvoices(Request $request): StreamedResponse',
            ],
            [
                'name' => 'exportInvoices',
                'start' => 'public function exportInvoices(Request $request): StreamedResponse',
                'end' => 'private function assertReportsView(): void',
            ],
        ];

        foreach ($sections as $section) {
            $start = strpos($source, $section['start']);
            self::assertNotFalse($start, 'Missing Reports method '.$section['name']);
            $end = strpos($source, $section['end'], $start + strlen($section['start']));
            self::assertNotFalse($end, 'Missing Reports method boundary '.$section['name']);
            $body = substr($source, $start, $end - $start);

            self::assertMatchesRegularExpression(
                '/\{\s*\$this->assertReportsView\(\);/',
                $body,
                'Reports permission guard must be the first statement of '.$section['name']
            );
            $guardPos = strpos($body, '$this->assertReportsView();');
            $validatePos = strpos($body, '$request->validate(');
            $queryPos = strpos($body, 'DB::table(');
            self::assertNotFalse($guardPos, 'Missing Reports permission guard '.$section['name']);
            self::assertNotFalse($validatePos, 'Missing date validation '.$section['name']);
            self::assertNotFalse($queryPos, 'Missing business query '.$section['name']);
            self::assertLessThan($validatePos, $guardPos, 'Permission must precede validation: '.$section['name']);
            self::assertLessThan($queryPos, $guardPos, 'Permission must precede business SQL: '.$section['name']);
        }
    }

    public function test_report_formulas_and_read_only_queries_remain_present(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2).'/app/Http/Controllers/ReportController.php');
        self::assertIsString($source);
        self::assertStringContainsString("private const ISSUED = ['Sent', 'Paid'];", $source);
        self::assertStringContainsString('CrmReportingDataScope::class', $source);
        self::assertStringContainsString("status IN ('Sent','Paid')", $source);
        self::assertStringContainsString("status = 'Sent' AND due_date < ?", $source);
        self::assertStringContainsString("'text/csv; charset=UTF-8'", $source);
        self::assertStringNotContainsString('->delete(', $source);
        self::assertStringNotContainsString('->truncate(', $source);
    }
}
