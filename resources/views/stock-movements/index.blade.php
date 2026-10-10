@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')

{{-- =====================================================
     PAGE HEADER
====================================================== --}}

<div class="page-head">
    <div>
        <h1>Stock Movements</h1>
        <p>Monitor stock transactions and track product quantity changes.</p>
    </div>

    <div class="actions">
        <a href="{{ route('inventories.index') }}" class="btn">
            View Inventory
        </a>

        <a href="{{ route('stock-movements.create') }}" class="btn primary">
            + New Movement
        </a>
    </div>
</div>


{{-- =====================================================
     NOTIFICATIONS
====================================================== --}}

@if(session('success'))
<div class="alert success" role="status">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert error" role="alert">
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert error" role="alert">
    <strong>Unable to process the request.</strong>

    <ul style="margin: 8px 0 0; padding-left: 20px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


{{-- =====================================================
     MOVEMENT SUMMARY
====================================================== --}}

<div class="sm-summary-grid">

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-blue">▤</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Total Transactions</span>
            <strong class="sm-summary-value">
                {{ number_format((int) ($summary->total_transactions ?? 0)) }}
            </strong>
            <span class="sm-summary-caption">Recorded stock movements</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-teal">⇄</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Total Movement Quantity</span>
            <strong class="sm-summary-value">
                {{ number_format((int) ($summary->total_quantity ?? 0)) }}
            </strong>
            <span class="sm-summary-caption">Sum of recorded movement quantities</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-green">↓</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Stock In</span>
            <strong class="sm-summary-value">
                {{ number_format((int) ($summary->inbound ?? 0)) }}
            </strong>
            <span class="sm-summary-caption">Opening stock and incoming quantity</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-red">↑</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Stock Out</span>
            <strong class="sm-summary-value">
                {{ number_format((int) ($summary->outbound ?? 0)) }}
            </strong>
            <span class="sm-summary-caption">Total outgoing quantity</span>
        </div>
    </div>

</div>


{{-- =====================================================
     MOVEMENT LIST
====================================================== --}}

