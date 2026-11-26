<?php
/** A7.10.9 REV2: locked-source, no-database patch installer. PHP >= 8.2. */
declare(strict_types=1);

$mode = $argv[1] ?? '--check';
$allowed = ['--check', '--apply', '--rollback'];
if (!in_array($mode, $allowed, true)) {
    fwrite(STDERR, "Usage: php a7109-package/a7109_installer.php --check|--apply|--rollback [BACKUP_ID]\n");
    exit(2);
}
$package = __DIR__;
$root = dirname($package);
$backupBase = $root . '/storage/app/private/a7109_rev2_backups';

function failA7109(string $message): never {
    fwrite(STDERR, '[FAIL] ' . $message . PHP_EOL . '[RESULT] A7109_REV2_ABORTED' . PHP_EOL);
    exit(1);
}
function readA7109(string $path): string {
    $body = @file_get_contents($path);
    if ($body === false) failA7109('File cannot be read: ' . $path);
    return $body;
}
function copyA7109(string $from, string $to): void {
    if (!@copy($from, $to)) failA7109('Copy failed: ' . $to);
}
function safeRelA7109(string $rel): bool {
    return $rel !== '' && !str_contains($rel, '\\') &&
        preg_match('~^(?:resources/views|tests/(?:Feature|Unit))/[A-Za-z0-9_/.-]+\.php$|^resources/views/auth/login\.blade\.php$~D', $rel) === 1 &&
        !str_contains($rel, '..');
}
function navHashesA7109(string $root, array $guards): void {
    foreach ($guards as $rel => $hash) {
        if (!safeRelA7109($rel) || !is_file($root . '/' . $rel) ||
            hash_file('sha256', $root . '/' . $rel) !== $hash) {
            failA7109('A7.10.8 protected file has changed: ' . $rel);
        }
    }
}
function replaceSafelyA7109(string $source, string $destination): void {
    $bytes = readA7109($source);
    $staging = $destination . '.a7109_rev2_' . bin2hex(random_bytes(4)) . '.tmp';
    if (@file_put_contents($staging, $bytes, LOCK_EX) !== strlen($bytes)) {
        @unlink($staging);
        failA7109('Unable to stage ' . $destination);
    }
    if (!@rename($staging, $destination)) {
        @unlink($staging);
        failA7109('Unable to replace ' . $destination);
    }
}

if (!is_file($root . '/artisan') || !is_file($root . '/composer.json')) {
    failA7109('Extract package into project root as folder a7109-package.');
}
$manifest = json_decode(readA7109($package . '/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
$guards = json_decode(readA7109($package . '/navigation_guard.json'), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($manifest) || count($manifest) !== 5 || !is_array($guards) || count($guards) !== 3) {
    failA7109('Invalid package metadata');
}

if ($mode === '--rollback') {
    $id = $argv[2] ?? '';
    if (!preg_match('/^\d{8}-\d{6}-[a-f0-9]{8}$/D', $id)) failA7109('Backup ID required');
    $folder = $backupBase . '/' . $id;
    if (!is_file($folder . '/state.json')) failA7109('Unknown backup ID');
    $records = json_decode(readA7109($folder . '/state.json'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($records) || count($records) !== 5) failA7109('Invalid backup manifest');
    foreach ($records as $record) {
        $rel = $record['path'] ?? '';
        if (!safeRelA7109($rel) || !is_file($folder . '/original/' . $rel) ||
            !is_file($root . '/' . $rel) ||
            hash_file('sha256', $root . '/' . $rel) !== ($record['after'] ?? '') ||
            hash_file('sha256', $folder . '/original/' . $rel) !== ($record['before'] ?? '')) {
            failA7109('Rollback blocked: changed or missing file ' . $rel);
        }
    }
    // Stage all original content before replacing any file.
    foreach ($records as $r) {
        $rel = $r['path'];
        replaceSafelyA7109($folder . '/original/' . $rel, $root . '/' . $rel);
        echo '[RESTORED] ' . $rel . PHP_EOL;
    }
    navHashesA7109($root, $guards);
    echo '[RESULT] A7109_REV2_ROLLED_BACK' . PHP_EOL;
    exit(0);
}

navHashesA7109($root, $guards);
$ready = 0;
foreach ($manifest as $record) {
    $rel = $record['path'] ?? '';
    if (!safeRelA7109($rel)) failA7109('Invalid destination in manifest');
    $target = $root . '/' . $rel;
    $payload = $package . '/payload/' . $rel;
    if (!is_file($target) || !is_file($payload)) failA7109('Missing file: ' . $rel);
    $actual = hash_file('sha256', $target);
    if (hash_file('sha256', $payload) !== $record['after']) failA7109('Payload hash mismatch: ' . $rel);
    if ($actual === $record['after']) {
        echo '[ALREADY] ' . $rel . PHP_EOL;
        continue;
    }
    if ($actual !== $record['before']) failA7109('Source drift or older installer applied, refuse overwrite: ' . $rel);
    if (str_ends_with($rel, '.php') && !str_ends_with($rel, '.blade.php')) {
        $lint = [];
        $code = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($payload) . ' 2>&1', $lint, $code);
        if ($code !== 0) failA7109('PHP lint failure: ' . $rel . ' ' . implode(' ', $lint));
    }
    $ready++;
    echo '[READY] ' . $rel . PHP_EOL;
}
if ($mode === '--check') {
    echo '[RESULT] ' . ($ready === 0 ? 'A7109_REV2_ALREADY_APPLIED' : 'A7109_REV2_CHECK_READY') . ' pending=' . $ready . ' (no files or database modified)' . PHP_EOL;
    exit(0);
}
if ($ready === 0) { echo '[RESULT] A7109_REV2_ALREADY_APPLIED' . PHP_EOL; exit(0); }
if ($ready !== count($manifest)) failA7109('Partial install detected; refusing mixed state');
$id = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
$folder = $backupBase . '/' . $id;
$records = [];
foreach ($manifest as $record) {
    $rel = $record['path'];
    $destination = $folder . '/original/' . $rel;
    if (!is_dir(dirname($destination)) && !@mkdir(dirname($destination), 0770, true) && !is_dir(dirname($destination))) {
        failA7109('Cannot create backup folder');
    }
    copyA7109($root . '/' . $rel, $destination);
    if (hash_file('sha256', $destination) !== $record['before']) failA7109('Backup verification failed: ' . $rel);
    $records[] = $record;
}
if (@file_put_contents($folder . '/state.json', json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) === false) {
    failA7109('Backup state cannot be written');
}
// Validate all originals again immediately before changes.
foreach ($manifest as $record) {
    if (hash_file('sha256', $root . '/' . $record['path']) !== $record['before']) {
        failA7109('Race detected before apply: ' . $record['path']);
    }
}
$written = [];
try {
    foreach ($manifest as $record) {
        $rel = $record['path'];
        $target = $root . '/' . $rel;
        replaceSafelyA7109($package . '/payload/' . $rel, $target);
        $written[] = $rel;
        if (hash_file('sha256', $target) !== $record['after']) throw new RuntimeException('Post-write hash mismatch ' . $rel);
        echo '[APPLIED] ' . $rel . PHP_EOL;
    }
    navHashesA7109($root, $guards);
} catch (Throwable $e) {
    foreach (array_reverse($written) as $rel) @copy($folder . '/original/' . $rel, $root . '/' . $rel);
    failA7109('Apply failed and recovery attempted: ' . $e->getMessage());
}
echo '[BACKUP_ID] ' . $id . PHP_EOL;
echo '[RESULT] A7109_REV2_APPLIED files=' . count($written) . ' (no DB operation)' . PHP_EOL;
