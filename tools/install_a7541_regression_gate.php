<?php
/** Safe additive installer for A7.5.4.1. Nothing existing is replaced. */
$root=dirname(__DIR__);
$payload=__DIR__.DIRECTORY_SEPARATOR.'a7541_payload';
$paths=[
    'app/Services/CrmIntegrationRegressionGate.php',
    'app/Console/Commands/RunCrmIntegrationRegression.php',
    'tests/Unit/CrmIntegrationRegressionGateTest.php',
    'tests/Feature/CrmIntegrationRegressionCommandTest.php',
];
$dry=in_array('--dry-run', array_slice($argv,1), true);
foreach (array_slice($argv,1) as $arg) {
    if ($arg !== '--dry-run') {fwrite(STDERR,"Unknown option: $arg\n");exit(2);}
}
$actions=[];
foreach($paths as $path){
    $src=$payload.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$path);
    $dst=$root.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$path);
    if(!is_file($src)){fwrite(STDERR,"[FAIL] Missing payload: $path\n");exit(1);}
    if(is_file($dst)){
        if(hash_file('sha256',$src)!==hash_file('sha256',$dst)){
            fwrite(STDERR,"[FAIL] Refusing to overwrite modified/existing file: $path\n");exit(1);
        }
        $actions[$path]='unchanged';
    } elseif(file_exists($dst)){
        fwrite(STDERR,"[FAIL] Destination is not a normal file: $path\n");exit(1);
    }else $actions[$path]='add';
}
foreach($actions as $path=>$action) echo '[CHECK] '.$action.': '.$path.PHP_EOL;
if($dry){echo "[PASS] Dry run. No files or database changed.\n";exit(0);}
$created=[];
foreach($actions as $path=>$action){
    if($action==='unchanged')continue;
    $src=$payload.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$path);
    $dst=$root.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$path);
    $parent=dirname($dst);
    if(!is_dir($parent)&&!mkdir($parent,0775,true)&&!is_dir($parent)){
        fwrite(STDERR,"[FAIL] Cannot create directory for $path\n");break;
    }
    $tmp=$dst.'.a7541.'.bin2hex(random_bytes(4)).'.tmp';
    if(!copy($src,$tmp)){
        fwrite(STDERR,"[FAIL] Cannot stage $path\n");break;
    }
    // Strict add-only; another process may have created destination meanwhile.
    if(file_exists($dst) || !rename($tmp,$dst)){
        @unlink($tmp); fwrite(STDERR,"[FAIL] Cannot add $path (destination changed?)\n");break;
    }
    $created[]=$dst;echo '[PASS] Added: '.$path.PHP_EOL;
}
if(count($created)!==count(array_filter($actions,static fn($a)=>$a==='add'))){
    foreach($created as $file)@unlink($file);
    fwrite(STDERR,"[FAIL] Install rolled back newly created files.\n");exit(1);
}
echo "[PASS] A7.5.4.1 installed. Additive-only; no migrations, edits, record IDs or DB writes.\n";
echo "Next: php tools/a7541_quick_check.php; php tools/a7541_runtime_preflight.php\n";