<div class="card">

    <div class="card-head">
        <div>
            <h3>Stock Movement List</h3>
            <p>
                {{ number_format($movements->total()) }}
                stock movements found
            </p>
        </div>
    </div>

    <div class="card-body">

        {{-- =================================================
             FILTER BAR
        ================================================== --}}

        <form
            method="GET"
            action="{{ route('stock-movements.index') }}"
            class="sm-filter-bar">

            {{-- Search --}}

            <div class="sm-search">
                <span class="sm-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ $search ?? request('search') }}"
                    placeholder="Search product, warehouse, reference..."
                    autocomplete="off"
                    aria-label="Search stock movements">

                @if(request('search'))
                <a
                    href="{{ route(
                            'stock-movements.index',
                            request()->except('search', 'page')
                        ) }}"
                    class="sm-search-clear"
                    title="Clear search"
                    aria-label="Clear search">×</a>
                @endif
            </div>


            {{-- Movement Type --}}

            <div class="sm-filter-select">
                <select name="movement_type" aria-label="Filter by movement type">
                    <option value="">All Movement Types</option>

                    <option
                        value="opening_stock"
                        @selected(($movementType ?? '' )==='opening_stock' )>
                        Stock Opening
                    </option>

                    <option
                        value="stock_in"
                        @selected(($movementType ?? '' )==='stock_in' )>
                        Stock In
                    </option>

                    <option
                        value="stock_out"
                        @selected(($movementType ?? '' )==='stock_out' )>
                        Stock Out
                    </option>
                </select>
            </div>


            {{-- Warehouse --}}

            <div class="sm-filter-select">
                <select name="warehouse_id" aria-label="Filter by warehouse">
                    <option value="">All Warehouses</option>

                    @foreach(($warehouses ?? collect()) as $warehouse)
                    <option
                        value="{{ $warehouse->warehouse_id }}"
                        @selected(
                        (string) ($warehouseId ?? '' )===(string) $warehouse->warehouse_id
                        )
                        >
                        {{ $warehouse->warehouse_code }}
                        - {{ $warehouse->warehouse_name }}
                    </option>
                    @endforeach
                </select>
            </div>


            {{-- Date From --}}

            <div class="sm-filter-date">
                <label for="sm-date-from">From</label>

                <input
                    id="sm-date-from"
                    type="date"
                    name="date_from"
                    value="{{ $dateFrom ?? '' }}"
                    aria-label="Start date">
            </div>


            {{-- Date To --}}

            <div class="sm-filter-date">
                <label for="sm-date-to">To</label>

                <input
                    id="sm-date-to"
                    type="date"
                    name="date_to"
                    value="{{ $dateTo ?? '' }}"
                    min="{{ $dateFrom ?? '' }}"
                    aria-label="End date">
            </div>


            {{-- Sort --}}

            <div class="sm-filter-select">
                <select name="sort" aria-label="Sort movements">
                    <option
                        value="created_at"
                        @selected(($sort ?? 'created_at' )==='created_at' )>
                        Transaction Date
                    </option>

                    <option
                        value="product_code"
                        @selected(($sort ?? '' )==='product_code' )>
                        Product Code
                    </option>

                    <option
                        value="product_name"
                        @selected(($sort ?? '' )==='product_name' )>
                        Product Name
                    </option>

                    <option
                        value="warehouse_name"
                        @selected(($sort ?? '' )==='warehouse_name' )>
                        Warehouse
                    </option>

                    <option
                        value="movement_type"
                        @selected(($sort ?? '' )==='movement_type' )>
                        Movement Type
                    </option>

                    <option
                        value="quantity"
                        @selected(($sort ?? '' )==='quantity' )>
                        Quantity
                    </option>
                </select>
            </div>


            {{-- Sort Direction --}}

            <div class="sm-filter-select">
                <select name="direction" aria-label="Sort direction">
                    <option
                        value="desc"
                        @selected(($direction ?? 'desc' )==='desc' )>
                        ↓ Descending
                    </option>

                    <option
                        value="asc"
                        @selected(($direction ?? '' )==='asc' )>
                        ↑ Ascending
                    </option>
                </select>
            </div>


            <button type="submit" class="btn sm-filter-button">
                Filter
            </button>

            @if(request()->hasAny([
            'search',
            'movement_type',
            'warehouse_id',
            'date_from',
            'date_to',
            'sort',
            'direction'
            ]))
            <a
                href="{{ route('stock-movements.index') }}"
                class="btn sm-reset-button">
                Reset
            </a>
            @endif

        </form>


        {{-- =================================================
             ACTIVE FILTER SUMMARY
        ================================================== --}}

        @php
        $activeMovementLabel = match($movementType ?? '') {
        'opening_stock' => 'Stock Opening',
        'stock_in' => 'Stock In',
        'stock_out' => 'Stock Out',
        default => null,
        };

        $selectedWarehouse = collect($warehouses ?? [])
        ->firstWhere('warehouse_id', $warehouseId ?? null);
        @endphp

        @if(
        !empty($search) ||
        !empty($movementType) ||
        !empty($warehouseId) ||
        !empty($dateFrom) ||
        !empty($dateTo)
        )
        <div class="sm-filter-summary">
            <span>Showing filtered results</span>

            @if(!empty($search))
            <span class="sm-filter-chip">
                Search: "{{ $search }}"
            </span>
            @endif

            @if($activeMovementLabel)
            <span class="sm-filter-chip">
                Type: {{ $activeMovementLabel }}
            </span>
            @endif

            @if(!empty($warehouseId))
            <span class="sm-filter-chip">
                Warehouse:
                {{ $selectedWarehouse
                            ? $selectedWarehouse->warehouse_name
                            : 'Selected warehouse'
                        }}
            </span>
            @endif

            @if(!empty($dateFrom))
            <span class="sm-filter-chip">From: {{ $dateFrom }}</span>
            @endif

            @if(!empty($dateTo))
            <span class="sm-filter-chip">To: {{ $dateTo }}</span>
            @endif
        </div>
        @endif


        {{-- =================================================
             MOVEMENT TABLE
        ================================================== --}}

        <div class="table-wrap sm-table-wrap">
            <table class="sm-table">

                <thead>
                    <tr>
                        <th>Transaction</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Movement Type</th>
                        <th>Quantity</th>
                        <th>Reference / Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($movements as $movement)

                    @php
                    $rawType = strtolower(
                    str_replace(
                    ['_', '-'],
                    ' ',
                    $movement->movement_type ?? ''
                    )
                    );

                    $movementLabel = match($rawType) {
                    'opening stock' => 'Stock Opening',
                    'stock in', 'in', 'inbound', 'masuk' => 'Stock In',
                    'stock out', 'out', 'outbound', 'keluar' => 'Stock Out',
                    default => ucwords($rawType ?: 'Unknown'),
                    };

                    $movementClass = match($rawType) {
                    'opening stock' => 'sm-badge-opening',
                    'stock in', 'in', 'inbound', 'masuk' => 'sm-badge-in',
                    'stock out', 'out', 'outbound', 'keluar' => 'sm-badge-out',
                    default => 'sm-badge-other',
                    };

                    $isInbound = in_array($rawType, [
                    'opening stock',
                    'stock in',
                    'in',
                    'inbound',
                    'masuk',
                    ], true);

                    $isOutbound = in_array($rawType, [
                    'stock out',
                    'out',
                    'outbound',
                    'keluar',
                    ], true);

                    $movementDate = $movement->created_at
                    ? \Illuminate\Support\Carbon::parse($movement->created_at)
                    : null;

                    $movementProduct = $movement->product_name
                    ?? 'Unknown Product';

                    $movementCode = $movement->product_code ?? '—';

                    $movementWarehouse = $movement->warehouse_name
                    ?? 'Unknown Warehouse';
                    @endphp

                    <tr>

                        {{-- Transaction --}}

                        <td class="sm-transaction-cell">
                            <strong class="sm-transaction-date">
                                {{ $movementDate
                                        ? $movementDate->format('d M Y')
                                        : '—'
                                    }}
                            </strong>

                            <div class="muted">
                                {{ $movementDate
                                        ? $movementDate->format('H:i')
                                        : 'Date unavailable'
                                    }}
                            </div>
                        </td>


                        {{-- Product --}}

                        <td>
                            <a
                                href="{{ route('stock-movements.edit', $movement->stock_movement_id) }}"
                                class="sm-product-code-link"
                                title="Edit stock movement">
                                <strong>{{ $movementCode }}</strong>
                            </a>

                            <div class="sm-product-name">
                                {{ $movementProduct }}
                            </div>
                        </td>


                        {{-- Warehouse --}}

                        <td>
                            <strong class="sm-warehouse-code">
                                {{ $movement->warehouse_code ?? '—' }}
                            </strong>

                            <div class="sm-warehouse-name">
                                {{ $movementWarehouse }}
                            </div>
                        </td>


                        {{-- Movement Type --}}

                        <td>
                            <span class="sm-movement-badge {{ $movementClass }}">
                                <span class="sm-movement-dot"></span>
                                {{ $movementLabel }}
                            </span>
                        </td>


                        {{-- Quantity --}}

                        <td>
                            <span class="sm-quantity
                                    {{ $isOutbound ? 'sm-quantity-out' : '' }}
                                    {{ $isInbound ? 'sm-quantity-in' : '' }}">
                                @if($isInbound)
                                +
                                @elseif($isOutbound)
                                −
                                @endif

                                {{ number_format((int) $movement->quantity) }}
                            </span>

                            <div class="sm-quantity-caption">
                                {{ $isInbound
                                        ? 'Added to stock'
                                        : ($isOutbound
                                            ? 'Removed from stock'
                                            : 'Recorded quantity')
                                    }}
                            </div>
                        </td>


                        {{-- Reference / Notes --}}

                        <td>
                            @if(!empty($movement->reference_type))
                            <div class="sm-reference-type">
                                {{ ucwords(str_replace(
                                            ['_', '-'],
                                            ' ',
                                            $movement->reference_type
                                        )) }}
                            </div>
                            @else
                            <div class="sm-reference-type muted">
                                No reference
                            </div>
                            @endif

                            @if(!empty($movement->reference_id))
                            <div
                                class="sm-reference-id"
                                title="{{ $movement->reference_id }}">
                                ID: {{ $movement->reference_id }}
                            </div>
                            @endif

                            @if(!empty($movement->notes))
                            <div
                                class="sm-notes"
                                title="{{ $movement->notes }}">
                                {{ $movement->notes }}
                            </div>
                            @else
                            <div class="muted sm-no-notes">
                                No notes
                            </div>
                            @endif
                        </td>


                        {{-- Actions --}}

                        <td>
                            <div class="table-actions">

                                {{-- View --}}


                                {{-- View: arahkan ke halaman edit karena route show belum tersedia --}}
                                <a
                                    href="{{ route('stock-movements.edit', $movement->stock_movement_id) }}"
                                    class="action-btn view"
                                    title="View Stock Movement"
                                    aria-label="View Stock Movement">
                                    👁
                                </a>



                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                            'stock-movements.edit',
                                            $movement->stock_movement_id
                                        ) }}"
                                    class="action-btn edit"
                                    title="Edit Stock Movement"
                                    aria-label="Edit Stock Movement">
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                            'stock-movements.destroy',
                                            $movement->stock_movement_id
                                        ) }}"
                                    method="POST"
                                    class="sm-delete-form"
                                    data-movement-product="{{ $movementProduct }}"
                                    data-movement-type="{{ $movementLabel }}"
                                    data-movement-quantity="{{ number_format((int) $movement->quantity) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Stock Movement"
                                        aria-label="Delete Stock Movement">
                                        🗑
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                    @empty

                    <tr>
                        <td colspan="7">
                            <div class="empty sm-empty">
                                <strong>No stock movements found.</strong>

                                <p>
                                    Add a new stock movement or adjust
                                    your search and filters.
                                </p>

                                <a
                                    href="{{ route('stock-movements.create') }}"
                                    class="btn primary">
                                    + New Movement
                                </a>
                            </div>
                        </td>
                    </tr>

                    @endforelse

                </tbody>
            </table>
        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}

        @if($movements->hasPages())
        <div class="pagination">
            {{ $movements->appends(request()->query())->links() }}
        </div>
        @endif

    </div>
