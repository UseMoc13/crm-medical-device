<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$paths=[
  'tools/a7105_rebuild_role_ui.php'=>['A7105_ORIGINAL_BACKUP_ID','a7105_transform','a7105_php_lint','--restore-original','A7105_SAFE_REBUILD_READY','A7105_ROLE_UI_REBUILT_AND_VALIDATED'],
  'tools/a7105_transform_smoke.php'=>['a7105_transform','a7105_php_lint','A7105_SMOKE_PASS'],
  'docs/A7105_RECOVERY.md'=>['20261126-050227-d28dcbfc','--check','--apply','--restore-original'],
];
$n=0;
foreach($paths as $path=>$terms){$s=@file_get_contents($root.'/'.$path);if($s===false){fwrite(STDERR,"[FAIL] missing $path\n");exit(1);}foreach($terms as $v){if(!str_contains($s,$v)){fwrite(STDERR,"[FAIL] $path missing $v\n");exit(1);}$n++;}
if(str_ends_with($path,'.php')){$o=[];$ret=0;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1',$o,$ret);if($ret!==0){fwrite(STDERR,"[FAIL] PHP lint $path: ".implode(' ',$o)."\n");exit(1);}$n++;}
echo "[PASS] $path\n";}
echo "[RESULT] A7105_SOURCE_READY checks=$n (Blade compiler not executed)\n";
