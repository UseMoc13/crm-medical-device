<?php
/** Guarded additive installer. Does not overwrite local modifications or change the DB. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payloadRoot = __DIR__ . '/a7521_payload/';
$files = [
    'app/Services/CrmIntegrationInvestigation.php',
    'app/Console/Commands/InspectCrmIntegration.php',
    'tests/Unit/CrmIntegrationInvestigationSourceTest.php',
    'tests/Feature/CrmIntegrationInvestigationCommandTest.php',
];
$dryRun = in_array('--dry-run', $argv, true);
$actions = [];
foreach ($files as $path) {
    $source = $payloadRoot . $path;
    $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_file($source) || !is_readable($source)) {
        fwrite(STDERR, "ERROR: Missing/unreadable payload: {$path}. No files modified.\n");
        exit(1);
    }
    if (file_exists($target)) {
        if (!is_file($target) || hash_file('sha256', $target) !== hash_file('sha256', $source)) {
            fwrite(STDERR, "ERROR: Existing file has unexpected local changes: {$path}. No files modified.\n");
            exit(1);
        }
        $actions[$path] = 'already installed';
    } else {
        $actions[$path] = 'add';
    }
}
if ($dryRun) {
    foreach ($actions as $path => $action) echo "[CHECK] {$action}: {$path}\n";
    echo "[PASS] Dry run. No file or database changes.\n";
    exit(0);
}

$created = [];
foreach ($files as $path) {
    if ($actions[$path] === 'already installed') {
        echo "[PASS] Already installed: {$path}\n";
        continue;
    }
    $source = $payloadRoot . $path;
    $target = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    $directory = dirname($target);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        fwrite(STDERR, "ERROR: Could not create directory {$directory}\n");
        foreach ($created as $file) @unlink($file);
        exit(1);
    }
    $stream = @fopen($target, 'x');
    if ($stream === false) {
        fwrite(STDERR, "ERROR: Cannot exclusively create {$path}; existing files not overwritten.\n");
        foreach ($created as $file) @unlink($file);
        exit(1);
    }
    $contents = file_get_contents($source);
    $bytes = $contents === false ? false : fwrite($stream, $contents);
    fclose($stream);
    if ($contents === false || $bytes !== strlen($contents)) {
        @unlink($target);
        foreach ($created as $file) @unlink($file);
        fwrite(STDERR, "ERROR: Incomplete write. Newly created files rolled back.\n");
        exit(1);
    }
    $created[] = $target;
    echo "[PASS] Added: {$path}\n";
}
echo "[PASS] A7.5.2.1 installed. No existing files overwritten. No database writes or migrations.\n";
