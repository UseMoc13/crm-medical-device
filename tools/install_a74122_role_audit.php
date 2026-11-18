<?php
/** A7.4.12.2 additive patch installer: preserves role CRUD, scope controls and audit history. */
$root = dirname(__DIR__);
$changes = []; $errors = [];
function a74122_edit(string $relative, callable $transform): void {
    global $root,$changes,$errors;
    $path=$root.'/'.$relative;
    if (!is_file($path)) { $errors[]="Missing existing file: {$relative}"; return; }
    $before=file_get_contents($path);
    if ($before===false) { $errors[]="Unreadable: {$relative}"; return; }
    $after=$transform($before);
    if ($after===null) { $errors[]="Unsupported local layout in {$relative}; no files changed."; return; }
    if ($before!==$after) $changes[$relative]=['before'=>$before,'after'=>$after];
}
function a74122_replace_once(string $s, string $needle, string $replacement): ?string {
    if (substr_count($s,$needle)!==1) return null;
    return str_replace($needle,$replacement,$s);
}

// Preserve ALL existing RoleController logic; integrate the separate guarded grant service.
a74122_edit('app/Http/Controllers/RoleController.php', function(string $s): ?string {
    $marker='WorkspaceRoleAuditGrant::class';
    if (str_contains($s,$marker)) {
        return substr_count($s,'synchronizeFromRoleForm($request, $role)')===2
            && substr_count($s,"'auditViewEnabled' =>")===1 ? $s : null;
    }
    $call='$this->syncPermissions($role, $access, $this->requestedWorkflowAccess($request, $access), $this->requestedScopeAccess($request));';
    if (substr_count($s,$call)!==2) return null;
    $s=str_replace($call, $call."\n            app(\\App\\Services\\WorkspaceRoleAuditGrant::class)->synchronizeFromRoleForm(\$request, \$role);",$s);
    $return="return ['scopePages'=>";
    if (substr_count($s,$return)!==1) return null;
    $s=str_replace($return,"return ['auditViewEnabled' => \$role ? app(\\App\\Services\\WorkspaceRoleAuditGrant::class)->isGranted(\$role) : false, 'scopePages'=>",$s);
    return $s;
});

// Place the permission control INSIDE the main editor column, not a third grid column.
a74122_edit('resources/views/roles/_form.blade.php', function(string $s): ?string {
    $include="@include('roles._audit_access')";
    if (str_contains($s,$include)) return substr_count($s,$include)===1?$s:null;
    $anchor='                <aside class="role-editor-aside"';
    if (substr_count($s,$anchor)!==1) return null;
    $prefix=substr($s,0,strpos($s,$anchor));
    // Need to be after closing Access Configuration and before closing main column.
    $pattern='~(</section>\s*)(</div>\s*)$~s';
    if (!preg_match($pattern,$prefix)) return null;
    $prefix=preg_replace($pattern,"</section>\n                    ".$include."\n                </div>\n",$prefix,1);
    return $prefix.substr($s,strpos($s,$anchor));
});

a74122_edit('resources/views/roles/show.blade.php', function(string $s): ?string {
    if (str_contains($s,'role-audit-summary')) return substr_count($s,'role-audit-summary')===1?$s:null;
    $anchor='<div class="card role-detail-card" style="margin-top:20px">';
    if (substr_count($s,$anchor)!==1) return null;
    $section=<<<'BLADE'
<div class="card role-detail-card role-audit-summary" style="margin-top:20px">
    <div class="card-head"><div><h3>Audit Trail Access</h3><p>Role-level read-only permission for Workspace Audit Logs.</p></div></div>
    <div class="card-body">
        <strong class="role-audit-status">{{ $isProtected ? 'Automatic: Super Admin' : (($auditViewEnabled ?? false) ? 'Enabled' : 'Not granted') }}</strong>
        <p class="role-help" style="margin:9px 0 0">Only Super Admin can grant or revoke this permission in the Role Editor. No access to personal Notes content or submission files is provided.</p>
    </div>
</div>
BLADE;
    return str_replace($anchor,$section."\n".$anchor,$s);
});

