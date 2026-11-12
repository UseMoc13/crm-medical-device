@extends('layouts.app')

@section('title', 'Maintenance')

@section('content')

<div class="page-head">
    <div>
        <h1>Maintenance</h1>
        <p>
            Manage preventive maintenance, calibration, inspection schedules,
            and maintenance records for registered medical devices.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('maintenance.create') }}" class="btn primary">
            + New Maintenance Schedule
        </a>
    </div>
</div>


{{-- SUMMARY --}}
<div class="mt-summary">
    <a href="{{ route('maintenance.index') }}" class="mt-summary-card">
        <span>Total Schedules</span>
        <strong>{{ $summary['total'] }}</strong>
    </a>

    <a href="{{ route('maintenance.index', ['status' => 'Scheduled']) }}" class="mt-summary-card">
        <span>Scheduled</span>
        <strong>{{ $summary['scheduled'] }}</strong>
    </a>

    <a href="{{ route('maintenance.index', ['status' => 'In Progress']) }}" class="mt-summary-card">
        <span>In Progress</span>
        <strong>{{ $summary['in_progress'] }}</strong>
    </a>

    <a href="{{ route('maintenance.index', ['status' => 'Completed']) }}" class="mt-summary-card">
        <span>Completed</span>
        <strong>{{ $summary['completed'] }}</strong>
    </a>

    <a href="{{ route('maintenance.index', ['status' => 'overdue']) }}" class="mt-summary-card danger">
        <span>Overdue</span>
        <strong>{{ $summary['overdue'] }}</strong>
    </a>
</div>


