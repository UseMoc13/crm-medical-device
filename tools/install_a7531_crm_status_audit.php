<?php
/** A7.5.3.1: conservative, idempotent integration with existing audit ledger. */
declare(strict_types=1);

$root = dirname(__DIR__);
$dryRun = in_array('--dry-run', $argv, true);
foreach (array_slice($argv, 1) as $arg) {
    if ($arg !== '--dry-run') {
        fwrite(STDERR, "Unknown argument: {$arg}\n");
        exit(2);
    }
}

/** @return array{0:?string,1:?string} */
function a7531_patch_trail(string $source): array
{
    $events = "'delivery.status_changed', 'installation.status_changed', 'invoice.status_changed'";
    $eventAnchor = "'collaboration.role_changed', 'todo.schedule_changed',";
    $oldSubjects = "'notification_preferences', 'role'];";
    $newSubjects = "'notification_preferences', 'role', 'delivery', 'installation', 'invoice'];";
    if (str_contains($source, "'delivery.status_changed'") ||
        str_contains($source, "'installation.status_changed'") ||
        str_contains($source, "'invoice.status_changed'")) {
        if (!str_contains($source, "'installation.status_changed'") ||
            !str_contains($source, "'invoice.status_changed'") ||
            !str_contains($source, $newSubjects) ||
            substr_count($source, "'installation.status_changed'") !== 1 ||
            substr_count($source, "'invoice.status_changed'") !== 1 ||
            substr_count($source, "'delivery.status_changed'") !== 1) {
            return [null, 'Partial or modified audit allowlist'];
        }
        return [$source, null];
    }
    if (substr_count($source, $eventAnchor) !== 1 ||
        substr_count($source, $oldSubjects) !== 1 ||
        !str_contains($source, "'status_from'") ||
        !str_contains($source, "'status_to'")) {
        return [null, 'AuditTrail baseline A7.4.12.2 not recognized'];
    }
    $source = str_replace($eventAnchor, $eventAnchor . "\n        {$events},", $source);
    $source = str_replace($oldSubjects, $newSubjects, $source);
    return [$source, null];
}

function a7531_patch_provider(string $source): array
{
    $name = 'CrmCrossModuleStatusAuditObserver::class';
    $anchor = '\\App\\Models\\WorkspaceTask::observe(\\App\\Observers\\WorkspaceAuditCoverageObserver::class);';
    $addition = '        \\App\\Models\\Delivery::observe(\\App\\Observers\\CrmCrossModuleStatusAuditObserver::class);' . "\n" .
        '        \\App\\Models\\Installation::observe(\\App\\Observers\\CrmCrossModuleStatusAuditObserver::class);';
    if (str_contains($source, 'CrmCrossModuleStatusAuditObserver')) {
        if (substr_count($source, $name) !== 2 || !str_contains($source, $addition)) {
            return [null, 'Partial or modified observer registration'];
        }
        return [$source, null];
    }
    if (substr_count($source, $anchor) !== 1) {
        return [null, 'AppServiceProvider baseline not recognized'];
    }
    return [str_replace($anchor, $anchor . "\n        // A7.5.3.1: append-only CRM status audit for Eloquent transitions.\n" . $addition, $source), null];
}

function a7531_patch_invoice(string $source): array
{
    $from = strpos($source, 'public function updateStatus(');
    $to = $from === false ? false : strpos($source, 'public function destroy(', $from);
    if ($from === false || $to === false || $to <= $from) {
        return [null, 'Invoice status method not identified'];
    }
    $method = substr($source, $from, $to - $from);
    $signature = "'invoice.status_changed'";
    if (str_contains($method, 'WorkspaceAuditTrail::class') ||
        str_contains($method, 'invoice.status_changed')) {
        if (substr_count($method, $signature) !== 1 ||
            !str_contains($method, 'CrmInvoiceRowGuard::class') ||
            !str_contains($method, "['status_from' => \$current, 'status_to' => \$target]") ||
            !str_contains($method, 'DB::transaction(') ||
            !str_contains($method, 'CrmInvoiceRowGuard::class') ||
            strpos($method, $signature) <= strpos($method, '->update([') ||
            strpos($method, $signature) >= strpos($method, 'return null;')) {
            return [null, 'Partial or modified Invoice audit insertion'];
        }
        return [$source, null];
    }
    if (!str_contains($method, 'CrmInvoiceRowGuard::class') ||
        substr_count($method, 'DB::transaction(') !== 1 ||
        substr_count($method, 'return null;') !== 1 ||
        substr_count($method, '->update([') !== 1 ||
        !str_contains($method, '->where(\'invoice_id\', $existing->invoice_id)')) {
        return [null, 'A7.5.2.2b Invoice status lock baseline not recognized'];
    }
    $eol = str_contains($source, "\r\n") ? "\r\n" : "\n";
    $audit = implode($eol, [
        '            // A7.5.3.1: same transaction as status update; rollback both on failure.',
        '            app(\\App\\Services\\WorkspaceAuditTrail::class)->record(',
        "                'invoice.status_changed',",
        "                'invoice',",
        '                (string) $existing->invoice_id,',
        "                ['status_from' => \$current, 'status_to' => \$target]",
        '            );',
    ]);
    $old = '            return null;';
    $methodNew = str_replace($old, $audit . $eol . $old, $method);
    return [substr($source, 0, $from) . $methodNew . substr($source, $to), null];
}

