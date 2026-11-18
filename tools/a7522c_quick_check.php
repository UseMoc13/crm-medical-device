<?php
declare(strict_types=1);
/** Static source checks only. Never boots database. */
$root = dirname(__DIR__);
$files = [
    'app/Services/CrmHistoricalRemediationReview.php',
    'app/Console/Commands/ReviewCrmHistoricalRemediation.php',
    'tests/Unit/CrmHistoricalRemediationReviewTest.php',
    'tests/Feature/CrmHistoricalRemediationReviewCommandTest.php',
    'tools/install_a7522c_remediation_review.php',
];
$passed = 0; $failed = 0;
$check = static function (string $name, bool $result) use (&$passed, &$failed): void {
    $result ? $passed++ : $failed++;
    echo ($result ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
};
foreach ($files as $file) $check('File ' . $file, is_file($root . '/' . $file));
$service = is_file($root . '/' . $files[0]) ? (string) file_get_contents($root . '/' . $files[0]) : '';
$cmd = is_file($root . '/' . $files[1]) ? (string) file_get_contents($root . '/' . $files[1]) : '';
$test = is_file($root . '/' . $files[3]) ? (string) file_get_contents($root . '/' . $files[3]) : '';
$check('Reuses source investigation rules', str_contains($service, '$this->investigation->run('));
$check('All four target rules present', array_reduce([
    'delivery.dispatched_item_missing_warehouse', 'delivery.stockout_quantity_mismatch',
    'installation.unknown_status', 'billing.unknown_status',
], static fn ($ok, $text) => $ok && str_contains($service, $text), true));
$check('No SQL mutations in reviewer', !preg_match('/\-\>(?:insert|update|delete|truncate|upsert|increment|decrement)\s*\(/i', $service)
    && !preg_match('/DB::(?:statement|unprepared|insert|update|delete)\s*\(/i', $service));
$check('Auto apply disabled in review', str_contains($service, "'auto_apply_allowed' => false") && str_contains($service, "'automatic_corrections' => 0"));
$check('Local-only details enforced by service', str_contains($service, "if (\$details && !app()->environment('local'))"));
$check('Local-only details enforced by command', str_contains($cmd, "if (\$details && !app()->environment('local'))"));
$check('No guessed billing state', str_contains($service, "'business_meaning_required'") && str_contains($service, 'Never guess Draft, Sent, or Paid.'));
$check('Installation prerequisites evidence checks', str_contains($service, "'related_delivery_delivered'") && str_contains($service, "'checklist_complete'") && str_contains($service, "'all_delivery_serials_installed'"));
$check('No raw evidence by default', str_contains($service, 'if ($details && $check[\'status\'] === \'flagged\')'));
$check('Correct artisan command', str_contains($cmd, 'crm:integration:remediation-review'));
$check('Machine-readable JSON provided', str_contains($cmd, "option('json')"));
$check('Nonzero exit on skipped or errors', str_contains($cmd, "\$report['summary']['skipped'] || \$report['summary']['errors']"));
$check('No production controller or route modifications', str_contains((string) file_get_contents($root . '/' . $files[4]), "'app/Services/CrmHistoricalRemediationReview.php'") && !str_contains((string) file_get_contents($root . '/' . $files[4]), 'InvoiceController.php'));
$check('Feature test verifies read-only', str_contains($test, 'test_report_is_read_only_at_sql_query_boundary'));
$check('Feature test checks absent schema without hiding errors', str_contains($test, "Schema::hasTable") && str_contains($test, "summary']['errors']"));
foreach ($files as $file) {
    if (!is_file($root . '/' . $file)) continue;
    $output = [];
    $code = 1;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($root . '/' . $file) . ' 2>&1', $output, $code);
    $check('PHP syntax ' . $file, $code === 0);
}
echo "\nA7.5.2.2c static: {$passed}/" . ($passed + $failed) . " PASS, {$failed} FAIL.\n";
exit($failed ? 1 : 0);
