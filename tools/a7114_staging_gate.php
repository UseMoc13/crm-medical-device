<?php
/** A7.11.4: no Laravel bootstrap / no DB connection / no SQL / no network. */
declare(strict_types=1);
$root=dirname(__DIR__);
$mode=$argv[1]??'--check';
if (!in_array($mode,['--check','--init','--validate'],true) || $argc!==2) {
    fwrite(STDERR,"Usage: php tools/a7114_staging_gate.php --check|--init|--validate\n"); exit(2);
}
if (!is_file($root.'/artisan') || !is_file($root.'/phpunit.xml')) {
    fwrite(STDERR,"[STOP] Laravel project root not found.\n");exit(2);
}
$envPath=$root.'/.env';
if (!is_file($envPath) || !is_readable($envPath)) {
    fwrite(STDERR,"[HOLD] Local .env unavailable; no DB accessed.\n");exit(1);
}
$raw=file_get_contents($envPath);
if ($raw===false) {fwrite(STDERR,"[HOLD] Cannot read local configuration.\n");exit(1);}
$cfg=[];
foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
    if (!preg_match('/^\s*([A-Z][A-Z0-9_]*)\s*=\s*(.*?)\s*$/', $line, $m)) continue;
    $value=trim($m[2]);
    if (strlen($value)>=2 && (($value[0]==='"' && substr($value,-1)==='"') || ($value[0]==="'" && substr($value,-1)==="'"))) {
        $value=substr($value,1,-1);
    } else {
        $value=preg_replace('/\s+#.*$/','',$value);
    }
    $cfg[$m[1]]=$value;
}
$checks=[
 'APP_ENV local'=>(($cfg['APP_ENV']??'')==='local'),
 'PostgreSQL configured'=>(($cfg['DB_CONNECTION']??'')==='pgsql'),
 'Explicit loopback DB_HOST'=>in_array(strtolower($cfg['DB_HOST']??''), ['localhost','127.0.0.1','::1'],true),
 'DB_URL absent/empty'=>trim($cfg['DB_URL']??'')==='',
 'Separate UAT-named database'=>(bool)preg_match('/(?:^|[_-])(uat|test|testing|sandbox|disposable)(?:$|[_-])/i', $cfg['DB_DATABASE']??''),
 'Not a default database'=>!in_array(strtolower($cfg['DB_DATABASE']??''),['','postgres','template0','template1'],true),
];
foreach ($checks as $name=>$ok) echo '['.($ok?'PASS':'HOLD').'] '.$name."\n";
$envReady=!in_array(false,$checks,true);
$folder=$root.'/storage/app/private/a7114_uat';
$file=$folder.'/staging_attestation.json';
if ($mode==='--init') {
    if (file_exists($file)) {echo "[ALREADY] Private attestation exists. Not overwritten.\n";exit(0);}
    if (!is_dir($folder) && !@mkdir($folder,0700,true) && !is_dir($folder)) {fwrite(STDERR,"[STOP] Cannot create private directory.\n");exit(2);}
    $template=[
       'version'=>'A7114-1',
       'database_is_disposable'=>false,
       'target_cluster_and_port_manually_verified'=>false,
       'no_port_forward_to_production'=>false,
       'synthetic_data_only'=>false,
       'backup_and_restore_verified'=>false,
       'business_owner_authorized_testing'=>false,
       'confirmation'=>'',
    ];
    $h=@fopen($file,'x');if (!$h) {fwrite(STDERR,"[STOP] Cannot exclusively create attestation.\n");exit(2);}
    $json=json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
    $ok=fwrite($h,$json)===strlen($json);fclose($h);
    if (!$ok) {@unlink($file);fwrite(STDERR,"[STOP] Write failed.\n");exit(2);}
    echo "[CREATED] storage/app/private/a7114_uat/staging_attestation.json (all approvals false)\n";
    echo "[NOTE] Inspect target and backup independently. Never fill true without evidence.\n";
    exit(0);
}
if ($mode==='--check') {
    echo '[RESULT] '.($envReady?'A7114_CONFIG_CANDIDATE_ATTESTATION_REQUIRED':'A7114_HOLD_CONFIG').' no_database_access'.PHP_EOL;
    exit($envReady?0:1);
}
if (!is_file($file) || !is_readable($file)) {
    echo "[HOLD] Missing private attestation. Run --init and verify manually.\n";
    echo "[RESULT] A7114_UAT_NOT_AUTHORIZED no_database_access\n";exit(1);
}
try {
 $json=file_get_contents($file);if($json===false || strlen($json)>16384)throw new RuntimeException('Invalid size');
 $data=json_decode($json,true,32,JSON_THROW_ON_ERROR);
 if (!is_array($data)||($data['version']??null)!=='A7114-1') throw new RuntimeException('Invalid version');
 $fields=['database_is_disposable','target_cluster_and_port_manually_verified','no_port_forward_to_production','synthetic_data_only','backup_and_restore_verified','business_owner_authorized_testing'];
 $attested=true;
 foreach ($fields as $field) { $ok=($data[$field]??null)===true; $attested=$attested&&$ok;echo '['.($ok?'ATTESTED':'HOLD').'] '.$field."\n"; }
 $phrase='I CONFIRM THIS IS A DISPOSABLE UAT DATABASE';
 $phraseOk=($data['confirmation']??null)===$phrase;
 echo '['.($phraseOk?'ATTESTED':'HOLD')."] Exact confirmation phrase\n";
 $ready=$envReady&&$attested&&$phraseOk;
 echo '[RESULT] '.($ready?'A7114_OPERATOR_ATTESTED_READY_FOR_MANUAL_UAT':'A7114_UAT_NOT_AUTHORIZED').' no_database_access'.PHP_EOL;
 echo "[LIMIT] This checks declarations only, NOT the actual PostgreSQL server, backup, or stakeholder authority.\n";
 exit($ready?0:1);
} catch(Throwable $e) {fwrite(STDERR,"[HOLD] Invalid private attestation.\n");exit(1);}
