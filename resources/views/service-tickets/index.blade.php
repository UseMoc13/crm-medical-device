
@extends('layouts.app')

@section('title', 'Service Tickets')

@section('content')

@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $isPreview = !isset($serviceTickets);

    $tickets = $serviceTickets ?? collect([
        [
            'service_ticket_id' => 'ST-001',
            'ticket_number' => 'TKT-2026-001',
            'title' => 'Device not powering on',
            'customer_name' => 'RS Medika Utama',
            'product_name' => 'Patient Monitor',
            'serial_number' => 'PM-2026-0001',
            'technician_name' => 'Andi Pratama',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'opened_at' => '2026-10-27 09:30:00',
        ],
        [
            'service_ticket_id' => 'ST-002',
            'ticket_number' => 'TKT-2026-002',
            'title' => 'Abnormal measurement readings',
            'customer_name' => 'Klinik Sehat Sentosa',
            'product_name' => 'Patient Monitor',
            'serial_number' => 'PM-2026-0008',
            'technician_name' => 'Budi Santoso',
            'priority' => 'high',
            'status' => 'open',
            'opened_at' => '2026-10-28 10:15:00',
        ],
        [
            'service_ticket_id' => 'ST-003',
            'ticket_number' => 'TKT-2026-003',
            'title' => 'Annual equipment inspection',
            'customer_name' => 'RS Harapan Bangsa',
            'product_name' => 'Infusion Pump',
            'serial_number' => 'IP-2026-0012',
            'technician_name' => 'Rizky Maulana',
            'priority' => 'medium',
            'status' => 'resolved',
            'opened_at' => '2026-10-25 13:00:00',
        ],
        [
            'service_ticket_id' => 'ST-004',
            'ticket_number' => 'TKT-2026-004',
            'title' => 'Display screen malfunction',
            'customer_name' => 'RS Permata Medika',
            'product_name' => 'Ultrasound System',
            'serial_number' => 'US-2026-0004',
            'technician_name' => 'Andi Pratama',
            'priority' => 'high',
            'status' => 'in_progress',
            'opened_at' => '2026-10-26 08:45:00',
        ],
        [
            'service_ticket_id' => 'ST-005',
            'ticket_number' => 'TKT-2026-005',
            'title' => 'Equipment calibration request',
            'customer_name' => 'Klinik Medika Sejahtera',
            'product_name' => 'ECG Machine',
            'serial_number' => 'ECG-2026-0015',
            'technician_name' => null,
            'priority' => 'low',
            'status' => 'open',
            'opened_at' => '2026-10-29 11:20:00',
        ],
        [
            'service_ticket_id' => 'ST-006',
            'ticket_number' => 'TKT-2026-006',
            'title' => 'Power supply replacement',
            'customer_name' => 'RS Citra Medika',
            'product_name' => 'Infusion Pump',
            'serial_number' => 'IP-2026-0021',
            'technician_name' => 'Budi Santoso',
            'priority' => 'medium',
            'status' => 'closed',
            'opened_at' => '2026-10-22 14:10:00',
        ],
    ]);

    $ticketCount = method_exists($tickets, 'total')
        ? $tickets->total()
        : count($tickets);

    $statuses = $statuses ?? [
        'open',
        'in_progress',
        'on_hold',
        'resolved',
        'closed',
    ];

    $priorities = $priorities ?? [
        'low',
        'medium',
        'high',
        'urgent',
    ];

    $technicians = $technicians ?? collect([
        (object) ['user_id' => 'tech-001', 'name' => 'Andi Pratama'],
        (object) ['user_id' => 'tech-002', 'name' => 'Budi Santoso'],
        (object) ['user_id' => 'tech-003', 'name' => 'Rizky Maulana'],
    ]);

    $sort = request('sort', 'opened_at');
    $direction = request('direction', 'desc');

    $formatDate = function ($date, $format = 'd M Y') {
        return $date
            ? Carbon::parse($date)->format($format)
            : '-';
    };

    $formatLabel = function ($value) {
        return ucwords(str_replace('_', ' ', $value ?? 'unknown'));
    };
