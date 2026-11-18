<?php
/** Additive, idempotent installer. Never overwrites existing files or writes to DB. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payloadRoot = __DIR__ . '/a7522c_payload/';
$files = [
    'app/Services/CrmHistoricalRemediationReview.php',
    'app/Console/Commands/ReviewCrmHistoricalRemediation.php',
    'tests/Unit/CrmHistoricalRemediationReviewTest.php',
    'tests/Feature/CrmHistoricalRemediationReviewCommandTest.php',
];
$prerequisites = [
    'app/Services/CrmIntegrationAudit.php',
    'app/Services/CrmIntegrationInvestigation.php',
    'app/Console/Commands/InspectCrmIntegration.php',
];
foreach ($prerequisites as $path) {
    if (!is_file($root . '/' . $path)) {
        fwrite(STDERR, "[FAIL] Missing required earlier stage: {$path}. No files changed.\n");
        exit(1);
    }
}
$dryRun = in_array('--dry-run', $argv, true);
$actions = [];
foreach ($files as $path) {
    $source = $payloadRoot . $path;
    $target = $root . '/' . $path;
    if (!is_file($source) || !is_readable($source)) {
        fwrite(STDERR, "[FAIL] Missing payload: {$path}. No files changed.\n");
        exit(1);
    }
    if (file_exists($target)) {
        if (!is_file($target) || hash_file('sha256', $target) !== hash_file('sha256', $source)) {
            fwrite(STDERR, "[FAIL] Existing locally modified file: {$path}. No files changed.\n");
            exit(1);
        }
        $actions[$path] = 'already installed';
    } else {
        $actions[$path] = 'add';
    }
}
if ($dryRun) {
    foreach ($actions as $path => $action) echo "[CHECK] {$action}: {$path}\n";
    echo "[PASS] Dry run; no file or database changes.\n";
    exit(0);
}
$created = [];
foreach ($actions as $path => $action) {
    if ($action === 'already installed') {
        echo "[PASS] Already installed: {$path}\n";
        continue;
    }
    $source = $payloadRoot . $path;
    $target = $root . '/' . $path;
    if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
        fwrite(STDERR, "[FAIL] Could not create directory for {$path}.\n");
        foreach ($created as $item) @unlink($item);
        exit(1);
    }
    $stream = @fopen($target, 'x');
    if ($stream === false) {
        fwrite(STDERR, "[FAIL] Failed exclusive create for {$path}.\n");
        foreach ($created as $item) @unlink($item);
        exit(1);
    }
    $contents = file_get_contents($source);
    $bytes = $contents === false ? false : fwrite($stream, $contents);
    fclose($stream);
    if ($contents === false || $bytes !== strlen($contents)) {
        @unlink($target);
        foreach ($created as $item) @unlink($item);
        fwrite(STDERR, "[FAIL] Partial copy for {$path}; new files rolled back.\n");
        exit(1);
    }
    $created[] = $target;
    echo "[PASS] Added: {$path}\n";
}
echo "[PASS] A7.5.2.2c review installed. No existing files overwritten, no database writes or migrations.\n";
