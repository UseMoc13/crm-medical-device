<?php

declare(strict_types=1);
/** Static checks only. No Laravel bootstrap and no DB connections. */
$root = dirname(__DIR__);
$files = [
 'app/Support/CrmUatSchemaSnapshot.php',
 'tools/a791_schema_preflight.php',
 'tools/a791_snapshot_smoke.php',
 'tools/a791_quick_check.php',
 'tests/Unit/CrmUatSchemaSnapshotTest.php',
 'docs/A791_SCHEMA_RESET_SAFETY.md',
 'README_START_HERE.md',
];
$pass=0;$fail=0;
$check = static function(string $label,bool $good) use (&$pass,&$fail):void{
 echo ($good?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
 $good?++$pass:++$fail;
};
foreach($files as $file){
 $p=$root.'/'.$file;
 $check('Exists '.$file,is_file($p));
 if(is_file($p) && str_ends_with($file,'.php')){
  $output=[];$code=1;
  exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($p).' 2>&1',$output,$code);
  $check('PHP syntax '.$file,$code===0);
 }
}
$script=(string)@file_get_contents($root.'/tools/a791_schema_preflight.php');
$check('Explicit schema-readonly mode',str_contains($script,"'--schema-readonly'"));
$check('Explicit snapshot option',str_contains($script,"'--snapshot'"));
$check('Local environment enforced',str_contains($script,"environment('local')"));
$check('Big-Changes branch enforced',str_contains($script,"'Big-Changes'"));
$check('PostgreSQL-only enforced',str_contains($script,"'pgsql'"));
$check('Loopback host enforced',str_contains($script,"'127.0.0.1'"));
$check('Read-only transaction',str_contains($script,'SET TRANSACTION READ ONLY'));
$check('Transaction has statement timeout',str_contains($script,'statement_timeout'));
$check('Rollback in finally',str_contains($script,'finally') && str_contains($script,'rollBack()'));
$check('Catalog-only SQL',substr_count($script,'FROM pg_catalog.') >= 5 && !str_contains($script,'FROM public.'));
$check('No destructive commands',preg_match('/(?:migrate:fresh|migrate:reset|\bTRUNCATE\b|\bDROP TABLE\b|\bDELETE FROM\b)/i',$script)!==1);
$check('No CRUD SQL facade calls',preg_match('/\$db->(?:insert|update|delete|affectingStatement|unprepared)\s*\(/i',$script)!==1);
$check('Snapshot uses exclusive create',str_contains($script,"fopen($".'output, \'x\')'));
$check('Never captures schema values from business rows',!str_contains($script,'SELECT * FROM'));
echo "A7.9.1 static: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. No DB.\n";
exit($fail===0?0:1);
