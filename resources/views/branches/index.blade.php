@extends('layouts.app')
@section('title', 'Branches')
@section('content')
<div class="page-head">
    <div><h1>Branches</h1><p>Manage CRM office branches and their assignments to users and teams.</p></div>
    <div class="actions">
        <a href="{{ route('users.index') }}" class="btn">Users</a>
        <a href="{{ route('branches.create') }}" class="btn primary">+ New Branch</a>
    </div>
</div>

<div class="branch-stats">
    <div class="branch-stat"><span>Total Branches</span><strong>{{ number_format($summary['total']) }}</strong><small>Registered locations</small></div>
    <div class="branch-stat"><span>Active</span><strong>{{ number_format($summary['active']) }}</strong><small>Operational branches</small></div>
    <div class="branch-stat"><span>Inactive</span><strong>{{ number_format($summary['inactive']) }}</strong><small>Not currently operational</small></div>
    <div class="branch-stat"><span>Assigned Users</span><strong>{{ number_format($summary['assigned_users']) }}</strong><small>Across all branches</small></div>
</div>

<div class="card branch-directory">
    <div class="card-head"><div><h3>Branch Directory</h3><p>{{ number_format($branches->total()) }} matching branches</p></div></div>
    <div class="card-body">
        <form method="GET" action="{{ route('branches.index') }}" class="branch-filter">
            <div class="branch-search">
                <label for="branch_search" class="branch-sr-only">Search branches</label>
                <input id="branch_search" name="search" type="search" maxlength="150" placeholder="Search code, name, or address..." value="{{ request('search') }}">
            </div>
            <div>
                <label for="branch_status" class="branch-sr-only">Branch status</label>
                <select id="branch_status" name="status">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div>
                <label for="branch_sort" class="branch-sr-only">Sort branches</label>
                <select id="branch_sort" name="sort">
                    <option value="created_at" @selected(request('sort','created_at') === 'created_at')>Created Date</option>
                    <option value="branch_name" @selected(request('sort') === 'branch_name')>Branch Name</option>
                    <option value="branch_code" @selected(request('sort') === 'branch_code')>Branch Code</option>
                    <option value="status" @selected(request('sort') === 'status')>Status</option>
                </select>
            </div>
            <div>
                <label for="branch_direction" class="branch-sr-only">Sort direction</label>
                <select id="branch_direction" name="direction">
                    <option value="desc" @selected(request('direction','desc') === 'desc')>Descending</option>
                    <option value="asc" @selected(request('direction') === 'asc')>Ascending</option>
                </select>
            </div>
            <button type="submit" class="btn primary">Apply</button>
            <a href="{{ route('branches.index') }}" class="btn">Reset</a>
        </form>

        <div class="table-wrap branch-table-wrap">
            <table class="branch-table">
                <thead><tr><th>Branch</th><th>Address</th><th>Phone</th><th>Users</th><th>Teams</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($branches as $branch)
                    @php $linked = (int) $branch->users_count > 0 || (int) $branch->teams_count > 0; @endphp
                    <tr>
                        <td><div class="branch-identity"><span class="branch-mini-avatar" aria-hidden="true">{{ strtoupper(substr((string)$branch->branch_name,0,1)) }}</span><div><a class="branch-name" href="{{ route('branches.show', $branch) }}">{{ $branch->branch_name }}</a><span class="branch-code">{{ $branch->branch_code }}</span></div></div></td>
                        <td><span class="branch-ellipsis" title="{{ $branch->address }}">{{ $branch->address ?: '—' }}</span></td>
                        <td>{{ $branch->phone ?: '—' }}</td>
                        <td><span class="branch-count">{{ (int) $branch->users_count }}</span></td>
                        <td><span class="branch-count">{{ (int) $branch->teams_count }}</span></td>
                        <td><span class="branch-status {{ strtolower((string)$branch->status) === 'active' ? 'active' : 'inactive' }}">{{ ucfirst(strtolower((string)$branch->status)) }}</span></td>
                        <td>
                            <div class="branch-actions">
                                <a href="{{ route('branches.show', $branch) }}" title="View Branch" aria-label="View {{ $branch->branch_name }}">View</a>
                                <a href="{{ route('branches.edit', $branch) }}" title="Edit Branch" aria-label="Edit {{ $branch->branch_name }}">Edit</a>
                                <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="branch-delete-form" data-branch-name="{{ $branch->branch_name }}" data-has-links="{{ $linked ? '1' : '0' }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="branch-delete-link" title="Delete Branch" aria-label="Delete {{ $branch->branch_name }}">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="branch-empty">No branches found. Adjust the filters or <a href="{{ route('branches.create') }}">create a branch</a>.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($branches->hasPages())<div class="branch-pagination">{{ $branches->links() }}</div>@endif
    </div>
</div>

<div class="branch-modal" id="branchDeleteModal" aria-hidden="true">
    <div class="branch-modal-overlay" data-branch-modal-close></div>
    <div class="branch-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="branchModalHeading" aria-describedby="branchModalMessage">
        <div class="branch-modal-icon">!</div>
        <h3 id="branchModalHeading">Delete Branch?</h3>
        <p id="branchModalMessage">Are you sure you want to delete <strong id="branchModalName"></strong>?</p>
        <p id="branchModalWarning" class="branch-modal-warning">This action cannot be undone. Branches assigned to Users or Teams cannot be deleted.</p>
        <div class="branch-modal-buttons">
            <button type="button" class="btn" data-branch-modal-close>Cancel</button>
            <button type="button" class="btn branch-danger-button" id="branchModalConfirm">Delete Branch</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('branchDeleteModal');
    if (!modal) return;
    const name = document.getElementById('branchModalName');
    const message = document.getElementById('branchModalMessage');
    const confirm = document.getElementById('branchModalConfirm');
    const warning = document.getElementById('branchModalWarning');
    let pendingForm = null;
    let returnFocus = null;
    const close = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.style.overflow = '';
        pendingForm = null;
        returnFocus?.focus();
    };
    document.querySelectorAll('.branch-delete-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            pendingForm = form;
            returnFocus = e.submitter;
            name.textContent = form.dataset.branchName || 'this branch';
            const linked = form.dataset.hasLinks === '1';
            message.hidden = linked;
            warning.textContent = linked
                ? 'This branch is still assigned to Users or Teams. Remove those assignments or mark the branch Inactive.'
                : 'This action cannot be undone. Branches assigned to Users or Teams cannot be deleted.';
            confirm.hidden = linked;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden','false');
            document.body.style.overflow = 'hidden';
            (linked ? modal.querySelector('[data-branch-modal-close].btn') : confirm).focus();
        });
    });
    modal.querySelectorAll('[data-branch-modal-close]').forEach(el => el.addEventListener('click', close));
    confirm.addEventListener('click', () => { if (pendingForm) pendingForm.submit(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
});
</script>
@include('branches._style')
@endsection
