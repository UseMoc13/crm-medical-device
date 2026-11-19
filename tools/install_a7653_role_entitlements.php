<?php
/** A7.6.5.3 additive-only installer; refuses collisions without rewriting files. */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$paths = [
    'app/Services/CrmFinancialRoleEntitlementAudit.php',
    'app/Console/Commands/AuditCrmFinancialRoleEntitlements.php',
    'tests/Unit/CrmFinancialRoleEntitlementPolicyTest.php',
    'tests/Feature/CrmFinancialRoleEntitlementIsolatedTest.php',
];
$staged = [];
foreach ($paths as $path) {
    $source = $root.'/tools/a7653_payload/'.$path;
    $dest = $root.'/'.$path;
    if (!is_file($source)) {fwrite(STDERR, "[FAIL] Missing payload: $path\n"); exit(1);}
    if (file_exists($dest)) {
        if (!is_file($dest) || !hash_equals(hash_file('sha256', $source), hash_file('sha256', $dest))) {
            fwrite(STDERR, "[FAIL] Local collision: $path (no files changed)\n"); exit(1);
        }
        $staged[] = [$path, $source, $dest, false];
    } else $staged[] = [$path, $source, $dest, true];
}
foreach ($staged as [$path, $source, $dest, $add]) {
    echo ($dry ? '[CHECK] ' : '[INFO] ').($add ? 'add: ' : 'already installed: ').$path.PHP_EOL;
}
if ($dry) {echo "[PASS] Dry run; no files or database changes.\n"; exit(0);}
$created=[];
try {
    foreach ($staged as [$path, $source, $dest, $add]) {
        if (!$add) continue;
        $dir=dirname($dest);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('Cannot create directory.');
        if (file_exists($dest)) throw new RuntimeException('File appeared during install: '.$path);
        $temp = $dest.'.a7653.tmp.'.getmypid();
        if (!copy($source, $temp)) throw new RuntimeException('Could not stage '.$path);
        $created[]=$temp;
        if (!hash_equals(hash_file('sha256',$source),hash_file('sha256',$temp))) throw new RuntimeException('Payload digest mismatch: '.$path);
        if (!rename($temp, $dest)) throw new RuntimeException('Could not move '.$path);
        $created[]=$dest;
        echo '[PASS] Added: '.$path.PHP_EOL;
    }
} catch (Throwable $e) {
    foreach (array_reverse($created) as $file) if(is_file($file)) @unlink($file);
    fwrite(STDERR, '[FAIL] Aborted: '.$e->getMessage().PHP_EOL);
    exit(1);
}
echo "[PASS] A7.6.5.3 installed. No existing files edited; no migrations or business DB writes.\n";
