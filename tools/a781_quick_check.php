<?php
declare(strict_types=1);
$root=dirname(__DIR__);
$required=[
 'app/Support/CrmIntegrationUatManifest.php',
 'tools/a781_integration_preflight.php',
 'tools/a781_manifest_smoke.php',
 'tests/Unit/CrmIntegrationUatManifestTest.php',
 'docs/A781_UAT_PREFLIGHT.md',
 'docs/A781_UAT_MATRIX.md',
];
$checks=[];
foreach($required as $name){
 $file=$root.'/'.$name;
 $checks['Exists '.$name]=is_file($file);
 if(str_ends_with($name,'.php')&&is_file($file)){
    $out=[];$code=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1',$out,$code);
    $checks['PHP syntax '.$name]=$code===0;
 }
}
$source=(string)@file_get_contents($root.'/tools/a781_integration_preflight.php');
$checks['APP_ENV local guard']=str_contains($source,"environment('local')");
$checks['PostgreSQL only']=str_contains($source,"DB::getDefaultConnection() !== 'pgsql'");
$checks['Explicit opt-in DB mode']=str_contains($source,"'--db-readonly'");
$checks['READ ONLY transaction']=str_contains($source,'SET TRANSACTION READ ONLY');
$checks['Statement timeout']=str_contains($source,'statement_timeout');
$checks['Rollback in finally']=str_contains($source,'finally {')&&str_contains($source,'->rollBack()');
$checks['Schema catalog only']=str_contains($source,'pg_catalog.pg_tables');
$checks['No DDL or DML in preflight']=preg_match('/\b(?:CREATE|DROP|TRUNCATE|ALTER|INSERT|UPDATE|DELETE)\s+(?:TABLE|INDEX|INTO|FROM|public\.)\b/i',$source)!==1;
$passed=0;
foreach($checks as $label=>$ok){echo($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;if($ok)$passed++;}
echo 'A7.8.1 quick: '.$passed.'/'.count($checks).' PASS; NO DB.'.PHP_EOL;
exit($passed===count($checks)?0:1);
