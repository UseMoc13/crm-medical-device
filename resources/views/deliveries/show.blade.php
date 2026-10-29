@extends('layouts.app')

@section('title', 'Delivery Details')

@section('content')

@php
    $deliveryStatuses = [
        'pending' => 'Pending',
        'in_transit' => 'In Transit',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    $statusLabel = $deliveryStatuses[
        strtolower($delivery->status ?? '')
    ] ?? ucfirst($delivery->status ?? 'Unknown');

    $statusClass = \Illuminate\Support\Str::slug(
        strtolower($delivery->status ?? 'unknown')
    );
@endphp

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>{{ $delivery->delivery_number }}</h1>
        <p>
            View delivery details and related sales order information.
        </p>
    </div>

    <div class="actions">
        <a
            href="{{ route('deliveries.edit', $delivery->delivery_id) }}"
            class="btn primary"
        >
            ✎ Edit Delivery
        </a>

        <a
            href="{{ route('deliveries.index') }}"
            class="btn"
        >
            ← Back
        </a>
    </div>
</div>

{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

{{-- DELIVERY INFORMATION --}}
<div class="information-layout">

    <div class="card information-card delivery-information-card">

        <div class="card-head">
            <div>
                <h3>Delivery Information</h3>
                <p>Basic delivery and shipment information.</p>
            </div>

            <span class="delivery-status-badge status-{{ $statusClass }}">
                {{ $statusLabel }}
            </span>
        </div>

        <div class="card-body">

            <div class="delivery-information-grid">

                <div class="information-field">
                    <span class="information-label">
                        Delivery Number
                    </span>

                    <strong class="information-value delivery-number">
                        {{ $delivery->delivery_number }}
                    </strong>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Delivery Date
                    </span>

                    <strong class="information-value">
                        {{ $delivery->delivery_date
                            ? $delivery->delivery_date->format('d M Y')
                            : '-' }}
                    </strong>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Delivery Status
                    </span>

                    <span class="delivery-status-badge status-{{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Created At
                    </span>

                    <strong class="information-value">
                        {{ $delivery->created_at
                            ? $delivery->created_at->format('d M Y, H:i')
                            : '-' }}
                    </strong>
                </div>

                <div class="information-field full-width">
                    <span class="information-label">
                        Delivery Address
                    </span>

                    <strong class="information-value delivery-text">
                        {{ $delivery->delivery_address ?: '-' }}
                    </strong>
                </div>

                <div class="information-field full-width">
                    <span class="information-label">
                        Notes
                    </span>

                    <div class="information-value delivery-text">
                        {{ $delivery->notes ?: '-' }}
                    </div>
                </div>

                <div class="information-field">
                    <span class="information-label">
                        Last Updated
                    </span>

                    <strong class="information-value">
                        {{ $delivery->updated_at
                            ? $delivery->updated_at->format('d M Y, H:i')
                            : '-' }}
                    </strong>
                </div>

            </div>

        </div>
    </div>

    {{-- SALES ORDER INFORMATION --}}
    <div class="card information-card sales-order-information-card">

        <div class="card-head">
            <div>
                <h3>Sales Order Information</h3>
                <p>Sales order associated with this delivery.</p>
            </div>
        </div>

        <div class="card-body">

            @if($delivery->salesOrder)

                <div class="sales-order-content">

                    <div class="sales-order-heading">
                        <span class="information-label">
                            Sales Order Number
                        </span>

                        <strong class="sales-order-number">
                            {{ $delivery->salesOrder->sales_order_number ?? '-' }}
                        </strong>
                    </div>

                    <div class="sales-order-grid">

                        <div class="information-field">
                            <span class="information-label">
                                Order Date
                            </span>

                            <strong class="information-value">
                                {{ $delivery->salesOrder->order_date
                                    ? \Illuminate\Support\Carbon::parse(
                                        $delivery->salesOrder->order_date
                                    )->format('d M Y')
                                    : '-' }}
                            </strong>
                        </div>

                        <div class="information-field">
                            <span class="information-label">
                                Sales Order Status
                            </span>

                            <strong class="information-value">
                                {{ $delivery->salesOrder->status ?? '-' }}
                            </strong>
                        </div>

                    </div>

                </div>

            @else

                <div class="empty-small">
                    No sales order information available.
                </div>

            @endif

        </div>
    </div>

</div>

<style>
    /* =====================================================
       DELIVERY DETAIL
    ====================================================== */

    .delivery-information-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 24px;
    }

    .delivery-information-grid .information-field {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
    }

    .delivery-information-grid .full-width {
        grid-column: 1 / -1;
    }

    .delivery-information-grid .information-label {
        color: var(--text-muted, #64748b);
        font-size: 13px;
    }

    .delivery-information-grid .information-value {
        color: var(--text-primary, #1e293b);
        font-size: 14px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .delivery-number {
        letter-spacing: 0.2px;
    }

    .delivery-text {
        white-space: pre-line;
        line-height: 1.7;
        font-weight: 400 !important;
    }

    /* =====================================================
       DELIVERY STATUS BADGE
    ====================================================== */

    .delivery-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: fit-content;
        padding: 6px 11px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-in-transit {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-delivered {
        background: #dcfce7;
        color: #166534;
    }

    .status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-unknown {
        background: #f1f5f9;
        color: #475569;
    }

    /* =====================================================
       SALES ORDER INFORMATION
    ====================================================== */

    .sales-order-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .sales-order-heading {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px;
        border: 1px solid var(--border-color, #e2e8f0);
        border-radius: 8px;
        background: var(--surface-secondary, #f8fafc);
    }

    .sales-order-number {
        color: var(--text-primary, #1e293b);
        font-size: 17px;
        overflow-wrap: anywhere;
    }

    .sales-order-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .sales-order-grid .information-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .sales-order-grid .information-label {
        color: var(--text-muted, #64748b);
        font-size: 13px;
    }

    .sales-order-grid .information-value {
        color: var(--text-primary, #1e293b);
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    @media (max-width: 768px) {
        .delivery-information-grid,
        .sales-order-grid {
            grid-template-columns: 1fr;
        }

        .delivery-information-grid .full-width {
            grid-column: auto;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 16px;
        }

        .page-head .actions {
            flex-wrap: wrap;
        }
    }
</style>

@endsection