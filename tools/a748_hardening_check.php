<?php
/** A7.4.8.1 static hardening check, read-only and independent of database. */
declare(strict_types=1);
$root = dirname(__DIR__);
$controllerPath = $root.'/app/Http/Controllers/WorkspaceCollaborationController.php';
$testPath = $root.'/tests/Feature/WorkspaceCollaborationHttpTest.php';
$runtimePath = $root.'/tools/a748_runtime_preflight.php';
$controller = is_file($controllerPath) ? (string) file_get_contents($controllerPath) : '';
$checks = [
    'Patched controller exists' => is_file($controllerPath),
    'Runtime preflight exists under tools/' => is_file($runtimePath),
    'Isolated HTTP test suite exists' => is_file($testPath),
    'Invitation uses transaction and parent lock' => str_contains($controller, 'Lock the parent resource') && str_contains($controller, 'lockForUpdate()'),
    'Invitation rechecks Work after acquiring lock' => str_contains($controller, 'Never allow a stale Work check') && str_contains($controller, "\$locked->visibility === 'work'"),
    'Member revocation is serialized' => str_contains($controller, 'Serialize revocations with invitation') && str_contains($controller, 'whereKey($member->getKey())->firstOrFail()'),
    'Progress is reauthorized inside transaction' => str_contains($controller, 'The parent lock is also acquired by member add/remove') && str_contains($controller, '$this->acl()->assertCanWrite($locked)'),
    'Personal TO-DO disallows collaboration entries' => str_contains($controller, "abort_unless(\$resource->visibility === 'work', 404)"),
    'Existing submission controller still installed' => is_file($root.'/app/Http/Controllers/WorkspaceTaskSubmissionController.php'),
    'Original UI panel still present' => is_file($root.'/resources/views/workspace-collaboration/_panel.blade.php'),
];
$passed = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ').$name.PHP_EOL;
    $passed += (int)$ok;
}
echo "\nA7.4.8.1 hardening static check: {$passed}/".count($checks)." PASS\n";
echo "Note: static checks are not HTTP/database/browser verification.\n";
exit($passed === count($checks) ? 0 : 1);
