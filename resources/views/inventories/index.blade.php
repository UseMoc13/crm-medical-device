
@extends('layouts.app')

@section('title', 'Inventories')

@section('content')

<div class="page-head">

    <div>
        <h1>Inventories</h1>

        <p>
            Monitor product stock levels across warehouses.
        </p>
    </div>

    <div class="actions">
        {{-- Stock adjustment action can be added when its route is ready. --}}
    </div>

</div>


{{-- =====================================================
     NOTIFICATIONS
====================================================== --}}

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


{{-- =====================================================
     INVENTORY SUMMARY
====================================================== --}}

<div class="inventory-summary-grid">

    <div class="inventory-summary-card">

        <div class="inventory-summary-icon inventory-icon-blue">
            <span>▤</span>
        </div>

        <div class="inventory-summary-content">
            <span class="inventory-summary-label">
                Inventory Records
            </span>

            <strong class="inventory-summary-value">
                {{ number_format((int) ($summary->total_records ?? 0)) }}
            </strong>

            <span class="inventory-summary-caption">
                Product and warehouse combinations
            </span>
        </div>

    </div>


    <div class="inventory-summary-card">

        <div class="inventory-summary-icon inventory-icon-teal">
            <span>▥</span>
        </div>

        <div class="inventory-summary-content">
            <span class="inventory-summary-label">
                Total Quantity
            </span>

            <strong class="inventory-summary-value">
                {{ number_format((int) ($summary->total_quantity ?? 0)) }}
            </strong>

            <span class="inventory-summary-caption">
                Total recorded product units
            </span>
        </div>

    </div>


    <div class="inventory-summary-card">

        <div class="inventory-summary-icon inventory-icon-orange">
            <span>!</span>
        </div>

        <div class="inventory-summary-content">
            <span class="inventory-summary-label">
                Low Stock
            </span>

            <strong class="inventory-summary-value">
                {{ number_format((int) ($summary->low_stock ?? 0)) }}
            </strong>

            <span class="inventory-summary-caption">
                At or below minimum stock
            </span>
        </div>

    </div>


    <div class="inventory-summary-card">

        <div class="inventory-summary-icon inventory-icon-red">
            <span>×</span>
        </div>

        <div class="inventory-summary-content">
            <span class="inventory-summary-label">
                Out of Stock
            </span>

            <strong class="inventory-summary-value">
                {{ number_format((int) ($summary->out_of_stock ?? 0)) }}
            </strong>

            <span class="inventory-summary-caption">
                Quantity is zero or below
            </span>
        </div>

    </div>

</div>


{{-- =====================================================
     INVENTORY LIST
====================================================== --}}

