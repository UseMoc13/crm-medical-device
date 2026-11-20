<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmUatDataReadiness.php';
use App\Support\CrmUatDataReadiness as Policy;
$passed=0;$failed=0;
$check=static function(bool $ok, string $label) use (&$passed,&$failed):void {
    echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
    $ok ? ++$passed : ++$failed;
};
$all=['customers'=>'HAS_SAMPLE','quotations'=>'HAS_SAMPLE','sales_orders'=>'HAS_SAMPLE'];
$linked=['customer_opportunity'=>'LINKED_SAMPLE','invoice_sales_chain'=>'LINKED_SAMPLE'];
$check(Policy::summarize($all,$linked)['decision']==='DATA_COVERAGE_CANDIDATE_NOT_UAT_PASS','All linked samples do not imply UAT pass');
$check(Policy::summarize(array_map(fn($x)=>'EMPTY',$all),$linked)['decision']==='SYNTHETIC_DATA_REQUIRED','All tables empty needs dataset');
$check(Policy::summarize($all,['customer_opportunity'=>'NO_LINKED_SAMPLE'])['decision']==='PARTIAL_SAMPLE_DATA_REVIEW','Missing linked chain needs review');
$check(Policy::summarize(['customers'=>'MISSING'], $linked)['decision']==='HOLD_SCHEMA_GAPS','Missing table holds');
$check(Policy::summarize($all,['invoice_sales_chain'=>'BLOCKED_MISSING_TABLE'])['decision']==='HOLD_SCHEMA_GAPS','Blocked chain holds');
try {Policy::summarize($all,['x'=>'NOT_A_STATUS']);$check(false,'Unknown chain status rejected');}
catch(InvalidArgumentException $e){$check(true,'Unknown chain status rejected');}
try {Policy::summarize([], $linked);$check(false,'Empty table summary rejected');}
catch(InvalidArgumentException $e){$check(true,'Empty table summary rejected');}
try {Policy::summarize($all, []);$check(false,'Empty chain summary rejected');}
catch(InvalidArgumentException $e){$check(true,'Empty chain summary rejected');}
$chains=Policy::chains();
$check(count($chains)===7,'Seven cross-module chain probes declared');
foreach($chains as $id=>$probe) {
    $sql=$probe['sql'];
    $check(str_starts_with($sql,'SELECT EXISTS (SELECT 1 FROM public.') && str_ends_with($sql,' LIMIT 1) AS found')
        && preg_match('/\b(?:INSERT|UPDATE|DELETE|TRUNCATE|ALTER|DROP|CREATE|ANALYZE)\b/i',$sql)!==1,
        'Fixed read-only SQL: '.$id);
}
echo "A7.8.2 smoke: {$passed}/".($passed+$failed)." PASS; {$failed} FAIL. NO DB.\n";
exit($failed===0?0:1);
