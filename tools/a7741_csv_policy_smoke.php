<?php
/** A7.7.4.1 pure PHP test; no Laravel or database. */
declare(strict_types=1);
require dirname(__DIR__).'/app/Support/CrmCsvCellSanitizer.php';
use App\Support\CrmCsvCellSanitizer as Csv;
$checks=[
    'Formula =' => [Csv::row(['=1+2'],[0])[0] === "'=1+2"],
    'Formula +' => [Csv::row(['+1+2'],[0])[0] === "'+1+2"],
    'Formula -' => [Csv::row(['-1+2'],[0])[0] === "'-1+2"],
    'Formula @' => [Csv::row(['@SUM()'],[0])[0] === "'@SUM()"],
    'Tab prefix' => [Csv::row(["\t=1+2"],[0])[0] === "'\t=1+2"],
    'Unicode invisibles' => [Csv::row(["\u{200B}=1+2"],[0])[0] === "'\u{200B}=1+2"],
    'Normal string' => [Csv::row(['Customer'],[0])[0] === 'Customer'],
    'Date text' => [Csv::row(['2026-11-20'],[0])[0] === '2026-11-20'],
    'Numeric negative unchanged' => [Csv::row([-42.0],[0])[0] === -42.0],
    'Numeric database string retained' => [Csv::row(['=bad','-250.00'],[0])[1] === '-250.00'],
    'Null unchanged' => [Csv::row([null],[0])[0] === null],
    'Invalid UTF8 fails closed' => [Csv::row(["\xC3\x28=1+1"],[0])[0] === "'\xC3\x28=1+1"],
];
$success=0;
foreach($checks as $name=>$cases){$ok=$cases[0];$success+=(int)$ok;echo ($ok?'[PASS] ':'[FAIL] ').$name."\n";}
echo "A7.7.4.1 smoke: {$success}/".count($checks)." PASS. No DB.\n";
exit($success===count($checks)?0:1);