</div>


{{-- =====================================================
     DELETE CONFIRMATION MODAL
====================================================== --}}

<div
    class="sm-delete-modal"
    id="deleteMovementModal"
    aria-hidden="true">
    <div class="sm-delete-overlay" data-close-movement-modal></div>

    <div
        class="sm-delete-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteMovementModalTitle">
        <div class="sm-delete-icon">!</div>

        <div class="sm-delete-content">
            <h3 id="deleteMovementModalTitle">
                Delete Stock Movement?
            </h3>

            <p>
                Are you sure you want to delete this transaction for
                <strong id="deleteMovementProduct"></strong>?
            </p>

            <div class="sm-delete-details">
                <span>Movement Type</span>
                <strong id="deleteMovementType">—</strong>

                <span>Quantity</span>
                <strong id="deleteMovementQuantity">—</strong>
            </div>

            <div class="sm-delete-warning">
                Deleting a stock movement may affect inventory balances.
                The server must reverse the transaction safely before deletion.
            </div>
        </div>

        <div class="sm-delete-actions">
            <button
                type="button"
                class="btn"
                id="cancelDeleteMovement">
                Cancel
            </button>

            <button
                type="button"
                class="btn sm-delete-confirm"
                id="confirmDeleteMovement">
                Delete Movement
            </button>
        </div>
    </div>
