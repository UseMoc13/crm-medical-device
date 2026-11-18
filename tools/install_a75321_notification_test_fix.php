<?php
/** A7.5.3.2.1 — scoped correction of one PHPUnit string assertion. */

declare(strict_types=1);

$base = dirname(__DIR__);
$target = $base . '/tests/Unit/CrmStatusNotificationSourceTest.php';
$payload = __DIR__ . '/a75321_payload/tests/Unit/CrmStatusNotificationSourceTest.php';
$expectedOriginal = '57e35927284af96fb36f6445ce0df5b6b6a1f5d6a139550a14a11d59934f910d';
$expectedPatched = '0493b0ae6d1fd6054471c27ef47e71cb31ad092dbcb89e23ef57525fc96eb820';
$dryRun = in_array('--dry-run', $argv, true);

$normalizedHash = static function (string $path): ?string {
    if (!is_file($path) || !is_readable($path)) return null;
    $source = file_get_contents($path);
    if ($source === false) return null;
    return hash('sha256', str_replace(["\r\n", "\r"], "\n", $source));
};

if ($normalizedHash($payload) !== $expectedPatched) {
    fwrite(STDERR, "[FAIL] Patch payload has unexpected content. No change made.\n");
    exit(1);
}
$current = $normalizedHash($target);
if ($current === $expectedPatched) {
    echo "[PASS] A7.5.3.2.1 already applied. No change needed.\n";
    exit(0);
}
if ($current !== $expectedOriginal) {
    fwrite(STDERR, "[FAIL] Existing test differs from the expected A7.5.3.2 version. Not overwriting local work.\n");
    exit(1);
}
if ($dryRun) {
    echo "[CHECK] patch: tests/Unit/CrmStatusNotificationSourceTest.php\n";
    echo "[PASS] Dry run. No file or DB changes.\n";
    exit(0);
}
$src = file_get_contents($target);
if ($src === false) {
    fwrite(STDERR, "[FAIL] Cannot read existing test.\n");
    exit(1);
}
$before = <<<'BEFORE'
        $this->assertStringContainsString("->allows($userId, 'crm_status', $type)", $publisher);
BEFORE;
$after = <<<'AFTER'
        $this->assertStringContainsString('->allows($userId, \'crm_status\', $type)', $publisher);
AFTER;
if (substr_count($src, $before) !== 1) {
    fwrite(STDERR, "[FAIL] Expected assertion not found exactly once. No change made.\n");
    exit(1);
}
$updated = str_replace($before, $after, $src);
if (hash('sha256', str_replace(["\r\n", "\r"], "\n", $updated)) !== $expectedPatched) {
    fwrite(STDERR, "[FAIL] Patch output failed verification; no change made.\n");
    exit(1);
}
$backupDir = $base . '/tools/a75321_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!@mkdir($backupDir, 0770, true) || !copy($target, $backupDir . '/CrmStatusNotificationSourceTest.php')) {
    fwrite(STDERR, "[FAIL] Backup failed, no change made.\n");
    exit(1);
}
if (file_put_contents($target, $updated, LOCK_EX) === false || $normalizedHash($target) !== $expectedPatched) {
    copy($backupDir . '/CrmStatusNotificationSourceTest.php', $target);
    fwrite(STDERR, "[FAIL] Write verification failed. Original restored from backup.\n");
    exit(1);
}
echo "[PASS] Fixed PHPUnit literal assertion in tests/Unit/CrmStatusNotificationSourceTest.php\n";
echo "[PASS] Backup: " . str_replace('\\', '/', $backupDir) . "\n";
echo "No production application code, notification preferences, migrations, or database records were changed.\n";
