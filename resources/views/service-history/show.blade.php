@extends('layouts.app')

@section('title', 'Device Service History')

@section('content')

@php
    $product = $serialNumber->product;
    $customer = $serialNumber->customer;

    $warrantyActive = $warranty
        && strtolower(trim($warranty->status)) === 'active'
        && \Carbon\Carbon::parse($warranty->end_date)->gte(today());
@endphp

<div class="page-head">
    <div>
        <h1>Device Service History</h1>
        <p>Chronological record of every service ticket and maintenance activity for this device.</p>
    </div>

    <div class="actions">
        <a href="{{ route('service-history.index') }}" class="btn">
            &larr; Back to Service History
        </a>

        <a href="{{ route('service-tickets.create') }}" class="btn primary">
            + New Service Ticket
        </a>
    </div>
</div>

<div class="sh-grid">

    {{-- DEVICE --}}
    <section class="card sh-block">
        <div class="card-head">
            <div>
                <h3>Device Information</h3>
                <p>Registered device and its owner.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="sh-identity">
                <div class="sh-identity-icon" aria-hidden="true">SN</div>

                <div class="sh-identity-body">
                    <h2>{{ $serialNumber->serial_number }}</h2>
                    <span>{{ $product?->product_name ?? 'Unknown Product' }}</span>
                </div>
            </div>

            <div class="sh-info-grid">

                <div class="sh-info-item">
                    <span class="sh-info-label">Product Code</span>
                    <strong>{{ $product?->product_code ?? '-' }}</strong>
                </div>

                <div class="sh-info-item">
                    <span class="sh-info-label">Device Status</span>
                    <strong>{{ $serialNumber->status ?? '-' }}</strong>
                </div>

                <div class="sh-info-item">
                    <span class="sh-info-label">Customer</span>
                    <strong>{{ $customer?->customer_name ?? 'No customer assigned' }}</strong>
                </div>

                <div class="sh-info-item">
                    <span class="sh-info-label">Purchase Date</span>
                    <strong>{{ $serialNumber->purchase_date?->format('d M Y') ?? 'Not recorded' }}</strong>
                </div>

                <div class="sh-info-item">
                    <span class="sh-info-label">Warranty</span>
                    <strong>
                        @if($warranty)
                            <span class="sh-badge {{ $warrantyActive ? 'sh-outcome-resolved' : 'sh-outcome-failed' }}">
                                {{ $warrantyActive ? 'Active' : ($warranty->status ?: 'Expired') }}
                            </span>
                        @else
                            No warranty recorded
                        @endif
                    </strong>
                </div>

                <div class="sh-info-item">
                    <span class="sh-info-label">Warranty Period</span>
                    <strong>
                        @if($warranty)
                            {{ \Carbon\Carbon::parse($warranty->start_date)->format('d M Y') }}
                            — {{ \Carbon\Carbon::parse($warranty->end_date)->format('d M Y') }}
                        @else
                            -
                        @endif
                    </strong>
                </div>

            </div>

        </div>
    </section>

    {{-- SUMMARY --}}
    <section class="card sh-block">
        <div class="card-head">
            <div>
                <h3>Service Summary</h3>
                <p>Overview of this device's service activity.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="sh-stat">
                <span>Total Service Events</span>
                <strong>{{ $stats['total'] }}</strong>
                <small>{{ $stats['tickets'] }} ticket(s) · {{ $stats['maintenance'] }} maintenance record(s)</small>
            </div>

            <div class="sh-stat">
                <span>Technician Work Logs / Resolutions</span>
                <strong>{{ $stats['work_logs'] }} / {{ $stats['resolutions'] }}</strong>
                <small>Individual updates included in the timeline</small>
            </div>

            <div class="sh-stat">
                <span>Open Tickets</span>
                <strong>{{ $stats['open_tickets'] }}</strong>
                <small>Tickets not yet resolved or closed</small>
            </div>

            <div class="sh-stat">
                <span>Last Service</span>
                <strong>{{ $stats['last_service']?->format('d M Y') ?? 'Never serviced' }}</strong>
                <small>
                    {{ $stats['last_service'] ? $stats['last_service']->diffForHumans() : 'No service activity recorded' }}
                </small>
            </div>

            <div class="sh-stat">
                <span>Parts Cost</span>
                <strong>Rp {{ number_format($stats['parts_cost'], 0, ',', '.') }}</strong>
                <small>Total of parts used in service tickets</small>
            </div>

        </div>
    </section>

