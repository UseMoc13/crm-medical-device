<?php
/**
 * A7.4.11.1 installer. Only patches known anchors, after verifying ALL targets.
 * New files are included at their normal relative paths in the archive.
 * Existing files are backed up automatically before any replacement.
 */
declare(strict_types=1);
$root = dirname(__DIR__);
$changes = [];
$fail = static function (string $message): never {fwrite(STDERR, "ERROR: {$message}\nNo files changed.\n"); exit(1);};
$patch = static function (string $path, string $old, string $new, string $label) use ($root, &$changes, $fail): void {
    $full = $root.'/'.$path;
    if (!is_file($full)) $fail("Missing {$path}");
    $src = file_get_contents($full);
    if ($src === false) $fail("Unreadable {$path}");
    if (str_contains($src, $new)) {
        echo "[SKIP] Already integrated: {$label}\n";
        return;
    }
    $matches = substr_count($src, $old);
    if ($matches !== 1) $fail("Unexpected anchor in {$path} (found {$matches}, expected 1). Review local changes before installing.");
    $changes[$path] = str_replace($old, $new, $src);
};

// Validate new files before mutating anything. The migration runs separately.
$required = [
    'database/migrations/2026_11_17_230000_create_workspace_notifications.php',
    'app/Services/WorkspaceNotificationCenter.php',
    'app/Services/WorkspaceReminderSync.php',
    'app/Models/WorkspaceNotification.php',
    'app/Http/Controllers/WorkspaceNotificationController.php',
    'app/Observers/WorkspaceTaskAssignmentNotificationObserver.php',
    'app/Observers/WorkspaceCollaborationMemberNotificationObserver.php',
    'app/Observers/WorkspaceTaskReviewNotificationObserver.php',
    'app/Console/Commands/SyncWorkspaceReminders.php',
    'resources/views/workspace-notifications/_style.blade.php',
    'resources/views/workspace-notifications/_topbar.blade.php',
    'resources/views/workspace-notifications/index.blade.php',
    'routes/workspace_notifications.php',
];
foreach ($required as $relative) if (!is_file($root.'/'.$relative)) $fail('Missing new file '.$relative);

$patch('app/Providers/AppServiceProvider.php',
    '        CrmCreatorAttribution::register();',
    <<<'NEW'
        CrmCreatorAttribution::register();
        // A7.4.11.1: notifications share the same DB transaction as their source action.
        \App\Models\WorkspaceTaskAssignment::observe(\App\Observers\WorkspaceTaskAssignmentNotificationObserver::class);
        \App\Models\WorkspaceCollaborationMember::observe(\App\Observers\WorkspaceCollaborationMemberNotificationObserver::class);
        \App\Models\WorkspaceTaskReview::observe(\App\Observers\WorkspaceTaskReviewNotificationObserver::class);
NEW,
    'Workspace event observers');
$patch('routes/web.php',
    "require __DIR__ . '/notes.php';",
    "require __DIR__ . '/notes.php';\n\n// A7.4.11.1: private notifications and reminders\nrequire __DIR__ . '/workspace_notifications.php';",
    'Notifications routes');
$patch('routes/console.php',
    "use Illuminate\\Support\\Facades\\Artisan;",
    "use Illuminate\\Support\\Facades\\Artisan;\nuse Illuminate\\Support\\Facades\\Schedule;",
    'Reminder scheduler import');
// Patch lower console part separately to avoid applying two edits to same source map.
$consolePath = 'routes/console.php';
if (isset($changes[$consolePath])) {
    $console = $changes[$consolePath];
    $scheduleLine = "Schedule::command('workspace:reminders:sync')->everyFifteenMinutes()->withoutOverlapping();";
    if (!str_contains($console, $scheduleLine)) {
        // Explicit registration works whether Laravel discovers app/Console/Commands automatically or not.
        $console .= "\n// A7.4.11.1: always register the reminder command before scheduling it.\n";
        $console .= "\\Illuminate\\Console\\Application::starting(static function (\$artisan): void {\n";
        $console .= "    if (!\$artisan->has('workspace:reminders:sync')) \$artisan->resolve(\\App\\Console\\Commands\\SyncWorkspaceReminders::class);\n";
        $console .= "});\n";
        $console .= "// Call `php artisan schedule:run` every minute via Windows Task Scheduler/cron.\n".$scheduleLine."\n";
    }
    $changes[$consolePath] = $console;
} elseif (is_file($root.'/'.$consolePath)) {
    $console = (string) file_get_contents($root.'/'.$consolePath);
    if (!str_contains($console, "Schedule::command('workspace:reminders:sync')")) {
        if (!str_contains($console, 'use Illuminate\\Support\\Facades\\Schedule;')) $fail('Cannot add scheduler: Schedule import missing.');
        $changes[$consolePath] = $console."\n// A7.4.11.1: reminder scan\n".$scheduleLine."\n";
    }
}
$bell = <<<'OLD'
                    <button
                        class="icon-btn"
                        type="button"
                        aria-label="Notifications">
                        🔔
                    </button>
OLD;
$patch('resources/views/layouts/app.blade.php', $bell,
    "                    @include('workspace-notifications._topbar')", 'Global notification bell');
// A second anchor in the same layout is patched against the changed copy, not the original.
$path = 'resources/views/layouts/app.blade.php';
$source = $changes[$path] ?? (string)file_get_contents($root.'/'.$path);
$anchor = "    @include('notes._style')";
$new = "    @include('notes._style')\n    @include('workspace-notifications._style')";
if (!str_contains($source, $new)) {
    if (substr_count($source, $anchor) !== 1) $fail('Layout stylesheet anchor changed; refuse partial patch.');
    $changes[$path] = str_replace($anchor, $new, $source);
}

// Optional: keep the legacy sidebar navigation present, but make its existing
// Notification item actually open the inbox.
$sidebar = 'resources/views/components/sidebar.blade.php';
if (is_file($root.'/'.$sidebar)) {
    $source = (string)file_get_contents($root.'/'.$sidebar);
    $old = '<a href="#" data-label="Notifications">';
    $new = '<a href="{{ route(\'workspace-notifications.index\') }}" data-label="Notifications">';
    if (str_contains($source, $new)) echo "[SKIP] Sidebar already linked\n";
    elseif (substr_count($source, $old) === 1) $changes[$sidebar] = str_replace($old, $new, $source);
    else $fail('Sidebar Notification item differs; no files changed.');
}

if (!$changes) {echo "[PASS] A7.4.11.1 already installed. No changes necessary.\n";exit(0);}
$backup = $root.'/tools/a74111_backups/'.date('Ymd-His').'-'.getmypid();
if (!is_dir($backup) && !mkdir($backup, 0775, true)) $fail('Unable to create backup directory');
foreach ($changes as $path => $content) {
    $to = $backup.'/'.$path;
    if (!is_dir(dirname($to)) && !mkdir(dirname($to), 0775, true)) $fail("Backup folder failed for {$path}");
    if (!copy($root.'/'.$path, $to)) $fail("Failed to back up {$path}");
}
foreach ($changes as $path => $content) {
    $to = $root.'/'.$path;
    $temp = $to.'.a74111.tmp';
    if (file_put_contents($temp, $content, LOCK_EX) === false || !rename($temp, $to)) {
        fwrite(STDERR, "Write failed for {$path}. Restore from {$backup} if necessary.\n");
        exit(1);
    }
    echo "Integrated: {$path}\n";
}
echo "[PASS] A7.4.11.1 installed. Backups: ".str_replace($root.'/', '', $backup)."\n";
echo "Run php artisan migrate and php tools/a74111_quick_check.php next. No reset needed.\n";
