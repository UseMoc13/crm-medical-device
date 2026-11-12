@extends('layouts.app')
@section('title', 'Role Details')
@section('content')
@php
    $roleUsers = $role->users()->orderBy('name')->get();
    $groupedPages = [];
    foreach ($accessPages as $slug => $page) {
        $key = $page['group'];
        if (!isset($groupedPages[$key])) $groupedPages[$key] = ['label' => $page['group_label'], 'pages' => []];
        $groupedPages[$key]['pages'][$slug] = $page;
    }
    $allowedPages = collect($accessPages)->filter(fn ($page, $slug) => count($accessSelected[$slug] ?? []) > 0)->count();
    $allowedPermissions = collect($accessSelected)->sum(fn ($actions) => count($actions ?? []));
    $isProtected = (bool) ($role->is_system ?? false);
@endphp
<div class="page-head">
    <div><h1>Role Details</h1><p>Review role information, assigned users, and access permissions.</p></div>
    <div class="actions"><a href="{{ route('roles.index') }}" class="btn">← Back to Roles</a>
        @unless($isProtected)<a href="{{ route('roles.edit', $role) }}" class="btn primary">Edit Role</a>@endunless
    </div>
</div>
<div class="role-detail-layout">
    <div class="card role-detail-card">
        <div class="card-head"><div><h3>Role Information</h3><p>Identity, description, and activity of this role.</p></div></div>
        <div class="card-body">
            <div class="role-detail-summary"><span class="role-detail-icon" aria-hidden="true">{{ strtoupper(substr($role->role_name, 0, 1)) }}</span><div><strong>{{ $role->role_name }}</strong><small>{{ $isProtected ? 'Protected system role' : 'Customizable CRM role' }}</small></div></div>
            <div class="role-detail-info-grid" style="margin-top:23px">
                <div class="role-detail-field"><span class="role-detail-label">Assigned Users</span><strong class="role-detail-value">{{ $roleUsers->count() }} {{ $roleUsers->count() === 1 ? 'User' : 'Users' }}</strong></div>
                <div class="role-detail-field"><span class="role-detail-label">Role Type</span><strong class="role-detail-value">{{ $isProtected ? 'System Role' : 'Standard Role' }}</strong></div>
                <div class="role-detail-field wide"><span class="role-detail-label">Description</span><span class="role-detail-value {{ $role->description ? '' : 'muted' }}">{{ $role->description ?: 'No description provided.' }}</span></div>
                <div class="role-detail-field"><span class="role-detail-label">Created</span><span class="role-detail-value">{{ $role->created_at?->format('d M Y, H:i') ?? '—' }}</span></div>
                <div class="role-detail-field"><span class="role-detail-label">Last Updated</span><span class="role-detail-value">{{ $role->updated_at?->format('d M Y, H:i') ?? '—' }}</span></div>
            </div>
        </div>
    </div>
    <div class="card role-detail-card">
        <div class="card-head"><div><h3>Assigned Users</h3><p>Users who currently have this role.</p></div><span class="role-detail-counter">{{ $roleUsers->count() }} Users</span></div>
        <div class="card-body">
            @forelse($roleUsers as $user)
                @if($loop->first)<div class="role-detail-users">@endif
                    <div class="role-detail-user"><span class="role-detail-user-icon" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</span><div class="role-detail-user-info"><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div><span class="role-detail-status {{ strtolower((string)$user->status) === 'active' ? 'active' : '' }}">{{ ucfirst((string) $user->status) }}</span></div>
                @if($loop->last)</div>@endif
            @empty
                <div class="role-detail-empty">No users assigned to this role yet.</div>
            @endforelse
        </div>
    </div>
</div>
<div class="card role-detail-card" style="margin-top:20px">
    <div class="card-head"><div><h3>Module & Page Permissions</h3><p>Permissions granted to this role, grouped by CRM module.</p></div><span class="role-detail-counter">{{ $allowedPages }} Pages · {{ $allowedPermissions }} Permissions</span></div>
    <div class="card-body">
        @if($isProtected)
            <p class="role-help" style="margin:0 0 14px">This protected system role receives full access through the Super Admin authorization policy. The matrix below lists explicit permission records only.</p>
        @endif
        <div class="role-detail-permission-grid">
        @foreach($groupedPages as $group)
            @php $groupCount = collect($group['pages'])->sum(fn ($page, $slug) => count($accessSelected[$slug] ?? [])); @endphp
            <div class="role-detail-module">
                <div class="role-detail-module-title"><span>{{ $group['label'] }}</span><span class="role-detail-module-count">{{ $groupCount }} permissions enabled</span></div>
                <div class="role-detail-matrix-wrap"><table class="role-detail-matrix"><thead><tr><th scope="col">Page / Feature</th>@foreach($accessActions as $action)<th scope="col">{{ $action === 'update' ? 'Edit' : ucfirst($action) }}</th>@endforeach</tr></thead><tbody>
                    @foreach($group['pages'] as $slug => $page)
                        <tr><th scope="row">{{ $page['label'] }}</th>@foreach($accessActions as $action)<td>@if(in_array($action, $accessSelected[$slug] ?? [], true))<span class="role-detail-allowed" aria-label="Allowed">✓</span>@else<span class="role-detail-denied" aria-label="Not allowed">—</span>@endif</td>@endforeach</tr>
                    @endforeach
                </tbody></table></div>
            </div>
        @endforeach
    </div></div>
</div>
@include('roles._style')
@endsection
