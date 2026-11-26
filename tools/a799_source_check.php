<?php
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [
    'app/Services/CrmOperationalAssignmentPolicy.php' => ['mutableTables', 'service_tickets', 'maintenance_records', 'HISTORICAL_PERFORMER_DO_NOT_REWRITE'],
    'app/Services/CrmOperationalAssignmentRemediator.php' => ['lockForUpdate()', 'audit_logs', "'user_id' => null", "->update(", 'CrmUserAssignmentEligibility', 'different branches'],
    'tools/a799_assignment_candidates.php' => ['--from=', 'A799_CANDIDATES_READ_ONLY', 'whereNull'],
    'tools/a799_assignment_reassign.php' => ['--apply', 'A799-ONE-RECORD', 'crm_medical_device', 'A799_REASSIGN_DRY_RUN_ONLY'],
    'tools/a799_integration_gate.php' => ['created_by_user_id', 'maintenance_records', 'CrmOperationalAssignmentPolicy::classification', 'A799_GATE_REVIEW_REQUIRED'],
    'tests/Unit/CrmOperationalAssignmentPolicyTest.php' => ['test_active_operational_states_are_writable', 'test_finalized_and_historical_records_are_immutable'],
    'docs/A799_START_HERE.md' => ['--strict', 'PostgreSQL backup', 'Never reassign historical'],
];
$n=0;
foreach ($checks as $path=>$needles) {
    $full = $root.'/'.$path;
    $content = is_file($full) ? file_get_contents($full) : false;
    foreach ($needles as $needle) {
        if ($content === false || !str_contains($content, $needle)) {
            fwrite(STDERR, '[FAIL] '.$path.' missing '.$needle.PHP_EOL);
            exit(1);
        }
        $n++;
    }
    if (str_ends_with($path, '.php')) {
        $out=[]; $rc=0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1', $out, $rc);
        if ($rc!==0) {
            fwrite(STDERR, '[FAIL] Syntax '.$path.': '.implode(' ',$out).PHP_EOL);
            exit(1);
        }
        $n++;
    }
    echo '[PASS] '.$path.PHP_EOL;
}
echo '[RESULT] A799_SOURCE_READY checks='.$n.' (Laravel/PostgreSQL not executed)'.PHP_EOL;
