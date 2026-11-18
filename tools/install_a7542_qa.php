<?php
/** A7.5.4.2: additive-only installer, no database reads or writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
$payload = __DIR__.'/a7542_payload';
$paths = ['tests/Feature/CrmIntegrationAtomicityIsolatedTest.php'];
$dry = in_array('--dry-run', array_slice($argv,1), true);
foreach (array_slice($argv,1) as $arg) {
    if ($arg !== '--dry-run') { fwrite(STDERR,"Unknown option: $arg\n"); exit(2); }
}
$tasks=[];
foreach ($paths as $rel) {
    $src = "$payload/$rel";
    $target = "$root/$rel";
    if (!is_file($src)) { fwrite(STDERR,"[FAIL] Payload missing: $rel\n"); exit(1); }
    if (is_file($target)) {
        if (hash_file('sha256',$src)!==hash_file('sha256',$target)) {
            fwrite(STDERR,"[FAIL] Existing modified test; refusing to overwrite: $rel\n"); exit(1);
        }
        $tasks[$rel]='unchanged';
    } elseif (file_exists($target)) {
        fwrite(STDERR,"[FAIL] Path exists but is not a file: $rel\n"); exit(1);
    } else $tasks[$rel]='add';
}
foreach ($tasks as $rel=>$what) echo '[CHECK] '.$what.': '.$rel.PHP_EOL;
if ($dry) { echo "[PASS] Dry run; no files or database changed.\n"; exit(0); }
$written=[];
try {
    foreach ($tasks as $rel=>$what) {
        if ($what==='unchanged') continue;
        $target="$root/$rel";
        if (!is_dir(dirname($target)) && !mkdir(dirname($target),0775,true) && !is_dir(dirname($target))) {
            throw new RuntimeException("Cannot create directory: $rel");
        }
        $tmp=$target.'.a7542.'.bin2hex(random_bytes(4)).'.tmp';
        if (!copy("$payload/$rel",$tmp)) throw new RuntimeException("Cannot stage: $rel");
        if (file_exists($target) || !rename($tmp,$target)) {
            @unlink($tmp); throw new RuntimeException("Destination changed: $rel");
        }
        $written[]=$target;
        echo "[PASS] Added: $rel\n";
    }
} catch(Throwable $e) {
    foreach($written as $path) @unlink($path);
    fwrite(STDERR,"[FAIL] ".$e->getMessage()."; newly created files rolled back.\n");
    exit(1);
}
echo "[PASS] A7.5.4.2 installed. Additive test fixture only; no controllers, migrations or business data changed.\n";
