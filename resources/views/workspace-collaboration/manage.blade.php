@extends('layouts.app')
@section('title', 'Manage Collaborators')
@section('content')
@php
    $prefix = $entityType === 'activity' ? 'activities' : 'todos';
    $routeKey = $entityType === 'activity' ? 'activity' : 'todo';
    $routeParams = [$routeKey => $entity->getKey()];
    $title = $entityType === 'activity' ? $entity->subject : $entity->title;
@endphp
<div class="page-head"><div><h1>Manage Collaborators</h1><p>{{ $title }} · Assign explicit Viewer or Contributor access.</p></div><div class="actions"><a href="{{ route($prefix.'.show', $routeParams) }}" class="btn">&larr; Back to Details</a></div></div>
@include('workspace-collaboration._style')
<div class="a748-shell">
    @if($errors->any())<div class="a748-alert"><strong>Unable to save changes.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="card" style="margin-bottom:19px">
        <div class="card-head"><div><h3>Active Collaborators</h3><p>{{ $members->count() }} / 20 explicit collaborators. Assignees remain managed separately in the TO-DO editor.</p></div></div>
        <div class="a748-body">
            <div class="a748-table-wrap"><table class="a748-table"><thead><tr><th>User</th><th>Access</th><th>Added</th><th>Action</th></tr></thead><tbody>
                @forelse($members as $member)
                    <tr><td><strong>{{ $member->user?->name ?? 'Unknown user' }}</strong><br><span class="a748-mini">{{ $member->user?->email }}</span></td><td><span class="a748-badge">{{ ucfirst($member->access_role) }}</span></td><td>{{ $member->created_at?->format('d M Y') ?? '-' }}</td><td><form method="POST" action="{{ route($prefix.'.collaboration.remove', $routeParams + ['member'=>$member->getKey()]) }}" data-delete-name="{{ $member->user?->name ?? 'collaborator' }}">@csrf @method('DELETE')<button type="submit" class="btn a748-action">Revoke</button></form></td></tr>
                @empty
                    <tr><td colspan="4"><div class="a748-empty">Nobody has been invited yet. Add a collaborator from the directory below.</div></td></tr>
                @endforelse
            </tbody></table></div>
        </div>
    </section>
    <section class="card">
        <div class="card-head"><div><h3>Recipient Directory</h3><p>Search and filter by role, branch, and team. These filters never override actual assignment permissions.</p></div></div>
        <div class="a748-body">
            <form class="a748-form" method="GET" action="{{ route($prefix.'.collaboration.manage', $routeParams) }}" style="margin-bottom:19px">
                <div class="a748-filter-grid">
                    <div><label for="a748-search">Find person</label><input type="search" id="a748-search" name="search" value="{{ request('search') }}" placeholder="Name / email" maxlength="150"></div>
                    <div><label for="a748-role">Role</label><select name="role_id" id="a748-role"><option value="">All Roles</option>@foreach($filters['roles'] as $option)<option value="{{ $option->role_id }}" @selected(request('role_id') === $option->role_id)>{{ $option->role_name }}</option>@endforeach</select></div>
                    <div><label for="a748-branch">Branch</label><select name="branch_id" id="a748-branch"><option value="">All Branches</option>@foreach($filters['branches'] as $option)<option value="{{ $option->branch_id }}" @selected(request('branch_id') === $option->branch_id)>{{ $option->branch_name }}</option>@endforeach</select></div>
                    <div><label for="a748-team">Team</label><select name="team_id" id="a748-team"><option value="">All Teams</option>@foreach($filters['teams'] as $option)<option value="{{ $option->team_id }}" @selected(request('team_id') === $option->team_id)>{{ $option->team_name }}</option>@endforeach</select></div>
                </div>
                <div class="a748-row"><button class="btn" type="submit">Apply Filters</button><a class="btn" href="{{ route($prefix.'.collaboration.manage', $routeParams) }}">Reset</a><span class="a748-mini">20 per page · {{ $people->total() }} eligible people</span></div>
            </form>
            <div class="a748-table-wrap"><table class="a748-table"><thead><tr><th>Person</th><th>Branch</th><th>Role(s)</th><th>Permission</th><th>Action</th></tr></thead><tbody>
                @forelse($people as $person)
                    @php $existing = $members->firstWhere('user_id', (string)$person->getKey()); @endphp
                    <tr>
                        <td><strong>{{ $person->name }}</strong><br><span class="a748-mini">{{ $person->email }}</span></td>
                        <td>{{ $person->branch?->branch_name ?? 'No branch' }}</td>
                        <td>{{ $person->roles->pluck('role_name')->join(', ') ?: ($person->role?->role_name ?? '—') }}</td>
                        <td colspan="2">
                            @if((string)$person->getKey() === (string)auth()->id())<span class="a748-badge muted">You are the creator</span>
                            @else
                                <form method="POST" action="{{ route($prefix.'.collaboration.add', $routeParams) }}" class="a748-row" style="gap:7px">@csrf
                                    <input type="hidden" name="user_id" value="{{ $person->getKey() }}">
                                    <select name="access_role" aria-label="Permission for {{ $person->name }}" style="min-width:125px;width:auto"><option value="viewer" @selected($existing?->access_role === 'viewer')>Viewer</option><option value="contributor" @selected($existing?->access_role === 'contributor')>Contributor</option></select>
                                    <button type="submit" class="btn primary">{{ $existing ? 'Update' : 'Add' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="a748-empty">No eligible recipients match these filters.</div></td></tr>
                @endforelse
            </tbody></table></div>
            <div class="a748-pagination">{{ $people->withQueryString()->links() }}</div>
            <p class="a748-caption">Viewers can only read. Contributors may post comments and progress updates. Neither permission grants access to other assignees' submission files or CRM records.</p>
        </div>
    </section>
</div>
@endsection
