@extends('layouts.app')

@section('title', 'Deliveries')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Deliveries</h1>
        <p>Manage product deliveries and track delivery status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('deliveries.create') }}" class="btn primary">
            + Add Delivery
        </a>
    </div>
</div>

{{-- SUCCESS MESSAGE --}}
@if (session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

{{-- ERROR MESSAGE --}}
@if (session('error'))
    <div class="alert danger">
        {{ session('error') }}
    </div>
@endif

{{-- DELIVERY TABLE --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Delivery List</h3>
            <p>Manage and monitor product deliveries.</p>
        </div>
    </div>

    <div class="card-body">

        <div class="table-wrap">
            <table class="delivery-table">
                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-delivery">Delivery Number</th>
                        <th class="col-order">Sales Order</th>
                        <th class="col-date">Delivery Date</th>
                        <th class="col-address">Delivery Address</th>
                        <th class="col-status">Status</th>
                        <th class="col-action">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($deliveries as $delivery)

                        @php
                            $status = strtolower(
                                str_replace(
                                    [' ', '-'],
                                    '_',
                                    trim($delivery->status ?? '')
                                )
                            );

                            $statusLabels = [
                                'pending' => 'Pending',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ];

                            $statusClasses = [
                                'pending' => 'warning',
                                'in_transit' => 'info',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ];

                            $statusLabel = $statusLabels[$status]
                                ?? ucwords(str_replace('_', ' ', $status));

                            $statusClass = $statusClasses[$status]
                                ?? 'secondary';
                        @endphp

                        <tr>
                            {{-- NUMBER --}}
                            <td>
                                {{ $deliveries->firstItem() + $loop->index }}
                            </td>

                            {{-- DELIVERY NUMBER --}}
                            <td>
                                <span class="delivery-number">
                                    {{ $delivery->delivery_number }}
                                </span>
                            </td>

                            {{-- SALES ORDER --}}
                            <td>
                                <span class="order-number">
                                    {{ $delivery->salesOrder->sales_order_number ?? '—' }}
                                </span>
                            </td>

                            {{-- DELIVERY DATE --}}
                            <td>
                                {{ $delivery->delivery_date
                                    ? $delivery->delivery_date->format('d M Y')
                                    : '—' }}
                            </td>

                            {{-- DELIVERY ADDRESS --}}
                            <td>
                                <div class="address-text">
                                    {{ $delivery->delivery_address ?: '—' }}
                                </div>
                            </td>

                            {{-- STATUS --}}
                            <td>
                                <span class="badge {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- ACTIONS --}}
                            <td>
                                <div class="table-actions">

                                    {{-- View --}}
                                    <a
                                        href="{{ route('deliveries.show', $delivery) }}"
                                        class="action-btn view"
                                        title="View Delivery"
                                        aria-label="View Delivery"
                                    >
                                        👁
                                    </a>

                                    {{-- Edit --}}
                                    <a
                                        href="{{ route('deliveries.edit', $delivery) }}"
                                        class="action-btn edit"
                                        title="Edit Delivery"
                                        aria-label="Edit Delivery"
                                    >
                                        ✎
                                    </a>

                                    {{-- Delete --}}
                                    <form
                                        action="{{ route('deliveries.destroy', $delivery) }}"
                                        method="POST"
                                        class="delete-form"
                                        onsubmit="return confirm('Are you sure you want to delete delivery {{ $delivery->delivery_number }}?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete Delivery"
                                            aria-label="Delete Delivery"
                                        >
                                            🗑
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <h4>No Deliveries Found</h4>
                                    <p>
                                        There are currently no delivery records.
                                    </p>

                                    <a
                                        href="{{ route('deliveries.create') }}"
                                        class="btn primary"
                                    >
                                        + Add Delivery
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- PAGE STYLES --}}
<style>
/* TABLE CONTAINER */
.table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

/* TABLE STRUCTURE */
.delivery-table {
    width: 100%;
    min-width: 950px;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

/* TABLE HEADER */
.delivery-table thead th {
    padding: 14px 12px;
    text-align: left;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    color: var(--text-muted, #64748b);
    background: var(--surface-secondary, #f8fafc);

    /* Partisi header */
    border-right: 1px solid #e2e8f0;
    border-bottom: 2px solid #cbd5e1;
}

/* TABLE BODY */
.delivery-table tbody td {
    padding: 14px 12px;
    font-size: 13px;
    vertical-align: middle;
    color: var(--text-primary, #1e293b);

    /* Partisi setiap sel */
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;

    overflow-wrap: anywhere;
}

/* HILANGKAN BORDER KANAN PADA KOLOM TERAKHIR */
.delivery-table thead th:last-child,
.delivery-table tbody td:last-child {
    border-right: none;
}

/* HILANGKAN BORDER BAWAH PADA BARIS TERAKHIR */
.delivery-table tbody tr:last-child td {
    border-bottom: none;
}

/* HOVER ROW */
.delivery-table tbody tr:hover {
    background: #f8fafc;
}

/* COLUMN WIDTHS */
.delivery-table .col-number {
    width: 6%;
}

.delivery-table .col-delivery {
    width: 16%;
}

.delivery-table .col-order {
    width: 15%;
}

.delivery-table .col-date {
    width: 12%;
}

.delivery-table .col-address {
    width: 25%;
}

.delivery-table .col-status {
    width: 12%;
}

.delivery-table .col-action {
    width: 14%;
    text-align: center;
}

/* DELIVERY NUMBER */
.delivery-number {
    font-weight: 600;
    white-space: nowrap;
}

/* SALES ORDER NUMBER */
.order-number {
    font-weight: 500;
    white-space: nowrap;
}

/* ADDRESS */
.address-text {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    overflow: hidden;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

/* ACTIONS */
.table-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    white-space: nowrap;
}

/* RESPONSIVE */
@media (max-width: 600px) {
    .delivery-table {
        min-width: 950px;
    }
}

/* STATUS BADGES */
.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.4;
    white-space: nowrap;
    border: 1px solid transparent;
}

/* PENDING */
.badge.warning {
    color: #92400e;
    background: #fef3c7;
    border-color: #fde68a;
}

/* IN TRANSIT */
.badge.info {
    color: #1d4ed8;
    background: #dbeafe;
    border-color: #bfdbfe;
}

/* DELIVERED */
.badge.success {
    color: #166534;
    background: #dcfce7;
    border-color: #bbf7d0;
}

/* CANCELLED */
.badge.danger {
    color: #991b1b;
    background: #fee2e2;
    border-color: #fecaca;
}

/* UNKNOWN STATUS */
.badge.secondary {
    color: #475569;
    background: #f1f5f9;
    border-color: #e2e8f0;
}
</style>

@endsection