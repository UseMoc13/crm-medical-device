<?php
declare(strict_types=1);
/* A7.7.6.2: one-statement, idempotent patch; no DB or migrations. */
$mode=$argv[1]??'';
if (!in_array($mode,['--dry-run','--apply'],true)||count($argv)!==2) {fwrite(STDERR,"Usage: php tools/a7762_apply_pagination_patch.php --dry-run|--apply\n");exit(2);}
$root=dirname(__DIR__);$file=$root.'/app/Http/Controllers/SalesOrderController.php';
if (!is_file($file)){fwrite(STDERR,"[FAIL] Missing SalesOrderController.\n");exit(2);}
exec('git -C '.escapeshellarg($root).' branch --show-current 2>&1',$lines,$rc);
if ($rc!==0||trim(implode("\n",$lines))!=='Big-Changes'){fwrite(STDERR,"[FAIL] Must be on branch Big-Changes.\n");exit(2);}
$old=file_get_contents($file);
if ($old===false){fwrite(STDERR,"[FAIL] Unable to read controller.\n");exit(2);}
$already='~\$orders\s*=\s*\$query[ \t]*\R[ \t]*->orderByDesc\(\x27sales_order_id\x27\)[ \t]*\R[ \t]*->paginate\(10\)~';
$anchor='~(\$orders\s*=\s*\$query[ \t]*\R[ \t]*)(->paginate\(10\))~';
if (preg_match($already,$old)===1){echo "[PASS] Already applied; no change.\n";exit(0);}
if (preg_match_all($anchor,$old)!==1||!str_contains($old,"foreach (\$query->orderByDesc('sales_order_id')->lazy(200) as \$order)")){
 fwrite(STDERR,"[FAIL] Expected source anchors absent; no changes made.\n");exit(2);
}
$eol=str_contains($old,"\r\n")?"\r\n":"\n";
$new=preg_replace_callback($anchor,static fn($m)=>$m[1]."->orderByDesc('sales_order_id')".$eol.'            '.$m[2],$old,1,$count);
if ($count!==1||!is_string($new)){fwrite(STDERR,"[FAIL] Failed to prepare patch.\n");exit(2);}
$temp=tempnam(sys_get_temp_dir(),'crm-a7762-');
if ($temp===false){fwrite(STDERR,"[FAIL] Temporary file unavailable.\n");exit(2);}
$error=null;
try {
 if (file_put_contents($temp,$new)===false)throw new RuntimeException('Could not stage file');
 exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($temp).' 2>&1',$lint,$lintCode);
 if($lintCode!==0)throw new RuntimeException('PHP lint failed: '.implode(' ',$lint));
 echo "[PASS] Exact pagination anchor + PHP syntax; CSV, PDF, permissions unchanged.\n";
 if ($mode==='--dry-run') echo "[PASS] Dry-run. No files or database changed.\n";
 else {
  $dir=sys_get_temp_dir().DIRECTORY_SEPARATOR.'crm_a7762_'.date('Ymd_His').'_'.bin2hex(random_bytes(4));
  if (!mkdir($dir,0700,true)||!copy($file,$dir.'/SalesOrderController.php'))throw new RuntimeException('Backup failed');
  if (file_put_contents($file,$new,LOCK_EX)!==strlen($new)){
   copy($dir.'/SalesOrderController.php',$file);throw new RuntimeException('Write failed; backup restored');
  }
  echo "[PASS] Pagination tie-break installed.\n[BACKUP] $dir\n[SAFE] No DB access, no controller temp files.\n";
 }
} catch (Throwable $e){$error=$e->getMessage();}
finally {@unlink($temp);}
if($error!==null){fwrite(STDERR,"[FAIL] $error\n");exit(2);}
