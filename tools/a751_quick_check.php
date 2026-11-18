<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS]' : '[FAIL]') . ' ' . $label . PHP_EOL;
};
$servicePath = $root.'/app/Services/CrmIntegrationAudit.php';
$commandPath = $root.'/app/Console/Commands/AuditCrmIntegration.php';
$unitPath = $root.'/tests/Unit/CrmIntegrationAuditCatalogTest.php';
$featurePath = $root.'/tests/Feature/CrmIntegrationAuditCommandTest.php';
foreach ([$servicePath,$commandPath,$unitPath,$featurePath] as $path) {
    $check('File '.str_replace($root.'/', '', $path), is_file($path));
}
if (!is_file($servicePath) || !is_file($commandPath)) exit(1);
$service = file_get_contents($servicePath);
$command = file_get_contents($commandPath);
$check('All scan rules use DB read builders', str_contains($service, 'DB::table(') && str_contains($service, 'DB::query()'));
$check('No audit INSERT/UPDATE/DELETE/TRUNCATE', !preg_match('/(?:->|::)(?:insert|update|delete|truncate|upsert|statement|unprepared|insertOrIgnore)\s*\(/i', $service));
$check('No schema modification commands', !preg_match('/Schema::(?:create|table|drop|rename)\s*\(/i', $service));
$check('Schema-guarded missing tables', str_contains($service,'Schema::hasTable') && str_contains($service,'Schema::hasColumn'));
$check('Grouped counts use subquery wrapper', str_contains($service,'fromSub($query'));
$check('No personal data fields in diagnostic output', !preg_match('/\b(customer_name|user_email|serial_number\s+as\s+entity_id|password|note_body)\b/i', $service));
$check('Fatal query errors separated from skipped checks', str_contains($service,"'status' = 'error'") || str_contains($service,"$"."record['status'] = 'error'"));
$check('Explicit severity labels', str_contains($service,"'critical'") && str_contains($service,"'review'"));
$check('No raw IDs unless --samples', str_contains($service,'if ($includeSamples)'));
$check('Command exposes read-only audit signature', str_contains($command,'crm:integration:audit'));
$check('JSON format available', str_contains($command,'{--json'));
$check('Strict mode available', str_contains($command,'{--strict'));
$check('Sample IDs local only', str_contains($command,"environment('local')"));
$check('No database reset or migration from installer', !preg_match('/migrate:fresh|migrate:reset|db:wipe|DROP TABLE/i', file_get_contents(__DIR__.'/install_a751_integration_audit.php')));
$check('Business records not modified by CLI', !preg_match('/DB::(?:insert|update|delete|statement)\s*\(/', $command));
$check('Coverage includes Sales, Delivery, Inventory, Installation',
    str_contains($service, 'sales.order_missing_quotation') && str_contains($service, 'delivery.dispatched_missing_stockout')
    && str_contains($service,'inventory.negative_quantity') && str_contains($service,'installation.completed_no_checklist'));
$check('Coverage includes Warranty, Service, Billing',
    str_contains($service, 'warranty.customer_mismatch') && str_contains($service,'service.customer_serial_mismatch')
    && str_contains($service,'billing.order_missing'));
if (is_file($servicePath)) {
    require_once $servicePath;
    $rules = (new \App\Services\CrmIntegrationAudit())->definitions();
    $check('At least 30 audit checks', count($rules) >= 30);
    $check('Rule IDs are unique', count(array_unique(array_column($rules,'id'))) === count($rules));
}
$badSyntax = [];
foreach ([$servicePath,$commandPath,$unitPath,$featurePath,
    __DIR__.'/install_a751_integration_audit.php', __FILE__] as $path) {
    if (!is_file($path)) { $badSyntax[] = $path; continue; }
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $out, $code);
    if ($code !== 0) $badSyntax[] = str_replace($root.'/', '', $path);
    unset($out);
}
$check('PHP syntax for installer and payload', !$badSyntax);
$pass = count(array_filter($checks));
echo "\nA7.5.1 static: {$pass}/".count($checks)." PASS".($pass===count($checks)?', 0 FAIL':', '.(count($checks)-$pass).' FAIL').".\n";
exit($pass===count($checks)?0:1);
