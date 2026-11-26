@extends('layouts.app')
@section('title', 'Organization Structure')
@section('content')
<div class="page-head">
    <div>
        <h1>Organization Structure</h1>
        <p>Configure job reporting levels and direct managers without changing CRM permissions.</p>
    </div>
    <div class="actions">
        
{{-- A7105:roles.index --}}
@if(\App\Support\CrmViewAction::allows('roles.index'))
<a href="{{ route('roles.index') }}" class="btn">Manage Roles</a>
@endif
{{-- /A7105 --}}

        
{{-- A7105:users.index --}}
@if(\App\Support\CrmViewAction::allows('users.index'))
<a href="{{ route('users.index') }}" class="btn">Manage Users</a>
@endif
{{-- /A7105 --}}

    </div>
</div>

@if($errors->any())
    <div class="alert error" role="alert">
        <strong>Unable to update organization structure.</strong>
        <ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

<div class="org-overview">
    <div class="card org-stat"><div class="org-stat-symbol">R</div><div><span>Configured Roles</span><strong>{{ $roles->count() }}</strong><small>Job levels and reporting roles</small></div></div>
    <div class="card org-stat"><div class="org-stat-symbol indigo">U</div><div><span>Registered Users</span><strong>{{ $users->count() }}</strong><small>CRM accounts in the organization</small></div></div>
    <div class="card org-stat"><div class="org-stat-symbol amber">↗</div><div><span>Manager Relationships</span><strong>{{ $reporting->count() }}</strong><small>Individual direct-report links</small></div></div>
</div>

<div class="org-security-note">
    <span class="org-security-icon" aria-hidden="true">i</span>
    <div><strong>Organization structure is not permission inheritance.</strong>
        <p>Assigning a parent role or direct manager does not grant access to another user's Activities, TO-DO, customers, personal calendar, notes or attachments. Existing RBAC and row-scopes remain unchanged.</p>
    </div>
</div>

<div class="org-section-tabs">
    <a href="#roles">Role Hierarchy</a>
    <a href="#reporting">Direct Managers</a>
</div>

<section id="roles" class="card org-panel">
    <div class="card-head">
        <div><h3>Role Hierarchy</h3><p>Choose which role each job position reports to. Root roles can remain unassigned.</p></div>
        <span class="org-panel-meta">{{ $roles->count() }} roles</span>
    </div>
    <div class="card-body">
        <div class="org-help"><strong>How it works:</strong> For example, Sales Staff → Sales Manager → Director. This relationship is only organizational metadata; permission settings remain in Roles.</div>
        <div class="org-table-wrap">
            <table class="org-table">
                <thead><tr><th>Role</th><th>Reports to Role</th><th>Relationship</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($roles as $role)
                        @php
                            $protectedRole = (bool) ($role->is_system ?? false) || strtolower((string) $role->role_name) === 'super admin';
                            $parentRole = $role->parent_role_id ? $roleLookup->get($role->parent_role_id) : null;
                            $formId = 'org-role-' . $role->role_id;
                        @endphp
                        <tr>
                            <td><div class="org-identity"><span class="org-avatar">{{ strtoupper(substr($role->role_name, 0, 1)) }}</span><div><strong>{{ $role->role_name }}</strong><small>{{ $protectedRole ? 'Protected system role' : ($role->description ?: 'Standard CRM role') }}</small></div></div></td>
                            <td>
                                @if($protectedRole)
                                    <span class="org-muted">System-managed</span>
                                @else
                                    
