<?php

namespace Tests\Unit;

use App\Services\CrmDashboardViewData;
use PHPUnit\Framework\TestCase;

final class CrmDashboardViewDataTest extends TestCase
{
    public function test_restricted_or_missing_metrics_never_display_unauthorized_values(): void
    {
        $cards = CrmDashboardViewData::present([
            'customers.total' => ['status' => 'visible', 'unit' => 'count', 'value' => 12],
            'leads.active' => ['status' => 'restricted', 'unit' => 'count', 'value' => 987654],
            'opportunities.total' => ['status' => 'unavailable_schema', 'unit' => 'count', 'value' => 42],
            'invoices.invoiced' => ['status' => 'restricted', 'unit' => 'IDR', 'value' => '9999999.00'],
        ]);
        self::assertSame('12', $cards['stats']['customers']);
        self::assertSame('—', $cards['stats']['leads']);
        self::assertSame('—', $cards['stats']['opportunities']);
        self::assertSame('—', $cards['stats']['service_tickets']);
        self::assertSame([], $cards['salesCards']);
        self::assertStringNotContainsString('987654', json_encode($cards));
        self::assertStringNotContainsString('9999999', json_encode($cards));
    }

    public function test_sales_overview_shows_only_authorized_kpis_without_floating_point(): void
    {
        $cards = CrmDashboardViewData::present([
            'opportunities.total' => ['status'=>'visible', 'unit'=>'count', 'value'=>3],
            'invoices.invoiced' => ['status'=>'visible', 'unit'=>'IDR', 'value'=>'9007199254740993123456.50'],
            'invoices.overdue' => ['status'=>'restricted', 'unit'=>'IDR', 'value'=>'11111'],
        ]);
        self::assertCount(2, $cards['salesCards']);
        self::assertSame('3', $cards['salesCards'][0]['value']);
        self::assertSame('Rp 9.007.199.254.740.993.123.456,5', $cards['salesCards'][1]['value']);
        self::assertStringNotContainsString('11111', json_encode($cards));
        self::assertSame('0', CrmDashboardViewData::formatDecimalIdr('0000.000'));
        self::assertNull(CrmDashboardViewData::formatDecimalIdr('1e12'));
        self::assertNull(CrmDashboardViewData::formatDecimalIdr('nan'));
    }
}
