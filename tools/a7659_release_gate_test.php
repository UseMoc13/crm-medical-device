<?php
/** Standalone, no-Laravel unit tests for the A7.6.5.9 release readiness gate. */
declare(strict_types=1);
require __DIR__.'/a7659_release_gate.php';

$work = sys_get_temp_dir().DIRECTORY_SEPARATOR.'crm_a7659_'.bin2hex(random_bytes(5));
$root = $work.DIRECTORY_SEPARATOR.'crm';
if (!mkdir($root, 0700, true) && !is_dir($root)) {
    throw new RuntimeException('Cannot create temporary test directory.');
}

function fixture(string $root, bool $sources = true): string
{
    if ($sources) {
        foreach (A7659ReleaseGate::REQUIRED_SOURCE as $name) {
            $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $name);
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0700, true);
            }
            file_put_contents($path, "<?php // synthetic fixture\n");
        }
    }
    $log = "[PASS] A7.6.5.8 installed.\n";
    $log .= "[PASS] Pilot flag default OFF\n";
    $log .= "[PASS] Local aggregate pilot currently OFF\n";
    $log .= "A7.6.5.8 static: 50/50 PASS; 0 FAIL. No DB queries.\n";
    $log .= "A7.6.5.8 runtime: 11/11 PASS; 0 FAIL. No business rows read.\n";
    foreach (A7659ReleaseGate::REQUIRED_TESTS as $name) {
        $group = str_contains($name, 'PolicyTest') || str_contains($name, 'SourceTest') ? 'Unit' : 'Feature';
        $log .= "PS C:\\repo> php artisan test --filter=$name\n";
        $log .= "    PASS  Tests\\$group\\$name\n";
        $log .= "  ✓ verified\n";
        $log .= "  Tests:    1 passed (1 assertions)\n";
    }
    $log .= "PS C:\\repo> php artisan crm:reporting:role-entitlements\n";
    $log .= "A7.6.5.3 Financial Role Entitlement Review [POLICY METADATA ONLY; READ-ONLY]\n";
    $log .= "Status: CLEAR | Roles 11 | Reports enabled 3 | Reports/no Invoice KPI 0 | CSV/Invoice KPI mismatch 0 | CSV strict pilot OFF\n";
    $log .= "PS C:\\repo> php artisan crm:reporting:parity-baseline\n";
    $log .= "A7.6.5.2 CRM Financial KPI & Scope Parity [SOURCE-ONLY; READ-ONLY]\n";
    $log .= "Status: CLEAR | Checks 13/13 | Failed 0 | Policy reviews 0\n";
    $log .= "PS C:\\repo> php artisan crm:reporting:baseline\n";
    $log .= "A7.6.1 CRM KPI Source Readiness [READ-ONLY]\n";
    $log .= "Status: SOURCE_READY | Sources: 12 | Available: 12 | Missing: 0 | Query errors: 0 | Review: 0\n";
    $log .= "PS C:\\repo> php artisan crm:integration:regression\n";
    $log .= "A7.5.4.1 CRM Integration Regression Gate [READ-ONLY]\n";
    $log .= "Status: OK | Audit 37 checks | OK 37 | Flagged 0 | Skipped 0 | Query errors 0\n";
    $log .= "Critical 0 | Review 0 | Shared Delivery 0\n";
    return $log;
}

