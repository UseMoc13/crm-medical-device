<?php
/** Static checks; no Laravel boot or SQL. */
declare(strict_types=1);
$root = dirname(__DIR__);
$checks = [];
$check = static function (string $label, bool $ok) use (&$checks): void {
    $checks[] = $ok;
    echo ($ok ? '[PASS] ' : '[FAIL] ') . $label . PHP_EOL;
};
$files = [
 'app/Http/Controllers/DashboardController.php',
 'resources/views/dashboard/index.blade.php',
 'app/Services/CrmDashboardViewData.php',
 'tests/Unit/CrmDashboardViewDataTest.php',
 'tests/Feature/CrmDashboardViewerIsolationTest.php',
];
foreach ($files as $f) $check('File '.$f, is_file($root.'/'.$f));
if (count(array_filter($checks)) !== count($checks)) exit(1);
$c = file_get_contents($root.'/'.$files[0]);
$v = file_get_contents($root.'/'.$files[1]);
$s = file_get_contents($root.'/'.$files[2]);
$t = file_get_contents($root.'/'.$files[4]);
$check('Dashboard delegates to scoped read model', str_contains($c, "return view('dashboard.index', \$dashboard->forCurrentUser());"));
$check('Dashboard has no old global table counter', !str_contains($c, 'private function countTable'));
$check('Dashboard controller issues no raw SQL', !str_contains($c, 'DB::'));
$check('Sales placeholder replaced', !str_contains($v, 'Sales data will appear here.') && str_contains($v, 'dash-a763-sales'));
$check('Sales renders only provided authorized cards', str_contains($v, '@forelse($salesCards as $sale)'));
$check('Existing announcement slider retained', str_contains($v, 'dashAnnouncementSlider') && str_contains($v, 'dashSliderTrack'));
$check('Existing styles remain isolated', str_contains($v, '.dash-a763-sales') && str_contains($v, '.dash-announcements-head'));
$check('Stat placeholders remain protected', substr_count($v, "\$statHints['") === 4);
$check('Auth required for dashboard model', str_contains($s, "Auth::guard('web')->user()") && str_contains($s, 'AuthenticationException'));
$check('KPI service reused, not recounted globally', str_contains($s, '$this->kpis->run()') && !str_contains($s, "DB::table('customers')"));
$check('Permission gate before activity query', str_contains($s, "CrmRoleAccessCatalog::key('activities', 'view')") && str_contains($s, "CrmRoleAccessCatalog::PAGES['activities']"));
$check('Ownership filter for activity text', str_contains($s, "where('activities.user_id', \$viewer)"));
$check('Explicit collaborator membership required', str_contains($s, "->where('workspace_collaboration_members.user_id', \$viewer)"));
$check('Opt-in row scope narrows activity query', str_contains($s, "config('crm_data_scope.activities_enabled'") && str_contains($s, '$this->dataScope->apply('));
$check('Activity list limited to five', str_contains($s, '->limit(5)->get()'));
$check('Restricted KPI values never read by mapper', str_contains($s, "=== 'visible'") && str_contains($s, "'—'"));
$check('Money formatted without float cast', str_contains($s, 'formatDecimalIdr') && !str_contains($s, '(float)'));
$check('No direct business mutations in model', !preg_match('/\b(?:insert|update|delete|truncate|create)\s*\(/i', $s));
$check('Isolated test forces APP_ENV testing', str_contains($t, "environment() !== 'testing'"));
$check('Isolated test uses in-memory DB', str_contains($t, "'database'=>':memory:'"));
$check('Isolated test verifies no other user activity', str_contains($t, "assertStringNotContainsString('Secret'"));
$check('Isolated test verifies no SQL writes', str_contains($t, 'assertSame([], $writes)'));
$check('Guarded installer uses Git blob baseline', str_contains(file_get_contents(__DIR__.'/install_a763_dashboard.php'),'0542fd14fe2625f43936d0567335eaa6545dc08e'));
foreach ([$files[0],$files[2],$files[3],$files[4]] as $f) {
    $cmd = 'php -l '.escapeshellarg($root.'/'.$f).' 2>&1';
    exec($cmd, $lines, $code);
    $check('PHP syntax '.$f, $code === 0);
    $lines = [];
}
$pass = count(array_filter($checks));
$total = count($checks);
echo PHP_EOL.'A7.6.3 static: '.$pass.'/'.$total.' PASS, '.($total-$pass).' FAIL.'.PHP_EOL;
exit($pass === $total ? 0 : 1);
