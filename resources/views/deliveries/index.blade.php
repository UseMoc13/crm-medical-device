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
                                    <a
                                        href="{{ route('deliveries.show', $delivery->delivery_id) }}"
                                        class="action-btn view"
                                        title="View"
                                        aria-label="View delivery"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>

                                    <a
                                        href="{{ route('deliveries.edit', $delivery->delivery_id) }}"
                                        class="action-btn edit"
                                        title="Edit"
                                        aria-label="Edit delivery"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M12 20h9"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                        </svg>
                                    </a>

                                    <form
                                        action="{{ route('deliveries.destroy', $delivery->delivery_id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this delivery?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete"
                                            aria-label="Delete delivery"
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
                            <td colspan="7" class="empty-state">
                                No delivery records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div>
                Showing {{ $deliveries->firstItem() ?? 0 }}
                to {{ $deliveries->lastItem() ?? 0 }}
                of {{ $deliveries->total() }} deliveries
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

.empty-state {
        padding: 32px !important;
        color: #6b7280;
        text-align: center !important;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        padding: 7px;
        border: 1px solid #e5e7eb;
        border-radius: 7px;
        background: #fff;
        cursor: pointer;
        text-decoration: none;
    }

    .action-btn svg {
        width: 17px;
        height: 17px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .action-btn.view {
        color: #2563eb;
    }

    .action-btn.edit {
        color: #d97706;
    }

    .action-btn.delete {
        color: #dc2626;
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