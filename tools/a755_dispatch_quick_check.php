<?php
/** A7.5.5 static installation verification; never boots Laravel or queries DB. */
declare(strict_types=1);
$root = dirname(__DIR__);
$files = [
    'app/Http/Controllers/DeliveryController.php',
    'app/Services/CrmDeliveryCompletionGuard.php',
    'tests/Unit/CrmDeliveryDispatchPostconditionSourceTest.php',
    'tests/Feature/CrmDeliveryDispatchPostconditionIsolationTest.php',
    'docs/A755_DELIVERY_DISPATCH_POSTCONDITION.md',
];
$pass = 0;
$fail = 0;
$check = static function (bool $result, string $label) use (&$pass, &$fail): void {
    if ($result) { $pass++; echo '[PASS] '.$label."\n"; }
    else { $fail++; echo '[FAIL] '.$label."\n"; }
};
foreach ($files as $path) {
    $file = $root.'/'.$path;
    $check(is_file($file), 'File: '.$path);
    if (is_file($file) && str_ends_with($path, '.php')) {
        $out = [];
        $code = 1;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $out, $code);
        $check($code === 0, 'PHP syntax: '.$path);
    }
}
$controller = @file_get_contents($root.'/'.$files[0]);
$controller = $controller === false ? '' : str_replace("\r\n", "\n", $controller);
$start = strpos($controller, 'public function changeStatus(');
$end = strpos($controller, 'private function dispatchAllocatedUnits(', $start === false ? 0 : $start);
$method = $start !== false && $end !== false ? substr($controller, $start, $end-$start) : '';
$dispatch = <<<'CODE'
            if ($validated['status'] === 'in_transit') {
                $this->dispatchAllocatedUnits($locked);
                // A7.5.5: fail closed before committing any stock-out or in_transit state.
                // Throws ValidationException => entire DB::transaction rolls back.
                app(\App\Services\CrmDeliveryCompletionGuard::class)->assertCanComplete($locked);
            }
CODE;
$check(str_contains($method, $dispatch), 'Dispatch block has postcondition after stock-out');
$check(substr_count($method, '->assertCanComplete($locked);') === 2, 'Both in_transit and delivered paths guarded');
$check(str_contains($method, 'DB::transaction(function () use ($delivery, $validated)'), 'Transition remains inside DB transaction');
$save = strpos($method, '$locked->save();');
$valid = strpos($method, '// A7.5.5: fail closed');
$check($save !== false && $valid !== false && $valid < $save, 'Postcondition runs before status saved');
$check(str_contains((string) @file_get_contents($root.'/'.$files[1]), 'quantitiesMatch($allocations, $movements)'), 'Uses existing strict warehouse/product reconciliation');
echo "A7.5.5 static: {$pass}/".($pass + $fail)." PASS; {$fail} FAIL. No DB queries.\n";
exit($fail ? 1 : 0);
