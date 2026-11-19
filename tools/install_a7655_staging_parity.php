<?php
/**
 * A7.6.5.5 guarded additive-only installer for CRM Big-Changes.
 * No existing file is overwritten. Safe to repeat. No business DB access.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$payload = __DIR__ . '/a7655_payload';
$paths = [
    'app/Services/CrmStagingInvoiceParity.php',
    'app/Console/Commands/AuditCrmStagingInvoiceParity.php',
    'config/crm_financial_parity.php',
    'tests/Unit/CrmStagingInvoiceParityGuardTest.php',
    'tests/Unit/CrmStagingInvoiceParitySourceTest.php',
    'tests/Feature/CrmStagingInvoiceParitySqliteTest.php',
];

$valid = [[], ['--dry-run']];
$options = array_slice($argv, 1);
if (!in_array($options, $valid, true)) {
    fwrite(STDERR, "Usage: php tools/install_a7655_staging_parity.php [--dry-run]\n");
    exit(2);
}
$dry = $options === ['--dry-run'];
if (!is_file($root . '/artisan') || !is_file($root . '/bootstrap/app.php')) {
    fwrite(STDERR, "[FAIL] Must run inside the Laravel CRM project root.\n");
    exit(2);
}

// Normalize line endings so a Windows/Unix re-run remains idempotent.
$normalize = static fn (string $s): string => str_replace("\r\n", "\n", $s);
$errors = [];
$pending = [];
foreach ($paths as $rel) {
    $source = $payload . '/' . $rel;
    $target = $root . '/' . $rel;
    if (!is_file($source) || !is_readable($source)) {
        $errors[] = 'Missing payload: ' . $rel;
        continue;
    }
    $expected = file_get_contents($source);
    if ($expected === false) {
        $errors[] = 'Unreadable payload: ' . $rel;
        continue;
    }
    if (is_file($target)) {
        $current = file_get_contents($target);
        if ($current === false || hash('sha256', $normalize($current)) !== hash('sha256', $normalize($expected))) {
            $errors[] = 'Collision with existing local file: ' . $rel;
        } else {
            echo "[SKIP] Already installed: {$rel}\n";
        }
    } else {
        if (file_exists($target)) {
            $errors[] = 'Destination path is not a regular file: ' . $rel;
        } else {
            $pending[$rel] = $expected;
            echo "[CHECK] Add: {$rel}\n";
        }
    }
}
if ($errors !== []) {
    foreach ($errors as $error) fwrite(STDERR, '[FAIL] ' . $error . "\n");
    fwrite(STDERR, "[FAIL] No files were added. Resolve conflicts manually.\n");
    exit(1);
}
if ($dry) {
    echo "[PASS] Dry run; no application files, DB records or Git index changed.\n";
    exit(0);
}
$added = [];
try {
    foreach ($pending as $rel => $contents) {
        $target = $root . '/' . $rel;
        $dir = dirname($target);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create directory for ' . $rel);
        }
        // Exclusive-create prevents overwriting a file created since preflight.
        $handle = @fopen($target, 'x');
        if ($handle === false) throw new RuntimeException('Cannot exclusively create ' . $rel);
        $added[] = $target;
        try {
            $wrote = fwrite($handle, $contents);
            if ($wrote !== strlen($contents)) throw new RuntimeException('Partial write for ' . $rel);
        } finally {
            fclose($handle);
        }
        echo "[PASS] Added: {$rel}\n";
    }
} catch (Throwable $e) {
    foreach ($added as $target) @unlink($target);
    fwrite(STDERR, "[FAIL] Install aborted and new files rolled back: " . $e->getMessage() . "\n");
    exit(1);
}
echo "[PASS] A7.6.5.5 installed (additive-only). No existing application files, CRM records or permissions changed.\n";
echo "[INFO] Staging SQL query gate defaults OFF. Safe command without opt-in returns BLOCKED.\n";