</div>


{{-- =====================================================
     STYLES
====================================================== --}}

<style>
    /* =====================================================
   SUMMARY CARDS
===================================================== */

    .sm-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .sm-summary-card {
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

    .sm-summary-icon {
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

    .sm-icon-blue {
        background: #edf2ff;
        color: #3656a6;
    }

    .sm-icon-teal {
        background: #e7f7f5;
        color: #218e88;
    }

    .sm-icon-green {
        background: #e8f5f0;
        color: #16805f;
    }

    .sm-icon-red {
        background: #fff0f0;
        color: #c94a4a;
    }

    .sm-summary-content {
        display: flex;
        flex-direction: column;
        min-width: 0;
        gap: 5px;
    }

    .sm-summary-label {
        color: #718096;
        font-size: 12px;
        font-weight: 500;
    }

    .sm-summary-value {
        color: #17284f;
        font-size: 25px;
        font-weight: 700;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .sm-summary-caption {
        color: #929bad;
        font-size: 11px;
        line-height: 1.5;
    }


    /* =====================================================
   FILTER BAR
===================================================== */

    .sm-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 18px;
    }

    .sm-search {
        position: relative;
        flex: 1 1 260px;
        min-width: 220px;
    }

    .sm-search input {
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

    .sm-search input::placeholder {
        color: #9aa3b2;
    }

    .sm-search input:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
    }

    .sm-search-icon {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #7d8797;
        font-size: 19px;
        pointer-events: none;
    }

    .sm-search-clear {
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

    .sm-search-clear:hover {
        background: #edf1f5;
        color: #17284f;
    }

    .sm-filter-select {
        min-width: 0;
    }

    .sm-filter-select select {
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
    }

    .sm-filter-select select:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
    }

    .sm-filter-date {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sm-filter-date label {
        color: #718096;
        font-size: 11px;
        white-space: nowrap;
    }

    .sm-filter-date input {
        width: 138px;
        height: 40px;
        box-sizing: border-box;
        padding: 0 9px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #34415c;
        font-family: inherit;
        font-size: 12px;
    }

    .sm-filter-date input:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
    }

    .sm-filter-button,
    .sm-reset-button {
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .sm-reset-button {
        text-decoration: none;
    }


    /* =====================================================
   ACTIVE FILTERS
===================================================== */

    .sm-filter-summary {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        margin-bottom: 15px;
        color: #7d8797;
        font-size: 12px;
    }

    .sm-filter-chip {
        padding: 5px 9px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #34415c;
        font-size: 11px;
        overflow-wrap: anywhere;
    }


    /* =====================================================
   TABLE AND LIST ITEMS
===================================================== */

    .sm-table {
        width: 100%;
        border-collapse: collapse;
    }

    .sm-table th {
        white-space: nowrap;
    }

    .sm-table td {
        vertical-align: middle;
    }

    .sm-table tbody tr {
        transition: background .15s ease;
    }

    .sm-table tbody tr:hover {
        background: #fafbfd;
    }

    .sm-table-wrap {
        overflow-x: auto;
    }

    .sm-transaction-cell {
        min-width: 100px;
    }

    .sm-transaction-date {
        display: inline-block;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .sm-product-code-link {
        color: #223a70;
        font-size: 12px;
        text-decoration: none;
    }

    .sm-product-code-link:hover {
        color: #2ba7a0;
    }

    .sm-product-name {
        max-width: 230px;
        margin-top: 4px;
        color: #34415c;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .sm-warehouse-code {
        color: #223a70;
        font-size: 12px;
    }

    .sm-warehouse-name {
        margin-top: 4px;
        color: #596780;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .muted {
        color: #929bad;
        font-size: 11px;
        line-height: 1.6;
    }


    /* =====================================================
   MOVEMENT BADGES
===================================================== */

    .sm-movement-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .sm-movement-dot {
        width: 6px;
        height: 6px;
        flex-shrink: 0;
        border-radius: 50%;
        background: currentColor;
    }

    .sm-badge-opening {
        background: #edf2ff;
        color: #3656a6;
    }

    .sm-badge-in {
        background: #e8f5f0;
        color: #16805f;
    }

    .sm-badge-out {
        background: #fff0f0;
        color: #b94040;
    }

    .sm-badge-other {
        background: #eef2f6;
        color: #657186;
    }


    /* =====================================================
   QUANTITY
===================================================== */

    .sm-quantity {
        display: inline-block;
        font-size: 14px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .sm-quantity-in {
        color: #16805f;
    }

    .sm-quantity-out {
        color: #c94a4a;
    }

    .sm-quantity-caption {
        margin-top: 3px;
        color: #929bad;
        font-size: 10px;
        white-space: nowrap;
    }


    /* =====================================================
   REFERENCE AND NOTES
===================================================== */

    .sm-reference-type {
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .sm-reference-id {
        max-width: 220px;
        margin-top: 3px;
        color: #7d8797;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .sm-notes {
        display: -webkit-box;
        max-width: 230px;
        margin-top: 4px;
        overflow: hidden;
        color: #718096;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .sm-no-notes {
        margin-top: 4px;
    }


    /* =====================================================
   TABLE ACTIONS
===================================================== */

    .table-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        white-space: nowrap;
    }

    .sm-delete-form {
        display: inline-flex;
        margin: 0;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 6px;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;
    }

    .action-btn.view {
        background: #eef4ff;
        color: #3656a6;
        border-color: #dce7ff;
    }

    .action-btn.view:hover {
        background: #3656a6;
        color: #fff;
    }

    .action-btn.edit {
        background: #e8f5f0;
        color: #16805f;
        border-color: #d4ebe1;
    }

    .action-btn.edit:hover {
        background: #16805f;
        color: #fff;
    }

    .action-btn.delete {
        background: #fff0f0;
        color: #c94a4a;
        border-color: #f8dddd;
    }

    .action-btn.delete:hover {
        background: #c94a4a;
        color: #fff;
    }

    .action-btn:focus-visible {
        outline: 2px solid #2ba7a0;
        outline-offset: 2px;
    }


    /* =====================================================
   EMPTY STATE
===================================================== */

    .sm-empty {
        padding: 35px 15px;
        text-align: center;
    }

    .sm-empty strong {
        display: block;
        color: #34415c;
        font-size: 14px;
    }

    .sm-empty p {
        margin: 8px 0 16px;
        color: #929bad;
        font-size: 12px;
    }


    /* =====================================================
   DELETE MODAL
===================================================== */

    .sm-delete-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .sm-delete-modal.open {
        display: flex;
    }

    .sm-delete-overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 24, 42, .45);
        backdrop-filter: blur(2px);
    }

    .sm-delete-dialog {
        position: relative;
        width: min(430px, calc(100% - 32px));
        box-sizing: border-box;
        padding: 26px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 60px rgba(23, 40, 79, .20);
        animation: smModalIn .18s ease;
    }

    @keyframes smModalIn {
        from {
            opacity: 0;
            transform: translateY(8px) scale(.98);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .sm-delete-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        border-radius: 50%;
        background: #fff0f0;
        color: #c94a4a;
        font-size: 21px;
        font-weight: 700;
    }

    .sm-delete-content h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 18px;
    }

    .sm-delete-content p {
        margin: 0 0 14px;
        color: #596780;
        font-size: 13px;
        line-height: 1.6;
    }

    .sm-delete-content p strong {
        color: #17284f;
    }

    .sm-delete-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 12px;
        padding: 12px;
        border: 1px solid #e7ebf2;
        border-radius: 8px;
        background: #fafbfd;
        font-size: 12px;
    }

    .sm-delete-details span {
        color: #929bad;
    }

    .sm-delete-details strong {
        color: #34415c;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .sm-delete-warning {
        margin-top: 14px;
        padding: 10px 12px;
        border-radius: 7px;
        background: #fff8e9;
        color: #87641d;
        font-size: 11px;
        line-height: 1.6;
    }

    .sm-delete-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 22px;
    }

    .sm-delete-confirm {
        background: #c94a4a !important;
        color: #fff !important;
        border-color: #c94a4a !important;
    }

    .sm-delete-confirm:hover {
        background: #b83f3f !important;
        border-color: #b83f3f !important;
    }


    /* =====================================================
   RESPONSIVE
===================================================== */

    @media (max-width: 1400px) {
        .sm-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sm-search {
            flex: 1 1 100%;
        }

        .sm-filter-select {
            flex: 1 1 170px;
        }

        .sm-filter-select select {
            width: 100%;
            min-width: 0;
        }

        .sm-filter-date {
            flex: 1 1 190px;
        }

        .sm-filter-date input {
            flex: 1;
            min-width: 0;
            width: 100%;
        }
    }

    @media (max-width: 700px) {
        .sm-summary-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .sm-summary-card {
            padding: 16px;
        }

        .sm-filter-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .sm-search,
        .sm-filter-select,
        .sm-filter-date,
        .sm-filter-button,
        .sm-reset-button {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .sm-filter-select select {
            width: 100%;
            box-sizing: border-box;
        }

        .sm-filter-date {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
        }

        .sm-filter-date input {
            width: 100%;
        }

        .sm-filter-button,
        .sm-reset-button {
            justify-content: center;
        }

        .sm-delete-dialog {
            padding: 22px;
        }

        .sm-delete-actions {
            flex-direction: column-reverse;
        }

        .sm-delete-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>


{{-- =====================================================
     DELETE CONFIRMATION SCRIPT
====================================================== --}}

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('deleteMovementModal');
        const productName = document.getElementById('deleteMovementProduct');
        const movementType = document.getElementById('deleteMovementType');
        const movementQuantity = document.getElementById('deleteMovementQuantity');

        const confirmButton = document.getElementById('confirmDeleteMovement');
        const cancelButton = document.getElementById('cancelDeleteMovement');
        const overlay = document.querySelector('[data-close-movement-modal]');

        if (
            !modal ||
            !productName ||
            !movementType ||
            !movementQuantity ||
            !confirmButton ||
            !cancelButton ||
            !overlay
        ) {
            return;
        }

        let activeForm = null;

        function openModal(form) {
            activeForm = form;

            productName.textContent =
                form.dataset.movementProduct || 'this product';

            movementType.textContent =
                form.dataset.movementType || 'Unknown movement';

            movementQuantity.textContent =
                form.dataset.movementQuantity || '0';

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            confirmButton.focus();
        }

        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            activeForm = null;
        }

        document.querySelectorAll('.sm-delete-form').forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                openModal(form);
            });
        });

        confirmButton.addEventListener('click', function() {
            if (activeForm) {
                activeForm.submit();
            }
        });

        cancelButton.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', function(event) {
            if (
                event.key === 'Escape' &&
                modal.classList.contains('open')
            ) {
                closeModal();
            }
        });

    });
</script>

@endsection