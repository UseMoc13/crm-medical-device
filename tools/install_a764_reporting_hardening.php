<?php
/** A7.6.4: guarded Dashboard GET hardening, publisher scheduling, Git-safe report case rename. */
declare(strict_types=1);
$root = dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);
$payload = __DIR__.'/a764_payload/';
function a764_fail(string $message): never { fwrite(STDERR, '[FAIL] '.$message.PHP_EOL); exit(1); }
function a764_read(string $file): string {
    if (!is_file($file) || !is_readable($file)) a764_fail('Missing/unreadable file: '.$file);
    $data = file_get_contents($file);
    if ($data === false) a764_fail('Failed reading '.$file);
    return $data;
}
function a764_normal(string $value): string { return str_replace(["\r\n","\r"], "\n", $value); }
function a764_line_endings(string $text, string $like): string {
    return str_contains($like,"\r\n") ? str_replace("\n","\r\n",a764_normal($text)) : a764_normal($text);
}
function a764_replace_once(string $source, string $needle, string $with, string $path): string {
    if (substr_count($source, $needle) !== 1) a764_fail('Expected unchanged anchor exactly once in '.$path);
    return str_replace($needle, $with, $source);
}
$changed = [];
$added = [
    'app/Services/CrmDashboardAnnouncements.php',
    'app/Console/Commands/PublishDueCrmAnnouncements.php',
    'tests/Unit/CrmDashboardAnnouncementSourceTest.php',
    'tests/Feature/CrmAnnouncementScheduleIsolatedTest.php',
];
foreach ($added as $relative) {
    $new = a764_normal(a764_read($payload.$relative));
    $target = $root.'/'.$relative;
    if (is_file($target)) {
        if (a764_normal(a764_read($target)) !== $new) a764_fail('Refusing to overwrite different local file: '.$relative);
        echo '[PASS] Already installed: '.$relative.PHP_EOL;
    } else { $changed[$relative] = ['old'=>null,'new'=>$new]; echo '[CHECK] add: '.$relative.PHP_EOL; }
}
// Controller from A7.6.3: ensure the expected source shape, not a whole-file overwrite.
$relative = 'app/Http/Controllers/DashboardController.php';
$old = a764_read($root.'/'.$relative);
$anchor = "return view('dashboard.index', \$dashboard->forCurrentUser());";
$replacement = <<<'PHP_SNIPPET'
return view('dashboard.index', [
            ...$dashboard->forCurrentUser(),
            'latestAnnouncements' => app(\App\Services\CrmDashboardAnnouncements::class)->latest(),
        ]);
