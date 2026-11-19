<?php
/** A7.6.5.5 Laravel boot and feature readiness only. NO business SQL. */
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pass = 0;
$fail = 0;
$check = static function (bool $ok, string $label) use (&$pass, &$fail): void {
    $ok ? $pass++ : $fail++;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n";
};
$check(app(\App\Services\CrmStagingInvoiceParity::class) instanceof \App\Services\CrmStagingInvoiceParity, 'Parity query service resolves');
$check(app(\App\Services\CrmFinancialParityBaseline::class) instanceof \App\Services\CrmFinancialParityBaseline, 'Prior source baseline resolves');
$check(app(\App\Services\CrmReportingDataScope::class) instanceof \App\Services\CrmReportingDataScope, 'Reporting scope adapter resolves');
$check(array_key_exists('crm:reporting:staging-parity', \Illuminate\Support\Facades\Artisan::all()), 'New staging Artisan command registered');
$check(is_bool(config('crm_financial_parity.staging_parity_enabled')), 'Configuration flag boolean');
$check(\App\Services\CrmStagingInvoiceParity::decision('production','pgsql',true,true,true,false)['allowed'] === false, 'Production refused even with flag ON');
$check(\App\Services\CrmStagingInvoiceParity::decision('staging','pgsql',false,true,true,false)['allowed'] === false, 'Default OFF refuses query');
$check(\App\Services\CrmStagingInvoiceParity::decision('staging','pgsql',true,true,true,false)['allowed'] === true, 'Explicit staging gate has valid path');
echo 'A7.6.5.5 runtime: ' . $pass . ' PASS, ' . $fail . " FAIL. No CRM records read.\n";
exit($fail ? 1 : 0);
