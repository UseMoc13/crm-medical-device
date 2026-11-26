<?php
/** Static source/installer smoke check. No DB operations. */
declare(strict_types=1);
$root = dirname(__DIR__);
$manifest = json_decode((string) file_get_contents(__DIR__ . '/a797_patches.json'), true, 512, JSON_THROW_ON_ERROR);
$checks = 0;
foreach (['config/crm_assignment.php', 'app/Services/CrmUserAssignmentEligibility.php', 'tests/Unit/CrmUserAssignmentEligibilityConfigTest.php'] as $path) {
    if (!is_file($root . '/' . $path)) { fwrite(STDERR, "[FAIL] Missing $path\n"); exit(1); }
    echo "[PASS] $path\n"; ++$checks;
}
foreach ($manifest['patches'] as $entry) {
    $source = @file_get_contents($root . '/' . $entry['path']);
    $found = is_string($source) ? substr_count($source, 'A797:' . $entry['name']) : -1;
    if ($found !== (int) $entry['count']) {
        fwrite(STDERR, "[FAIL] {$entry['path']} :: {$entry['name']} marker $found expected {$entry['count']}\n");
        exit(1);
    }
    ++$checks;
}
echo "[RESULT] A797_ASSIGNMENT_SOURCE_READY checks=$checks (HTTP and PostgreSQL not executed)\n";
