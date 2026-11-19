<?php
/** Runtime policy/schema-free preflight; no business queries. */
declare(strict_types=1);
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ok=0;$failed=0;
$check=static function(bool $passed,string $label) use (&$ok,&$failed): void {
    echo '['.($passed?'PASS':'FAIL').'] '.$label."\n";
    $passed?$ok++:$failed++;
};
$check(class_exists(\App\Services\CrmReportAggregatePrivacyPilot::class),'Aggregate policy autoloads');
$check(app(\App\Services\CrmReportAggregatePrivacyPilot::class) instanceof \App\Services\CrmReportAggregatePrivacyPilot,'Aggregate policy resolves');
$check(app(\App\Services\CrmInvoiceCsvPolicy::class) instanceof \App\Services\CrmInvoiceCsvPolicy,'Existing CSV policy resolves');
$check(class_exists(\App\Http\Controllers\ReportController::class),'Report controller resolves');
$check(\Illuminate\Support\Facades\Route::has('reports.index'),'Reports page route available');
$check(\Illuminate\Support\Facades\Route::has('reports.export.invoices'),'Reports CSV route available');
$check(is_bool(config('crm_reporting.report_aggregate_privacy_pilot_enabled')),'Aggregate flag boolean');
$check(\App\Services\CrmReportAggregatePrivacyPilot::decide(false,[])['financial']===true,'Flag OFF preserves aggregate visibility');
$check(\App\Services\CrmReportAggregatePrivacyPilot::decide(true,[])['financial']===false,'Flag ON fails closed for missing grant');
$check(\App\Services\CrmScopedKpiQuery::scopeDecision('invoices',[])==='restricted','Missing Invoice grant remains restricted');
$check(config('crm_reporting.report_aggregate_privacy_pilot_enabled')===false,'Local aggregate pilot currently OFF');
echo "A7.6.5.8 runtime: {$ok}/".($ok+$failed)." PASS; {$failed} FAIL. No business rows read.\n";
exit($failed?1:0);
