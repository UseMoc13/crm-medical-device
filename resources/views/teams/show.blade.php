@extends('layouts.app')
@section('title', 'Team Details')
@section('content')
<div class="page-head">
    <div><h1>Team Details</h1><p>Review the team, its leader, participating users and membership status.</p></div>
    <div class="actions">
{{-- A7105:teams.index --}}
@if(\App\Support\CrmViewAction::allows('teams.index'))
<a href="{{ route('teams.index') }}" class="btn">← Back to Teams</a>
@endif
{{-- /A7105 --}}

{{-- A7105:teams.edit --}}
@if(\App\Support\CrmViewAction::allows('teams.edit'))
<a href="{{ route('teams.edit', $team) }}" class="btn primary">Edit Team</a>
@endif
{{-- /A7105 --}}
</div>
</div>
<div class="crm-team-detail-grid">
    <div class="card crm-team-panel">
        <div class="card-head"><div><h3>Team Information</h3><p>Identity and scope of this organizational team.</p></div><span class="crm-team-tag {{ $team->status === 'active' ? 'teal' : 'gray' }}">{{ ucfirst($team->status) }}</span></div>
        <div class="card-body">
            <div class="crm-team-detail-identity"><span class="crm-team-avatar">{{ strtoupper(substr($team->team_name,0,1)) }}</span><div><h3>{{ $team->team_name }}</h3><small>{{ $team->team_code }}</small></div></div>
            <div class="crm-team-data-grid">
                <div><span>Team Type</span><strong>{{ $team->team_type === 'project' ? 'Project / Cross-functional' : 'Permanent / Operational' }}</strong></div>
                <div><span>Base Branch</span><strong>{{ $team->branch?->branch_name ?? 'Cross-branch / Not assigned' }}</strong></div>
                <div><span>Team Leader</span><strong>{{ $team->leader?->name ?? 'Not assigned' }}</strong></div>
                <div><span>Active Members</span><strong>{{ $team->activeMemberships->count() }}</strong></div>
                <div><span>Start Date</span><strong>{{ $team->starts_on?->format('d M Y') ?? '—' }}</strong></div>
                <div><span>End Date</span><strong>{{ $team->ends_on?->format('d M Y') ?? '—' }}</strong></div>
                <div><span>Created By</span><strong>{{ $team->creator?->name ?? 'Former user' }}</strong></div>
                <div><span>Previously Left</span><strong>{{ $historicalCount }} member records</strong></div>
            </div>
            <div class="crm-team-description"><strong>Description</strong><p>{{ $team->description ?: 'No additional details provided.' }}</p></div>
        </div>
    </div>
    <div class="card crm-team-panel">
        <div class="card-head"><div><h3>Governance</h3><p>Team membership is separated from CRM access permissions.</p></div></div>
        <div class="card-body">
            <div class="crm-team-security in-card"><span>ⓘ</span><div><strong>Membership ≠ authorization</strong>Adding a user here does not grant access to customers, other people's TO-DO, Activities, or private files.</div></div>
            <p class="crm-team-side-note">Permanent teams require members from one branch. Project teams can include members from different branches. Team Leaders must be selected members.</p>
            @if($team->status === 'archived')
                
{{-- A7105:teams.destroy --}}
@if(\App\Support\CrmViewAction::allows('teams.destroy'))
<form method="POST" action="{{ route('teams.destroy', $team) }}" data-delete-name="{{ $team->team_name }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn crm-team-danger">Delete Archived Team</button>
                </form>
@endif
{{-- /A7105 --}}

                <small class="crm-team-muted">This soft-deletes the team from the directory and preserves its history. It cannot be used for new assignments.</small>
            @else
                
{{-- A7105:teams.edit --}}
@if(\App\Support\CrmViewAction::allows('teams.edit'))
<a href="{{ route('teams.edit',$team) }}" class="btn">Archive or Update Team</a>
@endif
{{-- /A7105 --}}

                <small class="crm-team-muted">Archive a team before using Delete.</small>
            @endif
        </div>
    </div>
</div>
<div class="card crm-team-panel">
    <div class="card-head"><div><h3>Team Members</h3><p>Selected people participating in this team, regardless of their CRM job role.</p></div><span class="crm-team-count">{{ $team->activeMemberships->count() }} members</span></div>
    <div class="card-body"><div class="crm-team-table-wrap"><table class="crm-team-table"><thead><tr><th>Member</th><th>Role(s)</th><th>Branch</th><th>Responsibility</th><th>Status</th></tr></thead><tbody>
    @forelse($team->activeMemberships->sortBy(fn ($membership) => strtolower((string) $membership->user?->name)) as $membership)
        @php $person = $membership->user; @endphp
        <tr>
            <td><div class="crm-team-person"><span class="crm-team-person-avatar">{{ strtoupper(substr($person?->name ?? 'U',0,1)) }}</span><div><strong>{{ $person?->name ?? 'Former user' }}</strong><small>{{ $person?->email ?? 'Unavailable' }}</small></div></div></td>
            <td>{{ $person?->roles?->pluck('role_name')->join(', ') ?: 'No role' }}</td>
            <td>{{ $person?->branch?->branch_name ?? 'No branch' }}</td>
            <td>@if((string) $person?->getKey() === (string) $team->leader_user_id)<span class="crm-team-tag indigo">Team Leader</span>@else<span class="crm-team-tag gray">Member</span>@endif</td>
            <td><span class="crm-team-tag {{ strtolower((string)$person?->status)==='active' ? 'teal' : 'gray' }}">{{ ucfirst($person?->status ?? 'Unknown') }}</span></td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="crm-team-empty"><strong>No members assigned</strong><p>Edit this team to add its first member.</p></div></td></tr>
    @endforelse
    </tbody></table></div></div>
</div>
@include('teams._style')
@endsection
