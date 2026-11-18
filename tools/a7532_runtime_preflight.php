<?php
/** Read-only diagnostic after php artisan migrate. */
declare(strict_types=1);
$root = dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ok = [];
$check = static function (string $label, bool $result) use (&$ok): void {
    $ok[] = $result;
    echo ($result ? '[PASS] ' : '[FAIL] ') . $label . "\n";
};
$schema = \Illuminate\Support\Facades\Schema::class;
$check('Workspace notifications table', $schema::hasTable('workspace_notifications'));
$check('Workspace preferences table', $schema::hasTable('workspace_notification_preferences'));
$check('CRM opt-in preference migration applied', $schema::hasTable('workspace_notification_preferences') && $schema::hasColumn('workspace_notification_preferences','crm_status_enabled'));
foreach (['deliveries','installations','invoices','sales_orders','users'] as $table) {
    $check("CRM table $table", $schema::hasTable($table));
}
foreach (['deliveries','installations','invoices','sales_orders'] as $table) {
    $check("Creator attribution on $table", $schema::hasColumn($table, 'created_by'));
}
$check('Notifier resolves', app(\App\Services\CrmStatusNotificationPublisher::class) instanceof \App\Services\CrmStatusNotificationPublisher);
$check('Access guard resolves', app(\App\Services\CrmStatusNotificationAccess::class) instanceof \App\Services\CrmStatusNotificationAccess);
$names = \Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName();
foreach (['deliveries.show','installations.show','invoices.show','workspace-notifications.open','workspace-notifications.settings.update'] as $route) {
    $check("Route $route", isset($names[$route]));
}
$success = count(array_filter($ok)); $total = count($ok);
echo "A7.5.3.2 runtime: $success/$total PASS, ".($total-$success)." FAIL. Read-only.\n";
exit($success===$total ? 0 : 1);
