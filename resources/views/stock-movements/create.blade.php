
@extends('layouts.app')

@section('title', 'Create Stock Movement')

@section('content')

<div class="page-head">
    <div>
        <h1>Create Stock Movement</h1>
        <p>Record stock opening, incoming stock, or outgoing stock transactions.</p>
    </div>

    <div class="actions">
        <a href="{{ route('stock-movements.index') }}" class="btn">
            ← Back to Stock Movements
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert error">
        <strong>Please check the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card sm-create-card">
    <div class="card-head">
        <div>
            <h3>Stock Movement Information</h3>
            <p>Enter the product, warehouse, movement type, and transaction quantity.</p>
        </div>
    </div>

    <div class="card-body sm-create-body">
        <form method="POST"
              action="{{ route('stock-movements.store') }}"
              id="stockMovementForm">
            @csrf

            <div class="sm-form-layout">

                <div class="sm-form-main">

                    <div class="sm-section-heading sm-first-section">
                        <div>
                            <h4>Transaction Details</h4>
                            <p>Specify the type and quantity of this stock transaction.</p>
                        </div>
                    </div>

                    {{-- MOVEMENT TYPE --}}
                    <div class="sm-form-group">
                        <label for="movement_type">
                            Movement Type <span class="sm-required">*</span>
                        </label>

                        <select id="movement_type" name="movement_type" required>
                            <option value="">Select Movement Type</option>
                            <option value="opening_stock" @selected(old('movement_type') === 'opening_stock')>
                                Stock Opening
                            </option>
                            <option value="stock_in" @selected(old('movement_type') === 'stock_in')>
                                Stock In
                            </option>
                            <option value="stock_out" @selected(old('movement_type') === 'stock_out')>
                                Stock Out
                            </option>
                        </select>

                        <span class="sm-hint" id="movementTypeHint">
                            Choose the type of stock transaction.
                        </span>

                        @error('movement_type')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- PRODUCT --}}
                    <div class="sm-form-group">
                        <label for="product_id">
                            Product <span class="sm-required">*</span>
                        </label>

                        <select id="product_id" name="product_id" required>
                            <option value="">Select Product</option>

                            @foreach($products as $product)
                                <option
                                    value="{{ $product->product_id }}"
                                    data-code="{{ $product->product_code }}"
                                    data-name="{{ $product->product_name }}"
                                    data-unit="{{ $product->unit }}"
                                    @selected((string) old('product_id') === (string) $product->product_id)>
                                    {{ $product->product_code }} - {{ $product->product_name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="sm-hint">
                            Select the medical device or product involved in this transaction.
                        </span>

                        @error('product_id')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- WAREHOUSE --}}
                    <div class="sm-form-group">
                        <label for="warehouse_id">
                            Warehouse <span class="sm-required">*</span>
                        </label>

                        <select id="warehouse_id" name="warehouse_id" required>
                            <option value="">Select Warehouse</option>

                            @foreach($warehouses as $warehouse)
                                <option
                                    value="{{ $warehouse->warehouse_id }}"
                                    data-code="{{ $warehouse->warehouse_code }}"
                                    data-name="{{ $warehouse->warehouse_name }}"
                                    @selected((string) old('warehouse_id') === (string) $warehouse->warehouse_id)>
                                    {{ $warehouse->warehouse_code }} — {{ $warehouse->warehouse_name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="sm-hint">
                            Select the warehouse where stock is added or removed.
                        </span>

                        @error('warehouse_id')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- QUANTITY --}}
                    <div class="sm-form-group">
                        <label for="quantity">
                            Quantity <span class="sm-required">*</span>
                        </label>

                        <div class="sm-quantity-input">
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                value="{{ old('quantity', 1) }}"
                                min="1"
                                step="1"
                                placeholder="Enter quantity"
                                required>

                            <div class="sm-quantity-stepper">
                                <button
                                    type="button"
                                    id="quantityMinus"
                                    class="sm-stepper-button"
                                    aria-label="Decrease quantity">−</button>

                                <button
                                    type="button"
                                    id="quantityPlus"
                                    class="sm-stepper-button"
                                    aria-label="Increase quantity">+</button>
                            </div>
                        </div>

                        <span class="sm-hint" id="quantityHint">
                            Enter a whole number greater than zero.
                        </span>

                        @error('quantity')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- REFERENCE AND NOTES --}}
                    <div class="sm-section-heading">
                        <div>
                            <h4>Reference &amp; Notes</h4>
                            <p>Add optional transaction references and additional details.</p>
                        </div>
                    </div>

                    <div class="sm-form-group">
                        <label for="reference_type">Reference Type</label>

                        <input
                            type="text"
                            id="reference_type"
                            name="reference_type"
                            value="{{ old('reference_type') }}"
                            maxlength="50"
                            placeholder="e.g. Purchase Order, Sales Order">

                        <span class="sm-hint">
                            Document or business process associated with the movement.
                        </span>

                        @error('reference_type')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sm-form-group">
                        <label for="reference_id">Reference ID</label>

                        <input
                            type="text"
                            id="reference_id"
                            name="reference_id"
                            value="{{ old('reference_id') }}"
                            maxlength="36"
                            placeholder="Optional UUID reference">

                        <span class="sm-hint">
                            Enter a valid UUID if this transaction references another record.
                        </span>

                        @error('reference_id')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sm-form-group">
                        <label for="notes">Notes</label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            maxlength="2000"
                            placeholder="Enter transaction notes or additional information...">{{ old('notes') }}</textarea>

                        <div class="sm-notes-footer">
                            <span class="sm-hint">Optional transaction description.</span>
                            <span id="notesCounter">0 / 2000</span>
                        </div>

                        @error('notes')
                            <span class="sm-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- ACTIONS --}}
                    <div class="sm-form-actions">
                        <a href="{{ route('stock-movements.index') }}" class="btn">
                            Cancel
                        </a>

                        <button type="submit"
                                class="btn primary"
                                id="saveMovementButton">
                            Save Movement
                        </button>
                    </div>

                </div>

                {{-- TRANSACTION PREVIEW --}}
                <aside class="sm-preview-panel">

                    <div class="sm-preview-label">TRANSACTION PREVIEW</div>

                    <div class="sm-preview-head">
                        <div class="sm-preview-icon" id="previewIcon">S</div>

                        <div class="sm-preview-title">
                            <h3 id="previewProductName">No Product Selected</h3>
                            <span id="previewProductCode">Select a product to begin</span>
                        </div>
                    </div>

                    <div class="sm-preview-divider"></div>

                    <div class="sm-preview-detail">
                        <span>Movement Type</span>
                        <strong id="previewMovementType" class="sm-movement-badge">
                            Not Selected
                        </strong>
                    </div>

                    <div class="sm-preview-detail">
                        <span>Warehouse</span>
                        <strong id="previewWarehouse">Not Selected</strong>
                    </div>

                    <div class="sm-preview-divider"></div>

                    <div class="sm-preview-quantity">
                        <span>Transaction Quantity</span>

                        <div class="sm-preview-quantity-value">
                            <strong id="previewQuantity">1</strong>
                            <span id="previewQuantityUnit">Unit</span>
                        </div>
                    </div>

                    <p class="sm-preview-description" id="previewDescription">
                        Your transaction summary will appear here as you complete the form.
                    </p>

                    <div class="sm-preview-note">
                        <strong>Important Notes</strong>
                        <ul>
                            <li>Stock Opening sets the initial stock balance.</li>
                            <li>Stock In increases the current balance.</li>
                            <li>Stock Out decreases the balance and requires sufficient stock.</li>
                        </ul>
                    </div>

                    <div class="sm-preview-footer">
                        <span class="sm-preview-dot"></span>
                        <span>
                            Stock balance and movement history will be updated when the
                            transaction is saved successfully.
                        </span>
                    </div>

                </aside>

            </div>
        </form>
    </div>
