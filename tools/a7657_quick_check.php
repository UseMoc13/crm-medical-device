<?php
/** Static-only A7.6.5.7 smoke; never bootstraps CRM database. */
declare(strict_types=1);
$root=dirname(__DIR__);
$checks=[];
$service='app/Services/CrmReportPrivacyPilot.php';
$controller='app/Http/Controllers/ReportController.php';
$view='resources/views/reports/index.blade.php';
$config='config/crm_reporting.php';
$tests=['tests/Unit/CrmReportPrivacyPilotPolicyTest.php','tests/Unit/CrmReportPrivacyPilotSourceTest.php','tests/Feature/CrmReportPrivacyPilotHttpTest.php'];
foreach ([$service,$controller,$view,$config,...$tests,'tools/install_a7657_report_privacy.php','tools/a7657_quick_check.php','tools/a7657_runtime_preflight.php'] as $p) {
    $checks['File '.$p]=is_file($root.'/'.$p);
    if (str_ends_with($p,'.php')) {
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$p).' 2>&1',$lines,$status);
        $checks['PHP syntax '.$p]=$status===0;
    }
}
$s=file_get_contents($root.'/'.$service);$c=file_get_contents($root.'/'.$controller);
$v=file_get_contents($root.'/'.$view);$cfg=file_get_contents($root.'/'.$config);
$checks['Flag defaults OFF']=str_contains($cfg,"env('CRM_REPORT_DETAIL_PRIVACY_PILOT_ENABLED', false)");
$checks['Shared policy decision']=str_contains($s,'CrmInvoiceCsvPolicy') && str_contains($s,'customer_product_details');
$checks['Top Customers query gated']=str_contains($c,'$topCustomers = $canViewReportDetails ? app(');
$checks['Top Products query gated']=str_contains($c,'$topProducts = $canViewReportDetails ? app(');
$checks['No sensitive Top queries when denied']=substr_count($c,'->get() : collect();')===2;
$checks['CSV direct URL guarded by either flag']=str_contains($c,"|| (bool) config('crm_reporting.report_customer_product_privacy_pilot_enabled', false)");
$checks['Prior Reports auth gate kept']=substr_count($c,'$this->assertReportsView();')===2;
$checks['Prior CSV policy service kept']=str_contains($c,'CrmInvoiceCsvPolicy::class');
$checks['Report financial aggregates untouched']=str_contains($c,'COALESCE(SUM(total_amount) FILTER');
$checks['View hides both sections']=substr_count($v,'@if($canViewReportDetails)')===2;
$checks['View displays restricted text']=substr_count($v,'Invoice details restricted for this account.')===2;
$checks['View hides forbidden CSV link']=str_contains($v,'@if($canExportInvoiceCsv)');
$checks['Prior chart and customer product UI retained']=str_contains($v,'Revenue by Month') && str_contains($v,'Top Customers') && str_contains($v,'Top Products');
$checks['No migrations or role writes']=!preg_match('/DB::(?:insert|update|delete)|Schema::(?:create|drop|table)/',$s);
$checks['Installer supports dry-run']=str_contains(file_get_contents($root.'/tools/install_a7657_report_privacy.php'),'--dry-run');
$p=$f=0;foreach($checks as $label=>$ok){$ok?$p++:$f++;echo($ok?'[PASS] ':'[FAIL] ').$label."\n";}echo"A7.6.5.7 static: {$p} PASS, {$f} FAIL. No business DB queries.\n";exit($f?1:0);
