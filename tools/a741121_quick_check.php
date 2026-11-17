<?php
$root = dirname(__DIR__);
$file = $root . '/tests/Feature/WorkspaceNotificationPreferencesHttpTest.php';
if (!is_file($file)) {
    fwrite(STDERR, "[FAIL] Notification preferences feature test is missing.\n");
    exit(1);
}
$src = file_get_contents($file);
$checks = [
    'Order-independent private directive' => str_contains($src, "assertContains('private', \$cacheDirectives)"),
    'Order-independent no-store directive' => str_contains($src, "assertContains('no-store', \$cacheDirectives)"),
    'Reject public caching' => str_contains($src, "assertNotContains('public', \$cacheDirectives)"),
    'Actual Cache-Control header inspected' => str_contains($src, "headers->get('Cache-Control')"),
    'Old brittle header assertion removed' => !str_contains($src, "assertHeader('cache-control', 'private, no-store')"),
    'Owner and worker polling tests retained' => str_contains($src, 'test_poll_does_not_expose_other_users_notifications') && str_contains($src, 'worker-only'),
    'Reminder preference tests retained' => str_contains($src, 'test_reminder_lead_time_respects_each_user_and_deduplicates'),
];
$passed = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . "\n";
    if ($ok) $passed++;
}
echo "\nA7.4.11.2.1 static: $passed/" . count($checks) . " PASS.\n";
exit($passed === count($checks) ? 0 : 1);
