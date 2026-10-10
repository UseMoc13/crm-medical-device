@extends('layouts.app')

@section('title', 'Warehouse Details')

@section('content')

@php
    $warehouseStatus = strtolower((string) ($warehouse->status ?? 'inactive'));
    $isActive = $warehouseStatus === 'active';

    $inventoryItems = $warehouse->inventories ?? collect();

    $inventoryCount = $inventoryItems->count();

    $totalQuantity = $inventoryItems->sum(function ($inventory) {
        return (int) ($inventory->quantity ?? 0);
    });

    $lowStockCount = $inventoryItems->filter(function ($inventory) {
        $minimumStock = $inventory->minimum_stock;

        return $minimumStock !== null
            && (int) $inventory->quantity <= (int) $minimumStock;
    })->count();
@endphp

{{-- PAGE HEADER --}}

<div class="page-head">
    <div>
        <h1>Warehouse Details</h1>
        <p>
            View warehouse information, operational status, location, and inventory overview.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('warehouses.index') }}" class="btn">
            &larr; Back to Warehouses
        </a>

        <a
            href="{{ route('warehouses.edit', $warehouse->warehouse_id) }}"
            class="btn primary">
            Edit Warehouse
        </a>
    </div>
</div>


{{-- SESSION MESSAGES --}}

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


{{-- =========================================================
     WAREHOUSE OVERVIEW
========================================================= --}}

