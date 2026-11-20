<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = 0;
$fails = 0;
function a7752Assert(bool $ok, string $description): void
{
    global $checks, $fails;
    $checks++;
    if (!$ok) $fails++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $description . PHP_EOL;
}

$files = [
    'app/Http/Controllers/SalesOrderController.php',
    'app/Support/CrmCsvCellSanitizer.php',
    'tools/a7752_apply_sales_order_lazy_patch.php',
    'tests/Unit/CrmSalesOrderCsvBatchSourceTest.php',
    'tests/Feature/CrmSalesOrderCsvSqliteIsolationTest.php',
];
foreach ($files as $relative) {
    $path = $root . '/' . $relative;
    $exists = is_file($path);
    a7752Assert($exists, 'File ' . $relative);
    if (!$exists) continue;
    $code = 0;
    $output = [];
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1', $output, $code);
    a7752Assert($code === 0, 'PHP syntax ' . $relative);
}

$source = (string) @file_get_contents($root . '/app/Http/Controllers/SalesOrderController.php');
$tool = (string) @file_get_contents($root . '/tools/a7752_apply_sales_order_lazy_patch.php');
$test = (string) @file_get_contents($root . '/tests/Feature/CrmSalesOrderCsvSqliteIsolationTest.php');
a7752Assert(str_contains($source, "SalesOrder::with(['quotation.opportunity.customer'])"), 'Eager-loading relation chain retained');
a7752Assert(str_contains($source, "foreach (\$query->orderByDesc('sales_order_id')->lazy(200) as \$order) {"), 'CSV uses bounded batches and deterministic tie-break');
a7752Assert(!str_contains($source, 'foreach ($query->cursor() as $order) {'), 'CSV no longer drops eager loading via cursor');
a7752Assert(str_contains($source, 'CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10])'), 'Existing CSV formula protection retained');
a7752Assert(str_contains($source, '$orders = $query->limit(2000)->get();'), 'PDF export unchanged');
a7752Assert(str_contains($tool, "['--dry-run', '--apply']"), 'Explicit safe patch modes');
a7752Assert(str_contains($tool, 'finally {'), 'Temporary staging cleanup in finally');
a7752Assert(str_contains($test, "':memory:'"), 'Isolated test uses SQLite memory database');
a7752Assert(str_contains($test, 'getQueryLog()'), 'Query-count regression assertions present');
a7752Assert(!str_contains($tool, 'DB::') && !str_contains($tool, 'Schema::'), 'Patch tool contains no database calls');
if ($fails > 0) {
    echo "[RESULT] {$fails} FAIL; do not continue.\n";
    exit(1);
}
echo "[RESULT] {$checks}/{$checks} PASS; 0 FAIL. No DB.\n";