</div>


{{-- UPCOMING --}}
<section class="card sh-block">
    <div class="card-head">
        <div>
            <h3>Upcoming Maintenance</h3>
            <p>Open maintenance schedules for this device.</p>
        </div>
    </div>

    <div class="card-body">
        @forelse($upcoming as $schedule)
            <div class="sh-upcoming">
                <div>
                    <strong>{{ $schedule->maintenance_type }} Maintenance</strong>
                    <small>
                        {{ $schedule->scheduled_date?->format('d M Y') }}
                        · {{ $schedule->user?->name ?? 'Unassigned' }}
                        @if($schedule->isOverdue())
                            · <span class="sh-overdue">Overdue</span>
                        @endif
                    </small>
                </div>

                <a href="{{ route('maintenance.show', $schedule->maintenance_schedule_id) }}" class="btn">
                    View
                </a>
            </div>
        @empty
            <div class="sh-empty">
                <strong>No upcoming maintenance.</strong>
                <span>There are no open maintenance schedules for this device.</span>
            </div>
        @endforelse
    </div>
</section>


{{-- TIMELINE --}}
<section class="card sh-block">
    <div class="card-head">
        <div>
            <h3>Service Timeline</h3>
            <p>Service tickets, technician work logs, resolution updates, and maintenance records, newest first.</p>
        </div>
    </div>

    <div class="card-body">

        @forelse($timeline as $month => $monthEvents)

            <div class="sh-month">{{ $month }}</div>

            <div class="sh-timeline">

                @foreach($monthEvents as $event)

                    @php
                        $isTicket = $event['type'] === 'ticket';
                        $typeLabel = ['ticket' => 'Service Ticket', 'maintenance' => 'Maintenance', 'work_log' => 'Work Log', 'resolution' => 'Resolution'][$event['type']] ?? 'Service Event';
                        $typeClass = 'sh-type-' . str_replace('_', '-', $event['type']);
                        $outcomeLabel = ucwords(str_replace('_', ' ', (string) $event['outcome']));
                        $outcomeClass = \Illuminate\Support\Str::slug(str_replace('_', '-', (string) $event['outcome']));
                    @endphp

                    <div class="sh-event {{ $event['type'] }}">
                        <span class="sh-dot"></span>

                        <div class="sh-event-card">

                            <div class="sh-event-head">
                                <div class="sh-event-ref">
                                    <span class="sh-badge {{ $typeClass }}">
                                        {{ $typeLabel }}
                                    </span>

                                    <strong class="sh-link">{{ $event['reference'] }}</strong>
                                </div>

                                <span class="sh-badge sh-outcome-{{ $outcomeClass }}">
                                    {{ $outcomeLabel ?: '-' }}
                                </span>
                            </div>

                            <div class="sh-event-title">{{ $event['title'] }}</div>

                            <div class="sh-event-meta">
                                {{ $event['date']->format('d M Y, H:i') }}
                                · {{ $event['technician'] ?? 'Unassigned' }}
                            </div>

                            @if(filled($event['description']))
                                <p class="sh-event-desc">{{ $event['description'] }}</p>
                            @endif

                            @if(count($event['details']))
                                <div class="sh-event-details">
                                    @foreach($event['details'] as $label => $value)
                                        <div>
                                            <span>{{ $label }}</span>
                                            <p>{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="sh-event-foot">
                                <a href="{{ $event['url'] }}">
                                    {{ $event['type'] === 'maintenance' ? 'Open maintenance schedule →' : 'Open service ticket →' }}
                                </a>
                            </div>

                        </div>
                    </div>

                @endforeach

            </div>

        @empty

            <div class="sh-empty">
                <strong>No service history yet.</strong>
                <span>This device has no service tickets or maintenance records.</span>
            </div>

        @endforelse

    </div>
</section>

@include('service-history._style')

@endsection
