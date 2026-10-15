
@extends('layouts.app')

@section('title', 'Correct Stock Movement')

@section('content')
<div class="page-head">
    <div>
        <h1>Correct Stock Movement</h1>
        <p>Correct the warehouse, movement type, and quantity while preserving the original transaction.</p>
    </div>
    <div class="actions">
        <a href="{{ route('stock-movements.index') }}" class="btn">Back to Movements</a>
    </div>
</div>

@if($errors->any())
    <div class="alert error" role="alert">
        <strong>Unable to process the correction.</strong>
        <ul style="margin:8px 0 0;padding-left:20px">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-head">
        <div>
            <h3>{{ $selectedMovement ? 'Correction Details' : 'Find a Transaction' }}</h3>
            <p>
                {{ $selectedMovement
                    ? 'Review the original transaction and enter the corrected details.'
                    : 'Search and filter eligible Stock In and Stock Out transactions.' }}
            </p>
        </div>
    </div>

    <div class="card-body">
        @if(!$selectedMovement)
            <form method="GET" action="{{ route('stock-movements.correction.create') }}" class="sm-filter-form">
                <div class="sm-filter-grid">
                    <div class="sm-field">
                        <label for="search">Search</label>
                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="Product, warehouse, transaction ID...">
                    </div>

                    <div class="sm-field">
                        <label for="movement_type">Movement Type</label>
                        <select id="movement_type" name="movement_type">
                            <option value="">All Types</option>
                            <option value="stock_in" @selected($movementType === 'stock_in')>Stock In</option>
                            <option value="stock_out" @selected($movementType === 'stock_out')>Stock Out</option>
                        </select>
                    </div>

                    <div class="sm-field">
                        <label for="warehouse_id">Original Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->warehouse_id }}" @selected($warehouseId === $warehouse->warehouse_id)>
                                    {{ $warehouse->warehouse_code }} — {{ $warehouse->warehouse_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm-field">
                        <label for="date_from">Date From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}">
                    </div>

                    <div class="sm-field">
                        <label for="date_to">Date To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}">
                    </div>
                </div>

                <div class="sm-filter-actions">
                    <a href="{{ route('stock-movements.correction.create') }}" class="btn">Reset</a>
                    <button type="submit" class="btn primary">Search Transactions</button>
                </div>
            </form>

            <div class="sm-results-heading">
                <div>
                    <strong>Eligible Transactions</strong>
                    <p>{{ $movements->total() }} transaction(s) available for correction</p>
                </div>
            </div>

            @if($movements->isEmpty())
                <div class="sm-empty">
                    <strong>No eligible transactions found.</strong>
                    <p>Try changing the search terms or filters. Already-corrected transactions are excluded.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="sm-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Original Warehouse</th>
                                <th>Movement</th>
                                <th>Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $item)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $item->product_code }}</strong>
                                        <span class="sm-muted">{{ $item->product_name }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $item->warehouse_code }}</strong>
                                        <span class="sm-muted">{{ $item->warehouse_name }}</span>
                                    </td>
                                    <td>
                                        <span class="sm-type {{ $item->movement_type === 'stock_in' ? 'is-in' : 'is-out' }}">
                                            {{ $item->movement_type === 'stock_in' ? 'Stock In' : 'Stock Out' }}
                                        </span>
                                    </td>
                                    <td>{{ number_format((int) $item->quantity) }} {{ $item->unit }}</td>
                                    <td>
                                        <a
                                            href="{{ route('stock-movements.correction.create', ['stock_movement_id' => $item->stock_movement_id]) }}"
                                            class="btn primary">
                                            Select
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="sm-pagination">
                    {{ $movements->links() }}
                </div>
            @endif
        @else
            <div class="sm-original-card">
                <div class="sm-original-heading">
                    <div>
                        <span class="sm-muted">Original transaction</span>
                        <h3>{{ $selectedMovement->product_code }} — {{ $selectedMovement->product_name }}</h3>
                    </div>
                    <span class="sm-type {{ $selectedMovement->movement_type === 'stock_in' ? 'is-in' : 'is-out' }}">
                        {{ $selectedMovement->movement_type === 'stock_in' ? 'Stock In' : 'Stock Out' }}
                    </span>
                </div>

                <div class="sm-original-grid">
                    <div>
                        <span>Transaction ID</span>
                        <strong>{{ $selectedMovement->stock_movement_id }}</strong>
                    </div>
                    <div>
                        <span>Original Warehouse</span>
                        <strong>{{ $selectedMovement->warehouse_code }} — {{ $selectedMovement->warehouse_name }}</strong>
                    </div>
                    <div>
                        <span>Original Movement Type</span>
                        <strong>{{ $selectedMovement->movement_type === 'stock_in' ? 'Stock In' : 'Stock Out' }}</strong>
                    </div>
                    <div>
                        <span>Original Quantity</span>
                        <strong>{{ number_format((int) $selectedMovement->quantity) }} {{ $selectedMovement->unit }}</strong>
                    </div>
                    <div>
                        <span>Transaction Date</span>
                        <strong>{{ \Illuminate\Support\Carbon::parse($selectedMovement->created_at)->format('d M Y H:i') }}</strong>
                    </div>
                </div>
            </div>

            <div class="sm-notice">
                <strong>Audit history is preserved.</strong>
                Submitting this correction creates a reversal in the original warehouse and a replacement
                in the selected warehouse. The original transaction will not be deleted or overwritten.
            </div>

            <form
                method="POST"
                action="{{ route('stock-movements.correction.store', $selectedMovement->stock_movement_id) }}"
                class="sm-correction-form">
                @csrf

                <div class="sm-form-grid">
                    <div class="sm-field sm-field-full">
                        <label for="warehouse_id">Correct Warehouse <span>*</span></label>
                        <select id="warehouse_id" name="warehouse_id" required>
                            @foreach($warehouses as $warehouse)
                                <option
                                    value="{{ $warehouse->warehouse_id }}"
                                    @selected(old('warehouse_id', $selectedMovement->warehouse_id) === $warehouse->warehouse_id)>
                                    {{ $warehouse->warehouse_code }} — {{ $warehouse->warehouse_name }}
                                    @if($warehouse->warehouse_id === $selectedMovement->warehouse_id)
                                        (Original)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small>Select the warehouse where this movement should have been recorded.</small>
                    </div>

                    <div class="sm-field">
                        <label for="movement_type">Correct Movement Type <span>*</span></label>
                        <select id="movement_type" name="movement_type" required>
                            <option value="stock_in" @selected(old('movement_type', $selectedMovement->movement_type) === 'stock_in')>Stock In</option>
                            <option value="stock_out" @selected(old('movement_type', $selectedMovement->movement_type) === 'stock_out')>Stock Out</option>
                        </select>
                    </div>

                    <div class="sm-field">
                        <label for="quantity">Correct Quantity <span>*</span></label>
                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            min="1"
                            step="1"
                            value="{{ old('quantity', $selectedMovement->quantity) }}"
                            required>
                        <small>Enter the correct number of product units.</small>
                    </div>

                    <div class="sm-field sm-field-full">
                        <label for="correction_reason">Reason for Correction <span>*</span></label>
                        <textarea
                            id="correction_reason"
                            name="correction_reason"
                            rows="4"
                            maxlength="2000"
                            required
                            placeholder="Explain why the warehouse, movement type, or quantity needs correction.">{{ old('correction_reason') }}</textarea>
                    </div>

                    <div class="sm-field sm-field-full">
                        <label for="notes">Replacement Transaction Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="2000"
                            placeholder="Optional notes for the corrected transaction.">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="sm-form-actions">
                    <a href="{{ route('stock-movements.correction.create') }}" class="btn">Choose Another Transaction</a>
                    <button type="submit" class="btn primary">Submit Correction</button>
                </div>
            </form>
        @endif
    </div>
