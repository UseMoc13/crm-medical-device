<?php
declare(strict_types=1);
/** A7.8.2 static: no Laravel bootstrap, no DB, no writes. */
$root=dirname(__DIR__);
$files=[
 'app/Support/CrmIntegrationUatManifest.php',
 'app/Support/CrmUatDataReadiness.php',
 'tools/a782_data_readiness.php',
 'tools/a782_readiness_smoke.php',
 'tools/a782_quick_check.php',
 'tests/Unit/CrmUatDataReadinessTest.php',
];
$checks=[];
foreach($files as $file){
    $path=$root.'/'.$file;
    $checks['Exists '.$file]=is_file($path);
    if(is_file($path)){
        $result=1;$out=[];
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1',$out,$result);
        $checks['Syntax '.$file]=$result===0;
    }
}
$script=(string) @file_get_contents($root.'/tools/a782_data_readiness.php');
$checks['Explicit data-readonly opt-in']=str_contains($script,"['--data-readonly']");
$checks['Local application gate']=str_contains($script,"environment('local')");
$checks['Branch gate']=str_contains($script,"'Big-Changes'");
$checks['PostgreSQL-only']=str_contains($script,"'pgsql'");
$checks['Loopback-only']=str_contains($script,"'127.0.0.1'");
$checks['Transaction read only']=str_contains($script,"SET TRANSACTION READ ONLY");
$checks['Timeouts']=str_contains($script,'statement_timeout') && str_contains($script,'lock_timeout');
$checks['Aggregate EXISTS only']=str_contains($script,'SELECT EXISTS (SELECT 1 FROM public.');
$checks['Rollback in finally']=str_contains($script,'finally {') && str_contains($script,'rollBack()');
$checks['No migration or SQL mutation calls']=preg_match('/(?:->|::)(?:insert|update|delete|upsert|migrate|seed|truncate)\s*\(/i',$script)!==1;
$checks['No dump of values']=!str_contains($script,'var_dump(') && !str_contains($script,'print_r(');
$good=0;foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if($ok)$good++;}
echo 'A7.8.2 static: '.$good.'/'.count($checks).' PASS; '.(count($checks)-$good).' FAIL. NO DB.'.PHP_EOL;
exit($good===count($checks)?0:1);