{{-- A7105:organization.roles.parent --}}
@if(\App\Support\CrmViewAction::allows('organization.roles.parent'))
<form id="{{ $formId }}" method="POST" action="{{ route('organization.roles.parent', $role->role_id) }}">
                                        @csrf @method('PATCH')
                                        <label class="sr-only" for="parent-{{ $role->role_id }}">Parent role for {{ $role->role_name }}</label>
                                        <select id="parent-{{ $role->role_id }}" name="parent_role_id" class="org-select">
                                            <option value="">— Root / no parent —</option>
                                            @foreach($roles as $candidate)
                                                @continue((string) $candidate->role_id === (string) $role->role_id || (bool) ($candidate->is_system ?? false) || strtolower((string) $candidate->role_name) === 'super admin')
                                                <option value="{{ $candidate->role_id }}" @selected((string) $role->parent_role_id === (string) $candidate->role_id)>{{ $candidate->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
@endif
{{-- /A7105 --}}

                                @endif
                            </td>
                            <td>
                                @if($parentRole)
                                    <span class="org-link-pill">{{ $role->role_name }} <span>→</span> {{ $parentRole->role_name }}</span>
                                @else
                                    <span class="org-root-pill">{{ $protectedRole ? 'System' : 'Root level' }}</span>
                                @endif
                            </td>
                            <td>@unless($protectedRole)<button type="submit" class="btn org-save" form="{{ $formId }}">Save</button>@endunless</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty">No roles available yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section id="reporting" class="card org-panel">
    <div class="card-head">
        <div><h3>Direct Managers</h3><p>Assign the actual person each employee reports to, separately from the role hierarchy.</p></div>
        <span class="org-panel-meta">{{ $reporting->count() }} connected</span>
    </div>
    <div class="card-body">
        <div class="org-help"><strong>Selection rule:</strong> A manager must be active, belong to the same branch, and hold a role above the employee's primary role. Define Role Hierarchy first. A person cannot manage themselves or create a circular chain.</div>
        <div class="org-table-wrap">
            <table class="org-table org-users-table">
                <thead><tr><th>Employee</th><th>Primary Role / Branch</th><th>Direct Manager</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($users as $employee)
                        @php
                            $employeeId = (string) $employee->getKey();
                            $line = $reporting->get($employeeId);
                            $currentManagerId = (string) ($line?->manager_user_id ?? '');
                            $currentManager = $userLookup->get($currentManagerId);
                            $candidates = $eligibleManagers[$employeeId] ?? collect();
                            $isProtected = in_array($employeeId, $protectedUserIds, true);
                            $primaryRole = $roleLookup->get($hierarchy->primaryRoleId($employee));
                            $selectedValid = $currentManagerId === '' || $candidates->contains(fn ($candidate) => (string) $candidate->getKey() === $currentManagerId);
                            $formId = 'org-user-' . $employeeId;
                        @endphp
                        <tr>
                            <td><div class="org-identity"><span class="org-avatar indigo">{{ strtoupper(substr($employee->name ?? 'U', 0, 1)) }}</span><div><strong>{{ $employee->name }}</strong><small>{{ $employee->email }}</small></div></div></td>
                            <td><span class="org-role-name">{{ $primaryRole?->role_name ?? 'No primary role' }}</span><small class="org-muted">{{ $employee->branch?->branch_name ?? 'No branch' }} · {{ ucfirst((string) $employee->status) }}</small></td>
                            <td>
                                @if($isProtected)
                                    <span class="org-muted">Protected administrator</span>
                                @else
                                    
{{-- A7105:organization.users.manager --}}
@if(\App\Support\CrmViewAction::allows('organization.users.manager'))
<form id="{{ $formId }}" method="POST" action="{{ route('organization.users.manager', $employeeId) }}">
                                        @csrf @method('PATCH')
                                        <label class="sr-only" for="manager-{{ $employeeId }}">Direct manager for {{ $employee->name }}</label>
                                        <select id="manager-{{ $employeeId }}" name="manager_user_id" class="org-select">
                                            <option value="">— No direct manager —</option>
                                            @if($currentManagerId !== '' && !$selectedValid)
                                                <option value="{{ $currentManagerId }}" selected disabled>{{ $currentManager?->name ?? 'Unknown manager' }} (needs review)</option>
                                            @endif
                                            @foreach($candidates as $candidate)
                                                <option value="{{ $candidate->getKey() }}" @selected($currentManagerId === (string) $candidate->getKey())>{{ $candidate->name }} — {{ $candidate->roles->pluck('role_name')->join(', ') }}</option>
                                            @endforeach
                                        </select>
                                        @if($candidates->isEmpty() && $currentManagerId === '')<small class="org-muted">No eligible manager in the current hierarchy.</small>@endif
                                    </form>
@endif
{{-- /A7105 --}}

                                @endif
                            </td>
                            <td>@unless($isProtected)<button type="submit" class="btn org-save" form="{{ $formId }}">Save</button>@endunless</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty">No users found.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
<div class="org-footer-note">Next phase: Team Management and recipient selection by role, branch and team with per-user checkboxes. No automatic sharing is enabled at this stage.</div>
@include('organization._style')
@endsection
