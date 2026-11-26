<?php

declare(strict_types=1);

/** A7.10.2 hotfix — fixes the audit only. Never modifies app config, DB, routes, or permissions. */
$root = dirname(__DIR__);
$target = $root . '/tools/a710_route_security_gate.php';
$usage = "Usage: php tools/a7102_apply_fix.php --check|--apply\n";
if (count($argv) !== 2 || !in_array($argv[1], ['--check', '--apply'], true)) {
    fwrite(STDERR, $usage);
    exit(2);
}
$apply = $argv[1] === '--apply';
$text = @file_get_contents($target);
if ($text === false) {
    fwrite(STDERR, "[STOP] Missing A7.10 route gate. Install A7.10 before the hotfix.\n");
    exit(2);
}
$bootstrap = @file_get_contents($root . '/bootstrap/app.php');
$guards = [
    'App\\Http\\Middleware\\PreventCrmResponseCaching::class',
    'App\\Http\\Middleware\\EnsureCrmAuthenticated::class',
    'App\\Http\\Middleware\\EnforceCrmRouteAuthorization::class',
];
if ($bootstrap === false || !str_contains($bootstrap, '->withMiddleware(')) {
    fwrite(STDERR, "[STOP] Cannot verify withMiddleware registration in bootstrap/app.php. Nothing changed.\n");
    exit(2);
}
foreach ($guards as $guard) {
    if (!str_contains($bootstrap, $guard)) {
        fwrite(STDERR, '[STOP] Actual bootstrap/app.php is missing '.$guard.'; manual security review required. No changes made.'.PHP_EOL);
        exit(2);
    }
}
$webRead = '$web = $app[\'router\']->getMiddlewareGroups()[\'web\'] ?? [];';
$httpResolve = '$app->make(\\Illuminate\\Contracts\\Http\\Kernel::class);';
$consoleBoot = '$app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();';
if (!str_contains($text, $consoleBoot) || substr_count($text, $webRead) !== 1) {
    fwrite(STDERR, "[STOP] Route gate source differs from A7.10 baseline. Nothing changed.\n");
    exit(2);
}
$readIndex = strpos($text, $webRead);
$resolveIndex = strpos($text, $httpResolve);
if ($resolveIndex !== false) {
    if (substr_count($text, $httpResolve) !== 1 || $resolveIndex > $readIndex) {
        fwrite(STDERR, "[STOP] HTTP Kernel resolution is misplaced or duplicated. Manual review required.\n");
        exit(2);
    }
    echo "[PASS] HTTP Kernel is resolved before inspecting the web middleware group.\n";
    echo "[RESULT] A7102_PATCH_ALREADY_INSTALLED; no files modified\n";
    exit(0);
}
if (!str_contains($text, 'A710_ROUTE_SECURITY_CONTRACT_FAILED') || !str_contains($text, 'getMiddlewareGroups')) {
    fwrite(STDERR, "[STOP] Route gate contract markers missing; cannot safely patch.\n");
    exit(2);
}
$replacement = <<<'CHANGE'
// Laravel 11/12 registers withMiddleware() when the HTTP Kernel is resolved.
// Console bootstrap loads routes but does not materialize the router's web group.
// Resolve the HTTP Kernel WITHOUT dispatching an HTTP request or touching CRM data.
$app->make(\Illuminate\Contracts\Http\Kernel::class);
$web = $app['router']->getMiddlewareGroups()['web'] ?? [];
CHANGE;
$patched = str_replace($webRead, $replacement, $text, $count);
if ($count !== 1 || $patched === $text) {
    fwrite(STDERR, "[STOP] Hotfix did not match exactly one injection point.\n");
    exit(2);
}
if (!$apply) {
    echo "[READY] A7102 HTTP Kernel middleware audit fix; one file, no application security changes.\n";
    echo "[READY] Current bootstrap/app.php registers all three CRM guards.\n";
    echo "[RESULT] A7102_PATCH_COMPATIBLE; no files modified\n";
    exit(0);
}
$tmp = @tempnam(dirname($target), '.a7102-');
if ($tmp === false) {
    fwrite(STDERR, "[STOP] Unable to create temporary file in tools directory.\n");
    exit(2);
}
try {
    if (file_put_contents($tmp, $patched, LOCK_EX) !== strlen($patched)) {
        throw new RuntimeException('Cannot write patched temporary file.');
    }
    $output = []; $rc = 0;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($tmp).' 2>&1', $output, $rc);
    if ($rc !== 0) {
        throw new RuntimeException('Patched PHP syntax failed: '.implode(' ', $output));
    }
    $backups = $root . '/storage/app/private/a7102_route_gate_backups';
    if (!is_dir($backups) && !mkdir($backups, 0750, true) && !is_dir($backups)) {
        throw new RuntimeException('Cannot create backup directory.');
    }
    $stamp = date('Ymd-His') . '-' . bin2hex(random_bytes(4));
    $backup = $backups . '/a710_route_security_gate.' . $stamp . '.php.bak';
    if (!copy($target, $backup)) {
        throw new RuntimeException('Backup failed. No modification attempted.');
    }
    if (file_put_contents($target, $patched, LOCK_EX) !== strlen($patched)) {
        // Best effort restore if a write unexpectedly fails.
        @copy($backup, $target);
        throw new RuntimeException('Patch write failed. Restore attempted from backup.');
    }
    echo '[BACKUP] '.$backup.PHP_EOL;
    echo "[PATCH] tools/a710_route_security_gate.php\n";
    echo "[RESULT] A7102_MIDDLEWARE_GATE_FIX_APPLIED\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[STOP] '.$e->getMessage().PHP_EOL);
    exit(2);
} finally {
    @unlink($tmp);
}
