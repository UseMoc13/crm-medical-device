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

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif


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
                                        class="mt-icon-btn edit"
                                        title="Edit"
                                        aria-label="Edit maintenance"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('maintenance.destroy', $maintenance->maintenance_schedule_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this maintenance schedule and all of its records?')"
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

@include('maintenance._style')

@endsection
