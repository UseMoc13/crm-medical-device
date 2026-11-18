<?php
/** Source-only static checks: no database connection or mutation. */
$root = dirname(__DIR__);
$controller = @file_get_contents($root . '/app/Http/Controllers/InvoiceController.php');
if (is_string($controller)) $controller = str_replace(["\r\n", "\r"], "\n", $controller);
$guard = @file_get_contents($root . '/app/Services/CrmInvoiceRowGuard.php');
$test = @file_get_contents($root . '/tests/Unit/CrmInvoiceRowGuardTest.php');
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$check('InvoiceController installed', is_string($controller));
$check('Row lock service installed', is_string($guard));
$check('Unit test installed', is_string($test));
if (is_string($controller)) {
    foreach (['update', 'updateStatus', 'destroy'] as $method) {
        $start = strpos($controller, '    public function '.$method.'(');
        $end = $start === false ? false : strpos($controller, "\n    }\n", $start);
        $body = $end === false ? '' : substr($controller, $start, $end - $start);
        $check($method.' uses transaction', str_contains($body, 'DB::transaction('));
        $check($method.' rechecks locked status', str_contains($body, 'CrmInvoiceRowGuard::class') && str_contains($body, 'lockedStatus('));
    }
    $check('Existing invoice status policy preserved', str_contains($controller, "'Cancelled' => ['Draft']") && str_contains($controller, "'Sent' => ['Paid', 'Cancelled']"));
    $check('No legacy unpaid normalization', !str_contains($controller, "'unpaid' =>") && !str_contains($controller, "'Unpaid' =>"));
}
if (is_string($guard)) {
    $check('Row lock requires transaction', str_contains($guard,'DB::transactionLevel() < 1'));
    $check('Explicit lockForUpdate', str_contains($guard,'->lockForUpdate()'));
    $check('Guard does not write', !preg_match('/->(update|delete|insert|upsert)\s*\(/', $guard));
}
$files = [
    'app/Http/Controllers/InvoiceController.php',
    'app/Services/CrmInvoiceRowGuard.php',
    'tests/Unit/CrmInvoiceRowGuardTest.php',
    'tools/install_a7522b_invoice_guards.php',
];
foreach ($files as $f) {
    $out=[]; $exit=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$f).' 2>&1',$out,$exit);
    $check('PHP syntax: '.$f, $exit===0);
}
$passed=count(array_filter($checks));
echo "A7.5.2.2b static: {$passed}/".count($checks)." PASS; ".(count($checks)-$passed)." FAIL.".PHP_EOL;
exit($passed===count($checks) ? 0 : 1);
