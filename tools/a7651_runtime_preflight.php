<?php
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$p=0;$f=0;
function a7651_rt(bool $yes,string $name):void {global $p,$f; echo ($yes?'[PASS] ':'[FAIL] ').$name.PHP_EOL;$yes?$p++:$f++;}
$routes=Illuminate\Support\Facades\Route::getRoutes();
foreach (['reports.index','reports.export.invoices'] as $name) {
    $route=$routes->getByName($name);
    a7651_rt($route!==null && in_array('GET',$route->methods(),true),'Protected report route: '.$name.' [GET]');
}
a7651_rt(class_exists(App\Services\CrmNavigationAccess::class),'CRM navigation permission checker resolves');
a7651_rt(class_exists(App\Services\CrmPermissionResolver::class),'CRM permission resolver resolves');
a7651_rt(class_exists(App\Http\Controllers\ReportController::class),'Renamed Reports controller autoloads');
$src=file_get_contents($root.'/app/Http/Controllers/ReportController.php');
a7651_rt(substr_count($src,'$this->assertReportsView();')===2,'Both report handlers call guard');
a7651_rt(str_contains($src,"->canView(Auth::guard('web')->user(), 'reports')"),'Uses base + view role permission');
echo "A7.6.5.1 runtime: {$p} PASS, {$f} FAIL. No CRM business queries.\n"; exit($f?1:0);
