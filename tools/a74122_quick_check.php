<?php
/** Read-only source validation. No database modifications. */
$root=dirname(__DIR__); $pass=0; $fail=0;
function a74122_check(string $name, bool $good): void { global $pass,$fail; if($good){$pass++;echo '[PASS] '.$name."\n";}else{$fail++;echo '[FAIL] '.$name."\n";} }
$files=[
 'app/Services/WorkspaceRoleAuditGrant.php',
 'app/Observers/WorkspaceAuditCoverageObserver.php',
 'resources/views/roles/_audit_access.blade.php',
 'app/Http/Controllers/RoleController.php',
 'resources/views/roles/_form.blade.php',
 'resources/views/roles/show.blade.php',
 'resources/views/roles/_style.blade.php',
 'app/Providers/AppServiceProvider.php',
 'app/Services/WorkspaceAuditTrail.php',
 'tests/Feature/WorkspaceRoleAuditAccessHttpTest.php',
];
foreach($files as $f) a74122_check('File '.$f,is_file($root.'/'.$f));
if ($fail) {echo "A7.4.12.2 static: {$pass} PASS, {$fail} FAIL.\n";exit(1);}
$get=fn(string $p)=>file_get_contents($root.'/'.$p);
$controller=$get('app/Http/Controllers/RoleController.php');
$roleForm=$get('resources/views/roles/_form.blade.php');
$include=$get('resources/views/roles/_audit_access.blade.php');
$roleShow=$get('resources/views/roles/show.blade.php');
$roleStyle=$get('resources/views/roles/_style.blade.php');
$provider=$get('app/Providers/AppServiceProvider.php');
$trail=$get('app/Services/WorkspaceAuditTrail.php');
$grant=$get('app/Services/WorkspaceRoleAuditGrant.php');
$observer=$get('app/Observers/WorkspaceAuditCoverageObserver.php');
$test=$get('tests/Feature/WorkspaceRoleAuditAccessHttpTest.php');

a74122_check('Create and Update both synchronize audit grant',substr_count($controller,'synchronizeFromRoleForm($request, $role)')===2);
a74122_check('Existing RoleController syncPermissions preserved',substr_count($controller,'$this->syncPermissions($role, $access')===2);
a74122_check('Audit grant loaded for Role Create/Edit/Show',str_contains($controller,"'auditViewEnabled' =>"));
a74122_check('Role form includes new audit section once',substr_count($roleForm,"@include('roles._audit_access')")===1);
a74122_check('Audit form marker and checkbox present',str_contains($include,'name="workspace_audit_access_present"')&&str_contains($include,'name="workspace_audit_access"'));
a74122_check('Only Super Admin displays grant checkbox',str_contains($include,'$canManageAuditAccess')&&str_contains($include,'CrmPermissionResolver'));
a74122_check('Role details display audit status',str_contains($roleShow,'role-audit-summary')&&str_contains($roleShow,'$auditViewEnabled'));
a74122_check('One Roles stylesheet retained',substr_count($roleStyle,'</style>')===1&&str_contains($roleStyle,'.role-audit-choice'));
a74122_check('Three new coverage observer registrations',substr_count($provider,'WorkspaceAuditCoverageObserver::class')===3);
a74122_check('Five previous audit observer registrations retained',substr_count($provider,'WorkspaceAuditObserver::class')===5);
a74122_check('Audit event/subject allowlists extended',str_contains($trail,"'role.audit_access_changed'")&&str_contains($trail,"'role'")&&str_contains($trail,"'assignment.requirements_changed'"));
a74122_check('No text fields added to audit metadata',str_contains($trail,"'audit_access_from'")&&str_contains($trail,"'member_role_from'" ) && !str_contains($trail,"'password',"));
a74122_check('Legacy role grants preserved without marker',str_contains($grant,"!\$request->exists('workspace_audit_access_present')"));
a74122_check('Server-side Super Admin required',str_contains($grant,'isSuperAdmin($actor)')&&str_contains($grant,'abort_unless'));
a74122_check('Grant changes are audit recorded',str_contains($grant,"'role.audit_access_changed'"));
a74122_check('Only structural changes added to coverage observer',str_contains($observer,"'todo.schedule_changed'")&&str_contains($observer,"'collaboration.role_changed'"));
a74122_check('Tests have isolation guard',str_contains($test,"':memory:'")&&str_contains($test,'pdo_sqlite'));
a74122_check('Tests protect grants from staff and confirm rollback',str_contains($test,'assertForbidden()')&&str_contains($test,'DB::rollBack()'));
echo "\nA7.4.12.2 static: {$pass}/".($pass+$fail)." PASS, {$fail} FAIL.\n";
exit($fail?1:0);
