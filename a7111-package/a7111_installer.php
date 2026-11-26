<?php
/**
 * A7.11.1 Static Route Closure (safe source-only installer)
 * php a7111-package/a7111_installer.php --check|--apply|--rollback BACKUP_ID
 * No Artisan, migration, application bootstrap, or DB access.
 */
declare(strict_types=1);

function fail(string $message): never { fwrite(STDERR, "[ERROR] $message\n"); exit(2); }
function digest(string $path): string { return hash_file('sha256', $path) ?: fail("Cannot hash $path"); }
function getRoot(): string {
    $root = realpath(__DIR__ . '/../');
    if (!$root || !is_file($root . '/artisan') || !is_file($root . '/routes/web.php')) fail('Run from extracted a7111-package inside Laravel project root');
    return $root;
}
function absolute(string $root, string $relative): string {
    if ($relative === '' || str_contains($relative, '..') || str_contains($relative, '\\') || str_starts_with($relative, '/')) fail('Unsafe relative path: ' . $relative);
    return $root . '/' . $relative;
}
function readManifest(): array {
    $raw = file_get_contents(__DIR__ . '/manifest.json');
    $data = json_decode((string)$raw, true);
    if (!is_array($data) || ($data['stage'] ?? null) !== 'A7.11.1') fail('Invalid installer manifest');
    return $data;
}
function isSafeState(array $entry, string $root): string {
    $file = absolute($root, $entry['path']);
    if (is_link($file)) fail('Symlink refused: ' . $entry['path']);
    $op = $entry['action'];
    if ($op === 'replace') {
        $payload = absolute(__DIR__, $entry['payload']);
        if (!is_file($payload) || digest($payload) !== $entry['after']) fail('Payload digest mismatch: ' . $entry['path']);
        if (!is_file($file)) fail('Expected file missing: ' . $entry['path']);
        $hash = digest($file);
        if ($hash === $entry['after']) return 'already';
        if ($hash === $entry['before']) return 'pending';
        fail('Unknown or changed file; refusing overwrite: ' . $entry['path']);
    }
    if ($op === 'archive_and_remove') {
        if (!file_exists($file)) return 'already';
        if (!is_file($file) || digest($file) !== $entry['before']) fail('Legacy file was modified; refusing remove: ' . $entry['path']);
        return 'pending';
    }
    fail('Unrecognized operation');
}
function guardProtected(array $manifest, string $root): void {
    foreach ($manifest['protected'] as $relative => $expected) {
        $file = absolute($root, $relative);
        if (!is_file($file) || is_link($file) || digest($file) !== $expected) fail('A7.10.8 protected file changed; abort: ' . $relative);
    }
}
function writeFile(string $target, string $data): void {
    $dir = dirname($target);
    if (!is_dir($dir)) fail('Target folder missing: ' . $dir);
    $temp = tempnam($dir, '.a7111-');
    if ($temp === false) fail('Unable to allocate staging file');
    if (file_put_contents($temp, $data) !== strlen($data)) { @unlink($temp); fail('Short write: ' . $target); }
    // Windows rename cannot reliably overwrite an existing target; copy verified staging bytes instead.
    if (!copy($temp, $target)) { @unlink($temp); fail('Unable to write: ' . $target); }
    @unlink($temp);
}
function restore(array $receipt, string $root, string $backupDir, bool $force): void {
    foreach (array_reverse($receipt['operations']) as $entry) {
        $path = absolute($root, $entry['path']);
        $copy = $backupDir . '/files/' . $entry['path'];
        if (!is_file($copy) || digest($copy) !== $entry['before']) fail('Backup corrupt: ' . $entry['path']);
        if (is_link($path)) fail('Symlink refused on rollback');
        if (!$force) {
            if ($entry['action'] === 'replace' && (!is_file($path) || digest($path) !== $entry['after'])) fail('Post-install file modified, rollback refused: ' . $entry['path']);
            if ($entry['action'] === 'archive_and_remove' && file_exists($path)) fail('Archived file recreated, rollback refused: ' . $entry['path']);
        }
        writeFile($path, file_get_contents($copy));
        if (digest($path) !== $entry['before']) fail('Restored file hash mismatch: ' . $entry['path']);
        echo '[RESTORED] ' . $entry['path'] . "\n";
    }
}
$mode = $argv[1] ?? '--check';
if (!in_array($mode, ['--check', '--apply', '--rollback'], true)) fail('Use --check | --apply | --rollback BACKUP_ID');
$root = getRoot();
$manifest = readManifest();
$backupRoot = $root . '/.a7111_backups';
if ($mode === '--rollback') {
    $id = $argv[2] ?? '';
    if (!preg_match('/^a7111_[0-9]{8}_[0-9]{6}_[a-f0-9]{8}$/', $id)) fail('Invalid BACKUP_ID');
    $directory = $backupRoot . '/' . $id;
    $receiptFile = $directory . '/receipt.json';
    if (!is_file($receiptFile)) fail('Backup receipt not found');
    $receipt = json_decode((string)file_get_contents($receiptFile), true);
    if (!is_array($receipt) || ($receipt['stage'] ?? null) !== 'A7.11.1' || ($receipt['id'] ?? null) !== $id) fail('Invalid rollback receipt');
    // Prevalidate all before any restoration.
    foreach ($receipt['operations'] as $entry) {
        $p = absolute($root, $entry['path']);
        $copy = $directory . '/files/' . $entry['path'];
        if (!is_file($copy) || digest($copy) !== $entry['before']) fail('Backup invalid for ' . $entry['path']);
        if (is_link($p)) fail('Symlink in rollback target');
        if ($entry['action'] === 'replace' && (!is_file($p) || digest($p) !== $entry['after'])) fail('Rollback conflict: ' . $entry['path']);
        if ($entry['action'] === 'archive_and_remove' && file_exists($p)) fail('Rollback conflict: ' . $entry['path']);
    }
    restore($receipt, $root, $directory, false);
    file_put_contents($directory . '/ROLLED_BACK', date(DATE_ATOM));
    echo "[RESULT] A7111_ROLLED_BACK id=$id\n";
    exit(0);
}
guardProtected($manifest, $root);
$pending = [];
foreach ($manifest['operations'] as $entry) {
    $status = isSafeState($entry, $root);
    echo '[' . strtoupper($status) . '] ' . $entry['path'] . "\n";
    if ($status === 'pending') $pending[] = $entry;
}
if (!$pending) { echo '[RESULT] A7111_ALREADY_APPLIED pending=0 (no files or database modified)' . "\n"; exit(0); }
if ($mode === '--check') { echo '[RESULT] A7111_CHECK_READY pending=' . count($pending) . ' (no files modified)' . "\n"; exit(0); }
$id = 'a7111_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
$directory = $backupRoot . '/' . $id;
if (!mkdir($directory . '/files', 0775, true) && !is_dir($directory . '/files')) fail('Unable to create backup directory');
$receipt = ['stage' => 'A7.11.1', 'id' => $id, 'operations' => $pending];
foreach ($pending as $entry) {
    $copy = $directory . '/files/' . $entry['path'];
    if (!is_dir(dirname($copy)) && !mkdir(dirname($copy), 0775, true)) fail('Backup folder creation failed');
    if (!copy(absolute($root, $entry['path']), $copy) || digest($copy) !== $entry['before']) fail('Backup failed ' . $entry['path']);
}
file_put_contents($directory . '/receipt.json', json_encode($receipt, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
echo "[BACKUP_ID] $id\n";
$changed = [];
try {
    // Revalidate before mutation to avoid changes between check and apply.
    guardProtected($manifest, $root);
    foreach ($pending as $entry) if (isSafeState($entry, $root) !== 'pending') throw new RuntimeException('File changed after backup: ' . $entry['path']);
    foreach ($pending as $entry) {
        $target = absolute($root, $entry['path']);
        if ($entry['action'] === 'replace') {
            writeFile($target, file_get_contents(absolute(__DIR__, $entry['payload'])));
            if (digest($target) !== $entry['after']) throw new RuntimeException('Written file hash mismatch: ' . $entry['path']);
        } else {
            if (!unlink($target)) throw new RuntimeException('Cannot archive unused edit view: ' . $entry['path']);
        }
        $changed[] = $entry;
        echo '[APPLIED] ' . $entry['path'] . "\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] Apply failed: ' . $e->getMessage() . "\n");
    // Restore changed files, with source backups already verified.
    foreach (array_reverse($pending) as $entry) {
        $copy = $directory . '/files/' . $entry['path'];
        $target = absolute($root, $entry['path']);
        if (is_file($copy) && digest($copy) === $entry['before']) {
            try { writeFile($target, file_get_contents($copy)); } catch (Throwable $ignored) { fwrite(STDERR, '[ERROR] Manual restore needed: ' . $entry['path'] . "\n"); }
        }
    }
    fail('Apply aborted; restored backups where possible. BACKUP_ID=' . $id);
}
echo '[RESULT] A7111_APPLIED changed=' . count($pending) . ' backup=' . $id . "\n";
