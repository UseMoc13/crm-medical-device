<?php
/**
 * Run once: php tools/install_crm_login.php
 * Targeted, fail-closed edits only. Makes backups of touched files.
 */
$root = dirname(__DIR__);
$web = $root . '/routes/web.php';
$bootstrap = $root . '/bootstrap/app.php';
foreach ([$web, $bootstrap] as $file) {
    if (!is_file($file) || !is_writable($file)) {
        fwrite(STDERR, "Cannot read/write: {$file}\n");
        exit(1);
    }
}
if (!is_file($root . '/routes/crm_auth.php') || !is_file($root . '/app/Http/Middleware/EnsureCrmAuthenticated.php')) {
    fwrite(STDERR, "Extract the ZIP into the Laravel project root before running this installer.\n");
    exit(1);
}
$webText = file_get_contents($web);
$bootText = file_get_contents($bootstrap);
if (preg_match('/Route::(get|match|any)\s*\(\s*[\'\"]\/?login[\'\"]/', $webText) || preg_match('/require[^;]*\/auth\.php/', $webText)) {
    fwrite(STDERR, "Existing login route or auth route registration found. Review manually to avoid duplicate routes.\n");
    exit(1);
}
if (!str_contains($webText, "require __DIR__ . '/crm_auth.php';")) {
    $webText = rtrim($webText) . "\n\n// CRM Login / Logout (registered once)\nrequire __DIR__ . '/crm_auth.php';\n";
}
$inject = '$middleware->web(append: [\\App\\Http\\Middleware\\EnsureCrmAuthenticated::class]);';
if (!str_contains($bootText, $inject)) {
    $pattern = '/(->withMiddleware\(function\s*\(Middleware\s+\$middleware\)\s*:\s*void\s*\{)/';
    if (!preg_match($pattern, $bootText)) {
        fwrite(STDERR, "Unknown bootstrap/app.php layout. No files changed. Follow README manual steps.\n");
        exit(1);
    }
    $bootText = preg_replace_callback($pattern, static fn(array $m) => $m[1] . "\n        // Require a valid session on CRM web routes. /login remains public.\n        " . $inject, $bootText, 1);
}
foreach ([[$web, $webText], [$bootstrap, $bootText]] as [$path, $new]) {
    $old = file_get_contents($path);
    if ($new === $old) {
        echo "Already configured: {$path}\n";
        continue;
    }
    $backup = $path . '.before-a731-' . date('Ymd-His') . '.bak';
    if (!copy($path, $backup)) { fwrite(STDERR, "Backup failed: {$path}\n"); exit(1); }
    if (file_put_contents($path, $new) === false) { fwrite(STDERR, "Writing failed: {$path}\n"); exit(1); }
    echo "Patched: {$path}\nBackup: {$backup}\n";
}
echo "Finished. Run php artisan optimize:clear and php artisan route:list --path=login\n";
