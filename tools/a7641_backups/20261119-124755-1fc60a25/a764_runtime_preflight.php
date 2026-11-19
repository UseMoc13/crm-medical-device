<?php
/** Read-only app bootstrap check; no business queries or database writes. */
declare(strict_types=1);
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$checks=[];
$check=static function (string $label, bool $pass) use (&$checks): void {
    $checks[]=$pass;
    echo '['.($pass?'PASS':'FAIL').'] '.$label.PHP_EOL;
};
$check('Read-only announcement service resolves',app(\App\Services\CrmDashboardAnnouncements::class) instanceof \App\Services\CrmDashboardAnnouncements);
$check('Dashboard read model retained',app(\App\Services\CrmDashboardViewData::class) instanceof \App\Services\CrmDashboardViewData);
$check('Publisher command registered',\Illuminate\Support\Facades\Artisan::has('crm:announcements:publish-due'));
$check('Workspace reminder command retained',\Illuminate\Support\Facades\Artisan::has('workspace:reminders:sync'));
$check('Dashboard route available',\Illuminate\Support\Facades\Route::has('dashboard'));
$check('Reports page route available',\Illuminate\Support\Facades\Route::has('reports.index'));
$check('Reports export route available',\Illuminate\Support\Facades\Route::has('reports.export.invoices'));
$check('Report controller autoloads',class_exists(\App\Http\Controllers\ReportController::class));
$check('Dashboard Blade view available',view()->exists('dashboard.index'));
$check('Announcement model available',class_exists(\App\Models\Announcement::class));
$check('KPI source audit available',\Illuminate\Support\Facades\Artisan::has('crm:reporting:baseline'));
$pass=count(array_filter($checks));$total=count($checks);
echo "A7.6.4 runtime: {$pass}/{$total} PASS, ".($total-$pass)." FAIL. No business queries.".PHP_EOL;
exit($pass===$total?0:1);
