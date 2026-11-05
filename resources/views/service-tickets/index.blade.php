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
                                        href="{{ route('service-tickets.show', $ticket->service_ticket_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View ticket"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('service-tickets.edit', $ticket->service_ticket_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit ticket"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('service-tickets.destroy', $ticket->service_ticket_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this ticket?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete ticket"
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



@include('service-tickets._style')

@endsection