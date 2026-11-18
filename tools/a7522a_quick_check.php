<?php
$root = dirname(__DIR__);
$files = [
    'app/Http/Controllers/DeliveryController.php',
    'app/Services/CrmDeliveryCompletionGuard.php',
    'tests/Unit/CrmDeliveryCompletionGuardTest.php',
];
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    $checks[] = $ok;
};
foreach ($files as $file) $check('File ' . $file, is_file($root . '/' . $file));
if (in_array(false, $checks, true)) exit(1);
$controller = file_get_contents($root . '/' . $files[0]);
$service = file_get_contents($root . '/' . $files[1]);
$start = strpos($controller, 'public function changeStatus(');
$end = strpos($controller, 'private function dispatchAllocatedUnits(', $start ?: 0);
$section = ($start !== false && $end !== false) ? substr($controller, $start, $end - $start) : '';
$guardAt = strpos($section, 'CrmDeliveryCompletionGuard::class');
$dateAt = strpos($section, "if (\$validated['status'] === 'delivered' && empty(\$locked->delivery_date))");
$check('Guard registered exactly once in status workflow', substr_count($section, 'CrmDeliveryCompletionGuard::class') === 1);
$check('Guard runs before final status and date are saved', $guardAt !== false && $dateAt !== false && $guardAt < $dateAt && $guardAt < strpos($section, '$locked->save()'));
$check('Existing atomic dispatch remains intact', str_contains($section, '$this->dispatchAllocatedUnits($locked)'));
$check('Completion guard never mutates stock', ! preg_match('/DB::table\([^;]+\)->(?:insert|update|delete|truncate)\(/s', $service));
$check('Completion guard compares warehouse and product', str_contains($service, 'warehouse_id') && str_contains($service, 'product_id') && str_contains($service, 'quantitiesMatch'));
$check('Historical Delivery untouched by installer', str_contains(file_get_contents(__DIR__ . '/install_a7522a_delivery_guard.php'), 'No migrations, database writes or historical stock corrections'));
$php = escapeshellarg(PHP_BINARY);
foreach ($files as $file) {
    $output = []; $exit = 1;
    exec($php . ' -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1', $output, $exit);
    $check('PHP syntax ' . basename($file), $exit === 0);
}
$passed = count(array_filter($checks));
echo 'A7.5.2.2a static: ' . $passed . '/' . count($checks) . ' PASS.' . PHP_EOL;
exit($passed === count($checks) ? 0 : 1);
