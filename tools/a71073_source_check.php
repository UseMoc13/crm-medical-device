<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$files = [
    'tools/a71073_align_role_audit_test.php',
    'tools/a71073_source_check.php',
    'docs/A71073_TEST_ALIGNMENT.md',
];
foreach ($files as $relative) {
    if (!is_file($root . '/' . $relative)) {
        fwrite(STDERR, '[FAIL] Missing ' . $relative . "\n");
        exit(1);
    }
    echo '[PASS] ' . $relative . "\n";
}
foreach (array_slice($files, 0, 2) as $relative) {
    $lines = [];
    $code = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($root . '/' . $relative) . ' 2>&1', $lines, $code);
    if ($code !== 0) {
        fwrite(STDERR, '[FAIL] PHP syntax ' . $relative . ': ' . implode(' ', $lines) . "\n");
        exit(1);
    }
}
$lines = [];
$code = 0;
exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($root . '/tools/a71073_align_role_audit_test.php') . ' --self-test 2>&1', $lines, $code);
foreach ($lines as $line) echo $line . "\n";
if ($code !== 0) exit($code);
echo "[RESULT] A71073_SOURCE_READY; no Laravel/DB access\n";