</div>

<style>
    .sm-filter-form { margin-bottom: 25px; padding: 18px; border: 1px solid #e7ebf2; border-radius: 10px; background: #fafbfd; }
    .sm-filter-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .sm-field { display: flex; flex-direction: column; gap: 8px; min-width: 0; }
    .sm-field label { color: #34415c; font-size: 12px; font-weight: 600; }
    .sm-field label span { color: #c94a4a; }
    .sm-field input, .sm-field select, .sm-field textarea { width: 100%; box-sizing: border-box; padding: 11px 12px; border: 1px solid #d9dee8; border-radius: 8px; background: #fff; color: #34415c; font: inherit; font-size: 12px; }
    .sm-field input:focus, .sm-field select:focus, .sm-field textarea:focus { outline: none; border-color: #2ba7a0; box-shadow: 0 0 0 3px rgba(43,167,160,.08); }
    .sm-field textarea { resize: vertical; line-height: 1.6; }
    .sm-field small { color: #929bad; font-size: 10px; line-height: 1.6; }
    .sm-filter-actions, .sm-form-actions { display: flex; justify-content: flex-end; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
    .sm-results-heading { display: flex; align-items: center; justify-content: space-between; margin: 0 0 15px; }
    .sm-results-heading strong { color: #17284f; font-size: 14px; }
    .sm-results-heading p { margin: 5px 0 0; color: #8a94a6; font-size: 11px; }
    .sm-table { width: 100%; border-collapse: collapse; }
    .sm-table th, .sm-table td { padding: 13px 12px; text-align: left; vertical-align: middle; }
    .sm-table th { color: #718096; font-size: 10px; text-transform: uppercase; letter-spacing: .03em; white-space: nowrap; }
    .sm-table td { color: #34415c; font-size: 12px; border-top: 1px solid #edf0f5; }
    .sm-table td strong { display: block; font-size: 12px; overflow-wrap: anywhere; }
    .sm-muted { display: block; margin-top: 4px; color: #929bad; font-size: 11px; overflow-wrap: anywhere; }
    .sm-type { display: inline-flex; padding: 5px 9px; border-radius: 6px; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .sm-type.is-in { background: #e8f5f0; color: #16805f; }
    .sm-type.is-out { background: #fff0f0; color: #b94040; }
    .sm-empty { padding: 35px 15px; text-align: center; }
    .sm-empty strong { display: block; color: #17284f; font-size: 14px; }
    .sm-empty p { margin: 8px auto 0; color: #718096; font-size: 12px; line-height: 1.7; }
    .sm-pagination { margin-top: 18px; }
    .sm-original-card { padding: 20px; margin-bottom: 20px; border: 1px solid #e7ebf2; border-radius: 10px; background: #fafbfd; }
    .sm-original-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
    .sm-original-heading h3 { margin: 6px 0 0; color: #17284f; font-size: 16px; line-height: 1.5; }
    .sm-original-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-top: 22px; }
    .sm-original-grid > div { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .sm-original-grid span { color: #718096; font-size: 10px; text-transform: uppercase; letter-spacing: .03em; }
    .sm-original-grid strong { color: #34415c; font-size: 12px; line-height: 1.6; overflow-wrap: anywhere; }
    .sm-notice { margin-bottom: 24px; padding: 14px 16px; border: 1px solid #dbeafe; border-radius: 8px; background: #eff6ff; color: #31558a; font-size: 12px; line-height: 1.7; }
    .sm-notice strong { display: block; margin-bottom: 3px; }
    .sm-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .sm-field-full { grid-column: 1 / -1; }
    .sm-form-actions { margin-top: 25px; padding-top: 20px; border-top: 1px solid #edf0f5; }
    @media (max-width: 900px) {
        .sm-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 650px) {
        .sm-filter-grid, .sm-form-grid, .sm-original-grid { grid-template-columns: minmax(0, 1fr); }
        .sm-field-full { grid-column: auto; }
        .sm-filter-actions, .sm-form-actions { flex-direction: column-reverse; }
        .sm-filter-actions .btn, .sm-form-actions .btn { width: 100%; justify-content: center; text-align: center; }
        .sm-original-heading { align-items: flex-start; flex-direction: column; }
    }
</style>
@endsection
