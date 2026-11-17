<?php
/** A7.4.8.2 read-only sanity check; run from any working directory. */
$root = dirname(__DIR__);
$controller = $root . '/app/Http/Controllers/WorkspaceCollaborationController.php';
$test = $root . '/tests/Feature/WorkspaceCollaborationHttpTest.php';
$checks = [
    'Collaboration controller exists' => is_file($controller),
    'Collaboration HTTP test exists' => is_file($test),
];
$code = is_file($controller) ? file_get_contents($controller) : '';
$testCode = is_file($test) ? file_get_contents($test) : '';
$checks['Recipient selection is validated'] = str_contains($code, "assertAssignable(Auth::guard('web')->user(), [\$data['user_id']])");
$checks['Recipient validation error maps to user_id'] = str_contains($code, "'user_id' => \$errors['assignee_ids']");
$checks['Other validation exceptions rethrown'] = str_contains($code, "if (!array_key_exists('assignee_ids', \$errors))");
$checks['Out-of-branch regression test present'] = str_contains($testCode, 'test_only_creator_can_grant_permissions_and_out_of_branch_invites_fail');
$passed = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $name . PHP_EOL;
    $passed += $ok ? 1 : 0;
}
echo sprintf("A7.4.8.2 check: %d/%d PASS\n", $passed, count($checks));
exit($passed === count($checks) ? 0 : 1);
