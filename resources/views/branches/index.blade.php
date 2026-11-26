@extends('layouts.app')
@section('title', 'Branches')
@section('content')
<div class="page-head">
    <div>
        <h1>Branches</h1>
        <p>Manage office branches and their assignments within the CRM system.</p>
    </div>
    <div class="actions">
        {{-- A7103:users.index --}}@if(\App\Support\CrmViewAction::allows('users.index'))
<a href="{{ route('users.index') }}" class="btn">Manage Users</a>
@endif{{-- /A7103 --}}
        {{-- A7103:branches.create --}}@if(\App\Support\CrmViewAction::allows('branches.create'))
<a href="{{ route('branches.create') }}" class="btn primary">+ New Branch</a>
@endif{{-- /A7103 --}}
    </div>
</div>

<div class="card branch-directory">
    <div class="card-head branch-card-head">
        <div>
            <h3>Branch List</h3>
            <p>{{ number_format($branches->total()) }} branches found</p>
        </div>
        <div class="branch-head-counts" aria-label="Branch summary">
            <span><i class="branch-count-dot active" aria-hidden="true"></i>{{ number_format($summary['active']) }} Active</span>
            <span><i class="branch-count-dot inactive" aria-hidden="true"></i>{{ number_format($summary['inactive']) }} Inactive</span>
        </div>
    </div>
    <div class="card-body">
        {{-- A7103:branches.index --}}@if(\App\Support\CrmViewAction::allows('branches.index'))
<form method="GET" action="{{ route('branches.index') }}" class="branch-filter">
            <div class="branch-search">
                <label class="branch-sr-only" for="branch_search">Search branches</label>
                <span class="branch-search-icon" aria-hidden="true">⌕</span>
                <input id="branch_search" name="search" type="search" maxlength="150"
                    value="{{ request('search') }}" autocomplete="off"
                    placeholder="Search branches...">
                @if(request('search'))
                    {{-- A7103:branches.index --}}@if(\App\Support\CrmViewAction::allows('branches.index'))
<a href="{{ route('branches.index', request()->except('search', 'page')) }}"
                        class="branch-search-clear" aria-label="Clear search" title="Clear search">×</a>
@endif{{-- /A7103 --}}
                @endif
            </div>
            <div class="branch-filter-select">
                <label class="branch-sr-only" for="branch_status">Status</label>
                <select id="branch_status" name="status">
                    <option value="">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="branch-filter-select">
                <label class="branch-sr-only" for="branch_sort">Sort by</label>
                <select id="branch_sort" name="sort">
                    <option value="created_at" @selected(request('sort', 'created_at') === 'created_at')>Created Date</option>
                    <option value="branch_name" @selected(request('sort') === 'branch_name')>Branch Name</option>
                    <option value="branch_code" @selected(request('sort') === 'branch_code')>Branch Code</option>
                    <option value="status" @selected(request('sort') === 'status')>Status</option>
                </select>
            </div>
            <div class="branch-filter-select branch-direction-select">
                <label class="branch-sr-only" for="branch_direction">Sort direction</label>
                <select id="branch_direction" name="direction">
                    <option value="desc" @selected(request('direction', 'desc') === 'desc')>↓ Descending</option>
                    <option value="asc" @selected(request('direction') === 'asc')>↑ Ascending</option>
                </select>
            </div>
            <button type="submit" class="btn branch-filter-button">Filter</button>
            @if(request()->hasAny(['search', 'status', 'sort', 'direction']))
                {{-- A7103:branches.index --}}@if(\App\Support\CrmViewAction::allows('branches.index'))
<a href="{{ route('branches.index') }}" class="btn branch-reset-button">Reset</a>
@endif{{-- /A7103 --}}
            @endif
        </form>
@endif{{-- /A7103 --}}

        @if(request()->filled('search') || request()->filled('status'))
            <div class="branch-filter-summary">
                <span>Showing filtered results</span>
                @if(request()->filled('search'))
                    <span class="branch-filter-chip">Search: “{{ request('search') }}”</span>
                @endif
                @if(request()->filled('status'))
                    <span class="branch-filter-chip">Status: {{ ucfirst(request('status')) }}</span>
                @endif
            </div>
        @endif

        <div class="table-wrap branch-table-wrap">
            <table class="branch-table">
                <thead>
                    <tr>
                        <th scope="col">Branch</th>
                        <th scope="col">Address</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Users</th>
                        <th scope="col">Teams</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        @php
                            $linked = (int) $branch->users_count > 0 || (int) $branch->teams_count > 0;
                            $active = strtolower((string) $branch->status) === 'active';
                        @endphp
                        <tr>
                            <td>
                                <div class="branch-table-identity">
                                    {{-- A7103:branches.show --}}@if(\App\Support\CrmViewAction::allows('branches.show'))
