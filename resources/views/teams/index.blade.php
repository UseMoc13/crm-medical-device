@extends('layouts.app')
@section('title', 'Teams')
@section('content')
<div class="page-head">
    <div><h1>Teams</h1><p>Manage permanent teams, project groups, leaders and membership across the CRM organization.</p></div>
    <div class="actions"><a href="{{ route('organization.index') }}" class="btn">Organization Structure</a><a href="{{ route('teams.create') }}" class="btn primary">+ New Team</a></div>
</div>
<div class="crm-team-stats">
    <div class="card crm-team-stat"><span>Total Teams</span><strong>{{ $summary['total'] }}</strong><small>Non-deleted teams</small></div>
    <div class="card crm-team-stat"><span>Active Teams</span><strong>{{ $summary['active'] }}</strong><small>Available for membership</small></div>
    <div class="card crm-team-stat"><span>Permanent</span><strong>{{ $summary['permanent'] }}</strong><small>Branch operational teams</small></div>
    <div class="card crm-team-stat"><span>Project Teams</span><strong>{{ $summary['project'] }}</strong><small>Cross-functional work</small></div>
</div>
<div class="crm-team-security"><span aria-hidden="true">ⓘ</span><div><strong>Team membership does not grant CRM record access.</strong> Activities, TO-DO, customer data and attachments still require their own authorization. Team management is restricted to Super Admin in A7.4.6.</div></div>
<div class="card crm-team-panel">
    <div class="card-head"><div><h3>Team Directory</h3><p>{{ $teams->total() }} teams matching your search and filters.</p></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('teams.index') }}" class="crm-team-filters">
            <div class="crm-team-field crm-team-search"><label for="teamSearch">Search</label><input id="teamSearch" name="search" type="search" value="{{ request('search') }}" placeholder="Search team name or code..." maxlength="150"></div>
            <div class="crm-team-field"><label for="teamTypeFilter">Type</label><select id="teamTypeFilter" name="type"><option value="">All Types</option><option value="permanent" @selected(request('type')==='permanent')>Permanent</option><option value="project" @selected(request('type')==='project')>Project</option></select></div>
            <div class="crm-team-field"><label for="teamStatusFilter">Status</label><select id="teamStatusFilter" name="status"><option value="">All Statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="archived" @selected(request('status')==='archived')>Archived</option></select></div>
            <div class="crm-team-field"><label for="teamBranchFilter">Branch</label><select id="teamBranchFilter" name="branch_id"><option value="">All Branches</option>@foreach($branches as $branch)<option value="{{ $branch->branch_id }}" @selected(request('branch_id')===$branch->branch_id)>{{ $branch->branch_name }}</option>@endforeach</select></div>
            <div class="crm-team-filter-actions"><button type="submit" class="btn primary">Filter</button><a href="{{ route('teams.index') }}" class="btn">Reset</a></div>
        </form>
        <div class="crm-team-table-wrap"><table class="crm-team-table"><thead><tr><th>Team</th><th>Type</th><th>Branch</th><th>Team Leader</th><th>Members</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($teams as $team)
            <tr>
                <td><a href="{{ route('teams.show', $team) }}" class="crm-team-title">{{ $team->team_name }}</a><small class="crm-team-subtitle">{{ $team->team_code }}</small></td>
                <td><span class="crm-team-tag {{ $team->team_type === 'project' ? 'indigo' : 'teal' }}">{{ ucfirst($team->team_type) }}</span></td>
                <td>{{ $team->branch?->branch_name ?? ($team->team_type === 'project' ? 'Cross-branch' : 'Unassigned') }}</td>
                <td>{{ $team->leader?->name ?? 'Not assigned' }}</td>
                <td><strong>{{ $team->active_members_count }}</strong></td>
                <td><span class="crm-team-tag {{ $team->status === 'active' ? 'teal' : 'gray' }}">{{ ucfirst($team->status) }}</span></td>
                <td><div class="crm-team-actions"><a class="crm-team-action" href="{{ route('teams.show',$team) }}" title="View">View</a><a class="crm-team-action" href="{{ route('teams.edit',$team) }}" title="Edit">Edit</a></div></td>
            </tr>
        @empty
            <tr><td colspan="7"><div class="crm-team-empty"><strong>No teams found</strong><p>Create a team or adjust the search and filters.</p><a href="{{ route('teams.create') }}" class="btn primary">+ New Team</a></div></td></tr>
        @endforelse
        </tbody></table></div>
        @if($teams->hasPages())<div class="crm-team-pagination">{{ $teams->links() }}</div>@endif
    </div>
</div>
@include('teams._style')
@endsection
