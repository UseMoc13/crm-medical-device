<?php
/** A7.5.3.2 static checks: no application bootstrap or DB writes. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$files = [
    'app/Services/CrmStatusNotificationAccess.php',
    'app/Services/CrmStatusNotificationPublisher.php',
    'app/Observers/CrmCrossModuleStatusNotificationObserver.php',
    'app/Services/WorkspaceNotificationCenter.php',
    'app/Services/WorkspaceNotificationPreferences.php',
    'app/Models/WorkspaceNotificationPreference.php',
    'app/Providers/AppServiceProvider.php',
    'app/Http/Controllers/InvoiceController.php',
    'app/Http/Controllers/WorkspaceNotificationController.php',
    'resources/views/workspace-notifications/settings.blade.php',
    'database/migrations/2026_11_18_190000_add_crm_status_notification_preference.php',
    'tests/Unit/CrmStatusNotificationSourceTest.php',
    'tests/Feature/CrmStatusNotificationPrivacyTest.php',
];
foreach ($files as $rel) $check("File $rel", is_file("$root/$rel"));
$get = static fn (string $path): string => is_file("$root/$path") ? (string) file_get_contents("$root/$path") : '';
$publisher = $get('app/Services/CrmStatusNotificationPublisher.php');
$access = $get('app/Services/CrmStatusNotificationAccess.php');
$center = $get('app/Services/WorkspaceNotificationCenter.php');
$prefs = $get('app/Services/WorkspaceNotificationPreferences.php');
$provider = $get('app/Providers/AppServiceProvider.php');
$invoice = $get('app/Http/Controllers/InvoiceController.php');
$migration = $get('database/migrations/2026_11_18_190000_add_crm_status_notification_preference.php');
$check('Opt-in disabled until user enables it', str_contains($prefs, "\$defaults['crm_status_enabled'] = false"));
$check('Migration opt-in default false', str_contains($migration, "->default(false)"));
$check('Migration guarded by existing table / column', str_contains($migration, "Schema::hasTable(") && str_contains($migration, 'Schema::hasColumn('));
$check('No send if migration missing', str_contains($publisher, "Schema::hasColumn('workspace_notification_preferences', 'crm_status_enabled')"));
$check('Generic notification text only', !str_contains($publisher, 'customer_name') && !str_contains($publisher, 'email'));
$check('Stakeholders limited to two', str_contains($publisher, 'array_slice(array_values($candidates), 0, 2)'));
$check('Access checked before send', str_contains($publisher, '->canOpen($recipient, $type, $id)'));
$check('CRM route opens require permission', str_contains($access, 'CrmNavigationAccess::class'));
$check('CRM open verifies optional installation scope', str_contains($access, 'CrmDataScope::class'));
$check('Unimplemented sales scopes fail closed', str_contains($access, 'salesScopeEnabled()'));
$check('Delivery/Installation/Invoice routes allowed', str_contains($center, 'deliveries.show') && str_contains($center, 'installations.show') && str_contains($center, 'invoices.show'));
$check('CRM link check invoked on Open', str_contains($center, 'CrmStatusNotificationAccess::class'));
$check('Two observers registered once', substr_count($provider, 'CrmCrossModuleStatusNotificationObserver::class') === 2);
$check('Existing two audit observers retained', substr_count($provider, 'CrmCrossModuleStatusAuditObserver::class') === 2);
$check('Invoice notifier inserted once', substr_count($invoice, 'CrmStatusNotificationPublisher::class') === 1);
$check('Existing invoice audit retained', str_contains($invoice, "'invoice.status_changed'"));
$check('No CRM data migration or reset', !str_contains($migration, 'Schema::dropIfExists') && !str_contains($migration, 'DB::table('));
$check('Notification form has new preference', str_contains($get('resources/views/workspace-notifications/settings.blade.php'), "'crm_status_enabled'"));
$check('Legacy settings save allowed before migration', str_contains($get('app/Http/Controllers/WorkspaceNotificationController.php'), "if (\$field === 'crm_status_enabled'") && str_contains($get('app/Http/Controllers/WorkspaceNotificationController.php'), "Schema::hasColumn('workspace_notification_preferences'"));
$check('Other notification kinds retained', str_contains($prefs, "'assignment' =>") && str_contains($prefs, "'reminder' =>"));
$check('Unit tests do not migrate or seed', !str_contains($get('tests/Feature/CrmStatusNotificationPrivacyTest.php'), 'RefreshDatabase'));
foreach (array_merge($files, ['tools/install_a7532_crm_notifications.php', 'tools/a7532_quick_check.php']) as $rel) {
    if (!str_ends_with($rel, '.php')) continue;
    $path = escapeshellarg("$root/$rel");
    $out=[]; $rc=1;
    exec("php -l $path 2>&1", $out, $rc);
    $check("PHP syntax $rel", $rc === 0);
}
$pass = count(array_filter($checks)); $total = count($checks);
echo "\nA7.5.3.2 static: $pass/$total PASS, ".($total-$pass)." FAIL.\n";
exit($pass === $total ? 0 : 1);
