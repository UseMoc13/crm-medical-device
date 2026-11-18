<?php
/** A7.5.1.2.1 — repair preflight function resolution and output-buffer test only. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payload = __DIR__ . '/a75112_payload/';
$expected = [
    'tools/a751_runtime_preflight.php' => ['baseline' => 'dce703d2450493c108851de347e27f0faa9abdb4d555f9522ddbbb0045b30c22', 'patched' => 'b2fb1a973a9219200416555752c171ebc093bb4498d06694a08323570a7df1e2'],
    'tests/Feature/CrmIntegrationAuditCommandTest.php' => ['baseline' => 'edf266d4867af0f456de65e92f0c85f68e2f84d748f872963f13487d0a155973', 'patched' => '38b1b039bd040e327be2368b24e779a5cfa7f4963fb9cf522b636d0cc34ad7e1'],
];
$dryRun = in_array('--dry-run', $argv, true);
$actions = [];

// Preflight every file before any changes.
foreach ($expected as $relative => $hashes) {
    $target = $root . '/' . $relative;
    $source = $payload . $relative;
    if (!is_file($target) || !is_file($source)) {
        fwrite(STDERR, "[FAIL] Required file missing: {$relative}. Nothing changed.\n");
        exit(1);
    }
    if (hash_file('sha256', $source) !== $hashes['patched']) {
        fwrite(STDERR, "[FAIL] Payload hash mismatch: {$relative}. Nothing changed.\n");
        exit(1);
    }
    $current = hash_file('sha256', $target);
    if ($current === $hashes['patched']) {
        $actions[$relative] = 'already installed';
    } elseif ($current === $hashes['baseline']) {
        $actions[$relative] = 'replace';
    } else {
        fwrite(STDERR, "[FAIL] Unrecognized local changes: {$relative}. Nothing changed.\n");
        fwrite(STDERR, "Please compare and back up your file before applying this patch.\n");
        exit(1);
    }
}
foreach ($actions as $relative => $action) {
    echo '[' . ($dryRun ? 'CHECK' : 'INFO') . "] {$action}: {$relative}\n";
}
if ($dryRun) { echo "[PASS] Dry run. No files or DB changed.\n"; exit(0); }
if (!in_array('replace', $actions, true)) {
    echo "[PASS] Patch already installed. No files changed.\n";
    exit(0);
}

$backup = __DIR__ . '/a75112_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
foreach ($actions as $relative => $action) {
    if ($action !== 'replace') continue;
    $destination = $backup . '/' . $relative;
    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true) && !is_dir(dirname($destination))) {
        fwrite(STDERR, "[FAIL] Backup folder creation failed. No files changed.\n");
        exit(1);
    }
    if (!copy($root . '/' . $relative, $destination)
        || hash_file('sha256', $destination) !== $expected[$relative]['baseline']) {
        fwrite(STDERR, "[FAIL] Backup validation failed: {$relative}. No files changed.\n");
        exit(1);
    }
}

foreach ($actions as $relative => $action) {
    if ($action !== 'replace') continue;
    $target = $root . '/' . $relative;
    $data = file_get_contents($payload . $relative);
    if ($data === false || file_put_contents($target, $data, LOCK_EX) !== strlen($data)
        || hash_file('sha256', $target) !== $expected[$relative]['patched']) {
        foreach ($actions as $restore => $mode) {
            if ($mode === 'replace' && is_file($backup . '/' . $restore)) {
                copy($backup . '/' . $restore, $root . '/' . $restore);
            }
        }
        fwrite(STDERR, "[FAIL] Patch failed; original files restored where possible. Backup: {$backup}\n");
        exit(1);
    }
    echo "[PASS] Updated: {$relative}\n";
}
echo "[PASS] A7.5.1.2.1 installed. Backup: {$backup}\n";
echo "No production application code, audit rules, migrations, or database records modified.\n";
