<?php
/** Read-only static audit. HTTP and browser behaviors must be tested separately. */
declare(strict_types=1);
$root = dirname(__DIR__); $ok=0; $fail=0;
$check = static function(string $name,bool $pass) use (&$ok,&$fail):void {
    echo ($pass?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
    $pass ? $ok++ : $fail++;
};
$paths = [
    'app/Models/WorkspaceNotification.php',
    'app/Services/WorkspaceNotificationCenter.php',
    'app/Services/WorkspaceReminderSync.php',
    'app/Http/Controllers/WorkspaceNotificationController.php',
    'app/Observers/WorkspaceTaskAssignmentNotificationObserver.php',
    'app/Observers/WorkspaceCollaborationMemberNotificationObserver.php',
    'app/Observers/WorkspaceTaskReviewNotificationObserver.php',
    'app/Console/Commands/SyncWorkspaceReminders.php',
    'routes/workspace_notifications.php',
    'database/migrations/2026_11_17_230000_create_workspace_notifications.php',
    'resources/views/workspace-notifications/index.blade.php',
    'resources/views/workspace-notifications/_topbar.blade.php',
    'resources/views/workspace-notifications/_style.blade.php',
    'tests/Feature/WorkspaceNotificationHttpTest.php',
];
foreach ($paths as $path) $check('File '.$path, is_file($root.'/'.$path));
$get = static fn(string $p):string => is_file($root.'/'.$p)?(string)file_get_contents($root.'/'.$p):'';
$layout=$get('resources/views/layouts/app.blade.php');
$provider=$get('app/Providers/AppServiceProvider.php');
$routes=$get('routes/web.php');
$controller=$get('app/Http/Controllers/WorkspaceNotificationController.php');
$service=$get('app/Services/WorkspaceNotificationCenter.php');
$sync=$get('app/Services/WorkspaceReminderSync.php');
$console=$get('routes/console.php');
$check('Topbar has one notification component', substr_count($layout, "@include('workspace-notifications._topbar')") === 1);
$check('Notification style included exactly once', substr_count($layout, "@include('workspace-notifications._style')") === 1);
$check('Notifications routes registered once', substr_count($routes,"require __DIR__ . '/workspace_notifications.php';") === 1);
$check('Three notification observers registered',
    str_contains($provider, 'WorkspaceTaskAssignmentNotificationObserver::class')
    && str_contains($provider, 'WorkspaceCollaborationMemberNotificationObserver::class')
    && str_contains($provider, 'WorkspaceTaskReviewNotificationObserver::class'));
$check('Owner-scoped inbox',str_contains($controller,"where('recipient_user_id'"));
$check('Read operations are PATCH, open is POST',str_contains($get('routes/workspace_notifications.php'), "Route::post('workspace-notifications/{notification}/open'") && str_contains($get('routes/workspace_notifications.php'),"Route::patch('workspace-notifications/{notification}/read'"));
$check('Target authorization before redirect',str_contains($controller,'$center->canOpen('));
$check('Do not grant access through a notification',str_contains($service,'canOpen(User $user') && str_contains($service,'workspace_collaboration_members'));
$check('Idempotent dedupe insert',str_contains($service,'insertOrIgnore') && str_contains($get('database/migrations/2026_11_17_230000_create_workspace_notifications.php'),'wsn_user_dedupe_unique'));
$check('Scheduler registered',str_contains($console,"Schedule::command('workspace:reminders:sync')"));
$check('Reminder covers four sources',str_contains($sync,"'note' => 0, 'calendar' => 0, 'activity' => 0, 'todo' => 0"));
$check('Previous notes UI still present',str_contains($layout,"@include('notes._floating')"));
echo "\nA7.4.11.1 static: {$ok}/".($ok+$fail)." PASS; {$fail} FAIL.\n";
exit($fail ? 1 : 0);
