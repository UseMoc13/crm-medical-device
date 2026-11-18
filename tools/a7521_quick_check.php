<?php
/** Static verification of the additive A7.5.2.1 investigative tools. */
declare(strict_types=1);
$root = dirname(__DIR__);
$passed = 0;
$failed = 0;
$check = static function (string $label, bool $condition) use (&$passed, &$failed): void {
    $condition ? $passed++ : $failed++;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$files = [
    'app/Services/CrmIntegrationInvestigation.php',
    'app/Console/Commands/InspectCrmIntegration.php',
    'tests/Unit/CrmIntegrationInvestigationSourceTest.php',
    'tests/Feature/CrmIntegrationInvestigationCommandTest.php',
];
foreach ($files as $path) $check('Installed ' . $path, is_file($root . '/' . $path));
$service = file_get_contents($root . '/app/Services/CrmIntegrationInvestigation.php') ?: '';
$command = file_get_contents($root . '/app/Console/Commands/InspectCrmIntegration.php') ?: '';
$check('Uses A7.5.1 rule definitions', str_contains($service, '$this->audit->definitions()'));
$check('Investigates both Delivery findings', str_contains($service, "'delivery.dispatched_item_missing_warehouse'") && str_contains($service, "'delivery.stockout_quantity_mismatch'"));
$check('Investigates Installation and Billing statuses', str_contains($service, "'installation.unknown_status'") && str_contains($service, "'billing.unknown_status'"));
$check('Cross-checks shared Delivery records', str_contains($service, 'overlapping_delivery_count'));
$check('Missing schema is not treated as clean', str_contains($service, "'skipped'"));
$check('Error categories do not expose SQL', str_contains($service, "'error_type' => class_basename(\$e)"));
$check('No mutations in diagnostic service', !preg_match('/->(?:insert|update|delete|save|truncate|increment|decrement)\s*\(|DB::(?:statement|unprepared)\s*\(/', $service));
$check('Optional details require local environment', str_contains($command, "if (\$details && !app()->environment('local'))"));
$check('Details are opt-in', str_contains($command, '{--details :'));
$check('Safe JSON output supported', str_contains($command, '{--json :'));
$check('No existing production files are overwritten', str_contains(file_get_contents(__DIR__ . '/install_a7521_investigation.php') ?: '', "fopen(\$target, 'x')"));
$check('No migration or schema mutation scripts', !is_dir(__DIR__ . '/a7521_payload/database'));
$check('No raw customer names or emails in diagnostics', !str_contains($service, 'customer_name') && !str_contains($service, 'email'));
foreach ($files as $path) {
    $file = $root . '/' . $path;
    if (is_file($file)) {
        $out = [];
        $status = 1;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $out, $status);
        $check('PHP syntax: ' . basename($file), $status === 0);
    }
}
echo "\nA7.5.2.1 static: {$passed}/" . ($passed + $failed) . ' PASS, ' . $failed . ' FAIL.' . PHP_EOL;
exit($failed === 0 ? 0 : 1);
