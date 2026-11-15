<?php
/**
 * A7.3 narrow installer, preserving all pre-existing Laravel routes and sidebar UI.
 * Usage from Laravel root: php tools/install_todo_routes_sidebar.php
 */
$root = dirname(__DIR__);
$routePath = $root . '/routes/web.php';
$sidebarPath = $root . '/resources/views/components/sidebar.blade.php';
if (!is_file($routePath) || !is_file($sidebarPath)) {
    fwrite(STDERR, "ERROR: Run this script inside your Laravel project root.\n");
    exit(1);
}
$routeSource = file_get_contents($routePath);
$sidebarSource = file_get_contents($sidebarPath);
if (preg_match('/require\s*(?:\(\s*)?__DIR__\s*\.\s*[\'\"]\/todos\.php[\'\"]/', $routeSource)) {
    $nextRoute = $routeSource;
    echo "Route include already registered.\n";
} else {
    $nextRoute = rtrim($routeSource) . "\n\n// A7.3 To-Do management\nrequire __DIR__ . '/todos.php';\n";
}
$re = '~<a\s+href=[\'\"]\#[\'\"]\s+data-label=[\'\"]To-Do[\'\"]\s*>~';
$replacement = '<a href="{{ route(\'todos.index\') }}" class="{{ request()->routeIs(\'todos.*\') ? \'active\' : \'\' }}" data-label="To-Do">';
if (str_contains($sidebarSource, "route('todos.index')")) {
    $nextSidebar = $sidebarSource;
    echo "Sidebar To-Do link already registered.\n";
} else {
    $matches = preg_match_all($re, $sidebarSource);
    if ($matches !== 1) {
        fwrite(STDERR, "ERROR: Expected exactly one existing To-Do sidebar placeholder, found " . (int)$matches . ". No files changed.\n");
        exit(1);
    }
    $nextSidebar = preg_replace($re, $replacement, $sidebarSource, 1);
}
foreach ([[$routePath, $routeSource, $nextRoute], [$sidebarPath, $sidebarSource, $nextSidebar]] as [$path, $before, $after]) {
    if ($before === $after) continue;
    $backup = $path . '.before_a73.bak';
    if (!is_file($backup) && file_put_contents($backup, $before) === false) {
        fwrite(STDERR, "ERROR: Could not create backup for {$path}.\n");
        exit(1);
    }
    if (file_put_contents($path, $after) === false) {
        fwrite(STDERR, "ERROR: Could not update {$path}.\n");
        exit(1);
    }
    echo "Patched: " . str_replace($root . '/', '', $path) . " (backup: {$backup})\n";
}
echo "Done. Run: php artisan optimize:clear && php artisan route:list --path=todos\n";
