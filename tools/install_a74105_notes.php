<?php
/** A7.4.10.5 — fix the mention dropdown clipped inside Notes Create/Edit card. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payload = __DIR__ . '/a74105_payload';
$expected = [
    'resources/views/notes/_form.blade.php' => '52c3223fc8efdd90fade027d1cb3b0172bbaecbdcde6b719e6e939ac5d2c3b28',
    'resources/views/notes/_style.blade.php' => 'd12f1888332d0e3f45a0597a1d29104af5eb8f322e2bab1a5c401babaf4e80d5',
];
$sha = static fn (string $value): string => hash('sha256', str_replace(["\r\n", "\r"], "\n", $value));
$pending = [];
foreach ($expected as $relative => $knownHash) {
    $target = $root . '/' . $relative;
    $source = $payload . '/' . $relative;
    if (!is_file($target) || !is_file($source)) {
        fwrite(STDERR, "[FAIL] Missing target/payload {$relative}. Nothing changed.\n");
        exit(1);
    }
    $before = file_get_contents($target);
    $after = file_get_contents($source);
    if ($before === false || $after === false) {
        fwrite(STDERR, "[FAIL] Cannot read {$relative}. Nothing changed.\n");
        exit(1);
    }
    if ($sha($before) === $sha($after)) {
        echo "[PASS] Already integrated: {$relative}\n";
        continue;
    }
    if ($sha($before) !== $knownHash) {
        fwrite(STDERR, "[FAIL] Unexpected modifications in {$relative}; nothing changed.\n");
        fwrite(STDERR, "       This patch expects A7.4.10.4. Keep your changes and merge manually if customized.\n");
        exit(1);
    }
    $pending[$relative] = [$target, $before, $after];
}
if (!$pending) { echo "[PASS] A7.4.10.5 already installed; no action needed.\n"; exit(0); }
$backupDir = __DIR__ . '/a74105_backups/' . date('Ymd-His') . '-' . random_int(1000,9999);
foreach ($pending as $relative => [$target, $before, $after]) {
    $path = $backupDir . '/' . $relative;
    if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0775, true) && !is_dir(dirname($path))) {
        fwrite(STDERR, "[FAIL] Cannot create backup directory; nothing changed.\n");
        exit(1);
    }
    if (file_put_contents($path, $before, LOCK_EX) === false) {
        fwrite(STDERR, "[FAIL] Cannot back up {$relative}; nothing changed.\n");
        exit(1);
    }
}
$changed = [];
foreach ($pending as $relative => [$target, $before, $after]) {
    if (file_put_contents($target, $after, LOCK_EX) === false) {
        foreach ($changed as $item) {
            file_put_contents($pending[$item][0], $pending[$item][1], LOCK_EX);
        }
        fwrite(STDERR, "[FAIL] Write failed; earlier files restored. Backups: {$backupDir}\n");
        exit(1);
    }
    $changed[] = $relative;
    echo "Integrated: {$relative}\n";
}
echo "[PASS] A7.4.10.5 installed. Backups: tools/a74105_backups/" . basename($backupDir) . "\n";
echo "No migration or database reset necessary.\n";
