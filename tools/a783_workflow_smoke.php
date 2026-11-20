<?php
/** Pure fixture smoke: no Laravel boot/DB/HTTP. */
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmUatWorkflowCoverage.php';
use App\Support\CrmUatWorkflowCoverage as Coverage;
$all=Coverage::scenarios();$checks=[];
$checks['Exactly 15 UAT scenarios']=count($all)===15;
$checks['Consecutive UAT IDs']=array_keys($all)===array_map(static fn(int $i):string => sprintf('UAT-%02d',$i),range(1,15));
$methods=['GET','POST','PATCH','PUT','DELETE'];
$seen=0;$sensitive=0;$evidence=0;$valid=true;
foreach($all as $id=>$item){
    $valid=$valid && isset($item['name'],$item['routes'],$item['evidence'],$item['note'],$item['gate'])
       && is_string($item['name']) && is_string($item['note']) && $item['routes']!==[] && $item['evidence']!==[]
       && in_array($item['gate'],['NORMAL','MANUAL_REQUIRED','HOLD_POLICY'],true);
    foreach($item['routes'] as $route){$seen++;$valid=$valid && count($route)===2 && is_string($route[0]) && $route[0]!=='' && in_array($route[1],$methods,true);}
    foreach($item['evidence'] as $path){$evidence++;$valid=$valid && preg_match('~^tests/(?:Unit|Feature)/[A-Za-z0-9_]+Test\.php$~D',$path)===1;}
    if($item['gate']==='HOLD_POLICY')$sensitive++;
}
$checks['Complete route and evidence metadata']=$valid;
$checks['Workflow route sample total greater than 15']=$seen>15;
$checks['Supporting fixtures enumerated']=$evidence>=15;
$checks['Two unresolved policy holds are explicit']=$sensitive===2;
$checks['Announcement policy explicitly HELD']=$all['UAT-13']['gate']==='HOLD_POLICY';
$checks['Reports privacy approval explicitly HELD']=$all['UAT-14']['gate']==='HOLD_POLICY';
$checks['Delivery is PATCH']=$all['UAT-06']['routes'][0]===['deliveries.change-status','PATCH'];
$checks['Invoice status is PATCH']=$all['UAT-11']['routes'][0]===['invoices.status','PATCH'];
$checks['Warranty activation is POST']=$all['UAT-08']['routes'][0]===['installations.warranties.activate','POST'];
$checks['Baseline never says UAT passed']=Coverage::decision(0,0)==='WORKFLOW_BASELINE_READY_UAT_NOT_EXECUTED';
$checks['Route gaps fail closed']=Coverage::decision(1,0)==='HOLD_PREFLIGHT_GAPS';
$checks['Missing evidence fails closed']=Coverage::decision(0,1)==='HOLD_PREFLIGHT_GAPS';
try {Coverage::decision(-1,0);$checks['Negative stats rejected']=false;}catch(InvalidArgumentException){$checks['Negative stats rejected']=true;}
$good=0;foreach($checks as $name=>$pass){echo ($pass?'[PASS] ':'[FAIL] ').$name.PHP_EOL;if($pass)$good++;}
echo 'A7.8.3 smoke: '.$good.'/'.count($checks).' PASS; '.(count($checks)-$good).' FAIL. No DB.'.PHP_EOL;
exit($good===count($checks)?0:1);