</div>

<style>
    /* LAYOUT */
    .sm-create-card,
    .sm-create-body,
    .sm-form-layout,
    .sm-form-main {
        overflow: visible !important;
    }

    .sm-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, .8fr);
        gap: 32px;
        align-items: start;
        width: 100%;
    }

    .sm-form-main {
        min-width: 0;
    }

    /* SECTION HEADINGS */
    .sm-section-heading {
        margin: 26px 0 18px;
        padding-top: 22px;
        border-top: 1px solid #edf0f5;
    }

    .sm-section-heading.sm-first-section {
        margin-top: 0;
        padding-top: 0;
        border-top: 0;
    }

    .sm-section-heading h4 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 13px;
        font-weight: 700;
    }

    .sm-section-heading p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    /* FORM FIELDS */
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
        min-height: 110px;
        padding: 11px 12px;
        line-height: 1.6;
        resize: vertical;
    }

    .sm-form-group > input:focus,
    .sm-form-group > select:focus,
    .sm-form-group > textarea:focus {
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
    }

    .sm-form-group input::placeholder,
    .sm-form-group textarea::placeholder {
        color: #a0a8b6;
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
        line-height: 1.5;
    }

    /* QUANTITY INPUT */
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
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .sm-quantity-input:focus-within {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
    }

    .sm-quantity-input input[type="number"] {
        flex: 1;
        min-width: 0;
        width: 100%;
        height: 34px;
        padding: 0;
        border: 0;
        outline: 0;
        appearance: textfield;
        -moz-appearance: textfield;
        background: transparent;
        color: #17284f;
        font-family: inherit;
        font-size: 13px;
        box-shadow: none !important;
    }

    /* HIDE NUMBER ARROWS IN CHROME, EDGE, SAFARI, OPERA */
    .sm-quantity-input input[type="number"]::-webkit-inner-spin-button,
    .sm-quantity-input input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .sm-quantity-input input[type="number"]:focus {
        outline: none;
        border: 0;
        box-shadow: none !important;
    }

    .sm-quantity-stepper {
        display: flex;
        flex-shrink: 0;
        align-items: center;
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
        font-family: inherit;
        font-size: 17px;
        line-height: 1;
        cursor: pointer;
        transition: .15s ease;
    }

    .sm-stepper-button:hover {
        border-color: #2ba7a0;
        background: #f0faf7;
        color: #167d70;
    }

    .sm-stepper-button:active {
        transform: scale(.95);
    }

    /* NOTES COUNTER */
    .sm-notes-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
    }

    .sm-notes-footer .sm-hint {
        margin-top: 6px;
    }

    #notesCounter {
        flex-shrink: 0;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 10px;
    }

    /* FORM ACTIONS */
    .sm-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 9px;
        margin-top: 10px;
        padding-top: 18px;
        border-top: 1px solid #edf0f5;
    }

    #saveMovementButton:disabled {
        opacity: .65;
        cursor: wait;
    }

    /* PREVIEW PANEL */
    .sm-preview-panel {
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

    .sm-preview-head {
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .sm-preview-icon {
        display: flex;
        flex: 0 0 48px;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 16px;
        font-weight: 700;
    }

    .sm-preview-title {
        min-width: 0;
    }

    .sm-preview-title h3 {
        overflow-wrap: anywhere;
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
        font-weight: 700;
    }

    .sm-preview-title > span {
        display: block;
        color: #7d8797;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .sm-preview-divider {
        height: 1px;
        margin: 21px 0;
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

    /* MOVEMENT TYPE BADGES */
    .sm-movement-badge {
        padding: 5px 8px;
        border-radius: 6px;
        background: #eef1f5;
        color: #657188 !important;
    }

    .sm-movement-badge.opening {
        background: #eeeaff;
        color: #6952b8 !important;
    }

    .sm-movement-badge.in {
        background: #eaf7f0;
        color: #16845e !important;
    }

    .sm-movement-badge.out {
        background: #fff0ed;
        color: #c45142 !important;
    }

    /* PREVIEW QUANTITY */
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

    .sm-preview-quantity-value {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 7px;
    }

    .sm-preview-quantity-value strong {
        color: #17284f;
        font-size: 25px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .sm-preview-quantity-value span {
        color: #718096;
        font-size: 12px;
        overflow-wrap: anywhere;
    }

    .sm-preview-description {
        margin: 14px 0 0;
        color: #7d8797;
        font-size: 11px;
        line-height: 1.7;
    }

    /* IMPORTANT NOTES */
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

    /* RESPONSIVE */
    @media (max-width: 950px) {
        .sm-form-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .sm-preview-panel {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 600px) {
        .sm-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .sm-form-actions .btn {
            width: 100%;
            justify-content: center;
            text-align: center;
        }

        .sm-preview-panel {
            padding: 18px;
        }

        .sm-quantity-input {
            gap: 6px;
            padding-left: 9px;
        }

        .sm-quantity-stepper {
            gap: 3px;
            padding-left: 6px;
        }

        .sm-stepper-button {
            width: 27px;
            height: 29px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('stockMovementForm');

    const movementType = document.getElementById('movement_type');
    const productSelect = document.getElementById('product_id');
    const warehouseSelect = document.getElementById('warehouse_id');
    const quantityInput = document.getElementById('quantity');
    const notesInput = document.getElementById('notes');

    const quantityMinus = document.getElementById('quantityMinus');
    const quantityPlus = document.getElementById('quantityPlus');

    const typeHint = document.getElementById('movementTypeHint');
    const quantityHint = document.getElementById('quantityHint');
    const notesCounter = document.getElementById('notesCounter');

    const previewIcon = document.getElementById('previewIcon');
    const previewProductName = document.getElementById('previewProductName');
    const previewProductCode = document.getElementById('previewProductCode');
    const previewMovementType = document.getElementById('previewMovementType');
    const previewWarehouse = document.getElementById('previewWarehouse');
    const previewQuantity = document.getElementById('previewQuantity');
    const previewDescription = document.getElementById('previewDescription');

    const typeInformation = {
        opening_stock: {
            label: 'Stock Opening',
            hint: 'Establishes the initial stock balance for the selected product and warehouse.',
            quantityHint: 'Enter the initial quantity to establish the stock balance.',
            description: 'This transaction establishes the initial inventory balance.',
            badge: 'opening',
            icon: 'O'
        },
        stock_in: {
            label: 'Stock In',
            hint: 'Adds the entered quantity to the current stock balance.',
            quantityHint: 'Enter the quantity of stock received.',
            description: 'This transaction increases the current stock balance.',
            badge: 'in',
            icon: 'IN'
        },
        stock_out: {
            label: 'Stock Out',
            hint: 'Removes the entered quantity from the current stock balance.',
            quantityHint: 'The selected warehouse must have enough stock for this transaction.',
            description: 'This transaction decreases the current stock balance.',
            badge: 'out',
            icon: 'OUT'
        }
    };

    function getSelectedOption(select) {
        if (!select || select.selectedIndex < 0) {
            return null;
        }

        const option = select.options[select.selectedIndex];

        return option && option.value ? option : null;
    }

    function getQuantity() {
        const parsed = Number.parseInt(quantityInput.value, 10);

        return Number.isFinite(parsed) ? Math.max(1, parsed) : 1;
    }

    function updatePreview() {
        const product = getSelectedOption(productSelect);
        const warehouse = getSelectedOption(warehouseSelect);
        const type = typeInformation[movementType.value];
        const quantity = getQuantity();

        previewProductName.textContent = product
            ? product.dataset.name
            : 'No Product Selected';

        previewProductCode.textContent = product
            ? product.dataset.code
            : 'Select a product to begin';

        previewIcon.textContent = type ? type.icon : 'S';

        previewMovementType.textContent = type
            ? type.label
            : 'Not Selected';

        previewMovementType.className = 'sm-movement-badge';

        if (type) {
            previewMovementType.classList.add(type.badge);
        }

        previewWarehouse.textContent = warehouse
            ? warehouse.dataset.name
            : 'Not Selected';

        const unit = product && product.dataset.unit
            ? product.dataset.unit
            : '';

        previewQuantity.textContent = quantity.toLocaleString('id-ID');

        typeHint.textContent = type
            ? type.hint
            : 'Choose the type of stock transaction.';

        quantityHint.textContent = type
            ? type.quantityHint
            : 'Enter a whole number greater than zero.';

        let description = type
            ? type.description
            : 'Choose a movement type to see its description.';

        if (product && warehouse) {
            description += ' Product: ' + product.dataset.name +
                '. Warehouse: ' + warehouse.dataset.name + '.';
        }

        previewDescription.textContent = description;
    }

    /* QUANTITY BUTTONS */
    quantityMinus.addEventListener('click', function () {
        const current = Number.parseInt(quantityInput.value, 10) || 1;

        quantityInput.value = Math.max(1, current - 1);
        updatePreview();
    });

    quantityPlus.addEventListener('click', function () {
        const current = Number.parseInt(quantityInput.value, 10) || 0;

        quantityInput.value = current + 1;
        updatePreview();
    });

    quantityInput.addEventListener('input', updatePreview);

    quantityInput.addEventListener('change', function () {
        const parsed = Number.parseInt(quantityInput.value, 10);

        if (!Number.isFinite(parsed) || parsed < 1) {
            quantityInput.value = 1;
        } else {
            quantityInput.value = parsed;
        }

        updatePreview();
    });

    /* NOTES COUNTER */
    function updateNotesCounter() {
        notesCounter.textContent = notesInput.value.length + ' / 2000';
    }

    /* FORM EVENTS */
    movementType.addEventListener('change', updatePreview);
    productSelect.addEventListener('change', updatePreview);
    warehouseSelect.addEventListener('change', updatePreview);
    notesInput.addEventListener('input', updateNotesCounter);

    /* FORM VALIDATION */
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
            quantityInput.setCustomValidity(
                'Quantity must be a positive whole number.'
            );

            quantityInput.reportValidity();
            quantityInput.setCustomValidity('');

            return;
        }

        if (
            !movementType.value ||
            !productSelect.value ||
            !warehouseSelect.value
        ) {
            event.preventDefault();
            return;
        }

        const button = document.getElementById('saveMovementButton');

        button.disabled = true;
        button.textContent = 'Saving...';
    });
    
    updatePreview();
    updateNotesCounter();
});
</script>

@endsection
