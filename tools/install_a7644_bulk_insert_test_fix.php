<?php
/** A7.6.4.4: fixture-only bulk insert schema fix; no app or DB access. */
declare(strict_types=1);

$root = dirname(__DIR__);
$relative = 'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php';
$target = $root.'/'.$relative;
$payload = __DIR__.'/a7644_payload/'.$relative;
$oldHash = 'b404b0600a12f11af6a74398e30745b79507e6131120c933f11dafce776d01ba';
$newHash = 'f20165a6c38be81f8d98567fd5258040de1684203a26fcd578b35d0bdddc8441';
$dry = in_array('--dry-run', $argv, true);
function a7644_fail(string $message): never {
    fwrite(STDERR, '[FAIL] '.$message.PHP_EOL);
    exit(1);
}
function a7644_lf(string $content): string {
    return str_replace(["\r\n", "\r"], "\n", $content);
}
function a7644_read(string $path): string {
    if (!is_file($path) || !is_readable($path)) a7644_fail('Missing or unreadable: '.$path);
    $data = file_get_contents($path);
    if ($data === false) a7644_fail('Cannot read: '.$path);
    return $data;
}
if (count(array_diff($argv, [$argv[0], '--dry-run'])) > 0) a7644_fail('Unsupported arguments. Use --dry-run or no arguments.');
$before = a7644_read($target);
$afterLf = a7644_lf(a7644_read($payload));
if (hash('sha256', $afterLf) !== $newHash) a7644_fail('Payload checksum mismatch.');
$beforeHash = hash('sha256', a7644_lf($before));
if ($beforeHash === $newHash) {
    echo '[PASS] A7.6.4.4 already applied; no changes.'.PHP_EOL;
    exit(0);
}
if ($beforeHash !== $oldHash) a7644_fail('Unexpected local test changes; refusing overwrite.');
if (!str_contains($before, "['announcement_id'=>\$ids[0]")
    || !str_contains($before, "'scheduled_at'=>\$now->copy()->subHour()")) {
    a7644_fail('Previous fixture pattern missing; aborting.');
}
if (substr_count($afterLf, "'scheduled_at' =>") !== 3
    || substr_count($afterLf, "'published_at' =>") !== 3
    || !str_contains($afterLf, "where('scheduled_at', '<=', \$now)->exists()")
    || !str_contains($afterLf, "where('scheduled_at', '>', \$now)->exists()")
    || !str_contains($afterLf, 'self::assertSame(0, $command->run(new ArrayInput([]), new BufferedOutput()))')) {
    a7644_fail('New fixture safeguards missing.');
}
echo '[CHECK] patch: '.$relative.PHP_EOL;
if ($dry) {
    echo '[PASS] Dry run. No application files, Git index or DB changed.'.PHP_EOL;
    exit(0);
}
$backupDir = $root.'/tools/a7644_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($backupDir, 0775, true) && !is_dir($backupDir)) a7644_fail('Cannot create backup directory.');
$backup = $backupDir.'/CrmAnnouncementScheduleIsolatedTest.php';
if (file_put_contents($backup, $before, LOCK_EX) !== strlen($before)) a7644_fail('Backup failed; original unchanged.');
$newContent = str_contains($before, "\r\n") ? str_replace("\n", "\r\n", $afterLf) : $afterLf;
$tmp = $target.'.a7644-'.bin2hex(random_bytes(4)).'.tmp';
if (file_put_contents($tmp, $newContent, LOCK_EX) !== strlen($newContent)) {
    @unlink($tmp);
    a7644_fail('Staging failed; original unchanged.');
}
$lint = [];
$exitCode = 1;
exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($tmp).' 2>&1', $lint, $exitCode);
if ($exitCode !== 0) {
    @unlink($tmp);
    a7644_fail('Syntax error: '.implode(' ', $lint));
}
if (hash('sha256', a7644_lf(a7644_read($target))) !== $oldHash) {
    @unlink($tmp);
    a7644_fail('Target changed during install.');
}
if (!rename($tmp, $target)) {
    @unlink($tmp);
    a7644_fail('Could not replace test; backup saved.');
}
echo '[PASS] Fixed bulk insert dates: '.$relative.PHP_EOL;
echo '[PASS] Backup: '.$backupDir.PHP_EOL;
echo 'Test-only: no changes to announcement command, scheduler, Dashboard, migrations or CRM records.'.PHP_EOL;
