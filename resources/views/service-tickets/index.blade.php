@extends('layouts.app')

@section('title', 'Service Tickets')

@section('content')

<div class="page-head">

    <div>
        <h1>Service Tickets</h1>

        <p>
            Manage customer service requests, medical device issues,
            technical support, and ticket resolution records.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('service-tickets.create') }}" class="btn primary">
            + New Service Ticket
        </a>
    </div>

</div>


{{-- =====================================================
     NOTIFICATIONS
====================================================== --}}

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


{{-- =====================================================
     MAIN CARD
====================================================== --}}

<div class="card">

    <div class="card-head">

        <div>
            <h3>Service Ticket List</h3>

            <p>
                {{ method_exists($serviceTickets ?? [], 'total')
                    ? $serviceTickets->total()
                    : (isset($serviceTickets) ? count($serviceTickets) : 0)
                }}
                service tickets found
            </p>
        </div>

    </div>


    <div class="card-body">

        {{-- =================================================
             FILTER BAR
        ================================================== --}}

        <form
            method="GET"
            action="{{ route('service-tickets.index') }}"
            class="ticket-filter-bar"
        >

            {{-- Search --}}

            <div class="ticket-search">

                <span class="ticket-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search ticket, customer, or serial number..."
                    autocomplete="off"
                >

                @if(request('search'))
                    <a
                        href="{{ route('service-tickets.index', request()->except('search', 'page')) }}"
                        class="ticket-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >
                        ×
                    </a>
                @endif

            </div>


            {{-- Status --}}

            <div class="ticket-filter-select">

                <select name="status">

                    <option value="">All Statuses</option>

                    @foreach(($statuses ?? []) as $statusOption)

                        <option
                            value="{{ $statusOption }}"
                            @selected(request('status') === $statusOption)
                        >
                            {{ ucwords(str_replace('_', ' ', $statusOption)) }}
                        </option>

                    @endforeach

                    @if(empty($statuses))
                        <option value="open" @selected(request('status') === 'open')>
                            Open
                        </option>

                        <option value="in_progress" @selected(request('status') === 'in_progress')>
                            In Progress
                        </option>

                        <option value="on_hold" @selected(request('status') === 'on_hold')>
                            On Hold
                        </option>

                        <option value="resolved" @selected(request('status') === 'resolved')>
                            Resolved
                        </option>

                        <option value="closed" @selected(request('status') === 'closed')>
                            Closed
                        </option>
                    @endif

                </select>

            </div>


            {{-- Priority --}}

            <div class="ticket-filter-select">

                <select name="priority">

                    <option value="">All Priorities</option>

                    @foreach(($priorities ?? []) as $priorityOption)

                        <option
                            value="{{ $priorityOption }}"
                            @selected(request('priority') === $priorityOption)
                        >
                            {{ ucfirst($priorityOption) }}
                        </option>

                    @endforeach

                    @if(empty($priorities))
                        <option value="low" @selected(request('priority') === 'low')>
                            Low
                        </option>

                        <option value="medium" @selected(request('priority') === 'medium')>
                            Medium
                        </option>

                        <option value="high" @selected(request('priority') === 'high')>
                            High
                        </option>

                        <option value="urgent" @selected(request('priority') === 'urgent')>
                            Urgent
                        </option>
                    @endif

                </select>

            </div>


            {{-- Sort --}}

            <div class="ticket-filter-select">

                <select name="sort">

                    <option value="opened_at" @selected(($sort ?? request('sort', 'opened_at')) === 'opened_at')>
                        Opened Date
                    </option>

                    <option value="ticket_number" @selected(($sort ?? request('sort')) === 'ticket_number')>
                        Ticket Number
                    </option>

                    <option value="title" @selected(($sort ?? request('sort')) === 'title')>
                        Ticket Title
                    </option>

                    <option value="priority" @selected(($sort ?? request('sort')) === 'priority')>
                        Priority
                    </option>

                    <option value="status" @selected(($sort ?? request('sort')) === 'status')>
                        Status
                    </option>

                </select>

            </div>


            {{-- Sort Direction --}}

            <div class="ticket-filter-select sort-direction">

                <select name="direction">

                    <option value="asc" @selected(($direction ?? request('direction', 'desc')) === 'asc')>
                        ↑ Ascending
                    </option>

                    <option value="desc" @selected(($direction ?? request('direction', 'desc')) === 'desc')>
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply --}}

            <button type="submit" class="btn ticket-filter-button">
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'status',
                'priority',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('service-tickets.index') }}"
                    class="btn ticket-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =================================================
             ACTIVE FILTER SUMMARY
        ================================================== --}}

        @if(
            request('search') ||
            request('status') ||
            request('priority')
        )

            <div class="ticket-filter-summary">

                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                    </span>
                @endif

                @if(request('status'))
                    <span class="filter-chip">
                        Status:
                        {{ ucwords(str_replace('_', ' ', request('status'))) }}
                    </span>
                @endif

                @if(request('priority'))
                    <span class="filter-chip">
                        Priority: {{ ucfirst(request('priority')) }}
                    </span>
                @endif

            </div>

        @endif


        {{-- =================================================
             SERVICE TICKET TABLE
        ================================================== --}}

        <div class="table-wrap ticket-table-wrap">

            <table class="ticket-table">

                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-ticket">Ticket Information</th>
                        <th class="col-customer">Customer</th>
                        <th class="col-device">Medical Device</th>
                        <th class="col-technician">Assigned Technician</th>
                        <th class="col-opened">Opened Date</th>
                        <th class="col-priority">Priority</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse(($serviceTickets ?? []) as $ticket)

                        @php
                            $ticketStatus = strtolower(
                                str_replace(' ', '_', $ticket->status ?? 'unknown')
                            );

                            $ticketPriority = strtolower(
                                $ticket->priority ?? 'unknown'
                            );

                            $ticketId = $ticket->service_ticket_id;

                            $customerName = $ticket->customer->customer_name ?? 'Unknown Customer';

                            $serialNumber = $ticket->serialNumber->serial_number ?? '-';

                            $productName = $ticket->serialNumber->product->product_name ?? 'Unknown Product';

                            $technicianName = $ticket->user->name ?? null;
                        @endphp

                        <tr>

                            {{-- Number --}}

                            <td class="col-number">
                                {{ method_exists($serviceTickets, 'firstItem')
                                    ? $serviceTickets->firstItem() + $loop->index
                                    : $loop->iteration
                                }}
                            </td>


                            {{-- Ticket Information --}}

                            <td>

                                <a
                                    href="{{ route('service-tickets.show', $ticket) }}"
                                    class="ticket-number-link"
                                >
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                </a>

                                <div class="ticket-title">
                                    {{ $ticket->title }}
                                </div>

                            </td>


                            {{-- Customer --}}

                            <td>

                                <span class="ticket-customer-name">
                                    {{ $customerName }}
                                </span>

                            </td>


                            {{-- Medical Device --}}

                            <td>

                                <div class="ticket-product-name">
                                    {{ $productName }}
                                </div>

                                <div class="muted">
                                    SN: {{ $serialNumber }}
                                </div>

                            </td>


                            {{-- Assigned Technician --}}

                            <td>

                                @if($technicianName)

                                    <span class="ticket-technician-name">
                                        {{ $technicianName }}
                                    </span>

                                @else

                                    <span class="ticket-unassigned">
                                        Unassigned
                                    </span>

                                @endif

                            </td>


                            {{-- Opened Date --}}

                            <td>

                                @if($ticket->opened_at)

                                    {{ \Carbon\Carbon::parse($ticket->opened_at)->format('d M Y') }}

                                    <div class="muted">
                                        {{ \Carbon\Carbon::parse($ticket->opened_at)->format('H:i') }}
                                    </div>

                                @else

                                    <span class="muted">Not recorded</span>

                                @endif

                            </td>


                            {{-- Priority --}}

                            <td>

                                <span class="ticket-priority-badge ticket-priority-{{ \Illuminate\Support\Str::slug($ticketPriority) }}">
                                    {{ ucfirst($ticketPriority) }}
                                </span>

                            </td>


                            {{-- Status --}}

                            <td>

                                <span class="ticket-status-badge ticket-status-{{ \Illuminate\Support\Str::slug($ticketStatus) }}">
                                    {{ ucwords(str_replace('_', ' ', $ticketStatus)) }}
                                </span>

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="{{ route('service-tickets.show', $ticket) }}"
                                        class="action-btn view"
                                        title="View Service Ticket"
                                        aria-label="View Service Ticket"
                                    >
                                        👁
                                    </a>

                                    <a
                                        href="{{ route('service-tickets.edit', $ticket) }}"
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


        {{-- =================================================
             PAGINATION
        ================================================== --}}

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

        <div class="table-footer">
            <div>
                Showing {{ $serviceTickets->firstItem() ?? 0 }}
                to {{ $serviceTickets->lastItem() ?? 0 }}
                of {{ $serviceTickets->total() }} Service Tickets
            </div>

    </div>

