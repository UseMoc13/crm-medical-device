<?php
declare(strict_types=1);
/** Read-only Laravel command/service registration and schema check. */
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass = 0; $fail = 0;
$check = static function (string $label, bool $ok) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$commands = \Illuminate\Support\Facades\Artisan::all();
$check('Remediation review command discovered', array_key_exists('crm:integration:remediation-review', $commands));
$check('Existing investigation command retained', array_key_exists('crm:integration:inspect', $commands));
$check('Existing audit command retained', array_key_exists('crm:integration:audit', $commands));
$check('Review service resolves', app(\App\Services\CrmHistoricalRemediationReview::class) instanceof \App\Services\CrmHistoricalRemediationReview);
foreach (['deliveries', 'delivery_items', 'serial_numbers', 'stock_movements', 'installations', 'installation_checklists', 'invoices'] as $table) {
    $check('Required table ' . $table, \Illuminate\Support\Facades\Schema::hasTable($table));
}
echo "\nA7.5.2.2c runtime: {$pass}/" . ($pass + $fail) . " PASS, {$fail} FAIL. Read-only.\n";
exit($fail ? 1 : 0);