@endphp


<div class="page-head">

    <div>
        <h1>Service Tickets</h1>

        <p>
            Manage customer service requests, equipment issues,
            and technician repair activities.
        </p>
    </div>

    <div class="actions">
        <a href="{{ url('/service-tickets/create') }}" class="btn primary">
            + New Service Ticket
        </a>
    </div>

</div>


@if(session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif


@if($isPreview)
    <div class="st-preview-notice">
        <span class="st-preview-icon">i</span>
        <span>
            Preview mode — displaying sample service ticket records.
            Database integration has not been implemented yet.
        </span>
    </div>
@endif


<div class="card">

    <div class="card-head">

        <div>
            <h3>Service Ticket List</h3>

            <p>
                {{ number_format($ticketCount) }}
                service tickets found
            </p>
        </div>

    </div>


    <div class="card-body">


        {{-- FILTER BAR --}}

        <form
            method="GET"
            action="{{ url('/service-tickets') }}"
            class="st-filter-bar"
        >

            <div class="st-search">

                <span class="st-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search ticket, customer, device, or serial number..."
                    autocomplete="off"
                >

                @if(request('search'))
                    <a
                        href="{{ url('/service-tickets?' . http_build_query(request()->except('search', 'page'))) }}"
                        class="st-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >
                        ×
                    </a>
                @endif

            </div>


            <div class="st-filter-select">

                <select name="status">
                    <option value="">All Statuses</option>

                    @foreach($statuses as $statusOption)
                        <option
                            value="{{ $statusOption }}"
                            @selected(request('status') === $statusOption)
                        >
                            {{ $formatLabel($statusOption) }}
                        </option>
                    @endforeach
                </select>

            </div>


            <div class="st-filter-select">

                <select name="priority">
                    <option value="">All Priorities</option>

                    @foreach($priorities as $priorityOption)
                        <option
                            value="{{ $priorityOption }}"
                            @selected(request('priority') === $priorityOption)
                        >
                            {{ $formatLabel($priorityOption) }}
                        </option>
                    @endforeach
                </select>

            </div>


            <div class="st-filter-select">

                <select name="technician_id">

                    <option value="">All Technicians</option>

                    @foreach($technicians as $technician)
                        <option
                            value="{{ $technician->user_id }}"
                            @selected(request('technician_id') == $technician->user_id)
                        >
                            {{ $technician->name }}
                        </option>
                    @endforeach

                    <option
                        value="unassigned"
                        @selected(request('technician_id') === 'unassigned')
                    >
                        Unassigned
                    </option>

                </select>

            </div>


            <div class="st-filter-select">

                <select name="sort">

                    <option value="opened_at" @selected($sort === 'opened_at')>
                        Opened Date
                    </option>

                    <option value="ticket_number" @selected($sort === 'ticket_number')>
                        Ticket Number
                    </option>

                    <option value="title" @selected($sort === 'title')>
                        Ticket Title
                    </option>

                    <option value="priority" @selected($sort === 'priority')>
                        Priority
                    </option>

                    <option value="status" @selected($sort === 'status')>
                        Status
                    </option>

                </select>

            </div>


            <div class="st-filter-select st-direction">

                <select name="direction">

                    <option value="asc" @selected($direction === 'asc')>
                        ↑ Ascending
                    </option>

                    <option value="desc" @selected($direction === 'desc')>
                        ↓ Descending
                    </option>

                </select>

            </div>


            <button type="submit" class="btn st-filter-button">
                Filter
            </button>


            @if(request()->hasAny([
                'search',
                'status',
                'priority',
                'technician_id',
                'sort',
                'direction'
            ]))
                <a
                    href="{{ url('/service-tickets') }}"
                    class="btn st-reset-button"
                >
                    Reset
                </a>
            @endif

        </form>


        {{-- ACTIVE FILTER SUMMARY --}}

        @if(
            request('search') ||
            request('status') ||
            request('priority') ||
            request('technician_id')
        )

            <div class="st-filter-summary">

                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="st-filter-chip">
                        Search: "{{ request('search') }}"
                    </span>
                @endif

                @if(request('status'))
                    <span class="st-filter-chip">
                        Status: {{ $formatLabel(request('status')) }}
                    </span>
                @endif

                @if(request('priority'))
                    <span class="st-filter-chip">
                        Priority: {{ $formatLabel(request('priority')) }}
                    </span>
                @endif

                @if(request('technician_id'))
                    <span class="st-filter-chip">
                        Technician selected
                    </span>
                @endif

            </div>

        @endif


        {{-- SERVICE TICKET TABLE --}}

        <div class="table-wrap st-table-wrap">

            <table class="st-table">

                <thead>
                    <tr>
                        <th class="st-col-number">No.</th>
                        <th class="st-col-ticket">Ticket</th>
                        <th class="st-col-customer">Customer</th>
                        <th class="st-col-device">Device</th>
                        <th class="st-col-technician">Technician</th>
                        <th class="st-col-date">Opened Date</th>
                        <th class="st-col-priority">Priority</th>
                        <th class="st-col-status">Status</th>
                        <th class="st-col-actions">Actions</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($tickets as $ticket)

                        @php
                            $ticketId = data_get(
                                $ticket,
                                'service_ticket_id'
                            );

                            $ticketNumber = data_get(
                                $ticket,
                                'ticket_number',
                                '-'
                            );

                            $ticketTitle = data_get(
                                $ticket,
                                'title',
                                'Untitled Service Ticket'
                            );

                            $customerName = data_get(
                                $ticket,
                                'customer_name',
                                data_get($ticket, 'customer.customer_name', '-')
                            );

                            $productName = data_get(
                                $ticket,
                                'product_name',
                                data_get($ticket, 'serialNumber.product.product_name', '-')
                            );

                            $serialNumber = data_get(
                                $ticket,
                                'serial_number',
                                data_get($ticket, 'serialNumber.serial_number', '-')
                            );

                            $technicianName = data_get(
                                $ticket,
                                'technician_name',
                                data_get($ticket, 'user.name')
                            );

                            $priorityValue = strtolower(
                                str_replace(
                                    ' ',
                                    '_',
                                    data_get($ticket, 'priority', 'medium')
                                )
                            );

                            $statusValue = strtolower(
                                str_replace(
                                    ' ',
                                    '_',
                                    data_get($ticket, 'status', 'open')
                                )
                            );

                            $rowNumber = method_exists($tickets, 'firstItem')
                                ? $tickets->firstItem() + $loop->index
                                : $loop->iteration;
                        @endphp

                        <tr>

                            <td class="st-col-number">
                                {{ $rowNumber }}
                            </td>


                            {{-- TICKET INFORMATION --}}

                            <td>

                                <a
                                    href="{{ url('/service-tickets/' . $ticketId) }}"
                                    class="st-ticket-link"
                                >
                                    <strong>{{ $ticketNumber }}</strong>
                                </a>

                                <div class="st-ticket-title">
                                    {{ $ticketTitle }}
                                </div>

                            </td>


                            {{-- CUSTOMER --}}

                            <td>

                                <span class="st-customer-name">
                                    {{ $customerName }}
                                </span>

                            </td>


                            {{-- DEVICE --}}

                            <td>

                                <div class="st-device-name">
                                    {{ $productName }}
                                </div>

                                <div class="st-muted">
                                    S/N: {{ $serialNumber }}
                                </div>

                            </td>


                            {{-- TECHNICIAN --}}

                            <td>

                                @if($technicianName)

                                    <div class="st-technician">

                                        <span class="st-technician-avatar">
                                            {{ Str::upper(Str::substr($technicianName, 0, 1)) }}
                                        </span>

                                        <span>
                                            {{ $technicianName }}
                                        </span>

                                    </div>

                                @else

                                    <span class="st-unassigned">
                                        Unassigned
                                    </span>

                                @endif

                            </td>


                            {{-- OPENED DATE --}}

                            <td class="st-date-cell">

                                {{ $formatDate(data_get($ticket, 'opened_at')) }}

                                <div class="st-muted">
                                    {{ $formatDate(data_get($ticket, 'opened_at'), 'H:i') }}
                                </div>

                            </td>


                            {{-- PRIORITY --}}

                            <td>

                                <span class="st-priority-badge st-priority-{{ Str::slug($priorityValue) }}">
                                    <span class="st-priority-dot"></span>
                                    {{ $formatLabel($priorityValue) }}
                                </span>

                            </td>


                            {{-- STATUS --}}

                            <td>

                                <span class="st-status-badge st-status-{{ Str::slug($statusValue) }}">
                                    {{ $formatLabel($statusValue) }}
                                </span>

                            </td>


                            {{-- ACTIONS --}}

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="{{ url('/service-tickets/' . $ticketId) }}"
                                        class="action-btn view"
                                        title="View Service Ticket"
                                        aria-label="View Service Ticket"
                                    >
                                        👁
                                    </a>

                                    <a
                                        href="{{ url('/service-tickets/' . $ticketId . '/edit') }}"
                                        class="action-btn edit"
                                        title="Edit Service Ticket"
                                        aria-label="Edit Service Ticket"
                                    >
                                        ✎
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9">

                                <div class="empty">

                                    <strong>No service tickets found.</strong>

                                    <p>
                                        Create a new service ticket or adjust
                                        your search and filters.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}

        @if(
            isset($serviceTickets) &&
            is_object($serviceTickets) &&
            method_exists($serviceTickets, 'hasPages') &&
            $serviceTickets->hasPages()
        )

            <div class="pagination">
                {{ $serviceTickets->links() }}
            </div>

        @endif

    </div>

