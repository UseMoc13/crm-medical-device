<?php
/** A7.5.1.1: Repair only the Laravel command-registration check. No database modifications. */
declare(strict_types=1);

$root = dirname(__DIR__);
$source = __DIR__ . '/a7511_payload/';
$expected = [
    'tools/a751_runtime_preflight.php' => ['baseline' => 'bd55159c328aa8edb6aaa96af3c120d3755a5c9c97722cca2e48834e176c32a8', 'patched' => 'dce703d2450493c108851de347e27f0faa9abdb4d555f9522ddbbb0045b30c22'],
    'tests/Feature/CrmIntegrationAuditCommandTest.php' => ['baseline' => 'c6102c1bdac9bdf58e7ec68ac02235c65d95833a5c35b68034709bdd49855887', 'patched' => 'edf266d4867af0f456de65e92f0c85f68e2f84d748f872963f13487d0a155973'],
];
$dryRun = in_array('--dry-run', $argv, true);
$actions = [];
foreach ($expected as $relative => $hashes) {
    $to = $root . '/' . $relative;
    $from = $source . $relative;
    if (!is_file($to) || !is_file($from)) {
        fwrite(STDERR, "[FAIL] Missing required file: {$relative}; no files changed.\n");
        exit(1);
    }
    if (hash_file('sha256', $from) !== $hashes['patched']) {
        fwrite(STDERR, "[FAIL] Patch payload verification failed: {$relative}; no files changed.\n");
        exit(1);
    }
    $actual = hash_file('sha256', $to);
    if ($actual === $hashes['patched']) {
        $actions[$relative] = 'already installed';
    } elseif ($actual === $hashes['baseline']) {
        $actions[$relative] = 'replace';
    } else {
        fwrite(STDERR, "[FAIL] Unexpected local changes in {$relative}; no files changed.\n");
        fwrite(STDERR, "Compare/backup the local file before installing; refusing to overwrite it.\n");
        exit(1);
    }
}
foreach ($actions as $relative => $action) {
    echo "[".($dryRun?'CHECK':'INFO')."] {$action}: {$relative}\n";
}
if ($dryRun) {
    echo "[PASS] Dry run. No files or database changed.\n";
    exit(0);
}
if (!in_array('replace', $actions, true)) {
    echo "[PASS] Already installed. No files changed.\n";
    exit(0);
}
$backup = __DIR__.'/a7511_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
foreach ($actions as $relative => $action) {
    if ($action !== 'replace') continue;
    $destination = $backup.'/'.$relative;
    if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0775, true) && !is_dir(dirname($destination))) {
        fwrite(STDERR, "[FAIL] Could not create backup directory. No files changed.\n");
        exit(1);
    }
    if (!copy($root.'/'.$relative, $destination)) {
        fwrite(STDERR, "[FAIL] Could not backup {$relative}. No files changed.\n");
        exit(1);
    }
    if (hash_file('sha256', $destination) !== $expected[$relative]['baseline']) {
        fwrite(STDERR, "[FAIL] Backup integrity mismatch for {$relative}. No files changed.\n");
        exit(1);
    }
}
$changed=[];
foreach ($actions as $relative => $action) {
    if ($action !== 'replace') continue;
    $target=$root.'/'.$relative;
    $data=file_get_contents($source.$relative);
    if ($data===false || file_put_contents($target, $data, LOCK_EX)!==strlen($data)
        || hash_file('sha256', $target)!==$expected[$relative]['patched']) {
        foreach ($actions as $original => $a) {
            if ($a === 'replace' && is_file($backup.'/'.$original)) {
                copy($backup.'/'.$original, $root.'/'.$original);
            }
        }
        fwrite(STDERR, "[FAIL] Write/verification failed for {$relative}; restore attempted from {$backup}.\n");
        exit(1);
    }
    $changed[]=$relative;
    echo "[PASS] Updated: {$relative}\n";
}
echo "[PASS] A7.5.1.1 installed. Backup: ".str_replace($root.'/', '', $backup)."\n";
echo "No production code, audit rules, database rows, or migrations were changed.\n";
