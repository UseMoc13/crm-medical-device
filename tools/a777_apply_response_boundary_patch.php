<?php
/** A7.7.7: narrowly scoped middleware ordering fix. No Laravel boot or DB. */
declare(strict_types=1);

$mode = $argv[1] ?? '';
if (!in_array($mode, ['--dry-run', '--apply'], true) || count($argv) !== 2) {
    fwrite(STDERR, "Usage: php tools/a777_apply_response_boundary_patch.php --dry-run|--apply\n");
    exit(2);
}
$root = dirname(__DIR__);
$branch = trim((string) shell_exec('git -C '.escapeshellarg($root).' branch --show-current 2>NUL'));
if ($branch !== 'Big-Changes') {
    fwrite(STDERR, "[FAIL] Requires git branch Big-Changes (got: {$branch}).\n");
    exit(1);
}

$patches = [
    'bootstrap/app.php' => [
        <<<'OLD'
            \App\Http\Middleware\EnsureCrmAuthenticated::class,
            \App\Http\Middleware\EnforceCrmRouteAuthorization::class,
            \App\Http\Middleware\PreventCrmResponseCaching::class,
OLD,
        <<<'NEW'
            \App\Http\Middleware\PreventCrmResponseCaching::class,
            \App\Http\Middleware\EnsureCrmAuthenticated::class,
            \App\Http\Middleware\EnforceCrmRouteAuthorization::class,
NEW,
    ],
    'tools/a771_privacy_quick_check.php' => [
        <<<'OLD'
    'Auth runs before cache middleware' => strpos($boot, 'EnsureCrmAuthenticated::class') !== false
        && strpos($boot, 'PreventCrmResponseCaching::class') !== false
        && strpos($boot, 'EnsureCrmAuthenticated::class') < strpos($boot, 'PreventCrmResponseCaching::class'),
OLD,
        <<<'NEW'
    'Cache middleware wraps auth responses' => strpos($boot, 'EnsureCrmAuthenticated::class') !== false
        && strpos($boot, 'PreventCrmResponseCaching::class') !== false
        && strpos($boot, 'PreventCrmResponseCaching::class') < strpos($boot, 'EnsureCrmAuthenticated::class'),
NEW,
    ],
    'tools/a773_quick_check.php' => [
        <<<'OLD'
$checks['Middleware: auth before RBAC before privacy'] = $auth !== false && $rbac !== false && $cache !== false && $auth < $rbac && $rbac < $cache;
OLD,
        <<<'NEW'
$checks['Middleware: privacy wraps auth then RBAC'] = $auth !== false && $rbac !== false && $cache !== false && $cache < $auth && $auth < $rbac;
NEW,
    ],
];

function lintStaged(string $path, string $code): bool {
    $temp = tempnam(dirname($path), '.a777_');
    if ($temp === false) return false;
    try {
        if (file_put_contents($temp, $code) === false) return false;
        $output = []; $exitCode = 1;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $output, $exitCode);
        return $exitCode === 0;
    } finally {
        @unlink($temp);
    }
}

$changes = [];
foreach ($patches as $relative => [$old, $new]) {
    $target = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($target)) { fwrite(STDERR, "[FAIL] Missing {$relative}\n"); exit(1); }
    $original = file_get_contents($target);
    if (!is_string($original)) { fwrite(STDERR, "[FAIL] Cannot read {$relative}\n"); exit(1); }
    $eol = str_contains($original, "\r\n") ? "\r\n" : "\n";
    $normalized = str_replace("\r\n", "\n", $original);
    if (substr_count($normalized, $new) === 1 && substr_count($normalized, $old) === 0) {
        echo "[SKIP] Already patched {$relative}\n";
        continue;
    }
    if (substr_count($normalized, $old) !== 1 || substr_count($normalized, $new) !== 0) {
        fwrite(STDERR, "[FAIL] Source anchor mismatch in {$relative}; nothing applied.\n");
        exit(1);
    }
    $updated = str_replace($old, $new, $normalized);
    $updated = $eol === "\r\n" ? str_replace("\n", "\r\n", $updated) : $updated;
    if (!lintStaged($target, $updated)) {
        fwrite(STDERR, "[FAIL] PHP syntax check failed for {$relative}; no writes.\n");
        exit(1);
    }
    $changes[$relative] = [$target, $original, $updated];
    echo "[PASS] Exact anchor and PHP syntax verified: {$relative}\n";
}

if ($mode === '--dry-run') {
    echo '[PASS] Dry-run. '.count($changes).' file(s) would change; no application/DB writes.'.PHP_EOL;
    exit(0);
}
if ($changes === []) { echo '[PASS] Already applied. No writes.'.PHP_EOL; exit(0); }
$backupDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'crm_a777_'.date('Ymd_His').'_'.bin2hex(random_bytes(3));
if (!mkdir($backupDir, 0700, true)) { fwrite(STDERR, "[FAIL] Cannot create backup folder.\n"); exit(1); }
$applied = [];
try {
    foreach ($changes as $relative => [$target, $original, $updated]) {
        $backup = $backupDir.DIRECTORY_SEPARATOR.str_replace('/', '_', $relative).'.before';
        if (file_put_contents($backup, $original) !== strlen($original)) {
            throw new RuntimeException('Cannot make backup for '.$relative);
        }
    }
    foreach ($changes as $relative => [$target, $original, $updated]) {
        if (file_put_contents($target, $updated, LOCK_EX) !== strlen($updated)) {
            throw new RuntimeException('Cannot apply '.$relative);
        }
        $applied[$relative] = $target;
    }
} catch (Throwable $error) {
    foreach ($applied as $relative => $target) {
        @file_put_contents($target, $changes[$relative][1], LOCK_EX);
    }
    fwrite(STDERR, '[FAIL] Apply interrupted; attempted rollback. '.$error->getMessage().PHP_EOL);
    exit(1);
}
echo "[PASS] Cache privacy wraps authentication + RBAC. Auth still precedes RBAC.\n";
echo "[BACKUP] {$backupDir}\n";
echo "[SAFE] No database connection, migrations, grants, or business data changes.\n";
