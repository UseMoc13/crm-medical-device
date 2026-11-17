<?php
/**
 * A7.4.10.4 incremental Notes UI patch. Branch: Big-Changes.
 * This installer changes exactly two Notes view files and preserves originals.
 * Run from project root: php tools/install_a74104_notes.php
 */

declare(strict_types=1);

$projectRoot = dirname(__DIR__);
$payloadRoot = __DIR__ . DIRECTORY_SEPARATOR . 'a74104_payload';
$targets = [
    'resources/views/notes/_floating.blade.php' => 'dfa35828fd5ac11606673fa61fc5156997f34d6e9773e672861de07799089577',
    'resources/views/notes/_style.blade.php' => '5fdcf967ed45679409148b1e58a1d3a8d1263f16569fa6ff7a336296ea698b03',
];

/** Make CRLF and LF equivalent, so checkout EOL conversion is harmless. */
$normalizedSha = static function (string $data): string {
    return hash('sha256', str_replace(["\r\n", "\r"], "\n", $data));
};

$pending = [];
foreach ($targets as $relative => $expectedSha) {
    $destination = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $payload = $payloadRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($destination) || !is_file($payload)) {
        fwrite(STDERR, "[FAIL] Missing file: {$relative} (project or patch payload). No files changed.\n");
        exit(1);
    }
    $current = file_get_contents($destination);
    $replacement = file_get_contents($payload);
    if ($current === false || $replacement === false) {
        fwrite(STDERR, "[FAIL] Cannot read {$relative}. No files changed.\n");
        exit(1);
    }
    if ($normalizedSha($current) === $normalizedSha($replacement)) {
        echo "[PASS] Already integrated: {$relative}\n";
        continue;
    }
    if ($normalizedSha($current) !== $expectedSha) {
        fwrite(STDERR, "[FAIL] Unexpected local changes in {$relative}. No files changed.\n");
        fwrite(STDERR, "       Installer expects A7.4.10.3. Compare with tools/a74104_payload/ and merge manually if customized.\n");
        exit(1);
    }
    $pending[$relative] = [$destination, $replacement, $current];
}

if (!$pending) {
    echo "A7.4.10.4 is already installed. No changes needed.\n";
    exit(0);
}

$backupDir = __DIR__ . DIRECTORY_SEPARATOR . 'a74104_backups'
    . DIRECTORY_SEPARATOR . date('Ymd-His') . '-' . random_int(1000, 9999);
foreach ($pending as $relative => [$destination, $replacement, $current]) {
    $backup = $backupDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $folder = dirname($backup);
    if (!is_dir($folder) && !mkdir($folder, 0775, true) && !is_dir($folder)) {
        fwrite(STDERR, "[FAIL] Unable to create backup directory. No files changed.\n");
        exit(1);
    }
    if (file_put_contents($backup, $current, LOCK_EX) === false) {
        fwrite(STDERR, "[FAIL] Could not back up {$relative}. No files changed.\n");
        exit(1);
    }
}

foreach ($pending as $relative => [$destination, $replacement, $current]) {
    if (file_put_contents($destination, $replacement, LOCK_EX) === false) {
        fwrite(STDERR, "[FAIL] Cannot update {$relative}. Recover originals from {$backupDir}.\n");
        exit(1);
    }
    echo "Integrated: {$relative}\n";
}

echo "[PASS] A7.4.10.4 installed. Backups: "
    . 'tools/a74104_backups/' . basename($backupDir) . "\n";
echo "No database migration or reset required.\n";