$pass = 0;
$fail = 0;
function expectation(bool $condition, string $name): void
{
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo "[PASS] $name\n";
    } else {
        $fail++;
        echo "[FAIL] $name\n";
    }
}
function run_case(string $root, string $log, int $idx): array
{
    global $work;
    $file = $work.DIRECTORY_SEPARATOR.'sample_'.$idx.'.txt';
    file_put_contents($file, $log);
    return A7659ReleaseGate::evaluate($root, $file);
}
try {
    $clean = fixture($root);
    $case = run_case($root, $clean, 1);
    expectation($case['status'] === 'READY_FOR_MANUAL_REVIEW', 'clean evidence -> manual review ready, not rollout enabled');
    expectation(count($case['evidence']['tests'] ?? []) === 7, 'all seven regression suites required');

    $role = str_replace('Reports/no Invoice KPI 0 | CSV/Invoice KPI mismatch 0',
        'Reports/no Invoice KPI 2 | CSV/Invoice KPI mismatch 2', $clean);
    $role = str_replace('Status: CLEAR | Roles 11', 'Status: REVIEW_REQUIRED | Roles 11', $role);
    $case = run_case($root, $role, 2);
    expectation($case['status'] === 'HOLD_REVIEW_REQUIRED', 'role entitlement mismatch -> HOLD');

    $audit = str_replace('Status: OK | Audit 37 checks | OK 37 | Flagged 0',
        'Status: WATCH | Audit 37 checks | OK 33 | Flagged 4', $clean);
    $audit = str_replace('Critical 0 | Review 0', 'Critical 2 | Review 2', $audit);
    $case = run_case($root, $audit, 3);
    expectation($case['status'] === 'HOLD_REVIEW_REQUIRED', 'integration regression flags -> HOLD');

    $case = run_case($root, str_replace('A7.6.5.8 runtime: 11/11 PASS; 0 FAIL.',
        'A7.6.5.8 runtime: 10/11 PASS; 1 FAIL.', $clean), 4);
    expectation($case['status'] === 'HOLD_REVIEW_REQUIRED', 'runtime failure -> HOLD');

    $case = run_case($root, str_replace('Local aggregate pilot currently OFF',
        'Local aggregate pilot currently ON', $clean), 5);
    expectation($case['status'] === 'EVIDENCE_INCOMPLETE', 'flag OFF evidence required');

    $missingSuite = str_replace(
        ['php artisan test --filter=CrmInvoiceCsvPilotHttpTest', 'Tests\\Feature\\CrmInvoiceCsvPilotHttpTest'],
        ['php artisan test --filter=DifferentTest', 'Tests\\Feature\\DifferentTest'],
        $clean);
    $case = run_case($root, $missingSuite, 6);
    expectation($case['status'] === 'EVIDENCE_INCOMPLETE', 'missing security test -> incomplete');

    $old = $work.DIRECTORY_SEPARATOR.'old';
    mkdir($old);
    $case = run_case($old, $clean, 7);
    expectation($case['status'] === 'SOURCE_OUTDATED', 'older source does not pass');

    $withoutInstallLog = str_replace('[PASS] A7.6.5.8 installed.' . "\n", '', $clean);
    $case = run_case($root, $withoutInstallLog, 10);
    expectation($case['status'] === 'READY_FOR_MANUAL_REVIEW', 'fresh evidence without old installer output is valid when manifest exists');

    $withBadExit = $clean . "\nA7659_EXIT_CODE=1\n";
    $case = run_case($root, $withBadExit, 11);
    expectation($case['status'] === 'HOLD_REVIEW_REQUIRED', 'nonzero exit code in capture fails closed');

    $missingManifestRoot = $work.DIRECTORY_SEPARATOR.'no_manifest';
    mkdir($missingManifestRoot);
    fixture($missingManifestRoot);
    unlink($missingManifestRoot.DIRECTORY_SEPARATOR.'tools/a7658_install_state.json');
    $case = run_case($missingManifestRoot, $clean, 12);
    expectation($case['status'] === 'SOURCE_OUTDATED', 'manifest is required even when old installer log says PASS');

    $case = A7659ReleaseGate::evaluate($root, null);
    expectation($case['status'] === 'EVIDENCE_INCOMPLETE', 'no snapshot -> incomplete');

    $failedLater = $clean."\nPS C:\\repo> php artisan test --filter=CrmInvoiceCsvPilotHttpTest\n";
    $failedLater .= "    FAIL  Tests\\Feature\\CrmInvoiceCsvPilotHttpTest\n  Tests: 1 failed\n";
    $case = run_case($root, $failedLater, 8);
    expectation($case['status'] === 'HOLD_REVIEW_REQUIRED', 'later failing suite overrides earlier success');

    $withoutPrompt = preg_replace('/^PS C:\\\\repo> php artisan test --filter=[^\n]+\n/m', '', $clean) ?? $clean;
    $case = run_case($root, $withoutPrompt, 9);
    expectation($case['status'] === 'READY_FOR_MANUAL_REVIEW', 'transcript without command prompts supported');
} finally {
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($work, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iter as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($work);
}
echo "A7.6.5.9 unit tests: $pass passed, $fail failed\n";
exit($fail === 0 ? 0 : 1);
