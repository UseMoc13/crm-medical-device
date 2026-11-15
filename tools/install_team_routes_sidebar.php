<?php
/**
 * A7.4.6 non-destructive route + sidebar installer.
 * Usage: php tools/install_team_routes_sidebar.php [--dry-run]
 * Makes backups and is idempotent; does not replace existing controllers or views.
 */
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$files = [
    'routes/web.php' => $root . '/routes/web.php',
    'resources/views/components/sidebar.blade.php' => $root . '/resources/views/components/sidebar.blade.php',
];
foreach (['routes/teams.php', 'app/Http/Controllers/CrmTeamController.php', 'app/Http/Middleware/EnsureCrmOrganizationAdmin.php'] as $required) {
    if (!is_file($root . '/' . $required)) {
        fwrite(STDERR, "Missing {$required}. Install A7.4.5 first and extract A7.4.6 ZIP.\n");
        exit(1);
    }
}
$changes = [];
foreach ($files as $relative => $file) {
    if (!is_file($file) || !is_readable($file) || !is_writable($file)) {
        fwrite(STDERR, "Missing or inaccessible: {$relative}\n"); exit(1);
    }
    $changes[$relative] = file_get_contents($file);
}
$web = $changes['routes/web.php'];
if (!str_contains($web, "'/teams.php'")) {
    $web = rtrim($web) . "\n\n// A7.4.6 Team directory and membership (Super Admin only)\nrequire __DIR__ . '/teams.php';\n";
}
$changes['routes/web.php'] = $web;
$sidebar = $changes['resources/views/components/sidebar.blade.php'];
if (!str_contains($sidebar, "route('teams.index')")) {
    // Required anchor is already present in the original project and A7.4.5 sidebar.
    $anchor = '        <a href="#" data-label="Branches">';
    if (!str_contains($sidebar, $anchor)) {
        fwrite(STDERR, "Sidebar anchor changed; no files modified. Add the Teams link manually.\n");
        exit(1);
    }
    $nav = <<<'BLADE'
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        <a href="{{ route('teams.index') }}"
           class="{{ request()->routeIs('teams.*') ? 'active' : '' }}"
           data-label="Teams">
            <span class="ico">♟</span>
            <span class="nav-text">Teams</span>
        </a>
        @endif

BLADE;
    $sidebar = str_replace($anchor, $nav . $anchor, $sidebar);
}
$changes['resources/views/components/sidebar.blade.php'] = $sidebar;
$write = [];
foreach ($files as $relative => $file) {
    if (file_get_contents($file) !== $changes[$relative]) $write[$relative] = $changes[$relative];
}
if (!$write) { echo "Already installed. Nothing changed.\n"; exit(0); }
foreach ($write as $relative => $data) echo ($dry ? 'Would patch: ' : 'Patching: ') . $relative . "\n";
if ($dry) exit(0);
$stamp = date('Ymd-His');
$backups = [];
foreach ($write as $relative => $data) {
    $backup = $files[$relative] . '.before-a746-' . $stamp . '.bak';
    if (!copy($files[$relative], $backup)) {
        fwrite(STDERR, "Backup failed for {$relative}; no files modified.\n"); exit(1);
    }
    $backups[$relative] = $backup;
}
foreach ($write as $relative => $data) {
    if (file_put_contents($files[$relative], $data, LOCK_EX) === false) {
        fwrite(STDERR, "Write failed for {$relative}; backups: " . implode(', ', $backups) . "\n"); exit(1);
    }
}
echo "Team route + sidebar installed. Backups created.\n";
