
@extends('layouts.app')

@section('title', 'Sales Orders')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Sales Orders</h1>
        <p>Manage and monitor customer sales orders.</p>
    </div>
</div>

{{-- ALERT MESSAGES --}}
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

{{-- FILTER SECTION --}}
<div class="card filter-card">
    <form method="GET" action="{{ route('sales-orders.index') }}" class="filter-form">

        <div class="filter-search">
            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search SO number, quotation, or status..."
                value="{{ request('search') }}"
            >
        </div>

        <div class="filter-status">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>

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
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn primary">
                Search
            </button>

            <a href="{{ route('sales-orders.index') }}" class="btn">
                Reset
            </a>
        </div>

    </form>
</div>

{{-- SALES ORDERS TABLE --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Sales Order List</h3>
            <p>List of registered customer sales orders.</p>
        </div>
    </div>

    <div class="sales-orders-table-wrap">
        <table class="sales-orders-table">
            <thead>
                <tr>
                    <th class="col-number">No.</th>
                    <th class="col-sales-order">Sales Order Number</th>
                    <th class="col-quotation">Quotation</th>
                    <th class="col-customer">Customer</th>
                    <th class="col-date">Order Date</th>
                    <th class="col-total">Total Amount</th>
                    <th class="col-status">Status</th>
                    <th class="col-details">Details</th>
                </tr>
            </thead>

            
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        {{-- NUMBER --}}
                        <td class="col-number">
                            {{ $orders->firstItem() + $loop->index }}
                        </td>

                        {{-- SALES ORDER NUMBER --}}
                        <td class="col-sales-order">
                            <span class="sales-order-number">
                                {{ $order->sales_order_number ?? '-' }}
                            </span>
                        </td>

                        {{-- QUOTATION --}}
                        <td class="col-quotation">
                            <span class="quotation-number">
                                {{ $order->quotation->quotation_number ?? '-' }}
                            </span>
                        </td>

                        {{-- CUSTOMER --}}
                        <td class="col-customer">
                            {{ $order->quotation->opportunity->customer->customer_name ?? '-' }}
                        </td>

                        {{-- ORDER DATE --}}
                        <td class="col-date">
                            {{ $order->order_date?->format('d/m/Y') ?? '-' }}
                        </td>

                        {{-- TOTAL AMOUNT --}}
                        <td class="col-total">
                            <span class="sales-order-total">
                                Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </span>
                        </td>

                        {{-- STATUS --}}
                        <td class="col-status">
                            @php
                                $status = strtolower($order->status ?? 'draft');
                            @endphp

                            <span class="sales-order-status status-{{ $status }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        {{-- ACTION --}}
                        <td class="col-details">
                            <a href="{{ route('sales-orders.show', $order->sales_order_id) }}"
                            class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-content">
                                <strong>No Sales Orders Found</strong>
                                <p>
                                    There are currently no sales orders matching
                                    your search criteria.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    {{-- PAGINATION --}}
    @if ($orders->hasPages())
        <div class="pagination-wrapper">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
            <div class="table-footer">
                        <div>
                            Showing {{ $orders->firstItem() ?? 0 }}
                            to {{ $orders->lastItem() ?? 0 }}
                            of {{ $orders->total() }} Sales Orders
                        </div>

    </div>

{{-- PAGE STYLES --}}
<style>
    /* FILTER CARD */
    .filter-card {
        margin-bottom: 20px;
        padding: 18px;
    }

    .filter-form {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
    }

    .filter-search {
        flex: 1;
        min-width: 0;
    }

    .filter-status {
        flex: 0 0 220px;
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .filter-form .form-control {
        box-sizing: border-box;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #fff);
        color: var(--text-primary, #1e293b);
        font-family: inherit;
        font-size: 13px;
    }

    .filter-form .form-control:focus {
        outline: none;
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    /* CARD HEADER */
    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid var(--border, #e2e8f0);
    }

    .card-head h3 {
        margin: 0 0 5px;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }

    .card-head p {
        margin: 0;
        font-size: 12px;
        color: var(--text-muted, #64748b);
    }

    .record-count {
        padding: 5px 10px;
        border-radius: 6px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

 
/* =========================================================
   SALES ORDERS TABLE - MATCH CONTRACTS MODULE
========================================================= */

.sales-orders-table-wrap {
    width: 100%;
    padding: 16px 20px 20px;
    box-sizing: border-box;
    overflow-x: auto;
    border-radius: 0 0 8px 8px;
}

.sales-orders-table-wrap .sales-orders-table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    margin-bottom: 0;
    background: #ffffff;
    border: 1px solid #dce3ed !important;
}

/* HEADER */
.sales-orders-table-wrap .sales-orders-table thead th {
    padding: 14px 12px;
    background: #f8f9fc !important;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
    white-space: nowrap;

    border: 1px solid #e1e6ef !important;
}

/* BODY */
.sales-orders-table-wrap .sales-orders-table tbody td {
    padding: 13px 12px;
    font-size: 12px;
    color: #17284f;
    vertical-align: middle;
    background: #ffffff;

    /* Garis horizontal dan vertikal */
    border: 1px solid #e8ecf3 !important;
}

/* OUTER BORDER */
.sales-orders-table-wrap .sales-orders-table th:first-child,
.sales-orders-table-wrap .sales-orders-table td:first-child {
    border-left: 1px solid #dce3ed !important;
}

.sales-orders-table-wrap .sales-orders-table th:last-child,
.sales-orders-table-wrap .sales-orders-table td:last-child {
    border-right: 1px solid #dce3ed !important;
}

.sales-orders-table-wrap .sales-orders-table thead th {
    border-top: 1px solid #dce3ed !important;
    border-bottom: 1px solid #dce3ed !important;
}

.sales-orders-table-wrap .sales-orders-table tbody tr:last-child td {
    border-bottom: 1px solid #dce3ed !important;
}

/* HOVER */
.sales-orders-table-wrap .sales-orders-table tbody tr:hover td {
    background: #f8fafc !important;
}

/* COLUMN WIDTH */
.sales-orders-table .col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.sales-orders-table .col-sales-order {
    min-width: 170px;
    white-space: nowrap;
}

.sales-orders-table .col-quotation {
    min-width: 160px;
    white-space: nowrap;
}

.sales-orders-table .col-customer {
    min-width: 190px;
}

.sales-orders-table .col-date {
    min-width: 130px;
    white-space: nowrap;
}

.sales-orders-table .col-total {
    min-width: 150px;
    text-align: right !important;
    white-space: nowrap;
}

.sales-orders-table .col-status {
    min-width: 120px;
    text-align: center !important;
    white-space: nowrap;
}

.sales-orders-table .col-details {
    min-width: 110px;
    text-align: center !important;
    white-space: nowrap;
}


.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;

    padding: 16px 20px;

    color: #6b7280;
    font-size: 12px;
    background: #ffffff;
}

.table-info {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
}

.table-info strong {
    color: #17284f;
    font-weight: 600;
}


    /* SALES ORDER NUMBER */
    .sales-order-number {
        font-weight: 600;
        white-space: nowrap;
    }

    .quotation-number {
        font-weight: 500;
        color: var(--text-primary, #475569);
        white-space: nowrap;
    }

    .sales-order-total {
        font-weight: 600;
        color: #15966f;
    }

    /* STATUS BADGES */
    .sales-order-status {
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

    .status-draft {
        background: #f1f3f6;
        color: #667085;
    }

    .status-confirmed {
        background: #edf3ff;
        color: #315caa;
    }

    .status-processing {
        background: #fff4e5;
        color: #9a6a18;
    }

    .status-completed {
        background: #e8f5f0;
        color: #16805f;
    }

    .status-cancelled {
        background: #fff0f0;
        color: #a14d4d;
    }


    /* EMPTY STATE */
    .empty-state {
        text-align: center;
        padding: 35px 15px !important;
    }

    .empty-content strong {
        display: block;
        margin-bottom: 6px;
        color: var(--text-primary, #334155);
        font-size: 13px;
    }

    .empty-content p {
        margin: 0;
        color: var(--text-muted, #64748b);
        font-size: 12px;
    }

    /* PAGINATION */
    .pagination-wrapper {
        padding: 18px 22px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-search,
        .filter-status {
            flex: 1 1 auto;
            width: 100%;
        }

        .filter-actions {
            width: 100%;
        }

        .filter-actions .btn {
            flex: 1;
            justify-content: center;
        }

        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .pagination-wrapper {
            overflow-x: auto;
        }
    }


    

/* View button */
.sales-orders-table .btn-view {
    display: inline-flex;
    align-items: left;
    justify-content: center;
    gap: 6px;
    padding: 6px 6px;
    background: #ffffff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sales-orders-table .btn-view:hover {
    background: #f3f4f6;
    color: #111827;
    border-color: #9ca3af;
}

</style>

@endsection
