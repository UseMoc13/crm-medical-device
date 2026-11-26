<?php
/** A7.11.2 additive-only installer: no database, no Laravel bootstrap. */
declare(strict_types=1);
$arg=$argv[1]??'--check';
if (!in_array($arg,['--check','--apply'],true)) {fwrite(STDERR,"Usage: php a7112_install.php --check|--apply\n");exit(2);}
$root=getcwd(); $payload=__DIR__.'/payload';
if (!is_file($root.'/artisan') || !is_file($root.'/phpunit.xml')) {fwrite(STDERR,"[BLOCK] Run in Laravel project root.\n");exit(2);}
$files=[
 'tools/a7112_safe_preflight.php', 'tools/a7112_run_regression.ps1',
 'tools/a7112_init_ledger.php', 'docs/A7112_MANUAL_UAT_PLAYBOOK.md',
 'docs/A7112_DEFECT_REGISTER_TEMPLATE.csv'
];
$pending=[]; $blocked=[];
foreach ($files as $rel) {
 $src=$payload.'/'.$rel;$dst=$root.'/'.$rel;
 if (!is_file($src)) {$blocked[]=$rel.' missing payload';continue;}
 if (is_file($dst)) {
  if (hash_file('sha256',$dst)===hash_file('sha256',$src)) echo "[ALREADY] $rel\n";
  else {$blocked[]=$rel.' existing file differs';echo "[BLOCK] $rel existing file differs\n";}
 } elseif (file_exists($dst)) {$blocked[]=$rel.' path occupied';echo "[BLOCK] $rel path occupied\n";}
 else {$pending[]=$rel;echo "[PENDING] $rel\n";}
}
if ($blocked) {echo '[RESULT] A7112_BLOCKED conflicts='.count($blocked).PHP_EOL;exit(2);}
if ($arg==='--check') {echo '[RESULT] '.($pending?'A7112_CHECK_READY':'A7112_ALREADY_APPLIED').' pending='.count($pending).PHP_EOL;exit(0);}
foreach ($pending as $rel) {
 $dst=$root.'/'.$rel;$dir=dirname($dst);
 if (!is_dir($dir) && !mkdir($dir,0775,true) && !is_dir($dir)) {fwrite(STDERR,"[FAIL] Cannot create $dir\n");exit(2);}
 // Exclusive creation prevents overwriting a file created between check and apply.
 $handle=@fopen($dst,'x');
 if (!$handle) {fwrite(STDERR,"[BLOCK] Destination appeared $rel\n");exit(2);}
 $bytes=file_get_contents($payload.'/'.$rel);
 if ($bytes===false || fwrite($handle,$bytes)!==strlen($bytes)) {fclose($handle);@unlink($dst);fwrite(STDERR,"[FAIL] Write $rel\n");exit(2);}
 fclose($handle);
 if (hash_file('sha256',$dst)!==hash_file('sha256',$payload.'/'.$rel)) {fwrite(STDERR,"[FAIL] Hash mismatch $rel\n");exit(2);}
 echo "[CREATED] $rel\n";
}
echo '[RESULT] A7112_INSTALLED new='.count($pending).' no existing source/database modified'.PHP_EOL;
