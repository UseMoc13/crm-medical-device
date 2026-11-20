<?php
/** A7.8.4 pure smoke without Laravel, DB or file writes. */
declare(strict_types=1);
require_once dirname(__DIR__).'/app/Support/CrmUatWorkflowCoverage.php';
require_once dirname(__DIR__).'/app/Support/CrmUatExecutionLedger.php';
use App\Support\CrmUatExecutionLedger as L;
$pass=0;$fail=0;
$check=static function (string $label, bool $ok) use (&$pass,&$fail):void {
    echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
    $ok ? ++$pass : ++$fail;
};
$reject=static function (array $data):bool {
    try {L::evaluate($data);return false;}catch(InvalidArgumentException){return true;}
};
$base=L::template();$summary=L::evaluate($base);
$check('15 scenarios and two holds in template',count($base['scenarios'])===15 && $summary['policy_holds']===2);
$check('Not-run template never becomes UAT pass',$summary['decision']==='UAT_NOT_EXECUTED' && !$summary['production_release_approved']);
$bad=$base;unset($bad['scenarios']['UAT-15']);$check('Missing scenario rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-99']=$bad['scenarios']['UAT-01'];$check('Extra scenario rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-01']['status']='PASS';$check('PASS without evidence rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-01']['status']='FAIL';$check('FAIL without defect rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-01']['status']='BLOCKED';$check('BLOCKED without reason rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-13']['status']='PASS';$bad['scenarios']['UAT-13']['role']='admin';$bad['scenarios']['UAT-13']['observed_result']='Checked';$bad['scenarios']['UAT-13']['evidence_reference']='case-13';$check('Held policy cannot PASS',$reject($bad));
$bad=$base;$bad['policies']['announcement_public_assets']['status']='APPROVAL_RECORDED';$check('Policy approval requires reference',$reject($bad));
$bad=$base;$bad['review']['status']='MANUAL_REVIEW_RECORDED';$check('Manual review requires reference',$reject($bad));
$bad=$base;$bad['environment']='PRODUCTION';$check('Production environment rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-03']['status']='PASS';$bad['scenarios']['UAT-03']['role']='sales';$bad['scenarios']['UAT-03']['observed_result']='Correct';$bad['scenarios']['UAT-03']['evidence_reference']='case-03';$check('PASS without dataset/backup rejected',$reject($bad));
$bad['environment']='STAGING_DISPOSABLE';$bad['dataset_reference']='fixture-A79';$bad['backup_reference']='snapshot-001';$result=L::evaluate($bad);
$check('One PASS with two policy holds stays HELD',$result['passed']===1 && $result['decision']==='UAT_POLICY_HOLD');
$bad=$base;$bad['scenarios']['UAT-04']['status']='BLOCKED';$bad['scenarios']['UAT-04']['observed_result']='Fixture unavailable';$result=L::evaluate($bad);
$check('BLOCKED case is never approved',$result['decision']==='UAT_BLOCKED');
$bad=$base;$bad['scenarios']['UAT-04']['status']='FAIL';$bad['scenarios']['UAT-04']['role']='sales';$bad['scenarios']['UAT-04']['observed_result']='Duplicate created';$bad['scenarios']['UAT-04']['defect_reference']='DEFECT-4';$bad['environment']='LOCAL_DISPOSABLE';$bad['dataset_reference']='fixture-A79';$bad['backup_reference']='snapshot-001';$result=L::evaluate($bad);
$check('FAILED scenario needs retest',$result['decision']==='UAT_FAILED_NEEDS_RETEST');
$bad=$base;$bad['scenarios']['UAT-01']['status']='HACKED';$check('Unknown status rejected',$reject($bad));
$bad=$base;$bad['policies']['reports_privacy_pilot']['status']='APPROVAL_RECORDED';$bad['policies']['reports_privacy_pilot']['approval_reference']='review-123';$result=L::evaluate($bad);
$check('Single recorded approval still holds remaining policy',$result['policy_holds']===1 && $result['decision']==='UAT_NOT_EXECUTED');
$bad=$base;$bad['scenarios']['UAT-02']['expected_result']='';$check('Missing expected result rejected',$reject($bad));
$bad=$base;$bad['scenarios']['UAT-02']['role']=str_repeat('x',301);$check('Oversized text rejected',$reject($bad));
echo "A7.8.4 smoke: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. No DB.\n";exit($fail===0?0:1);
