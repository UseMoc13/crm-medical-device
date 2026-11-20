<?php
/** A7.7.4.2 surgical, idempotent source patch. No installer, no DB writes. */
declare(strict_types=1);

$mode = $argv[1] ?? '--dry-run';
if (!in_array($mode, ['--dry-run', '--apply'], true) || count($argv) !== 2) {
    fwrite(STDERR, "Usage: php tools/a7742_apply_private_file_patch.php --dry-run | --apply\n");
    exit(2);
}
$root = dirname(__DIR__);
if (!is_file($root . '/artisan') || !is_file($root . '/app/Support/CrmPrivateFilePath.php')) {
    fwrite(STDERR, "[STOP] Run in Laravel root; copy the support file from ZIP first.\n");
    exit(2);
}
$branch = [];
$branchCode = 1;
exec('git -C ' . escapeshellarg($root) . ' branch --show-current 2>&1', $branch, $branchCode);
if ($branchCode !== 0 || trim(implode("\n", $branch)) !== 'Big-Changes') {
    fwrite(STDERR, "[STOP] Requires Git branch Big-Changes.\n");
    exit(2);
}

/** @var array<string,list<array{0:string,1:string}>> $patches */
$patches = [
    'app/Http/Controllers/WorkspaceTaskSubmissionController.php' => [
        [
            <<<'OLD'
use App\Models\WorkspaceTaskSubmissionFile;
OLD,
            <<<'NEW'
use App\Models\WorkspaceTaskSubmissionFile;
use App\Support\CrmPrivateFilePath;
NEW
        ],
        [
            <<<'OLD'
            $path = $document->stored_path;
            $document->delete();
OLD,
            <<<'NEW'
            $path = (string) $document->stored_path;
            // Do not delete arbitrary private disk paths from corrupted legacy rows.
            abort_unless(CrmPrivateFilePath::submission(
                $path, (string) $lockedTask->getKey(), (string) $locked->getKey()
            ), 404);
            $document->delete();
NEW
        ],
        [
            <<<'OLD'
        $path = Storage::disk('local')->path($file->stored_path);
        abort_unless(str_starts_with($file->stored_path, 'crm-task-submissions/') && is_file($path), 404);
        return response()->download($path, $this->downloadName($file), [
OLD,
            <<<'NEW'
        $storedPath = (string) $file->stored_path;
        $taskId = (string) $file->assignment->task_id;
        $assignmentId = (string) $file->assignment_id;
        abort_unless(CrmPrivateFilePath::submission($storedPath, $taskId, $assignmentId), 404);
        $path = CrmPrivateFilePath::resolve(
            Storage::disk('local')->path(''), $storedPath,
            'crm-task-submissions/'.$taskId.'/'.$assignmentId
        );
        abort_unless($path !== null, 404);
        return response()->download($path, $this->downloadName($file), [
NEW
        ],
        [
            <<<'OLD'
            $path = Storage::disk('local')->path($file->stored_path);
            if (!str_starts_with($file->stored_path, 'crm-task-submissions/') || !is_file($path)) { $missing++; continue; }
            $name = $this->downloadName($file);
OLD,
            <<<'NEW'
            $storedPath = (string) $file->stored_path;
            $taskId = (string) $file->assignment->task_id;
            $assignmentId = (string) $file->assignment_id;
            $allowed = CrmPrivateFilePath::submission($storedPath, $taskId, $assignmentId);
            $path = $allowed ? CrmPrivateFilePath::resolve(
                Storage::disk('local')->path(''), $storedPath,
                'crm-task-submissions/'.$taskId.'/'.$assignmentId
            ) : null;
            if ($path === null) { $missing++; continue; }
            $name = $this->downloadName($file);
NEW
        ],
    ],
    'app/Http/Controllers/ContractController.php' => [
        [
            <<<'OLD'
use App\Models\Contract;
OLD,
            <<<'NEW'
use App\Models\Contract;
use App\Support\CrmPrivateFilePath;
NEW
        ],
        [
            <<<'OLD'
    $disk = Storage::disk('local');

    if (!$disk->exists($contract->document_path)) {
        return redirect()
            ->route('contracts.show', $contract->contract_id)
            ->with('error', 'File dokumen kontrak tidak ditemukan.');
    }

    return $disk->download(
        $contract->document_path,
        $contract->contract_number . '.pdf'
    );
OLD,
            <<<'NEW'
    $storedPath = (string) $contract->document_path;
    abort_unless(CrmPrivateFilePath::contract($storedPath), 404);
    $disk = Storage::disk('local');
    $resolved = CrmPrivateFilePath::resolve($disk->path(''), $storedPath, 'contracts');

    if ($resolved === null) {
        return redirect()
            ->route('contracts.show', $contract->contract_id)
            ->with('error', 'File dokumen kontrak tidak ditemukan.');
    }

    $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) $contract->contract_number);
    return response()->download(
        $resolved,
        ($safeName ?: 'contract') . '.pdf',
        ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']
    );
NEW
        ],
    ],
];

$updates = [];
foreach ($patches as $relative => $replacements) {
    $path = $root . '/' . $relative;
    $original = @file_get_contents($path);
    if ($original === false) {
        fwrite(STDERR, "[STOP] File missing: {$relative}\n");
        exit(2);
    }
    $crlf = str_contains($original, "\r\n");
    $source = str_replace("\r\n", "\n", $original);
    $changed = $source;
    foreach ($replacements as [$old, $new]) {
        if (substr_count($changed, $new) === 1) {
            continue;
        }
        if (substr_count($changed, $old) !== 1) {
            fwrite(STDERR, "[STOP] Source anchor mismatch: {$relative}. No application files changed.\n");
            exit(2);
        }
        $changed = str_replace($old, $new, $changed);
    }
    $candidate = $crlf ? str_replace("\n", "\r\n", $changed) : $changed;
    if ($candidate !== $original) {
        $updates[$relative] = ['before' => $original, 'after' => $candidate];
    }
}
if (!$updates) {
    echo "[PASS] Already patched; no files changed.\n";
    exit(0);
}
echo '[CHECK] Branch Big-Changes; ' . count($updates) . " controller(s) require patch.\n";
echo "[CHECK] Scope: only task submission file paths and private contract downloads.\n";

$temporary = [];
$backupDirectory = null;
try {
    // Always syntax-check staged candidates before changing any controller.
    foreach ($updates as $relative => $change) {
        $file = $root . '/' . $relative;
        $tmp = $file . '.a7742-' . bin2hex(random_bytes(6)) . '.tmp.php';
        if (file_put_contents($tmp, $change['after'], LOCK_EX) === false) {
            throw new RuntimeException('Unable to stage ' . $relative);
        }
        $temporary[$relative] = $tmp;
        $lines = [];
        $status = 1;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($tmp) . ' 2>&1', $lines, $status);
        if ($status !== 0) {
            throw new RuntimeException('PHP syntax check failed for ' . $relative . ': ' . implode(' ', $lines));
        }
        echo "[PASS] PHP syntax staged: {$relative}\n";
    }
    if ($mode === '--dry-run') {
        echo "[PASS] Dry-run. No application files or DB records changed.\n";
        // Return from the script so PHP executes finally and cleans staged .tmp.php files.
        return;
    }

    $base = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'crm_a7742_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    if (!@mkdir($base, 0700) && !is_dir($base)) {
        throw new RuntimeException('Unable to create backup folder');
    }
    $backupDirectory = $base;
    foreach ($updates as $relative => $change) {
        $backupFile = $base . DIRECTORY_SEPARATOR . basename($relative);
        if (!copy($root . '/' . $relative, $backupFile)) {
            throw new RuntimeException('Unable to back up ' . $relative);
        }
    }
    $applied = [];
    try {
        foreach ($updates as $relative => $change) {
            $target = $root . '/' . $relative;
            if (file_get_contents($target) !== $change['before']) {
                throw new RuntimeException('Concurrent source modification detected: ' . $relative);
            }
            if (!@rename($temporary[$relative], $target)) {
                throw new RuntimeException('Unable to replace controller ' . $relative);
            }
            $applied[] = $relative;
            unset($temporary[$relative]);
        }
    } catch (Throwable $e) {
        foreach ($applied as $relative) {
            $backupFile = $base . DIRECTORY_SEPARATOR . basename($relative);
            if (!@copy($backupFile, $root . '/' . $relative)) {
                fwrite(STDERR, "[CRITICAL] Manual rollback required: {$backupFile}\n");
            }
        }
        throw $e;
    }
    echo "[PASS] A7.7.4.2 private file controller patch applied.\n";
    echo "[BACKUP] {$base}\n";
    echo "[SAFE] No database connections, migrations, role grants, or file deletions.\n";
    echo "[NEXT] php tools/a7742_quick_check.php\n";
} catch (Throwable $e) {
    fwrite(STDERR, '[STOP] ' . $e->getMessage() . "\n");
    if ($backupDirectory !== null) fwrite(STDERR, "[BACKUP] {$backupDirectory}\n");
    exit(1);
} finally {
    foreach ($temporary as $tmp) if (is_file($tmp)) @unlink($tmp);
}
