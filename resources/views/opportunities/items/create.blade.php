@extends('layouts.app')

@section('title', 'Add Opportunity Item')

@section('content')

<div class="page-head">

    <div>

        <h1>Add Opportunity Item</h1>

        <p>
            Add a product to this sales opportunity and configure its quantity and estimated selling price.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('opportunities.show', $opportunity) }}"
            class="btn"
        >
            ← Back to Opportunity
        </a>

    </div>

</div>


@if($errors->any())

<div class="alert error">

    <strong>Please check the following errors:</strong>

    <ul>

        @foreach($errors->all() as $error)

        <li>
            {{ $error }}
        </li>

        @endforeach

    </ul>

</div>

@endif


<div class="card opportunity-item-create-card">

    <div class="card-head">

        <div>

            <h3>
                Opportunity Item Information
            </h3>

            <p>
                Select a product, specify the quantity, and enter the estimated selling price for this opportunity.
            </p>

        </div>

    </div>


    <div class="card-body opportunity-item-create-body">

        <form
            method="POST"
            action="{{ route('opportunities.items.store', $opportunity) }}"
            id="opportunityItemForm"
        >

            @csrf


            <div class="opportunity-item-layout">


                {{-- =====================================================
                     LEFT : FORM
                ====================================================== --}}

                <div class="opportunity-item-form-main">


                    {{-- =================================================
                         OPPORTUNITY INFORMATION
                    ================================================== --}}

                    <div class="section-divider first-section">

                        <div>

                            <h4>
                                Opportunity Information
                            </h4>

                            <p>
                                The item will be added to the opportunity below.
                            </p>

                        </div>

                    </div>


                    <div class="opportunity-context-grid">


                        <div class="context-item">

                            <span class="context-label">
                                Opportunity Code
                            </span>

                            <strong class="context-value highlight">
                                {{ $opportunity->opportunity_code }}
                            </strong>

                        </div>


                        <div class="context-item">

                            <span class="context-label">
                                Opportunity Name
                            </span>

                            <strong class="context-value">
                                {{ $opportunity->name }}
                            </strong>

                        </div>


                        <div class="context-item">

                            <span class="context-label">
                                Customer
                            </span>

                            <strong class="context-value">
                                {{ $opportunity->customer->customer_name ?? '-' }}
                            </strong>

                        </div>


                        <div class="context-item">

                            <span class="context-label">
                                Stage
                            </span>

                            <strong class="context-value">
                                {{ $opportunity->stage }}
                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         PRODUCT INFORMATION
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Product Information
                            </h4>

                            <p>
                                Select the product that will be included in this opportunity.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="product_id">

                            Product

                            <span class="required">*</span>

                        </label>


                        <select
                            name="product_id"
                            id="product_id"
                            required
                        >

                            <option value="">
                                Select Product
                            </option>


                            @foreach($products as $product)

                            <option
                                value="{{ $product->product_id }}"
                                data-code="{{ $product->product_code }}"
                                data-name="{{ $product->product_name }}"
                                data-price="{{ $product->price }}"
                                data-type="{{ $product->product_type }}"
                                data-unit="{{ $product->unit }}"
                                @selected(
                                    old('product_id') == $product->product_id
                                )
                            >

                                {{ $product->product_code }}
                                —
                                {{ $product->product_name }}

                            </option>

                            @endforeach

                        </select>


                        <span class="form-hint">
                            Select the product that will be added to this opportunity.
                        </span>


                        @if($products->isEmpty())

                        <span class="form-hint warning">
                            No active products are currently available.
                        </span>

                        @endif


                        @error('product_id')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         PRODUCT REFERENCE
                    ================================================== --}}

                    <div
                        class="product-reference"
                        id="productReference"
                        style="display: none;"
                    >

                        <div class="product-reference-item">

                            <span>
                                Product Code
                            </span>

                            <strong id="referenceProductCode">
                                -
                            </strong>

                        </div>


                        <div class="product-reference-item">

                            <span>
                                Product Type
                            </span>

                            <strong id="referenceProductType">
                                -
                            </strong>

                        </div>


                        <div class="product-reference-item">

                            <span>
                                Unit
                            </span>

                            <strong id="referenceProductUnit">
                                -
                            </strong>

                        </div>


                        <div class="product-reference-item">

                            <span>
                                Master Price
                            </span>

                            <strong id="referenceProductPrice">
                                Rp 0
                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         SALES ITEM INFORMATION
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Sales Item Information
                            </h4>

                            <p>
                                Configure the quantity and estimated selling price for this opportunity item.
                            </p>

                        </div>

                    </div>


                    <div class="item-two-column">


                        {{-- Quantity --}}

                        <div class="form-group">

                            <label for="quantity">

                                Quantity

                                <span class="required">*</span>

                            </label>


                            <div class="quantity-input">

                                <input
                                    type="number"
                                    name="quantity"
                                    id="quantity"
                                    value="{{ old('quantity', 1) }}"
                                    min="1"
                                    step="1"
                                    required
                                >


                                <div class="quantity-steppers">

                                    <button
                                        type="button"
                                        class="quantity-stepper"
                                        id="quantityMinus"
                                        aria-label="Decrease quantity"
                                    >
                                        −
                                    </button>


                                    <button
                                        type="button"
                                        class="quantity-stepper"
                                        id="quantityPlus"
                                        aria-label="Increase quantity"
                                    >
                                        +
                                    </button>

                                </div>

                            </div>


                            <span class="form-hint">
                                Number of units for this product.
                            </span>


                            @error('quantity')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>


                        {{-- Estimated Price --}}

                        <div class="form-group">

                            <label for="estimated_price">

                                Estimated Price

                                <span class="required">*</span>

                            </label>


                            <div class="currency-input">

                                <span class="currency-prefix">
                                    Rp
                                </span>


                                <button
                                    type="button"
                                    class="currency-stepper minus"
                                    id="estimatedPriceMinus"
                                    aria-label="Decrease estimated price"
                                >
                                    −
                                </button>


                                <input
                                    type="text"
                                    name="estimated_price"
                                    id="estimated_price"
                                    value="{{ old('estimated_price') }}"
                                    placeholder="0"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    required
                                >


                                <button
                                    type="button"
                                    class="currency-stepper plus"
                                    id="estimatedPricePlus"
                                    aria-label="Increase estimated price"
                                >
                                    +
                                </button>

                            </div>


                            <span class="form-hint">
                                Estimated selling price per unit. You can adjust this price from the master product price.
                            </span>


                            @error('estimated_price')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>

                    </div>


                    {{-- Estimated Subtotal --}}

                    <div class="estimated-subtotal-box">

                        <div>

                            <span class="subtotal-label">
                                Estimated Subtotal
                            </span>

                            <span class="subtotal-description">
                                Quantity × Estimated Price
                            </span>

                        </div>


                        <strong id="estimatedSubtotal">
                            Rp 0
                        </strong>

                    </div>


                    {{-- Price Information --}}

                    <div class="price-information">

                        <div class="price-information-icon">
                            i
                        </div>


                        <div>

                            <strong>
                                Estimated Price
                            </strong>

                            <p>
                                The Master Price is the standard product price used as a reference.
                                Estimated Price is the expected selling price for this opportunity and can be adjusted based on customer negotiation, discount, or special pricing.
                            </p>

                        </div>

                    </div>


                    {{-- =================================================
                         NOTES
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Additional Information
                            </h4>

                            <p>
                                Add notes or additional information about this opportunity item.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="notes">
                            Notes
                        </label>


                        <textarea
                            name="notes"
                            id="notes"
                            rows="5"
                            placeholder="Enter additional notes about this opportunity item..."
                        >{{ old('notes') }}</textarea>


                        <span class="form-hint">
                            Optional notes, pricing information, customer requirements, or other item details.
                        </span>


                        @error('notes')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         FORM ACTIONS
                    ================================================== --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('opportunities.show', $opportunity) }}"
                            class="btn"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Add Opportunity Item
                        </button>

                    </div>


                </div>


                {{-- =====================================================
                     RIGHT : PREVIEW
                ====================================================== --}}

                <aside class="opportunity-item-preview-panel">


                    <div class="preview-label">
                        OPPORTUNITY ITEM PREVIEW
                    </div>


                    <div class="preview-product-head">

                        <div class="preview-product-icon">
                            P
                        </div>


                        <div class="preview-product-main">

                            <h3 id="previewProductName">
                                No Product Selected
                            </h3>


                            <span id="previewProductCode">
                                Select a product
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-detail">

                        <span>
                            Opportunity
                        </span>

                        <strong>
                            {{ $opportunity->opportunity_code }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Customer
                        </span>

                        <strong id="previewCustomer">
                            {{ $opportunity->customer->customer_name ?? '-' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Product Type
                        </span>

                        <strong id="previewProductType">
                            -
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Unit
                        </span>

                        <strong id="previewUnit">
                            -
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-financial-row">

                        <span>
                            Master Price
                        </span>

                        <strong id="previewMasterPrice">
                            Rp 0
                        </strong>

                    </div>


                    <div class="preview-financial-row">

                        <span>
                            Quantity
                        </span>

                        <strong id="previewQuantity">
                            1
                        </strong>

                    </div>


                    <div class="preview-financial-row">

                        <span>
                            Estimated Price
                        </span>

                        <strong id="previewEstimatedPrice">
                            Rp 0
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-total">

                        <span>
                            Estimated Subtotal
                        </span>

                        <strong id="previewSubtotal">
                            Rp 0
                        </strong>

                    </div>


                    <div class="preview-description">

                        <div class="preview-description-title">
                            Notes
                        </div>

                        <p id="previewNotes">
                            No notes provided.
                        </p>

                    </div>


                    <div class="preview-note">

                        <strong>
                            Pricing Information
                        </strong>

                        <p>
                            Master Price is the standard product price. Estimated Price represents the expected selling price for this opportunity item and can be adjusted manually.
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   MAIN LAYOUT
========================================================= */

.opportunity-item-create-card,
.opportunity-item-create-body,
.opportunity-item-layout,
.opportunity-item-form-main {
    overflow: visible !important;
}

.opportunity-item-layout {
    display: grid;
    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);
    gap: 32px;
    align-items: start;
    width: 100%;
}

.opportunity-item-form-main {
    min-width: 0;
}


/* =========================================================
   SECTION DIVIDER
========================================================= */

.section-divider {
    display: flex;
    align-items: center;
    margin: 25px 0 18px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.section-divider h4 {
    margin: 0 0 4px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.section-divider p {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}


/* =========================================================
   OPPORTUNITY CONTEXT
========================================================= */

.opportunity-context-grid {
    display: grid;
    grid-template-columns:
        repeat(2, minmax(0, 1fr));
    gap: 12px 20px;
    padding: 16px;
    border: 1px solid #e6eaf0;
    border-radius: 10px;
    background: #fafbfd;
}

.context-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.context-label {
    color: #8a94a6;
    font-size: 10px;
    font-weight: 500;
}

.context-value {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.context-value.highlight {
    color: #2ba7a0;
}


/* =========================================================
   FORM
========================================================= */

.form-group {
    margin-bottom: 19px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.required {
    color: #c94a4a;
}


/* =========================================================
   INPUT
========================================================= */

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    outline: none;
    transition:
        border-color .18s ease,
        box-shadow .18s ease,
        background .18s ease;
}

.form-group input,
.form-group select {
    height: 42px;
    padding: 0 12px;
}

.form-group textarea {
    min-height: 120px;
    padding: 11px 12px;
    line-height: 1.6;
    resize: vertical;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2ba7a0;
    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a0a8b6;
}


/* =========================================================
   HINT / ERROR
========================================================= */

.form-hint {
    display: block;
    margin-top: 6px;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

.form-hint.warning {
    color: #bd7b35;
}

.form-error {
    display: block;
    margin-top: 6px;
    color: #c94a4a;
    font-size: 11px;
    line-height: 1.5;
}


/* =========================================================
   PRODUCT REFERENCE
========================================================= */

.product-reference {
    display: grid;
    grid-template-columns:
        repeat(4, minmax(0, 1fr));
    gap: 12px;
    margin-top: 4px;
    padding: 14px 15px;
    border: 1px solid #e3e8ef;
    border-radius: 9px;
    background: #f8fafc;
}

.product-reference-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.product-reference-item span {
    color: #8a94a6;
    font-size: 10px;
}

.product-reference-item strong {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #34415c;
    font-size: 11px;
    font-weight: 600;
}


/* =========================================================
   TWO COLUMN
========================================================= */

.item-two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}


/* =========================================================
   REMOVE BROWSER NUMBER SPINNER
========================================================= */

.quantity-input input::-webkit-outer-spin-button,
.quantity-input input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}


/* =========================================================
   QUANTITY INPUT
========================================================= */

.quantity-input {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
    height: 42px;
}

.quantity-input input {
    width: 100%;
    height: 42px;
    box-sizing: border-box;
    padding: 0 72px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    text-align: left;
    outline: none;
}

.quantity-input input:focus {
    border-color: #2ba7a0;
    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   QUANTITY STEPPERS
========================================================= */

.quantity-steppers {
    position: absolute;
    right: 7px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 3;
}

.quantity-stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 27px;
    height: 27px;
    padding: 0;
    border: 1px solid #dfe4eb;
    border-radius: 6px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 16px;
    font-weight: 500;
    line-height: 1;
    cursor: pointer;
    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease;
}

.quantity-stepper:hover {
    background: #f5f7fb;
    border-color: #2ba7a0;
    color: #2ba7a0;
}

.quantity-stepper:active {
    transform: scale(.95);
}


/* =========================================================
   CURRENCY INPUT
========================================================= */

.currency-input {
    position: relative;
    width: 100%;
    height: 42px;
    box-sizing: border-box;
}

.currency-input input {
    width: 100%;
    height: 42px;
    box-sizing: border-box;
    padding: 0 76px 0 35px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    text-align: left;
    outline: none;
}

.currency-input input:focus {
    border-color: #2ba7a0;
    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.currency-prefix {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #718096;
    font-size: 11px;
    font-weight: 600;
    pointer-events: none;
    z-index: 2;
}

.currency-stepper {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 25px;
    height: 25px;
    padding: 0;
    border: 1px solid #dfe4eb;
    border-radius: 6px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 16px;
    font-weight: 500;
    line-height: 23px;
    text-align: center;
    cursor: pointer;
    z-index: 3;
    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease;
}

.currency-stepper.minus {
    right: 40px;
}

.currency-stepper.plus {
    right: 10px;
}

.currency-stepper:hover {
    background: #f5f7fb;
    border-color: #2ba7a0;
    color: #2ba7a0;
}

.currency-stepper:active {
    transform:
        translateY(-50%) scale(.96);
}


/* =========================================================
   ESTIMATED SUBTOTAL
========================================================= */

.estimated-subtotal-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin: 2px 0 16px;
    padding: 15px 17px;
    border: 1px solid #dce8e4;
    border-radius: 9px;
    background: #f5fbf8;
}

.subtotal-label {
    display: block;
    margin-bottom: 3px;
    color: #34415c;
    font-size: 11px;
    font-weight: 700;
}

.subtotal-description {
    display: block;
    color: #8a94a6;
    font-size: 10px;
}

.estimated-subtotal-box > strong {
    color: #167d70;
    font-size: 16px;
    font-weight: 700;
    white-space: nowrap;
}


/* =========================================================
   PRICE INFORMATION
========================================================= */

.price-information {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin: 4px 0 4px;
    padding: 13px 14px;
    border-radius: 8px;
    background: #f5f7fa;
}

.price-information-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    border: 1px solid #cfd7e2;
    border-radius: 50%;
    color: #718096;
    font-size: 10px;
    font-weight: 700;
}

.price-information strong {
    display: block;
    margin-bottom: 4px;
    color: #34415c;
    font-size: 10px;
}

.price-information p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #edf0f5;
}


/* =========================================================
   PREVIEW PANEL
========================================================= */

.opportunity-item-preview-panel {
    position: sticky;
    top: 92px;
    align-self: start;
    height: fit-content;
    padding: 24px;
    border: 1px solid #e6eaf0;
    border-radius: 12px;
    background: #fafbfd;
    box-sizing: border-box;
    min-width: 0;
    z-index: 5;
}


/* =========================================================
   PREVIEW
========================================================= */

.preview-label {
    margin-bottom: 20px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.preview-product-head {
    display: flex;
    align-items: center;
    gap: 13px;
}

.preview-product-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 17px;
    font-weight: 700;
}

.preview-product-main {
    min-width: 0;
}

.preview-product-main h3 {
    margin: 0 0 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #17284f;
    font-size: 16px;
    font-weight: 700;
}

.preview-product-main > span {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #7d8797;
    font-size: 11px;
}

.preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e5e9ef;
}

.preview-detail {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 8px 0;
}

.preview-detail > span {
    color: #8a94a6;
    font-size: 11px;
}

.preview-detail > strong {
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: #34415c;
    font-size: 11px;
    font-weight: 600;
    text-align: right;
}

.preview-financial-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 8px 0;
}

