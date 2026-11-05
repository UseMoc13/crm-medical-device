
@extends('layouts.app')

@section('title', 'Service Ticket Details')

@section('content')

@php
    $ticketNumber = $serviceTicket->ticket_number ?? '-';
    $ticketTitle = $serviceTicket->title ?? 'Untitled Service Ticket';

    $priority = trim((string) ($serviceTicket->priority ?? 'Unknown'));
    $normalizedPriority = strtolower(str_replace(['_', '-'], ' ', $priority));

    $priorityStyles = [
        'low' => 'low',
        'medium' => 'medium',
        'high' => 'high',
        'urgent' => 'urgent',
        'critical' => 'urgent',
    ];

    $priorityClass = $priorityStyles[$normalizedPriority] ?? 'unknown';

    $status = trim((string) ($serviceTicket->status ?? 'Unknown'));
    $normalizedStatus = strtolower(str_replace(['_', '-'], ' ', $status));

    $statusStyles = [
        'open' => 'open',
        'in progress' => 'progress',
        'on hold' => 'hold',
        'resolved' => 'resolved',
        'closed' => 'closed',
        'cancelled' => 'danger',
    ];

    $statusClass = $statusStyles[$normalizedStatus] ?? 'unknown';

    $customer = $serviceTicket->customer;
    $serialNumber = $serviceTicket->serialNumber;
    $product = $serialNumber?->product;
    $technician = $serviceTicket->user;

    $customerName = $customer?->customer_name ?? 'Unknown Customer';
    $serialCode = $serialNumber?->serial_number ?? '-';
    $productName = $product?->product_name ?? 'Unknown Product';
    $productCode = $product?->product_code ?? '-';
    $technicianName = $technician?->name ?? null;

    $openedAt = $serviceTicket->opened_at
        ? \Carbon\Carbon::parse($serviceTicket->opened_at)->format('d M Y, H:i')
        : '-';

    $closedAt = $serviceTicket->closed_at
        ? \Carbon\Carbon::parse($serviceTicket->closed_at)->format('d M Y, H:i')
        : null;

    $purchaseDate = $serialNumber?->purchase_date
        ? \Carbon\Carbon::parse($serialNumber->purchase_date)->format('d M Y')
        : null;

    $description = $serviceTicket->description;
    $resolution = $serviceTicket->resolution;

    $parts = $serviceTicket->parts ?? collect();

    $totalParts = $parts->count();

    $totalQuantity = $parts->sum('quantity');

    $totalPartsCost = $parts->sum(function ($part) {
        return (float) ($part->unit_price ?? 0) * (int) ($part->quantity ?? 0);
    });

    $formatCurrency = fn ($amount) => number_format((float) $amount, 2, ',', '.');

    $hasCustomer = $customer !== null;
    $hasDevice = $serialNumber !== null;
    $hasTechnician = $technician !== null;

    $progressSteps = [
        'open' => 'Open',
        'in progress' => 'In Progress',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ];

    $progressOrder = [
        'open' => 1,
        'in progress' => 2,
        'resolved' => 3,
        'closed' => 4,
    ];

    $currentProgress = $progressOrder[$normalizedStatus] ?? 0;
@endphp

<div class="page-head">
    <div>
        <h1>Service Ticket Details</h1>
        <p>View ticket information, customer details, assigned technician, and service resolution.</p>
    </div>

    <div class="actions">
        <a href="{{ route('service-tickets.index') }}" class="btn">
            &larr; Back to Service Tickets
        </a>

        <a href="{{ route('service-tickets.edit', $serviceTicket->service_ticket_id) }}" class="btn primary">
            Edit Ticket
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="alert error" role="alert">
        <strong>Unable to save service part.</strong>
        <ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif
@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif

@include('service-tickets._workflow')

@include('service-tickets._warranty_detail')

<div class="ticket-quick-actions"><a href="#ticket-service-parts" class="btn">View Service Parts &darr;</a></div>