a74122_edit('resources/views/roles/_style.blade.php', function(string $s): ?string {
    if (str_contains($s,'.role-audit-choice')) return substr_count($s,'.role-audit-choice')>=1?$s:null;
    if (substr_count($s,'</style>')!==1) return null;
    $css=<<<'CSS'
/* A7.4.12.2 — Role Audit Access (single Roles stylesheet) */
.role-audit-permission {padding-bottom:10px}
.role-audit-choice {display:flex;align-items:flex-start;gap:13px;padding:18px;border:1px solid #cfdfed;background:#f6fafc;border-radius:10px;cursor:pointer}
.role-audit-choice:focus-within {outline:2px solid #24978d;outline-offset:2px}
.role-audit-choice input[type=checkbox] {width:18px;height:18px;accent-color:#15978f;flex-shrink:0;margin:2px 0 0}
.role-audit-choice-text {display:grid;gap:6px;min-width:0}
.role-audit-choice-text strong,.role-audit-readonly strong,.role-audit-status {font-size:13px;font-weight:700;color:#17284f}
.role-audit-choice-text small,.role-audit-readonly span {font-size:12px;line-height:1.55;color:#64748b}
.role-audit-readonly {border:1px solid #e0e8ef;border-radius:9px;padding:16px;display:grid;gap:7px;background:#f8fafc}
.role-audit-error {margin-top:9px;color:#c33f54;font-size:12px}
CSS;
    return str_replace('</style>',$css."\n</style>",$s);
});

a74122_edit('app/Providers/AppServiceProvider.php', function(string $s): ?string {
    $target='WorkspaceAuditCoverageObserver::class';
    if (str_contains($s,$target)) return substr_count($s,$target)===3?$s:null;
    $anchor='\\App\\Models\\WorkspaceNotificationPreference::observe(\\App\\Observers\\WorkspaceAuditObserver::class);';
    if (substr_count($s,$anchor)!==1) return null;
    $more=<<<'PHP'
        // A7.4.12.2: structured, text-free coverage for schedule and access-rule changes.
        \App\Models\WorkspaceTaskAssignment::observe(\App\Observers\WorkspaceAuditCoverageObserver::class);
        \App\Models\WorkspaceCollaborationMember::observe(\App\Observers\WorkspaceAuditCoverageObserver::class);
        \App\Models\WorkspaceTask::observe(\App\Observers\WorkspaceAuditCoverageObserver::class);
PHP;
    return str_replace($anchor,$anchor."\n".$more,$s);
});

a74122_edit('app/Services/WorkspaceAuditTrail.php', function(string $s): ?string {
    if (str_contains($s,"'role.audit_access_changed'")) {
        return str_contains($s,"'assignment.requirements_changed'") && str_contains($s,"'member_role_from'")
            && str_contains($s,"'audit_access_from'") && str_contains($s,"'role'") ? $s : null;
    }
    $a="        'notification_preferences.updated',";
    $b="    public const SUBJECTS = ['todo', 'assignment', 'activity', 'notification_preferences'];";
    $c="        'changed_settings',";
    if (substr_count($s,$a)!==1 || substr_count($s,$b)!==1 || substr_count($s,$c)!==1) return null;
    $s=str_replace($a,$a."\n        'role.audit_access_changed', 'assignment.requirements_changed',\n        'collaboration.role_changed', 'todo.schedule_changed',",$s);
    $s=str_replace($b,"    public const SUBJECTS = ['todo', 'assignment', 'activity', 'notification_preferences', 'role'];",$s);
    return str_replace($c,$c."\n        'audit_access_from', 'audit_access_to', 'member_role_from', 'member_role_to',",$s);
});

foreach ([
 'app/Services/WorkspaceRoleAuditGrant.php',
 'app/Observers/WorkspaceAuditCoverageObserver.php',
 'resources/views/roles/_audit_access.blade.php',
 'tests/Feature/WorkspaceRoleAuditAccessHttpTest.php',
 'tools/a74122_quick_check.php',
 'tools/a74122_runtime_preflight.php',
] as $relative) if (!is_file($root.'/'.$relative)) $errors[]='Missing new patch file: '.$relative;
if ($errors) { foreach($errors as $error) fwrite(STDERR,"ERROR: {$error}\n"); fwrite(STDERR,"No tracked files changed by this run.\n"); exit(1); }
if (!$changes) { echo "[PASS] A7.4.12.2 already integrated. Nothing duplicated.\n"; exit(0); }
$stamp=date('Ymd-His').'-'.bin2hex(random_bytes(4));
$backupRel='tools/a74122_backups/'.$stamp;
$applied=[];
try {
    foreach($changes as $file=>$diff) {
        $backup=$root.'/'.$backupRel.'/'.$file;
        if (!is_dir(dirname($backup)) && !mkdir(dirname($backup),0775,true) && !is_dir(dirname($backup)))
            throw new \RuntimeException('Unable to create backup for '.$file);
        if (file_put_contents($backup,$diff['before'],LOCK_EX)===false)
            throw new \RuntimeException('Unable to back up '.$file);
    }
    foreach($changes as $file=>$diff) {
        $path=$root.'/'.$file; $tmp=$path.'.a74122.tmp';
        if (file_put_contents($tmp,$diff['after'],LOCK_EX)===false || !rename($tmp,$path)) {
            @unlink($tmp); throw new \RuntimeException('Failed to write '.$file);
        }
        $applied[]=$file; echo '[PASS] Integrated: '.$file."\n";
    }
} catch (\Throwable $error) {
    foreach($applied as $file) file_put_contents($root.'/'.$file,$changes[$file]['before'],LOCK_EX);
    fwrite(STDERR,'ERROR: '.$error->getMessage().". Applied files restored.\n"); exit(1);
}
echo '[PASS] A7.4.12.2 integrated. Backup: '.$backupRel."\n";
echo "No database migration or reset required.\n";
