<?php
/** A7.6.5.6 guarded additive-only installer; uses no database connection. */
declare(strict_types=1);

$root = dirname(__DIR__);
$payload = __DIR__ . '/a7656_payload';
$paths = [
    'app/Services/CrmMultiRoleReportsPrivacyAudit.php',
    'app/Console/Commands/AuditCrmMultiRoleReportsPrivacy.php',
    'tests/Unit/CrmMultiRoleReportsPrivacyPolicyTest.php',
    'tests/Feature/CrmMultiRoleReportsPrivacyIsolatedTest.php',
    'tests/Feature/CrmMultiRoleReportsHttpIsolationTest.php',
];
$args = array_slice($argv, 1);
if ($args !== [] && $args !== ['--dry-run']) {
    fwrite(STDERR, "Usage: php tools/install_a7656_multi_role_privacy.php [--dry-run]\n");
    exit(2);
}
$dryRun = $args === ['--dry-run'];
if (!is_file($root.'/artisan') || !is_file($root.'/bootstrap/app.php')
    || !is_file($root.'/app/Services/CrmFinancialRoleEntitlementAudit.php')
    || !is_file($root.'/app/Services/CrmInvoiceCsvPolicy.php')) {
    fwrite(STDERR, "[FAIL] CRM project root and A7.6.5.4 prerequisites required.\n");
    exit(2);
}
$normalize = static fn (string $value): string => str_replace("\r\n", "\n", $value);
$errors = [];
$pending = [];
foreach ($paths as $relative) {
    $source = $payload.'/'.$relative;
    $target = $root.'/'.$relative;
    if (!is_file($source) || !is_readable($source)) {
        $errors[] = 'Missing payload: '.$relative;
        continue;
    }
    $contents = file_get_contents($source);
    if ($contents === false) {
        $errors[] = 'Cannot read payload: '.$relative;
        continue;
    }
    if (file_exists($target)) {
        if (!is_file($target)) {
            $errors[] = 'Destination is not a regular file: '.$relative;
            continue;
        }
        $original = file_get_contents($target);
        if ($original === false || hash('sha256', $normalize($original)) !== hash('sha256', $normalize($contents))) {
            $errors[] = 'Local file collision: '.$relative;
        } else {
            echo "[SKIP] Already installed: {$relative}\n";
        }
    } else {
        $pending[$relative] = $contents;
        echo "[CHECK] Add: {$relative}\n";
    }
}
if ($errors !== []) {
    foreach ($errors as $error) fwrite(STDERR, '[FAIL] '.$error."\n");
    fwrite(STDERR, "[FAIL] No files added; review local conflicts.\n");
    exit(1);
}
if ($dryRun) {
    echo "[PASS] Dry run; no application files, Git index or DB modified.\n";
    exit(0);
}
$added = [];
try {
    foreach ($pending as $relative => $contents) {
        $target = $root.'/'.$relative;
        $dir = dirname($target);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create directory for '.$relative);
        }
        $handle = @fopen($target, 'x'); // exclusive: never overwrite local changes
        if ($handle === false) throw new RuntimeException('Cannot exclusively create '.$relative);
        $added[] = $target;
        try {
            $length = strlen($contents);
            $written = 0;
            while ($written < $length) {
                $n = fwrite($handle, substr($contents, $written));
                if ($n === false || $n === 0) throw new RuntimeException('Partial write for '.$relative);
                $written += $n;
            }
        } finally {
            fclose($handle);
        }
        echo "[PASS] Added: {$relative}\n";
    }
} catch (Throwable $e) {
    foreach ($added as $target) @unlink($target);
    fwrite(STDERR, '[FAIL] Install rolled back new files: '.$e->getMessage()."\n");
    exit(1);
}
echo "[PASS] A7.6.5.6 installed. Additive-only; no existing files, role grants, user memberships or business data changed.\n";
echo "[INFO] Role combinations are hypothetical; privacy issues remain REVIEW_REQUIRED.\n";
