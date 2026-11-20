<?php
/** Pure, database-free tests. Run: php tools/a756_evidence_test.php */
declare(strict_types=1);
require __DIR__.'/a756_evidence_rules.php';
$passed = 0; $failed = 0;
$test = static function (bool $ok, string $name) use (&$passed, &$failed): void {
    $ok ? $passed++ : $failed++;
    echo '['.($ok ? 'PASS' : 'FAIL').'] '.$name.PHP_EOL;
};
$item = static fn (string $id, ?string $warehouse, string $product='p1', int $quantity=1, string $serial='s1'): array =>
    ['item_id'=>$id,'warehouse_id'=>$warehouse,'product_id'=>$product,'quantity'=>$quantity,'serial_id'=>$serial];
$movement = static fn (?string $warehouse, string $product='p1', int $quantity=1): array =>
    ['warehouse_id'=>$warehouse,'product_id'=>$product,'quantity'=>$quantity];
$ok = A756EvidenceRules::delivery([$item('a',null)], [$movement('w1')], 'a');
$test($ok['decision']==='MATCHES_IF_PROVENANCE_APPROVED' && !$ok['auto_apply_allowed'], 'one historical null warehouse: simulated match, NO automatic fix');
$test(A756EvidenceRules::delivery([$item('a',null)], [$movement('w1'),$movement('w2')], 'a')['decision']==='HOLD_AMBIGUOUS_WAREHOUSE', 'multiple warehouses fail closed');
$test(A756EvidenceRules::delivery([$item('a',null)], [$movement('w1','p1',2)], 'a')['decision']==='HOLD_QUANTITY_OR_PRODUCT_MISMATCH', 'wrong stock-out quantity fails closed');
$test(A756EvidenceRules::delivery([$item('a',null)], [$movement('w1','p2')], 'a')['decision']==='HOLD_AMBIGUOUS_WAREHOUSE', 'wrong product fails closed');
$test(A756EvidenceRules::delivery([$item('a',null),$item('b',null,'p1',1,'s2')], [$movement('w1','p1',2)], 'a')['decision']==='HOLD_OTHER_ITEM_MISSING_WAREHOUSE', 'unresolved other allocation fails closed');
$test(A756EvidenceRules::delivery([$item('a',null),$item('b','w1','p1',1,'s1')], [$movement('w1','p1',2)], 'a')['decision']==='HOLD_ITEM_INCONSISTENT', 'duplicate serial fails closed');
$test(A756EvidenceRules::delivery([$item('a','w1')], [$movement('w1')], 'a')['decision']==='HOLD_ITEM_INCONSISTENT', 'already allocated source not rewritten');
$test(A756EvidenceRules::delivery([$item('a',null)], [], 'a')['decision']==='HOLD_AMBIGUOUS_WAREHOUSE', 'absent stockout fails closed');
$test(A756EvidenceRules::delivery([$item('a',null)], [$movement('w1')], 'other')['decision']==='HOLD_ITEM_NOT_FOUND', 'missing target fails closed');
$inst = A756EvidenceRules::installation('completed','delivered',4,4,['assigned']);
$test($inst['decision']==='HOLD_PRECONDITIONS' && !$inst['all_serials_installed'], 'installation case-only difference cannot bypass serial state');
$inst = A756EvidenceRules::installation('completed','delivered',4,4,['installed']);
$test($inst['decision']==='FORMAT_ONLY_CANDIDATE_NEEDS_APPROVAL' && !$inst['auto_apply_allowed'], 'healthy installation still requires approval');
$test(A756EvidenceRules::installation('completed','pending',4,4,['installed'])['decision']==='HOLD_PRECONDITIONS', 'delivery precondition required');
$test(A756EvidenceRules::installation('completed','delivered',0,0,['installed'])['decision']==='HOLD_PRECONDITIONS', 'empty checklist never treated complete');
$test(A756EvidenceRules::invoice('unpaid')['decision']==='HOLD_BILLING_MEANING', 'legacy unpaid is never guessed into Sent or Paid');
$test(A756EvidenceRules::invoice(' paid ')['decision']==='FORMAT_ONLY_CANDIDATE_NEEDS_APPROVAL', 'case/whitespace candidate requires approval');
$test(A756EvidenceRules::invoice('Sent')['decision']==='RECOGNIZED', 'existing standard invoice untouched');
echo 'A7.5.6: '.$passed.'/'.($passed+$failed).' PASS; '.$failed.' FAIL. No DB access.'.PHP_EOL;
exit($failed ? 1 : 0);
