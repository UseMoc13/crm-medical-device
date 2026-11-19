<?php
/** A7.6.5.2: guarded additive installer — no edits or business writes. */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$targets = [
    'app/Services/CrmFinancialParityBaseline.php',
    'app/Console/Commands/AuditCrmFinancialParity.php',
    'tests/Unit/CrmFinancialParityContractTest.php',
    'tests/Feature/CrmFinancialParityBaselineCommandTest.php',
];
$prerequisites = [
    'app/Http/Controllers/ReportController.php' => ['$this->assertReportsView();','CrmReportingDataScope::class'],
    'app/Services/CrmScopedKpiQuery.php' => ['scopeDecision','authorizeMetric'],
    'app/Services/CrmReportingDataScope.php' => ['salesScopeEnabled'],
];
foreach ($prerequisites as $rel => $tokens) {
    $path = $root.'/'.$rel;
    $source = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
    if (!is_string($source)) { fwrite(STDERR, '[FAIL] Missing prerequisite '.$rel."\n"); exit(1); }
    foreach ($tokens as $token) if (!str_contains($source, $token)) {
        fwrite(STDERR, '[FAIL] Prerequisite source drift in '.$rel."\n"); exit(1);
    }
}
$pending = [];
foreach ($targets as $rel) {
    $destination = $root.'/'.$rel;
    $sourcePath = $root.'/tools/a7652_payload/'.$rel;
    if (!is_file($sourcePath) || !is_readable($sourcePath)) { fwrite(STDERR, '[FAIL] Missing payload '.$rel."\n"); exit(1); }
    $expected = file_get_contents($sourcePath);
    if (file_exists($destination)) {
        if (!is_file($destination) || !hash_equals(hash('sha256', $expected), hash_file('sha256', $destination))) {
            fwrite(STDERR, '[FAIL] Refusing local file conflict '.$rel."\n"); exit(1);
        }
        echo '[PASS] Already installed: '.$rel."\n";
    } else {
        echo ($dry ? '[CHECK] add: ' : '[INFO] add: ').$rel."\n";
        $pending[$rel] = $expected;
    }
}
if ($dry) { echo '[PASS] Dry run. No files or database changed.'."\n"; exit(0); }
foreach ($pending as $rel => $source) {
    $dest = $root.'/'.$rel;
    $parent = dirname($dest);
    if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) { fwrite(STDERR, '[FAIL] Cannot create directory '.$parent."\n"); exit(1); }
    if (file_put_contents($dest, $source, LOCK_EX) !== strlen($source)) { fwrite(STDERR, '[FAIL] Cannot write '.$rel."\n"); exit(1); }
    echo '[PASS] Added: '.$rel."\n";
}
echo '[PASS] A7.6.5.2 installed. Additive only; no business queries, DB writes, migrations, or existing file edits.'."\n";
