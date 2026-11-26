<?php
/** A7.11.4 additive-only fail-closed installer. No framework or database boot. */
declare(strict_types=1);
$mode=$argv[1]??'--check';
if ($argc>2 || !in_array($mode,['--check','--apply'],true)) {fwrite(STDERR,"Usage: php a7114_installer.php --check|--apply\n");exit(2);}
$root=getcwd();$payload=__DIR__.'/payload';
if (!is_file($root.'/artisan')||!is_file($root.'/phpunit.xml')) {fwrite(STDERR,"[BLOCK] Run from Laravel root.\n");exit(2);}
$files=['tools/a7114_staging_gate.php','docs/A7114_STAGING_AND_UAT_RUNBOOK.md','docs/A7114_UAT_SEQUENCE.csv'];
$pending=[];$blocks=[];
foreach($files as $f){$src="$payload/$f";$dst="$root/$f";
 if (!is_file($src)){$blocks[]=$f;echo "[BLOCK] Missing payload $f\n";continue;}
 if (is_file($dst)) {if (hash_file('sha256',$src)===hash_file('sha256',$dst))echo "[ALREADY] $f\n";else{$blocks[]=$f;echo "[BLOCK] Different existing file $f\n";}}
 elseif(file_exists($dst)){$blocks[]=$f;echo "[BLOCK] Occupied path $f\n";}
 else{$pending[]=$f;echo "[PENDING] $f\n";}
}
if($blocks){echo '[RESULT] A7114_BLOCKED conflicts='.count($blocks).PHP_EOL;exit(2);}
if($mode==='--check'){echo '[RESULT] '.($pending?'A7114_CHECK_READY':'A7114_ALREADY_APPLIED').' pending='.count($pending).PHP_EOL;exit(0);}
$created=[];
foreach($pending as $f){$src="$payload/$f";$dst="$root/$f";$parent=dirname($dst);
 if(!is_dir($parent)&&!@mkdir($parent,0775,true)&&!is_dir($parent)){fwrite(STDERR,"[FAIL] Create directory\n");break;}
 $h=@fopen($dst,'x');if(!$h){fwrite(STDERR,"[FAIL] File appeared during installation\n");break;}
 $bytes=file_get_contents($src);$ok=$bytes!==false&&fwrite($h,$bytes)===strlen($bytes);fclose($h);
 if(!$ok){@unlink($dst);fwrite(STDERR,"[FAIL] Write failed\n");break;}
 $created[]=$dst;
 if(hash_file('sha256',$dst)!==hash_file('sha256',$src)){fwrite(STDERR,"[FAIL] Hash mismatch\n");break;}
 echo "[CREATED] $f\n";
}
if(count($created)!==count($pending)){foreach($created as $p)@unlink($p);echo "[RESULT] A7114_FAILED_ROLLED_BACK_CREATED_FILES\n";exit(2);}
echo '[RESULT] A7114_INSTALLED new='.count($created).' no existing source/database modified'.PHP_EOL;