$edits = [
    'app/Services/WorkspaceAuditTrail.php' => 'a7531_patch_trail',
    'app/Providers/AppServiceProvider.php' => 'a7531_patch_provider',
    'app/Http/Controllers/InvoiceController.php' => 'a7531_patch_invoice',
];
$additions = [
    'app/Observers/CrmCrossModuleStatusAuditObserver.php',
    'tests/Unit/CrmCrossModuleStatusAuditSourceTest.php',
    'tests/Feature/CrmCrossModuleStatusAuditTransactionTest.php',
];
$errors = [];
$changes = [];
foreach ($edits as $relative => $function) {
    $path = $root . '/' . $relative;
    $source = is_file($path) ? file_get_contents($path) : false;
    if ($source === false) {
        $errors[] = "Missing editable file: {$relative}";
        continue;
    }
    [$updated, $error] = $function($source);
    if ($error !== null || $updated === null) {
        $errors[] = "Refusing to patch {$relative}: {$error}";
        continue;
    }
    if ($updated !== $source) {
        $changes[$relative] = ['before' => $source, 'after' => $updated];
    }
}
foreach ($additions as $relative) {
    $path = $root . '/' . $relative;
    $payload = $root . '/tools/a7531_payload/' . $relative;
    $expected = is_file($payload) ? file_get_contents($payload) : false;
    if ($expected === false) {
        $errors[] = "Missing payload: {$relative}";
        continue;
    }
    if (is_file($path)) {
        if (hash_file('sha256', $path) !== hash('sha256', $expected)) {
            $errors[] = "Existing custom file must be reviewed: {$relative}";
        }
    } else {
        $changes[$relative] = ['before' => null, 'after' => $expected];
    }
}
if ($errors) {
    foreach ($errors as $error) fwrite(STDERR, "[FAIL] {$error}\n");
    fwrite(STDERR, "No files or database records were modified.\n");
    exit(1);
}
if (!$changes) {
    echo "[PASS] A7.5.3.1 already installed, no duplicate changes.\n";
    exit(0);
}
foreach ($changes as $relative => $change) {
    echo '[' . ($dryRun ? 'CHECK' : 'INFO') . '] ' .
        ($change['before'] === null ? 'add' : 'patch') . ': ' . $relative . PHP_EOL;
}
if ($dryRun) {
    echo "[PASS] Dry run. No file or DB changes.\n";
    exit(0);
}

$backupRel = 'tools/a7531_backups/' . date('Ymd-His') . '-' . bin2hex(random_bytes(4));
$written = [];
try {
    // Back up all existing targets before modifying a single production file.
    foreach ($changes as $relative => $change) {
        if ($change['before'] === null) continue;
        $target = $root . '/' . $backupRel . '/' . $relative;
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
            throw new RuntimeException("Unable to create backup folder: {$relative}");
        }
        if (file_put_contents($target, $change['before'], LOCK_EX) === false) {
            throw new RuntimeException("Unable to back up {$relative}");
        }
    }
    foreach ($changes as $relative => $change) {
        $target = $root . '/' . $relative;
        if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true) && !is_dir(dirname($target))) {
            throw new RuntimeException("Unable to create destination folder: {$relative}");
        }
        // Recheck before writing; do not overwrite concurrent edits.
        if ($change['before'] === null && file_exists($target)) {
            throw new RuntimeException("New file appeared concurrently: {$relative}");
        }
        if ($change['before'] !== null && (!is_file($target) || file_get_contents($target) !== $change['before'])) {
            throw new RuntimeException("Changed since preflight: {$relative}");
        }
        $temp = $target . '.a7531.tmp';
        if (file_put_contents($temp, $change['after'], LOCK_EX) === false || !rename($temp, $target)) {
            @unlink($temp);
            throw new RuntimeException("Could not write {$relative}");
        }
        $written[] = $relative;
        echo '[PASS] ' . ($change['before'] === null ? 'Added: ' : 'Patched: ') . $relative . PHP_EOL;
    }
} catch (Throwable $exception) {
    foreach (array_reverse($written) as $relative) {
        $original = $changes[$relative]['before'];
        if ($original === null) @unlink($root . '/' . $relative);
        else file_put_contents($root . '/' . $relative, $original, LOCK_EX);
    }
    fwrite(STDERR, '[FAIL] ' . $exception->getMessage() . ". Applied files restored.\n");
    exit(1);
}
echo "[PASS] A7.5.3.1 installed. Backup: {$backupRel}\n";
echo "No database migration, data correction, stock movement or notification delivery performed.\n";
