<?php
/** A7.6.5.6 source-only checks. Does not bootstrap DB. */
declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    'app/Services/CrmMultiRoleReportsPrivacyAudit.php',
    'app/Console/Commands/AuditCrmMultiRoleReportsPrivacy.php',
    'tests/Unit/CrmMultiRoleReportsPrivacyPolicyTest.php',
    'tests/Feature/CrmMultiRoleReportsPrivacyIsolatedTest.php',
    'tests/Feature/CrmMultiRoleReportsHttpIsolationTest.php',
    'tools/install_a7656_multi_role_privacy.php',
    'tools/a7656_quick_check.php',
    'tools/a7656_runtime_preflight.php',
];
$pass = $fail = 0;
$check = static function (bool $ok, string $label) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$label."\n";
};
foreach ($paths as $path) {
    $full = $root.'/'.$path;
    $check(is_file($full), 'File '.$path);
    if (is_file($full)) {
        $cmd = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1';
        exec($cmd, $output, $status);
        $check($status === 0, 'PHP syntax '.$path);
        unset($output);
    }
}
$src = @file_get_contents($root.'/app/Services/CrmMultiRoleReportsPrivacyAudit.php') ?: '';
$command = @file_get_contents($root.'/app/Console/Commands/AuditCrmMultiRoleReportsPrivacy.php') ?: '';
$fixture = @file_get_contents($root.'/tests/Feature/CrmMultiRoleReportsPrivacyIsolatedTest.php') ?: '';
$http = @file_get_contents($root.'/tests/Feature/CrmMultiRoleReportsHttpIsolationTest.php') ?: '';
$installer = @file_get_contents($root.'/tools/install_a7656_multi_role_privacy.php') ?: '';
$check(str_contains($src, "DB::table('roles')"), 'Audits role metadata');
$check(str_contains($src, "DB::table('role_permissions as rp')"), 'Audits permission grants');
$check(!str_contains($src, "DB::table('users')") && !str_contains($src, "DB::table('user_roles')"), 'Never queries actual user assignments');
$check(!str_contains($src, "DB::table('invoices')") && !str_contains($src, "DB::table('customers')"), 'No business row queries');
$check(str_contains($src, 'CrmFinancialRoleEntitlementAudit::assessRole'), 'Reuses existing policy decision');
$check(str_contains($src, 'SOURCE_DRIFT'), 'Report source drift fails closed');
$check(str_contains($src, 'csv_invoice_customer_details'), 'Flags CSV privacy surface');
$check(str_contains($src, 'reports_customer_product_labels'), 'Flags Reports labels privacy surface');
$check(str_contains($src, 'actual_user_memberships_checked'), 'Does not claim actual user membership');
$check(str_contains($command, 'crm:reporting:multi-role-preview'), 'Read-only audit command registered by class');
$check(str_contains($command, '{--roles='), 'Requires explicit role names');
$check(str_contains($command, '{--json'), 'Supports JSON');
$check(str_contains($fixture, "'database'=>':memory:'"), 'Fixture uses SQLite memory only');
$check(str_contains($fixture, "environment('testing')"), 'Fixture tests require APP_ENV testing');
$check(str_contains($fixture, "DB::setDefaultConnection($"), 'Fixture restores connection');
$check(str_contains($http, "'role_id'=>'impostor'"), 'HTTP fixture tests non-system lookalike');
$check(str_contains($http, "'data_scope'=>'all'"), 'HTTP fixture tests explicit all scope');
$check(str_contains($http, "'date_from'=>'invalid-date'"), 'HTTP requests stop before business SQL');
$check(str_contains($installer, "fopen($"), 'Installer exclusively creates files');
$check(str_contains($installer, "hash('sha256'"), 'Installer rejects local-file changes');
$check(str_contains($installer, '--dry-run'), 'Installer supports dry-run');
echo "A7.6.5.6 static: {$pass} PASS, {$fail} FAIL. No DB query.\n";
exit($fail ? 1 : 0);
