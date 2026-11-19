<?php
/** A7.6.4.1: patch only the runtime preflight; fail closed on local source drift. */
declare(strict_types=1);
$root = dirname(__DIR__);
$relative = 'tools/a764_runtime_preflight.php';
$target = $root.'/'.$relative;
$payload = __DIR__.'/a7641_payload/tools/a764_runtime_preflight.php';
$dry = in_array('--dry-run', $argv, true);
function a7641_fail(string $message): never {
    fwrite(STDERR, '[FAIL] '.$message.PHP_EOL);
    exit(1);
}
function a7641_normal(string $data): string {
    return str_replace(["\r\n", "\r"], "\n", $data);
}
function a7641_read(string $path): string {
    if (!is_file($path) || !is_readable($path)) a7641_fail('Missing/unreadable file: '.$path);
    $data=file_get_contents($path);
    if ($data === false) a7641_fail('Unable to read: '.$path);
    return $data;
}
$current = a7641_read($target);
$replacement = a7641_normal(a7641_read($payload));
$hash = hash('sha256', a7641_normal($current));
$oldHash = '0f0d2dce154f22c615557f93fb5be80a84c72ed08cc12f8af03457603529895f';
$newHash = '7571456d1dd98a5b376d77331a80780395719175ff041b09a89bc01d1ffdfacc';
if (hash('sha256', $replacement) !== $newHash) a7641_fail('Payload integrity check failed.');
if ($hash === $newHash) {
    echo '[PASS] A7.6.4.1 already installed; no changes.'.PHP_EOL;
    exit(0);
}
if ($hash !== $oldHash) {
    a7641_fail('Unknown local modifications to '.$relative.'; refusing overwrite. Compare manually with the A7.6.4 backup.');
}
if (substr_count($current, 'Artisan::has(')!==3) a7641_fail('Unexpected preflight source; refusing patch.');
echo '[CHECK] patch: '.$relative.PHP_EOL;
if ($dry) {
    echo '[PASS] Dry run. No files, Git index or database changed.'.PHP_EOL;
    exit(0);
}
$backupDir = $root.'/tools/a7641_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($backupDir,0775,true) && !is_dir($backupDir)) a7641_fail('Could not create backup directory.');
$backup = $backupDir.'/a764_runtime_preflight.php';
if (file_put_contents($backup, $current, LOCK_EX)!==strlen($current)) a7641_fail('Unable to write backup.');
$final = str_contains($current, "\r\n") ? str_replace("\n", "\r\n", $replacement) : $replacement;
$tmp = $target.'.a7641-'.bin2hex(random_bytes(4)).'.tmp';
if (file_put_contents($tmp, $final, LOCK_EX)!==strlen($final)) {
    @unlink($tmp);
    a7641_fail('Unable to write staged temporary file; original remains intact.');
}
$lint=[];$rc=1;
exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($tmp).' 2>&1', $lint, $rc);
if ($rc!==0) { @unlink($tmp); a7641_fail('PHP syntax check failed on staged file; original remains intact.'); }
if (!rename($tmp, $target)) { @unlink($tmp); a7641_fail('Could not replace preflight; restore from '.$backup); }
echo '[PASS] Fixed Artisan command lookup in '.$relative.PHP_EOL;
echo '[PASS] Original backup: '.$backup.PHP_EOL;
echo 'No changes to controllers, scheduler, migrations, application data or Git index.'.PHP_EOL;
