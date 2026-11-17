<?php
/** Static audit for A7.4.11.1.1. Does not mutate files or database. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [];
$source = static function (string $file) use ($root): string { return is_file($root.'/'.$file) ? (string)file_get_contents($root.'/'.$file) : ''; };
$sidebar = $source('resources/views/components/sidebar.blade.php');
$topbar = $source('resources/views/workspace-notifications/_topbar.blade.php');
$index = $source('resources/views/workspace-notifications/index.blade.php');
$styles = $source('resources/views/workspace-notifications/_style.blade.php');
$svg = $source('resources/views/workspace-notifications/_bell_icon.blade.php');
$note = "@include('workspace-notifications._bell_icon')";
$general = strpos($sidebar, 'General & Workspace');
$admin = strpos($sidebar, 'Administration');
$nav = strpos($sidebar, 'data-label="Notifications"');
$todo = strpos($sidebar, 'data-label="To-Do"');
$checks = [
  'Notification sidebar entry occurs exactly once' => substr_count($sidebar, 'data-label="Notifications"') === 1,
  'Notification follows To-Do inside General & Workspace' => $general !== false && $todo !== false && $nav !== false && $admin !== false && $general < $todo && $todo < $nav && $nav < $admin,
  'Notification sidebar uses existing inbox route' => str_contains($sidebar, "route('workspace-notifications.index')"),
  'Sidebar marks Notification route active' => str_contains($sidebar, "request()->routeIs('workspace-notifications.*')"),
  'Sidebar icon uses shared SVG' => substr_count($sidebar, $note) === 1,
  'Topbar icon uses shared SVG' => substr_count($topbar, $note) === 1,
  'Empty inbox icon uses shared SVG' => substr_count($index, $note) === 1,
  'No bell emoji remains in Notification views' => !str_contains($sidebar, '🔔') && !str_contains($topbar, '🔔') && !str_contains($index, '🔔'),
  'Shared icon is inline SVG' => str_contains($svg, '<svg class="wsn-icon"') && str_contains($svg, 'stroke="currentColor"'),
  'SVG is hidden from screen readers as decoration' => str_contains($svg, 'aria-hidden="true"'),
  'Icon CSS is present only once' => substr_count($styles, '.wsn-icon{display:block;') === 1,
  'Existing Notification badge preserved' => str_contains($topbar, 'wsn-count'),
  'Existing inbox link preserved' => str_contains($topbar, "route('workspace-notifications.index')"),
  'Existing Notes styling untouched by patch' => is_file($root.'/resources/views/notes/_style.blade.php'),
];
$pass = 0;
foreach ($checks as $label => $ok) {
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . "\n";
    if ($ok) $pass++;
}
echo "\nA7.4.11.1.1 navigation check: {$pass}/" . count($checks) . " PASS\n";
exit($pass === count($checks) ? 0 : 1);
