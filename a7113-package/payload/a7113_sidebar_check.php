<?php

declare(strict_types=1);

// Read-only structural regression: verifies all role-gated module headers
// depend on the same route permissions as their respective links.
$root = dirname(__DIR__);
$path = $root . '/resources/views/components/sidebar.blade.php';
if (!is_file($path)) {
    fwrite(STDERR, "[FAIL] Missing sidebar.blade.php\n");
    exit(1);
}
$source = file_get_contents($path);
if ($source === false) {
    fwrite(STDERR, "[FAIL] Cannot read sidebar.blade.php\n");
    exit(1);
}
$groups = [
    'Customer & Lead' => ['customers.index', 'contacts.index', 'leads.index'],
    'Sales CRM' => ['opportunities.index', 'quotations.index', 'sales-orders.index', 'contracts.index'],
    'Product & Inventory' => ['products.index', 'product-categories.index', 'brands.index', 'warehouses.index', 'inventories.index', 'stock-movements.index', 'serial-numbers.index'],
    'Installation & Warranty' => ['deliveries.index', 'installations.index', 'warranties.index'],
    'Service & Maintenance' => ['service-tickets.index', 'maintenance.index', 'service-history.index'],
    'Billing & Reporting' => ['invoices.index', 'reports.index'],
    'Administration' => ['announcement-management.index', 'users.index', 'roles.index', 'organization.index', 'teams.index', 'branches.index'],
];
$failures = 0;
foreach ($groups as $title => $routes) {
    $expression = preg_quote($title, '/');
    $pattern = '/@if\s*\((.*?)\)\s*<div class="nav-group">\s*' . $expression . '\s*<\/div>\s*@endif/s';
    $hits = preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);
    if ($hits !== 1) {
        fwrite(STDERR, "[FAIL] Missing or ambiguous conditional group: {$title}\n");
        ++$failures;
        continue;
    }
    $condition = $matches[0][1];
    foreach ($routes as $route) {
        $needle = "CrmViewAction::allows('{$route}')";
        if (substr_count($condition, $needle) !== 1) {
            fwrite(STDERR, "[FAIL] {$title}: expected permission gate {$route}\n");
            ++$failures;
        }
    }
    if ($title === 'Administration' && !str_contains($condition, "WorkspaceAuditAccess::class)->canView(auth('web')->user())")) {
        fwrite(STDERR, "[FAIL] Administration: audit permission gate absent\n");
        ++$failures;
    }
    if ($failures === 0) {
        echo "[PASS] {$title}: heading gated by authorized entries\n";
    }
}
if ($failures !== 0) {
    echo "[RESULT] A7113_SIDEBAR_GROUP_CHECK_FAIL findings={$failures}\n";
    exit(1);
}
echo '[RESULT] A7113_SIDEBAR_GROUP_CHECK_PASS groups=' . count($groups) . " (read-only source test)\n";
