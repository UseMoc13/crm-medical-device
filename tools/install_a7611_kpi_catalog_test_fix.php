<?php
/**
 * A7.6.1.1: surgical, test-only KPI source catalog regex fix.
 * Guards: source fingerprint, dry-run, backup, CRLF preservation, idempotency.
 * Never opens a database or edits a production source file.
 */
$root = dirname(__DIR__);
$relative = 'tests/Unit/CrmKpiSourceCatalogTest.php';
$destination = $root . '/' . $relative;
$payload = __DIR__ . '/a7611_payload/' . $relative;
$old = '\'/^[a-z_]+\\.[a-z_]+$/\'';
$new = '\'/^[a-z_]+\\.[a-z_][a-z0-9_]*$/\'';
$originalSha = '71fb9d2b75a5ca016a92bfd5b464287da3e85826a6082369e5dd6b87fb5238b2';
$patchedSha  = 'dbbe143108fc8d93e598f01871a5d1952e1c8019e47176c6c7a6f4325c419fe8';
$dryRun = in_array('--dry-run', $argv, true);
if (!is_file($destination) || !is_file($payload)) {
    fwrite(STDERR, '[FAIL] Missing original test or patch payload. No changes made.' . PHP_EOL);
    exit(1);
}
$source = file_get_contents($destination);
$target = file_get_contents($payload);
if ($source === false || $target === false) {
    fwrite(STDERR, '[FAIL] Unable to read test. No changes made.' . PHP_EOL);
    exit(1);
}
$normalize = static fn (string $s): string => str_replace(["\r\n", "\r"], "\n", $s);
$sha = static fn (string $s): string => hash('sha256', $normalize($s));
if ($sha($target) !== $patchedSha) {
    fwrite(STDERR, '[FAIL] Patch payload integrity mismatch. No changes made.' . PHP_EOL);
    exit(1);
}
if ($sha($source) === $patchedSha) {
    echo '[PASS] A7.6.1.1 already installed; no changes made.' . PHP_EOL;
    exit(0);
}
if ($sha($source) !== $originalSha || substr_count($source, $old) !== 1) {
    fwrite(STDERR, '[FAIL] Refusing to overwrite a modified local test: ' . $relative . PHP_EOL);
    exit(1);
}
$patched = str_replace($old, $new, $source, $count);
if ($count !== 1 || $sha($patched) !== $patchedSha) {
    fwrite(STDERR, '[FAIL] Patching produced an unexpected result. No changes made.' . PHP_EOL);
    exit(1);
}
echo '[CHECK] patch: ' . $relative . PHP_EOL;
if ($dryRun) {
    echo '[PASS] Dry run. No file or DB changes.' . PHP_EOL;
    exit(0);
}
$backupDirectory = __DIR__ . '/a7611_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) {
    fwrite(STDERR, '[FAIL] Backup directory could not be created.' . PHP_EOL);
    exit(1);
}
$backupFile = $backupDirectory . '/CrmKpiSourceCatalogTest.php';
if (!copy($destination, $backupFile) || file_get_contents($backupFile) !== $source) {
    fwrite(STDERR, '[FAIL] Failed to verify backup; test unchanged.' . PHP_EOL);
    exit(1);
}
if (file_put_contents($destination, $patched, LOCK_EX) === false
    || $sha((string)file_get_contents($destination)) !== $patchedSha) {
    if (!copy($backupFile, $destination)) {
        fwrite(STDERR, '[CRITICAL] Cannot restore backup automatically; use ' . $backupFile . PHP_EOL);
    }
    fwrite(STDERR, '[FAIL] File verification failed. See backup: ' . $backupFile . PHP_EOL);
    exit(1);
}
echo '[PASS] Fixed: ' . $relative . PHP_EOL;
echo '[PASS] Backup: ' . $backupFile . PHP_EOL;
echo 'No application services, controllers, KPI catalogs, routes, migrations or database rows changed.' . PHP_EOL;
