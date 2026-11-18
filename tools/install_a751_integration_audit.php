<?php
/** A7.5.1 additive installer; no existing production files are replaced. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payload = __DIR__ . '/a751_payload/';
$files = [
    'app/Services/CrmIntegrationAudit.php',
    'app/Console/Commands/AuditCrmIntegration.php',
    'tests/Unit/CrmIntegrationAuditCatalogTest.php',
    'tests/Feature/CrmIntegrationAuditCommandTest.php',
];
$dryRun = in_array('--dry-run', $argv, true);
$actions = [];
foreach ($files as $relative) {
    $from = $payload . $relative;
    $to = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($from)) {
        fwrite(STDERR, "ERROR: Missing patch payload: {$relative}\n");
        exit(1);
    }
    if (!is_readable($from)) {
        fwrite(STDERR, "ERROR: Payload not readable: {$relative}\n");
        exit(1);
    }
    if (file_exists($to)) {
        if (!is_file($to) || hash_file('sha256', $to) !== hash_file('sha256', $from)) {
            fwrite(STDERR, "ERROR: Existing local file differs: {$relative}. No files changed.\n");
            fwrite(STDERR, "Review/back up that file before applying this additive patch.\n");
            exit(1);
        }
        $actions[$relative] = 'already installed';
    } else {
        $actions[$relative] = 'add';
    }
}
if ($dryRun) {
    foreach ($actions as $relative => $action) echo "[CHECK] {$action}: {$relative}\n";
    echo "[PASS] Dry run. No file or DB changes.\n";
    exit(0);
}

$added = [];
foreach ($files as $relative) {
    if ($actions[$relative] !== 'add') {
        echo "[PASS] Already installed: {$relative}\n";
        continue;
    }
    $from = $payload . $relative;
    $to = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $parent = dirname($to);
    if (!is_dir($parent) && !mkdir($parent, 0775, true) && !is_dir($parent)) {
        fwrite(STDERR, "ERROR: Could not make directory: {$parent}\n");
        foreach ($added as $created) @unlink($created);
        exit(1);
    }
    // Exclusive creation means concurrent or pre-existing files are never overwritten.
    $handle = @fopen($to, 'x');
    if (!$handle) {
        fwrite(STDERR, "ERROR: Cannot exclusively create {$relative}; no existing file overwritten.\n");
        foreach ($added as $created) @unlink($created);
        exit(1);
    }
    $data = file_get_contents($from);
    $size = fwrite($handle, $data === false ? '' : $data);
    fclose($handle);
    if ($data === false || $size !== strlen($data)) {
        @unlink($to);
        foreach ($added as $created) @unlink($created);
        fwrite(STDERR, "ERROR: Unable to write full payload for {$relative}. New files rolled back.\n");
        exit(1);
    }
    $added[] = $to;
    echo "[PASS] Added: {$relative}\n";
}
echo "[PASS] A7.5.1 installed: ".count($added)." new files. No existing production files overwritten.\n";
echo "No migrations, SQL writes, route changes, or database reset required.\n";
echo "Next: php tools/a751_quick_check.php; php tools/a751_runtime_preflight.php\n";