<div class="warehouse-overview-grid">

    {{-- WAREHOUSE INFORMATION --}}

    <section class="card warehouse-information-card">

        <div class="card-head">
            <div>
                <h3>Warehouse Information</h3>
                <p>
                    Basic information and identification of this warehouse.
                </p>
            </div>
        </div>

        <div class="card-body">

            {{-- WAREHOUSE IDENTITY --}}

            <div class="warehouse-identity">

                <div class="warehouse-icon" aria-hidden="true">
                    W
                </div>

                <div class="warehouse-identity-content">
                    <h2>
                        {{ $warehouse->warehouse_name ?: 'Unnamed Warehouse' }}
                    </h2>

                    <span>
                        {{ $warehouse->warehouse_code ?: 'No warehouse code' }}
                    </span>
                </div>

                <div class="warehouse-status">

                    @if ($isActive)
                        <span class="status-badge active">
                            <span class="status-dot"></span>
                            Active
                        </span>
                    @else
                        <span class="status-badge inactive">
                            <span class="status-dot"></span>
                            Inactive
                        </span>
                    @endif

                </div>

            </div>


            {{-- WAREHOUSE INFORMATION GRID --}}

            <div class="warehouse-info-grid">

                <div class="info-item">
                    <span class="info-label">Warehouse Code</span>
                    <strong>
                        {{ $warehouse->warehouse_code ?: '-' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Warehouse Name</span>
                    <strong>
                        {{ $warehouse->warehouse_name ?: '-' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Operational Status</span>

                    <strong class="{{ $isActive ? 'text-active' : 'text-inactive' }}">
                        {{ $warehouse->status ?: '-' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Inventory Records</span>
                    <strong>
                        {{ number_format($inventoryCount) }}
                        {{ $inventoryCount === 1 ? 'record' : 'records' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Created</span>
                    <strong>
                        {{ $warehouse->created_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Last Updated</span>
                    <strong>
                        {{ $warehouse->updated_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>

            </div>

        </div>
    </section>


    {{-- WAREHOUSE SUMMARY --}}

    <section class="card warehouse-summary-card">

        <div class="card-head">
            <div>
                <h3>Warehouse Summary</h3>
                <p>
                    Operational and inventory summary for this warehouse.
                </p>
            </div>
        </div>

        <div class="card-body">

            <div class="warehouse-summary-list">

                {{-- STATUS --}}

                <div class="warehouse-summary-item">

                    <div class="summary-item-icon status-icon" aria-hidden="true">
                        ✓
                    </div>

                    <div class="summary-item-content">
                        <span>Warehouse Status</span>

                        <strong class="{{ $isActive ? 'text-active' : 'text-inactive' }}">
                            {{ $warehouse->status ?: 'Unknown' }}
                        </strong>

                        <small>
                            {{ $isActive
                                ? 'Available for warehouse operations'
                                : 'Currently unavailable for new operations' }}
                        </small>
                    </div>

                </div>


                {{-- INVENTORY RECORDS --}}

                <div class="warehouse-summary-item">

                    <div class="summary-item-icon inventory-icon" aria-hidden="true">
                        I
                    </div>

                    <div class="summary-item-content">
                        <span>Inventory Records</span>

                        <strong>
                            {{ number_format($inventoryCount) }}
                        </strong>

                        <small>
                            {{ $inventoryCount === 1 ? 'Product inventory record' : 'Product inventory records' }}
                        </small>
                    </div>

                </div>


                {{-- TOTAL QUANTITY --}}

                <div class="warehouse-summary-item">

                    <div class="summary-item-icon quantity-icon" aria-hidden="true">
                        #
                    </div>

                    <div class="summary-item-content">
                        <span>Total Stock Quantity</span>

                        <strong>
                            {{ number_format($totalQuantity) }}
                        </strong>

                        <small>
                            Combined quantity across inventory records
                        </small>
                    </div>

                </div>


                {{-- LOW STOCK --}}

                <div class="warehouse-summary-item">

                    <div class="summary-item-icon low-stock-icon" aria-hidden="true">
                        !
                    </div>

                    <div class="summary-item-content">
                        <span>Low Stock Records</span>

                        <strong class="{{ $lowStockCount > 0 ? 'text-warning' : 'text-active' }}">
                            {{ number_format($lowStockCount) }}
                        </strong>

                        <small>
                            Inventory records at or below minimum stock
                        </small>
                    </div>

                </div>

            </div>

        </div>
    </section>

</div>


{{-- =========================================================
     WAREHOUSE ADDRESS
========================================================= --}}

<section class="card warehouse-address-card">

    <div class="card-head">
        <div>
            <h3>Warehouse Location</h3>
            <p>
                Address and location information for this warehouse facility.
            </p>
        </div>
    </div>

    <div class="card-body">

        <div class="warehouse-location-layout">

            <div class="warehouse-location-icon" aria-hidden="true">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round">

                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                    <circle cx="12" cy="10" r="2.5" />
                </svg>
            </div>

            <div class="warehouse-location-content">

                <span class="location-label">
                    Registered Address
                </span>

                @if (filled($warehouse->address))
                    <div class="warehouse-address-text">{!! nl2br(e($warehouse->address)) !!}</div>
                @else
                    <div class="warehouse-address-empty">
                        <strong>No address provided</strong>
                        <p>
                            A warehouse address has not been registered.
                            Edit the warehouse to add its location information.
                        </p>
                    </div>
                @endif

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
     INVENTORY OVERVIEW
========================================================= --}}

<section class="card warehouse-inventory-card">

    <div class="card-head warehouse-inventory-head">

        <div>
            <h3>Inventory Overview</h3>
            <p>
                Products and stock quantities recorded for this warehouse.
            </p>
        </div>

        <div class="inventory-header-actions">

            <span class="section-count">
                {{ $inventoryCount }}
                {{ $inventoryCount === 1 ? 'record' : 'records' }}
            </span>

            {{-- Enable this link when the Inventory index route is available. --}}
            {{-- 
            <a href="{{ route('inventory.index', ['warehouse_id' => $warehouse->warehouse_id]) }}"
               class="btn">
                View Inventory
            </a>
            --}}

        </div>

    </div>

    <div class="card-body no-padding">

        @if ($inventoryCount > 0)

            <div class="table-wrap warehouse-inventory-table-wrap">

                <table class="warehouse-inventory-table">

                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Product Code</th>
                            <th>Quantity</th>
                            <th>Minimum Stock</th>
                            <th>Stock Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($inventoryItems->sortByDesc('updated_at')->take(5) as $inventory)

                            @php
                                $quantity = (int) ($inventory->quantity ?? 0);
                                $minimumStock = $inventory->minimum_stock;

                                $isLowStock = $minimumStock !== null
                                    && $quantity <= (int) $minimumStock;

                                $inventoryProduct = $inventory->product ?? null;
                            @endphp

                            <tr>

                                {{-- PRODUCT --}}

                                <td>
                                    <div class="inventory-product-cell">

                                        <div class="inventory-product-icon" aria-hidden="true">
                                            P
                                        </div>

                                        <div class="inventory-product-content">

                                            <strong>
                                                {{ $inventoryProduct?->product_name ?? 'Unknown Product' }}
                                            </strong>

                                            <small>
                                                {{ $inventoryProduct?->unit ?? 'Unit not specified' }}
                                            </small>

                                        </div>

                                    </div>
                                </td>


                                {{-- PRODUCT CODE --}}

                                <td>
                                    <span class="inventory-product-code">
                                        {{ $inventoryProduct?->product_code ?? '-' }}
                                    </span>
                                </td>


                                {{-- QUANTITY --}}

                                <td>
                                    <strong class="inventory-quantity">
                                        {{ number_format($quantity) }}
                                    </strong>
                                </td>


                                {{-- MINIMUM STOCK --}}

                                <td>
                                    <span class="minimum-stock-value">
                                        {{ $minimumStock !== null
                                            ? number_format((int) $minimumStock)
                                            : '-' }}
                                    </span>
                                </td>


                                {{-- STOCK STATUS --}}

                                <td>
                                    @if ($isLowStock)
                                        <span class="inventory-status-badge low">
                                            <span class="status-dot"></span>
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="inventory-status-badge normal">
                                            <span class="status-dot"></span>
                                            Normal
                                        </span>
                                    @endif
                                </td>


                                {{-- UPDATED --}}

                                <td>
                                    <span class="inventory-updated">
                                        {{ $inventory->updated_at?->format('d M Y, H:i') ?? '-' }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            @if ($inventoryCount > 5)
                <div class="inventory-table-footer">
                    <span>
                        Showing 5 of {{ $inventoryCount }} inventory records.
                    </span>
                </div>
            @endif

        @else

            <div class="warehouse-empty-state">

                <div class="warehouse-empty-icon" aria-hidden="true">
                    I
                </div>

                <h4>No Inventory Records</h4>

                <p>
                    No product inventory has been recorded for this warehouse yet.
                    Inventory information will appear here once stock records are added.
                </p>

            </div>

        @endif

    </div>

</section>

<style>

    .warehouse-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .warehouse-information-card,
    .warehouse-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .warehouse-information-card .card-body,
    .warehouse-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .warehouse-address-card,
    .warehouse-inventory-card,
    .warehouse-actions-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .warehouse-overview-grid .card-head h3,
    .warehouse-address-card .card-head h3,
    .warehouse-inventory-card .card-head h3,
    .warehouse-actions-card .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
        font-weight: 700;
    }

    .warehouse-overview-grid .card-head p,
    .warehouse-address-card .card-head p,
    .warehouse-inventory-card .card-head p,
    .warehouse-actions-card .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }


    /* =========================================================
       WAREHOUSE IDENTITY
    ========================================================= */

    .warehouse-identity {
        display: flex;
        align-items: center;
        gap: 13px;
        padding-bottom: 20px;
        margin-bottom: 21px;
        border-bottom: 1px solid #edf0f5;
    }

    .warehouse-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        width: 50px;
        height: 50px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 17px;
        font-weight: 700;
    }

    .warehouse-identity-content {
        flex: 1;
        min-width: 0;
    }

    .warehouse-identity-content h2 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .warehouse-identity-content > span {
        color: #7d8797;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .warehouse-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-badge.active {
        background: #eaf7f3;
        color: #167d70;
    }

    .status-badge.inactive {
        background: #fceeee;
        color: #b34b4b;
    }

    .status-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }


    /* =========================================================
       INFORMATION GRID
    ========================================================= */

    .warehouse-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 24px;
    }

    .info-item {
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

    .info-item strong {
        display: block;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .text-active {
        color: #167d70 !important;
    }

    .text-inactive {
        color: #b34b4b !important;
    }

    .text-warning {
        color: #c27a28 !important;
    }


    /* =========================================================
       WAREHOUSE SUMMARY
    ========================================================= */

    .warehouse-summary-list {
        display: flex;
        flex-direction: column;
    }

    .warehouse-summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 0;
    }

    .warehouse-summary-item + .warehouse-summary-item {
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
        font-size: 12px;
        font-weight: 700;
    }

    .status-icon,
    .inventory-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .quantity-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .low-stock-icon {
        background: #fff4e5;
        color: #c27a28;
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
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .summary-item-content > small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
    }


    /* =========================================================
       ADDRESS
    ========================================================= */

    .warehouse-location-layout {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 5px 0;
    }

    .warehouse-location-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #eef2ff;
        color: #5264a6;
    }

    .warehouse-location-content {
        flex: 1;
        min-width: 0;
    }

    .location-label {
        display: block;
        margin-bottom: 7px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .warehouse-address-text {
        color: #34415c;
        font-size: 12px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .warehouse-address-empty {
        padding: 13px 15px;
        border: 1px dashed #dfe4eb;
        border-radius: 8px;
        background: #fafbfd;
    }

    .warehouse-address-empty strong {
        display: block;
        margin-bottom: 5px;
        color: #596579;
        font-size: 12px;
    }

    .warehouse-address-empty p {
        margin: 0;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.7;
    }


    /* =========================================================
       INVENTORY TABLE
    ========================================================= */

    .warehouse-inventory-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .inventory-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-count {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #596579;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .no-padding {
        padding: 0 !important;
    }

    .warehouse-inventory-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .warehouse-inventory-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .warehouse-inventory-table thead th {
        padding: 12px 15px;
        border-bottom: 1px solid #e6eaf0;
        background: #fafbfd;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .warehouse-inventory-table tbody td {
        padding: 13px 15px;
        border-bottom: 1px solid #edf0f5;
        color: #596579;
        font-size: 11px;
        vertical-align: middle;
    }

    .warehouse-inventory-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .warehouse-inventory-table tbody tr:hover {
        background: #fafbfd;
    }

    .inventory-product-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 175px;
    }

    .inventory-product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 12px;
        font-weight: 700;
    }

    .inventory-product-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .inventory-product-content strong {
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .inventory-product-content small {
        color: #8a94a6;
        font-size: 10px;
    }

    .inventory-product-code,
    .inventory-quantity,
    .minimum-stock-value,
    .inventory-updated {
        white-space: nowrap;
    }

    .inventory-product-code {
        color: #596579;
        font-size: 10px;
    }

    .inventory-quantity {
        color: #17284f;
        font-size: 12px;
        font-weight: 700;
    }

    .minimum-stock-value {
        color: #596579;
        font-size: 11px;
    }

    .inventory-updated {
        color: #8a94a6;
        font-size: 10px;
    }

    .inventory-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .inventory-status-badge.normal {
        background: #eaf7f3;
        color: #167d70;
    }

    .inventory-status-badge.low {
        background: #fff4e5;
        color: #b97822;
    }

    .inventory-table-footer {
        padding: 12px 16px;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 10px;
    }


    /* =========================================================
       EMPTY STATE
    ========================================================= */

    .warehouse-empty-state {
        padding: 42px 24px;
        text-align: center;
    }

    .warehouse-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 15px;
        font-weight: 700;
    }

    .warehouse-empty-state h4 {
        margin: 0 0 6px;
        color: #34415c;
        font-size: 13px;
    }

    .warehouse-empty-state p {
        max-width: 430px;
        margin: 0 auto;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.7;
    }


    /* =========================================================
       QUICK ACTIONS
    ========================================================= */

    .warehouse-action-list {
        display: flex;
        flex-direction: column;
    }

    .warehouse-action-item {
        display: flex;
        align-items: center;
        gap: 13px;
        padding: 14px 0;
        color: inherit;
        text-decoration: none;
    }

    .warehouse-action-item + .warehouse-action-item {
        border-top: 1px solid #edf0f5;
    }

    .warehouse-action-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
    }

    .edit-action-icon {
        background: #eaf7f3;
        color: #167d70;
    }

    .list-action-icon {
        background: #eef2ff;
        color: #5264a6;
    }

    .warehouse-action-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .warehouse-action-content strong {
        color: #34415c;
        font-size: 12px;
        font-weight: 700;
    }

    .warehouse-action-content small {
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6;
    }

    .warehouse-action-arrow {
        color: #9aa3b2;
        font-size: 17px;
        transition: transform .15s ease, color .15s ease;
    }

    .warehouse-action-item:hover .warehouse-action-content strong {
        color: #167d70;
    }

    .warehouse-action-item:hover .warehouse-action-arrow {
        color: #167d70;
        transform: translateX(3px);
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 950px) {
        .warehouse-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 650px) {
        .warehouse-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .warehouse-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .warehouse-status {
            margin-left: 0;
        }

        .warehouse-info-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 17px;
        }

        .warehouse-inventory-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .inventory-header-actions {
            justify-content: flex-start;
        }

        .warehouse-location-layout {
            gap: 11px;
        }
    }
</style>

@endsection