</div>


<style>

/* =====================================================
   PREVIEW NOTICE
===================================================== */

.st-preview-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
    padding: 11px 14px;
    border: 1px solid #d8e5f3;
    border-radius: 8px;
    background: #f2f7fc;
    color: #49627f;
    font-size: 12px;
    line-height: 1.5;
}

.st-preview-icon {
    width: 19px;
    height: 19px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    background: #dceafa;
    color: #3569a7;
    font-size: 12px;
    font-weight: 700;
}


/* =====================================================
   FILTER BAR
===================================================== */

.st-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.st-search {
    position: relative;
    flex: 1 1 270px;
    min-width: 220px;
}

.st-search input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 0 38px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.st-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.st-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.st-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #7d8797;
    text-decoration: none;
    font-size: 18px;
}

.st-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECT
===================================================== */

.st-filter-select select {
    width: 100%;
    min-width: 135px;
    height: 40px;
    padding: 0 32px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-size: 12px;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.st-filter-select select:hover {
    border-color: #b7c0cf;
}

.st-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.st-filter-button,
.st-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.st-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.st-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    color: #7d8797;
    font-size: 12px;
}

.st-filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   TICKET INFORMATION
===================================================== */

.st-ticket-link {
    color: #223a70;
    text-decoration: none;
    transition: color .18s ease;
}

