<?php
/** Read-only inspection. Absolutely no database reset, insertion, or alteration. */
declare(strict_types=1);
$root=dirname(__DIR__);
if(!is_file($root.'/vendor/autoload.php')){fwrite(STDERR,"[FAIL] Laravel vendor/autoload.php not found.\n");exit(1);}
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$ok=0;$fail=0;
$check=function(string $label,bool $pass) use(&$ok,&$fail):void{
 echo ($pass?'[PASS] ':'[FAIL] ').$label.PHP_EOL;$pass?$ok++:$fail++;
};
try {
 $check('Existing personal notes',\Illuminate\Support\Facades\Schema::hasTable('workspace_personal_notes'));
 $check('Note revision column',\Illuminate\Support\Facades\Schema::hasColumn('workspace_personal_notes','revision'));
 $check('Mention registry',\Illuminate\Support\Facades\Schema::hasTable('workspace_note_mentions'));
 foreach(['mention_id','note_id','target_type','target_id'] as $name){$check("Mention field {$name}",\Illuminate\Support\Facades\Schema::hasColumn('workspace_note_mentions',$name));}
} catch(\Throwable $e){$check('Database/schema connectivity',false);}
foreach([
 'notes.floating.index'=>'GET', 'notes.floating.create'=>'POST',
 'notes.floating.show'=>'GET', 'notes.floating.save'=>'PATCH', 'notes.floating.destroy'=>'DELETE',
 'notes.mention-options'=>'GET', 'notes.index'=>'GET',
 'activities.show'=>'GET','todos.show'=>'GET','calendar.show'=>'GET',
] as $name=>$method){
 $route=\Illuminate\Support\Facades\Route::getRoutes()->getByName($name);
 $check("Route {$name} [{$method}]",$route!==null&&in_array($method,$route->methods(),true));
}
echo "\nA7.4.10.1 runtime: {$ok}/".($ok+$fail)." PASS, {$fail} FAIL. Read-only.\n";
exit($fail?1:0);
