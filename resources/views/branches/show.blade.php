@extends('layouts.app')
@section('title', 'Branch Details')
@section('content')
<div class="page-head">
    <div><h1>Branch Details</h1><p>Review branch identity, operational status, and linked Users and Teams.</p></div>
    <div class="actions">
        <a href="{{ route('branches.index') }}" class="btn">← Back to Branches</a>
        <a href="{{ route('branches.edit', $branch) }}" class="btn primary">Edit Branch</a>
    </div>
</div>
<div class="branch-detail-grid">
    <section class="card">
        <div class="card-head"><div><h3>Branch Information</h3><p>Office information and identification</p></div></div>
        <div class="card-body">
            <div class="branch-profile"><span class="branch-avatar" aria-hidden="true">{{ strtoupper(substr((string)$branch->branch_name,0,1)) }}</span><div><h2>{{ $branch->branch_name }}</h2><span class="branch-code">{{ $branch->branch_code }}</span></div><span class="branch-status {{ strtolower((string)$branch->status) === 'active' ? 'active' : 'inactive' }}">{{ ucfirst(strtolower((string)$branch->status)) }}</span></div>
            <div class="branch-details">
                <div><small>Branch Code</small><strong>{{ $branch->branch_code }}</strong></div>
                <div><small>Phone</small><strong>{{ $branch->phone ?: 'Not provided' }}</strong></div>
                <div class="branch-detail-wide"><small>Address</small><strong class="branch-address">{{ $branch->address ?: 'Not provided' }}</strong></div>
                <div><small>Created At</small><strong>{{ $branch->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
                <div><small>Last Updated</small><strong>{{ $branch->updated_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
            </div>
        </div>
    </section>
    <section class="card">
        <div class="card-head"><div><h3>Branch Summary</h3><p>Current organization assignments</p></div></div>
        <div class="card-body">
            <div class="branch-detail-metric"><span>Assigned Users</span><strong>{{ number_format($counts['users']) }}</strong></div>
            <div class="branch-detail-metric"><span>Linked Teams (including archived)</span><strong>{{ number_format($counts['teams']) }}</strong></div>
            <div class="branch-note">{{ $counts['users'] + $counts['teams'] > 0 ? 'This branch cannot be deleted until all Users and Teams are unlinked.' : 'This branch has no linked Users or Teams and may be deleted.' }}</div>
        </div>
    </section>
</div>
<section class="card branch-related-card">
    <div class="card-head"><div><h3>Assigned Users</h3><p>Accounts currently linked to this branch</p></div><a href="{{ route('users.index') }}" class="btn">View All Users</a></div>
    <div class="card-body">
        <div class="table-wrap branch-table-wrap"><table class="branch-table">
            <thead><tr><th>User</th><th>Email</th><th>Primary Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr><td><strong>{{ $user->name }}</strong></td><td>{{ $user->email }}</td><td>{{ $user->role?->role_name ?: '—' }}</td><td><span class="branch-status {{ strtolower((string)$user->status) === 'active' ? 'active' : 'inactive' }}">{{ ucfirst((string)$user->status) }}</span></td><td><a href="{{ route('users.show', $user) }}" class="branch-text-link">View User</a></td></tr>
            @empty
                <tr><td colspan="5"><div class="branch-empty">No users assigned to this branch.</div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($users->hasPages())<div class="branch-pagination">{{ $users->links() }}</div>@endif
    </div>
</section>
<section class="card branch-related-card">
    <div class="card-head"><div><h3>Linked Teams</h3><p>Up to 10 current teams associated with this branch</p></div><a href="{{ route('teams.index') }}" class="btn">Team Directory</a></div>
    <div class="card-body">
        <div class="branch-team-list">
        @forelse($teams as $team)
            <div class="branch-team-row"><span class="branch-mini-avatar" aria-hidden="true">T</span><div><a href="{{ route('teams.show', $team->team_id) }}" class="branch-name">{{ $team->team_name }}</a><small>{{ $team->team_code }}</small></div><span class="branch-status {{ $team->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($team->status) }}</span></div>
        @empty
            <div class="branch-empty">No current teams linked to this branch.</div>
        @endforelse
        </div>
    </div>
</section>
@include('branches._style')
@endsection
