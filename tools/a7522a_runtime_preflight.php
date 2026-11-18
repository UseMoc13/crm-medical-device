<?php
/** Only schema inspection; no SELECT of customer or operational record contents. */
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    $checks[] = $ok;
};
$check('Guard service resolves', app(\App\Services\CrmDeliveryCompletionGuard::class) instanceof \App\Services\CrmDeliveryCompletionGuard);
foreach (['deliveries', 'delivery_items', 'serial_numbers', 'stock_movements', 'sales_order_items'] as $table) {
    $check('Table ' . $table, \Illuminate\Support\Facades\Schema::hasTable($table));
}
$check('Delivery warehouse provenance column', \Illuminate\Support\Facades\Schema::hasColumn('delivery_items', 'warehouse_id'));
$passed = count(array_filter($checks));
echo 'A7.5.2.2a runtime preflight: ' . $passed . '/' . count($checks) . ' PASS (read-only).' . PHP_EOL;
exit($passed === count($checks) ? 0 : 1);
