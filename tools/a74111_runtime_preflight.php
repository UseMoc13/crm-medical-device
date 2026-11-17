<?php
/** Read-only production runtime preflight. Never seeds, migrates or resets data. */
declare(strict_types=1);
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ok=0;$fail=0;
$check=static function(string $name,bool $pass)use(&$ok,&$fail):void {echo ($pass?'[PASS] ':'[FAIL] ').$name.PHP_EOL;$pass?$ok++:$fail++;};
$schema=Illuminate\Support\Facades\Schema::class;
$check('Notification table exists',$schema::hasTable('workspace_notifications'));
foreach(['notification_id','recipient_user_id','actor_user_id','kind','title','message','target_type','target_id','dedupe_key','read_at','created_at'] as $col) {
    $check('Column '.$col,$schema::hasTable('workspace_notifications') && $schema::hasColumn('workspace_notifications',$col));
}
foreach(['workspace-notifications.index'=>'GET', 'workspace-notifications.open'=>'POST','workspace-notifications.read'=>'PATCH','workspace-notifications.read-all'=>'PATCH'] as $route=>$verb) {
    $record=Illuminate\Support\Facades\Route::getRoutes()->getByName($route);
    $check('Route '.$route.' ['.$verb.']',$record && in_array($verb,$record->methods(),true));
}
$check('Reminders command registered',array_key_exists('workspace:reminders:sync', Illuminate\Support\Facades\Artisan::all()));
$read=Illuminate\Support\Facades\DB::table('workspace_notifications')->whereNotNull('read_at')->count();
$check('Read-state rows queryable',$read>=0);
echo "\nA7.4.11.1 runtime: {$ok}/".($ok+$fail)." PASS; {$fail} FAIL. Read-only.\n";
exit($fail?1:0);
