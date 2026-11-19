<?php
/** Static, no database access. */
declare(strict_types=1);
$root=dirname(__DIR__);
$checks=[];
$add=static function (string $name,bool $ok) use (&$checks): void {
    $checks[$name]=$ok;
    echo '['.($ok?'PASS':'FAIL').'] '.$name.PHP_EOL;
};
$files=[
    'app/Services/CrmDashboardAnnouncements.php',
    'app/Console/Commands/PublishDueCrmAnnouncements.php',
    'tests/Unit/CrmDashboardAnnouncementSourceTest.php',
    'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php',
    'app/Http/Controllers/DashboardController.php',
    'resources/views/dashboard/index.blade.php',
    'routes/console.php',
    'app/Http/Controllers/ReportController.php',
];
foreach ($files as $path) $add('File '.$path,is_file($root.'/'.$path));
$read=static fn (string $path): string => is_file($root.'/'.$path) ? (string) file_get_contents($root.'/'.$path) : '';
$view=$read('resources/views/dashboard/index.blade.php');
$controller=$read('app/Http/Controllers/DashboardController.php');
$service=$read('app/Services/CrmDashboardAnnouncements.php');
$cmd=$read('app/Console/Commands/PublishDueCrmAnnouncements.php');
$schedule=$read('routes/console.php');
$report=$read('app/Http/Controllers/ReportController.php');
$add('No publish call in Dashboard Blade',!str_contains($view,'publishDueScheduled('));
$add('No Dashboard Blade fallback direct DB query',!str_contains($view,'Announcement::query()'));
$add('Announcement slider kept',str_contains($view,'dashAnnouncementSlider'));
$add('Existing sales UI kept',str_contains($view,'dash-a763-sales'));
$add('Dashboard uses read-only service',str_contains($controller,"CrmDashboardAnnouncements::class)->latest()"));
$add('Read service scopes published and limits six',str_contains($service,'->published()')&&str_contains($service,'->limit(6)'));
$add('Read service has no publishing call',!str_contains($service,'publishDueScheduled('));
$add('Explicit publisher command exists',str_contains($cmd,"crm:announcements:publish-due")&&str_contains($cmd,'Announcement::publishDueScheduled()'));
$add('Publisher registered once in scheduler',substr_count($schedule,"Schedule::command('crm:announcements:publish-due')")===1);
$add('Reminder schedule still retained',str_contains($schedule,"Schedule::command('workspace:reminders:sync')"));
$add('Report exact filename casing',!in_array('Reportcontroller.php',scandir($root.'/app/Http/Controllers')?:[],true));
$add('Report class matches filename',preg_match('/class\s+ReportController\b/',$report)===1);
$test=$read('tests/Feature/CrmAnnouncementScheduleIsolatedTest.php');
$add('Integration fixture uses memory SQLite',str_contains($test,"'database' => ':memory:'"));
$add('Integration fixture restricts testing env',str_contains($test,"app()->environment('testing')"));
$add('No schema migrations introduced',!is_dir($root.'/tools/a764_payload/database/migrations'));
foreach (array_merge($files,['tools/install_a764_reporting_hardening.php','tools/a764_quick_check.php','tools/a764_runtime_preflight.php']) as $path) {
    if (!str_ends_with($path,'.php') || !is_file($root.'/'.$path)) continue;
    $result=[]; $code=1;
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($root.'/'.$path).' 2>&1',$result,$code);
    $add('PHP syntax '.$path,$code===0);
}
$pass=count(array_filter($checks));$total=count($checks);
echo "A7.6.4 static: {$pass}/{$total} PASS, ".($total-$pass)." FAIL.".PHP_EOL;
exit($pass===$total?0:1);
