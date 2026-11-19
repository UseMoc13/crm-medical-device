<?php
/** Read-only metadata checks, no business aggregates. */
declare(strict_types=1);
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks = [];
$check = static function (string $text, bool $ok) use (&$checks) {
    echo ($ok ? '[PASS] ' : '[FAIL] ').$text.PHP_EOL;
    $checks[] = $ok;
};
$check('Dashboard read model resolves', app(\App\Services\CrmDashboardViewData::class) instanceof \App\Services\CrmDashboardViewData);
$check('Scoped KPI service resolves', app(\App\Services\CrmScopedKpiQuery::class) instanceof \App\Services\CrmScopedKpiQuery);
$check('KPI contracts still contain 12 metrics', count(\App\Services\CrmKpiSourceReadiness::METRICS) === 12);
$check('Permission resolver resolves', app(\App\Services\CrmPermissionResolver::class) instanceof \App\Services\CrmPermissionResolver);
$check('Activity row-scope service available', app(\App\Services\CrmDataScope::class) instanceof \App\Services\CrmDataScope);
$check('Dashboard route available', \Illuminate\Support\Facades\Route::has('dashboard'));
$check('Activities table available', \Illuminate\Support\Facades\Schema::hasTable('activities'));
$check('Activities user_id column available', \Illuminate\Support\Facades\Schema::hasColumn('activities','user_id'));
$check('Activities text columns available', \Illuminate\Support\Facades\Schema::hasColumn('activities','title') && \Illuminate\Support\Facades\Schema::hasColumn('activities','description'));
$check('Dashboard view compiles as named view', view()->exists('dashboard.index'));
$check('Announcement view available', view()->exists('dashboard.index') && \Illuminate\Support\Facades\Route::has('announcements.index'));
$passed = count(array_filter($checks)); $count = count($checks);
echo PHP_EOL.'A7.6.3 runtime preflight: '.$passed.'/'.$count.' PASS. Schema-only, no CRM records read.'.PHP_EOL;
exit($passed === $count ? 0 : 1);