<div class="ticket-overview-grid">

    <section class="card ticket-information-card">
        <div class="card-head">
            <div>
                <h3>Service Ticket Information</h3>
                <p>Ticket identification and service request details.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="ticket-identity">
                <div class="ticket-icon" aria-hidden="true">
                    ST
                </div>

                <div class="ticket-identity-content">
                    <h2>{{ $ticketNumber }}</h2>
                    <span>{{ $ticketTitle }}</span>
                </div>

                <div class="ticket-status">
                    <span class="status-badge {{ $statusClass }}">
                        <span class="status-dot"></span>
                        {{ $status }}
                    </span>
                </div>
            </div>

            <div class="ticket-info-grid">

                <div class="info-item">
                    <span class="info-label">Ticket Number</span>
                    <strong class="ticket-code">{{ $ticketNumber }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Ticket Status</span>
                    <strong>
                        <span class="status-text {{ $statusClass }}">
                            {{ $status }}
                        </span>
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Ticket Title</span>
                    <strong>{{ $ticketTitle }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Priority</span>
                    <strong>
                        <span class="priority-text {{ $priorityClass }}">
                            {{ $priority }}
                        </span>
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Customer</span>
                    <strong>{{ $customerName }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Assigned Technician</span>
                    <strong>{{ $technicianName ?: 'Unassigned' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Opened At</span>
                    <strong>{{ $openedAt }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Closed At</span>
                    <strong>{{ $closedAt ?: 'Not closed yet' }}</strong>
                </div>

            </div>

            @if (filled($description))
                <div class="ticket-description">
                    <span class="info-label">Problem Description</span>
                    <p>{{ $description }}</p>
                </div>
            @endif

        </div>
    </section>

    <section class="card ticket-summary-card">
        <div class="card-head">
            <div>
                <h3>Ticket Summary</h3>
                <p>Overview of this service request.</p>
            </div>
        </div>

        <div class="card-body">

            <div class="ticket-summary-list">

                <div class="ticket-summary-item">
                    <div class="summary-item-icon ticket-summary-icon" aria-hidden="true">
                        ST
                    </div>

                    <div class="summary-item-content">
                        <span>Ticket Identification</span>
                        <strong>{{ $ticketNumber }}</strong>
                        <small>Unique service ticket reference</small>
                    </div>
                </div>

                <div class="ticket-summary-item">
                    <div class="summary-item-icon customer-summary-icon" aria-hidden="true">
                        C
                    </div>

                    <div class="summary-item-content">
                        <span>Customer</span>
                        <strong>{{ $customerName }}</strong>
                        <small>Customer associated with this request</small>
                    </div>
                </div>

                <div class="ticket-summary-item">
                    <div class="summary-item-icon priority-summary-icon" aria-hidden="true">
                        !
                    </div>

                    <div class="summary-item-content">
                        <span>Ticket Priority</span>
                        <strong>
                            <span class="priority-text {{ $priorityClass }}">
                                {{ $priority }}
                            </span>
                        </strong>
                        <small>Recorded service request priority</small>
                    </div>
                </div>

                <div class="ticket-summary-item">
                    <div class="summary-item-icon status-summary-icon" aria-hidden="true">
                        ✓
                    </div>

                    <div class="summary-item-content">
                        <span>Current Status</span>
                        <strong>
                            <span class="status-text {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </strong>
                        <small>Latest status recorded in the CRM</small>
                    </div>
                </div>

                <div class="ticket-summary-item">
                    <div class="summary-item-icon parts-summary-icon" aria-hidden="true">
                        P
                    </div>

                    <div class="summary-item-content">
                        <span>Service Parts</span>
                        <strong>{{ $totalParts }} {{ $totalParts === 1 ? 'Item' : 'Items' }}</strong>
                        <small>{{ $totalQuantity }} total unit(s) recorded</small>
                    </div>
                </div>

            </div>

        </div>
    </section>

</div>

<div class="ticket-three-column-grid">
<section class="card ticket-device-card">
    <div class="card-head">
        <div>
            <h3>Device Information</h3>
            <p>Device and product associated with this service ticket.</p>
        </div>
    </div>

    <div class="card-body">

        @if ($hasDevice)

            <div class="ticket-related-layout">

                <div class="related-icon device-related-icon" aria-hidden="true">
                    SN
                </div>

                <div class="related-content">
                    <span class="related-label">Registered Device</span>
                    <h4>{{ $serialCode }}</h4>

                    <div class="related-meta">

                        <div>
                            <span>Product Name</span>
                            <strong>{{ $productName }}</strong>
                        </div>

                        <div>
                            <span>Product Code</span>
                            <strong>{{ $productCode }}</strong>
                        </div>

                        <div>
                            <span>Purchase Date</span>
                            <strong>{{ $purchaseDate ?: 'Not recorded' }}</strong>
                        </div>

                        <div>
                            <span>Device Status</span>
                            <strong>{{ $serialNumber->status ?? '-' }}</strong>
                        </div>

                    </div>
                </div>

                <div class="related-indicator">
                    <span class="related-indicator-dot"></span>
                    Linked Device
                </div>

            </div>

        @else

            <div class="ticket-empty-state">
                <div class="ticket-empty-icon" aria-hidden="true">SN</div>
                <h4>No Device Information</h4>
                <p>No serial number is currently associated with this service ticket.</p>
            </div>

        @endif

    </div>
</section>

<section class="card ticket-customer-card">
    <div class="card-head">
        <div>
            <h3>Customer Information</h3>
            <p>Customer associated with this service request.</p>
        </div>
    </div>

    <div class="card-body">

        @if ($hasCustomer)

            <div class="ticket-related-layout">

                <div class="related-icon customer-related-icon" aria-hidden="true">
                    C
                </div>

                <div class="related-content">
                    <span class="related-label">Registered Customer</span>
                    <h4>{{ $customerName }}</h4>

                    <div class="related-meta">

                        <div>
                            <span>Customer ID</span>
                            <strong>{{ $customer->customer_id }}</strong>
                        </div>

                        <div>
                            <span>Device Serial Number</span>
                            <strong>{{ $hasDevice ? $serialCode : '-' }}</strong>
                        </div>

                        <div>
                            <span>Service Ticket</span>
                            <strong>{{ $ticketNumber }}</strong>
                        </div>

                    </div>
                </div>

                <div class="related-indicator">
                    <span class="related-indicator-dot"></span>
                    Linked Customer
                </div>

            </div>

        @else

            <div class="ticket-empty-state">
                <div class="ticket-empty-icon" aria-hidden="true">C</div>
                <h4>No Customer Information</h4>
                <p>No customer record is currently associated with this service ticket.</p>
            </div>

        @endif

    </div>
</section>

<section class="card ticket-technician-card">
    <div class="card-head">
        <div>
            <h3>Technician Assignment</h3>
            <p>Technician responsible for handling this service request.</p>
        </div>
    </div>

    <div class="card-body">

        @if ($hasTechnician)

            <div class="ticket-related-layout">

                <div class="related-icon technician-related-icon" aria-hidden="true">
                    T
                </div>

                <div class="related-content">
                    <span class="related-label">Assigned Technician</span>
                    <h4>{{ $technicianName }}</h4>

                    <div class="related-meta">

                        <div>
                            <span>User ID</span>
                            <strong>{{ $technician->user_id }}</strong>
                        </div>

                        <div>
                            <span>Assignment Status</span>
                            <strong>Technician Assigned</strong>
                        </div>

                        <div>
                            <span>Ticket Number</span>
                            <strong>{{ $ticketNumber }}</strong>
                        </div>

                    </div>
                </div>

                <div class="related-indicator">
                    <span class="related-indicator-dot"></span>
                    Assigned
                </div>

            </div>

        @else

            <div class="ticket-empty-state">
                <div class="ticket-empty-icon" aria-hidden="true">T</div>
                <h4>No Technician Assigned</h4>
                <p>This service ticket has not been assigned to a technician. Edit the ticket to assign a responsible technician.</p>

                <div class="empty-state-actions">
                    <a href="{{ route('service-tickets.edit', $serviceTicket->service_ticket_id) }}" class="btn">
                        Assign Technician
                    </a>
                </div>
            </div>

        @endif

    </div>
</section>


</div>

<section class="card ticket-resolution-card">
    <div class="card-head">
        <div>
            <h3>Latest Resolution</h3>
            <p>Current resolution summary. All saved versions are listed in Resolution History below.</p>
        </div>
    </div>

    <div class="card-body">

        @if (filled($resolution))

            <div class="resolution-content">
                <div class="resolution-icon" aria-hidden="true">✓</div>

                <div class="resolution-details">
                    <span class="related-label">Recorded Resolution</span>
                    <p>{{ $resolution }}</p>

                    @if ($closedAt)
                        <div class="resolution-meta">
                            <span>Closed At</span>
                            <strong>{{ $closedAt }}</strong>
                        </div>
                    @endif
                </div>
            </div>

        @else

            <div class="ticket-empty-state">
                <div class="ticket-empty-icon" aria-hidden="true">R</div>
                <h4>No Resolution Recorded</h4>
                <p>
                    @if ($normalizedStatus === 'closed' || $normalizedStatus === 'resolved')
                        This ticket is marked as {{ $status }}, but no resolution details have been recorded.
                    @else
                        The service resolution will appear here once it has been recorded.
                    @endif
                </p>
            </div>

        @endif

    </div>
</section>


<section class="card ticket-resolution-history-card">
    <div class="card-head"><div><h3>Resolution History</h3><p>Chronological record of each saved resolution and ticket completion event. Existing records are never overwritten.</p></div><span class="ticket-resolution-count">{{ $resolutionHistory->count() }} entries</span></div>
    <div class="card-body">
        @forelse($resolutionHistory as $log)
            <div class="ticket-resolution-history-entry">
                <div class="ticket-resolution-history-meta"><strong>{{ ucwords(str_replace('_', ' ', $log->status_from)) }} → {{ ucwords(str_replace('_', ' ', $log->status_to)) }}</strong><span>{{ \Illuminate\Support\Carbon::parse($log->created_at)->format('d M Y, H:i') }}</span></div>
                <p>{{ $log->resolution }}</p>
            </div>
        @empty
            <p class="ticket-resolution-history-empty">No resolution history has been recorded yet. New resolution updates will appear here after saving.</p>
        @endforelse
    </div>
</section>

<section class="card ticket-parts-card" id="ticket-service-parts">
    <div class="card-head">
        <div>
            <h3>Service Parts</h3>
            <p>Products and replacement parts recorded for this service ticket.</p>
        </div>

        <div class="ticket-part-header-actions">
            <span class="parts-count">{{ $totalParts }} {{ $totalParts === 1 ? 'Item' : 'Items' }}</span>
            @if($serviceTicket->status === 'in_progress')
                <button type="button" class="btn primary ticket-part-open" data-mode="add">+ Add Service Part</button>
            @endif
        </div>
    </div>

    <div class="card-body">
        @if($serviceTicket->status !== 'in_progress')
            <p class="ticket-parts-guidance">To add or edit Service Parts, first change the ticket status to <strong>In Progress</strong> using Service Ticket Workflow above. Parts already recorded remain visible below.</p>
        @elseif($partProducts->isEmpty())
            <p class="ticket-parts-guidance">No products are available. Register products before adding a Service Part.</p>
        @else
            <p class="ticket-parts-guidance">Select <strong>+ Add Service Part</strong> at the top right of this card to record a replacement product.</p>
        @endif

        @if ($parts->isNotEmpty())

            <div class="ticket-parts-table-wrap">
                <table class="ticket-parts-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Subtotal</th>
                            <th>Notes</th>
                            @if($serviceTicket->status === 'in_progress')<th>Actions</th>@endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($parts as $part)
                            @php
                                $partProduct = $part->product;
                                $quantity = (int) ($part->quantity ?? 0);
                                $unitPrice = (float) ($part->unit_price ?? 0);
                                $subtotal = $quantity * $unitPrice;
                            @endphp

                            <tr>
                                <td>
                                    <div class="part-product">
                                        <div class="part-product-icon" aria-hidden="true">
                                            P
                                        </div>

                                        <div>
                                            <strong>{{ $partProduct?->product_name ?? 'Unknown Product' }}</strong>
                                            <small>{{ $partProduct?->product_code ?? '-' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="quantity-value">{{ $quantity }}</span>
                                </td>

                                <td>
                                    {{ $formatCurrency($unitPrice) }}
                                </td>

                                <td>
                                    <strong class="subtotal-value">
                                        {{ $formatCurrency($subtotal) }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="part-notes">
                                        {{ filled($part->notes) ? $part->notes : '-' }}
                                    </span>
                                </td>
                                @if($serviceTicket->status === 'in_progress')
                                    <td class="ticket-part-action-cell">
                                        <button type="button" class="ticket-part-action ticket-part-edit"
                                            aria-label="Edit part" title="Edit part"
                                            data-url="{{ route('service-tickets.parts.update', [$serviceTicket, $part->service_ticket_part_id]) }}"
                                            data-product="{{ $part->product_id }}"
                                            data-quantity="{{ $quantity }}"
                                            data-price="{{ $unitPrice }}"
                                            data-notes="{{ $part->notes }}">Edit</button>
                                        <button type="button" class="ticket-part-action ticket-part-remove"
                                            aria-label="Remove part" title="Remove part"
                                            data-url="{{ route('service-tickets.parts.destroy', [$serviceTicket, $part->service_ticket_part_id]) }}"
                                            data-name="{{ $partProduct?->product_name ?? 'this part' }}">Remove</button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <strong>Total</strong>
                            </td>

                            <td>
                                <strong>—</strong>
                            </td>

                            <td>
                                <strong class="subtotal-value">
                                    {{ $formatCurrency($totalPartsCost) }}
                                </strong>
                            </td>

                            <td></td>
                            @if($serviceTicket->status === 'in_progress')<td></td>@endif
                        </tr>
                    </tfoot>
                </table>
            </div>

        @else

            <div class="ticket-empty-state">
                <div class="ticket-empty-icon" aria-hidden="true">P</div>
                <h4>No Service Parts Recorded</h4>
                <p>No replacement products or service parts have been recorded for this ticket.</p>
            </div>

        @endif

    </div>
</section>

<section class="card ticket-progress-card">
    <div class="card-head">
        <div>
            <h3>Ticket Progress</h3>
            <p>Overview of the service ticket lifecycle.</p>
        </div>
    </div>

    <div class="card-body">

        @if ($normalizedStatus === 'on hold')

            <div class="ticket-hold-notice">
                <span class="hold-notice-icon">!</span>
                <div>
                    <strong>Ticket On Hold</strong>
                    <p>This service ticket is currently on hold. The progress will continue when the ticket is resumed.</p>
                </div>
            </div>

        @endif

        @if ($normalizedStatus === 'cancelled')

            <div class="ticket-cancelled-notice">
                <strong>Ticket Cancelled</strong>
                <p>This service ticket has been cancelled.</p>
            </div>

        @else

            <div class="ticket-progress-list">

                @foreach ($progressSteps as $stepKey => $stepLabel)
                    @php
                        $stepNumber = $progressOrder[$stepKey];
                        $isCompleted = $currentProgress >= $stepNumber;
                        $isCurrent = $currentProgress === $stepNumber;
                    @endphp

                    <div class="progress-step {{ $isCompleted ? 'completed' : '' }} {{ $isCurrent ? 'current' : '' }}">

                        <div class="progress-marker">
                            @if ($isCompleted)
                                <span>✓</span>
                            @else
                                <span>{{ $stepNumber }}</span>
                            @endif
                        </div>

                        <div class="progress-step-content">
                            <strong>{{ $stepLabel }}</strong>

                            @if ($isCurrent)
                                <small>Current ticket status</small>
                            @elseif ($isCompleted)
                                <small>Completed</small>
                            @else
                                <small>Pending</small>
                            @endif
                        </div>

                    </div>
                @endforeach

            </div>

        @endif

    </div>
</section>

<section class="card ticket-registration-card">
    <div class="card-head">
        <div>
            <h3>Registration Information</h3>
            <p>System identifiers and related record references.</p>
        </div>
    </div>

    <div class="card-body">

        <div class="ticket-registration-grid">

            <div class="registration-item">
                <span class="info-label">Service Ticket Record ID</span>
                <strong class="registration-id">{{ $serviceTicket->service_ticket_id }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Customer Record ID</span>
                <strong class="registration-id">{{ $serviceTicket->customer_id }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Serial Number Record ID</span>
                <strong class="registration-id">{{ $serviceTicket->serial_number_id }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Technician User ID</span>
                <strong class="registration-id">{{ $serviceTicket->user_id ?: '-' }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Ticket Opened</span>
                <strong>{{ $openedAt }}</strong>
            </div>

            <div class="registration-item">
                <span class="info-label">Ticket Closed</span>
                <strong>{{ $closedAt ?: '-' }}</strong>
            </div>

        </div>

    </div>
</section>





@include('service-tickets._parts_interaction')
@include('service-tickets._style')

@endsection
