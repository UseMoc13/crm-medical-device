<?php
declare(strict_types=1);
/* Static checks only; no DB or Laravel bootstrap. */
$root=dirname(__DIR__);$pass=0;$fail=0;
$files=['app/Http/Controllers/SalesOrderController.php','app/Http/Controllers/ReportController.php','app/Services/CrmDashboardViewData.php','tools/a7762_apply_pagination_patch.php','tests/Feature/CrmSalesOrderPaginationSqliteIsolationTest.php','tests/Unit/CrmDashboardPresentationReliabilityTest.php','tests/Unit/CrmReportingReliabilityGuardTest.php'];
foreach($files as $rel){$path=$root.'/'.$rel;$ok=is_file($path);echo ($ok?'[PASS]':'[FAIL]')." File $rel\n";$ok?$pass++:$fail++;if(!$ok)continue;
 $o=[];exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1',$o,$code);$ok=($code===0);echo ($ok?'[PASS]':'[FAIL]')." Syntax $rel\n";$ok?$pass++:$fail++;}
if($fail===0){$c=(string)file_get_contents($root.'/app/Http/Controllers/SalesOrderController.php');$r=(string)file_get_contents($root.'/app/Http/Controllers/ReportController.php');
 $checks=[
  'Index pagination tie-break'=>(bool)preg_match('~\$orders\s*=\s*\$query\s*->orderByDesc\(\x27sales_order_id\x27\)\s*->paginate\(10\)~',$c),
  'CSV lazy(200) unchanged'=>str_contains($c,"->orderByDesc('sales_order_id')->lazy(200)"),
  'Sales Order CSV sanitizer remains'=>str_contains($c,'CrmCsvCellSanitizer::row('),
  'PDF export remains'=>str_contains($c,'SalesOrderReportPdf::make('),
  'Report access guard remains'=>str_contains($r,'$this->assertReportsView();'),
  'Report privacy pilot unchanged'=>str_contains($r,'CrmReportAggregatePrivacyPilot::class'),
  'Invoice CSV sanitizer remains'=>str_contains($r,'CrmCsvCellSanitizer::row('),
  'SQLite in-memory fixture'=>str_contains((string)file_get_contents($root.'/tests/Feature/CrmSalesOrderPaginationSqliteIsolationTest.php'),"':memory:'"),
 ];foreach($checks as $name=>$ok){echo ($ok?'[PASS]':'[FAIL]')." $name\n";$ok?$pass++:$fail++;}}
echo "A7.7.6.2 static: $pass PASS; $fail FAIL. No DB.\n";exit($fail===0?0:1);