.st-ticket-link:hover {
    color: #2ba7a0;
}

.st-ticket-link strong {
    font-size: 13px;
    letter-spacing: .15px;
}

.st-ticket-title {
    max-width: 220px;
    margin-top: 4px;
    color: #7d8797;
    font-size: 11px;
    line-height: 1.5;
}

.st-customer-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
}

.st-device-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
}

.st-muted {
    margin-top: 4px;
    color: #929baa;
    font-size: 11px;
}

.st-date-cell {
    color: #34415c;
    font-size: 12px;
    white-space: nowrap;
}


/* =====================================================
   TECHNICIAN
===================================================== */

.st-technician {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #34415c;
    font-size: 12px;
    white-space: nowrap;
}

.st-technician-avatar {
    width: 27px;
    height: 27px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    background: #edf1fa;
    color: #385b9a;
    font-size: 11px;
    font-weight: 700;
}

.st-unassigned {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 6px;
    background: #f2f4f7;
    color: #8791a1;
    font-size: 11px;
}


/* =====================================================
   PRIORITY BADGES
===================================================== */

.st-priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 6px;
    background: #f0f1f4;
    color: #737d8d;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.st-priority-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.st-priority-low {
    background: #edf3f8;
    color: #647b91;
}

.st-priority-medium {
    background: #edf1fa;
    color: #45669c;
}

