@php
    $auditActor = auth('web')->user();
    $canManageAuditAccess = $auditActor && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin($auditActor);
    $auditEnabled = old('workspace_audit_access_present') === '1'
        ? old('workspace_audit_access') === '1'
        : (bool) ($auditViewEnabled ?? false);
@endphp
<section class="role-form-section role-audit-permission" aria-labelledby="roleAuditHeading">
    <div class="role-section-heading">
        <h4 id="roleAuditHeading">Audit Trail Permission</h4>
        <p>Read-only access to sanitized Workspace Audit Logs. This does not grant access to private Notes or task files.</p>
    </div>
    @if($canManageAuditAccess)
        <input type="hidden" name="workspace_audit_access_present" value="1">
        <label class="role-audit-choice" for="roleAuditView">
            <input type="checkbox" id="roleAuditView" name="workspace_audit_access" value="1" @checked($auditEnabled)>
            <span class="role-audit-choice-text">
                <strong>View Audit Logs</strong>
                <small>Allow this role to open Administration → Audit Logs. Only Super Admin can change this permission.</small>
            </span>
        </label>
    @else
        <div class="role-audit-readonly">
            <strong>{{ $auditEnabled ? 'Enabled' : 'Not granted' }}</strong>
            <span>Only Super Admin can change this sensitive permission. Other role settings remain editable according to your existing access rules.</span>
        </div>
    @endif
    @error('workspace_audit_access')<p class="role-audit-error">{{ $message }}</p>@enderror
</section>
