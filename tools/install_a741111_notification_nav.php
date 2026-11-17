<?php
/**
 * A7.4.11.1.1 Notification navigation and SVG icon refinement.
 * Preserves local Blade files by patching only verified anchors.
 * Checks all files before writing, backs up originals, supports re-running.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$changes = [];
$stop = static function (string $reason): never {
    fwrite(STDERR, "ERROR: {$reason}\nNo files changed by this run.\n");
    exit(1);
};
$read = static function (string $relative) use ($root, $stop): string {
    $path = $root . '/' . $relative;
    if (!is_file($path)) $stop("Missing {$relative}");
    $content = file_get_contents($path);
    if ($content === false) $stop("Cannot read {$relative}");
    return $content;
};
$replaceExact = static function (string $path, string $old, string $new) use ($read, $stop, &$changes): void {
    $source = $read($path);
    if (str_contains($source, $new)) {
        echo "[SKIP] Already patched: {$path}\n";
        return;
    }
    $matches = substr_count($source, $old);
    if ($matches !== 1) $stop("Unexpected {$path}: anchor found {$matches} time(s), expected 1. Inspect local edits.");
    $changes[$path] = str_replace($old, $new, $source);
};

$icon = 'resources/views/workspace-notifications/_bell_icon.blade.php';
if (!str_contains($read($icon), '<svg class="wsn-icon"')) $stop('Shared SVG bell icon not present or invalid.');

$sidebarPath = 'resources/views/components/sidebar.blade.php';
$sidebar = $read($sidebarPath);
$oldSidebar = <<<'BLADE'
        <a href="{{ route('workspace-notifications.index') }}" data-label="Notifications">
            <span class="ico">🔔</span>
            <span class="nav-text">Notifications</span>
        </a>
BLADE;
$newSidebar = <<<'BLADE'
        <a href="{{ route('workspace-notifications.index') }}"
           class="{{ request()->routeIs('workspace-notifications.*') ? 'active' : '' }}"
           data-label="Notifications">
            <span class="ico">@include('workspace-notifications._bell_icon')</span>
            <span class="nav-text">Notifications</span>
        </a>
BLADE;
$todoAnchor = <<<'BLADE'
        <a href="{{ route('todos.index') }}"
        class="{{ request()->routeIs('todos.*') ? 'active' : '' }}"
        data-label="To-Do">
            <span class="ico">☑</span>
            <span class="nav-text">To-Do</span>
        </a>
BLADE;
if (str_contains($sidebar, $newSidebar)) {
    $generalAt = strpos($sidebar, 'General & Workspace');
    $notificationAt = strpos($sidebar, $newSidebar);
    $adminAt = strpos($sidebar, 'Administration');
    if ($generalAt === false || $adminAt === false || !($generalAt < $notificationAt && $notificationAt < $adminAt)) {
        $stop('Sidebar Notifications exists but is outside General & Workspace. Refusing uncertain patch.');
    }
    echo "[SKIP] Sidebar already moved and uses SVG\n";
} else {
    if (substr_count($sidebar, $oldSidebar) !== 1) $stop('Legacy Notifications sidebar item not found exactly once.');
    if (substr_count($sidebar, $todoAnchor) !== 1) $stop('To-Do sidebar anchor not found exactly once.');
    // Remove the legacy item (including its following newline), preserving all other nav elements.
    $withoutLegacy = str_replace($oldSidebar . "\n", '', $sidebar);
    $changes[$sidebarPath] = str_replace($todoAnchor, $todoAnchor . "\n\n" . $newSidebar, $withoutLegacy);
}

$replaceExact('resources/views/workspace-notifications/_topbar.blade.php',
    '<span aria-hidden="true">🔔</span>',
    "@include('workspace-notifications._bell_icon')");
$replaceExact('resources/views/workspace-notifications/index.blade.php',
    '<span aria-hidden="true">🔔</span>',
    "@include('workspace-notifications._bell_icon')");

$stylePath = 'resources/views/workspace-notifications/_style.blade.php';
$style = $read($stylePath);
$svgStyles = <<<'CSS'
/* A7.4.11.1.1: shared SVG bell, sized for each location. */
.wsn-icon{display:block;width:20px;height:20px;flex-shrink:0;stroke:currentColor}
.wsn-bell .wsn-icon{width:20px;height:20px}
.sidebar .nav a[data-label="Notifications"] .ico .wsn-icon{width:17px;height:17px}
.wsn-inbox-empty .wsn-icon{width:34px;height:34px;margin:0 auto;color:#8093a9}
CSS;
if (str_contains($style, $svgStyles)) {
    echo "[SKIP] SVG icon CSS already integrated\n";
} else {
    if (substr_count($style, '</style>') !== 1) $stop('Notification stylesheet has unexpected closing tag.');
    $changes[$stylePath] = str_replace('</style>', "\n" . $svgStyles . "\n</style>", $style);
}

if (!$changes) {
    echo "[PASS] A7.4.11.1.1 is already installed. No changes required.\n";
    exit(0);
}

$backup = $root . '/tools/a741111_backups/' . date('Ymd-His') . '-' . getmypid();
if (!mkdir($backup, 0775, true) && !is_dir($backup)) $stop('Unable to create backup directory.');
foreach ($changes as $path => $content) {
    $dest = $backup . '/' . $path;
    if (!is_dir(dirname($dest)) && !mkdir(dirname($dest), 0775, true)) $stop("Cannot create backup folder for {$path}");
    if (!copy($root . '/' . $path, $dest)) $stop("Cannot back up {$path}");
}
foreach ($changes as $path => $content) {
    $original = $root . '/' . $path;
    $temporary = $original . '.a741111.tmp';
    if (file_put_contents($temporary, $content, LOCK_EX) === false || !rename($temporary, $original)) {
        fwrite(STDERR, "Write error: {$path}. Restore affected files from {$backup}.\n");
        exit(1);
    }
    echo "Integrated: {$path}\n";
}
echo '[PASS] Navigation refined; originals backed up to ' . str_replace($root . '/', '', $backup) . "\n";
echo "No migration or database reset is needed.\n";
