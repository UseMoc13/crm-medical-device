@extends('layouts.app')

@section('title', 'Sales Order Details')

@section('content')

{{-- Page Header --}}
<div class="page-head">
    <div>
        <h1>Sales Order Details</h1>
        <p>View sales order information and associated products.</p>
    </div>

    <div class="actions">
        <a href="{{ route('sales_orders.index') }}" class="btn secondary">
            Back to Sales Orders
        </a>
    </div>
</div>

{{-- Flash Messages --}}
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

{{-- Sales Order Information --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Sales Order Information</h3>
            <p>General information about this sales order.</p>
        </div>

        <span class="sales-order-status-badge {{ strtolower($order->status ?? '') === 'confirmed' ? 'confirmed' : '' }}">
            {{ ucfirst($order->status ?? 'unknown') }}
        </span>
    </div>

    <div class="card-body">
        <div class="form-grid">

            <div class="form-group">
                <label>Sales Order Number</label>
                <div class="sales-order-detail-value">
                    {{ $order->sales_order_number ?? '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Quotation Number</label>
                <div class="sales-order-detail-value">
                    {{ $order->quotation->quotation_number ?? '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Customer</label>
                <div class="sales-order-detail-value">
                    {{ $order->quotation->opportunity->customer->customer_name ?? '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Order Date</label>
                <div class="sales-order-detail-value">
                    {{ $order->order_date
                        ? $order->order_date->format('d M Y')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Expected Delivery Date</label>
                <div class="sales-order-detail-value">
                    {{ $order->delivery_date
                        ? $order->delivery_date->format('d M Y')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Contract</label>
                <div class="sales-order-detail-value">
                    {{ $order->contract->contract_number ?? 'No contract linked' }}
                </div>
            </div>

            <div class="form-group">
                <label>Created At</label>
                <div class="sales-order-detail-value">
                    {{ $order->created_at
                        ? $order->created_at->format('d M Y, H:i')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Last Updated</label>
                <div class="sales-order-detail-value">
                    {{ $order->updated_at
                        ? $order->updated_at->format('d M Y, H:i')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Notes</label>
                <div class="sales-order-detail-value">
                    {{ $order->notes ?: '-' }}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Product Items --}}
<div class="card sales-order-items-card">
    <div class="card-head">
        <div>
            <h3>Order Items</h3>
            <p>Products included in this sales order.</p>
        </div>
    </div>

    <div class="card-body">
        <div class="sales-order-table-wrap">
            <table class="sales-order-detail-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Product Code</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                {{ $item->product->product_code ?? '-' }}
                            </td>

                            <td>
                                {{ $item->product->product_name
                                    ?? 'Product not found' }}
                            </td>

                            <td>{{ $item->quantity }}</td>

                            <td>
                                Rp {{ number_format(
                                    (float) $item->unit_price,
                                    0, ',', '.'
                                ) }}
                            </td>

                            <td>
                                Rp {{ number_format(
                                    (float) ($item->discount ?? 0),
                                    0, ',', '.'
                                ) }}
                            </td>

                            <td>
                                Rp {{ number_format(
                                    (float) $item->subtotal,
                                    0, ',', '.'
                                ) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No products found for this sales order.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Financial Summary --}}
<div class="card sales-order-summary-card">
    <div class="card-head">
        <div>
            <h3>Financial Summary</h3>
            <p>Summary of sales order amounts.</p>
        </div>
    </div>

    <div class="card-body">
        <div class="sales-order-summary-grid">

            <div class="sales-order-summary-item">
                <span>Subtotal</span>
                <strong>
                    Rp {{ number_format(
                        (float) $order->subtotal,
                        0, ',', '.'
                    ) }}
                </strong>
            </div>

            <div class="sales-order-summary-item">
                <span>Tax</span>
                <strong>
                    Rp {{ number_format(
                        (float) $order->tax,
                        0, ',', '.'
                    ) }}
                </strong>
            </div>

            <div class="sales-order-summary-item total">
                <span>Total Amount</span>
                <strong>
                    Rp {{ number_format(
                        (float) $order->total_amount,
                        0, ',', '.'
                    ) }}
                </strong>
            </div>

        </div>
    </div>
</div>


<style>
    
    /* Sales Order Status */
    .sales-order-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    background: #eef2ff;
    color: #4338ca;
    white-space: nowrap;
}

.sales-order-status-badge.confirmed {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

    /* Detail Values */
    .sales-order-detail-value {
        min-height: 42px;
        padding: 11px 13px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #f8fafc;
        color: #334155;
        font-size: 14px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Product Table */
.sales-order-table-wrap {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
}

.sales-order-detail-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    font-size: 13px;
    background: #ffffff;
}

/* Header */
.sales-order-detail-table thead th {
    padding: 14px;
    background: #f1f5f9;
    color: #334155;
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #cbd5e1;
}

/* Table Cells */
.sales-order-detail-table tbody td {
    padding: 12px 14px;
    color: #334155;
    border: 1px solid #cbd5e1;
    vertical-align: middle;
}

/* Alternating Rows */
.sales-order-detail-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.sales-order-detail-table tbody tr:nth-child(odd) {
    background: #ffffff;
}

/* Hover Effect */
.sales-order-detail-table tbody tr:hover {
    background: #eff6ff;
}

/* Column Alignment */
.sales-order-detail-table td:nth-child(1),
.sales-order-detail-table td:nth-child(4) {
    text-align: center;
}

.sales-order-detail-table td:nth-child(5),
.sales-order-detail-table td:nth-child(6),
.sales-order-detail-table td:nth-child(7) {
    text-align: right;
    white-space: nowrap;
}

/* Empty State */
.sales-order-detail-table .empty-state {
    padding: 24px;
    text-align: center;
    color: #64748b;
}

    /* Financial Summary */
    .sales-order-summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .sales-order-summary-item {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 18px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
    }

    .sales-order-summary-item span {
        color: #64748b;
        font-size: 13px;
    }

    .sales-order-summary-item strong {
        color: #1e293b;
        font-size: 18px;
        overflow-wrap: anywhere;
    }

    .sales-order-summary-item.total {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    .sales-order-summary-item.total strong {
        font-size: 20px;
    }

    @media (max-width: 768px) {
        .sales-order-summary-grid {
            grid-template-columns: 1fr;
        }

        .page-head {
            gap: 16px;
            flex-wrap: wrap;
        }

        .sales-order-detail-table {
            min-width: 850px;
        }
    }

</style>
@endsection
