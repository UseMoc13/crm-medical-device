<?php
/** A7.6.2 additive-only, guarded, atomic per-file installer. No SQL or migrations. */
$root = dirname(__DIR__);
$payload = __DIR__.'/a762_payload';
$expected = [
    'app/Services/CrmScopedKpiQuery.php' => '8bcbf5d2a375d452311ef08ac5b4e1c1827b030aebd31be70242ed76ac495325',
    'tests/Unit/CrmScopedKpiPolicyTest.php' => 'c382e8bb2e2b531f25349df6d09471d7eee362a7ec7e336fb023282a229274c8',
    'tests/Feature/CrmScopedKpiIsolatedTest.php' => '026eb4f63da4adf53376485eb17af1d9d1f3578323d122481bb1c0dea32a0e98',
];
$dryRun = in_array('--dry-run', $argv, true);
$plan = [];
foreach ($expected as $relative => $hash) {
    $src = $payload.'/'.$relative;
    $dst = $root.'/'.$relative;
    if (!is_file($src) || hash_file('sha256', $src) !== $hash) {
        fwrite(STDERR, '[FAIL] Missing or altered package payload: '.$relative.PHP_EOL);
        exit(1);
    }
    if (file_exists($dst) && !is_file($dst)) {
        fwrite(STDERR, '[FAIL] Unsafe destination type: '.$relative.PHP_EOL);
        exit(1);
    }
    if (is_file($dst) && hash_file('sha256', $dst) !== $hash) {
        fwrite(STDERR, '[FAIL] Refusing conflicting local file: '.$relative.PHP_EOL);
        exit(1);
    }
    $plan[] = [is_file($dst) ? 'same' : 'add', $src, $dst, $relative, $hash];
}
foreach ($plan as [$kind, , , $relative]) {
    echo ($dryRun ? '[CHECK] ' : '[INFO] ').($kind === 'add' ? 'add: ' : 'already installed: ').$relative.PHP_EOL;
}
if ($dryRun) {
    echo '[PASS] Dry run: no files or database changed.'.PHP_EOL;
    exit(0);
}
$added = [];
foreach ($plan as [$kind, $src, $dst, $relative, $hash]) {
    if ($kind === 'same') continue;
    $dir = dirname($dst);
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        foreach ($added as $p) @unlink($p);
        fwrite(STDERR, '[FAIL] Cannot create directory: '.$relative.PHP_EOL);
        exit(1);
    }
    $temp = $dst.'.a762.'.bin2hex(random_bytes(5)).'.tmp';
    if (!copy($src, $temp) || hash_file('sha256', $temp) !== $hash) {
        @unlink($temp);
        foreach ($added as $p) @unlink($p);
        fwrite(STDERR, '[FAIL] Failed staged copy: '.$relative.PHP_EOL);
        exit(1);
    }
    // Prevent accidental replacement if another process created the target.
    if (file_exists($dst) || !rename($temp, $dst)) {
        @unlink($temp);
        foreach ($added as $p) @unlink($p);
        fwrite(STDERR, '[FAIL] Destination became unavailable: '.$relative.PHP_EOL);
        exit(1);
    }
    $added[] = $dst;
    echo '[PASS] Added: '.$relative.PHP_EOL;
}
echo '[PASS] A7.6.2 installed: '.count($added).' new file(s). No existing files overwritten.'.PHP_EOL;
echo 'No migrations, controllers, dashboard UI or business records changed.'.PHP_EOL;
