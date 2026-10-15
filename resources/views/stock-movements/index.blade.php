@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="page-head">
    <div>
        <h1>Stock Movements</h1>
        <p>Monitor stock transactions and track product quantity changes.</p>
    </div>

    <div class="actions sm-header-actions">
        <a href="{{ route('inventories.index') }}" class="btn">View Inventory</a>
        <a href="{{ route('stock-movements.correction.create') }}" class="btn">Correct Movement</a>
        <a href="{{ route('stock-movements.create') }}" class="btn primary">+ New Movement</a>
    </div>
</div>

@if(session('success'))
    <div class="alert success" role="status">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert error" role="alert">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert error" role="alert">
        <strong>Unable to process the request.</strong>
        <ul style="margin:8px 0 0;padding-left:20px">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="sm-summary-grid">
    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-blue">▤</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Total Transactions</span>
            <strong class="sm-summary-value">{{ number_format((int) ($summary['total_transactions'] ?? 0)) }}</strong>
            <span class="sm-summary-caption">Recorded stock movements</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-teal">⇄</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Total Movement Quantity</span>
            <strong class="sm-summary-value">{{ number_format((int) ($summary['total_quantity'] ?? 0)) }}</strong>
            <span class="sm-summary-caption">Sum of recorded movement quantities</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-green">↓</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Stock In</span>
            <strong class="sm-summary-value">{{ number_format((int) ($summary['inbound'] ?? 0)) }}</strong>
            <span class="sm-summary-caption">Opening stock and incoming quantity</span>
        </div>
    </div>

    <div class="sm-summary-card">
        <div class="sm-summary-icon sm-icon-red">↑</div>
        <div class="sm-summary-content">
            <span class="sm-summary-label">Stock Out</span>
            <strong class="sm-summary-value">{{ number_format((int) ($summary['outbound'] ?? 0)) }}</strong>
            <span class="sm-summary-caption">Total outgoing quantity</span>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3>Stock Movement List</h3>
            <p>{{ number_format($movements->total()) }} stock movements found</p>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('stock-movements.index') }}" class="sm-filter-bar">
            <div class="sm-search">
                <span class="sm-search-icon">⌕</span>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search product, warehouse, reference..."
                    autocomplete="off"
                    aria-label="Search stock movements">
            </div>

            <div class="sm-filter-select">
                <select name="movement_type" aria-label="Filter by movement type">
                    <option value="">All Movement Types</option>
                    <option value="opening_stock" @selected($movementType === 'opening_stock')>Stock Opening</option>
                    <option value="stock_in" @selected($movementType === 'stock_in')>Stock In</option>
                    <option value="stock_out" @selected($movementType === 'stock_out')>Stock Out</option>
                </select>
            </div>

            <div class="sm-filter-select">
                <select name="warehouse_id" aria-label="Filter by warehouse">
                    <option value="">All Warehouses</option>
                    @foreach($warehouses as $warehouse)
                        <option
                            value="{{ $warehouse->warehouse_id }}"
                            @selected((string) $warehouseId === (string) $warehouse->warehouse_id)>
                            {{ $warehouse->warehouse_code }} - {{ $warehouse->warehouse_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm-filter-date">
                <label for="sm-date-from">From</label>
                <input id="sm-date-from" type="date" name="date_from" value="{{ $dateFrom }}">
            </div>

            <div class="sm-filter-date">
                <label for="sm-date-to">To</label>
                <input id="sm-date-to" type="date" name="date_to" value="{{ $dateTo }}" min="{{ $dateFrom }}">
            </div>

            <div class="sm-filter-select">
                <select name="sort" aria-label="Sort movements">
                    <option value="created_at" @selected($sort === 'created_at')>Transaction Date</option>
                    <option value="product_code" @selected($sort === 'product_code')>Product Code</option>
                    <option value="product_name" @selected($sort === 'product_name')>Product Name</option>
                    <option value="warehouse_name" @selected($sort === 'warehouse_name')>Warehouse</option>
                    <option value="movement_type" @selected($sort === 'movement_type')>Movement Type</option>
                    <option value="quantity" @selected($sort === 'quantity')>Quantity</option>
                </select>
            </div>

            <div class="sm-filter-select">
                <select name="direction" aria-label="Sort direction">
                    <option value="desc" @selected($direction === 'desc')>↓ Descending</option>
                    <option value="asc" @selected($direction === 'asc')>↑ Ascending</option>
                </select>
            </div>

            <button type="submit" class="btn sm-filter-button">Filter</button>
            <a href="{{ route('stock-movements.index') }}" class="btn sm-reset-button">Reset</a>
        </form>

        @php
            $activeMovementLabel = match($movementType) {
                'opening_stock' => 'Stock Opening',
                'stock_in' => 'Stock In',
                'stock_out' => 'Stock Out',
                default => null,
            };

            $selectedWarehouse = $warehouses->firstWhere('warehouse_id', $warehouseId);
        @endphp

        @if($search !== '' || $movementType !== '' || $warehouseId !== '' || $dateFrom !== '' || $dateTo !== '')
            <div class="sm-filter-summary">
                <span>Showing filtered results</span>

                @if($search !== '')
                    <span class="sm-filter-chip">Search: "{{ $search }}"</span>
                @endif

                @if($activeMovementLabel)
                    <span class="sm-filter-chip">Type: {{ $activeMovementLabel }}</span>
                @endif

                @if($warehouseId !== '')
                    <span class="sm-filter-chip">Warehouse: {{ $selectedWarehouse?->warehouse_name ?? 'Selected warehouse' }}</span>
                @endif

                @if($dateFrom !== '')
                    <span class="sm-filter-chip">From: {{ $dateFrom }}</span>
                @endif

                @if($dateTo !== '')
                    <span class="sm-filter-chip">To: {{ $dateTo }}</span>
                @endif
            </div>
        @endif

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
                            $rawType = strtolower($movement->movement_type ?? '');

                            $movementLabel = match($rawType) {
                                'opening_stock' => 'Stock Opening',
                                'stock_in' => 'Stock In',
                                'stock_out' => 'Stock Out',
                                default => ucwords(str_replace('_', ' ', $rawType ?: 'Unknown')),
                            };

                            $movementClass = match($rawType) {
                                'opening_stock' => 'sm-badge-opening',
                                'stock_in' => 'sm-badge-in',
                                'stock_out' => 'sm-badge-out',
                                default => 'sm-badge-other',
                            };

                            $isInbound = in_array($rawType, ['opening_stock', 'stock_in'], true);
                            $isOutbound = $rawType === 'stock_out';

                            if ($movement->reference_type === 'correction_reversal') {
                                $movementLabel = 'Reversal · ' . $movementLabel;
                            } elseif ($movement->reference_type === 'correction') {
                                $movementLabel = 'Correction · ' . $movementLabel;
                            }

                            $movementDate = $movement->created_at
                                ? \Illuminate\Support\Carbon::parse($movement->created_at)
                                : null;
                        @endphp

                        <tr>
                            <td class="sm-transaction-cell">
                                <strong class="sm-transaction-date">
                                    {{ $movementDate?->format('d M Y') ?? '—' }}
                                </strong>
                                <div class="muted">{{ $movementDate?->format('H:i') ?? 'Date unavailable' }}</div>
                            </td>

                            <td>
                                <a href="{{ route('stock-movements.show', $movement->stock_movement_id) }}" class="sm-product-code-link">
                                    <strong>{{ $movement->product_code }}</strong>
                                </a>
                                <div class="sm-product-name">{{ $movement->product_name }}</div>
                                @if($movement->reversed_by_id)
                                    <span class="sm-correction-status">Corrected</span>
                                @elseif($movement->reversal_of_id)
                                    <span class="sm-reversal-status">Reversal entry</span>
                                @endif
                            </td>

                            <td>
                                <strong class="sm-warehouse-code">{{ $movement->warehouse_code }}</strong>
                                <div class="sm-warehouse-name">{{ $movement->warehouse_name }}</div>
                            </td>

                            <td>
                                <span class="sm-movement-badge {{ $movementClass }}">
                                    <span class="sm-movement-dot"></span>
                                    {{ $movementLabel }}
                                </span>
                            </td>

                            <td>
                                <span class="sm-quantity {{ $isOutbound ? 'sm-quantity-out' : '' }} {{ $isInbound ? 'sm-quantity-in' : '' }}">
                                    {{ $isInbound ? '+' : ($isOutbound ? '−' : '') }}{{ number_format((int) $movement->quantity) }}
                                </span>
                                <div class="sm-quantity-caption">
                                    {{ $isInbound ? 'Added to stock' : ($isOutbound ? 'Removed from stock' : 'Recorded quantity') }}
                                </div>
                            </td>

                            <td>
                                <div class="sm-reference-type">
                                    {{ $movement->reference_type ? ucwords(str_replace(['_', '-'], ' ', $movement->reference_type)) : 'No reference' }}
                                </div>

                                @if($movement->reference_id)
                                    <div class="sm-reference-id">ID: {{ $movement->reference_id }}</div>
                                @endif

                                @if($movement->notes)
                                    <div class="sm-notes" title="{{ $movement->notes }}">{{ $movement->notes }}</div>
                                @endif

                                @if($movement->correction_reason)
                                    <div class="sm-correction-reason" title="{{ $movement->correction_reason }}">
                                        Reason: {{ $movement->correction_reason }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a
                                        href="{{ route('stock-movements.show', $movement->stock_movement_id) }}"
                                        class="action-btn view"
                                        title="View Stock Movement"
                                        aria-label="View Stock Movement">👁</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty sm-empty">
                                    <strong>No stock movements found.</strong>
                                    <p>Add a new stock movement or adjust your search and filters.</p>
                                    <a href="{{ route('stock-movements.create') }}" class="btn primary">+ New Movement</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="pagination">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .sm-header-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .sm-summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .sm-summary-card { display: flex; align-items: flex-start; gap: 14px; min-width: 0; padding: 20px; background: #fff; border: 1px solid #e7ebf2; border-radius: 10px; box-shadow: 0 2px 8px rgba(23,40,79,.025); }
    .sm-summary-icon { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 42px; height: 42px; border-radius: 9px; font-size: 22px; font-weight: 600; }
    .sm-icon-blue { background: #edf2ff; color: #3656a6; }
    .sm-icon-teal { background: #e7f7f5; color: #218e88; }
    .sm-icon-green { background: #e8f5f0; color: #16805f; }
    .sm-icon-red { background: #fff0f0; color: #c94a4a; }
    .sm-summary-content { display: flex; flex-direction: column; min-width: 0; gap: 5px; }
    .sm-summary-label { color: #718096; font-size: 12px; font-weight: 500; }
    .sm-summary-value { color: #17284f; font-size: 25px; font-weight: 700; line-height: 1.25; overflow-wrap: anywhere; }
    .sm-summary-caption { color: #929bad; font-size: 11px; line-height: 1.5; }
    .sm-filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
    .sm-search { position: relative; flex: 1 1 260px; min-width: 220px; }
    .sm-search input { width: 100%; height: 40px; box-sizing: border-box; padding: 0 12px; border: 1px solid #d9dee8; border-radius: 8px; background: #fff; color: #17284f; font-size: 13px; }
    .sm-filter-select select, .sm-filter-date input { height: 40px; padding: 0 10px; border: 1px solid #d9dee8; border-radius: 8px; background: #fff; color: #34415c; font-size: 12px; }
    .sm-filter-select select { min-width: 145px; max-width: 100%; }
    .sm-filter-date { display: flex; align-items: center; gap: 6px; }
    .sm-filter-date label { color: #718096; font-size: 11px; }
    .sm-filter-date input { width: 138px; }
    .sm-filter-summary { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; margin-bottom: 15px; color: #7d8797; font-size: 12px; }
    .sm-filter-chip { padding: 5px 9px; border-radius: 6px; background: #f1f5f7; color: #34415c; font-size: 11px; overflow-wrap: anywhere; }
    .sm-table { width: 100%; border-collapse: collapse; }
    .sm-table th { white-space: nowrap; }
    .sm-table td { vertical-align: middle; }
    .sm-table tbody tr:hover { background: #fafbfd; }
    .sm-table-wrap { overflow-x: auto; }
    .sm-transaction-cell { min-width: 100px; }
    .sm-transaction-date { display: inline-block; color: #34415c; font-size: 12px; white-space: nowrap; }
    .sm-product-code-link { color: #223a70; font-size: 12px; text-decoration: none; }
    .sm-product-code-link:hover { color: #2ba7a0; }
    .sm-product-name, .sm-warehouse-name { max-width: 230px; margin-top: 4px; color: #596780; font-size: 12px; line-height: 1.5; overflow-wrap: anywhere; }
    .sm-warehouse-code { color: #223a70; font-size: 12px; }
    .muted { color: #929bad; font-size: 11px; line-height: 1.6; }
    .sm-movement-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; }
    .sm-movement-dot { width: 6px; height: 6px; flex-shrink: 0; border-radius: 50%; background: currentColor; }
    .sm-badge-opening { background: #edf2ff; color: #3656a6; }
    .sm-badge-in { background: #e8f5f0; color: #16805f; }
    .sm-badge-out { background: #fff0f0; color: #b94040; }
    .sm-badge-other { background: #eef2f6; color: #657186; }
    .sm-quantity { display: inline-block; font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .sm-quantity-in { color: #16805f; }
    .sm-quantity-out { color: #c94a4a; }
    .sm-quantity-caption { margin-top: 3px; color: #929bad; font-size: 10px; white-space: nowrap; }
    .sm-reference-type { color: #34415c; font-size: 12px; font-weight: 600; overflow-wrap: anywhere; }
    .sm-reference-id, .sm-correction-reason { max-width: 230px; margin-top: 4px; color: #718096; font-size: 10px; overflow-wrap: anywhere; }
    .sm-notes { display: -webkit-box; max-width: 230px; margin-top: 4px; overflow: hidden; color: #718096; font-size: 11px; line-height: 1.5; overflow-wrap: anywhere; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
    .sm-correction-status, .sm-reversal-status { display: inline-block; margin-top: 5px; padding: 3px 6px; border-radius: 5px; font-size: 10px; }
    .sm-correction-status { background: #fff5df; color: #946200; }
    .sm-reversal-status { background: #f1f5f9; color: #526174; }
    .table-actions { display: inline-flex; align-items: center; justify-content: center; gap: 7px; white-space: nowrap; }
    .action-btn { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; box-sizing: border-box; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; color: #475569; text-decoration: none; cursor: pointer; padding: 0; font-size: 15px; transition: background-color .18s ease, color .18s ease, border-color .18s ease; }
    .action-btn.view:hover { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; }
    .sm-empty { padding: 35px 15px; text-align: center; }
    .sm-empty strong { display: block; color: #34415c; font-size: 14px; }
    .sm-empty p { margin: 8px 0 16px; color: #929bad; font-size: 12px; }
    @media (max-width: 1400px) {
        .sm-summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .sm-search { flex: 1 1 100%; }
        .sm-filter-select { flex: 1 1 170px; }
        .sm-filter-select select { width: 100%; min-width: 0; }
        .sm-filter-date { flex: 1 1 190px; }
        .sm-filter-date input { flex: 1; min-width: 0; width: 100%; }
    }
    @media (max-width: 700px) {
        .sm-summary-grid { grid-template-columns: 1fr; gap: 12px; }
        .sm-summary-card { padding: 16px; }
        .sm-header-actions { width: 100%; }
        .sm-header-actions .btn { flex: 1 1 auto; text-align: center; }
        .sm-filter-bar { flex-direction: column; align-items: stretch; }
        .sm-search, .sm-filter-select, .sm-filter-date, .sm-filter-button, .sm-reset-button { width: 100%; min-width: 0; box-sizing: border-box; }
        .sm-filter-select select { width: 100%; }
        .sm-filter-date { display: grid; grid-template-columns: 42px minmax(0, 1fr); }
        .sm-filter-date input { width: 100%; }
        .sm-filter-button, .sm-reset-button { justify-content: center; }
    }
</style>
@endsection
