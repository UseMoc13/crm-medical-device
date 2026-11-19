<?php
$root=dirname(__DIR__);$pass=0;$fail=0;
function a7654_check(bool $good,string $label):void{global $pass,$fail;echo ($good?'[PASS] ':'[FAIL] ').$label.PHP_EOL;$good?$pass++:$fail++;}
$files=[
 'app/Services/CrmInvoiceCsvPolicy.php','config/crm_reporting.php',
 'app/Http/Controllers/ReportController.php','app/Services/CrmFinancialRoleEntitlementAudit.php',
 'app/Console/Commands/AuditCrmFinancialRoleEntitlements.php',
 'tests/Unit/CrmInvoiceCsvPilotPolicyTest.php','tests/Unit/CrmInvoiceCsvPilotSourceTest.php',
 'tests/Feature/CrmInvoiceCsvPilotHttpTest.php','tools/install_a7654_csv_pilot.php',
];
foreach($files as $path){a7654_check(is_file($root.'/'.$path),'File '.$path);if(is_file($root.'/'.$path)){
 $lines=[];$ret=0;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1',$lines,$ret);
 a7654_check($ret===0,'PHP syntax '.$path);
}}
$cfg=@file_get_contents($root.'/config/crm_reporting.php')?:'';
$controller=@file_get_contents($root.'/app/Http/Controllers/ReportController.php')?:'';
$policy=@file_get_contents($root.'/app/Services/CrmInvoiceCsvPolicy.php')?:'';
$audit=@file_get_contents($root.'/app/Services/CrmFinancialRoleEntitlementAudit.php')?:'';
$cmd=@file_get_contents($root.'/app/Console/Commands/AuditCrmFinancialRoleEntitlements.php')?:'';
a7654_check(str_contains($cfg,"env('CRM_REPORT_CSV_INVOICE_ALL_SCOPE_ENABLED', false)"),'Feature flag default OFF');
a7654_check(substr_count($controller,'$this->assertReportsView();')===2,'Both Reports guards retained');
a7654_check(str_contains($controller,'CrmInvoiceCsvPolicy::class'),'CSV calls strict policy');
a7654_check(str_contains($policy,"canView($".'user, '."'reports')"),'CSV requires Reports view');
a7654_check(str_contains($policy,"canView($".'user, '."'invoices')"),'CSV requires Invoice view');
a7654_check(str_contains($policy,"scopeDecision('invoices'"),'CSV reuses Invoice scope decision');
a7654_check(str_contains($audit,'reports_without_invoice_kpi'),'Reports-only audit remains visible');
a7654_check(str_contains($audit,'csv_pilot_enabled'),'Audit reports pilot state');
a7654_check(str_contains($cmd, "($"."role['csv_export'] ? 'YES' : 'NO')"),'Role report prints actual CSV state');
a7654_check(str_contains($controller,"status IN ('Sent','Paid')"),'Existing financial formulas retained');
a7654_check(str_contains($controller,'CrmReportingDataScope::class'),'Existing Report downstream scope retained');
a7654_check(!str_contains($policy,'->update(') && !str_contains($policy,'->insert('),'Strict policy read-only');
$state=$root.'/tools/a7654_install_state.json';a7654_check(is_file($state),'Installer manifest installed');
echo "A7.6.5.4 static: {$pass} PASS, {$fail} FAIL.\n";exit($fail?1:0);
