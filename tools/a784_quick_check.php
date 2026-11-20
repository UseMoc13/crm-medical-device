<?php
/** A7.8.4 static preflight; NO DB and NO Laravel boot. */
declare(strict_types=1);
$root=dirname(__DIR__);
$files=[
'app/Support/CrmUatWorkflowCoverage.php',
'app/Support/CrmUatExecutionLedger.php',
'tools/a784_validate_uat_ledger.php',
'tools/a784_ledger_smoke.php',
'tools/a784_quick_check.php',
'tests/Unit/CrmUatExecutionLedgerTest.php',
'docs/uat/a784_uat_execution_template.json',
'docs/A784_MANUAL_UAT_REGISTER.md',
];
$ok=0;$bad=0;
$check=static function(string $msg,bool $good)use(&$ok,&$bad):void{
 echo ($good?'[PASS] ':'[FAIL] ').$msg.PHP_EOL;$good?++$ok:++$bad;
};
foreach($files as $file){$p=$root.'/'.$file;$check('Exists '.$file,is_file($p));
 if(is_file($p) && str_ends_with($p,'.php')){$out=[];$ret=1;exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($p).' 2>&1',$out,$ret);$check('Syntax '.$file,$ret===0);}
}
$src=(string)@file_get_contents($root.'/app/Support/CrmUatExecutionLedger.php');
$cli=(string)@file_get_contents($root.'/tools/a784_validate_uat_ledger.php');
$check('All manual UAT outcomes explicit',str_contains($src,"'NOT_RUN', 'PASS', 'FAIL', 'BLOCKED'"));
$check('Policy holds preserved',str_contains($src,"'announcement_public_assets', 'reports_privacy_pilot'"));
$check('No automatic production approval',str_contains($src,"'production_release_approved' => false"));
$check('Policy approval requires a reference',str_contains($src,'Policy approval reference required.'));
$check('No application DB or HTTP dependencies',preg_match('/\b(?:DB|Schema|Http|Artisan)::|->(?:insert|update|delete|truncate)\s*\(/i',$src.$cli)!==1);
$data=json_decode((string)@file_get_contents($root.'/docs/uat/a784_uat_execution_template.json'),true);
$check('Template is valid JSON with 15 cases',is_array($data) && count($data['scenarios']??[])===15);
$check('Template starts with 2 policy holds',is_array($data) && ($data['policies']['announcement_public_assets']['status']??null)==='HOLD' && ($data['policies']['reports_privacy_pilot']['status']??null)==='HOLD');
echo "A7.8.4 static: {$ok}/".($ok+$bad)." PASS; {$bad} FAIL. NO DB.\n";exit($bad===0?0:1);
