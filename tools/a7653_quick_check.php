<?php
$root=dirname(__DIR__);
$checks=[];
$path='app/Services/CrmFinancialRoleEntitlementAudit.php';
$svc=@file_get_contents($root.'/'.$path) ?: '';
$cmd=@file_get_contents($root.'/app/Console/Commands/AuditCrmFinancialRoleEntitlements.php') ?: '';
$unit=@file_get_contents($root.'/tests/Unit/CrmFinancialRoleEntitlementPolicyTest.php') ?: '';
$feature=@file_get_contents($root.'/tests/Feature/CrmFinancialRoleEntitlementIsolatedTest.php') ?: '';
$installer=@file_get_contents($root.'/tools/install_a7653_role_entitlements.php') ?: '';
$check=static function(string $label, bool $ok) use (&$checks): void {
    $checks[$label]=$ok; echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
};
foreach (['app/Services/CrmFinancialRoleEntitlementAudit.php',
'app/Console/Commands/AuditCrmFinancialRoleEntitlements.php',
'tests/Unit/CrmFinancialRoleEntitlementPolicyTest.php',
'tests/Feature/CrmFinancialRoleEntitlementIsolatedTest.php'] as $file) {
    $check('File '.$file,is_file($root.'/'.$file));
}
$check('Audits only roles grants permissions',str_contains($svc, "DB::table('roles')") && str_contains($svc,"DB::table('role_permissions as rp')"));
$check('Does not query user or business records', !preg_match("/DB::table\(['\"](?:users|invoices|customers|sales_orders)['\"]\)/",$svc));
$check('Report page checks two permissions', str_contains($svc,"'reporting.access'") && str_contains($svc,"'crm_page.reports.view'"));
$check('Invoice KPI checks two permissions',str_contains($svc,"'billing.access'") && str_contains($svc,"'crm_page.invoices.view'"));
$check('Uses existing scope decision',str_contains($svc,"CrmScopedKpiQuery::scopeDecision('invoices'"));
$check('Null grants do not become all',str_contains($svc, "? 'missing' : ".chr(36)."decision"));
$check('Super admin conditional',str_contains($svc,chr(36)."role->is_system") && str_contains($svc,"=== 'super admin'"));
$check('Downstream scope flagged',str_contains($svc,'downstream_sales_scope_may_differ'));
$check('Unsupported statuses fail',str_contains($svc,"'INCOMPLETE'") && str_contains($svc,"'BLOCKED'"));
$check('No user IDs in result',!str_contains($svc,"'user_id' =>") && !str_contains($svc,"'role_id' =>"));
$check('CSV only mirrors current Reports guard',str_contains($svc,"'csv_export' => ".chr(36)."reports"));
$check('Command JSON and strict options',str_contains($cmd,'{--json') && str_contains($cmd,'{--strict'));
$check('Feature fixture isolated SQLite',str_contains($feature,"'database' => ':memory:'") && str_contains($feature,"assertSame('testing'"));
$check('Feature restores DB connection',str_contains($feature,'DB::setDefaultConnection($previous)') && str_contains($feature,"DB::purge('a7653_memory')"));
$check('Feature asserts no business queries',str_contains($feature,'self::assertStringNotContainsString'));
$check('Unit tests cover report-only role',str_contains($unit,'reports_csv_without_invoice_kpi'));
$check('Installer collision guard',str_contains($installer,'Local collision'));
$check('Installer dry-run',str_contains($installer,'--dry-run'));
foreach (['app/Services/CrmFinancialRoleEntitlementAudit.php','app/Console/Commands/AuditCrmFinancialRoleEntitlements.php','tests/Unit/CrmFinancialRoleEntitlementPolicyTest.php','tests/Feature/CrmFinancialRoleEntitlementIsolatedTest.php','tools/install_a7653_role_entitlements.php','tools/a7653_quick_check.php','tools/a7653_runtime_preflight.php'] as $file) {
    $out=[];$code=0;exec('php -l '.escapeshellarg($root.'/'.$file).' 2>&1',$out,$code);
    $check('PHP syntax '.$file, $code===0);
}
$passed=count(array_filter($checks));
echo "A7.6.5.3 static: $passed/".count($checks)." PASS, ".(count($checks)-$passed)." FAIL.\n";
exit($passed===count($checks)?0:1);
