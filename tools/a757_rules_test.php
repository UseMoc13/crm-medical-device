<?php
/** A7.5.7 pure tests; no autoload, no Laravel, no DB. */
declare(strict_types=1);
require __DIR__.'/a757_delivery_rules.php';
$checks=0; $failed=0;
$assert=static function(bool $ok,string $name) use (&$checks,&$failed): void {
    ++$checks;
    if(!$ok) ++$failed;
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
};
$delivery=['delivery_id'=>'d1','sales_order_id'=>'s1','status'=>'delivered'];
$items=[['item_id'=>'i1','warehouse_id'=>null,'product_id'=>'p1','quantity'=>1,'serial_id'=>'sn1','serial_status'=>'assigned']];
$movements=[['warehouse_id'=>'w1','product_id'=>'p1','quantity'=>1]];
$ordered=['p1'=>1];
$test=static function(?array $d=null,?array $i=null,?array $m=null,?array $o=null,array $w=['w1'],string $item='i1',string $wh='w1') use ($delivery,$items,$movements,$ordered): array {
    return A757DeliveryRules::evaluate($d??$delivery,$i??$items,$m??$movements,$o??$ordered,$w,$item,$wh);
};
$assert($test()['decision']==='TECHNICAL_MATCH_PENDING_HUMAN_PROOF', 'matching case is technical only; human approval required');
$assert($test()['approval_required']===true && $test()['technical_ready']===true, 'technical decision never implies approval');
$assert($test(['delivery_id'=>'d1','sales_order_id'=>'s1','status'=>'pending'])['decision']==='HOLD_DELIVERY_NOT_DISPATCHED','pending delivery rejected');
$assert($test(['delivery_id'=>'d1','sales_order_id'=>'s1','status'=>'cancelled'])['decision']==='HOLD_DELIVERY_NOT_DISPATCHED','cancelled delivery rejected');
$assert($test($delivery,[], $movements)['decision']==='HOLD_MISSING_SOURCE','empty items rejected');
$assert($test($delivery,$items, [])['decision']==='HOLD_MISSING_SOURCE','missing stockouts rejected');
$assert($test($delivery,$items,$movements,$ordered,[])['decision']==='HOLD_PROPOSED_WAREHOUSE_NOT_FOUND','warehouse must exist');
$assert($test($delivery,$items,$movements,$ordered,['w2'],'i1','w1')['decision']==='HOLD_PROPOSED_WAREHOUSE_NOT_FOUND','nonexisting target rejected');
$assert($test($delivery,$items,$movements,$ordered,['w1'],'wrong')['decision']==='HOLD_TARGET_NOT_FOUND','wrong item rejected');
$i=$items;$i[0]['warehouse_id']='w1';
$assert($test($delivery,$i)['decision']==='HOLD_ALREADY_HAS_WAREHOUSE','already populated item cannot change');
$i=$items;$i[0]['serial_status']='available';
$assert($test($delivery,$i)['decision']==='HOLD_ALLOCATION_OR_SERIAL_STATE','unexpected serial lifecycle state rejected');
$i=$items;$i[0]['serial_status']='installed';
$assert($test($delivery,$i)['technical_ready']===true,'installed legacy serial is supported when everything else matches');
$i=$items;$i[]=$i[0];
$assert($test($delivery,$i)['decision']==='HOLD_ALLOCATION_OR_SERIAL_STATE','duplicate serial/item rejected');
$i=$items;$i[0]['quantity']=2;
$assert($test($delivery,$i)['decision']==='HOLD_ALLOCATION_OR_SERIAL_STATE','non-unit allocation rejected');
$assert($test($delivery,$items,$movements,['p1'=>0])['decision']==='HOLD_SALES_ORDER_BOUND','order bound enforced');
$assert($test($delivery,$items,$movements,[])['decision']==='HOLD_SALES_ORDER_BOUND','missing order product rejected');
$m=$movements;$m[]=['warehouse_id'=>'w2','product_id'=>'p1','quantity'=>1];
$assert($test($delivery,$items,$m)['decision']==='HOLD_STOCKOUT_WAREHOUSE_AMBIGUOUS_OR_DIFFERENT','two stockout warehouses rejected');
$assert($test($delivery,$items,$movements,$ordered,['w2'],'i1','w2')['decision']==='HOLD_STOCKOUT_WAREHOUSE_AMBIGUOUS_OR_DIFFERENT','different proposed warehouse rejected');
$m=[['warehouse_id'=>'w1','product_id'=>'p1','quantity'=>2]];
$assert($test($delivery,$items,$m)['decision']==='HOLD_SIMULATED_ALLOCATION_MISMATCH','stockout quantity mismatch rejected');
$m=[['warehouse_id'=>'w1','product_id'=>'p2','quantity'=>1]];
$assert($test($delivery,$items,$m)['decision']==='HOLD_STOCKOUT_WAREHOUSE_AMBIGUOUS_OR_DIFFERENT','wrong movement product rejected');
$i=$items;$i[]=['item_id'=>'i2','warehouse_id'=>null,'product_id'=>'p1','quantity'=>1,'serial_id'=>'sn2','serial_status'=>'assigned'];
$assert($test($delivery,$i,$movements,['p1'=>2])['decision']==='HOLD_SIMULATED_ALLOCATION_MISMATCH','unresolved second warehouse rejected');
$i=$items;$i[0]['warehouse_id']='w1';
$assert(A757DeliveryRules::postcondition($i,$movements),'post-update matching groups accepted');
$assert(!A757DeliveryRules::postcondition($i,[['warehouse_id'=>'w2','product_id'=>'p1','quantity'=>1]]),'wrong warehouse postcondition rejected');
$assert(!A757DeliveryRules::postcondition($i,[]),'empty movements postcondition rejected');
$approved=[
 'decision'=>'approve_single_delivery_item_warehouse_correction',
 'delivery_id'=>'d1','delivery_item_id'=>'i1','warehouse_id'=>'w1',
 'original_dispatch_document_reference'=>'Dispatch docket #123 (internal)',
 'original_dispatch_document_verified'=>true,
 'warehouse_provenance_independently_verified'=>true,
 'approved_by'=>'Reviewer One', 'independently_verified_by'=>'Reviewer Two',
 'approved_at'=>gmdate('c'),
];
$assert(A757DeliveryRules::approval($approved,'d1','i1','w1'),'external approval metadata accepted only after verification');
$assert(!A757DeliveryRules::approval($approved,'d1','i1','w2'),'approval must match warehouse');
$assert(!A757DeliveryRules::approval($approved,'d2','i1','w1'),'approval must match delivery');
$assert(!A757DeliveryRules::approval($approved,'d1','i2','w1'),'approval must match item');
$a=$approved;$a['independently_verified_by']='Reviewer One';
$assert(!A757DeliveryRules::approval($a,'d1','i1','w1'),'independent reviewer required');
$a=$approved;$a['warehouse_provenance_independently_verified']=false;
$assert(!A757DeliveryRules::approval($a,'d1','i1','w1'),'independent document check required');
$a=$approved;unset($a['original_dispatch_document_reference']);
$assert(!A757DeliveryRules::approval($a,'d1','i1','w1'),'original document reference required');
$a=$approved;$a['approved_at']='1900-01-01';
$assert(!A757DeliveryRules::approval($a,'d1','i1','w1'),'stale approval rejected');
$a=$approved;$a['original_dispatch_document_verified']=false;
$assert(!A757DeliveryRules::approval($a,'d1','i1','w1'),'unverified provenance rejected');
$src=file_get_contents(__DIR__.'/a757_delivery_warehouse.php');
$assert(str_contains($src,"whereNull('warehouse_id')")&&str_contains($src,"->update(['warehouse_id'=>\$warehouseId])"),'single-field compare-and-swap code present');
$assert(str_contains($src,'SET TRANSACTION READ ONLY'),'PostgreSQL preview read-only transaction');
$assert(str_contains($src,'--execute')&&str_contains($src,'APPLY_APPROVED_WAREHOUSE_CORRECTION'),'explicit confirmation required');
$assert(!preg_match('/(?:stock_movements|serial_numbers|inventories|deliveries)[\s\S]{0,120}?->update\s*\(/',$src),'no neighboring business table updates');
echo "A7.5.7 rules: ".($checks-$failed)."/{$checks} PASS; {$failed} FAIL. Pure tests, no DB.\n";
exit($failed?1:0);
