<?php
/** Read-only runtime check: never writes, seeds or resets user data. */
declare(strict_types=1);
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass=0;$fail=0;
$check=static function (string $name,bool $ok) use (&$pass,&$fail):void {
    echo ($ok?'[PASS] ':'[FAIL] ').$name.PHP_EOL;$ok?$pass++:$fail++;
};
$schema=Illuminate\Support\Facades\Schema::class;
$table='workspace_notification_preferences';
$check('Notification inbox exists',$schema::hasTable('workspace_notifications'));
$check('Preference table exists',$schema::hasTable($table));
foreach (['user_id','assignment_enabled','collaboration_enabled','submission_enabled','review_enabled',
    'reminder_note_enabled','reminder_calendar_enabled','reminder_activity_enabled',
    'reminder_todo_enabled','reminder_lead_minutes','created_at','updated_at'] as $column) {
    $check('Preference column '.$column,$schema::hasTable($table) && $schema::hasColumn($table,$column));
}
foreach (['workspace-notifications.poll'=>'GET',
    'workspace-notifications.settings'=>'GET',
    'workspace-notifications.settings.update'=>'PATCH'] as $name=>$method) {
    $record=Illuminate\Support\Facades\Route::getRoutes()->getByName($name);
    $check('Route '.$name.' ['.$method.']',$record !== null && in_array($method,$record->methods(),true));
}
$check('Existing reminder command registered',array_key_exists('workspace:reminders:sync', Illuminate\Support\Facades\Artisan::all()));
$check('Preference rows queryable',$schema::hasTable($table) && Illuminate\Support\Facades\DB::table($table)->count()>=0);
echo "\nA7.4.11.2 runtime: {$pass}/".($pass+$fail)." PASS; {$fail} FAIL. Read-only.\n";
exit($fail?1:0);
