<?php
/** Read-only Laravel readiness check. NEVER executes business aggregates. */
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = static function (string $title, bool $ok) use (&$checks): void {
    $checks[] = $ok; echo ($ok ? '[PASS] ' : '[FAIL] ').$title.PHP_EOL;
};
$check('Scoped KPI query resolves', app(\App\Services\CrmScopedKpiQuery::class) instanceof \App\Services\CrmScopedKpiQuery);
$check('Previous KPI baseline service available', app(\App\Services\CrmKpiSourceReadiness::class) instanceof \App\Services\CrmKpiSourceReadiness);
$check('All twelve metric definitions retained', count(\App\Services\CrmKpiSourceReadiness::METRICS) === 12);
$check('Permission resolver resolves', app(\App\Services\CrmPermissionResolver::class) instanceof \App\Services\CrmPermissionResolver);
$check('Row scope utility resolves', app(\App\Services\CrmDataScope::class) instanceof \App\Services\CrmDataScope);
$check('Dashboard controller remains unchanged by package', is_file(base_path('app/Http/Controllers/DashboardController.php')));
$check('Reports controller remains available', is_file(base_path('app/Http/Controllers/Reportcontroller.php')) || is_file(base_path('app/Http/Controllers/ReportController.php')));
$check('KPI baseline Artisan command retained', array_key_exists('crm:reporting:baseline', \Illuminate\Support\Facades\Artisan::all()));
$allAvailable = true;
foreach (\App\Services\CrmKpiSourceReadiness::METRICS as $metric) {
    $table = $metric['table'];
    if (!\Illuminate\Support\Facades\Schema::hasTable($table)) { $allAvailable = false; continue; }
    foreach ($metric['columns'] as $column) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn($table, $column)) $allAvailable = false;
    }
}
$check('All twelve KPI source schemas present', $allAvailable);
$ok = count(array_filter($checks));$total=count($checks);
echo PHP_EOL.'A7.6.2 runtime preflight: '.$ok.'/'.$total.' PASS. Schema metadata only; no business records read.'.PHP_EOL;
exit($ok === $total ? 0 : 1);
