<?php
namespace Tests\Unit;
use App\Services\CrmReportAggregatePrivacyPilot;
use PHPUnit\Framework\TestCase;
final class CrmReportingReliabilityGuardTest extends TestCase {
 public function test_aggregate_pilot_does_not_grant_missing_scopes():void {
  $flags=CrmReportAggregatePrivacyPilot::decide(true,['financial'=>true]);
  self::assertTrue($flags['financial']);self::assertFalse($flags['operations']);self::assertFalse($flags['pipeline']);self::assertFalse($flags['warranty']);
 }
 public function test_report_permission_precedes_business_queries_and_csv_remains_protected():void {
  $s=(string)file_get_contents(dirname(__DIR__,2).'/app/Http/Controllers/ReportController.php');
  $i=strpos($s,'public function index(');$e=strpos($s,'public function exportInvoices(');
  self::assertNotFalse($i);self::assertNotFalse($e);
  $indexPart=substr($s,$i,$e-$i);$exportPart=substr($s,$e);
  $gi=strpos($indexPart,'$this->assertReportsView();');$qi=strpos($indexPart,'DB::table(');
  $ge=strpos($exportPart,'$this->assertReportsView();');$qe=strpos($exportPart,'DB::table(');
  foreach([$gi,$qi,$ge,$qe] as $n)self::assertNotFalse($n);
  self::assertLessThan($qi,$gi);self::assertLessThan($qe,$ge);
  self::assertStringContainsString('CrmReportAggregatePrivacyPilot::class',$s);
  self::assertStringContainsString('CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5])',$s);
 }
}
