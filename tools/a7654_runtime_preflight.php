<?php
$root=dirname(__DIR__);require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass=0;$fail=0;
function a7654_pre(bool $ok,string $name):void{global $pass,$fail;echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;$ok?$pass++:$fail++;}
a7654_pre(class_exists(\App\Services\CrmInvoiceCsvPolicy::class),'CSV pilot policy autoload');
a7654_pre(app(\App\Services\CrmInvoiceCsvPolicy::class) instanceof \App\Services\CrmInvoiceCsvPolicy,'CSV policy resolves');
a7654_pre(class_exists(\App\Services\CrmFinancialRoleEntitlementAudit::class),'Role audit service retained');
a7654_pre(class_exists(\App\Http\Controllers\ReportController::class),'Report controller autoload');
a7654_pre(\Illuminate\Support\Facades\Route::has('reports.index'),'Reports page route');
a7654_pre(\Illuminate\Support\Facades\Route::has('reports.export.invoices'),'Invoice CSV route');
a7654_pre(array_key_exists('crm:reporting:role-entitlements', \Illuminate\Support\Facades\Artisan::all()),'Role entitlement command registered');
a7654_pre(is_bool(config('crm_reporting.csv_invoice_all_scope_pilot_enabled')),'Opt-in configuration boolean');
$enabled=(bool)config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false);
echo 'CSV strict pilot: '.($enabled?'ON (verify approval and grants)':'OFF (legacy CSV permissions unchanged)').PHP_EOL;
echo "A7.6.5.4 runtime: {$pass} PASS, {$fail} FAIL. No business records read.\n";exit($fail?1:0);
