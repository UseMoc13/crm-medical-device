<?php
/** Read-only A7.4.11.2 static integration audit. */
declare(strict_types=1);
$root=dirname(__DIR__); $pass=0; $fail=0;
$check=static function (string $name,bool $ok) use (&$pass,&$fail):void {
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;
    $ok?$pass++:$fail++;
};
$get=static fn(string $p): string => is_file($root.'/'.$p)?(string)file_get_contents($root.'/'.$p):'';
foreach ([
    'app/Models/WorkspaceNotificationPreference.php',
    'app/Services/WorkspaceNotificationPreferences.php',
    'database/migrations/2026_11_17_234000_create_workspace_notification_preferences.php',
    'resources/views/workspace-notifications/settings.blade.php',
    'public/js/workspace-notifications-poll.js',
    'tests/Feature/WorkspaceNotificationPreferencesHttpTest.php',
    'resources/views/workspace-notifications/_topbar.blade.php',
    'resources/views/workspace-notifications/_bell_icon.blade.php',
] as $path) $check('File '.$path,is_file($root.'/'.$path));
$controller=$get('app/Http/Controllers/WorkspaceNotificationController.php');
$routes=$get('routes/workspace_notifications.php');
$service=$get('app/Services/WorkspaceNotificationCenter.php');
$sync=$get('app/Services/WorkspaceReminderSync.php');
$topbar=$get('resources/views/workspace-notifications/_topbar.blade.php');
$style=$get('resources/views/workspace-notifications/_style.blade.php');
$js=$get('public/js/workspace-notifications-poll.js');
$settings=$get('resources/views/workspace-notifications/settings.blade.php');
$sidebar=$get('resources/views/components/sidebar.blade.php');
$check('Polling endpoint owner scoped',str_contains($controller, "where('recipient_user_id'") && str_contains($controller,'public function poll()'));
$check('Polling includes no raw target ID',str_contains($controller, "'created_label'") && !str_contains(substr($controller,strpos($controller,'public function poll()'), strpos($controller,'public function settings(')-strpos($controller,'public function poll()')), "'target_id'"));
$check('Polling GET is throttled',str_contains($routes,"Route::get('workspace-notifications/poll'") && str_contains($routes,"throttle:20,1"));
$check('Preferences GET and PATCH',str_contains($routes,"Route::get('workspace-notifications/settings'") && str_contains($routes,"Route::patch('workspace-notifications/settings'"));
$check('Preferences saved for current user',str_contains($controller,'updateOrCreate(') && str_contains($controller,'Auth::guard(\'web\')->id()'));
$check('Notification sender filters new events',str_contains($service,'WorkspaceNotificationPreferences::class') && str_contains($service,'->allows('));
$check('Reminder uses per-user lead',str_contains($sync,"$".'pref[\'reminder_lead_minutes\']') && str_contains($sync,"$".'type === \'note\' ? 15'));
$check('Reminder retains idempotent key',str_contains($sync,"'remind:'") && str_contains($service,'insertOrIgnore'));
$check('Topbar bell SVG preserved',str_contains($topbar,"@include('workspace-notifications._bell_icon')") && !str_contains($topbar,'🔔'));
$check('Topbar polls and retains CSRF form posts',str_contains($topbar,'workspace-notifications-poll.js') && str_contains($topbar,'@csrf'));
$check('Safe DOM rendering',str_contains($js,'textContent') && str_contains($js,'replaceChildren(') && !str_contains($js,'innerHTML'));
$check('Polling respects hidden tab',str_contains($js,'document.hidden') && str_contains($js,'30000'));
$check('One notification stylesheet',substr_count($get('resources/views/layouts/app.blade.php'),"@include('workspace-notifications._style')")===1);
$check('Settings form CSRF and PATCH',str_contains($settings,'@csrf @method(\'PATCH\')'));
$check('Sidebar nav remains General and Workspace',strpos($sidebar,'General & Workspace')!==false && strpos($sidebar,'Notifications')!==false && strpos($sidebar,'Administration')!==false && strpos($sidebar,'General & Workspace')<strpos($sidebar,'Notifications') && strpos($sidebar,'Notifications')<strpos($sidebar,'Administration'));
$check('Topbar icon design retained',str_contains($style,'.wsn-icon') && str_contains($style,'.wsn-settings-layout'));
echo "\nA7.4.11.2 static: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL.\n";
exit($fail?1:0);
