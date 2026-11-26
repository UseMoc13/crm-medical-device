@extends('layouts.app')
@section('title', 'Branch Details')
@section('content')
@php $active = strtolower((string) $branch->status) === 'active'; @endphp
<div class="page-head">
    <div><h1>Branch Details</h1><p>Review branch information, assigned users, and linked teams.</p></div>
    <div class="actions">
        <a href="{{ route('branches.index') }}" class="btn">← Back to Branches</a>
        <a href="{{ route('branches.edit', $branch) }}" class="btn primary">Edit Branch</a>
    </div>
</div>

<div class="branch-detail-grid">
    <section class="card branch-detail-card">
        <div class="card-head"><div><h3>Branch Information</h3><p>Identity and contact information for this branch.</p></div></div>
        <div class="card-body">
            <div class="branch-detail-identity">
                <span class="branch-avatar" aria-hidden="true">{{ strtoupper(substr((string) $branch->branch_name, 0, 1)) }}</span>
                <div class="branch-detail-primary">
                    <h2>{{ $branch->branch_name }}</h2>
                    <span>{{ $branch->branch_code }}</span>
                </div>
                <span class="branch-status {{ $active ? 'active' : 'inactive' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="branch-details">
                <div class="branch-detail-field"><span>Branch Code</span><strong>{{ $branch->branch_code }}</strong></div>
                <div class="branch-detail-field"><span>Phone Number</span><strong>{{ $branch->phone ?: 'Not provided' }}</strong></div>
                <div class="branch-detail-field wide"><span>Address</span><strong class="branch-address">{{ $branch->address ?: 'Not provided' }}</strong></div>
                <div class="branch-detail-field"><span>Created</span><strong>{{ $branch->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
                <div class="branch-detail-field"><span>Last Updated</span><strong>{{ $branch->updated_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
            </div>
        </div>
    </section>
    <section class="card branch-detail-card">
        <div class="card-head"><div><h3>Branch Summary</h3><p>Current CRM organization assignments.</p></div></div>
        <div class="card-body">
            <div class="branch-summary-line"><span>Assigned Users</span><strong>{{ number_format($counts['users']) }}</strong></div>
            <div class="branch-summary-line"><span>Linked Teams</span><strong>{{ number_format($counts['teams']) }}</strong></div>
            <div class="branch-summary-notice">{{ $counts['users'] + $counts['teams'] > 0 ? 'This branch cannot be deleted while linked to Users or Teams.' : 'There are no User or Team assignments. This branch can be deleted.' }}</div>
        </div>
    </section>
</div>

<section class="card branch-related-card">
    <div class="card-head branch-related-head">
        <div><h3>Assigned Users</h3><p>Accounts currently associated with this branch.</p></div>
        <a href="{{ route('users.index') }}" class="btn">View All Users</a>
    </div>
    <div class="card-body">
        <div class="table-wrap branch-table-wrap">
            <table class="branch-table branch-user-table">
                <thead><tr><th scope="col">User</th><th scope="col">Email</th><th scope="col">Primary Role</th><th scope="col">Status</th><th scope="col">Action</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td><a href="{{ route('users.show', $user) }}" class="branch-name-link"><strong>{{ $user->name }}</strong></a></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role?->role_name ?: '—' }}</td>
                            <td><span class="branch-status {{ strtolower((string) $user->status) === 'active' ? 'active' : 'inactive' }}">{{ ucfirst((string) $user->status) }}</span></td>
                            <td><a class="branch-row-link" href="{{ route('users.show', $user) }}">View User</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="branch-empty">No users are assigned to this branch.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="branch-pagination">{{ $users->links() }}</div>@endif
    </div>
</section>

<section class="card branch-related-card">
    <div class="card-head branch-related-head">
        <div><h3>Linked Teams</h3><p>Current teams associated with this branch (up to 10 shown).</p></div>
        <a href="{{ route('teams.index') }}" class="btn">Team Directory</a>
    </div>
    <div class="card-body">
        <div class="branch-team-list">
            @forelse($teams as $team)
                <div class="branch-team-row">
                    <span class="branch-team-icon" aria-hidden="true">T</span>
                    <div class="branch-team-info">
                        <a href="{{ route('teams.show', $team->team_id) }}" class="branch-name-link"><strong>{{ $team->team_name }}</strong></a>
                        <small>{{ $team->team_code }}</small>
                    </div>
                    <span class="branch-status {{ strtolower((string) $team->status) === 'active' ? 'active' : 'inactive' }}">{{ ucfirst((string) $team->status) }}</span>
                </div>
            @empty
                <div class="branch-empty">No teams are currently linked to this branch.</div>
            @endforelse
        </div>
    </div>
</section>
@include('branches._style')
@endsection
