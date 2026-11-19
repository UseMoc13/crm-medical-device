<?php
/** A7.6.5.8 static readiness; no Laravel bootstrap or DB queries. */
declare(strict_types=1);
$root = dirname(__DIR__);
$files = [
    'app/Http/Controllers/ReportController.php',
    'resources/views/reports/index.blade.php',
    'config/crm_reporting.php',
    'app/Services/CrmReportAggregatePrivacyPilot.php',
    'tests/Unit/CrmReportAggregatePrivacyPolicyTest.php',
    'tests/Unit/CrmReportAggregatePrivacySourceTest.php',
    'tests/Feature/CrmReportAggregatePrivacyIsolationTest.php',
    'tools/install_a7658_aggregate_privacy.php',
    'tools/a7658_quick_check.php',
    'tools/a7658_runtime_preflight.php',
];
$good=0;$bad=0;
$check=static function(bool $ok,string $label) use (&$good,&$bad): void {
    echo '['.($ok?'PASS':'FAIL').'] '.$label."\n";
    $ok?$good++:$bad++;
};
foreach ($files as $file) {
    $full=$root.'/'.$file;
    $check(is_file($full),'File '.$file);
    if (is_file($full)) {
        $result=[];$code=-1;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1', $result,$code);
        $check($code===0, 'PHP syntax '.$file);
    }
}
$source=static fn(string $f): string => (string) file_get_contents($root.'/'.$f);
$controller=$source('app/Http/Controllers/ReportController.php');
$view=$source('resources/views/reports/index.blade.php');
$config=$source('config/crm_reporting.php');
$policy=$source('app/Services/CrmReportAggregatePrivacyPilot.php');
$manifest=is_file($root.'/tools/a7658_install_state.json');
$check($manifest,'Install manifest exists');
$check(str_contains($config,"'report_aggregate_privacy_pilot_enabled' => (bool) env('CRM_REPORT_AGGREGATE_PRIVACY_PILOT_ENABLED', false)"),'Pilot flag default OFF');
$check(str_contains($controller,'$this->assertReportsView();'),'Reports base access guard preserved');
foreach (['financial'=>'$kpi = $canViewFinancial ?', 'breakdown'=>'$statusBreakdown = $canViewFinancial ?',
    'monthly'=>'$monthly = $canViewFinancial ?', 'tickets'=>'$tickets = $canViewOperations ?',
    'maintenance'=>'$maintenance = $canViewOperations ?', 'pipeline'=>'$pipeline = $canViewPipeline ?',
    'warranty'=>'$warranty = $canViewWarranty ?'] as $label=>$needle) {
    $check(str_contains($controller,$needle),'Query guard '.$label);
}
$check(str_contains($controller,'$canViewReportDetails = $reportPrivacy[\'customer_product_details\'] && $canViewFinancial;'),'Customer/product detail depends on finance gate');
$check(str_contains($controller,'$canExportInvoiceCsv = $reportPrivacy[\'invoice_csv\'] && $canViewFinancial;'),'CSV button depends on finance gate');
$check(str_contains($controller,"config('crm_reporting.report_aggregate_privacy_pilot_enabled', false)"),'Direct CSV endpoint strict when aggregate flag ON');
$check(str_contains($controller,'CrmInvoiceCsvPolicy::class'),'Existing strict CSV policy reused');
foreach (['Financial'=>'canViewFinancial','Operations'=>'canViewOperations','Pipeline'=>'canViewPipeline','Warranty'=>'canViewWarranty'] as $label=>$flag) {
    $check(str_contains($view,'@if($'.$flag.')'), 'Blade '.$label.' authorization');
}
$check(substr_count($view, 'restricted for this account.') >= 7,'Restricted blocks do not display zero as real data');
$check(str_contains($view,'@if($canViewReportDetails)'),'Earlier detail privacy pilot preserved');
$check(str_contains($view,'@if($canExportInvoiceCsv)'),'Earlier CSV button privacy preserved');
$check(str_contains($policy,"'operations' => \$this->hasFullScope(\$viewer, 'service_tickets', 'service_tickets')"),'Operations policy checks both source modules');
$check(str_contains($policy,"'maintenance', 'maintenance_schedules'"),'Maintenance permission required');
$check(str_contains($policy,"=== 'all'"),'Policy requires explicit all scope');
$check(!preg_match('/(?:DB::|Schema::|->(?:update|insert|delete|save|create)\s*\()/i',$policy),'Policy has no business mutation');
$check(str_contains($controller,"'canViewFinancial' => \$canViewFinancial"),'Authorized view decision passed explicitly');
$check(str_contains($controller,"'canViewOperations' => \$canViewOperations"),'Operations decision passed explicitly');
$check(str_contains($controller,'$canViewReportDetails ? app('),'Legacy detail SQL remains lazily guarded');
$check(str_contains($controller,'$canViewFinancial ? app('),'Financial SQL remains lazily guarded');
$check(str_contains($controller,'$canViewWarranty ? ['),'Warranty SQL remains lazily guarded');
echo "A7.6.5.8 static: {$good}/".($good+$bad)." PASS; {$bad} FAIL. No DB queries.\n";
exit($bad?1:0);
