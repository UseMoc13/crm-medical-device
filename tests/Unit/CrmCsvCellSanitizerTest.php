<?php

namespace Tests\Unit;

use App\Support\CrmCsvCellSanitizer;
use PHPUnit\Framework\TestCase;

/** No Laravel app or DB is bootstrapped by these tests. */
final class CrmCsvCellSanitizerTest extends TestCase
{
    public function test_formula_triggers_are_written_as_text(): void
    {
        $input = ['=SUM(A1:A2)', '+cmd', '-1+2', '@HYPERLINK("x")'];
        self::assertSame(["'=SUM(A1:A2)", "'+cmd", "'-1+2", "'@HYPERLINK(\"x\")"], CrmCsvCellSanitizer::row($input, [0,1,2,3]));
    }

    public function test_leading_invisible_characters_cannot_bypass_protection(): void
    {
        foreach (["\t=1+1", "\r\n+1+1", " \t@a", "\u{00A0}=1", "\u{200B}=1", "\u{FEFF}-1"] as $text) {
            self::assertSame("'" . $text, CrmCsvCellSanitizer::row([$text], [0])[0]);
        }
    }

    public function test_normal_text_dates_numbers_and_nulls_are_unchanged(): void
    {
        $input = ['Customer A', 'INV-001', '2026-11-20', 'Paid', 500, -25.5, 0, null, '', "'already text"];
        self::assertSame($input, CrmCsvCellSanitizer::row($input, [0,1,2,3,4,5,6,7,8,9]));
    }

    public function test_numeric_database_decimal_strings_are_never_modified(): void
    {
        $input = ['=SUSPICIOUS', '-42.00', '-1200000.50', 'Customer'];
        self::assertSame(["'=SUSPICIOUS", '-42.00', '-1200000.50', 'Customer'], CrmCsvCellSanitizer::row($input, [0, 3]));
    }

    public function test_internal_formula_characters_are_not_overescaped(): void
    {
        self::assertSame(['B=2', 'Name @ location', 'X - Y'], CrmCsvCellSanitizer::row(['B=2', 'Name @ location', 'X - Y'], [0,1,2]));
    }

    public function test_invalid_utf8_fails_closed(): void
    {
        $invalid = "\xC3\x28=1+1";
        self::assertSame("'" . $invalid, CrmCsvCellSanitizer::row([$invalid], [0])[0]);
    }

    public function test_csv_roundtrip_keeps_formulas_as_text_and_amounts_numeric(): void
    {
        $handle = fopen('php://temp', 'w+');
        self::assertIsResource($handle);
        $fields = CrmCsvCellSanitizer::row(['=HYPERLINK("evil")', 'Client, A', '-123.45'], [0,1]);
        fputcsv($handle, $fields);
        rewind($handle);
        self::assertSame(["'=HYPERLINK(\"evil\")", 'Client, A', '-123.45'], fgetcsv($handle));
        fclose($handle);
    }
}
