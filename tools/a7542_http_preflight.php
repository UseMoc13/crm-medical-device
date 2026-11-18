<?php
/** Read-only HTTP/browser QA preflight. No migrations, transactions or writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$n=0; $errors=0;
$check=static function(string $name,bool $ok)use(&$n,&$errors){
    $n++; if (!$ok) $errors++;
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
};
$tables=['workspace_audit_events','workspace_notifications','workspace_notification_preferences',
    'users','sales_orders','deliveries','delivery_items','stock_movements','serial_numbers',
    'installations','installation_checklists','invoices'];
foreach($tables as $t) $check('Table '.$t,Schema::hasTable($t));
$check('CRM opt-in column', Schema::hasTable('workspace_notification_preferences')
    && Schema::hasColumn('workspace_notification_preferences','crm_status_enabled'));
$routes=[
    'deliveries.show'=>['GET'],
    'deliveries.change-status'=>['PATCH'],
    'installations.show'=>['GET'],
    'installations.change-status'=>['PATCH'],
    'invoices.show'=>['GET'],
    'invoices.status'=>['PATCH'],
    'workspace-notifications.index'=>['GET'],
    'workspace-notifications.open'=>['POST'],
    'workspace-notifications.settings'=>['GET'],
    'workspace-notifications.settings.update'=>['PATCH'],
];
foreach($routes as $name=>$expected){
    $route=Route::getRoutes()->getByName($name);
    $methods=$route? $route->methods() : [];
    $check('Route '.$name.' ['.implode(',',$expected).']', $route!==null && count(array_diff($expected,$methods))===0);
}
foreach(['delivery.status_changed','installation.status_changed','invoice.status_changed'] as $event){
    $check('Audit event '.$event, in_array($event, \App\Services\WorkspaceAuditTrail::EVENTS,true));
}
$check('CRM notification default disabled',
    app(\App\Services\WorkspaceNotificationPreferences::class)->defaults()['crm_status_enabled'] === false);
$check('CRM report command available',array_key_exists('crm:integration:regression',\Illuminate\Support\Facades\Artisan::all()));
echo "\nA7.5.4.2 HTTP readiness: ".($n-$errors)."/$n PASS, $errors FAIL. Read-only.\n";
echo "For actual browser status transitions, use a non-production staging database and two test accounts.\n";
exit($errors ? 1 : 0);
