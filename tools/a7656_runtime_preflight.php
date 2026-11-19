<?php
/** A7.6.5.6 Laravel boot + schema metadata only; no user or business queries. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass = $fail = 0;
$check = static function (bool $ok, string $label) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label."\n";
};
$check(app(\App\Services\CrmMultiRoleReportsPrivacyAudit::class) instanceof \App\Services\CrmMultiRoleReportsPrivacyAudit, 'Multi-role preview service resolves');
$check(app(\App\Services\CrmFinancialRoleEntitlementAudit::class) instanceof \App\Services\CrmFinancialRoleEntitlementAudit, 'Prior entitlement service resolves');
$check(app(\App\Services\CrmInvoiceCsvPolicy::class) instanceof \App\Services\CrmInvoiceCsvPolicy, 'CSV policy resolves');
$check(array_key_exists('crm:reporting:multi-role-preview', \Illuminate\Support\Facades\Artisan::all()), 'Read-only audit Artisan command registered');
foreach (['roles'=>['role_id','role_name','is_system'], 'permissions'=>['permission_id','permission_name'],
    'role_permissions'=>['role_id','permission_id','data_scope']] as $table=>$cols) {
    $ready = \Illuminate\Support\Facades\Schema::hasTable($table);
    if ($ready) foreach ($cols as $col) $ready = $ready && \Illuminate\Support\Facades\Schema::hasColumn($table, $col);
    $check($ready, 'Required schema '.$table);
}
$check(is_bool(config('crm_reporting.csv_invoice_all_scope_pilot_enabled', false)), 'CSV pilot mode available');
$check(\App\Services\CrmMultiRoleReportsPrivacyAudit::assess([
    ['role_name'=>'Super Admin','is_system'=>false]], [], false, true)['reports_page'] === false, 'Non-system Super Admin name has no bypass');
echo "A7.6.5.6 runtime: {$pass} PASS, {$fail} FAIL. Schema metadata only.\n";
exit($fail ? 1 : 0);
