<?php
/** Local pure test runner. No Laravel bootstrap or database. */
declare(strict_types=1);
require __DIR__ . '/a758_case_rules.php';
$pass = 0; $fail = 0;
$check = static function (string $label, bool $ok) use (&$pass, &$fail): void {
    echo '['.($ok ? 'PASS' : 'FAIL').'] '.$label.PHP_EOL;
    $ok ? $pass++ : $fail++;
};
$valid = [
    'status' => 'completed', 'delivery_delivered' => true, 'checklist_total' => 4,
    'checklist_pending' => 0, 'item_count' => 1, 'unique_serials' => true,
    'missing_serials' => 0, 'installed_serials' => 1,
    'assigned_serials' => 0, 'other_serials' => 0,
];
$success = A758CaseRules::installation($valid);
$check('fully reconciled installation still needs documented approval',
    $success['decision'] === 'FORMAT_ONLY_CANDIDATE_PENDING_ORIGINAL_HANDOVER_AND_APPROVAL' && !$success['change_authorized']);
$actual = $valid; $actual['installed_serials']=0; $actual['assigned_serials']=1;
$r = A758CaseRules::installation($actual);
$check('legacy assigned device holds completion',
    $r['decision'] === 'HOLD_INSTALLATION_LIFECYCLE_REVIEW' && in_array('SERIAL_ASSIGNED_NOT_INSTALLED',$r['issues'],true));
$changes = [
    ['status','Scheduled','NOT_CASE_ONLY_COMPLETED'],
    ['delivery_delivered',false,'DELIVERY_NOT_CONFIRMED'],
    ['checklist_total',0,'CHECKLIST_NOT_VERIFIED'],
    ['checklist_pending',1,'CHECKLIST_NOT_VERIFIED'],
    ['item_count',0,'SERIAL_ALLOCATION_NOT_VERIFIED'],
    ['unique_serials',false,'SERIAL_ALLOCATION_NOT_VERIFIED'],
    ['missing_serials',1,'SERIAL_REFERENCE_MISSING'],
    ['other_serials',1,'SERIAL_NONINSTALLED_OR_UNKNOWN'],
    ['installed_serials',0,'NOT_ALL_SERIALS_INSTALLED'],
];
foreach ($changes as [$field,$value,$issue]) {
    $facts=$valid;$facts[$field]=$value;
    $check('installation fails closed on '.$field,
        in_array($issue,A758CaseRules::installation($facts)['issues'],true));
}
foreach (['unpaid', 'UNPAID', ' unpaid '] as $status) {
    $r=A758CaseRules::invoice(['status'=>$status]);
    $check('legacy unpaid requires real billing evidence: '.trim($status),
        $r['decision']==='HOLD_ORIGINAL_ISSUANCE_AND_PAYMENT_PROOF' && !$r['change_authorized']);
}
foreach (['Draft','Sent','Paid','Cancelled'] as $status) {
    $r=A758CaseRules::invoice(['status'=>$status]);
    $check('canonical invoice status unaffected: '.$status,
        $r['decision']==='CANONICAL_STATUS_NO_CORRECTION_PROPOSED' && !$r['change_authorized']);
}
$check('invoice case-only difference still requires approval',
    A758CaseRules::invoice(['status'=>' paid '])['decision']==='FORMAT_ONLY_CANDIDATE_PENDING_DOCUMENTS_AND_APPROVAL');
$check('arbitrary billing status cannot be guessed',
    A758CaseRules::invoice(['status'=>'partially_collected'])['decision']==='HOLD_UNRECOGNIZED_INVOICE_BUSINESS_STATE');
$source=file_get_contents(__DIR__.'/a758_incident_review.php');
$check('application tool enforces PostgreSQL read-only transaction',
    str_contains($source,"$".'connection->beginTransaction()') && str_contains($source,"SET TRANSACTION READ ONLY"));
$check('application tool has no mutation callsites',
    !preg_match('/\-\>(?:insert|update|delete|truncate|upsert|increment|decrement|save|create)\s*\(/i',$source)
    && !preg_match('/DB::(?:statement|unprepared|insert|update|delete)\s*\(/i',$source));
$check('tool only permits explicit UUIDs and local app environment',
    str_contains($source,'--installation') && str_contains($source,'--invoice')
    && str_contains($source,"environment('local')"));
echo "A7.5.8 pure tests: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. No DB reads or writes.\n";
exit($fail?1:0);
