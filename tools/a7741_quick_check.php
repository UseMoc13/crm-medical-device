<?php
/** A7.7.4.1 static application verification; no database. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [];
function checkA7741(string $name, bool $ok): void {
    global $checks;
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ').$name."\n";
}
foreach (['app/Support/CrmCsvCellSanitizer.php','app/Http/Controllers/ReportController.php','app/Http/Controllers/SalesOrderController.php','tests/Unit/CrmCsvCellSanitizerTest.php','tests/Unit/CrmCsvExportSourceTest.php'] as $file) {
    $path=$root.'/'.$file;
    checkA7741('Exists: '.$file, is_file($path));
    if (!is_file($path)) continue;
    $out=[];$exitCode=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1',$out,$exitCode);
    checkA7741('PHP syntax: '.$file, $exitCode===0);
}
$invoice = (string)@file_get_contents($root.'/app/Http/Controllers/ReportController.php');
$sales = (string)@file_get_contents($root.'/app/Http/Controllers/SalesOrderController.php');
$helper = (string)@file_get_contents($root.'/app/Support/CrmCsvCellSanitizer.php');
checkA7741('Invoice export sanitized',str_contains($invoice,'fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 3, 4, 5]));'));
checkA7741('Sales Order export sanitized',str_contains($sales,'fputcsv($out, \\App\\Support\\CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10]));'));
checkA7741('Invoice permission guard retained',str_contains($invoice,'$this->assertReportsView();'));
checkA7741('Invoice privacy pilot guard retained',str_contains($invoice,'CrmInvoiceCsvPolicy::class'));
checkA7741('Invoice data-scope retained',str_contains($invoice,'CrmReportingDataScope::class'));
checkA7741('Sales Order query still cursor streamed',str_contains($sales,'foreach ($query->cursor() as $order)'));
checkA7741('Sanitizer rejects leading formula triggers',str_contains($helper,'[=+\\-@]'));
checkA7741('No DB calls in sanitizer',!preg_match('/(?:DB::|->(?:update|insert|delete)\s*\()/i',$helper));
$passed=count(array_filter($checks));$all=count($checks);
echo "A7.7.4.1 static: {$passed}/{$all} PASS; ".($all-$passed)." FAIL. No DB.\n";
exit($passed===$all?0:1);
