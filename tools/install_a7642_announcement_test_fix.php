<?php
/** A7.6.4.2: test-only patch, SHA-256 guarded, no app or DB changes. */
declare(strict_types=1);
$root=dirname(__DIR__);
$dry=in_array('--dry-run',$argv,true);
function a7642_fail(string $message): never {
    fwrite(STDERR, '[FAIL] '.$message.PHP_EOL);
    exit(1);
}
function a7642_normalize(string $content): string {
    return str_replace(["\r\n","\r"],"\n",$content);
}
function a7642_read(string $path): string {
    if (!is_file($path) || !is_readable($path)) a7642_fail('File missing or unreadable: '.$path);
    $data=file_get_contents($path);
    if ($data===false) a7642_fail('Cannot read: '.$path);
    return $data;
}
$files=[
    'tests/Unit/CrmDashboardAnnouncementSourceTest.php'=>[
        'old'=>'b735848ec5cac8f7a0d6f50a4805dfea6edf0b20ca90b4f6911adb874e28744a',
        'new'=>'b5e7f58b9d2ad1501ef345f2330df02a16997dc7c78762368dc2d82a36201977',
    ],
    'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php'=>[
        'old'=>'f2c07cd183dab911b9596612d37d001ca53cce32abafa26c18370d4aa2643eaa',
        'new'=>'dbedd8f8063e7a8b94c7419c00fac39156377e2b415193f587fd7dede49a2525',
    ],
];
$toPatch=[];
foreach($files as $relative=>$check){
    $target=$root.'/'.$relative;
    $payload=__DIR__.'/a7642_payload/'.$relative;
    $old=a7642_read($target);
    $new=a7642_normalize(a7642_read($payload));
    $hash=hash('sha256',a7642_normalize($old));
    if(hash('sha256',$new)!==$check['new']) a7642_fail('Payload checksum mismatch: '.$relative);
    if($hash===$check['new']){
        echo '[PASS] Already fixed: '.$relative.PHP_EOL;
        continue;
    }
    if($hash!==$check['old']){
        a7642_fail('Unexpected local file contents: '.$relative.'; refusing overwrite. Compare with your backup.');
    }
    if($relative==='tests/Unit/CrmDashboardAnnouncementSourceTest.php' && !str_contains($old, 'assertFileDoesNotExist(')) {
        a7642_fail('Missing expected Windows-sensitive assertion.');
    }
    if($relative==='tests/Feature/CrmAnnouncementScheduleIsolatedTest.php' && !str_contains($old, "\$table->uuid('created_by')->nullable();")){
        a7642_fail('Missing expected announcement schema fixture.');
    }
    echo '[CHECK] patch: '.$relative.PHP_EOL;
    $toPatch[$relative]=['before'=>$old,'after'=>$new,'target'=>$target];
}
if(!$toPatch){echo '[PASS] A7.6.4.2 already installed; nothing changed.'.PHP_EOL;exit(0);}
if($dry){echo '[PASS] Dry run. No application files, Git index, or database changed.'.PHP_EOL;exit(0);}
$backupDir=$root.'/tools/a7642_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if(!mkdir($backupDir,0775,true)&&!is_dir($backupDir)) a7642_fail('Cannot create backup directory.');
$staged=[];
foreach($toPatch as $relative=>$data){
    $backup=$backupDir.'/'.basename($relative);
    if(file_put_contents($backup,$data['before'],LOCK_EX)!==strlen($data['before'])) a7642_fail('Cannot back up '.$relative);
    $after=str_contains($data['before'],"\r\n") ? str_replace("\n","\r\n",$data['after']) : $data['after'];
    $temp=$data['target'].'.a7642-'.bin2hex(random_bytes(4)).'.tmp';
    if(file_put_contents($temp,$after,LOCK_EX)!==strlen($after)){
        @unlink($temp);
        foreach($staged as $p) @unlink($p['temp']);
        a7642_fail('Could not stage '.$relative.'; original files unchanged.');
    }
    $lint=[];$code=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1',$lint,$code);
    if($code!==0){
        @unlink($temp);
        foreach($staged as $p) @unlink($p['temp']);
        a7642_fail('Syntax validation failed for '.$relative.'; original files unchanged.');
    }
    $staged[$relative]=['temp'=>$temp,'backup'=>$backup,'target'=>$data['target']];
}
$replaced=[];
foreach($staged as $relative=>$entry){
    if(!rename($entry['temp'],$entry['target'])){
        foreach($replaced as $prior) {
            if(!copy($prior['backup'],$prior['target'])) fwrite(STDERR,'[WARN] Manual restore required from '.$prior['backup'].PHP_EOL);
        }
        foreach($staged as $p) @unlink($p['temp']);
        a7642_fail('Cannot replace '.$relative.'; earlier changes restored from backup when possible.');
    }
    $replaced[]=$entry;
    echo '[PASS] Fixed: '.$relative.PHP_EOL;
}
echo '[PASS] Backup: '.$backupDir.PHP_EOL;
echo 'No controllers, models, services, scheduler, migrations, Git index, or CRM records changed.'.PHP_EOL;
