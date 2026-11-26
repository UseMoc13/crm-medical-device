<?php
/** Standalone source & fixture checks, no Laravel bootstrap or database access. */
declare(strict_types=1);
$root=dirname(__DIR__);
$files=[
 'tools/a7108_full_audit.php','tools/a7108_navigation_fix.php',
 'tools/a7108_patch_self_test.php','tools/a7108_js_regression.js',
 'tools/a7108_source_check.php','docs/A7108_START_HERE.md','docs/A7108_UAT_MATRIX.md',
];
foreach($files as $file){if(!is_file($root.'/'.$file)){fwrite(STDERR,"[FAIL] Missing $file\n");exit(1);}echo "[PASS] $file\n";}
foreach(['tools/a7108_full_audit.php','tools/a7108_navigation_fix.php','tools/a7108_patch_self_test.php','tools/a7108_source_check.php'] as $file){
    $cmd=escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$file).' 2>&1';
    $lines=[];$code=0;exec($cmd,$lines,$code);if($code!==0){fwrite(STDERR,'[FAIL] PHP lint '.implode(' ',$lines).PHP_EOL);exit(1);}
}
echo "[PASS] PHP lint 4 files\n";
foreach(['a7108_full_audit.php --self-test','a7108_patch_self_test.php'] as $entry){
    $parts=explode(' ',$entry,2);
    $command=escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.'/tools/'.$parts[0]).(isset($parts[1])?' '.$parts[1]:'').' 2>&1';
    $lines=[];$code=0;exec($command,$lines,$code);
    foreach($lines as $line)echo $line.PHP_EOL;
    if($code!==0){fwrite(STDERR,"[FAIL] $entry\n");exit(1);}
}
$lines=[];$code=0;exec('node --version 2>&1',$lines,$code);
if($code===0){
    $check=[];$exit=0;exec('node --check '.escapeshellarg($root.'/tools/a7108_js_regression.js').' 2>&1',$check,$exit);
    if($exit!==0){fwrite(STDERR,'[FAIL] Node JS syntax '.implode(' ',$check).PHP_EOL);exit(1);}
    echo "[PASS] Node JS test syntax\n";
}else echo "[NOTICE] Node not found; JS regression will require Node.js after patch is applied\n";
echo '[RESULT] A7108_SOURCE_READY; no application source or database writes'.PHP_EOL;
