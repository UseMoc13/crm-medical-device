
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

<section class="card ticket-resolution-card">
    <div class="card-head">
        <div>
            <h3>Resolution Information</h3>
            <p>Service resolution and completion details.</p>
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

<section class="card ticket-parts-card">
    <div class="card-head">
        <div>
            <h3>Service Parts</h3>
            <p>Products and replacement parts recorded for this service ticket.</p>
        </div>

        <div class="parts-count">
            {{ $totalParts }} {{ $totalParts === 1 ? 'Item' : 'Items' }}
        </div>
    </div>

    <div class="card-body">

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
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <strong>Total</strong>
                            </td>

                            <td>
                                <strong>{{ $formatCurrency($parts->sum('unit_price')) }}</strong>
                            </td>

                            <td>
                                <strong class="subtotal-value">
                                    {{ $formatCurrency($totalPartsCost) }}
                                </strong>
                            </td>

                            <td></td>
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

<style>
    .ticket-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .ticket-information-card,
    .ticket-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .ticket-information-card .card-body,
    .ticket-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .ticket-device-card,
    .ticket-customer-card,
    .ticket-technician-card,
    .ticket-resolution-card,
    .ticket-parts-card,
    .ticket-progress-card,
    .ticket-registration-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .ticket-overview-grid .card-head h3,
    .ticket-device-card .card-head h3,
    .ticket-customer-card .card-head h3,
    .ticket-technician-card .card-head h3,
    .ticket-resolution-card .card-head h3,
    .ticket-parts-card .card-head h3,
    .ticket-progress-card .card-head h3,
    .ticket-registration-card .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
        font-weight: 700;
    }

    .ticket-overview-grid .card-head p,
    .ticket-device-card .card-head p,
    .ticket-customer-card .card-head p,
    .ticket-technician-card .card-head p,
    .ticket-resolution-card .card-head p,
    .ticket-parts-card .card-head p,
    .ticket-progress-card .card-head p,
    .ticket-registration-card .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    .ticket-identity {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 20px;
        margin-bottom: 21px;
        border-bottom: 1px solid #edf0f5;
    }

    .ticket-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        width: 50px;
        height: 50px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 14px;
        font-weight: 700;
    }

    .ticket-identity-content {
        flex: 1;
        min-width: 0;
    }

    .ticket-identity-content h2 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .ticket-identity-content > span {
        color: #7d8797;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .ticket-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }

    .status-badge.open,
    .status-text.open {
        color: #5264a6;
    }

    .status-badge.open {
        background: #eef2ff;
    }

    .status-badge.progress,
    .status-text.progress {
        color: #167d70;
    }

    .status-badge.progress {
        background: #eaf7f3;
    }

    .status-badge.hold,
    .status-text.hold {
        color: #a66b19;
    }

    .status-badge.hold {
        background: #fff4e5;
    }

    .status-badge.resolved,
    .status-text.resolved,
    .status-badge.closed,
    .status-text.closed {
        color: #167d70;
    }

    .status-badge.resolved,
    .status-badge.closed {
        background: #eaf7f3;
    }

    .status-badge.danger,
    .status-text.danger {
        color: #b34b4b;
    }

    .status-badge.danger {
        background: #fceeee;
    }

    .status-badge.unknown,
    .status-text.unknown {
        color: #596579;
    }

    .status-badge.unknown {
        background: #f1f3f6;
    }

    .status-text,
    .priority-text {
        font-size: 12px;
        font-weight: 700;
    }

    .priority-text.low {
        color: #167d70;
    }

    .priority-text.medium {
        color: #5264a6;
    }

    .priority-text.high {
        color: #c27a28;
    }

    .priority-text.urgent {
        color: #b34b4b;
    }

    .priority-text.unknown {
        color: #596579;
    }

    .ticket-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .info-item,
    .registration-item {
        min-width: 0;
    }

    .info-label {
        display: block;
        margin-bottom: 6px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .info-item strong,
    .registration-item strong {
        display: block;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .ticket-code,
    .registration-id {
        font-family: Consolas, Monaco, monospace;
        letter-spacing: .2px;
    }

    .ticket-description {
        padding-top: 18px;
        margin-top: 20px;
        border-top: 1px solid #edf0f5;
    }

    .ticket-description p {
        margin: 0;
        color: #596579;
        font-size: 12px;
        line-height: 1.8;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .ticket-summary-list {
        display: flex;
        flex-direction: column;
    }

    .ticket-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 0;
    }

    .ticket-summary-item + .ticket-summary-item {
        border-top: 1px solid #edf0f5;
    }

    .summary-item-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        font-size: 11px;
        font-weight: 700;
    }

    .ticket-summary-icon,
    .parts-summary-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .customer-summary-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .priority-summary-icon {
        background: #fff4e5;
        color: #c27a28;
    }

    .status-summary-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .summary-item-content {
        flex: 1;
        min-width: 0;
    }

    .summary-item-content > span {
        display: block;
        margin-bottom: 4px;
        color: #8a94a6;
        font-size: 10px;
    }

    .summary-item-content > strong {
        display: block;
        margin-bottom: 3px;
        color: #17284f;
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .summary-item-content > small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .ticket-related-layout {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
        padding: 5px 0;
    }

    .related-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
    }

    .device-related-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .customer-related-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .technician-related-icon {
        background: #fff4e5;
        color: #c27a28;
    }

    .related-content {
        flex: 1;
        min-width: 0;
    }

    .related-label {
        display: block;
        margin-bottom: 5px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .related-content h4 {
        margin: 0 0 13px;
        color: #17284f;
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .related-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 32px;
    }

    .related-meta > div {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 120px;
    }

    .related-meta span {
        color: #8a94a6;
        font-size: 10px;
    }

    .related-meta strong {
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .related-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding: 6px 9px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .related-indicator-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .ticket-empty-state {
        padding: 30px 20px;
        text-align: center;
    }

    .ticket-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 14px;
        font-weight: 700;
    }

    .ticket-empty-state h4 {
        margin: 0 0 6px;
        color: #34415c;
        font-size: 13px;
    }

    .ticket-empty-state p {
        max-width: 480px;
        margin: 0 auto;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.7;
    }

    .empty-state-actions {
        margin-top: 15px;
    }

    .resolution-content {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 5px 0;
    }

    .resolution-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 16px;
        font-weight: 700;
    }

    .resolution-details {
        flex: 1;
        min-width: 0;
    }

    .resolution-details p {
        margin: 0;
        color: #596579;
        font-size: 12px;
        line-height: 1.8;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .resolution-meta {
        display: flex;
        flex-direction: column;
        gap: 5px;
        padding-top: 14px;
        margin-top: 14px;
        border-top: 1px solid #edf0f5;
    }

    .resolution-meta span {
        color: #8a94a6;
        font-size: 10px;
    }

    .resolution-meta strong {
        color: #34415c;
        font-size: 11px;
    }

    .parts-count {
        padding: 6px 10px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ticket-parts-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .ticket-parts-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px;
    }

    .ticket-parts-table th {
        padding: 12px 13px;
        background: #f8f9fc;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    .ticket-parts-table td {
        padding: 14px 13px;
        border-bottom: 1px solid #edf0f5;
        color: #596579;
        font-size: 11px;
        vertical-align: middle;
    }

    .ticket-parts-table tbody tr:last-child td {
        border-bottom: none;
    }

    .part-product {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 180px;
    }

    .part-product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 11px;
        font-weight: 700;
    }

    .part-product strong {
        display: block;
        margin-bottom: 4px;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .part-product small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
    }

    .quantity-value {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        padding: 5px 8px;
        border-radius: 5px;
        background: #f1f3f6;
        color: #34415c;
        font-weight: 700;
    }

    .subtotal-value {
        color: #167d70;
        font-weight: 700;
    }

    .part-notes {
        display: block;
        max-width: 180px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .ticket-parts-table tfoot td {
        padding: 14px 13px;
        background: #f8f9fc;
        border-top: 1px solid #edf0f5;
        border-bottom: none;
        color: #34415c;
    }

    .ticket-progress-list {
        display: flex;
        align-items: flex-start;
        gap: 0;
        width: 100%;
    }

    .progress-step {
        position: relative;
        display: flex;
        flex: 1;
        flex-direction: column;
        align-items: center;
        min-width: 0;
        text-align: center;
    }

    .progress-step:not(:last-child)::after {
        position: absolute;
        top: 15px;
        left: calc(50% + 17px);
        width: calc(100% - 34px);
        height: 2px;
        background: #e8ebf1;
        content: '';
    }

    .progress-step.completed:not(:last-child)::after {
        background: #15966f;
    }

    .progress-marker {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        margin-bottom: 10px;
        border: 2px solid #e8ebf1;
        border-radius: 50%;
        background: #fff;
        color: #9aa3b2;
        font-size: 11px;
        font-weight: 700;
    }

    .progress-step.completed .progress-marker {
        border-color: #15966f;
        background: #15966f;
        color: #fff;
    }

    .progress-step.current .progress-marker {
        box-shadow: 0 0 0 4px #eaf7f3;
    }

    .progress-step-content strong {
        display: block;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .progress-step-content small {
        display: block;
        margin-top: 4px;
        color: #9aa3b2;
        font-size: 9px;
    }

    .progress-step.current .progress-step-content strong {
        color: #167d70;
    }

    .ticket-hold-notice,
    .ticket-cancelled-notice {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 13px 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        background: #fff4e5;
        color: #a66b19;
    }

    .ticket-cancelled-notice {
        display: block;
        background: #fceeee;
        color: #b34b4b;
    }

    .hold-notice-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 24px;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f8e5c8;
        font-size: 12px;
        font-weight: 700;
    }

    .ticket-hold-notice strong,
    .ticket-cancelled-notice strong {
        display: block;
        margin-bottom: 4px;
        font-size: 11px;
    }

    .ticket-hold-notice p,
    .ticket-cancelled-notice p {
        margin: 0;
        font-size: 10px;
        line-height: 1.6;
    }

    .ticket-registration-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .registration-id {
        font-size: 11px !important;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    @media (max-width: 950px) {
        .ticket-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 650px) {
        .ticket-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .ticket-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .ticket-status {
            margin-left: 0;
        }

        .ticket-info-grid,
        .ticket-registration-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 17px;
        }

        .ticket-related-layout {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .related-content {
            flex-basis: calc(100% - 65px);
        }

        .related-indicator {
            margin-left: 61px;
        }

        .related-meta {
            gap: 12px 20px;
        }

        .ticket-progress-list {
            align-items: stretch;
            flex-direction: column;
            gap: 17px;
        }

        .progress-step {
            flex-direction: row;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .progress-step:not(:last-child)::after {
            top: 32px;
            left: 15px;
            width: 2px;
            height: 17px;
        }

        .progress-marker {
            flex-shrink: 0;
            margin-bottom: 0;
        }

        .progress-step-content {
            min-width: 0;
        }
    }
</style>

@endsection