<div class="card">

    {{-- CARD HEADER --}}

    <div class="card-head">

        <div>
            <h3>Inventory List</h3>

            <p>
                {{ number_format($inventories->total()) }}
                inventory records found
            </p>
        </div>

    </div>


    <div class="card-body">

        {{-- =============================================
             FILTER BAR
        ============================================== --}}

        <form
            method="GET"
            action="{{ route('inventories.index') }}"
            class="inventory-filter-bar"
        >

            {{-- Search --}}

            <div class="inventory-search">

                <span class="inventory-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search products or warehouses..."
                    autocomplete="off"
                    aria-label="Search products or warehouses"
                >

                @if(!empty($search))

                    <a
                        href="{{ route(
                            'inventories.index',
                            request()->except('search', 'page')
                        ) }}"
                        class="inventory-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Warehouse Filter --}}

            <div class="inventory-filter-select">

                <select
                    name="warehouse_id"
                    aria-label="Filter by warehouse"
                >

                    <option value="">
                        All Warehouses
                    </option>

                    @foreach($warehouses as $warehouse)

                        <option
                            value="{{ $warehouse->warehouse_id }}"
                            @selected(
                                (string) ($warehouseId ?? '') ===
                                (string) $warehouse->warehouse_id
                            )
                        >
                            {{ $warehouse->warehouse_code }}
                            - {{ $warehouse->warehouse_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Stock Status --}}

            <div class="inventory-filter-select">

                <select
                    name="stock_status"
                    aria-label="Filter by stock status"
                >

                    <option value="">
                        All Stock Statuses
                    </option>

                    <option
                        value="in_stock"
                        @selected(($stockStatus ?? '') === 'in_stock')
                    >
                        In Stock
                    </option>

                    <option
                        value="low_stock"
                        @selected(($stockStatus ?? '') === 'low_stock')
                    >
                        Low Stock
                    </option>

                    <option
                        value="out_of_stock"
                        @selected(($stockStatus ?? '') === 'out_of_stock')
                    >
                        Out of Stock
                    </option>

                    <option
                        value="no_minimum"
                        @selected(($stockStatus ?? '') === 'no_minimum')
                    >
                        No Minimum
                    </option>

                </select>

            </div>


            {{-- Sort --}}

            <div class="inventory-filter-select">

                <select name="sort" aria-label="Sort inventory">

                    <option
                        value="updated_at"
                        @selected(($sort ?? 'updated_at') === 'updated_at')
                    >
                        Last Updated
                    </option>

                    <option
                        value="product_code"
                        @selected(($sort ?? '') === 'product_code')
                    >
                        Product Code
                    </option>

                    <option
                        value="product_name"
                        @selected(($sort ?? '') === 'product_name')
                    >
                        Product Name
                    </option>

                    <option
                        value="warehouse_name"
                        @selected(($sort ?? '') === 'warehouse_name')
                    >
                        Warehouse Name
                    </option>

                    <option
                        value="quantity"
                        @selected(($sort ?? '') === 'quantity')
                    >
                        Quantity
                    </option>

                    <option
                        value="minimum_stock"
                        @selected(($sort ?? '') === 'minimum_stock')
                    >
                        Minimum Stock
                    </option>

                </select>

            </div>


            {{-- Sort Direction --}}

            <div class="inventory-filter-select inventory-sort-direction">

                <select
                    name="direction"
                    aria-label="Sort direction"
                >

                    <option
                        value="asc"
                        @selected(($direction ?? 'desc') === 'asc')
                    >
                        ↑ Ascending
                    </option>

                    <option
                        value="desc"
                        @selected(($direction ?? 'desc') === 'desc')
                    >
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply Filter --}}

            <button
                type="submit"
                class="btn inventory-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'warehouse_id',
                'stock_status',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('inventories.index') }}"
                    class="btn inventory-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =============================================
             ACTIVE FILTER SUMMARY
        ============================================== --}}

        @if(
            !empty($search) ||
            !empty($warehouseId) ||
            !empty($stockStatus)
        )

            <div class="inventory-filter-summary">

                <span>
                    Showing filtered results
                </span>

                @if(!empty($search))

                    <span class="filter-chip">
                        Search: "{{ $search }}"
                    </span>

                @endif

                @if(!empty($warehouseId))

                    @php
                        $selectedWarehouse = $warehouses->firstWhere(
                            'warehouse_id',
                            $warehouseId
                        );
                    @endphp

                    <span class="filter-chip">
                        Warehouse:
                        {{ $selectedWarehouse
                            ? $selectedWarehouse->warehouse_name
                            : 'Selected warehouse'
                        }}
                    </span>

                @endif

                @if(!empty($stockStatus))

                    <span class="filter-chip">
                        Stock Status:
                        @switch($stockStatus)
                            @case('in_stock')
                                In Stock
                                @break

                            @case('low_stock')
                                Low Stock
                                @break

                            @case('out_of_stock')
                                Out of Stock
                                @break

                            @case('no_minimum')
                                No Minimum
                                @break

                            @default
                                {{ $stockStatus }}
                        @endswitch
                    </span>

                @endif

            </div>

        @endif


        {{-- =============================================
             INVENTORY TABLE
        ============================================== --}}

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Minimum Stock</th>
                        <th>Stock Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($inventories as $inventory)

                        @php
                            $quantity = (int) $inventory->quantity;
                            $minimumStock = $inventory->minimum_stock;

                            if ($quantity <= 0) {
                                $inventoryStatus = 'out_of_stock';
                                $inventoryStatusLabel = 'Out of Stock';
                            } elseif ($minimumStock === null) {
                                $inventoryStatus = 'no_minimum';
                                $inventoryStatusLabel = 'No Minimum';
                            } elseif ($quantity <= (int) $minimumStock) {
                                $inventoryStatus = 'low_stock';
                                $inventoryStatusLabel = 'Low Stock';
                            } else {
                                $inventoryStatus = 'in_stock';
                                $inventoryStatusLabel = 'In Stock';
                            }
                        @endphp

                        <tr>

                            {{-- Product --}}

                            <td>

                                <div class="inventory-product-code">
                                    <strong>
                                        {{ $inventory->product_code }}
                                    </strong>
                                </div>

                                <div class="inventory-product-name">
                                    {{ $inventory->product_name }}
                                </div>

                                <div class="muted">
                                    {{ $inventory->product_type ?: 'Product' }}
                                </div>

                            </td>


                            {{-- Warehouse --}}

                            <td>

                                <div class="inventory-warehouse-code">
                                    <strong>
                                        {{ $inventory->warehouse_code }}
                                    </strong>
                                </div>

                                <div class="inventory-warehouse-name">
                                    {{ $inventory->warehouse_name }}
                                </div>

                            </td>


                            {{-- Quantity --}}

                            <td>

                                <span class="inventory-quantity
                                    {{ $quantity <= 0
                                        ? 'inventory-quantity-empty'
                                        : ''
                                    }}"
                                >
                                    {{ number_format($quantity) }}
                                </span>

                                <span class="inventory-unit">
                                    {{ $inventory->unit ?: 'unit' }}
                                </span>

                            </td>


                            {{-- Minimum Stock --}}

                            <td>

                                @if($minimumStock !== null)

                                    <span class="inventory-minimum-stock">
                                        {{ number_format((int) $minimumStock) }}
                                    </span>

                                    <span class="inventory-unit">
                                        {{ $inventory->unit ?: 'unit' }}
                                    </span>

                                @else

                                    <span class="muted">
                                        Not configured
                                    </span>

                                @endif

                            </td>


                            {{-- Stock Status --}}

                            <td>

                                <span class="inventory-status-badge
                                    inventory-status-{{ $inventoryStatus }}"
                                >
                                    <span class="inventory-status-dot"></span>
                                    {{ $inventoryStatusLabel }}
                                </span>

                            </td>


                            {{-- Last Updated --}}

                            <td>

                                @if($inventory->updated_at)

                                    <span class="inventory-updated-date">
                                        {{ \Illuminate\Support\Carbon::parse(
                                            $inventory->updated_at
                                        )->format('d M Y') }}
                                    </span>

                                    <div class="muted">
                                        {{ \Illuminate\Support\Carbon::parse(
                                            $inventory->updated_at
                                        )->format('H:i') }}
                                    </div>

                                @else

                                    <span class="muted">
                                        Not available
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">

                                <div class="empty">

                                    <strong>
                                        No inventory records found.
                                    </strong>

                                    <p>
                                        Try adjusting your search or filters
                                        to find inventory records.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =============================================
             PAGINATION
        ============================================== --}}

        @if($inventories->hasPages())

            <div class="pagination">
                {{ $inventories->links() }}
            </div>

        @endif

    </div>

