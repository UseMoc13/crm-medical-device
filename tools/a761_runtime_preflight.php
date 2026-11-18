<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = function (string $title, bool $pass) use (&$checks): void {
    $checks[] = $pass;
    echo ($pass ? '[PASS] ' : '[FAIL] ').$title.PHP_EOL;
};
$all = \Illuminate\Support\Facades\Artisan::all();
$check('KPI baseline command registered', array_key_exists('crm:reporting:baseline', $all));
$check('KPI readiness service resolves', app(\App\Services\CrmKpiSourceReadiness::class) instanceof \App\Services\CrmKpiSourceReadiness);
$check('At least 10 source contracts', count(\App\Services\CrmKpiSourceReadiness::METRICS) >= 10);
$check('Dashboard source readable', is_readable(base_path('app/Http/Controllers/DashboardController.php')));
$check('Report source readable', is_readable(base_path('app/Http/Controllers/Reportcontroller.php')) || is_readable(base_path('app/Http/Controllers/ReportController.php')));
$check('Dashboard view source readable', is_readable(base_path('resources/views/dashboard/index.blade.php')));
$check('Report view source readable', is_readable(base_path('resources/views/reports/index.blade.php')));
$total = count($checks); $ok = count(array_filter($checks));
echo PHP_EOL.'A7.6.1 runtime: '.$ok.'/'.$total.' PASS. No business records read.'.PHP_EOL;
exit($total === $ok ? 0 : 1);
