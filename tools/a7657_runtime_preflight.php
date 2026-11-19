<?php
/** Bootstrap-only A7.6.5.7 preflight: no user or business queries. */
declare(strict_types=1);
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks=[];
$checks['Detail privacy service resolves']=app(\App\Services\CrmReportPrivacyPilot::class) instanceof \App\Services\CrmReportPrivacyPilot;
$checks['Existing CSV policy resolves']=app(\App\Services\CrmInvoiceCsvPolicy::class) instanceof \App\Services\CrmInvoiceCsvPolicy;
$checks['Reports controller autoloads']=class_exists(\App\Http\Controllers\ReportController::class);
$checks['Reports route available']=\Illuminate\Support\Facades\Route::has('reports.index');
$checks['CSV route available']=\Illuminate\Support\Facades\Route::has('reports.export.invoices');
$checks['Detail pilot boolean flag']=is_bool(config('crm_reporting.report_customer_product_privacy_pilot_enabled',false));
$checks['Default OFF retains HTML and CSV']=\App\Services\CrmReportPrivacyPilot::decide(false,false,false)['invoice_csv']===true;
$checks['Detail ON denies HTML and CSV without all']=\App\Services\CrmReportPrivacyPilot::decide(true,false,false)['customer_product_details']===false && \App\Services\CrmReportPrivacyPilot::decide(true,false,false)['invoice_csv']===false;
$checks['CSV-only mode does not claim to hide HTML']=\App\Services\CrmReportPrivacyPilot::decide(false,true,false)['customer_product_details']===true;
$p=$f=0;foreach($checks as $name=>$ok){$ok?$p++:$f++;echo($ok?'[PASS] ':'[FAIL] ').$name."\n";}echo"A7.6.5.7 runtime: {$p} PASS, {$f} FAIL. No CRM business queries.\n";exit($f?1:0);
