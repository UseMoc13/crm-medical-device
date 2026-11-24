<?php
declare(strict_types=1);
// Offline only; no Laravel bootstrap or database access.
$root=dirname(__DIR__);
try {
 $m=json_decode(file_get_contents($root.'/database/uat/a794_import_manifest.json'),true,512,JSON_THROW_ON_ERROR);
 foreach($m['sha256'] as $path=>$expected){if(hash_file('sha256',$root.'/'.$path)!==$expected)throw new RuntimeException('Checksum mismatch: '.$path); echo "[PASS] SHA-256 $path\n";}
 $a=file_get_contents($root.'/database/uat/a794_rehearsal.sql'); $b=file_get_contents($root.'/database/uat/a794_commit.sql');
 $normalize=fn($s)=>preg_replace('/\n(?:COMMIT|ROLLBACK);\nSELECT .* AS result;\n\z/','',substr($s,strpos($s,"\n")+1));
 if($normalize($a)!==$normalize($b)||!str_ends_with($a,"ROLLBACK;\nSELECT 'A794_REHEARSAL_PASSED_ROLLED_BACK' AS result;\n")||!str_ends_with($b,"COMMIT;\nSELECT 'A794_DATASET_COMMITTED' AS result;\n"))throw new RuntimeException('Transaction payload mismatch');
 echo "[PASS] Identical rehearsal and commit payloads; explicit transaction endings\n";
 $counts=$m['expected_counts_before_bootstrap']; if(count($counts)!==65||array_sum($counts)-$counts['migrations']-$counts['permissions']!==2675)throw new RuntimeException('Invalid manifest counts');
 if(!str_contains($a,"current_database() <> 'crm_medical_device'")||!str_contains($a,'IN SHARE ROW EXCLUSIVE MODE')||!str_contains($a,'A794 requires empty prototype table')||preg_match('/^\s*(DROP|TRUNCATE|DELETE|UPDATE|ALTER)\b/im',$a))throw new RuntimeException('Safety markers invalid');
 echo "[PASS] Target guard, lock, empty-table guard, 65-table manifest\n[RESULT] A794_PACKAGE_OFFLINE_READY\n[LIMIT] Actual PostgreSQL execution required.\n";
} catch(Throwable $e){fwrite(STDERR,"[FAIL] ".$e->getMessage()."\n");exit(1);}
