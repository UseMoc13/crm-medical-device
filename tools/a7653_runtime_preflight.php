<?php
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks=[];
$check=static function(string $label,bool $ok) use (&$checks):void {
    $checks[$label]=$ok;echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
};
$check('Role entitlement service resolves',app(\App\Services\CrmFinancialRoleEntitlementAudit::class) instanceof \App\Services\CrmFinancialRoleEntitlementAudit);
$check('Scoped KPI contract resolves',app(\App\Services\CrmScopedKpiQuery::class) instanceof \App\Services\CrmScopedKpiQuery);
$check('Artisan audit command available',array_key_exists('crm:reporting:role-entitlements',\Illuminate\Support\Facades\Artisan::all()));
foreach (['roles'=>['role_id','role_name','is_system'],
    'permissions'=>['permission_id','permission_name'],
    'role_permissions'=>['role_id','permission_id','data_scope']] as $table=>$cols) {
    $tableOk=\Illuminate\Support\Facades\Schema::hasTable($table);
    $check('Table '.$table,$tableOk);
    if ($tableOk) $check('Required columns '.$table,array_reduce($cols,static fn(bool $ok,string $c):bool=>$ok&&\Illuminate\Support\Facades\Schema::hasColumn($table,$c),true));
}
$passed=count(array_filter($checks));echo "A7.6.5.3 runtime: $passed/".count($checks)." PASS. Schema metadata only; no role grants or business records read.\n";
exit($passed===count($checks)?0:1);
