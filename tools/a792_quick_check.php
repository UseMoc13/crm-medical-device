<?php
declare(strict_types=1);
// Offline validation only; no Laravel bootstrap or database access.
$root = dirname(__DIR__);
$base = $root.'/database/uat/';
$path = $root.'/storage/app/private/a791_schema_snapshot_33ebc4b1ef1ecff5.json';
if ($argc === 2 && str_starts_with($argv[1], '--snapshot=')) $path=substr($argv[1],11);
elseif ($argc !== 1) { fwrite(STDERR,"Usage: php tools/a792_quick_check.php [--snapshot=path]\n"); exit(1); }
function need(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
function loadJson(string $file): array { need(is_file($file), 'Missing file: '.basename($file)); $v=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR); need(is_array($v),'Invalid JSON'); return $v; }
function tuple(array $row,array $cols): ?string { $v=[]; foreach($cols as $c){ if(!isset($row[$c]))return null; $v[]=$row[$c]; } return json_encode($v,JSON_THROW_ON_ERROR); }
try {
 $s=loadJson($path); $m=loadJson($base.'a792_dataset_manifest.json'); $r=loadJson($base.'a792_fixture_rows.json');
 $sql=(string)file_get_contents($base.'a792_rehearsal_only.sql');
 need(hash('sha256',$sql)===$m['sql_sha256'],'SQL checksum mismatch');
 need(hash_file('sha256',$base.'a792_fixture_rows.json')===$m['rows_sha256'],'Fixture checksum mismatch');
 need($s['format']==='CRM_A791_SCHEMA_ONLY_V1' && $s['schema']==='public' && $s['fingerprint_sha256']===$m['schema_fingerprint'],'Snapshot fingerprint mismatch');
 $ts=array_column($s['tables'],null,'name'); need(count($ts)===65 && count($m['candidate_counts'])===65 && !array_diff_key($ts,$m['candidate_counts']) && !array_diff_key($m['candidate_counts'],$ts),'Table inventory mismatch');
 echo "[PASS] Snapshot identity, SQL/fixture integrity and 65-table inventory\n";
 need(preg_match('/\bROLLBACK;\s*$/',$sql)===1 && !preg_match('/^\s*(COMMIT|DROP|TRUNCATE|DELETE|UPDATE|ALTER)\b/im',$sql) && str_contains($sql,"current_database() <> 'crm_medical_device_uat'"),'Unsafe rehearsal SQL');
 $keys=[];$rows=0;
 foreach($r as $t=>$records){need(isset($ts[$t]),'Unknown fixture table');$cols=array_column($ts[$t]['columns'],null,'name');
  need(count($records)===$m['candidate_counts'][$t]['count'],'Count mismatch '.$t);
  foreach($records as $row){++$rows;need(!array_diff_key($row,$cols),'Unknown fixture column '.$t);
   foreach($cols as $n=>$c){ if($c['not_null']&&!$c['has_default']&&!$c['identity']&&!$c['generated'])need(isset($row[$n]),"Required column $t.$n"); }
   foreach($row as $n=>$v){$c=$cols[$n]; if($v===null){need(!$c['not_null'],"Null column $t.$n");continue;}
    if($c['data_type']==='uuid')need(is_string($v)&&preg_match('/^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$/D',$v)===1,"Invalid UUID $t.$n");
    if($c['data_type']==='boolean')need(is_bool($v),"Invalid boolean $t.$n");
    if($c['data_type']==='integer')need(is_int($v),"Invalid integer $t.$n");
    if(preg_match('/^character varying\((\d+)\)$/',$c['data_type'],$a))need(is_string($v)&&strlen($v)<=(int)$a[1],"Invalid text length $t.$n");
   }
  }
  foreach($ts[$t]['constraints'] as $c){if(!in_array($c['kind'],['p','u'],true))continue;need(preg_match('/\(([^)]+)\)/',$c['definition'],$a)===1,'Unknown key syntax');$cs=explode(', ',$a[1]);$k=$t.'.'.implode(',',$cs);$keys[$k]=[]; foreach($records as $row){$v=tuple($row,$cs);if($v===null){need($c['kind']!=='p','Missing primary key');continue;}need(!isset($keys[$k][$v]),'Duplicate fixture key '.$k);$keys[$k][$v]=true;}}
 }
 foreach($r as $t=>$records)foreach($ts[$t]['constraints'] as $c){if($c['kind']!=='f')continue;need(preg_match('/^FOREIGN KEY \(([^)]+)\) REFERENCES (\w+)\(([^)]+)\)/',$c['definition'],$a)===1,'Unknown FK syntax');$cs=explode(', ',$a[1]);$k=$a[2].'.'.implode(',',explode(', ',$a[3]));foreach($records as $row){$v=tuple($row,$cs);if($v!==null)need(isset($keys[$k][$v]),'Missing fixture FK '.$c['name']);}}
 need($rows===$m['offline_validated_explicit_rows'],'Explicit row count mismatch');
 $g=0; foreach($m['grants'] as $grants)foreach($grants as $grant){++$g; need(in_array($grant[1],[null,'all','own','branch','team','assigned'],true),'Invalid grant scope');}
 need($rows+$g===$m['total_candidate_rows']&&$g===$m['candidate_counts']['role_permissions']['count'],'Grant count mismatch');
 echo "[PASS] $rows explicit rows: required fields, UUIDs, lengths, PK/unique and populated FKs\n[PASS] $g permission references (catalog existence still requires PostgreSQL)\n";
 $net=[];foreach($r['stock_movements'] as $v){$k=$v['warehouse_id'].':'.$v['product_id'];$net[$k]=($net[$k]??0)+($v['movement_type']==='stock_out'?-$v['quantity']:$v['quantity']);}
 foreach($r['inventories'] as $v)need(($net[$v['warehouse_id'].':'.$v['product_id']]??null)===$v['quantity'],'Inventory mismatch');
 foreach(['invoice','sales_order','quotation'] as $prefix){$tot=[];foreach($r[$prefix.'_items'] as $v){$k=$v[$prefix.'_id'];$tot[$k]=($tot[$k]??0)+$v['subtotal'];} foreach($r[$prefix.'s'] as $v)need(($tot[$v[$prefix.'_id']]??null)===$v['subtotal'] && $v['subtotal']===$v['total_amount'],'Amount mismatch');}
 need(count($r['sales_orders'])===405 && count($r['quotations'])===410,'Sales benchmark size mismatch');
 echo "[PASS] Stock reconciliation, financial totals, 405 orders / 410 quotations\n[PASS] Rehearsal ends in ROLLBACK; dedicated database required\n[RESULT] DATASET_CANDIDATE_OFFLINE_VALIDATED_NOT_IMPORTED\n[LIMIT] No PostgreSQL execution, full constraint/default/index check or business HTTP UAT.\n";
} catch(Throwable $e){fwrite(STDERR,"[FAIL] ".$e->getMessage()."\n");exit(1);}
