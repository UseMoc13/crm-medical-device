<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmIntegrationUatManifest.php';
use App\Support\CrmIntegrationUatManifest as Manifest;
$checks=[];
$all=[];
foreach(Manifest::routeGroups() as $group=>$rows){
 $checks['Nonempty routes: '.$group]=count($rows)>0;
 foreach($rows as [$name,$method]){
  $checks['Method valid: '.$name]=in_array($method,['GET','POST','PUT','PATCH','DELETE'],true);
  $all[]=$name;
 }
}
$checks['Unique route names']=count($all)===count(array_unique($all));
$checks['Delivery workflow uses PATCH']=in_array(['deliveries.change-status','PATCH'],Manifest::routeGroups()['Delivery & Installation'],true);
$checks['Invoice status uses PATCH']=in_array(['invoices.status','PATCH'],Manifest::routeGroups()['Billing & Reporting'],true);
$checks['Sales conversion uses POST']=in_array(['sales_orders.from_quotation','POST'],Manifest::routeGroups()['Sales CRM'],true);
$tables=[];
foreach(Manifest::schemaGroups() as $rows){foreach($rows as $table)$tables[$table]=true;}
$checks['At least 25 schema tables']=count($tables)>=25;
foreach(Manifest::relationshipHints() as [$from,$col,$to]){
 $checks['Relation tables declared: '.$from.'.'.$col]=isset($tables[$from],$tables[$to]);
}
$pass=0;
foreach($checks as $label=>$ok){echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL; if($ok)$pass++;}
echo 'A7.8.1 manifest smoke: '.$pass.'/'.count($checks).' PASS; no DB.'.PHP_EOL;
exit($pass===count($checks)?0:1);
