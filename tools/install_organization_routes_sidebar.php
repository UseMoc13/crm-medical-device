<?php
/**
 * CRM A7.4.5 non-destructive integration.
 * Usage: php tools/install_organization_routes_sidebar.php [--dry-run]
 * Backups before writing; never replaces a controller or a whole existing view.
 */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$changes = [];
$paths = [
    'routes/web.php' => $root . '/routes/web.php',
    'resources/views/components/sidebar.blade.php' => $root . '/resources/views/components/sidebar.blade.php',
    'resources/views/roles/index.blade.php' => $root . '/resources/views/roles/index.blade.php',
    'resources/views/users/index.blade.php' => $root . '/resources/views/users/index.blade.php',
];
foreach ($paths as $relative => $file) {
    if (!is_file($file) || !is_readable($file) || !is_writable($file)) {
        fwrite(STDERR, "Missing or inaccessible: {$relative}\n"); exit(1);
    }
    $changes[$relative] = file_get_contents($file);
}
if (!is_file($root.'/routes/organization.php')) {
    fwrite(STDERR, "Extract the A7.4.5 ZIP into the Laravel project first.\n"); exit(1);
}
// Only add our route group once. Do not disturb existing Laravel/RBAC routes.
$web = $changes['routes/web.php'];
if (!str_contains($web, "'/organization.php'")) {
    $web = rtrim($web) . "\n\n// A7.4.5 Organization hierarchy (Super Admin only)\nrequire __DIR__ . '/organization.php';\n";
}
$changes['routes/web.php'] = $web;

// Menu is guarded as well as the routes. Hidden links do NOT replace server authorization.
$sidebar = $changes['resources/views/components/sidebar.blade.php'];
if (!str_contains($sidebar, "route('organization.index')")) {
    $anchor = '        <a href="#" data-label="Branches">';
    if (!str_contains($sidebar, $anchor)) {
        fwrite(STDERR, "Sidebar anchor changed: cannot locate Branches link; no files modified.\n"); exit(1);
    }
    $nav = <<<'BLADE'
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        <a href="{{ route('organization.index') }}"
           class="{{ request()->routeIs('organization.*') ? 'active' : '' }}"
           data-label="Organization">
            <span class="ico">♧</span>
            <span class="nav-text">Organization</span>
        </a>
        @endif

BLADE;
    $sidebar = str_replace($anchor, $nav . $anchor, $sidebar);
}
$changes['resources/views/components/sidebar.blade.php'] = $sidebar;

// Discovery shortcut in both existing Administration indexes.
foreach (['resources/views/roles/index.blade.php','resources/views/users/index.blade.php'] as $relative) {
    $source = $changes[$relative];
    if (str_contains($source, "route('organization.index')")) continue;
    $anchor = '    <div class="actions">';
    if (!str_contains($source, $anchor)) {
        fwrite(STDERR, "Index action anchor changed in {$relative}; no files modified.\n"); exit(1);
    }
    $link = <<<'BLADE'
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
            <a href="{{ route('organization.index') }}" class="btn">Organization Structure</a>
        @endif
BLADE;
    $source = preg_replace('/'.preg_quote($anchor, '/').'/', $anchor . "\n" . $link, $source, 1);
    $changes[$relative] = $source;
}

$toWrite = [];
foreach ($paths as $relative => $file) {
    $old = file_get_contents($file);
    if ($old !== $changes[$relative]) $toWrite[$relative] = $changes[$relative];
}
if (!$toWrite) {
    echo "Already installed. Nothing changed.\n";
    exit(0);
}
foreach (array_keys($toWrite) as $relative) echo ($dry ? "Would patch: " : "Patching: ") . $relative . "\n";
if ($dry) exit(0);

// Stage backups for all files before changing anything.
$backups = [];
$stamp = date('Ymd-His');
foreach ($toWrite as $relative => $content) {
    $path = $paths[$relative];
    $backup = $path . '.before-a745-' . $stamp . '.bak';
    if (!copy($path, $backup)) {
        fwrite(STDERR, "Backup failed: {$relative}. No application files modified.\n"); exit(1);
    }
    $backups[$relative] = $backup;
}
foreach ($toWrite as $relative => $content) {
    if (file_put_contents($paths[$relative], $content, LOCK_EX) === false) {
        fwrite(STDERR, "Write failed: {$relative}. Backups: " . implode(', ', $backups) . "\n");
        exit(1);
    }
}
echo "Organization routes + navigation installed. Backups created.\n";
