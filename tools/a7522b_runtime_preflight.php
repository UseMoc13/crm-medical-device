<?php
/** Read-only runtime preflight. No migrations, writes or seeding. */
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks=[];
$check=static function(string $label,bool $ok) use (&$checks):void {
    $checks[]=$ok;
    echo ($ok?'[PASS] ':'[FAIL] ').$label.PHP_EOL;
};
$check('Guard service resolves', app(\App\Services\CrmInvoiceRowGuard::class) instanceof \App\Services\CrmInvoiceRowGuard);
$check('Invoice table present', \Illuminate\Support\Facades\Schema::hasTable('invoices'));
$check('Invoice status column present', \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'status'));
$check('Invoice ID column present', \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'invoice_id'));
$check('Invoice items table present', \Illuminate\Support\Facades\Schema::hasTable('invoice_items'));
$routes=app('router')->getRoutes();
$route=$routes->getByName('invoices.status');
$check('Invoice status route available', $route!==null && in_array('PATCH', $route->methods(),true));
$check('Invoice update route available', $routes->getByName('invoices.update')!==null);
$check('Invoice delete route available', $routes->getByName('invoices.destroy')!==null);
$passed=count(array_filter($checks));
echo "A7.5.2.2b runtime: {$passed}/".count($checks)." PASS. Read-only.".PHP_EOL;
exit($passed===count($checks)?0:1);
