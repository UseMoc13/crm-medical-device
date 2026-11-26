<?php
/** A7110 scoped test-only installer. PHP 8.2+. No Laravel bootstrap, no DB operations. */
declare(strict_types=1);
$mode = $argv[1] ?? '--check';
$pkg = __DIR__;
$repo = dirname(__DIR__);
$manifest = json_decode((string)file_get_contents($pkg.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
$targets = $manifest['files'];
if (!in_array($mode,['--check','--apply','--rollback'],true)) {fwrite(STDERR,"Use --check, --apply or --rollback BACKUP_ID\n");exit(2);}
if (!is_file($repo.'/artisan') || !is_file($repo.'/composer.json')) {fwrite(STDERR,"[ERROR] Expected Laravel project root, extract into project root\n");exit(2);}
$backupRoot = $repo.'/storage/app/private/a7110_test_backups';
if ($mode==='--rollback') {
    $id=$argv[2]??'';
    if (!preg_match('/^[a-zA-Z0-9_-]{8,80}$/',$id)) {fwrite(STDERR,"[ERROR] BACKUP_ID required\n");exit(2);}
    $dir=$backupRoot.'/'.$id;
    if (!is_file($dir.'/manifest.json')) {fwrite(STDERR,"[ERROR] Backup not found\n");exit(2);}
    $snapshot=json_decode((string)file_get_contents($dir.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
    foreach ($snapshot as $rel=>$item) {
        if (!array_key_exists($rel,$targets)) {fwrite(STDERR,"[ERROR] Unknown backup target\n");exit(2);}
        $path=$repo.'/'.$rel;
        if (!is_file($path)||hash_file('sha256',$path)!==$item['new_sha256']||!is_file($dir.'/'.$rel)||hash_file('sha256',$dir.'/'.$rel)!==$item['old_sha256']) {fwrite(STDERR,"[ERROR] Rollback conflict: $rel\n");exit(2);}
    }
    foreach ($snapshot as $rel=>$item) {if (!copy($dir.'/'.$rel,$repo.'/'.$rel)) {fwrite(STDERR,"[ERROR] Restore failed: $rel\n");exit(1);} echo "[RESTORED] $rel\n";}
    echo "[RESULT] A7110_ROLLBACK_DONE\n";exit(0);
}
$pending=[];$errors=0;
foreach ($targets as $rel=>$hashes) {
    $path=$repo.'/'.$rel;
    if (!is_file($path)) {echo "[ERROR] Missing $rel\n";$errors++;continue;}
    $actual=hash_file('sha256',$path);
    if ($actual===$hashes['new_sha256']) {echo "[ALREADY] $rel\n";continue;}
    if ($actual!==$hashes['old_sha256']) {echo "[ERROR] Source mismatch: $rel\n";$errors++;continue;}
    $payload=$pkg.'/payload/'.$rel;
    if (!is_file($payload)||hash_file('sha256',$payload)!==$hashes['new_sha256']) {echo "[ERROR] Corrupt payload: $rel\n";$errors++;continue;}
    $pending[$rel]=$hashes; echo "[READY] $rel\n";
}
if ($errors) {echo "[RESULT] A7110_REFUSED errors=$errors; no files modified\n";exit(1);}
if ($mode==='--check') {echo '[RESULT] '.(count($pending)?'A7110_CHECK_READY':'A7110_ALREADY_APPLIED').' pending='.count($pending)."; no files modified\n";exit(0);}
if (!$pending) {echo "[RESULT] A7110_ALREADY_APPLIED\n";exit(0);}
// Validate both complete files with the runtime PHP parser, before any write.
foreach ($pending as $rel=>$hashes) {
    $payload=$pkg.'/payload/'.$rel;
    $cmd=escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($payload).' 2>&1';
    exec($cmd,$output,$code); if ($code!==0) {echo "[ERROR] PHP lint failed $rel\n";exit(1);}
}
$id=date('Ymd-His').'-'.bin2hex(random_bytes(5));
$dir=$backupRoot.'/'.$id;
if (!mkdir($dir,0700,true)) {fwrite(STDERR,"[ERROR] Cannot make backup\n");exit(1);}
foreach ($pending as $rel=>$hashes) {
    $d=$dir.'/'.$rel; if (!is_dir(dirname($d))) mkdir(dirname($d),0700,true);
    if (!copy($repo.'/'.$rel,$d)||hash_file('sha256',$d)!==$hashes['old_sha256']) {fwrite(STDERR,"[ERROR] Backup failed $rel\n");exit(1);}
}
file_put_contents($dir.'/manifest.json',json_encode($pending,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
// Recheck every source immediately before first change.
foreach ($pending as $rel=>$hashes) {if (hash_file('sha256',$repo.'/'.$rel)!==$hashes['old_sha256']) {echo "[ERROR] Changed during install $rel\n";exit(1);}}
$applied=[];
foreach ($pending as $rel=>$hashes) {
    if (!copy($pkg.'/payload/'.$rel,$repo.'/'.$rel)||hash_file('sha256',$repo.'/'.$rel)!==$hashes['new_sha256']) {
        foreach ($applied as $done) copy($dir.'/'.$done,$repo.'/'.$done);
        echo "[ERROR] Apply failed; restored previous files where possible. Backup: $id\n";exit(1);
    }
    $applied[]=$rel;echo "[PATCHED] $rel\n";
}
echo "[BACKUP_ID] $id\n[RESULT] A7110_APPLIED files=".count($applied)." (no database operations)\n";
