@extends('layouts.app')

@section('title', 'Correct Stock Movement')

@section('content')
@php
    $formatMovement = function ($type) {
        return match ($type) {
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'opening_stock' => 'Stock Opening',
            default => ucwords(str_replace('_', ' ', (string) $type)),
        };
    };
@endphp

<div class="page-head">
    <div>
        <h1>{{ $selectedMovement ? 'Correct Stock Movement' : 'Select Movement to Correct' }}</h1>
        <p>
            {{ $selectedMovement
                ? 'Review the original transaction and record its corrected replacement.'
                : 'Search and filter eligible Stock In or Stock Out transactions.' }}
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('stock-movements.index') }}" class="btn">
            &larr; Back to Stock Movements
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert error" role="alert">
        <strong>Please check the following errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(!$selectedMovement)

    <section class="card sm-correction-filter-card">
        <div class="card-head">
            <div>
                <h3>Find a Transaction</h3>
                <p>Use search and filters to locate the movement that needs correction.</p>
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('stock-movements.correction.create') }}" class="sm-correction-filter-form">
                <div class="sm-filter-search">
                    <label for="search">Search</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Product, warehouse, or transaction ID">
                </div>

                <div>
                    <label for="filter_movement_type">Movement Type</label>
                    <select id="filter_movement_type" name="movement_type">
                        <option value="">All eligible types</option>
                        <option value="stock_in" @selected($movementType === 'stock_in')>Stock In</option>
                        <option value="stock_out" @selected($movementType === 'stock_out')>Stock Out</option>
                    </select>
                </div>

                <div>
                    <label for="filter_warehouse_id">Warehouse</label>
                    <select id="filter_warehouse_id" name="warehouse_id">
                        <option value="">All Warehouses</option>
                        @foreach($warehouses as $warehouse)
                            <option
                                value="{{ $warehouse->warehouse_id }}"
                                @selected($warehouseId === $warehouse->warehouse_id)>
                                {{ $warehouse->warehouse_code }} — {{ $warehouse->warehouse_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}">
                </div>

                <div>
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}">
                </div>

                <div class="sm-filter-actions">
                    <a href="{{ route('stock-movements.correction.create') }}" class="btn">Reset Filters</a>
                    <button type="submit" class="btn primary">Search Transactions</button>
                </div>
            </form>
        </div>
    </section>

    <section class="card sm-correction-list-card">
        <div class="card-head sm-correction-list-head">
            <div>
                <h3>Eligible Transactions</h3>
                <p>Only uncorrected Stock In and Stock Out records are available.</p>
            </div>
            <span class="sm-record-count">
                {{ $movements->total() }} {{ $movements->total() === 1 ? 'record' : 'records' }}
            </span>
        </div>

        <div class="card-body no-padding">
            @if($movements->count())
                <div class="table-wrap">
                    <table class="sm-correction-table">
                        <thead>
                            <tr>
                                <th>Transaction</th>
                                <th>Product</th>
                                <th>Warehouse</th>
                                <th>Movement Type</th>
                                <th>Quantity</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($movements as $movement)
                                <tr>
                                    <td>
                                        <span class="sm-transaction-id">
                                            {{ substr($movement->stock_movement_id, 0, 8) }}…
                                        </span>
                                        <small class="sm-table-muted">
                                            {{ \Illuminate\Support\Carbon::parse($movement->created_at)->format('d M Y, H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="sm-table-product">
                                            <span class="sm-table-product-icon">P</span>
                                            <span>
                                                <strong>{{ $movement->product_name }}</strong>
                                                <small>{{ $movement->product_code }}</small>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $movement->warehouse_code }}</strong>
                                        <small class="sm-table-muted">{{ $movement->warehouse_name }}</small>
                                    </td>
                                    <td>
                                        <span class="sm-correction-type {{ $movement->movement_type === 'stock_in' ? 'is-in' : 'is-out' }}">
                                            {{ $formatMovement($movement->movement_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="sm-table-quantity">
                                            {{ number_format((int) $movement->quantity) }}
                                        </strong>
                                        <small class="sm-table-muted">{{ $movement->unit ?: 'Unit' }}</small>
                                    </td>
                                    <td>
                                        {{ \Illuminate\Support\Carbon::parse($movement->created_at)->format('d M Y') }}
                                        <small class="sm-table-muted">
                                            {{ \Illuminate\Support\Carbon::parse($movement->created_at)->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <a
                                            href="{{ route('stock-movements.correction.create', ['stock_movement_id' => $movement->stock_movement_id]) }}"
                                            class="btn primary sm-select-button">
                                            Select Movement
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
            @else
                <div class="sm-empty-state">
                    <div class="sm-empty-icon">S</div>
                    <h3>No Eligible Transactions</h3>
                    <p>
                        No transactions match your current search and filters.
                        Try changing your search terms or resetting the filters.
                    </p>
                    <a href="{{ route('stock-movements.correction.create') }}" class="btn">
                        Reset Filters
                    </a>
                </div>
            @endif
        </div>
    </section>

@else

    <div class="card sm-correction-create-card">
        <div class="card-head">
            <div>
                <h3>Correction Information</h3>
                <p>Enter the corrected movement details and review the transaction preview.</p>
            </div>
        </div>

        <div class="card-body sm-correction-create-body">
            <div class="sm-correction-layout">

                <div class="sm-correction-main">

                    <div class="sm-correction-section sm-correction-first-section">
                        <h4>Original Transaction</h4>
                        <p>This transaction will remain in the movement history.</p>
                    </div>

                    <div class="sm-original-panel">
                        <div class="sm-original-heading">
                            <div class="sm-original-product-icon">P</div>
                            <div class="sm-original-product">
                                <h3>{{ $selectedMovement->product_name }}</h3>
                                <span>{{ $selectedMovement->product_code }}</span>
                            </div>
                            <span class="sm-correction-type {{ $selectedMovement->movement_type === 'stock_in' ? 'is-in' : 'is-out' }}">
                                {{ $formatMovement($selectedMovement->movement_type) }}
                            </span>
                        </div>

                        <div class="sm-original-details">
                            <div>
                                <span>Transaction ID</span>
                                <strong>{{ $selectedMovement->stock_movement_id }}</strong>
                            </div>
                            <div>
                                <span>Original Warehouse</span>
                                <strong>{{ $selectedMovement->warehouse_code }} — {{ $selectedMovement->warehouse_name }}</strong>
                            </div>
                            <div>
                                <span>Original Quantity</span>
                                <strong>{{ number_format((int) $selectedMovement->quantity) }} {{ $selectedMovement->unit ?: 'Unit' }}</strong>
                            </div>
                            <div>
                                <span>Transaction Date</span>
                                <strong>{{ \Illuminate\Support\Carbon::parse($selectedMovement->created_at)->format('d M Y, H:i:s') }}</strong>
                            </div>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('stock-movements.correction.store', $selectedMovement->stock_movement_id) }}"
                        id="stockMovementCorrectionForm">
                        @csrf

                        <div class="sm-correction-section">
                            <h4>Corrected Transaction Details</h4>
                            <p>Specify the correct warehouse, movement type, and quantity.</p>
                        </div>

                        <div class="sm-form-group">
                            <label for="correct_warehouse_id">
                                Correct Warehouse <span class="sm-required">*</span>
                            </label>
                            <select id="correct_warehouse_id" name="warehouse_id" required>
                                @foreach($warehouses as $warehouse)
                                    <option
                                        value="{{ $warehouse->warehouse_id }}"
                                        data-code="{{ $warehouse->warehouse_code }}"
                                        data-name="{{ $warehouse->warehouse_name }}"
                                        @selected(old('warehouse_id', $selectedMovement->warehouse_id) === $warehouse->warehouse_id)>
                                        {{ $warehouse->warehouse_code }} — {{ $warehouse->warehouse_name }}
                                        @if($warehouse->warehouse_id === $selectedMovement->warehouse_id)
                                            (Original)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <span class="sm-hint">Select the warehouse where this movement should have been recorded.</span>
                            @error('warehouse_id')
                                <span class="sm-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm-form-group">
                            <label for="correct_movement_type">
                                Correct Movement Type <span class="sm-required">*</span>
                            </label>
                            <select id="correct_movement_type" name="movement_type" required>
                                <option value="stock_in" @selected(old('movement_type', $selectedMovement->movement_type) === 'stock_in')>Stock In</option>
                                <option value="stock_out" @selected(old('movement_type', $selectedMovement->movement_type) === 'stock_out')>Stock Out</option>
                            </select>
                            <span class="sm-hint">Choose the correct direction of the stock movement.</span>
                            @error('movement_type')
                                <span class="sm-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm-form-group">
                            <label for="quantity">
                                Correct Quantity <span class="sm-required">*</span>
                            </label>
                            <div class="sm-quantity-input">
                                <input
                                    type="number"
                                    id="quantity"
                                    name="quantity"
                                    min="1"
                                    step="1"
                                    value="{{ old('quantity', $selectedMovement->quantity) }}"
                                    required>
                                <span class="sm-quantity-unit">{{ $selectedMovement->unit ?: 'Unit' }}</span>
                                <div class="sm-quantity-stepper">
                                    <button type="button" id="quantityMinus" class="sm-stepper-button" aria-label="Decrease quantity">−</button>
                                    <button type="button" id="quantityPlus" class="sm-stepper-button" aria-label="Increase quantity">+</button>
                                </div>
                            </div>
                            <span class="sm-hint">Enter a positive whole number.</span>
                            @error('quantity')
                                <span class="sm-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm-correction-section">
                            <h4>Correction Reason &amp; Notes</h4>
                            <p>Document why the original transaction needs to be corrected.</p>
                        </div>

                        <div class="sm-form-group">
                            <label for="correction_reason">
                                Reason for Correction <span class="sm-required">*</span>
                            </label>
                            <textarea
                                id="correction_reason"
                                name="correction_reason"
                                rows="4"
                                maxlength="2000"
                                required
                                placeholder="Explain why the warehouse, movement type, or quantity needs correction...">{{ old('correction_reason') }}</textarea>
                            <div class="sm-notes-footer">
                                <span class="sm-hint">Required for the audit history.</span>
                                <span id="reasonCounter">0 / 2000</span>
                            </div>
                            @error('correction_reason')
                                <span class="sm-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm-form-group">
                            <label for="notes">Replacement Transaction Notes</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="4"
                                maxlength="2000"
                                placeholder="Optional notes for the corrected transaction...">{{ old('notes') }}</textarea>
                            <div class="sm-notes-footer">
                                <span class="sm-hint">Optional additional information.</span>
                                <span id="notesCounter">0 / 2000</span>
                            </div>
                            @error('notes')
                                <span class="sm-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sm-form-actions">
                            <a href="{{ route('stock-movements.correction.create') }}" class="btn">Choose Another Transaction</a>
                            <button type="submit" class="btn primary" id="submitCorrectionButton">
                                Submit Correction
                            </button>
                        </div>
                    </form>
                </div>

                <aside class="sm-correction-preview">
                    <div class="sm-preview-label">CORRECTION PREVIEW</div>

                    <div class="sm-preview-product">
                        <div class="sm-preview-icon" id="previewIcon">P</div>
                        <div>
                            <h3>{{ $selectedMovement->product_name }}</h3>
                            <span>{{ $selectedMovement->product_code }}</span>
                        </div>
                    </div>

                    <div class="sm-preview-divider"></div>

                    <div class="sm-preview-detail">
                        <span>Original Warehouse</span>
                        <strong>{{ $selectedMovement->warehouse_code }} — {{ $selectedMovement->warehouse_name }}</strong>
                    </div>

                    <div class="sm-preview-detail">
                        <span>Correct Warehouse</span>
                        <strong id="previewWarehouse">
                            {{ old('warehouse_id') && old('warehouse_id') !== $selectedMovement->warehouse_id
                                ? optional($warehouses->firstWhere('warehouse_id', old('warehouse_id')))->warehouse_code . ' — ' . optional($warehouses->firstWhere('warehouse_id', old('warehouse_id')))->warehouse_name
                                : $selectedMovement->warehouse_code . ' — ' . $selectedMovement->warehouse_name }}
                        </strong>
                    </div>

                    <div class="sm-preview-detail">
                        <span>Original Movement</span>
                        <strong>{{ $formatMovement($selectedMovement->movement_type) }}</strong>
                    </div>

                    <div class="sm-preview-detail">
                        <span>Original Quantity</span>
                        <strong>{{ number_format((int) $selectedMovement->quantity) }} {{ $selectedMovement->unit ?: 'Unit' }}</strong>
                    </div>

                    <div class="sm-preview-divider"></div>

                    <div class="sm-preview-detail">
                        <span>Corrected Movement</span>
                        <strong id="previewMovementType">{{ $formatMovement(old('movement_type', $selectedMovement->movement_type)) }}</strong>
                    </div>

                    <div class="sm-preview-quantity">
                        <span>Corrected Quantity</span>
                        <div>
                            <strong id="previewQuantity">{{ number_format((int) old('quantity', $selectedMovement->quantity)) }}</strong>
                            <small>{{ $selectedMovement->unit ?: 'Unit' }}</small>
                        </div>
                    </div>

                    <p class="sm-preview-description" id="previewDescription">
                        The original entry will be reversed and a replacement entry will be recorded.
                    </p>

                    <div class="sm-preview-note">
                        <strong>Important Notes</strong>
                        <ul>
                            <li>The original transaction will not be deleted.</li>
                            <li>A reversal entry will offset the original movement in the original warehouse.</li>
                            <li>A replacement entry will use the corrected warehouse, movement type, and quantity.</li>
                            <li>The correction will be rejected if the resulting stock becomes negative.</li>
                        </ul>
                    </div>

                    <div class="sm-preview-footer">
                        <span class="sm-preview-dot"></span>
                        Inventory balances and movement history will be updated together when the correction succeeds.
                    </div>
                </aside>

            </div>
        </div>
    </div>

@endif

<style>
    .sm-correction-filter-card,
    .sm-correction-list-card,
    .sm-correction-create-card {
        margin-bottom: 18px;
        min-width: 0;
    }

    .sm-correction-filter-form {
        display: grid;
        grid-template-columns: minmax(220px, 1.5fr) repeat(2, minmax(150px, 1fr));
        gap: 17px;
        align-items: end;
    }

    .sm-correction-filter-form > div {
        min-width: 0;
    }

    .sm-correction-filter-form label {
        display: block;
        margin-bottom: 7px;
        color: #17284f;
        font-size: 12px;
        font-weight: 600;
    }

    .sm-correction-filter-form input,
    .sm-correction-filter-form select {
        width: 100%;
        height: 41px;
        box-sizing: border-box;
        padding: 0 11px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #34415c;
        font: inherit;
        font-size: 12px;
    }

    .sm-correction-filter-form input:focus,
    .sm-correction-filter-form select:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43,167,160,.09);
    }

    .sm-filter-search {
        grid-column: span 1;
    }

    .sm-filter-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        grid-column: 1 / -1;
        padding-top: 3px;
    }

    .sm-correction-list-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .sm-record-count {
        flex-shrink: 0;
        padding: 7px 10px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #596579;
        font-size: 11px;
        font-weight: 600;
    }

    .no-padding {
        padding: 0 !important;
    }

    .sm-correction-table {
        width: 100%;
        min-width: 930px;
        border-collapse: collapse;
    }

    .sm-correction-table th {
        padding: 13px 14px;
        background: #fafbfd;
        border-bottom: 1px solid #e6eaf0;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .35px;
        white-space: nowrap;
    }

    .sm-correction-table td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf0f5;
        color: #34415c;
        font-size: 11px;
        vertical-align: middle;
    }

    .sm-correction-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .sm-correction-table tbody tr:hover {
        background: #fafcfd;
    }

    .sm-transaction-id {
        display: block;
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .sm-table-muted {
        display: block;
        margin-top: 5px;
        color: #929bad;
        font-size: 10px;
        line-height: 1.5;
    }

    .sm-table-product {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 160px;
    }

    .sm-table-product-icon,
    .sm-original-product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 12px;
        font-weight: 700;
    }

    .sm-table-product > span:last-child {
        min-width: 0;
    }

    .sm-table-product strong {
        display: block;
        color: #34415c;
        font-size: 11px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .sm-table-product small {
        display: block;
        margin-top: 3px;
        color: #8a94a6;
        font-size: 10px;
    }

    .sm-table-quantity {
        color: #17284f;
        font-size: 12px;
    }

    .sm-correction-type {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .sm-correction-type.is-in {
        background: #e8f5f0;
        color: #16805f;
    }

    .sm-correction-type.is-out {
        background: #fff0ed;
        color: #bd5144;
    }

    .sm-select-button {
        white-space: nowrap;
    }

    .sm-pagination {
        padding: 16px 18px;
        border-top: 1px solid #edf0f5;
    }

    .sm-empty-state {
        padding: 45px 22px;
        text-align: center;
    }

    .sm-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 46px;
        height: 46px;
        margin: 0 auto 14px;
        border-radius: 12px;
        background: #eef2f5;
        color: #718096;
        font-weight: 700;
    }

    .sm-empty-state h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 15px;
    }

    .sm-empty-state p {
        max-width: 440px;
        margin: 0 auto 18px;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.7;
    }

    .sm-correction-create-card,
    .sm-correction-create-body,
    .sm-correction-layout,
    .sm-correction-main {
        overflow: visible !important;
    }

    .sm-correction-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
        gap: 32px;
        align-items: start;
        width: 100%;
    }

    .sm-correction-main {
        min-width: 0;
    }

    .sm-correction-section {
        margin: 28px 0 18px;
        padding-top: 22px;
        border-top: 1px solid #edf0f5;
    }

    .sm-correction-first-section {
        margin-top: 0;
        padding-top: 0;
        border-top: 0;
    }

    .sm-correction-section h4 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 13px;
        font-weight: 700;
    }

    .sm-correction-section p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    .sm-original-panel {
        padding: 18px;
        border: 1px solid #e6eaf0;
        border-radius: 10px;
        background: #fafbfd;
    }

    .sm-original-heading {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .sm-original-product {
        flex: 1;
        min-width: 0;
    }

    .sm-original-product h3 {
        margin: 0 0 4px;
        color: #17284f;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .sm-original-product > span {
        color: #8a94a6;
        font-size: 11px;
    }

    .sm-original-details {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px 20px;
        margin-top: 22px;
        padding-top: 19px;
        border-top: 1px solid #e6eaf0;
    }

    .sm-original-details > div {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }

    .sm-original-details span {
        color: #8a94a6;
        font-size: 10px;
    }

    .sm-original-details strong {
        color: #34415c;
        font-size: 11px;
        line-height: 1.6;
        overflow-wrap: anywhere;
    }

    .sm-form-group {
        min-width: 0;
        margin-bottom: 20px;
    }

    .sm-form-group > label {
        display: block;
        margin-bottom: 7px;
        color: #17284f;
        font-size: 13px;
        font-weight: 600;
    }

    .sm-required {
        color: #c94a4a;
    }

    .sm-form-group > input,
    .sm-form-group > select,
    .sm-form-group > textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #17284f;
        font-family: inherit;
        font-size: 13px;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .sm-form-group > input,
    .sm-form-group > select {
        height: 42px;
        padding: 0 12px;
    }

    .sm-form-group > textarea {
        min-height: 95px;
        padding: 11px 12px;
        line-height: 1.6;
        resize: vertical;
    }

    .sm-form-group > input:focus,
    .sm-form-group > select:focus,
    .sm-form-group > textarea:focus,
    .sm-quantity-input:focus-within {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43,167,160,.1);
    }

    .sm-hint {
        display: block;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.5;
    }

    .sm-error {
        display: block;
        margin-top: 6px;
        color: #c94a4a;
        font-size: 11px;
    }

    .sm-quantity-input {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        box-sizing: border-box;
        padding: 4px 6px 4px 12px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
    }

    .sm-quantity-input input {
        flex: 1;
        min-width: 0;
        width: 100%;
        height: 34px;
        padding: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: #17284f;
        font-family: inherit;
        font-size: 13px;
    }

    .sm-quantity-input input:focus {
        outline: none;
        border: 0;
        box-shadow: none;
    }

    .sm-quantity-unit {
        flex-shrink: 0;
        color: #718096;
        font-size: 11px;
    }

    .sm-quantity-stepper {
        display: flex;
        gap: 4px;
        padding-left: 8px;
        border-left: 1px solid #edf0f5;
    }

    .sm-stepper-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 29px;
        height: 29px;
        padding: 0;
        border: 1px solid #dfe4eb;
        border-radius: 6px;
        background: #fff;
        color: #17284f;
        font-size: 17px;
        cursor: pointer;
    }

    .sm-stepper-button:hover {
        border-color: #2ba7a0;
        background: #f0faf7;
        color: #167d70;
    }

    .sm-notes-footer {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }

    .sm-notes-footer > span:last-child {
        flex-shrink: 0;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 10px;
    }

    .sm-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
        margin-top: 10px;
        padding-top: 18px;
        border-top: 1px solid #edf0f5;
    }

    .sm-form-actions button:disabled {
        opacity: .65;
        cursor: wait;
    }

    .sm-correction-preview {
        position: sticky;
        top: 92px;
        align-self: start;
        min-width: 0;
        box-sizing: border-box;
        padding: 24px;
        border: 1px solid #e6eaf0;
        border-radius: 12px;
        background: #fafbfd;
    }

    .sm-preview-label {
        margin-bottom: 20px;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.2px;
    }

    .sm-preview-product {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .sm-preview-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 46px;
        width: 46px;
        height: 46px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 14px;
        font-weight: 700;
    }

    .sm-preview-product h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .sm-preview-product span {
        color: #8a94a6;
        font-size: 11px;
    }

    .sm-preview-divider {
        height: 1px;
        margin: 20px 0;
        background: #e5e9ef;
    }

    .sm-preview-detail {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
    }

    .sm-preview-detail > span {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .sm-preview-detail > strong {
        max-width: 62%;
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .sm-preview-quantity {
        padding: 15px;
        border: 1px solid #e2eae9;
        border-radius: 9px;
        background: #fff;
    }

    .sm-preview-quantity > span {
        display: block;
        margin-bottom: 8px;
        color: #8a94a6;
        font-size: 11px;
    }

    .sm-preview-quantity > div {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 7px;
    }

    .sm-preview-quantity strong {
        color: #17284f;
        font-size: 25px;
    }

    .sm-preview-quantity small {
        color: #718096;
        font-size: 12px;
    }

    .sm-preview-description {
        margin: 14px 0 0;
        color: #7d8797;
        font-size: 11px;
        line-height: 1.7;
    }

    .sm-preview-note {
        margin-top: 22px;
        padding: 15px;
        border-radius: 9px;
        background: #f0f4f7;
    }

    .sm-preview-note > strong {
        display: block;
        margin-bottom: 10px;
        color: #34415c;
        font-size: 11px;
    }

    .sm-preview-note ul {
        margin: 0;
        padding-left: 16px;
        color: #718096;
        font-size: 10px;
        line-height: 1.8;
    }

    .sm-preview-footer {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-top: 18px;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6;
    }

    .sm-preview-dot {
        flex: 0 0 7px;
        width: 7px;
        height: 7px;
        margin-top: 4px;
        border-radius: 50%;
        background: #2ba7a0;
    }

    @media (max-width: 1000px) {
        .sm-correction-filter-form {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sm-filter-search,
        .sm-filter-actions {
            grid-column: 1 / -1;
        }

        .sm-correction-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .sm-correction-preview {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 650px) {
        .sm-correction-filter-form {
            grid-template-columns: minmax(0, 1fr);
        }

        .sm-filter-search,
        .sm-filter-actions {
            grid-column: auto;
        }

        .sm-filter-actions,
        .sm-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .sm-filter-actions .btn,
        .sm-form-actions .btn,
        .sm-form-actions button {
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .sm-original-heading {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .sm-original-details {
            grid-template-columns: minmax(0, 1fr);
        }

        .sm-correction-list-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .sm-correction-preview {
            padding: 18px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('stockMovementCorrectionForm');

    if (!form) {
        return;
    }

    const warehouseInput = document.getElementById('correct_warehouse_id');
    const typeInput = document.getElementById('correct_movement_type');
    const quantityInput = document.getElementById('quantity');
    const reasonInput = document.getElementById('correction_reason');
    const notesInput = document.getElementById('notes');
    const minusButton = document.getElementById('quantityMinus');
    const plusButton = document.getElementById('quantityPlus');

    const previewWarehouse = document.getElementById('previewWarehouse');
    const previewType = document.getElementById('previewMovementType');
    const previewQuantity = document.getElementById('previewQuantity');
    const previewDescription = document.getElementById('previewDescription');
    const reasonCounter = document.getElementById('reasonCounter');
    const notesCounter = document.getElementById('notesCounter');
    const submitButton = document.getElementById('submitCorrectionButton');

    const originalWarehouseId = @json($selectedMovement->warehouse_id);
    const originalWarehouseName = @json($selectedMovement->warehouse_code . ' — ' . $selectedMovement->warehouse_name);
    const originalType = @json($selectedMovement->movement_type);
    const originalQuantity = @json((int) $selectedMovement->quantity);

    function currentQuantity() {
        const value = Number.parseInt(quantityInput.value, 10);
        return Number.isFinite(value) && value > 0 ? value : 1;
    }

    function updatePreview() {
        const selectedWarehouse = warehouseInput.options[warehouseInput.selectedIndex];
        const warehouseCode = selectedWarehouse?.dataset.code || '';
        const warehouseName = selectedWarehouse?.dataset.name || '';
        const warehouseLabel = warehouseCode && warehouseName
            ? warehouseCode + ' — ' + warehouseName
            : originalWarehouseName;

        const type = typeInput.value;
        const quantity = currentQuantity();
        const warehouseChanged = warehouseInput.value !== originalWarehouseId;
        const typeChanged = type !== originalType;
        const quantityChanged = quantity !== originalQuantity;

        previewWarehouse.textContent = warehouseLabel;
        previewType.textContent = type === 'stock_in' ? 'Stock In' : 'Stock Out';
        previewQuantity.textContent = quantity.toLocaleString('id-ID');

        if (warehouseChanged && typeChanged) {
            previewDescription.textContent = 'The original movement will be reversed in its original warehouse. A replacement with the corrected warehouse and movement type will be recorded.';
        } else if (warehouseChanged) {
            previewDescription.textContent = 'The original movement will be reversed in its original warehouse. A replacement will be recorded in the selected warehouse.';
        } else if (typeChanged) {
            previewDescription.textContent = 'The original movement will be reversed and replaced with the corrected movement type in the same warehouse.';
        } else if (quantityChanged) {
            previewDescription.textContent = 'The original movement will be reversed and replaced with the corrected quantity.';
        } else {
            previewDescription.textContent = type === 'stock_in'
                ? 'The original entry will be reversed and a replacement Stock In transaction will be recorded.'
                : 'The original entry will be reversed and a replacement Stock Out transaction will be recorded.';
        }
    }

    function updateCounters() {
        reasonCounter.textContent = reasonInput.value.length + ' / 2000';
        notesCounter.textContent = notesInput.value.length + ' / 2000';
    }

    minusButton.addEventListener('click', function () {
        quantityInput.value = Math.max(1, currentQuantity() - 1);
        updatePreview();
    });

    plusButton.addEventListener('click', function () {
        quantityInput.value = currentQuantity() + 1;
        updatePreview();
    });

    warehouseInput.addEventListener('change', updatePreview);
    typeInput.addEventListener('change', updatePreview);
    quantityInput.addEventListener('input', updatePreview);
    reasonInput.addEventListener('input', updateCounters);
    notesInput.addEventListener('input', updateCounters);

    quantityInput.addEventListener('change', function () {
        quantityInput.value = currentQuantity();
        updatePreview();
    });

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
            return;
        }

        const quantity = Number(quantityInput.value);

        if (!Number.isInteger(quantity) || quantity < 1) {
            event.preventDefault();
            quantityInput.focus();
            return;
        }

        if (!reasonInput.value.trim()) {
            event.preventDefault();
            reasonInput.focus();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Submitting Correction...';
    });

    updatePreview();
    updateCounters();
});
</script>
@endsection