<?php
/**
 * A7.4.11.2.1: safely replace only the strict Cache-Control header assertion.
 * Executed from the CRM project root, independently of the current working directory.
 */
$root = dirname(__DIR__);
$relative = 'tests/Feature/WorkspaceNotificationPreferencesHttpTest.php';
$destination = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
$payload = __DIR__ . '/a741121_payload/' . $relative;
$acceptedSource = 'e25ba9904ad7606b5bb8f139f9199797978316f2538ef8d6099578524c7d3d6c';
$expectedPayload = '4d2a99ce852c560f739df778fa1c2245b6082cb2b12d524beb9cfef82fa0a3bb';

if (!is_file($payload) || hash_file('sha256', $payload) !== $expectedPayload) {
    fwrite(STDERR, "[ERROR] Package payload is missing or has been modified. No files changed.\n");
    exit(1);
}
if (!is_file($destination)) {
    fwrite(STDERR, "[ERROR] Existing feature test not found. No files changed.\n");
    exit(1);
}
$current = hash_file('sha256', $destination);
if ($current === $expectedPayload) {
    echo "[PASS] A7.4.11.2.1 already applied; no changes needed.\n";
    exit(0);
}
if ($current !== $acceptedSource) {
    fwrite(STDERR, "[ERROR] Existing test differs from the shipped A7.4.11.2 baseline. No files changed.\n");
    fwrite(STDERR, "Check the difference manually before replacing the test.\n");
    exit(1);
}
$backup = $root . '/tools/a741121_backups/' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
if (!is_dir($backup) && !mkdir($backup, 0775, true) && !is_dir($backup)) {
    fwrite(STDERR, "[ERROR] Cannot create backup directory. No files changed.\n");
    exit(1);
}
$backupFile = $backup . '/' . basename($destination);
if (!copy($destination, $backupFile) || hash_file('sha256', $backupFile) !== $acceptedSource) {
    fwrite(STDERR, "[ERROR] Cannot create valid backup. No files changed.\n");
    exit(1);
}
if (!copy($payload, $destination) || hash_file('sha256', $destination) !== $expectedPayload) {
    copy($backupFile, $destination);
    fwrite(STDERR, "[ERROR] Copy failed. Restored original test from backup.\n");
    exit(1);
}
echo "[PASS] Fixed order-independent Cache-Control assertion.\n";
echo "[PASS] Backup: " . substr($backup, strlen($root) + 1) . "\n";
echo "No production PHP, routes, UI, migrations, or database rows changed.\n";
