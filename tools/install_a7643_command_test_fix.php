<?php
/** A7.6.4.3: surgical test-only fix. No Laravel bootstrap or DB access. */
declare(strict_types=1);

$root = dirname(__DIR__);
$relative = 'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$target = $root.'/'.$relative;
$payload = __DIR__.'/a7643_payload/'.$relative;
$oldHash = 'dbedd8f8063e7a8b94c7419c00fac39156377e2b415193f587fd7dede49a2525';
$newHash = 'b404b0600a12f11af6a74398e30745b79507e6131120c933f11dafce776d01ba';
$dry = in_array('--dry-run', $argv, true);
function a7643_fail(string $message): never {
    fwrite(STDERR, '[FAIL] '.$message.PHP_EOL);
    exit(1);
}
function a7643_lf(string $content): string {
    return str_replace(["\r\n", "\r"], "\n", $content);
}
function a7643_read(string $path): string {
    if (!is_file($path) || !is_readable($path)) a7643_fail('Missing/unreadable: '.$path);
    $text = file_get_contents($path);
    if ($text === false) a7643_fail('Cannot read: '.$path);
    return $text;
}
$before = a7643_read($target);
$new = a7643_lf(a7643_read($payload));
if (hash('sha256', $new) !== $newHash) a7643_fail('Payload checksum mismatch.');
$current = hash('sha256', a7643_lf($before));
if ($current === $newHash) {
    echo '[PASS] A7.6.4.3 already applied; no changes.'.PHP_EOL;
    exit(0);
}
if ($current !== $oldHash) {
    a7643_fail('Unexpected local test content; refusing overwrite. Compare local modifications before applying.');
}
if (!str_contains($before, "Artisan::call('crm:announcements:publish-due')")) {
    a7643_fail('Expected isolated fixture invocation not found.');
}
if (str_contains($new, "Artisan::call('crm:announcements:publish-due')")
    || !str_contains($new, "(new Announcement())->getConnection()->getName()")
    || !str_contains($new, '$command->run(new ArrayInput([]), new BufferedOutput())')) {
    a7643_fail('New fixture is missing required execution safeguards.');
}
echo '[CHECK] patch: '.$relative.PHP_EOL;
if ($dry) {
    echo '[PASS] Dry run. No files, Git index, migrations or DB changed.'.PHP_EOL;
    exit(0);
}
$backupDir = $root.'/tools/a7643_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($backupDir, 0775, true) && !is_dir($backupDir)) a7643_fail('Cannot create backup dir.');
$backup = $backupDir.'/CrmAnnouncementScheduleIsolatedTest.php';
if (file_put_contents($backup, $before, LOCK_EX) !== strlen($before)) a7643_fail('Backup failed; unchanged.');
$after = str_contains($before, "\r\n") ? str_replace("\n", "\r\n", $new) : $new;
$temp = $target.'.a7643-'.bin2hex(random_bytes(4)).'.tmp';
if (file_put_contents($temp, $after, LOCK_EX) !== strlen($after)) {
    @unlink($temp);
    a7643_fail('Cannot stage update; original unchanged.');
}
$lint = [];
$exit = 1;
exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1', $lint, $exit);
if ($exit !== 0) {
    @unlink($temp);
    a7643_fail('PHP syntax validation failed: '.implode(' ', $lint));
}
if (hash('sha256', a7643_lf(a7643_read($target))) !== $oldHash) {
    @unlink($temp);
    a7643_fail('Target changed while preparing; aborting.');
}
if (!rename($temp, $target)) {
    @unlink($temp);
    a7643_fail('Could not replace test; original and backup available.');
}
echo '[PASS] Updated: '.$relative.PHP_EOL;
echo '[PASS] Backup: '.$backupDir.PHP_EOL;
echo 'Only the PHPUnit fixture changed. No app command, controller, scheduler, model, migration or business records modified.'.PHP_EOL;
