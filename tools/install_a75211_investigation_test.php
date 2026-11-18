<?php
/** A7.5.2.1.1: guarded, test-only schema-aware expectation fix. */
declare(strict_types=1);
$root = dirname(__DIR__);
$targetRel = 'tests/Feature/CrmIntegrationInvestigationCommandTest.php';
$payloadRel = 'tools/a75211_payload/' . $targetRel;
$target = $root . '/' . $targetRel;
$payload = $root . '/' . $payloadRel;
$oldSha = '901adea43e32638a4d615ed33dfc359a93016ca3cc904c00dd5f6d1506ab8d72';
$newSha = 'e216657ace0c5e78a26e11c379ecb90cc7b8488d38917648b8a175d747b4c37b';
$dryRun = in_array('--dry-run', $argv, true);
if (!is_file($payload) || hash_file('sha256', $payload) !== $newSha) {
    fwrite(STDERR, "ERROR: Patch payload missing or corrupted. No changes made.\n");
    exit(1);
}
if (!is_file($target)) {
    fwrite(STDERR, "ERROR: Expected A7.5.2.1 test file does not exist. No changes made.\n");
    exit(1);
}
$currentSha = hash_file('sha256', $target);
if ($currentSha === $newSha) {
    echo "[PASS] A7.5.2.1.1 is already installed. No files changed.\n";
    exit(0);
}
if ($currentSha !== $oldSha) {
    fwrite(STDERR, "ERROR: Unexpected changes in $targetRel. Compare manually; no file overwritten.\n");
    exit(1);
}
if ($dryRun) {
    echo "[CHECK] replace: $targetRel\n[PASS] Dry run. No files or database changed.\n";
    exit(0);
}
$backupDir = $root . '/tools/a75211_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!is_dir($backupDir) && !mkdir($backupDir, 0700, true) && !is_dir($backupDir)) {
    fwrite(STDERR, "ERROR: Cannot make backup directory.\n"); exit(1);
}
$backup = $backupDir . '/CrmIntegrationInvestigationCommandTest.php';
if (!copy($target, $backup) || hash_file('sha256', $backup) !== $oldSha) {
    fwrite(STDERR, "ERROR: Backup failed. No replacement performed.\n"); exit(1);
}
$tmp = $target . '.a75211-' . bin2hex(random_bytes(4)) . '.tmp';
if (!copy($payload, $tmp) || hash_file('sha256', $tmp) !== $newSha) {
    @unlink($tmp);
    fwrite(STDERR, "ERROR: Temporary payload verification failed. Original kept intact.\n"); exit(1);
}
if (!rename($tmp, $target)) {
    @unlink($tmp);
    fwrite(STDERR, "ERROR: Cannot replace test file. Original retained, backup available.\n"); exit(1);
}
echo "[PASS] Updated: $targetRel\n[PASS] Backup: $backupDir\n[PASS] Test-only fix installed. No application code, rules, routes, migrations or database rows changed.\n";
