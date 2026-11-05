@extends('layouts.app')

@section('title', 'Installations')
@section('content')
<div class="page-head">
    <div>
        <h1>Installations</h1>
        <p>Manage medical device installation schedules and activities.</p>
    </div>

    <div class="actions">
        <a href="{{ route('installations.create') }}" class="btn primary">
            + Create Installation
        </a>
    </div>
</div>

@include('installations._toast')

<div class="card">
    <div class="card-head">
        <div><h3>Installation List</h3><p>Manage and monitor installation schedules.</p></div>
    </div>

    <div class="card-body">
        <form method="GET"
              action="{{ route('installations.index') }}"
              class="installation-filter-bar">

            <div class="installation-filter-search">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search delivery, location, or status..."
                >
            </div>

            <div >
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach ([
                        'Scheduled' => 'Scheduled',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                        'Cancelled' => 'Cancelled',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(request('status') === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn primary">
                Search
            </button>

            <a href="{{ route('installations.index') }}" class="btn">
                Reset
            </a>
        </form>

        <div class="table-wrap installation-table-wrap">
            <table class="installation-table">
                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-delivery">Delivery</th>
                        <th class="col-date">Installation Date</th>
                        <th class="col-location">Location</th>
                        <th class="col-user">Assigned User</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($installations as $installation)
                        @php
                            $status = strtolower(
                                str_replace(' ', '_', $installation->status)
                            );

                            $badgeClass = match ($status) {
                                'scheduled' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary',
                            };
                        @endphp

                        <tr>
                            <td>
                                {{ $installations->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <a class="installation-link" href="{{ route('deliveries.show', $installation->delivery_id) }}">{{ $installation->delivery?->delivery_number ?? '—' }}</a>
                            </td>

                            <td>
                                {{ $installation->installation_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                {{ $installation->location ?: '—' }}
                            </td>

                            <td>
                                {{ $installation->user?->name
                                    ?? $installation->user?->full_name
                                    ?? $installation->user?->username
                                    ?? $installation->user?->email
                                    ?? '—' }}
                            </td>

                            <td>
                                <span class="installation-badge {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $installation->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('installations.show', $installation->installation_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View installation"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('installations.edit', $installation->installation_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit installation"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('installations.destroy', $installation->installation_id) }}"
                                        method="POST"
                                        class="installation-delete-form"
                                        data-locked="{{ $installation->status === 'Scheduled' ? '0' : '1' }}"
                                        data-number="{{ $installation->delivery?->delivery_number ?? $installation->installation_id }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete installation"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M3 6h18"/>
                                                <path d="M8 6V4h8v2"/>
                                                <path d="m19 6-1 14H6L5 6"/>
                                                <path d="M10 11v5M14 11v5"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No installation records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    <div class="installation-table-footer">
            <div>
                Showing {{ $installations->firstItem() ?? 0 }}
                to {{ $installations->lastItem() ?? 0 }}
                of {{ $installations->total() }} installations
            </div>

        <div>{{ $installations->links() }}</div>
    </div>
    </div>
</div>

<div class="installation-confirm" id="installationDeleteModal" hidden role="dialog" aria-modal="true" aria-labelledby="installationDeleteTitle">
  <div class="installation-confirm-dialog">
    <div class="installation-confirm-symbol">!</div><h3 id="installationDeleteTitle">Delete Installation?</h3><p id="installationDeleteText"></p>
    <div class="installation-confirm-actions"><button type="button" class="btn" id="installationDeleteCancel">Cancel</button><button type="button" class="btn installation-delete-confirm" id="installationDeleteConfirm">Delete Installation</button></div>
  </div>
</div>
<script>
(()=>{const modal=document.getElementById('installationDeleteModal');const title=document.getElementById('installationDeleteTitle');const msg=document.getElementById('installationDeleteText');const approve=document.getElementById('installationDeleteConfirm');let selected=null;
 const close=()=>{modal.hidden=true;selected=null};document.querySelectorAll('.installation-delete-form').forEach(form=>form.addEventListener('submit',e=>{e.preventDefault();selected=form;const locked=form.dataset.locked==='1';title.textContent=locked?'Cannot Delete Installation':'Delete Installation?';msg.textContent=locked?'Only Scheduled installations can be deleted.':'Delete the installation for '+form.dataset.number+'? This action cannot be undone.';approve.hidden=locked;modal.hidden=false;}));approve.addEventListener('click',()=>{if(selected)selected.submit()});document.getElementById('installationDeleteCancel').addEventListener('click',close);modal.addEventListener('click',e=>{if(e.target===modal)close()});document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
})();
</script>
@include('installations._style')
@endsection