</div>


{{-- =====================================================
     STYLES
====================================================== --}}

<style>

/* =====================================================
   SUMMARY CARDS
===================================================== */

.inventory-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.inventory-summary-card {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    min-width: 0;
    padding: 20px;
    background: #fff;
    border: 1px solid #e7ebf2;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(23, 40, 79, .025);
}

.inventory-summary-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 42px;
    height: 42px;
    border-radius: 9px;
    font-size: 22px;
    font-weight: 600;
}

.inventory-icon-blue {
    background: #edf2ff;
    color: #3656a6;
}

.inventory-icon-teal {
    background: #e7f7f5;
    color: #218e88;
}

.inventory-icon-orange {
    background: #fff4e6;
    color: #c47a20;
}

.inventory-icon-red {
    background: #fff0f0;
    color: #c94a4a;
}

.inventory-summary-content {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 5px;
}

.inventory-summary-label {
    color: #718096;
    font-size: 12px;
    font-weight: 500;
}

.inventory-summary-value {
    color: #17284f;
    font-size: 25px;
    font-weight: 700;
    line-height: 1.25;
}

.inventory-summary-caption {
    color: #929bad;
    font-size: 11px;
    line-height: 1.5;
}


/* =====================================================
   FILTER BAR
===================================================== */

.inventory-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.inventory-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.inventory-search input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 0 38px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.inventory-search input::placeholder {
    color: #9aa3b2;
}

