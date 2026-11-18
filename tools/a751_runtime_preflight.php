<?php
/** Read-only Laravel runtime preflight, not an integration scan. */
declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root.'/vendor/autoload.php')) {
    fwrite(STDERR, "[FAIL] Composer vendor/autoload.php missing. Run composer install first.\n");
    exit(1);
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$check('Artisan crm:integration:audit discovered', \Illuminate\Support\Facades\Artisan::has('crm:integration:audit'));
$check('Integration audit service resolves', app(\App\Services\CrmIntegrationAudit::class) instanceof \App\Services\CrmIntegrationAudit);
$definitions = app(\App\Services\CrmIntegrationAudit::class)->definitions();
$check('Audit catalog has 30+ checks', count($definitions) >= 30);
foreach (['sales_orders','quotations','sales_order_items','deliveries','delivery_items','serial_numbers',
    'inventories','stock_movements','installations','installation_checklists','warranties','service_tickets','invoices'] as $table) {
    try { $ok = \Illuminate\Support\Facades\Schema::hasTable($table); }
    catch (\Throwable $e) { $ok = false; }
    $check('Required CRM table '.$table, $ok);
}
$pass = count(array_filter($checks));
echo "\nA7.5.1 runtime: {$pass}/".count($checks)." PASS. Read-only.\n";
exit($pass===count($checks)?0:1);