.preview-financial-row span {
    color: #8a94a6;
    font-size: 11px;
}

.preview-financial-row strong {
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
    text-align: right;
}

.preview-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 0;
}

.preview-total span {
    color: #34415c;
    font-size: 11px;
    font-weight: 700;
}

.preview-total strong {
    color: #167d70;
    font-size: 16px;
    font-weight: 700;
    text-align: right;
}

.preview-description {
    margin-top: 5px;
    margin-bottom: 20px;
}

.preview-description-title {
    margin-bottom: 8px;
    color: #34415c;
    font-size: 11px;
    font-weight: 700;
}

.preview-description p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
    white-space: pre-line;
    word-break: break-word;
}

.preview-note {
    padding: 13px 14px;
    border-radius: 8px;
    background: #f1f5f7;
}

.preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #34415c;
    font-size: 11px;
}

.preview-note p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .opportunity-item-layout {
        grid-template-columns: 1fr;
    }

    .opportunity-item-preview-panel {
        position: static;
        order: -1;
    }

}

@media (max-width: 800px) {

    .product-reference {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}

@media (max-width: 650px) {

    .item-two-column {
        grid-template-columns: 1fr;
    }

    .opportunity-context-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .form-actions .btn {
        width: 100%;
        justify-content: center;
    }

}

@media (max-width: 450px) {

    .product-reference {
        grid-template-columns: 1fr;
    }

    .opportunity-item-preview-panel {
        padding: 20px;
    }

    .preview-detail {
        flex-direction: column;
        gap: 4px;
    }

    .preview-detail > strong {
        max-width: 100%;
        text-align: left;
    }

    .preview-financial-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 4px;
    }

    .preview-financial-row strong {
        text-align: left;
    }

    .estimated-subtotal-box {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /* =====================================================
           FORM ELEMENTS
        ====================================================== */

        const productSelect =
            document.getElementById('product_id');

        const quantityInput =
            document.getElementById('quantity');

        const estimatedPriceInput =
            document.getElementById('estimated_price');

        const notesInput =
            document.getElementById('notes');


        const quantityMinus =
            document.getElementById('quantityMinus');

        const quantityPlus =
            document.getElementById('quantityPlus');

        const estimatedPriceMinus =
            document.getElementById('estimatedPriceMinus');

        const estimatedPricePlus =
            document.getElementById('estimatedPricePlus');


        /* =====================================================
           PRODUCT REFERENCE
        ====================================================== */

        const productReference =
            document.getElementById('productReference');

        const referenceProductCode =
            document.getElementById('referenceProductCode');

        const referenceProductType =
            document.getElementById('referenceProductType');

        const referenceProductUnit =
            document.getElementById('referenceProductUnit');

        const referenceProductPrice =
            document.getElementById('referenceProductPrice');


        /* =====================================================
           PREVIEW ELEMENTS
        ====================================================== */

        const previewProductName =
            document.getElementById('previewProductName');

        const previewProductCode =
            document.getElementById('previewProductCode');

        const previewProductType =
            document.getElementById('previewProductType');

        const previewUnit =
            document.getElementById('previewUnit');

        const previewMasterPrice =
            document.getElementById('previewMasterPrice');

        const previewQuantity =
            document.getElementById('previewQuantity');

        const previewEstimatedPrice =
            document.getElementById('previewEstimatedPrice');

        const previewSubtotal =
            document.getElementById('previewSubtotal');

        const previewNotes =
            document.getElementById('previewNotes');

        const estimatedSubtotal =
            document.getElementById('estimatedSubtotal');


        /* =====================================================
           FORMAT NUMBER FROM DATABASE
           
           IMPORTANT:
           Jangan menggunakan replace(/\D/g, '') di sini.
           
           Contoh:
           "20000.00" -> 20000
           bukan
           "2000000"
        ====================================================== */

        function getProductPrice(value) {

            const number =
                parseFloat(value);

            return Number.isFinite(number)
                ? number
                : 0;

        }


        /* =====================================================
           FORMAT CURRENCY FOR DISPLAY
        ====================================================== */

        function formatCurrency(value) {

            const number =
                Number(value) || 0;

            return 'Rp ' +
                number.toLocaleString('id-ID');

        }


        /* =====================================================
           GET NUMERIC CURRENCY FROM USER INPUT
           
           Input:
           20.000
           
           Result:
           20000
        ====================================================== */

        function getNumericCurrency(value) {

            const cleaned =
                String(value ?? '')
                    .replace(/\./g, '')
                    .replace(/,/g, '')
                    .replace(/[^\d]/g, '');

            return Number(cleaned) || 0;

        }


        /* =====================================================
           FORMAT CURRENCY INPUT
           
           20000
           ->
           20.000
        ====================================================== */

        function formatCurrencyInput(value) {

            const cleaned =
                String(value ?? '')
                    .replace(/\./g, '')
                    .replace(/,/g, '')
                    .replace(/[^\d]/g, '');

            if (!cleaned) {

                return '';

            }

            return Number(cleaned)
                .toLocaleString('id-ID');

        }


        /* =====================================================
           GET QUANTITY
        ====================================================== */

        function getQuantity() {

            const value =
                parseInt(
                    quantityInput.value,
                    10
                );

            return Math.max(
                1,
                Number.isNaN(value)
                    ? 1
                    : value
            );

        }


        /* =====================================================
           UPDATE PRODUCT INFORMATION
        ====================================================== */

        function updateProductInformation(
            resetEstimatedPrice = true
        ) {

            const selectedOption =
                productSelect.options[
                    productSelect.selectedIndex
                ];


            if (
                !selectedOption ||
                !selectedOption.value
            ) {

                productReference.style.display =
                    'none';

                referenceProductCode.textContent =
                    '-';

                referenceProductType.textContent =
                    '-';

                referenceProductUnit.textContent =
                    '-';

                referenceProductPrice.textContent =
                    'Rp 0';


                previewProductName.textContent =
                    'No Product Selected';

                previewProductCode.textContent =
                    'Select a product';

                previewProductType.textContent =
                    '-';

                previewUnit.textContent =
                    '-';

                previewMasterPrice.textContent =
                    'Rp 0';


                if (resetEstimatedPrice) {

                    estimatedPriceInput.value =
                        '';

                }


                updatePreview();

                return;

            }


            const code =
                selectedOption.dataset.code || '-';

            const name =
                selectedOption.dataset.name ||
                selectedOption.text.trim();

            const type =
                selectedOption.dataset.type || '-';

            const unit =
                selectedOption.dataset.unit || '-';


            /*
             * PENTING:
             *
             * Harga product dibaca menggunakan
             * parseFloat(), bukan getNumericCurrency().
             *
             * 20000.00 -> 20000
             */

            const price =
                getProductPrice(
                    selectedOption.dataset.price
                );


            /* Product Reference */

            referenceProductCode.textContent =
                code;

            referenceProductType.textContent =
                type;

            referenceProductUnit.textContent =
                unit;

            referenceProductPrice.textContent =
                formatCurrency(price);

            productReference.style.display =
                'grid';


            /* Preview */

            previewProductName.textContent =
                name;

            previewProductCode.textContent =
                code;

            previewProductType.textContent =
                type;

            previewUnit.textContent =
                unit;

            previewMasterPrice.textContent =
                formatCurrency(price);


            /*
             * Saat product dipilih,
             * Estimated Price mengikuti Master Price.
             */

            if (resetEstimatedPrice) {

                estimatedPriceInput.value =
                    price > 0
                        ? formatCurrencyInput(price)
                        : '';

            }


            updatePreview();

        }


        /* =====================================================
           CALCULATE SUBTOTAL
        ====================================================== */

        function calculateSubtotal() {

            const quantity =
                getQuantity();

            const estimatedPrice =
                getNumericCurrency(
                    estimatedPriceInput.value
                );

            return quantity *
                estimatedPrice;

        }


        /* =====================================================
           UPDATE PREVIEW
        ====================================================== */

        function updatePreview() {

            const quantity =
                getQuantity();

            const estimatedPrice =
                getNumericCurrency(
                    estimatedPriceInput.value
                );

            const subtotal =
                quantity *
                estimatedPrice;


            previewQuantity.textContent =
                quantity;


            previewEstimatedPrice.textContent =
                formatCurrency(
                    estimatedPrice
                );


            previewSubtotal.textContent =
                formatCurrency(
                    subtotal
                );


            estimatedSubtotal.textContent =
                formatCurrency(
                    subtotal
                );


            previewNotes.textContent =
                notesInput.value.trim() ||
                'No notes provided.';

        }


        /* =====================================================
           PRODUCT CHANGE
        ====================================================== */

        productSelect.addEventListener(
            'change',
            function () {

                updateProductInformation(true);

            }
        );


        /* =====================================================
           ESTIMATED PRICE INPUT
        ====================================================== */

        estimatedPriceInput.addEventListener(
            'input',
            function () {

                estimatedPriceInput.value =
                    formatCurrencyInput(
                        estimatedPriceInput.value
                    );

                updatePreview();

            }
        );


        /* =====================================================
           ESTIMATED PRICE PLUS
        ====================================================== */

        estimatedPricePlus.addEventListener(
            'click',
            function () {

                const currentValue =
                    getNumericCurrency(
                        estimatedPriceInput.value
                    );

                const newValue =
                    currentValue + 1000;

                estimatedPriceInput.value =
                    formatCurrencyInput(
                        newValue
                    );

                estimatedPriceInput.focus();

                updatePreview();

            }
        );


        /* =====================================================
           ESTIMATED PRICE MINUS
        ====================================================== */

        estimatedPriceMinus.addEventListener(
            'click',
            function () {

                const currentValue =
                    getNumericCurrency(
                        estimatedPriceInput.value
                    );

                const newValue =
                    Math.max(
                        0,
                        currentValue - 1000
                    );

                estimatedPriceInput.value =
                    formatCurrencyInput(
                        newValue
                    );

                estimatedPriceInput.focus();

                updatePreview();

            }
        );


        /* =====================================================
           QUANTITY INPUT
        ====================================================== */

        quantityInput.addEventListener(
            'input',
            function () {

                let quantity =
                    parseInt(
                        quantityInput.value,
                        10
                    );


                if (
                    Number.isNaN(quantity) ||
                    quantity < 1
                ) {

                    quantity = 1;

                }


                quantityInput.value =
                    quantity;

                updatePreview();

            }
        );


        /* =====================================================
           QUANTITY PLUS
        ====================================================== */

        quantityPlus.addEventListener(
            'click',
            function () {

                const currentQuantity =
                    getQuantity();

                quantityInput.value =
                    currentQuantity + 1;

                quantityInput.focus();

                updatePreview();

            }
        );


        /* =====================================================
           QUANTITY MINUS
        ====================================================== */

        quantityMinus.addEventListener(
            'click',
            function () {

                const currentQuantity =
                    getQuantity();

                quantityInput.value =
                    Math.max(
                        1,
                        currentQuantity - 1
                    );

                quantityInput.focus();

                updatePreview();

            }
        );


        /* =====================================================
           NOTES
        ====================================================== */

        notesInput.addEventListener(
            'input',
            updatePreview
        );


        /* =====================================================
           FORM SUBMIT
        ====================================================== */

        const form =
            document.getElementById(
                'opportunityItemForm'
            );


        form.addEventListener(
            'submit',
            function () {

                /*
                 * Laravel menerima:
                 *
                 * 20000
                 *
                 * bukan:
                 *
                 * 20.000
                 */

                estimatedPriceInput.value =
                    getNumericCurrency(
                        estimatedPriceInput.value
                    ) || '';


                quantityInput.value =
                    getQuantity();

            }
        );


        /* =====================================================
           INITIAL PREVIEW
        ====================================================== */

        if (productSelect.value) {

            updateProductInformation(true);

        }


        updatePreview();

    }
);

</script>

@endsection