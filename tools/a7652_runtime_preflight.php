<?php
/** Source/route/command readiness, no business rows. */
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = static function (string $name, bool $ok) use (&$checks): void { $checks[$name] = $ok; echo ($ok ? '[PASS] ' : '[FAIL] ').$name."\n"; };
$check('Parity service resolves', app(\App\Services\CrmFinancialParityBaseline::class) instanceof \App\Services\CrmFinancialParityBaseline);
$check('Parity Artisan command registered', array_key_exists('crm:reporting:parity-baseline', \Illuminate\Support\Facades\Artisan::all()));
$check('Reports index route GET', \Illuminate\Support\Facades\Route::has('reports.index') && in_array('GET', \Illuminate\Support\Facades\Route::getRoutes()->getByName('reports.index')->methods(), true));
$check('Reports CSV route GET', \Illuminate\Support\Facades\Route::has('reports.export.invoices') && in_array('GET', \Illuminate\Support\Facades\Route::getRoutes()->getByName('reports.export.invoices')->methods(), true));
$check('Dashboard query service resolves', app(\App\Services\CrmScopedKpiQuery::class) instanceof \App\Services\CrmScopedKpiQuery);
$check('Report downstream scope resolves', app(\App\Services\CrmReportingDataScope::class) instanceof \App\Services\CrmReportingDataScope);
echo 'A7.6.5.2 runtime: '.count(array_filter($checks)).'/'.count($checks).' PASS. No CRM record queries.'."\n";
exit(in_array(false, $checks, true) ? 1 : 0);