.inventory-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.inventory-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.inventory-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    color: #7d8797;
    text-decoration: none;
    font-size: 18px;
}

.inventory-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECTS
===================================================== */

.inventory-filter-select select {
    min-width: 145px;
    max-width: 100%;
    height: 40px;
    padding: 0 32px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-size: 13px;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.inventory-filter-select select:hover {
    border-color: #b7c0cf;
}

.inventory-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.inventory-filter-button,
.inventory-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.inventory-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.inventory-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    color: #7d8797;
    font-size: 12px;
}

.filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   PRODUCT AND WAREHOUSE INFORMATION
===================================================== */

.inventory-product-code,
.inventory-warehouse-code {
    margin-bottom: 3px;
    color: #223a70;
    font-size: 12px;
}

.inventory-product-name,
.inventory-warehouse-name {
    color: #34415c;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.inventory-product-name {
    max-width: 260px;
}

.inventory-product-code .muted {
    margin-top: 4px;
}


/* =====================================================
   QUANTITY
===================================================== */

.inventory-quantity {
    display: inline-block;
    color: #17284f;
    font-size: 14px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}

.inventory-quantity-empty {
    color: #c94a4a;
}

.inventory-minimum-stock {
    color: #596780;
    font-size: 13px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}

.inventory-unit {
    display: inline-block;
    margin-left: 3px;
    color: #8a94a6;
    font-size: 11px;
}


/* =====================================================
   STOCK STATUS
===================================================== */

.inventory-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.inventory-status-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: currentColor;
}

.inventory-status-in_stock {
    background: #e8f5f0;
    color: #16805f;
}

.inventory-status-low_stock {
    background: #fff4e6;
    color: #b97719;
}

.inventory-status-out_of_stock {
    background: #fff0f0;
    color: #b94040;
}

.inventory-status-no_minimum {
    background: #eef2f6;
    color: #657186;
}


/* =====================================================
   LAST UPDATED
===================================================== */

.inventory-updated-date {
    display: inline-block;
    color: #34415c;
    font-size: 12px;
    white-space: nowrap;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .inventory-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .inventory-search {
        flex: 1 1 100%;
    }

    .inventory-filter-select {
        flex: 1 1 160px;
        min-width: 0;
    }

    .inventory-filter-select select {
        width: 100%;
        min-width: 0;
    }

}

@media (max-width: 700px) {

    .inventory-summary-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .inventory-summary-card {
        padding: 16px;
    }

    .inventory-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .inventory-search,
    .inventory-filter-select,
    .inventory-filter-button,
    .inventory-reset-button {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    .inventory-filter-select select {
        width: 100%;
        box-sizing: border-box;
    }

    .inventory-product-name {
        max-width: 200px;
    }

}

</style>

@endsection