PHP_SNIPPET;
if (str_contains($old, "'latestAnnouncements' => app(\\App\\Services\\CrmDashboardAnnouncements::class)->latest()")) {
    echo '[PASS] Already installed: '.$relative.PHP_EOL;
} else {
    if (!str_contains($old,'use App\\Services\\CrmDashboardViewData;') || !str_contains($old,'public function index(CrmDashboardViewData $dashboard)')) a764_fail('Dashboard controller changed; refusing patch.');
    $new = a764_replace_once($old,$anchor,$replacement,$relative);
    $changed[$relative]=['old'=>$old,'new'=>$new]; echo '[CHECK] patch: '.$relative.PHP_EOL;
}
// Remove ALL fallback queries and publish side-effect from the Blade render block.
$relative = 'resources/views/dashboard/index.blade.php';
$old = a764_read($root.'/'.$relative);
if (!str_contains($old, 'Announcement::publishDueScheduled();')) {
    if (!str_contains($old, 'dashAnnouncementSlider') || !str_contains($old, 'use Illuminate\\Support\\Facades\\Storage;')) a764_fail('Unexpected Dashboard Blade state.');
    echo '[PASS] Already installed: '.$relative.PHP_EOL;
} else {
    if (!str_contains($old, 'dash-a763-sales') || !str_contains($old, 'dashAnnouncementSlider')) a764_fail('Dashboard A7.6.3 UI baseline absent.');
    $pattern = '/@php\s+use App\\\\Models\\\\Announcement;\s+use Illuminate\\\\Support\\\\Facades\\\\Storage;\s+\/\/ Prefer \$latestAnnouncements.*?@endphp/s';
    $new = preg_replace($pattern, "@php\n    use Illuminate\\Support\\Facades\\Storage;\n@endphp", $old, 1, $cnt);
    if ($new === null || $cnt !== 1) a764_fail('Dashboard announcement fallback changed; no files written.');
    if (str_contains($new,'publishDueScheduled(')) a764_fail('Dashboard still has publishing side-effect after patch.');
    $changed[$relative]=['old'=>$old,'new'=>$new]; echo '[CHECK] patch: '.$relative.PHP_EOL;
}
// Keep existing reminder schedule; append separate publisher registration exactly once.
$relative='routes/console.php'; $old=a764_read($root.'/'.$relative);
$needle = "Schedule::command('crm:announcements:publish-due')";
if (str_contains($old,$needle)) {
    if (substr_count($old,$needle)!==1) a764_fail('Duplicate publisher schedule detected.');
    echo '[PASS] Already installed: '.$relative.PHP_EOL;
} else {
    if (!str_contains($old, "Schedule::command('workspace:reminders:sync')")) a764_fail('Existing reminder schedule missing; refusing patch.');
    $append = <<<'SCHEDULE'

// A7.6.4: scheduled publishing is a WRITE; never run it from Dashboard Blade/GET.
\Illuminate\Console\Application::starting(static function ($artisan): void {
    if (!$artisan->has('crm:announcements:publish-due')) $artisan->resolve(\App\Console\Commands\PublishDueCrmAnnouncements::class);
});
Schedule::command('crm:announcements:publish-due')->everyMinute()->withoutOverlapping();
SCHEDULE;
    $new=rtrim($old)."\n".$append."\n";
    $changed[$relative]=['old'=>$old,'new'=>$new]; echo '[CHECK] patch: '.$relative.PHP_EOL;
}
// Treat exact directory entry as authoritative on case-insensitive Windows FS.
$directory = $root.'/app/Http/Controllers';
$names = scandir($directory);
if ($names===false) a764_fail('Cannot inspect controller directory.');
$hasOld = in_array('Reportcontroller.php',$names,true);
$hasNew = in_array('ReportController.php',$names,true);
if ($hasOld && $hasNew) a764_fail('Both Reportcontroller.php and ReportController.php exist; resolve manually.');
if (!$hasOld && !$hasNew) a764_fail('Missing reporting controller, cannot fix filename.');
if ($hasOld) {
    $report = a764_read($directory.'/Reportcontroller.php');
    if (!str_contains($report,'namespace App\\Http\\Controllers;') || !preg_match('/class\s+ReportController\b/',$report)) a764_fail('Reportcontroller.php has unexpected class/namespace.');
    // The Git index must track the rename on Windows. Never perform an invisible case-only filesystem rename.
    $gitTracked = []; $code=0;
    exec('git -C '.escapeshellarg($root).' ls-files --error-unmatch -- app/Http/Controllers/Reportcontroller.php 2>&1', $gitTracked, $code);
    if ($code !== 0) a764_fail('Reportcontroller.php is not tracked by Git. Stage/inspect it manually before installing.');
    echo '[CHECK] case-safe Git rename: Reportcontroller.php -> ReportController.php'.PHP_EOL;
} else {
    $report = a764_read($directory.'/ReportController.php');
    if (!preg_match('/class\s+ReportController\b/',$report)) a764_fail('ReportController.php class mismatch.');
    echo '[PASS] ReportController.php already has correct case.'.PHP_EOL;
}
if ($dry) { echo '[PASS] Dry run. No files, Git index, or DB records changed.'.PHP_EOL; exit(0); }
if (!$changed && !$hasOld) { echo '[PASS] A7.6.4 already installed; no changes.'.PHP_EOL; exit(0); }
$backup = $root.'/tools/a764_backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
if (!mkdir($backup,0775,true) && !is_dir($backup)) a764_fail('Cannot create backup directory.');
$restore = []; $created = []; $renamed = false;
function a764_git_mv(string $root,string $source,string $target): void {
    $cmd = 'git -C '.escapeshellarg($root).' mv -- '.escapeshellarg($source).' '.escapeshellarg($target).' 2>&1';
    $lines=[]; $status=0; exec($cmd, $lines, $status);
    if ($status!==0) throw new RuntimeException('Git rename failed: '.implode(' ',array_slice($lines,0,3)));
}
try {
    $backupFiles = $changed;
    if ($hasOld) $backupFiles['app/Http/Controllers/Reportcontroller.php'] = ['old'=>$report,'new'=>null];
    foreach ($backupFiles as $path=>$pair) {
        if ($pair['old']===null) continue;
        $dest = $backup.'/'.$path;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest),0775,true)) throw new RuntimeException('Cannot create backup subdirectory');
        if (file_put_contents($dest,$pair['old'],LOCK_EX)!==strlen($pair['old'])) throw new RuntimeException('Backup write failed: '.$path);
    }
    foreach ($changed as $path=>$pair) {
        $dest=$root.'/'.$path;
        if (!is_dir(dirname($dest)) && !mkdir(dirname($dest),0775,true)) throw new RuntimeException('Cannot create parent: '.$path);
        if ($pair['old']===null) $created[]=$dest;
        else $restore[$path]=$pair['old'];
        $final=$pair['old']===null?$pair['new']:a764_line_endings($pair['new'],$pair['old']);
        if (file_put_contents($dest,$final,LOCK_EX)!==strlen($final)) throw new RuntimeException('Write failed: '.$path);
        echo '[PASS] '.($pair['old']===null?'Added: ':'Patched: ').$path.PHP_EOL;
    }
    if ($hasOld) {
        $temp='app/Http/Controllers/.a764_report_case_'.bin2hex(random_bytes(5)).'.php';
        a764_git_mv($root,'app/Http/Controllers/Reportcontroller.php',$temp);
        try { a764_git_mv($root,$temp,'app/Http/Controllers/ReportController.php'); }
        catch (Throwable $exception) { a764_git_mv($root,$temp,'app/Http/Controllers/Reportcontroller.php'); throw $exception; }
        $renamed=true;
        echo '[PASS] Git-staged case rename: ReportController.php'.PHP_EOL;
    }
} catch (Throwable $e) {
    if ($renamed) {
        try {
            $temp='app/Http/Controllers/.a764_undo_'.bin2hex(random_bytes(5)).'.php';
            a764_git_mv($root,'app/Http/Controllers/ReportController.php',$temp);
            a764_git_mv($root,$temp,'app/Http/Controllers/Reportcontroller.php');
        } catch (Throwable $ignored) { fwrite(STDERR,'[WARN] Report rename rollback needs manual review.'.PHP_EOL); }
    }
    foreach ($restore as $path=>$old) file_put_contents($root.'/'.$path,$old,LOCK_EX);
    foreach ($created as $path) if (is_file($path)) unlink($path);
    a764_fail('Restored application files after failure: '.$e->getMessage());
}
echo '[PASS] A7.6.4 installed. Backup: '.$backup.PHP_EOL;
echo 'NOTE: Git case-only rename is staged. No migration or business DB writes. Run schedule:run via Task Scheduler/cron.'.PHP_EOL;