<a class="branch-name-link" href="{{ route('branches.show', $branch) }}">
                                        <strong>{{ $branch->branch_name }}</strong>
                                    </a>
@endif{{-- /A7103 --}}
                                    <span class="branch-subtitle">{{ $branch->branch_code }}</span>
                                </div>
                            </td>
                            <td><span class="branch-ellipsis" title="{{ $branch->address ?: 'No address' }}">{{ $branch->address ?: '—' }}</span></td>
                            <td>{{ $branch->phone ?: '—' }}</td>
                            <td><span class="branch-user-badge {{ $branch->users_count > 0 ? '' : 'empty' }}">{{ $branch->users_count > 0 ? $branch->users_count . ' Users' : 'No Users' }}</span></td>
                            <td><span class="branch-user-badge {{ $branch->teams_count > 0 ? '' : 'empty' }}">{{ $branch->teams_count > 0 ? $branch->teams_count . ' Teams' : 'No Teams' }}</span></td>
                            <td><span class="branch-status {{ $active ? 'active' : 'inactive' }}">{{ $active ? 'Active' : 'Inactive' }}</span></td>
                            <td>
                                <div class="table-actions branch-actions">
                                    {{-- A7103:branches.show --}}@if(\App\Support\CrmViewAction::allows('branches.show'))
<a class="action-btn view" href="{{ route('branches.show', $branch) }}" title="View Branch" aria-label="View {{ $branch->branch_name }}">👁</a>
@endif{{-- /A7103 --}}
                                    {{-- A7103:branches.edit --}}@if(\App\Support\CrmViewAction::allows('branches.edit'))
<a class="action-btn edit" href="{{ route('branches.edit', $branch) }}" title="Edit Branch" aria-label="Edit {{ $branch->branch_name }}">✎</a>
@endif{{-- /A7103 --}}
                                    {{-- A7103:branches.destroy --}}@if(\App\Support\CrmViewAction::allows('branches.destroy'))
<form method="POST" action="{{ route('branches.destroy', $branch) }}"
                                        class="branch-delete-form" data-branch-name="{{ $branch->branch_name }}" data-has-links="{{ $linked ? '1' : '0' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn delete" title="Delete Branch" aria-label="Delete {{ $branch->branch_name }}">🗑</button>
                                    </form>
@endif{{-- /A7103 --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="branch-empty">No branches found. @if(request()->hasAny(['search','status']))Try adjusting the filters.@else{{-- A7103:branches.create --}}@if(\App\Support\CrmViewAction::allows('branches.create'))
<a href="{{ route('branches.create') }}">Create your first branch.</a>
@endif{{-- /A7103 --}}@endif</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($branches->hasPages())
            <div class="branch-pagination">{{ $branches->links() }}</div>
        @endif
    </div>
</div>

<div class="branch-modal" id="branchDeleteModal" aria-hidden="true">
    <div class="branch-modal-overlay" data-branch-modal-close></div>
    <div class="branch-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="branchModalHeading" aria-describedby="branchModalWarning" tabindex="-1">
        <div class="branch-modal-icon" aria-hidden="true">!</div>
        <h3 id="branchModalHeading">Delete Branch?</h3>
        <p id="branchModalMessage">Are you sure you want to delete <strong id="branchModalName"></strong>?</p>
        <p id="branchModalWarning" class="branch-modal-warning">Branches linked to Users or Teams cannot be deleted.</p>
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
    const dialog = modal.querySelector('.branch-modal-dialog');
    let pendingForm = null;
    let returnFocus = null;
    const close = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        pendingForm = null;
        returnFocus?.focus();
    };
    document.querySelectorAll('.branch-delete-form').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            pendingForm = form;
            returnFocus = event.submitter || form.querySelector('button');
            name.textContent = form.dataset.branchName || 'this branch';
            const linked = form.dataset.hasLinks === '1';
            message.hidden = linked;
            warning.textContent = linked
                ? 'This branch is currently linked to Users or Teams. Remove the assignments or mark it Inactive instead.'
                : 'This action cannot be undone. Branches linked to Users or Teams cannot be deleted.';
            confirm.hidden = linked;
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            (linked ? modal.querySelector('[data-branch-modal-close].btn') : confirm).focus();
        });
    });
    modal.querySelectorAll('[data-branch-modal-close]').forEach(el => el.addEventListener('click', close));
    confirm.addEventListener('click', () => { if (pendingForm) pendingForm.submit(); });
    modal.addEventListener('keydown', event => {
        if (event.key === 'Tab') {
            const focusable = Array.from(modal.querySelectorAll('button:not([hidden])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && modal.classList.contains('open')) close();
    });
});
</script>
@include('branches._style')
@endsection
