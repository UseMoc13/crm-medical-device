<?php

namespace Tests\Unit;

use App\Services\CrmStagingInvoiceParity;
use PHPUnit\Framework\TestCase;

final class CrmStagingInvoiceParityGuardTest extends TestCase
{
    public function test_requires_every_staging_condition_and_refuses_active_scopes(): void
    {
        $ok = CrmStagingInvoiceParity::decision('staging', 'pgsql', true, true, true, false);
        self::assertTrue($ok['allowed']);
        self::assertSame([], $ok['reasons']);

        foreach ([
            ['production', 'pgsql', true, true, true, false],
            ['local', 'pgsql', true, true, true, false],
            ['testing', 'sqlite', true, true, true, false],
            ['staging', 'sqlite', true, true, true, false],
            ['staging', 'pgsql', false, true, true, false],
            ['staging', 'pgsql', true, false, true, false],
            ['staging', 'pgsql', true, true, false, false],
            ['staging', 'pgsql', true, true, true, true],
        ] as $case) {
            self::assertFalse(CrmStagingInvoiceParity::decision(...$case)['allowed']);
        }
    }

    public function test_decimal_comparison_never_uses_float_conversion(): void
    {
        self::assertSame('42.75', CrmStagingInvoiceParity::decimalString('00042.75000'));
        self::assertSame('0', CrmStagingInvoiceParity::decimalString('0.000'));
        self::assertSame('0', CrmStagingInvoiceParity::decimalString('-000.00'));
        self::assertSame('-5.2', CrmStagingInvoiceParity::decimalString('-00005.200'));
        self::assertSame('100000000000000000000.1234', CrmStagingInvoiceParity::decimalString('100000000000000000000.123400'));
        self::assertTrue(CrmStagingInvoiceParity::validDate('2026-11-19'));
        self::assertFalse(CrmStagingInvoiceParity::validDate('2026-02-30'));
        self::assertFalse(CrmStagingInvoiceParity::validDate("2026-11-19' OR 1=1"));
    }
}
