<?php
/** A7.8.3 source gate only; no Laravel or DB bootstrap. */
declare(strict_types=1);
$root=dirname(__DIR__);
$files=[
 'app/Support/CrmUatWorkflowCoverage.php',
 'tools/a783_workflow_preflight.php',
 'tools/a783_workflow_smoke.php',
 'tools/a783_quick_check.php',
 'tests/Unit/CrmUatWorkflowCoverageTest.php',
 'docs/A783_WORKFLOW_UAT_PREFLIGHT.md',
 'docs/A783_MANUAL_UAT_EXECUTION.md',
];
$checks=[];
foreach($files as $path){
 $full=$root.'/'.$path;$checks['Exists '.$path]=is_file($full);
 if(is_file($full) && str_ends_with($full,'.php')){
   $out=[];$rc=1;
   exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($full).' 2>&1',$out,$rc);
   $checks['Syntax '.$path]=$rc===0;
 }
}
$s=(string)@file_get_contents($root.'/tools/a783_workflow_preflight.php');
$ps=(string)@file_get_contents($root.'/tools/a783_run_checks.ps1');
$checks['Only explicit routes mode']=str_contains($s,"['--routes']");
$checks['Local app gate']=str_contains($s,"environment('local')");
$checks['Branch gate']=str_contains($s,"'Big-Changes'");
$checks['No SQL facade or DML statements']=!str_contains($s,'DB::') && preg_match('/\b(?:INSERT|UPDATE|DELETE|ALTER|DROP|TRUNCATE)\b/i',$s)!==1;
$checks['No business HTTP invocation']=!str_contains($s,'Http::') && !str_contains($s,'->dispatch(');
$checks['UAT never auto-approved']=str_contains($s,'uat=NOT_EXECUTED');
$checks['Selected isolated test filter']=str_contains($ps,'--filter=') && !str_contains($ps,'--parallel');
$checks['SQLite memory env in runner']=str_contains($ps,"$".'env:DB_DATABASE = \':memory:\'');
$checks['Restore runner environment']=str_contains($ps,"[Environment]::SetEnvironmentVariable");
$checks['No migrations or app code patch']=preg_match('/\b(?:migrate:fresh|migrate:refresh|db:seed|schema:drop|--apply)\b/i',$ps.$s)!==1;
$good=0;foreach($checks as $name=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if($ok)$good++;}
echo 'A7.8.3 quick: '.$good.'/'.count($checks).' PASS; '.(count($checks)-$good).' FAIL. No DB.'.PHP_EOL;
exit($good===count($checks)?0:1);
