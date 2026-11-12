@extends('layouts.app')

@section('title', 'Maintenance Details')

@section('content')

@php
    $serial = $maintenance->serialNumber;
    $product = $serial?->product;
    $customer = $serial?->customer;
    $technician = $maintenance->user;

    $overdue = $maintenance->isOverdue();
    $statusClass = \Illuminate\Support\Str::slug($maintenance->status);

    $lastRecord = $allRecords->first();
    $canAddRecord = $maintenance->status === 'In Progress';

@endphp

<div class="page-head">
    <div>
        <h1>Maintenance Details</h1>
        <p>View the maintenance schedule, device information, and maintenance records.</p>
    </div>

    <div class="actions">
        <a href="{{ route('maintenance.index') }}" class="btn">
            &larr; Back to Maintenance
        </a>

        @if(in_array($maintenance->status, ['Scheduled', 'In Progress'], true))
            <a href="{{ route('maintenance.edit', $maintenance->maintenance_schedule_id) }}" class="btn primary">Edit Schedule</a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif

@include('maintenance._workflow')

{{-- OVERVIEW --}}
<div class="mt-grid">

    <section class="card mt-block">
        <div class="card-head">
            <div>
                <h3>Schedule Information</h3>
                <p>Maintenance type, date, and assignment details.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="mt-identity">
                <div class="mt-identity-icon" aria-hidden="true">MT</div>

                <div class="mt-identity-body">
                    <h2>{{ $maintenance->maintenance_type }} Maintenance</h2>
                    <span>{{ $serial?->serial_number ?? '-' }} — {{ $product?->product_name ?? 'Unknown Product' }}</span>
                </div>

                <span class="mt-badge mt-status-{{ $statusClass }}">
                    {{ $maintenance->status }}
                </span>
            </div>

            <div class="mt-info-grid">

                <div class="mt-info-item">
                    <span class="mt-info-label">Maintenance Type</span>
                    <strong>{{ $maintenance->maintenance_type }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Scheduled Date</span>
                    <strong>
                        {{ $maintenance->scheduled_date?->format('d M Y') ?? '-' }}
                        @if($overdue)
                            <br><span class="mt-overdue-tag">OVERDUE {{ $maintenance->scheduled_date->diffInDays(today()) }} DAYS</span>
                        @endif
                    </strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Assigned Technician</span>
                    <strong>{{ $technician?->name ?? 'Unassigned' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Status</span>
                    <strong>{{ $maintenance->status }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Created At</span>
                    <strong>{{ $maintenance->created_at?->format('d M Y, H:i') ?? '-' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Last Updated</span>
                    <strong>{{ $maintenance->updated_at?->format('d M Y, H:i') ?? '-' }}</strong>
                </div>

            </div>

            @if(filled($maintenance->notes))
                <div class="mt-notes">
                    <span class="mt-info-label">Notes</span>
                    <p>{{ $maintenance->notes }}</p>
                </div>
            @endif

        </div>
    </section>

    <section class="card mt-block">
        <div class="card-head">
            <div>
                <h3>Maintenance Summary</h3>
                <p>Overview of this maintenance schedule.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="mt-stat-list">

                <div class="mt-stat">
                    <span>Customer</span>
                    <strong>{{ $customer?->customer_name ?? 'No customer assigned' }}</strong>
                    <small>Owner of the registered device</small>
                </div>

                <div class="mt-stat">
                    <span>Records Performed</span>
                    <strong>{{ $allRecords->count() }} {{ $allRecords->count() === 1 ? 'Record' : 'Records' }}</strong>
                    <small>Maintenance activities logged for this schedule</small>
                </div>

                <div class="mt-stat">
                    <span>Latest Result</span>
                    <strong>
                        @if($lastRecord)
                            <span class="mt-badge mt-result-{{ \Illuminate\Support\Str::slug($lastRecord->result) }}">
                                {{ $lastRecord->result }}
                            </span>
                        @else
                            No record yet
                        @endif
                    </strong>
                    <small>
                        {{ $lastRecord ? $lastRecord->performed_at?->format('d M Y, H:i') : 'Result of the most recent activity' }}
                    </small>
                </div>

            </div>
        </div>
    </section>

</div>


{{-- DEVICE --}}
<section class="card mt-block">
    <div class="card-head">
        <div>
            <h3>Device Information</h3>
            <p>Device and customer associated with this maintenance schedule.</p>
        </div>
    </div>

    <div class="card-body">
        @if($serial)
            <div class="mt-info-grid">
                <div class="mt-info-item">
                    <span class="mt-info-label">Serial Number</span>
                    <strong class="mt-mono">{{ $serial->serial_number }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Product</span>
                    <strong>{{ $product?->product_name ?? 'Unknown Product' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Product Code</span>
                    <strong>{{ $product?->product_code ?? '-' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Device Status</span>
                    <strong>{{ $serial->status ?? '-' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Purchase Date</span>
                    <strong>{{ $serial->purchase_date?->format('d M Y') ?? 'Not recorded' }}</strong>
                </div>

                <div class="mt-info-item">
                    <span class="mt-info-label">Customer</span>
                    <strong>{{ $customer?->customer_name ?? '-' }}</strong>
                </div>
            </div>
        @else
            <div class="mt-empty">
                <h4>No Device Information</h4>
                <p>No serial number is associated with this maintenance schedule.</p>
            </div>
        @endif
    </div>
</section>


{{-- RECORDS --}}
<section class="card mt-block mt-records-card">

    <div class="card-head">
        <div>
            <h3>Maintenance Records</h3>
            <p>Activities performed by technicians for this schedule.</p>
        </div>

        <div class="mt-head-actions">

            <span class="mt-count">
                {{ $allRecords->count() }}
                {{ $allRecords->count() === 1 ? 'Record' : 'Records' }}
            </span>

            <button
                type="button"
                class="mt-btn-show-all"
                onclick="openMaintenanceRecordsModal()"
            >
                Show All
            </button>

            @if($canAddRecord)
                <a
                    href="{{ route('maintenance.records.create', $maintenance->maintenance_schedule_id) }}"
                    class="mt-btn-add"
                >
                    + Add Maintenance Record
                </a>
            @endif

        </div>
    </div>

    <div class="mt-rec-wrap">

        @if($records->count())

            @include('maintenance.records._table', ['rows' => $records])

            @if($records->hasPages())
                <div class="mt-rec-pagination">
                    <div>
                        Showing <strong>{{ $records->firstItem() }}</strong>
                        to <strong>{{ $records->lastItem() }}</strong>
                        of <strong>{{ $records->total() }}</strong> records
                    </div>

                    <div class="pagination">
                        {{ $records->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif

        @else

            <div class="mt-rec-empty">
                <strong>No maintenance records yet.</strong>
                <span>
                    @if($canAddRecord)
                        Log the first activity using the + Add Maintenance Record button above.
                    @else
                        @if($maintenance->status === 'Scheduled')
                            Start maintenance to add records.
                        @else
                            This schedule is finalized; its records are read-only.
                        @endif
                    @endif
                </span>
            </div>

        @endif

    </div>

</section>


{{-- REGISTRATION --}}
<section class="card mt-block">
    <div class="card-head">
        <div>
            <h3>Registration Information</h3>
            <p>System identifiers and related record references.</p>
        </div>
    </div>

    <div class="card-body">
        <div class="mt-info-grid">
            <div class="mt-info-item">
                <span class="mt-info-label">Maintenance Schedule ID</span>
                <strong class="mt-mono">{{ $maintenance->maintenance_schedule_id }}</strong>
            </div>

            <div class="mt-info-item">
                <span class="mt-info-label">Serial Number Record ID</span>
                <strong class="mt-mono">{{ $maintenance->serial_number_id }}</strong>
            </div>

            <div class="mt-info-item">
                <span class="mt-info-label">Technician User ID</span>
                <strong class="mt-mono">{{ $maintenance->user_id ?: '-' }}</strong>
            </div>
        </div>
    </div>
</section>


{{-- MAINTENANCE RECORDS MODAL --}}
<div id="maintenanceRecordsModal" class="mt-modal" aria-hidden="true">

    <div class="mt-modal-overlay" onclick="closeMaintenanceRecordsModal()"></div>

    <div class="mt-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="mtModalTitle">

        <div class="mt-modal-header">
            <div>
                <div class="mt-modal-title" id="mtModalTitle">Maintenance Records</div>
                <div class="mt-modal-subtitle">Manage all maintenance activities logged for this schedule.</div>
            </div>

            <button
                type="button"
                class="mt-modal-close"
                onclick="closeMaintenanceRecordsModal()"
                aria-label="Close"
            >×</button>
        </div>

        <div class="mt-modal-toolbar">

            <div class="mt-modal-search">
                <span class="mt-search-icon">⌕</span>
                <input
                    type="text"
                    id="maintenanceRecordSearch"
                    placeholder="Search technician, action, condition, or notes..."
                    autocomplete="off"
                >
            </div>

            <div class="mt-modal-filter">
                <select id="maintenanceRecordFilter">
                    <option value="all">All Results</option>
                    @foreach($results as $resultOption)
                        <option value="{{ strtolower($resultOption) }}">{{ $resultOption }}</option>
                    @endforeach
                </select>
            </div>

            @if($canAddRecord)
                <a
                    href="{{ route('maintenance.records.create', $maintenance->maintenance_schedule_id) }}"
                    class="mt-btn-add"
                >
                    + Add Maintenance Record
                </a>
            @endif

        </div>

        <div class="mt-modal-body">

            @if($allRecords->count())
                @include('maintenance.records._table', [
                    'rows' => $allRecords,
                    'tbodyId' => 'maintenanceRecordsModalBody',
                ])
            @endif

            <div
                id="maintenanceRecordsEmpty"
                class="mt-modal-empty {{ $allRecords->count() ? '' : 'show' }}"
            >
                {{ $allRecords->count() ? 'No records match your search.' : 'No maintenance records yet.' }}
            </div>

        </div>

        <div class="mt-modal-footer">
            <span id="maintenanceRecordsFooter">
                {{ $allRecords->count() }}
                {{ $allRecords->count() === 1 ? 'Record' : 'Records' }} total
            </span>

            <button type="button" class="btn" onclick="closeMaintenanceRecordsModal()">Close</button>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('maintenanceRecordsModal');
    const search = document.getElementById('maintenanceRecordSearch');
    const filter = document.getElementById('maintenanceRecordFilter');
    const body = document.getElementById('maintenanceRecordsModalBody');
    const empty = document.getElementById('maintenanceRecordsEmpty');
    const footer = document.getElementById('maintenanceRecordsFooter');

    window.openMaintenanceRecordsModal = function () {
        if (!modal) return;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        setTimeout(function () { search && search.focus(); }, 100);
    };

    window.closeMaintenanceRecordsModal = function () {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && modal.classList.contains('open')) {
            closeMaintenanceRecordsModal();
        }
    });

    function applyFilter() {
        if (!body) return;

        const term = (search?.value || '').toLowerCase().trim();
        const result = (filter?.value || 'all').toLowerCase();
        const rows = body.querySelectorAll('tr');
        let visible = 0;

        rows.forEach(function (row) {
            const matchSearch = !term || row.textContent.toLowerCase().includes(term);
            const matchResult = result === 'all' || (row.dataset.result || '') === result;
            const show = matchSearch && matchResult;

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        if (empty) {
            empty.textContent = 'No records match your search.';
            empty.classList.toggle('show', visible === 0);
        }

        if (footer) {
            footer.textContent = visible + ' of ' + rows.length + ' records shown';
        }
    }

    search && search.addEventListener('input', applyFilter);
    filter && filter.addEventListener('change', applyFilter);
});
</script>

@include('maintenance._delete_confirm')
@include('maintenance._style')

@endsection
