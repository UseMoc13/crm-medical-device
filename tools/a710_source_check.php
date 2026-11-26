<?php

declare(strict_types=1);

/** Source checks only. Does not bootstrap Laravel or read PostgreSQL. */
$root = dirname(__DIR__);
$contracts = [
    'app/Support/CrmFinalRouteSecurityCatalog.php' => ['criticalRoutes', 'service-tickets.work-logs.store', 'organization.users.manager', 'crm_page.service_tickets.create'],
    'tools/a710_route_security_gate.php' => ['A710_ROUTE_SECURITY_CONTRACT_READY', 'gatherMiddleware', 'CrmRoleAccessCatalog', 'getMiddlewareGroups'],
    'tools/a710_rbac_db_preflight.php' => ['SET TRANSACTION READ ONLY', 'crm_medical_device', 'users_primary_role_mismatch', 'rollBack'],
    'tools/a710_run_checks.ps1' => ['DB_DATABASE', ":memory:", 'a799_integration_gate.php', 'A710_AUTOMATED_BASELINE_READY'],
    'tests/Unit/CrmFinalRouteSecurityCatalogTest.php' => ['test_sensitive_routes_preserve_exact_permissions', 'test_work_log_still_requires_service_page_permission'],
    'docs/A710_START_HERE.md' => ['A7.10.1', 'A7.10.2', 'HOLD', 'UAT-15'],
];
$checks = 0;
foreach ($contracts as $path => $needles) {
    $text = @file_get_contents($root.'/'.$path);
    if ($text === false) { fwrite(STDERR, '[FAIL] Missing '.$path.PHP_EOL); exit(1); }
    foreach ($needles as $needle) {
        if (!str_contains($text, $needle)) { fwrite(STDERR, '[FAIL] '.$path.' lacks '.$needle.PHP_EOL); exit(1); }
        $checks++;
    }
    if (str_ends_with($path, '.php')) {
        $output = []; $code = 0;
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1', $output, $code);
        if ($code !== 0) { fwrite(STDERR, '[FAIL] Invalid PHP syntax '.$path.PHP_EOL); exit(1); }
        $checks++;
    }
    echo '[PASS] '.$path.PHP_EOL;
}
echo '[RESULT] A710_SOURCE_READY checks='.$checks.' (Laravel, tests and PostgreSQL NOT executed)'.PHP_EOL;
