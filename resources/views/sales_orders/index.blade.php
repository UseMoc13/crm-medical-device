@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3">Sales Orders</h1>
            <p class="text-muted">Daftar pesanan pelanggan.</p>
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

    <form method="GET" class="quotation-filter-bar" style="display:flex; align-items:stretch; gap:10px; width:100%; margin-bottom:16px;">
        <input
            type="text"
            name="search"
            placeholder="Cari nomor SO, quotation, atau status"
            value="{{ request('search') }}"
            style="display:block; flex:1 1 0; width:0; min-width:0; box-sizing:border-box;"
        >

        <select
            name="status"
            style="display:block; flex:0 0 25%; width:25%; min-width:0; box-sizing:border-box;"
        >
            <option value="">Semua status</option>

            @foreach ([
                'draft',
                'confirmed',
                'processing',
                'completed',
                'cancelled',
            ] as $status)
                <option
                    value="{{ $status }}"
                    @selected(request('status') === $status)
                >
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>

        <button
            type="submit"
            class="btn primary"
            style="display:block; flex:0 0 20%; width:20%; white-space:nowrap;"
        >
            Cari
        </button>
    </form>


        <div class="card">
            <div class="sales-orders-table-wrap">
                <table class="table table-hover align-middle sales-orders-table">
                    <thead>
                        <tr>
                            <th class="col-number">No.</th>
                            <th class="col-sales-order">Nomor Sales Order</th>
                            <th class="col-quotation">Quotation</th>
                            <th class="col-customer">Customer</th>
                            <th class="col-date">Tanggal Order</th>
                            <th class="col-total">Total</th>
                            <th class="col-status">Status</th>
                            <th class="col-details">Details</th>
                        </tr>
                    </thead>

                    <tbody>
                        
                        @forelse ($orders as $order)
                            <tr>

                                <td class="col-number">
                                    {{ $orders->firstItem() + $loop->index }}
                                </td>
                            
                                <td>
                                    <span class="sales-order-number">
                                        {{ $order->sales_order_number }}
                                    </span>
                                </td>

                                <td>
                                    <span class="quotation-number">
                                        {{ $order->quotation->quotation_number ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $order->quotation->opportunity->customer->customer_name ?? '-' }}
                                </td>

                                <td>
                                    {{ $order->order_date?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>
                                    <span class="sales-order-total">
                                        Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="sales-order-status status-{{ strtolower($order->status ?? 'draft') }}">
                                        {{ ucfirst($order->status ?? 'draft') }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('sales-orders.show', $order->sales_order_id) }}"
                                    class="btn btn-sm btn-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    Belum ada Sales Order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    <div class="mt-3">
        {{ $orders->links() }}
    </div>

</div>
@endsection

<style>
    
    .sales-orders-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    box-sizing: border-box;
}

.sales-orders-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 0;
    background: #fff;
}

/* Table Header */
.sales-orders-table thead th {
    padding: 14px 12px;
    background: #f3f4f6;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #d1d5db;
}

/* Table Body */
.sales-orders-table tbody td {
    padding: 13px 12px;
    font-size: 12px;
    color: #17284f;
    vertical-align: middle;
    border: 1px solid #d1d5db;
}

/* Row Hover */
.sales-orders-table tbody tr:hover {
    background-color: #f8fafc;
}

/* Sales Order Number */
.sales-orders-table .sales-order-number {
    font-weight: 600;
    color: #223a70;
    white-space: nowrap;
}

/* Quotation Number */
.sales-orders-table .quotation-number {
    font-weight: 500;
    color: #34415c;
    white-space: nowrap;
}

/* Total Amount */
.sales-orders-table .sales-order-total {
    font-weight: 600;
    color: #15966f;
    white-space: nowrap;
}

/* Status Badge */
.sales-orders-table .sales-order-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    background: #eef2f6;
    color: #4d5b70;
}

.sales-orders-table .status-draft {
    background: #f1f3f6;
    color: #667085;
}

.sales-orders-table .status-confirmed {
    background: #edf3ff;
    color: #315caa;
}

.sales-orders-table .status-processing {
    background: #fff4e5;
    color: #9a6a18;
}

.sales-orders-table .status-completed {
    background: #e8f5f0;
    color: #16805f;
}

.sales-orders-table .status-cancelled {
    background: #fff0f0;
    color: #a14d4d;
}

/* Empty State */
.sales-orders-table .empty-state {
    text-align: center;
    padding: 28px 12px;
    color: #7d8797;
}

/* Responsive */
@media (max-width: 768px) {
    .sales-orders-table {
        min-width: 850px;
    }
}
    .col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.col-sales-order {
    min-width: 170px;
}

.col-quotation {
    min-width: 150px;
}

.col-customer {
    min-width: 170px;
}

.col-date {
    min-width: 140px;
    white-space: nowrap;
}

.col-total {
    min-width: 130px;
    text-align: right;
    white-space: nowrap;
}

.col-status {
    min-width: 110px;
}

.col-details {
    min-width: 100px;
    text-align: center !important;
}
    
}
</style>