<div class="card">

    <div class="card-head">
        <div>
            <h3>Maintenance Schedule List</h3>
            <p>{{ $maintenances->total() }} maintenance schedules found</p>
        </div>
    </div>

    <div class="card-body">

        {{-- FILTER BAR --}}
        <form method="GET" action="{{ route('maintenance.index') }}" class="mt-filter-bar">

            <div class="mt-search">
                <span class="mt-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search serial number, product, customer, or technician..."
                    autocomplete="off"
                >

                @if(request('search'))
                    <a
                        href="{{ route('maintenance.index', request()->except('search', 'page')) }}"
                        class="mt-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >×</a>
                @endif
            </div>

            <div class="mt-select">
                <select name="status">
                    <option value="">All Statuses</option>

                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach

                    <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
                </select>
            </div>

            <div class="mt-select">
                <select name="type">
                    <option value="">All Types</option>

                    @foreach($types as $typeOption)
                        <option value="{{ $typeOption }}" @selected(request('type') === $typeOption)>
                            {{ $typeOption }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mt-select">
                <select name="sort">
                    <option value="scheduled_date" @selected($sort === 'scheduled_date')>Scheduled Date</option>
                    <option value="maintenance_type" @selected($sort === 'maintenance_type')>Maintenance Type</option>
                    <option value="status" @selected($sort === 'status')>Status</option>
                    <option value="created_at" @selected($sort === 'created_at')>Created Date</option>
                </select>
            </div>

            <div class="mt-select">
                <select name="direction">
                    <option value="asc" @selected($direction === 'asc')>↑ Ascending</option>
                    <option value="desc" @selected($direction === 'desc')>↓ Descending</option>
                </select>
            </div>

            <button type="submit" class="btn mt-filter-btn">Filter</button>

            @if(request()->hasAny(['search', 'status', 'type', 'sort', 'direction']))
                <a href="{{ route('maintenance.index') }}" class="btn mt-filter-btn">Reset</a>
            @endif

        </form>

        @if(request('search') || request('status') || request('type'))
            <div class="mt-filter-summary">
                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="mt-chip">Search: "{{ request('search') }}"</span>
                @endif

                @if(request('status'))
                    <span class="mt-chip">Status: {{ ucfirst(request('status')) }}</span>
                @endif

                @if(request('type'))
                    <span class="mt-chip">Type: {{ request('type') }}</span>
                @endif
            </div>
        @endif


        {{-- TABLE --}}
        <div class="mt-table-wrap">
            <table class="mt-table">

                <thead>
                    <tr>
                        <th class="mt-col-no">No.</th>
                        <th>Medical Device</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Scheduled Date</th>
                        <th>Technician</th>
                        <th>Records</th>
                        <th>Status</th>
                        <th style="min-width:150px">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($maintenances as $maintenance)

                        @php
                            $serial = $maintenance->serialNumber;
                            $overdue = $maintenance->isOverdue();
                        @endphp

                        <tr>

                            <td class="mt-col-no">
                                {{ $maintenances->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <a
                                    href="{{ route('maintenance.show', $maintenance->maintenance_schedule_id) }}"
                                    class="mt-link"
                                >
                                    {{ $serial?->serial_number ?? '-' }}
                                </a>

                                <div class="mt-sub">
                                    {{ $serial?->product?->product_name ?? 'Unknown Product' }}
                                </div>
                            </td>

                            <td>{{ $serial?->customer?->customer_name ?? '-' }}</td>

                            <td>
                                <span class="mt-badge mt-type-badge">
                                    {{ $maintenance->maintenance_type }}
                                </span>
                            </td>

                            <td>
                                {{ $maintenance->scheduled_date?->format('d M Y') ?? '-' }}

                                @if($overdue)
                                    <div>
                                        <span class="mt-overdue-tag">
                                            OVERDUE {{ $maintenance->scheduled_date->diffInDays(today()) }}d
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                @if($maintenance->user)
                                    {{ $maintenance->user->name }}
                                @else
                                    <span class="mt-unassigned">Unassigned</span>
                                @endif
                            </td>

                            <td>{{ $maintenance->records_count }}</td>

                            <td>
                                <span class="mt-badge mt-status-{{ \Illuminate\Support\Str::slug($maintenance->status) }}">
                                    {{ $maintenance->status }}
                                </span>
                            </td>

                            <td>
                                <div class="mt-actions">

                                    @if($maintenance->status !== 'Cancelled')
                                        <a
                                            href="{{ route('maintenance.records.create', $maintenance->maintenance_schedule_id) }}"
                                            class="mt-icon-btn add"
                                            title="Add Maintenance Record"
                                            aria-label="Add maintenance record"
                                        >
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M12 5v14M5 12h14"/>
                                            </svg>
                                        </a>
                                    @endif

                                    <a
                                        href="{{ route('maintenance.show', $maintenance->maintenance_schedule_id) }}"
                                        class="mt-icon-btn view"
                                        title="View"
                                        aria-label="View maintenance"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('maintenance.edit', $maintenance->maintenance_schedule_id) }}"
                                        class="mt-icon-btn edit js-mt-protected-action"
                                        title="Edit"
                                        aria-label="Edit maintenance"
                                        data-action="edit"
                                        data-status="{{ $maintenance->status }}"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('maintenance.destroy', $maintenance->maintenance_schedule_id) }}"
                                        method="POST"
                                        class="js-mt-delete-form"
                                        data-status="{{ $maintenance->status }}"
                                        data-records="{{ $maintenance->records_count }}"
                                        data-device="{{ $maintenance->serialNumber?->serial_number ?? 'Unknown Device' }}"
                                        data-type="{{ $maintenance->maintenance_type }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="mt-icon-btn delete"
                                            title="Delete"
                                            aria-label="Delete maintenance"
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
                            <td colspan="9">
                                <div class="empty">
                                    <strong>No maintenance schedules found.</strong>
                                    <p>Create a new schedule or adjust your search and filters.</p>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($maintenances->hasPages())
            <div class="pagination">
                {{ $maintenances->links() }}
            </div>
        @endif

        <div class="mt-table-footer">
            <div>
                Showing {{ $maintenances->firstItem() ?? 0 }}
                to {{ $maintenances->lastItem() ?? 0 }}
                of {{ $maintenances->total() }} maintenance schedules
            </div>
        </div>

    </div>
</div>


