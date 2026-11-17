<?php
/** Safe, idempotent A7.4.12.1 installer. Never resets or seeds the CRM database. */
$root = dirname(__DIR__);
$changes = [];
$errors = [];

$providerPath = 'app/Providers/AppServiceProvider.php';
$sidebarPath = 'resources/views/components/sidebar.blade.php';
$routesPath = 'routes/web.php';

$providerBlock = <<<'CODE'
        // A7.4.12.1: append-only structured audit, in the same DB transaction as source.
        \App\Models\WorkspaceTaskAssignment::observe(\App\Observers\WorkspaceAuditObserver::class);
        \App\Models\WorkspaceCollaborationMember::observe(\App\Observers\WorkspaceAuditObserver::class);
        \App\Models\WorkspaceTaskReview::observe(\App\Observers\WorkspaceAuditObserver::class);
        \App\Models\WorkspaceTask::observe(\App\Observers\WorkspaceAuditObserver::class);
        \App\Models\WorkspaceNotificationPreference::observe(\App\Observers\WorkspaceAuditObserver::class);
CODE;
$sidebarBlock = <<<'BLADE'
        @if(app(\App\Services\WorkspaceAuditAccess::class)->canView(auth('web')->user()))
        <a href="{{ route('workspace-audit.index') }}"
           class="{{ request()->routeIs('workspace-audit.*') ? 'active' : '' }}"
           data-label="Audit Logs">
            <span class="ico" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5l3 2"/><circle cx="12" cy="12" r="9"/></svg></span>
            <span class="nav-text">Audit Logs</span>
        </a>
        @endif
BLADE;

function planReplacement(string $relative, callable $make): void
{
    global $root, $changes, $errors;
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $errors[] = "Missing required file: {$relative}";
        return;
    }
    $before = file_get_contents($path);
    if ($before === false) {
        $errors[] = "Cannot read: {$relative}";
        return;
    }
    $after = $make($before);
    if ($after === null) {
        $errors[] = "Unknown layout/anchor in {$relative}. No changes will be made.";
        return;
    }
    if ($after !== $before) $changes[$relative] = ['before' => $before, 'after' => $after];
}

planReplacement($providerPath, function (string $s) use ($providerBlock): ?string {
    if (str_contains($s, 'WorkspaceAuditObserver::class')) {
        return substr_count($s, 'WorkspaceAuditObserver::class') === 5 ? $s : null;
    }
    $anchor = 'CrmCreatorAttribution::register();';
    if (substr_count($s, $anchor) !== 1 || !str_contains($s, 'WorkspaceTaskReviewNotificationObserver::class')) return null;
    return str_replace($anchor, $anchor . "\n" . $providerBlock, $s);
});

planReplacement($sidebarPath, function (string $s) use ($sidebarBlock): ?string {
    if (str_contains($s, "route('workspace-audit.index')")) {
        return substr_count($s, "route('workspace-audit.index')") === 1 ? $s : null;
    }
    $pattern = '~<a\s+href="#"\s+data-label="Audit Logs">\s*<span class="ico">.*?</span>\s*<span class="nav-text">Audit Logs</span>\s*</a>~s';
    if (preg_match_all($pattern, $s) !== 1) return null;
    return preg_replace($pattern, $sidebarBlock, $s, 1);
});

planReplacement($routesPath, function (string $s): ?string {
    $include = "require __DIR__ . '/workspace_audit.php';";
    if (str_contains($s, $include)) return substr_count($s, $include) === 1 ? $s : null;
    $anchor = "require __DIR__ . '/notes.php';";
    if (substr_count($s, $anchor) !== 1) return null;
    return str_replace($anchor, $anchor . "\n\n// A7.4.12.1: read-only workspace audit, permission-gated.\n" . $include, $s);
});

// Explicitly verify the additive source files are present. Existing local copies
// are never replaced blindly; the caller extracts the ZIP before running this.
foreach ([
    'app/Services/WorkspaceAuditAccess.php',
    'app/Services/WorkspaceAuditTrail.php',
    'app/Observers/WorkspaceAuditObserver.php',
    'app/Models/WorkspaceAuditEvent.php',
    'app/Http/Controllers/WorkspaceAuditController.php',
    'database/migrations/2026_11_18_010000_create_workspace_audit_events.php',
    'routes/workspace_audit.php',
    'resources/views/workspace-audit/index.blade.php',
    'resources/views/workspace-audit/_style.blade.php',
] as $file) {
    if (!is_file($root . '/' . $file)) $errors[] = 'Missing patch component: ' . $file;
}
if ($errors) {
    foreach ($errors as $e) fwrite(STDERR, "ERROR: {$e}\n");
    fwrite(STDERR, "No tracked files were changed.\n");
    exit(1);
}
if (!$changes) {
    echo "[PASS] A7.4.12.1 already integrated. No duplicate changes.\n";
    exit(0);
}
$stamp = date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
$backupRoot = $root . '/tools/a74121_backups/' . $stamp;
$applied = [];
try {
    foreach ($changes as $file => $diff) {
        $backup = $backupRoot . '/' . $file;
        if (!is_dir(dirname($backup)) && !mkdir(dirname($backup), 0775, true) && !is_dir(dirname($backup))) {
            throw new RuntimeException('Cannot create backup folder for ' . $file);
        }
        if (file_put_contents($backup, $diff['before'], LOCK_EX) === false) {
            throw new RuntimeException('Cannot back up ' . $file);
        }
    }
    foreach ($changes as $file => $diff) {
        $target = $root . '/' . $file;
        $temp = $target . '.a74121.tmp';
        if (file_put_contents($temp, $diff['after'], LOCK_EX) === false || !rename($temp, $target)) {
            @unlink($temp);
            throw new RuntimeException('Failed to write ' . $file);
        }
        $applied[] = $file;
        echo '[PASS] Integrated: ' . $file . "\n";
    }
} catch (Throwable $e) {
    foreach ($applied as $file) file_put_contents($root . '/' . $file, $changes[$file]['before'], LOCK_EX);
    fwrite(STDERR, 'ERROR: ' . $e->getMessage() . ". Applied files restored.\n");
    exit(1);
}
echo '[PASS] A7.4.12.1 integrated. Backup: tools/a74121_backups/' . $stamp . "\n";
echo "Run php artisan migrate, then php tools/a74121_quick_check.php. No database reset.\n";
