<?php
/** A7.8.3 named route and evidence inventory only. No business DB/HTTP requests. */
declare(strict_types=1);
$root=dirname(__DIR__);
if(array_slice($argv,1)!==['--routes']){
    fwrite(STDERR,"Usage: php tools/a783_workflow_preflight.php --routes\n");exit(2);
}
$branch=trim((string)@shell_exec('git -C '.escapeshellarg($root).' branch --show-current 2>NUL'));
if($branch!=='Big-Changes'){
    fwrite(STDERR,"[STOP] Requires branch Big-Changes. No DB access attempted.\n");exit(2);
}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('local')){
    fwrite(STDERR,"[STOP] Requires APP_ENV=local. No DB access attempted.\n");exit(2);
}
$routes=[];
foreach(\Illuminate\Support\Facades\Route::getRoutes() as $route){
    $name=$route->getName();if(!is_string($name)||$name==='')continue;
    foreach($route->methods() as $method){$routes[$name][$method]=true;}
}
$cases=\App\Support\CrmUatWorkflowCoverage::scenarios();
$unmatched=0;$missingTests=0;$routeCount=0;$totalTests=0;$holds=0;
echo "A7.8.3 Workflow UAT Preflight [ROUTES + TEST FILES ONLY; NO DB]\n";
foreach($cases as $id=>$case){
    $missR=0;$missE=0;
    foreach($case['routes'] as [$name,$method]){
        ++$routeCount;
        if(!isset($routes[$name][$method])){
            $missR++; echo '[MISSING ROUTE] '.$id.' '.$name.' ['.$method."]\n";
        }
    }
    foreach($case['evidence'] as $relative){
        $totalTests++;
        if(!is_file($root.'/'.$relative)){$missE++;echo '[MISSING TEST] '.$id.' '.$relative."\n";}
    }
    $unmatched+=$missR;$missingTests+=$missE;
    if($case['gate']==='HOLD_POLICY')$holds++;
    echo '[CASE] '.$id.' route_checks='.count($case['routes']).' route_missing='.$missR
        .' evidence_files='.count($case['evidence']).' evidence_missing='.$missE
        .' policy='.($case['gate']==='HOLD_POLICY'?'HOLD':'PENDING')
        .' uat=NOT_EXECUTED'."\n";
}
$decision=\App\Support\CrmUatWorkflowCoverage::decision($unmatched,$missingTests);
echo '[RESULT] cases='.count($cases).' routes_checked='.$routeCount.' route_gaps='.$unmatched
    .' evidence_checked='.$totalTests.' evidence_missing='.$missingTests
    .' policy_holds='.$holds.' decision='.$decision."\n";
echo "[LIMIT] Tests are supporting evidence, NOT actual business HTTP UAT; no role-based signoff.\n";
echo "[SAFE] No CRM DB queries, migrations, seeds, HTTP requests or file changes.\n";
exit($unmatched===0 && $missingTests===0?0:1);
