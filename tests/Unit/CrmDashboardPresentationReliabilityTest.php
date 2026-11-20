<?php
namespace Tests\Unit;
use App\Services\CrmDashboardViewData;
use PHPUnit\Framework\TestCase;
final class CrmDashboardPresentationReliabilityTest extends TestCase {
 public function test_hidden_values_are_not_shown_and_authorized_counts_are_formatted():void {
  $result=CrmDashboardViewData::present([
   'customers.total'=>['status'=>'restricted','unit'=>'count','value'=>999999],
   'leads.active'=>['status'=>'visible','unit'=>'count','value'=>1200],
   'opportunities.total'=>['status'=>'unavailable_schema','unit'=>'count','value'=>100],
   'service_tickets.total'=>['status'=>'visible','unit'=>'count','value'=>0],
   'invoices.invoiced'=>['status'=>'restricted','unit'=>'IDR','value'=>'999999.00'],
  ]);
  self::assertSame('—',$result['stats']['customers']);self::assertSame('1.200',$result['stats']['leads']);
  self::assertSame('—',$result['stats']['opportunities']);self::assertSame('0',$result['stats']['service_tickets']);
  self::assertSame([],$result['salesCards']);
 }
 public function test_currency_string_is_not_rounded_through_float():void {
  self::assertSame('123.456.789,5',CrmDashboardViewData::formatDecimalIdr('123456789.50'));
  self::assertSame('-2,25',CrmDashboardViewData::formatDecimalIdr('-2.2500'));
  self::assertNull(CrmDashboardViewData::formatDecimalIdr('1e9'));
 }
}
