<?php
/** A7.6.3.1: guarded and idempotent schema compatibility fix. No migrations or SQL. */
declare(strict_types=1);
$root = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv, true);
$files = [
    'app/Services/CrmDashboardViewData.php' => ['before' => 'ddd96d6b4cfd8b752e902c2e1c60f76bcb951757481fca003e5441382bced282', 'after' => '7edade7ca657c47b2bbd5ba5b4a04936b37243dd2693e5f16acfbe5160226b8d'],
    'tests/Feature/CrmDashboardViewerIsolationTest.php' => ['before' => '2e37befadb928fcd168bd85fdfdc9d80add35f14ab156a6d80ba8d969c833c3c', 'after' => '05bfa857b6de9c241467967ee182f862ca951caf95e4cc907494c8e2c55e128b'],
    'tools/a763_runtime_preflight.php' => ['before' => 'af79cd31d12ea1aa3f76b635f2aae4fe34981099590ae65d2f83b0abe01d8812', 'after' => 'a2e016f8bc5ea896e7928a9f693ff76f9f9a0299c0229cc1c02ab2de6298796c'],
];
function a7631_fail(string $reason): never {
    fwrite(STDERR, '[FAIL] '.$reason.PHP_EOL);
    exit(1);
}
function a7631_norm(string $text): string {
    return str_replace(["\r\n", "\r"], "\n", $text);
}
function a7631_text(string $path): string {
    if (!is_file($path) || !is_readable($path)) a7631_fail('Missing/unreadable file: '.$path);
    $text = file_get_contents($path);
    if ($text === false) a7631_fail('Cannot read: '.$path);
    return $text;
}
$updates = [];
// Pre-check ALL destinations and payloads before any file write.
foreach ($files as $relative => $hashes) {
    $destination = $root.'/'.$relative;
    $payload = __DIR__.'/a7631_payload/'.$relative;
    $current = a7631_text($destination);
    $replacement = a7631_norm(a7631_text($payload));
    if (hash('sha256', $replacement) !== $hashes['after']) a7631_fail('Unexpected bundled payload: '.$relative);
    $fingerprint = hash('sha256', a7631_norm($current));
    if ($fingerprint === $hashes['after']) {
        echo '[PASS] Already fixed: '.$relative.PHP_EOL;
        continue;
    }
    if ($fingerprint !== $hashes['before']) {
        a7631_fail('Local file differs from the audited A7.6.3 baseline: '.$relative.'. No files changed.');
    }
    $lineEnding = str_contains($current, "\r\n") ? "\r\n" : "\n";
    $updates[$relative] = [
        'old' => $current,
        'new' => $lineEnding === "\r\n" ? str_replace("\n", "\r\n", $replacement) : $replacement,
    ];
    echo '[CHECK] patch: '.$relative.PHP_EOL;
}
if ($dryRun) {
    echo '[PASS] Dry run. No application files or database changed.'.PHP_EOL;
    exit(0);
}
if ($updates === []) {
    echo '[PASS] A7.6.3.1 already installed; no changes.'.PHP_EOL;
    exit(0);
}
$backup = $root.'/tools/a7631_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($backup, 0775, true) && !is_dir($backup)) a7631_fail('Unable to create backup directory.');
$written = [];
try {
    foreach ($updates as $relative => $data) {
        $target = $backup.'/'.$relative;
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true)) {
            throw new RuntimeException('Backup directory cannot be created.');
        }
        if (file_put_contents($target, $data['old'], LOCK_EX) !== strlen($data['old'])) {
            throw new RuntimeException('Cannot back up '.$relative);
        }
    }
    foreach ($updates as $relative => $data) {
        $written[] = $relative;
        if (file_put_contents($root.'/'.$relative, $data['new'], LOCK_EX) !== strlen($data['new'])) {
            throw new RuntimeException('Cannot write '.$relative);
        }
        echo '[PASS] Fixed: '.$relative.PHP_EOL;
    }
} catch (Throwable $e) {
    foreach ($written as $relative) {
        file_put_contents($root.'/'.$relative, $updates[$relative]['old'], LOCK_EX);
    }
    a7631_fail('Rolled back installed files after error: '.$e->getMessage());
}
echo '[PASS] A7.6.3.1 installed. Backup: '.$backup.PHP_EOL;
echo 'No controllers, migrations, business data, Dashboard Blade, or notification settings changed.'.PHP_EOL;
