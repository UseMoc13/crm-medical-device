<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Command must emit only a source review; never aggregate live business data. */
final class CrmFinancialParityBaselineCommandTest extends TestCase
{
    public function test_json_contract_identifies_access_parity_review_without_business_values(): void
    {
        self::assertSame(0, Artisan::call('crm:reporting:parity-baseline', ['--json' => true]));
        $json = Artisan::output();
        $result = json_decode(trim($json), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('A7.6.5.2', $result['stage']);
        self::assertTrue($result['read_only']);
        self::assertFalse($result['business_rows_read']);
        self::assertSame('REVIEW_REQUIRED', $result['status']);
        self::assertSame(2, $result['summary']['policy_reviews']);
        self::assertSame(0, $result['summary']['failed']);
        self::assertArrayNotHasKey('customer_name', $result);
        self::assertArrayNotHasKey('amount', $result);
        self::assertSame(1, Artisan::call('crm:reporting:parity-baseline', ['--strict' => true]));
    }

    public function test_audit_executes_no_business_sql(): void
    {
        $executed = [];
        DB::listen(static function ($query) use (&$executed): void { $executed[] = $query->sql; });
        self::assertSame(0, Artisan::call('crm:reporting:parity-baseline'));
        self::assertSame([], $executed);
    }
}
