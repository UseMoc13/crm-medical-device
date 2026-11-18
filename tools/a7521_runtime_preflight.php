<?php
/** Boots Laravel only; read-only schema and command discovery checks. */
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass = 0; $fail = 0;
$check = static function (string $name, bool $ok) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
$commands = \Illuminate\Support\Facades\Artisan::all();
$check('A7.5.2.1 command registered', array_key_exists('crm:integration:inspect', $commands));
$check('A7.5.1 audit command still registered', array_key_exists('crm:integration:audit', $commands));
$check('A7.5.2.1 investigator resolves', app(\App\Services\CrmIntegrationInvestigation::class) instanceof \App\Services\CrmIntegrationInvestigation);
foreach (['delivery_items', 'deliveries', 'serial_numbers', 'stock_movements', 'installations', 'invoices'] as $table) {
    $check('Required table ' . $table, \Illuminate\Support\Facades\Schema::hasTable($table));
}
echo "\nA7.5.2.1 runtime: {$pass}/" . ($pass + $fail) . ' PASS, ' . $fail . ' FAIL. Read-only.' . PHP_EOL;
exit($fail ? 1 : 0);
