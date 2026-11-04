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

<div class="card delivery-workflow">
    <div class="card-head"><div><h3>Delivery Workflow</h3><p>Record shipping progress after completing the physical action.</p></div></div>
    <div class="card-body">
        <div class="delivery-workflow-body">
            @if($delivery->status === 'pending')
                <button type="button" class="btn primary" data-delivery-action="in_transit" data-delivery-title="Mark as In Transit?" data-delivery-text="Confirm the shipment has been dispatched.">Mark as In Transit</button>
                <button type="button" class="btn" data-delivery-action="cancelled" data-delivery-title="Cancel Delivery?" data-delivery-text="Cancel this pending delivery?">Cancel Delivery</button>
            @elseif($delivery->status === 'in_transit')
                <button type="button" class="btn primary" data-delivery-action="delivered" data-delivery-title="Confirm Delivery?" data-delivery-text="Confirm that the customer has received this shipment. Inventory and serial numbers are not automatically updated yet.">Confirm Delivered</button>
            @else
                <span class="delivery-warning">This delivery has reached a final status.</span>
            @endif
        </div>
        <p class="delivery-workflow-note">Stock movements, individual serial numbers, and installation scheduling will be connected in a separate integration step.</p>
    </div>
</div>

<div class="delivery-confirm" id="deliveryConfirm" hidden role="dialog" aria-modal="true" aria-labelledby="deliveryConfirmTitle">
    <div class="delivery-confirm-dialog">
        <div class="delivery-modal-symbol">?</div>
        <h3 id="deliveryConfirmTitle">Confirm status</h3>
        <p id="deliveryConfirmText"></p>
        <form method="POST" action="{{ route('deliveries.change-status', $delivery->delivery_id) }}" id="deliveryStatusForm">
            @csrf
            <input type="hidden" name="status" id="deliveryNextStatus">
            <div class="delivery-confirm-actions">
                <button type="button" class="btn" id="deliveryCancel">Cancel</button>
                <button type="submit" class="btn primary">Confirm</button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('deliveryConfirm');
    const title = document.getElementById('deliveryConfirmTitle');
    const message = document.getElementById('deliveryConfirmText');
    const next = document.getElementById('deliveryNextStatus');
    const close = () => { modal.hidden = true; };
    document.querySelectorAll('[data-delivery-action]').forEach(button => {
        button.addEventListener('click', () => {
            title.textContent = button.dataset.deliveryTitle;
            message.textContent = button.dataset.deliveryText;
            next.value = button.dataset.deliveryAction;
            modal.hidden = false;
        });
    });
    document.getElementById('deliveryCancel').addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if(e.key === 'Escape') close(); });
})();
</script>

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

                        <a class="delivery-origin-link sales-order-number" href="{{ route('sales-orders.show', $delivery->sales_order_id) }}">
                            {{ $delivery->salesOrder->sales_order_number ?? '-' }}
                        </a>
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

@include('deliveries._style')

@endsection