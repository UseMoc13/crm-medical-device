@extends('layouts.app')

@section('title', 'Service History')

@section('content')

<div class="page-head">
    <div>
        <h1>Service History</h1>
        <p>
            Complete record of service tickets and maintenance activities
            performed on customer medical devices.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('service-tickets.index') }}" class="btn">Service Tickets</a>
        <a href="{{ route('maintenance.index') }}" class="btn">Maintenance</a>
    </div>
</div>

@if(session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert error">
        <strong>Please check the filters:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


{{-- SUMMARY --}}
<div class="sh-summary">
    <div class="sh-summary-card">
        <span>Total Service Events</span>
        <strong>{{ number_format($summary['total']) }}</strong>
    </div>

    <div class="sh-summary-card">
        <span>Service Tickets</span>
        <strong>{{ number_format($summary['tickets']) }}</strong>
    </div>

    <div class="sh-summary-card">
        <span>Maintenance Records</span>
        <strong>{{ number_format($summary['maintenance']) }}</strong>
    </div>

    <div class="sh-summary-card">
        <span>Devices Serviced</span>
        <strong>{{ number_format($summary['devices']) }}</strong>
    </div>

    <div class="sh-summary-card">
        <span>Last 30 Days</span>
        <strong>{{ number_format($summary['last_30_days']) }}</strong>
    </div>
</div>


<div class="card">

    <div class="card-head">
        <div>
            <h3>Service Event Log</h3>
            <p>{{ number_format($events->total()) }} service events found</p>
        </div>
    </div>

    <div class="card-body">

        {{-- FILTER BAR --}}
        <form method="GET" action="{{ route('service-history.index') }}" class="sh-filter-bar">

            <div class="sh-search">
                <span class="sh-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search reference, device, customer, or technician..."
                    autocomplete="off"
                >

                @if(request('search'))
                    <a
                        href="{{ route('service-history.index', request()->except('search', 'page')) }}"
                        class="sh-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >×</a>
                @endif
            </div>

            <div class="sh-field">
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="">All Types</option>
                    <option value="ticket" @selected(request('type') === 'ticket')>Service Ticket</option>
                    <option value="maintenance" @selected(request('type') === 'maintenance')>Maintenance</option>
                </select>
            </div>

            <div class="sh-field">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id">
                    <option value="">All Customers</option>
                    @foreach($customers as $customer)
                        <option
                            value="{{ $customer->customer_id }}"
                            @selected(request('customer_id') === $customer->customer_id)
                        >
                            {{ $customer->customer_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sh-field">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="sh-field">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="sh-field">
                <label for="direction">Order</label>
                <select id="direction" name="direction">
                    <option value="desc" @selected($direction === 'desc')>Newest First</option>
                    <option value="asc" @selected($direction === 'asc')>Oldest First</option>
                </select>
            </div>

            <button type="submit" class="btn sh-btn">Filter</button>

            @if(request()->hasAny(['search', 'type', 'customer_id', 'date_from', 'date_to', 'direction']))
                <a href="{{ route('service-history.index') }}" class="btn sh-btn">Reset</a>
            @endif

        </form>

        @if(request()->hasAny(['search', 'type', 'customer_id', 'date_from', 'date_to']))
            <div class="sh-filter-summary">
                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="sh-chip">Search: "{{ request('search') }}"</span>
                @endif

                @if(request('type'))
                    <span class="sh-chip">Type: {{ request('type') === 'ticket' ? 'Service Ticket' : 'Maintenance' }}</span>
                @endif

                @if(request('customer_id'))
                    <span class="sh-chip">
                        Customer: {{ $customers->firstWhere('customer_id', request('customer_id'))?->customer_name ?? '-' }}
                    </span>
                @endif

                @if(request('date_from') || request('date_to'))
                    <span class="sh-chip">
                        Date: {{ request('date_from') ?: '…' }} → {{ request('date_to') ?: '…' }}
                    </span>
                @endif
            </div>
        @endif


        {{-- TABLE --}}
        <div class="sh-table-wrap">
            <table class="sh-table">

                <thead>
                    <tr>
                        <th class="sh-col-no">No.</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Medical Device</th>
                        <th>Customer</th>
                        <th>Technician</th>
                        <th>Outcome</th>
                        <th style="min-width:110px">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($events as $event)

                        @php
                            $isTicket = $event->event_type === 'ticket';
                            $date = \Carbon\Carbon::parse($event->event_date);

                            $outcomeLabel = ucwords(str_replace('_', ' ', (string) $event->outcome));
                            $outcomeClass = \Illuminate\Support\Str::slug(str_replace('_', '-', (string) $event->outcome));

                            $sourceUrl = $isTicket
                                ? route('service-tickets.show', $event->source_id)
                                : route('maintenance.show', $event->parent_id);
                        @endphp

                        <tr>

                            <td class="sh-col-no">
                                {{ $events->firstItem() + $loop->index }}
                            </td>

                            <td>
                                {{ $date->format('d M Y') }}
                                <div class="sh-sub">{{ $date->format('H:i') }}</div>
                            </td>

                            <td>
                                <span class="sh-badge {{ $isTicket ? 'sh-type-ticket' : 'sh-type-maintenance' }}">
                                    {{ $isTicket ? 'Service Ticket' : 'Maintenance' }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ $sourceUrl }}" class="sh-link">
                                    {{ $isTicket ? $event->reference : $event->reference . ' Maintenance' }}
                                </a>

                                @if(filled($event->summary))
                                    <div class="sh-sub">
                                        <span class="sh-clip">{{ \Illuminate\Support\Str::limit($event->summary, 90) }}</span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <a
                                    href="{{ route('service-history.show', $event->serial_number_id) }}"
                                    class="sh-link"
                                >
                                    {{ $event->serial_number ?? '-' }}
                                </a>
                                <div class="sh-sub">{{ $event->product_name ?? 'Unknown Product' }}</div>
                            </td>

                            <td>{{ $event->customer_name ?? '-' }}</td>

                            <td>{{ $event->technician_name ?? 'Unassigned' }}</td>

                            <td>
                                <span class="sh-badge sh-outcome-{{ $outcomeClass }}">
                                    {{ $outcomeLabel ?: '-' }}
                                </span>
                            </td>

                            <td>
                                <div class="sh-actions">

                                    <a
                                        href="{{ $sourceUrl }}"
                                        class="sh-icon-btn view"
                                        title="{{ $isTicket ? 'View Service Ticket' : 'View Maintenance' }}"
                                        aria-label="View source record"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('service-history.show', $event->serial_number_id) }}"
                                        class="sh-icon-btn history"
                                        title="Device Service History"
                                        aria-label="Device service history"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path d="M12 7v5l3 2"/>
                                        </svg>
                                    </a>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9">
                                <div class="sh-empty">
                                    <strong>No service history found.</strong>
                                    <span>Adjust your search and filters, or create a service ticket or maintenance record.</span>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        @if($events->hasPages())
            <div class="pagination">
                {{ $events->links() }}
            </div>
        @endif

        <div class="sh-footer">
            <div>
                Showing {{ $events->firstItem() ?? 0 }}
                to {{ $events->lastItem() ?? 0 }}
                of {{ number_format($events->total()) }} service events
            </div>
        </div>

    </div>
</div>

@include('service-history._styles')

@endsection