.st-priority-high {
    background: #fff3e7;
    color: #b97828;
}

.st-priority-urgent {
    background: #fff0f0;
    color: #c94a4a;
}


/* =====================================================
   STATUS BADGES
===================================================== */

.st-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef2f6;
    color: #596780;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.st-status-open {
    background: #edf1fa;
    color: #385b9a;
}

.st-status-in-progress {
    background: #e7f6f5;
    color: #168b85;
}

.st-status-on-hold {
    background: #fff3e7;
    color: #b97828;
}

.st-status-resolved {
    background: #e8f5ef;
    color: #16805f;
}

.st-status-closed {
    background: #f0f1f4;
    color: #737d8d;
}


/* =====================================================
   TABLE ACTIONS
===================================================== */

.table-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.table-actions .action-btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #e0e5ed;
    border-radius: 8px;
    background: #fff;
    color: #68758b;
    text-decoration: none;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    transition:
        color .18s ease,
        background .18s ease,
        border-color .18s ease;
}

.table-actions .action-btn.view:hover {
    background: #edf4ff;
    border-color: #cbdcf9;
    color: #3569b8;
}

.table-actions .action-btn.edit:hover {
    background: #e8f5ef;
    border-color: #c3e6d6;
    color: #16805f;
}


/* =====================================================
   TABLE STRUCTURE
===================================================== */

.table-wrap.st-table-wrap {
    width: 100% !important;
    overflow-x: auto !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px;
}

.table-wrap.st-table-wrap table.st-table {
    width: 100% !important;
    min-width: 1250px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: 1px solid #cbd5e1 !important;
}

.table-wrap.st-table-wrap table.st-table thead th {
    padding: 13px 12px;
    border: 1px solid #cbd5e1 !important;
    background-color: #f3f5f9 !important;
    color: #34415c;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}

.table-wrap.st-table-wrap table.st-table tbody td {
    padding: 13px 12px;
    border: 1px solid #d5dce6 !important;
    color: #34415c;
    font-size: 12px;
    vertical-align: middle;
}

.table-wrap.st-table-wrap table.st-table tbody tr {
    border-bottom: 1px solid #d5dce6 !important;
}

.table-wrap.st-table-wrap table.st-table tbody tr:hover {
    background-color: #f8fafc !important;
}

.table-wrap.st-table-wrap table.st-table tr:last-child td {
    border-bottom: 1px solid #d5dce6 !important;
}


/* =====================================================
   COLUMN WIDTHS
===================================================== */

.st-col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.st-col-ticket {
    min-width: 190px;
}

.st-col-customer {
    min-width: 170px;
}

.st-col-device {
    min-width: 170px;
}

.st-col-technician {
    min-width: 155px;
}

.st-col-date {
    min-width: 115px;
    white-space: nowrap;
}

.st-col-priority {
    min-width: 105px;
}

.st-col-status {
    min-width: 115px;
}

.st-col-actions {
    min-width: 100px;
    text-align: center !important;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .st-search {
        flex: 1 1 100%;
    }

    .st-filter-select {
        flex: 1 1 145px;
        min-width: 0;
    }

}

@media (max-width: 700px) {

    .st-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .st-search,
    .st-filter-select,
    .st-filter-button,
    .st-reset-button {
        width: 100%;
        box-sizing: border-box;
    }

    .st-filter-select select {
        box-sizing: border-box;
    }

}

</style>

@endsection
