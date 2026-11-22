<?php

declare(strict_types=1);
/** No Laravel boot, NO DB; synthetic schema catalog checks. */
require dirname(__DIR__).'/app/Support/CrmUatSchemaSnapshot.php';
use App\Support\CrmUatSchemaSnapshot as S;
$pass = 0; $fail = 0;
$test = static function (string $label, bool $good) use (&$pass,&$fail): void {
    echo ($good ? '[PASS] ' : '[FAIL] ').$label.PHP_EOL;
    $good ? ++$pass : ++$fail;
};
$c = static fn (string $t,string $n,int $i,bool $required): object => (object) [
 'table_name'=>$t,'column_name'=>$n,'ordinal'=>$i,'data_type'=>'uuid',
 'not_null'=>$required,'has_default'=>!$required,'is_identity'=>false,'is_generated'=>false
];
$a = S::build([(object)['table_name'=>'customers'],(object)['table_name'=>'invoices']], [
    $c('customers','customer_id',1,false),$c('invoices','invoice_id',1,false),
    $c('invoices','customer_id',2,true),
], [(object)['table_name'=>'invoices','constraint_name'=>'invoices_customer_fk',
    'constraint_type'=>'f','definition'=>'FOREIGN KEY (customer_id) REFERENCES customers(customer_id)']],[],[]);
$b = S::build([(object)['table_name'=>'invoices'],(object)['table_name'=>'customers']], [
    $c('customers','customer_id',1,false),$c('invoices','invoice_id',1,false),
    $c('invoices','customer_id',2,true),
], [(object)['table_name'=>'invoices','constraint_name'=>'invoices_customer_fk',
    'constraint_type'=>'f','definition'=>'FOREIGN KEY (customer_id) REFERENCES customers(customer_id)']],[],[]);
$test('Two synthetic tables', $a['summary']['tables'] === 2);
$test('Three synthetic columns', $a['summary']['columns'] === 3);
$test('One required input', $a['summary']['required_without_default'] === 1);
$test('One foreign key', $a['summary']['foreign_keys'] === 1);
$test('No database connection details', !isset($a['connection']) && !isset($a['database']));
$test('Missing schema table detected', S::missingTables($a, ['customers','products']) === ['products']);
$test('Fingerprint stable under table reordering', $a['fingerprint_sha256'] === $b['fingerprint_sha256']);
$test('No business records in snapshot', !isset($a['rows']) && !isset($a['records']));
foreach([
    'Empty catalog rejected' => static fn () => S::build([],[],[],[],[]),
    'Unknown constraint table rejected' => static fn () => S::build([(object)['table_name'=>'customers']],[],[(object)['table_name'=>'other','constraint_name'=>'fk','constraint_type'=>'f','definition'=>'FOREIGN KEY (x) REFERENCES y(id)']],[],[]),
    'Unknown column table rejected' => static fn () => S::build([(object)['table_name'=>'customers']],[$c('other','id',1,true)],[],[],[]),
    'Malformed table identifier rejected' => static fn () => S::build([(object)['table_name'=>'oops;DROP']],[],[],[],[]),
] as $label=>$fn) {
    $rejected = false;
    try { $fn(); } catch (\InvalidArgumentException $e) { $rejected = true; }
    $test($label, $rejected);
}
echo "A7.9.1 smoke: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. No DB.\n";
exit($fail === 0 ? 0 : 1);
