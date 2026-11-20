<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$pass = 0;
$fail = 0;
function checkA7761(bool $condition, string $message): void
{
    global $pass, $fail;
    if ($condition) $pass++; else $fail++;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $message . PHP_EOL;
}

$files = [
    'app/Http/Controllers/SalesOrderController.php',
    'app/Support/CrmCsvCellSanitizer.php',
    'tests/Feature/CrmSalesOrderCsvMultiBatchReliabilityTest.php',
    'tests/Unit/CrmSalesOrderCsvReliabilitySourceTest.php',
];
foreach ($files as $relative) {
    $file = $root.'/'.$relative;
    checkA7761(is_file($file), "Exists {$relative}");
    if (!is_file($file)) continue;
    $command = escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1';
    exec($command, $out, $status);
    checkA7761($status === 0, "PHP syntax {$relative}");
    unset($out, $status);
}
$controller = (string) @file_get_contents($root.'/app/Http/Controllers/SalesOrderController.php');
$feature = (string) @file_get_contents($root.'/tests/Feature/CrmSalesOrderCsvMultiBatchReliabilityTest.php');
$unit = (string) @file_get_contents($root.'/tests/Unit/CrmSalesOrderCsvReliabilitySourceTest.php');
checkA7761(str_contains($controller, "orderByDesc('sales_order_id')->lazy(200)"), 'Batch size 200 and stable tie-break preserved');
checkA7761(str_contains($controller, "SalesOrder::with(['quotation.opportunity.customer'])"), 'Eager-loaded relationships preserved');
checkA7761(str_contains($controller, 'CrmCsvCellSanitizer::row($fields, [0, 1, 2, 5, 10])'), 'CSV formula sanitation preserved');
checkA7761(str_contains($controller, '$orders = $query->limit(2000)->get();'), 'PDF branch untouched');
checkA7761(str_contains($feature, "':memory:'"), 'Synthetic SQLite memory database required');
checkA7761(str_contains($feature, "app()->environment()"), 'Testing-only gate present');
checkA7761(str_contains($feature, 'DB::setDefaultConnection(self::CONNECTION)'), 'Isolated database connection switched explicitly');
checkA7761(str_contains($feature, 'DB::setDefaultConnection($this->previousConnection)'), 'Original database connection restored');
checkA7761(str_contains($feature, '405'), 'Multi-batch fixture crosses two 200-row boundaries');
checkA7761(str_contains($feature, 'getQueryLog()'), 'Query counts measured');
checkA7761(str_contains($feature, 'array_unique($identifiers)'), 'Duplicate-row regression asserted');
checkA7761(!str_contains($feature, 'RefreshDatabase') && !str_contains($feature, 'artisan migrate'), 'No CRM DB migration/reset commands');
checkA7761(str_contains($unit, 'test_export_remains_batched_ordered_and_formula_safe'), 'Source regression test provided');
echo "A7.7.6.1 static: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. No DB connection used.\n";
exit($fail ? 1 : 0);
