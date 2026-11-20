<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$pass = 0;
$fail = 0;
function check(bool $condition, string $name): void {
    global $pass, $fail;
    echo ($condition ? '[PASS] ' : '[FAIL] ') . $name . "\n";
    $condition ? $pass++ : $fail++;
}
$files = [
    'app/Support/CrmPrivateFilePath.php',
    'app/Http/Controllers/WorkspaceTaskSubmissionController.php',
    'app/Http/Controllers/ContractController.php',
    'tests/Unit/CrmPrivateFilePathTest.php',
    'tests/Unit/CrmPrivateFileAccessSourceTest.php',
    'tools/a7742_apply_private_file_patch.php',
];
foreach ($files as $relative) {
    $path = $root . '/' . $relative;
    check(is_file($path), 'Exists ' . $relative);
    if (!is_file($path)) continue;
    $cmd = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($path) . ' 2>&1';
    exec($cmd, $lines, $exit);
    check($exit === 0, 'PHP syntax ' . $relative);
    unset($lines, $exit);
}
$task = (string) @file_get_contents($root.'/app/Http/Controllers/WorkspaceTaskSubmissionController.php');
$contract = (string) @file_get_contents($root.'/app/Http/Controllers/ContractController.php');
$helper = (string) @file_get_contents($root.'/app/Support/CrmPrivateFilePath.php');
check(str_contains($task, 'CrmPrivateFilePath::submission('), 'Task file scope is checked');
check(substr_count($task, 'CrmPrivateFilePath::resolve(') >= 2, 'Direct + ZIP downloads resolve within task scope');
check(str_contains($task, 'if ($missing > 0)'), 'ZIP fail-closed on missing or invalid files');
check(str_contains($task, '$this->scopedFile($todo,$file)') && str_contains($task, '$this->owner($todo)'), 'Existing task authorization retained');
check(str_contains($task, '$document->delete();') && str_contains($task, 'Do not delete arbitrary private disk paths'), 'Task file delete validates stored path');
check(str_contains($contract, 'CrmPrivateFilePath::contract($storedPath)'), 'Contract path validated');
check(str_contains($contract, "CrmPrivateFilePath::resolve(\$disk->path(''), \$storedPath, 'contracts')"), 'Contract download restricted to contracts folder');
check(str_contains($contract, "'Cache-Control' => 'private, no-store'"), 'Contract response no-store');
check(str_contains($helper, 'realpath(') && str_contains($helper, 'is_file('), 'Physical path and file validated');
check(!preg_match('/\-\>(?:insert|update|delete|truncate|upsert)\s*\(|DB::(?:insert|update|delete|statement|unprepared)\s*\(/', $helper), 'Path helper contains no DB writes');
echo "A7.7.4.2 static: {$pass}/" . ($pass + $fail) . " PASS; {$fail} FAIL. No DB used.\n";
exit($fail ? 1 : 0);
