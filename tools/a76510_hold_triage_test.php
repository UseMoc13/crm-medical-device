<?php
/** A7.6.5.10 pure standalone tests. No Laravel bootstrap or database. */
declare(strict_types=1);
require __DIR__.'/a76510_hold_triage.php';

function fixture(): array {
    $targets = [];
    $cases = [];
    foreach (A76510HoldTriage::TARGETS as $i => $id) {
        $targets[] = ['rule'=>$id,'status'=>'flagged','count'=>1];
        $cases[] = ['id'=>$id,'status'=>'flagged','count'=>1];
    }
    return [
        'entitlements'=>['exit'=>0,'payload'=>[
            'stage'=>'A7.6.5.3','status'=>'REVIEW_REQUIRED','summary'=>[
                'reports_without_invoice_kpi'=>2,'csv_without_invoice_kpi'=>2,
            ], 'single_role_preview'=>[['role'=>'Finance','private_email'=>'redact@example.org']],
        ]],
        'parity'=>['exit'=>0,'payload'=>[
            'stage'=>'A7.6.5.2','status'=>'REVIEW_REQUIRED','summary'=>['policy_reviews'=>2,'failed'=>0],
        ]],
        'integration'=>['exit'=>0,'payload'=>[
            'stage'=>'A7.5.4.1','status'=>'WATCH', 'summary'=>[
                'audit_flagged'=>4,'critical_findings'=>2,'query_errors_across_reports'=>0,
                'inconsistent_rules'=>0,'shared_delivery_records'=>1,
            ], 'targets'=>$targets,
        ]],
        'remediation'=>['exit'=>0,'payload'=>[
            'stage'=>'A7.5.2.2c','summary'=>['checked'=>4,'errors'=>0,'skipped'=>0],
            'cases'=>$cases,
        ]],
    ];
}
$ok=0; $bad=0;
function runcheck(string $name,bool $pass): void {
    global $ok,$bad;
    echo ($pass?'[PASS] ':'[FAIL] ').$name."\n";
    if($pass)$ok++;else$bad++;
}
$r=fixture();$out=A76510HoldTriage::assess($r);
runcheck('four valid sources classified as HOLD', $out['status']==='HOLD_REVIEW_REQUIRED' && $out['commands_checked']===4);
runcheck('known mismatch counts retained', $out['entitlements']['reports_without_invoice_kpi']===2 && $out['parity']['policy_reviews']===2);
runcheck('four findings captured without record identifiers', count($out['findings'])===4 && $out['findings'][0]['status']==='FLAGGED');
runcheck('shared delivery count retained only as aggregate', $out['integration']['shared_delivery_records']===1);
runcheck('source PII never echoed', !str_contains(json_encode($out),'redact@example.org'));
runcheck('never authorizes production or automatic changes', $out['no_automatic_changes']===true && $out['production_activation_authorized']===false);

$q=fixture();unset($q['parity']);
runcheck('missing command -> evidence incomplete', A76510HoldTriage::assess($q)['status']==='EVIDENCE_INCOMPLETE');
$q=fixture();$q['parity']['exit']=1;
runcheck('nonzero exit -> evidence incomplete', A76510HoldTriage::assess($q)['status']==='EVIDENCE_INCOMPLETE');
$q=fixture();$q['parity']['payload']['stage']='WRONG';
runcheck('unexpected stage -> evidence incomplete', A76510HoldTriage::assess($q)['status']==='EVIDENCE_INCOMPLETE');
$q=fixture();$q['remediation']['payload']['cases'][0]['count']=3;
runcheck('scanner count disagreement prevents proceeding', A76510HoldTriage::assess($q)['status']==='SCAN_DISAGREEMENT');
$q=fixture();unset($q['remediation']['payload']['cases'][0]);
runcheck('missing target never hidden', A76510HoldTriage::assess($q)['status']==='SCAN_DISAGREEMENT');
$q=fixture();$q['integration']['payload']['summary']['query_errors_across_reports']=1;
runcheck('query errors take precedence over HOLD', A76510HoldTriage::assess($q)['status']==='SCAN_DISAGREEMENT');
$q=fixture();$q['parity']['payload']['summary']['policy_reviews']=-1;
runcheck('negative evidence count -> incomplete', A76510HoldTriage::assess($q)['status']==='EVIDENCE_INCOMPLETE');
$q=fixture();$q['entitlements']['payload']['status']='ELIGIBLE_FOR_NUMERIC_COMPARISON';
$q['entitlements']['payload']['summary']['reports_without_invoice_kpi']=0;
$q['entitlements']['payload']['summary']['csv_without_invoice_kpi']=0;
$q['parity']['payload']['status']='READY';
$q['parity']['payload']['summary']['policy_reviews']=0;
$q['integration']['payload']['status']='READY';
$q['integration']['payload']['summary']['audit_flagged']=0;
$q['integration']['payload']['summary']['critical_findings']=0;
foreach($q['integration']['payload']['targets'] as &$t){$t['count']=0;$t['status']='ok';} unset($t);
foreach($q['remediation']['payload']['cases'] as &$c){$c['count']=0;$c['status']='ok';} unset($c);
runcheck('clean artificial fixture -> manual review only', A76510HoldTriage::assess($q)['status']==='ELIGIBLE_FOR_SEPARATE_MANUAL_REVIEW');
runcheck('safe status normalized', A76510HoldTriage::assess($q)['findings'][0]['status']==='OK');

echo "A7.6.5.10: $ok/".($ok+$bad)." PASS; $bad FAIL. No DB, no writes.\n";
exit($bad?1:0);