{{-- Shared Maintenance delete confirmation / protected-action dialog. --}}
<div id="mtDeleteConfirmModal" class="mt-confirm-modal" aria-hidden="true">
    <div class="mt-confirm-backdrop" data-mt-close></div>
    <section class="mt-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="mtConfirmTitle" aria-describedby="mtConfirmText" tabindex="-1">
        <button type="button" class="mt-confirm-x" data-mt-close aria-label="Close dialog">&times;</button>
        <div class="mt-confirm-icon" id="mtConfirmIcon" aria-hidden="true">!</div>
        <h3 id="mtConfirmTitle">Delete Maintenance Schedule?</h3>
        <p id="mtConfirmText">This action cannot be undone.</p>
        <div id="mtConfirmDevice" class="mt-confirm-device" hidden></div>
        <div class="mt-confirm-buttons">
            <button type="button" class="btn" data-mt-close id="mtConfirmCancel">Cancel</button>
            <button type="button" class="btn danger" id="mtConfirmProceed">Delete Schedule</button>
        </div>
    </section>
</div>

<script>
(() => {
    const modal = document.getElementById('mtDeleteConfirmModal');
    if (!modal) return;
    const dialog = modal.querySelector('.mt-confirm-dialog');
    const title = document.getElementById('mtConfirmTitle');
    const message = document.getElementById('mtConfirmText');
    const device = document.getElementById('mtConfirmDevice');
    const confirm = document.getElementById('mtConfirmProceed');
    const cancel = document.getElementById('mtConfirmCancel');
    const symbol = document.getElementById('mtConfirmIcon');
    let pendingForm = null;
    let opener = null;
    let committing = false;

    function openModal({heading, body, deviceText = '', destructive = false, form = null}, trigger) {
        pendingForm = form;
        opener = trigger;
        committing = false;
        title.textContent = heading;
        message.textContent = body;
        device.textContent = deviceText;
        device.hidden = !deviceText;
        confirm.hidden = !destructive;
        confirm.disabled = false;
        confirm.textContent = 'Delete Schedule';
        cancel.textContent = destructive ? 'Cancel' : 'OK';
        symbol.classList.toggle('mt-confirm-icon-warning', !destructive);
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('mt-confirm-open');
        dialog.focus();
    }
    function closeModal() {
        if (committing) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('mt-confirm-open');
        pendingForm = null;
        opener?.focus();
    }
    modal.querySelectorAll('[data-mt-close]').forEach(btn => btn.addEventListener('click', closeModal));
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
        if (e.key === 'Tab' && modal.classList.contains('open')) {
            const focusable = [...dialog.querySelectorAll('button:not([hidden]):not([disabled])')];
            if (!focusable.length) return;
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });

    document.querySelectorAll('.js-mt-protected-action').forEach(link => {
        link.addEventListener('click', e => {
            const status = link.dataset.status;
            if (!['Completed', 'Cancelled'].includes(status)) return;
            e.preventDefault();
            openModal({
                heading: 'Edit Not Allowed',
                body: `This maintenance schedule is ${status}. Finalized maintenance schedules are read-only and cannot be edited.`
            }, link);
        });
    });

    document.querySelectorAll('.js-mt-delete-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            if (committing) return;
            const status = form.dataset.status;
            const count = Number(form.dataset.records || 0);
            const trigger = e.submitter || form.querySelector('button[type="submit"]');
            if (status !== 'Scheduled' || count > 0) {
                openModal({
                    heading: 'Delete Not Allowed',
                    body: `This maintenance schedule is ${status}${count ? ` and has ${count} maintenance record(s)` : ''}. Only a Scheduled maintenance without records can be deleted.`
                }, trigger);
                return;
            }
            openModal({
                heading: 'Delete Maintenance Schedule?',
                body: 'Are you sure you want to permanently delete this maintenance schedule? This action cannot be undone.',
                deviceText: `${form.dataset.device || 'Unknown Device'} · ${form.dataset.type || 'Maintenance'}`,
                destructive: true,
                form
            }, trigger);
        });
    });

    confirm.addEventListener('click', () => {
        if (!pendingForm || committing) return;
        committing = true;
        confirm.disabled = true;
        confirm.textContent = 'Deleting...';
        // Native form submission bypasses the submit event listener; Laravel validates again.
        HTMLFormElement.prototype.submit.call(pendingForm);
    });
})();
</script>

@include('maintenance._style')

@endsection
