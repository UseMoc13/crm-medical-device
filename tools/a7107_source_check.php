<?php
/** A7.10.7 offline source check: no Laravel or database access. */
declare(strict_types=1);
$root = dirname(__DIR__);
$cases = [
    'app/Support/CrmAuditPermissionSeedPolicy.php' => ['workspace_audit.view', "'CONFLICT'", 'insertFields'],
    'tools/a7107_workspace_audit_permission.php' => ['--check', '--apply', 'A7107-ONE-PERMISSION', 'DB::transaction', 'inet_server_addr()', "'role_permissions'", 'insertOrIgnore'],
    'tests/Unit/CrmAuditPermissionSeedPolicyTest.php' => ['test_missing_row', 'test_correct_row', 'test_conflicting_metadata', 'test_insert_fields'],
    'docs/A7107_START_HERE.md' => ['php artisan migrate:status', 'php tools/a7107_workspace_audit_permission.php', 'No role grants'],
];
$count = 0;
foreach ($cases as $relative=>$needles) {
    $path = $root.'/'.$relative;
    $s = is_file($path) ? file_get_contents($path) : false;
    if ($s === false) {
        fwrite(STDERR, "[FAIL] Missing $relative\n"); exit(1);
    }
    foreach ($needles as $needle) {
        if (!str_contains($s, $needle)) {
            fwrite(STDERR, "[FAIL] $relative missing $needle\n"); exit(1);
        }
        $count++;
    }
    if (str_ends_with($relative, '.php')) {
        $output=[]; $rc=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $output, $rc);
        if ($rc!==0) {
            fwrite(STDERR, '[FAIL] Syntax '.$relative.' '.implode(' ', $output).PHP_EOL); exit(1);
        }
        $count++;
    }
    echo '[PASS] '.$relative.PHP_EOL;
}
echo '[RESULT] A7107_SOURCE_READY checks='.$count.' (Laravel/PostgreSQL not executed)'.PHP_EOL;
