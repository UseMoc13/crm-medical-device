<?php
/**
 * A7.5.5 - Direct controller patch (no installer, no Laravel bootstrap, no DB access).
 * Safe for PHP 8.2+ invoked from VS Code PowerShell on Windows 11.
 *
 * php tools/a755_apply_dispatch_fix.php --dry-run
 * php tools/a755_apply_dispatch_fix.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$targetRelative = 'app/Http/Controllers/DeliveryController.php';
$target = $root . '/app/Http/Controllers/DeliveryController.php';
$guard = $root . '/app/Services/CrmDeliveryCompletionGuard.php';
$dryRun = in_array('--dry-run', $argv, true);

function stopA755(string $message): never
{
    fwrite(STDERR, '[STOP] ' . $message . PHP_EOL);
    exit(1);
}

foreach (array_slice($argv, 1) as $argument) {
    if ($argument !== '--dry-run') {
        stopA755('Unsupported argument: ' . $argument . '. Use --dry-run or no option.');
    }
}
if (!is_file($root . '/artisan') || !is_file($target) || !is_file($guard)) {
    stopA755('Copy the tools files into the Laravel project root; required CRM files are missing.');
}

$gitOutput = [];
$gitCode = 1;
exec('git -C ' . escapeshellarg($root) . ' branch --show-current 2>&1', $gitOutput, $gitCode);
$branch = trim(implode("\n", $gitOutput));
if ($gitCode !== 0 || $branch !== 'Big-Changes') {
    stopA755('Expected Git branch Big-Changes. Current: ' . ($branch !== '' ? $branch : 'unknown'));
}

$original = file_get_contents($target);
if (!is_string($original)) stopA755('Unable to read DeliveryController.php.');
$originalHash = hash('sha256', $original);
$crlf = str_contains($original, "\r\n");
$source = str_replace("\r\n", "\n", $original);

$oldBlock = <<<'BLOCK'
            if ($validated['status'] === 'in_transit') {
                $this->dispatchAllocatedUnits($locked);
            }
BLOCK;
$newBlock = <<<'BLOCK'
            if ($validated['status'] === 'in_transit') {
                $this->dispatchAllocatedUnits($locked);
                // A7.5.5: fail closed before committing any stock-out or in_transit state.
                // Throws ValidationException => entire DB::transaction rolls back.
                app(\App\Services\CrmDeliveryCompletionGuard::class)->assertCanComplete($locked);
            }
BLOCK;

$start = strpos($source, 'public function changeStatus(Request $request, Delivery $delivery)');
$end = $start === false ? false : strpos($source, 'private function dispatchAllocatedUnits(', $start);
if ($start === false || $end === false || $end <= $start) {
    stopA755('Expected Delivery changeStatus/dispatch methods not found. Nothing changed.');
}
$method = substr($source, $start, $end - $start);
if (!str_contains($method, 'DB::transaction(function () use ($delivery, $validated)')
    || !str_contains($method, "if (\$validated['status'] === 'delivered')")
    || !str_contains($method, '$locked->save();')) {
    stopA755('Current controller transition structure differs. Nothing changed.');
}
$oldCount = substr_count($method, $oldBlock);
$newCount = substr_count($method, $newBlock);
$guardCount = substr_count($method, '->assertCanComplete($locked);');

if ($newCount === 1 && $oldCount === 0 && $guardCount === 2) {
    echo '[PASS] Already patched. No files changed.' . PHP_EOL;
    exit(0);
}
if ($oldCount !== 1 || $newCount !== 0 || $guardCount !== 1) {
    stopA755('Unexpected dispatch block or guard count. Nothing changed.');
}
if (strpos($method, $oldBlock) > strpos($method, '$locked->save();')) {
    stopA755('Dispatch must occur before Delivery status is saved. Nothing changed.');
}

$updatedMethod = str_replace($oldBlock, $newBlock, $method);
$updated = substr($source, 0, $start) . $updatedMethod . substr($source, $end);
if (substr_count($updatedMethod, '->assertCanComplete($locked);') !== 2
    || strpos($updatedMethod, $newBlock) === false
    || strpos($updatedMethod, '// A7.5.5: fail closed') >= strpos($updatedMethod, '$locked->save();')) {
    stopA755('Postcondition verification failed. Nothing changed.');
}
if ($crlf) $updated = str_replace("\n", "\r\n", $updated);

// Ensure that the only change is the intended three-line insertion.
$expect = str_replace($oldBlock, $newBlock, $source);
if (str_replace("\r\n", "\n", $updated) !== $expect) {
    stopA755('Patch would change unrelated source content. Nothing changed.');
}

// Verify PHP syntax before any write; use a temporary file, not Laravel/DB.
$staged = tempnam(sys_get_temp_dir(), 'a755_php_');
if ($staged === false) stopA755('Could not create a temporary PHP syntax-check file.');
try {
    if (file_put_contents($staged, $updated, LOCK_EX) === false) {
        stopA755('Unable to stage candidate PHP source.');
    }
    $lint = [];
    $lintCode = 1;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($staged) . ' 2>&1', $lint, $lintCode);
    if ($lintCode !== 0) stopA755('Proposed DeliveryController PHP syntax invalid: ' . implode(' ', $lint));
} finally {
    @unlink($staged);
}

echo '[CHECK] Branch Big-Changes; source anchor and delivered guard validated.' . PHP_EOL;
echo '[CHECK] PHP syntax passed; only three postcondition lines will be inserted.' . PHP_EOL;
if ($dryRun) {
    echo '[PASS] Dry run completed. No application files or database records changed.' . PHP_EOL;
    exit(0);
}

// Do not overwrite other local modifications; current user's previous log showed clean controller.
$diffLines = [];
$diffCode = 1;
exec('git -C ' . escapeshellarg($root) . ' diff --quiet -- ' . escapeshellarg($targetRelative) . ' 2>&1', $diffLines, $diffCode);
if ($diffCode !== 0) {
    stopA755('DeliveryController.php contains uncommitted tracked changes. Review them before patching.');
}
if (hash_file('sha256', $target) !== $originalHash) {
    stopA755('Controller changed during validation. Refusing to overwrite.');
}

$backupDir = $root . '/tools/a755_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
if (!mkdir($backupDir, 0775, true) && !is_dir($backupDir)) {
    stopA755('Could not create backup directory.');
}
$backup = $backupDir . '/DeliveryController.php';
if (!copy($target, $backup) || hash_file('sha256', $backup) !== $originalHash) {
    stopA755('Backup failed or checksum mismatch. Controller was not changed.');
}

// The former .ps1 used File.Replace(..., null) and failed on Windows.
// PHP copy() avoids that API. Verify and restore the backup if a write fails.
try {
    if (file_put_contents($target, $updated, LOCK_EX) !== strlen($updated)) {
        throw new RuntimeException('Controller write incomplete.');
    }
    if (hash_file('sha256', $target) !== hash('sha256', $updated)) {
        throw new RuntimeException('Controller checksum differs from proposed patch.');
    }
    $lint = [];
    $lintCode = 1;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($target) . ' 2>&1', $lint, $lintCode);
    if ($lintCode !== 0) throw new RuntimeException('Final PHP lint failed.');
} catch (Throwable $e) {
    if (!copy($backup, $target) || hash_file('sha256', $target) !== $originalHash) {
        stopA755('Patch failed and automatic rollback could not be verified. Restore from ' . $backup);
    }
    stopA755('Patch failed, backup restored: ' . $e->getMessage());
}

echo '[PASS] A7.5.5 successfully patched DeliveryController.php.' . PHP_EOL;
echo '[BACKUP] ' . $backup . PHP_EOL;
echo '[SAFE] No database read/write, no migrations, no permission changes.' . PHP_EOL;
echo '[NEXT] php tools/a755_dispatch_quick_check.php' . PHP_EOL;
echo '[NEXT] php artisan test --filter=CrmDeliveryDispatchPostcondition' . PHP_EOL;
