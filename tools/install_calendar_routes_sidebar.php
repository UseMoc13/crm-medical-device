<?php
/** Run once: php tools/install_calendar_routes_sidebar.php */
$root = dirname(__DIR__);
$webFile = $root . '/routes/web.php';
$sideFile = $root . '/resources/views/components/sidebar.blade.php';
$calendarRoutes = $root . '/routes/calendar.php';
if (!is_file($calendarRoutes)) { fwrite(STDERR, "Missing routes/calendar.php\n"); exit(1); }
foreach ([$webFile,$sideFile] as $file) {
    if (!is_file($file) || !is_writable($file)) { fwrite(STDERR, "Cannot read/write {$file}\n"); exit(1); }
}
$web = file_get_contents($webFile); $side = file_get_contents($sideFile);
$include = "require __DIR__ . '/calendar.php';";
if (!str_contains($web, $include)) {
    $web = rtrim($web) . "\n\n// A7.4: Calendar routes\n{$include}\n";
}
$pattern = '~<a\s+href="\#"\s+data-label="Calendar">~i';
$replacement = '<a href="{{ route(\'calendar.index\') }}" class="{{ request()->routeIs(\'calendar.*\') ? \'active\' : \'\' }}" data-label="Calendar">';
if (str_contains($side, "route('calendar.index')")) {
    // Already installed.
} elseif (preg_match($pattern, $side)) {
    $side = preg_replace($pattern, $replacement, $side, 1);
} else { fwrite(STDERR, "Calendar sidebar anchor not found. No files changed; manually update sidebar.\n"); exit(1); }
foreach ([[$webFile,$web],[$sideFile,$side]] as [$file,$new]) {
    $old = file_get_contents($file);
    if ($old === $new) { echo "Already configured: {$file}\n"; continue; }
    $backup = $file . '.before-a74-' . date('Ymd-His') . '.bak';
    if (!copy($file,$backup) || file_put_contents($file,$new) === false) { fwrite(STDERR,"Could not write {$file}\n");exit(1); }
    echo "Updated: {$file}\nBackup: {$backup}\n";
}
echo "Finished. Run php artisan optimize:clear && php artisan route:list --path=calendar\n";
