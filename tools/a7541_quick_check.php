<?php
/** A7.5.4.1 source safety checks; no Laravel boot / DB access. */
$root=dirname(__DIR__);$n=0;$bad=0;
$check=static function(string $name,bool $condition)use(&$n,&$bad){$n++;if(!$condition)$bad++;echo($condition?'[PASS] ':'[FAIL] ').$name.PHP_EOL;};
$files=[
 'app/Services/CrmIntegrationRegressionGate.php',
 'app/Console/Commands/RunCrmIntegrationRegression.php',
 'tests/Unit/CrmIntegrationRegressionGateTest.php',
 'tests/Feature/CrmIntegrationRegressionCommandTest.php',
];
foreach($files as $path){$check('File '.$path,is_file($root.'/'.$path));}
$s=is_file($root.'/'.$files[0])?file_get_contents($root.'/'.$files[0]):'';
$c=is_file($root.'/'.$files[1])?file_get_contents($root.'/'.$files[1]):'';
$u=is_file($root.'/'.$files[2])?file_get_contents($root.'/'.$files[2]):'';
$f=is_file($root.'/'.$files[3])?file_get_contents($root.'/'.$files[3]):'';
$check('Existing 37-rule audit used',str_contains($s,'$this->audit->run(false)'));
$check('Investigation uses no details',str_contains($s,'$this->investigation->run(false)'));
$check('Remediation uses no details',str_contains($s,'$this->review->run(false)'));
$check('Cross-report evidence parity',str_contains($s,"$".'inconsistencies'));
$check('Notification migration precondition',str_contains($s,"'crm_status_enabled'"));
$check('Routes required',str_contains($s,"'workspace-notifications.open'"));
$check('CRM status audit events required',str_contains($s,'invoice.status_changed'));
$check('No scanner mutation methods',!preg_match('/->(?:insert|update|delete|truncate|drop|create|upsert)\s*\(/i',$s));
$check('No raw customer names or emails',!str_contains($s,"'customer_name'")&&!str_contains($s,"'email'"));
$check('Public status categories distinct',str_contains($s,"'BLOCKED'")&&str_contains($s,"'INCOMPLETE'")&&str_contains($s,"'WATCH'")&&str_contains($s,"'READY'"));
$check('Command provides strict and JSON',str_contains($c,'{--strict')&&str_contains($c,'{--json'));
$check('Unit tests check skip and query errors',str_contains($u,'INCOMPLETE')&&str_contains($u,'BLOCKED'));
$check('Feature test consumes Artisan output once',substr_count($f,'Artisan::output()')===1);
$check('Feature test ensures no writes',str_contains($f,'DB::listen'));
$check('Installer only adds files',!preg_match('/unlink\s*\(\s*\$dst\s*\)/',file_get_contents(__DIR__.'/install_a7541_regression_gate.php')));
foreach(array_merge($files,['tools/install_a7541_regression_gate.php','tools/a7541_quick_check.php','tools/a7541_runtime_preflight.php']) as $file){
 $path=$root.'/'.$file;if(!is_file($path)){$check('PHP syntax '.$file,false);continue;}
 $cmd=escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1';
 exec($cmd,$out,$rc);$check('PHP syntax '.$file,$rc===0);$out=[];
}
echo "\nA7.5.4.1 static: ".($n-$bad)."/$n PASS, $bad FAIL.\n";
exit($bad?1:0);