</div>


{{-- =====================================================
     STYLES
====================================================== --}}

<style>

/* =====================================================
   FILTER BAR
===================================================== */

.ticket-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.ticket-search {
    position: relative;
    flex: 1 1 270px;
    min-width: 220px;
}

.ticket-search input {
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

.ticket-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.ticket-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.ticket-search-clear {
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

.ticket-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECT
===================================================== */

.ticket-filter-select select {
    width: 100%;
    min-width: 145px;
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

.ticket-filter-select select:hover {
    border-color: #b7c0cf;
}

.ticket-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.ticket-filter-button,
.ticket-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.ticket-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.ticket-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    color: #7d8797;
    font-size: 12px;
}

.ticket-filter-summary .filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   TICKET INFORMATION
===================================================== */

.ticket-number-link {
    color: #223a70;
    text-decoration: none;
    transition: color .18s ease;
}

.ticket-number-link:hover {
    color: #2ba7a0;
}

.ticket-number-link strong {
    font-size: 13px;
    letter-spacing: .15px;
}

.ticket-title {
    margin-top: 4px;
    color: #7d8797;
    font-size: 11px;
    line-height: 1.5;
}

.ticket-customer-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
}

.ticket-product-name {
    color: #34415c;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.5;
}

.ticket-technician-name {
    color: #34415c;
    font-size: 12px;
}

.ticket-unassigned {
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

.ticket-priority-badge,
.ticket-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.ticket-priority-low {
    background: #f0f1f4;
    color: #737d8d;
}

.ticket-priority-medium {
    background: #fff5d9;
    color: #946200;
}

.ticket-priority-high {
    background: #fff0df;
    color: #b97828;
}

.ticket-priority-urgent {
    background: #fdeaea;
    color: #c03939;
}


/* =====================================================
   STATUS BADGES
===================================================== */

.ticket-status-open {
    background: #edf1fa;
    color: #385b9a;
}

.ticket-status-in-progress {
    background: #fff5d9;
    color: #946200;
}

.ticket-status-on-hold {
    background: #fff0df;
    color: #b97828;
}

.ticket-status-resolved {
    background: #e8f5ef;
    color: #16805f;
}

.ticket-status-closed {
    background: #e7f6f5;
    color: #168b85;
}

.ticket-status-unknown {
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
   TABLE
===================================================== */

.table-wrap.ticket-table-wrap {
    width: 100% !important;
    overflow-x: auto !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px;
}

.table-wrap.ticket-table-wrap table.ticket-table {
    width: 100% !important;
    min-width: 1250px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: 1px solid #cbd5e1 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table thead th {
    border: 1px solid #cbd5e1 !important;
    background-color: #f3f5f9 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody td {
    border: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody tr {
    border-bottom: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table th,
.table-wrap.ticket-table-wrap table.ticket-table td {
    border-right: 1px solid #d5dce6 !important;
    border-bottom: 1px solid #d5dce6 !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tbody tr:hover {
    background-color: #f8fafc !important;
}

.table-wrap.ticket-table-wrap table.ticket-table tr:last-child td {
    border-bottom: 1px solid #d5dce6 !important;
}

.table-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 18px;
        color: #6b7280;
        font-size: 13px;
    }

    .table-footer {
            align-items: flex-start;
            flex-direction: column;
        }


/* =====================================================
   TABLE COLUMN WIDTHS
===================================================== */

.ticket-table .col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.ticket-table .col-ticket {
    min-width: 190px;
}

.ticket-table .col-customer {
    min-width: 165px;
}

.ticket-table .col-device {
    min-width: 200px;
}

.ticket-table .col-technician {
    min-width: 150px;
}

.ticket-table .col-opened {
    min-width: 115px;
    white-space: nowrap;
}

.ticket-table .col-priority,
.ticket-table .col-status {
    min-width: 105px;
}

.ticket-table .col-actions {
    min-width: 100px;
    text-align: center;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .ticket-search {
        flex: 1 1 100%;
    }

    .ticket-filter-select {
        flex: 1 1 150px;
        min-width: 0;
    }

}

@media (max-width: 700px) {

    .ticket-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .ticket-search,
    .ticket-filter-select,
    .ticket-filter-button,
    .ticket-reset-button {
        width: 100%;
        box-sizing: border-box;
    }

    .ticket-filter-select select {
        box-sizing: border-box;
    }

}

</style>

@endsection