<?php
/** Read-only PostgreSQL route/schema check. Never inserts or resets. */
$root=dirname(__DIR__);
require $root.'/vendor/autoload.php';
$app=require $root.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$pass=0;$fail=0;
$check=function(string $label,bool $ok)use(&$pass,&$fail):void{
  if($ok){$pass++;echo "[PASS] {$label}\n";}else{$fail++;echo "[FAIL] {$label}\n";}
};
$schema=\Illuminate\Support\Facades\Schema::class;
$check('Existing roles table', $schema::hasTable('roles'));
$check('Existing permissions table', $schema::hasTable('permissions'));
$check('Existing role_permissions table', $schema::hasTable('role_permissions'));
$check('Existing workspace audit event table', $schema::hasTable('workspace_audit_events'));
if($schema::hasTable('permissions')) {
 $check('workspace_audit.view permission available',\Illuminate\Support\Facades\DB::table('permissions')->where('permission_name','workspace_audit.view')->exists());
}
$routes=\Illuminate\Support\Facades\Route::getRoutes();
foreach(['roles.create'=>'GET','roles.edit'=>'GET','roles.show'=>'GET','roles.store'=>'POST','roles.update'=>'PUT'] as $name=>$method){
  $route=$routes->getByName($name);
  $check('Route '.$name.' ['.$method.']',$route!==null&&in_array($method,$route->methods(),true));
}
$check('Audit events allowlist active',in_array('role.audit_access_changed',\App\Services\WorkspaceAuditTrail::EVENTS,true));
$check('Audit grant service ready',class_exists(\App\Services\WorkspaceRoleAuditGrant::class));
$check('Coverage observer ready',class_exists(\App\Observers\WorkspaceAuditCoverageObserver::class));
echo "\nA7.4.12.2 runtime: {$pass}/".($pass+$fail)." PASS, {$fail} FAIL. Read-only.\n";
exit($fail?1:0);
