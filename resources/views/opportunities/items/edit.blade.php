@extends('layouts.app')

@section('title', 'Edit Opportunity Item')

@section('content')

<div class="page-head">

<div>

    <h1>Edit Opportunity Item</h1>

    <p>
        Update the product, quantity, estimated price, or notes
        for this opportunity item.
    </p>

</div>

<div class="actions">

    <a
        href="{{ route('opportunities.show', $opportunity) }}"
        class="btn">
        ← Back
    </a>

</div>

</div>

@if($errors->any())

<div class="alert error">

<strong>Please check the following:</strong>

<ul>

    @foreach($errors->all() as $error)

    <li>{{ $error }}</li>

    @endforeach

</ul>

</div>

@endif

{{-- =========================================================
OPPORTUNITY INFORMATION
========================================================= --}}

<div class="card opportunity-info">

<div class="card-head">

    <div>

        <h3>Opportunity</h3>

        <p>
            Opportunity associated with this item.
        </p>

    </div>

</div>


<div class="card-body">

    <div class="opportunity-info-grid">

        <div>

            <span class="info-label">
                Opportunity Code
            </span>

            <span class="info-value highlight">
                {{ $opportunity->opportunity_code }}
            </span>

        </div>


        <div>

            <span class="info-label">
                Opportunity Name
            </span>

            <span class="info-value">
                {{ $opportunity->name }}
            </span>

        </div>


        <div>

            <span class="info-label">
                Customer
            </span>

            <span class="info-value">
                {{ $opportunity->customer->customer_name ?? '-' }}
            </span>

        </div>


        <div>

            <span class="info-label">
                Stage
            </span>

            <span class="info-value">
                {{ $opportunity->stage }}
            </span>

        </div>

    </div>

</div>

</div>

<form
    method="POST"
    action="{{ route(
        'opportunities.items.update',
        [$opportunity, $item]
    ) }}"
    id="opportunityItemForm">

@csrf

@method('PUT')


<div class="edit-layout">


    {{-- =================================================
     LEFT
     FORM
================================================== --}}

    <div class="card form-card">

        <div class="card-head">

            <div>

                <h3>Item Information</h3>

                <p>
                    Update the information for this opportunity item.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="form-grid">


                {{-- =================================================
                 PRODUCT
            ================================================== --}}

                <div class="form-group full">

                    <label for="product_id">

                        Product

                        <span class="required">
                            *
                        </span>

                    </label>


                    <select
                        name="product_id"
                        id="product_id"
                        required>

                        <option value="">
                            Select Product
                        </option>


                        @foreach($products as $product)

                        <option
                            value="{{ $product->product_id }}"
                            data-code="{{ $product->product_code }}"
                            data-name="{{ $product->product_name }}"
                            data-price="{{ $product->getRawOriginal('price') }}"
                            data-type="{{ $product->product_type }}"
                            data-unit="{{ $product->unit }}"
                            @selected(
                                old(
                                    'product_id',
                                    $item->product_id
                                ) == $product->product_id
                            )>

                            {{ $product->product_code }}
                            —
                            {{ $product->product_name }}

                            @if($product->status !== 'active')

                            (Inactive)

                            @endif

                        </option>

                        @endforeach

                    </select>


                    @if($products->isEmpty())

                    <small class="form-hint warning">

                        No products are available.

                    </small>

                    @else

                    <small class="form-hint">

                        Select the product that will be included
                        in this opportunity.

                    </small>

                    @endif

                </div>


                {{-- =================================================
                 PRODUCT INFORMATION
            ================================================== --}}

                <div
                    class="product-reference full"
                    id="productReference">

                    <div>

                        <span class="reference-label">
                            Product Code
                        </span>

                        <span
                            class="reference-value"
                            id="productCode">
                            -
                        </span>

                    </div>


                    <div>

                        <span class="reference-label">
                            Product Type
                        </span>

                        <span
                            class="reference-value"
                            id="productType">
                            -
                        </span>

                    </div>


                    <div>

                        <span class="reference-label">
                            Unit
                        </span>

                        <span
                            class="reference-value"
                            id="productUnit">
                            -
                        </span>

                    </div>


                    <div>

                        <span class="reference-label">
                            Master Price
                        </span>

                        <span
                            class="reference-value price"
                            id="productPrice">
                            -
                        </span>

                    </div>

                </div>


                {{-- =================================================
                 QUANTITY
            ================================================== --}}

                <div class="form-group">

                    <label for="quantity">

                        Quantity

                        <span class="required">
                            *
                        </span>

                    </label>


                    <div class="quantity-input">

                        <input
                            type="number"
                            name="quantity"
                            id="quantity"
                            value="{{ old(
                                'quantity',
                                $item->quantity
                            ) }}"
                            min="1"
                            step="1"
                            required>


                        <div class="quantity-steppers">

                            <button
                                type="button"
                                class="quantity-stepper"
                                id="quantityMinus"
                                aria-label="Decrease quantity">
                                −
                            </button>


                            <button
                                type="button"
                                class="quantity-stepper"
                                id="quantityPlus"
                                aria-label="Increase quantity">
                                +
                            </button>

                        </div>

                    </div>


                    <small class="form-hint">

                        Number of units for this product.

                    </small>

                </div>


                {{-- =================================================
                 ESTIMATED PRICE
            ================================================== --}}

                <div class="form-group">

                    <label for="estimated_price">

                        Estimated Price

                        <span class="required">
                            *
                        </span>

                    </label>


                    <div class="currency-input">

                        <span class="currency-prefix">
                            Rp
                        </span>

                        <input
                            type="text"
                            name="estimated_price"
                            id="estimated_price"
                            value="{{ old(
                                'estimated_price',
                                $item->estimated_price
                            ) }}"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="0"
                            required>

                    </div>


                    <small class="form-hint">

                        Estimated selling price per unit.

                    </small>

                </div>


                {{-- =================================================
                 ESTIMATED SUBTOTAL
            ================================================== --}}

                <div class="form-group full">

                    <label>
                        Estimated Subtotal
                    </label>


                    <div class="subtotal-box">

                        <div>

                            <span class="subtotal-label">
                                Quantity × Estimated Price
                            </span>

                            <span
                                class="subtotal-value"
                                id="subtotalValue">
                                Rp 0
                            </span>

                        </div>

                    </div>


                    <small class="form-hint">

                        Estimated subtotal is calculated automatically
                        based on quantity and estimated selling price.

                    </small>

                </div>


                {{-- =================================================
                 PRICE INFORMATION
            ================================================== --}}

                <div class="price-info full">

                    <div class="price-info-icon">
                        i
                    </div>

                    <div>

                        <strong>
                            Pricing Information
                        </strong>

                        <p>
                            Master Price is the standard price of the
                            selected product. Estimated Price represents
                            the expected selling price for this
                            opportunity and may be adjusted based on
                            negotiation.
                        </p>

                    </div>

                </div>


                {{-- =================================================
                 NOTES
            ================================================== --}}

                <div class="form-group full">

                    <label for="notes">
                        Notes
                    </label>


                    <textarea
                        name="notes"
                        id="notes"
                        rows="5"
                        placeholder="Additional notes about this item...">{{ old(
                            'notes',
                            $item->notes
                        ) }}</textarea>


                    <small class="form-hint">

                        Optional notes for this opportunity item.

                    </small>

                </div>

            </div>

        </div>


        {{-- =================================================
         FORM FOOTER
    ================================================== --}}

        <div class="form-footer">

            <a
                href="{{ route(
                    'opportunities.show',
                    $opportunity
                ) }}"
                class="btn">
                Cancel
            </a>


            <button
                type="submit"
                class="btn primary">
                Update Item
            </button>

        </div>

    </div>


    {{-- =================================================
     RIGHT
     PREVIEW
================================================== --}}

    <div class="card preview-card">

        <div class="card-head">

            <div>

                <h3>Item Preview</h3>

                <p>
                    Preview of the opportunity item.
                </p>

            </div>

        </div>


        <div class="card-body preview-body">


            {{-- Product --}}

            <div class="preview-product">

                <span class="preview-small-label">
                    Product
                </span>

                <strong
                    id="previewProductName"
                    class="preview-product-name">
                    -
                </strong>

                <span
                    id="previewProductCode"
                    class="preview-product-code">
                    -
                </span>

            </div>


            {{-- Opportunity --}}

            <div class="preview-section">

                <span class="preview-section-title">
                    Opportunity
                </span>


                <div class="preview-row">

                    <span>
                        Code
                    </span>

                    <strong>
                        {{ $opportunity->opportunity_code }}
                    </strong>

                </div>


                <div class="preview-row">

                    <span>
                        Customer
                    </span>

                    <strong>
                        {{ $opportunity->customer->customer_name ?? '-' }}
                    </strong>

                </div>

            </div>


            {{-- Product Details --}}

            <div class="preview-section">

                <span class="preview-section-title">
                    Product Details
                </span>


                <div class="preview-row">

                    <span>
                        Product Type
                    </span>

                    <strong id="previewType">
                        -
                    </strong>

                </div>


                <div class="preview-row">

                    <span>
                        Unit
                    </span>

                    <strong id="previewUnit">
                        -
                    </strong>

                </div>


                <div class="preview-row">

                    <span>
                        Master Price
                    </span>

                    <strong id="previewMasterPrice">
                        -
                    </strong>

                </div>

            </div>


            {{-- Pricing --}}

            <div class="preview-section">

                <span class="preview-section-title">
                    Pricing
                </span>


                <div class="preview-row">

                    <span>
                        Quantity
                    </span>

                    <strong id="previewQuantity">
                        {{ $item->quantity }}
                    </strong>

                </div>


                <div class="preview-row">

                    <span>
                        Estimated Price
                    </span>

                    <strong id="previewEstimatedPrice">
                        -
                    </strong>

                </div>


                <div class="preview-total">

                    <span>
                        Estimated Subtotal
                    </span>

                    <strong id="previewSubtotal">
                        Rp 0
                    </strong>

                </div>

            </div>


            {{-- Notes --}}

            <div class="preview-section notes-preview">

                <span class="preview-section-title">
                    Notes
                </span>


                <p id="previewNotes">
                    No notes added.
                </p>

            </div>

        </div>

    </div>

</div>

</form>

<style>

    .opportunity-info {
        margin-bottom: 20px;
    }

    .opportunity-info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 20px;
    }

    .opportunity-info-grid > div {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        color: #8a94a6;
        font-size: 10px;
    }

    .info-value {
        color: #17284f;
        font-size: 12px;
        font-weight: 600;
    }

    .info-value.highlight {
        color: #2ba7a0;
    }


    /* =========================================================
       EDIT LAYOUT
    ========================================================= */

    .edit-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.55fr) minmax(300px, .85fr);
        gap: 20px;
        align-items: start;
    }

    .form-card,
    .preview-card {
        min-width: 0;
    }


    /* =========================================================
       FORM
    ========================================================= */

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .form-group.full {
        grid-column: 1 / -1;
    }

    .form-group label {
        color: #17284f;
        font-size: 11px;
        font-weight: 700;
    }

    .required {
        color: #d35c5c;
    }


    /* =========================================================
       INPUT
    ========================================================= */

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 9px 11px;
        border: 1px solid #dfe4ec;
        border-radius: 8px;
        background: #ffffff;
        color: #17284f;
        font-family: inherit;
        font-size: 12px;
        outline: none;
        transition:
            border-color .15s ease,
            box-shadow .15s ease;
    }

    .form-group input,
    .form-group select {
        min-height: 38px;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 110px;
        line-height: 1.5;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #2ba7a0;
        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);
    }


    /* =========================================================
       REMOVE NUMBER SPINNER
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
       QUANTITY
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
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .quantity-input input:focus {
        border-color: #2ba7a0;
        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);
    }

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
        text-align: center;
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
       CURRENCY
    ========================================================= */

    .currency-input {
        position: relative;
        width: 100%;
    }

    .currency-prefix {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #667085;
        font-size: 12px;
        font-weight: 600;
        pointer-events: none;
        z-index: 2;
    }

    .currency-input input {
        padding-left: 34px !important;
        font-weight: 600;
    }


    /* =========================================================
       HINT
    ========================================================= */

    .form-hint {
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.5;
    }

    .form-hint.warning {
        color: #bd7b35;
    }


    /* =========================================================
       PRODUCT REFERENCE
    ========================================================= */

    .product-reference {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        padding: 13px 15px;
        border: 1px solid #e3e9ef;
        border-radius: 8px;
        background: #f8fafc;
    }

    .product-reference > div {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .reference-label {
        color: #8a94a6;
        font-size: 9px;
    }

    .reference-value {
        color: #17284f;
        font-size: 11px;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .reference-value.price {
        color: #15966f;
    }


    /* =========================================================
       SUBTOTAL
    ========================================================= */

    .subtotal-box {
        display: flex;
        align-items: center;
        min-height: 58px;
        padding: 12px 14px;
        box-sizing: border-box;
        border: 1px solid #dcebe5;
        border-radius: 8px;
        background: #f5fbf8;
    }

    .subtotal-box > div {
        display: flex;
        flex-direction: column;
        gap: 4px;
        width: 100%;
    }

    .subtotal-label {
        color: #8a94a6;
        font-size: 9px;
        font-weight: 500;
    }

    .subtotal-value {
        color: #15966f;
        font-size: 16px;
        font-weight: 700;
    }


    /* =========================================================
       PRICE INFORMATION
    ========================================================= */

    .price-info {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        background: #fafbfc;
    }

    .price-info-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        width: 20px;
        height: 20px;
        border: 1px solid #cfd8e3;
        border-radius: 50%;
        color: #667085;
        font-size: 10px;
        font-weight: 700;
    }

    .price-info strong {
        display: block;
        margin-bottom: 4px;
        color: #17284f;
        font-size: 10px;
    }

    .price-info p {
        margin: 0;
        color: #8a94a6;
        font-size: 9px;
        line-height: 1.55;
    }


    /* =========================================================
       FORM FOOTER
    ========================================================= */

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 20px;
        border-top: 1px solid #edf0f4;
    }


    /* =========================================================
       PREVIEW CARD
    ========================================================= */

    .preview-card {
        position: sticky;
        top: 85px;
    }

    .preview-body {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }


    /* =========================================================
       PREVIEW PRODUCT
    ========================================================= */

    .preview-product {
        padding: 14px;
        border: 1px solid #e1e8ef;
        border-radius: 8px;
        background: #f8fafc;
    }

    .preview-small-label {
        display: block;
        margin-bottom: 5px;
        color: #8a94a6;
        font-size: 9px;
    }

    .preview-product-name {
        display: block;
        color: #17284f;
        font-size: 14px;
        line-height: 1.35;
    }

    .preview-product-code {
        display: block;
        margin-top: 4px;
        color: #2ba7a0;
        font-size: 9px;
        font-weight: 600;
    }


    /* =========================================================
       PREVIEW SECTION
    ========================================================= */

    .preview-section {
        display: flex;
        flex-direction: column;
        gap: 9px;
        padding-bottom: 15px;
        border-bottom: 1px solid #edf0f4;
    }

    .preview-section-title {
        color: #8a94a6;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .preview-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .preview-row span {
        color: #8a94a6;
        font-size: 10px;
    }

    .preview-row strong {
        color: #17284f;
        font-size: 10px;
        font-weight: 600;
        text-align: right;
    }


    /* =========================================================
       PREVIEW TOTAL
    ========================================================= */

    .preview-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 3px;
        padding: 11px 12px;
        border: 1px solid #dcebe5;
        border-radius: 7px;
        background: #f5fbf8;
    }

    .preview-total span {
        color: #667085;
        font-size: 10px;
        font-weight: 600;
    }

    .preview-total strong {
        color: #15966f;
        font-size: 12px;
        font-weight: 700;
    }


    /* =========================================================
       NOTES PREVIEW
    ========================================================= */

    .notes-preview {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .notes-preview p {
        margin: 0;
        color: #667085;
        font-size: 10px;
        line-height: 1.55;
        word-break: break-word;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1100px) {

        .edit-layout {
            grid-template-columns: 1fr;
        }

        .preview-card {
            position: static;
        }

    }


    @media (max-width: 900px) {

        .opportunity-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .product-reference {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 700px) {

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: auto;
        }

        .product-reference {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 500px) {

        .opportunity-info-grid {
            grid-template-columns: 1fr;
        }

        .form-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-footer .btn {
            width: 100%;
        }

    }

</style>

{{-- =========================================================
SCRIPT
========================================================= --}}

<script>

    document.addEventListener(
        'DOMContentLoaded',
        function() {

            const form =
                document.getElementById(
                    'opportunityItemForm'
                );

            const productSelect =
                document.getElementById(
                    'product_id'
                );

            const quantityInput =
                document.getElementById(
                    'quantity'
                );

            const estimatedPriceInput =
                document.getElementById(
                    'estimated_price'
                );

            const notesInput =
                document.getElementById(
                    'notes'
                );


            /* =====================================================
               PRODUCT REFERENCE
            ===================================================== */

            const productCode =
                document.getElementById(
                    'productCode'
                );

            const productType =
                document.getElementById(
                    'productType'
                );

            const productUnit =
                document.getElementById(
                    'productUnit'
                );

            const productPrice =
                document.getElementById(
                    'productPrice'
                );


            /* =====================================================
               FORM PREVIEW
            ===================================================== */

            const previewProductName =
                document.getElementById(
                    'previewProductName'
                );

            const previewProductCode =
                document.getElementById(
                    'previewProductCode'
                );

            const previewType =
                document.getElementById(
                    'previewType'
                );

            const previewUnit =
                document.getElementById(
                    'previewUnit'
                );

            const previewMasterPrice =
                document.getElementById(
                    'previewMasterPrice'
                );

            const previewQuantity =
                document.getElementById(
                    'previewQuantity'
                );

            const previewEstimatedPrice =
                document.getElementById(
                    'previewEstimatedPrice'
                );

            const previewSubtotal =
                document.getElementById(
                    'previewSubtotal'
                );

            const previewNotes =
                document.getElementById(
                    'previewNotes'
                );


            /* =====================================================
               SUBTOTAL
            ===================================================== */

            const subtotalValue =
                document.getElementById(
                    'subtotalValue'
                );


            /* =====================================================
               CURRENCY PARSING
            ===================================================== */

            /*
             * =====================================================
             * CLEAN PRODUCT PRICE
             *
             * Digunakan untuk harga Product yang berasal
             * dari PostgreSQL.
             *
             * Contoh:
             *
             * "23000.00"
             *      ↓
             * "23000"
             *      ↓
             * 23000
             * =====================================================
             */

            function cleanProductPrice(value) {

                if (
                    value === null ||
                    value === undefined ||
                    value === ''
                ) {
                    return 0;
                }


                let stringValue =
                    String(value).trim();


                /*
                 * PostgreSQL DECIMAL / NUMERIC:
                 *
                 * 23000.00
                 * 18500.00
                 *
                 * Bagian decimal tidak digunakan karena
                 * harga disimpan sebagai nominal Rupiah.
                 */

                if (
                    /^\d+\.\d{1,2}$/.test(
                        stringValue
                    )
                ) {

                    stringValue =
                        stringValue.split('.')[0];

                }


                stringValue =
                    stringValue.replace(
                        /\D/g,
                        ''
                    );


                return Number(
                    stringValue
                ) || 0;

            }


            /*
             * =====================================================
             * CLEAN ESTIMATED VALUE
             *
             * Fungsi utama untuk Estimated Price.
             *
             * Database:
             *
             * 23000.00
             *      ↓
             * 23000
             *
             * Tampilan:
             *
             * 23.000
             *
             * Input:
             *
             * 23.000
             *      ↓
             * 23000
             *
             * Backend:
             *
             * 23000
             * =====================================================
             */

            function cleanEstimatedValue(value) {

                if (
                    value === null ||
                    value === undefined ||
                    value === ''
                ) {
                    return 0;
                }


                let stringValue =
                    String(value).trim();


                /*
                 * PostgreSQL decimal.
                 *
                 * Contoh:
                 *
                 * 23000.00
                 *
                 * menjadi:
                 *
                 * 23000
                 *
                 * Ini adalah bagian penting yang
                 * mencegah 23000.00 menjadi 2300000.
                 */

                if (
                    /^\d+\.\d{1,2}$/.test(
                        stringValue
                    )
                ) {

                    stringValue =
                        stringValue.split('.')[0];

                }


                /*
                 * Setelah decimal PostgreSQL dibuang,
                 * titik pada format Indonesia dianggap
                 * sebagai pemisah ribuan.
                 *
                 * Contoh:
                 *
                 * 23.000
                 * →
                 * 23000
                 */

                stringValue =
                    stringValue.replace(
                        /\D/g,
                        ''
                    );


                return Number(
                    stringValue
                ) || 0;

            }


            /*
             * =====================================================
             * GET NUMERIC CURRENCY
             * =====================================================
             */

            function getNumericCurrency(value) {

                return cleanEstimatedValue(
                    value
                );

            }


            /*
             * =====================================================
             * FORMAT CURRENCY INPUT
             *
             * Contoh:
             *
             * 23000
             * →
             * 23.000
             *
             * 18500
             * →
             * 18.500
             * =====================================================
             */

            function formatCurrencyInput(value) {

                const numericValue =
                    cleanEstimatedValue(
                        value
                    );


                if (
                    numericValue <= 0
                ) {

                    return '';

                }


                return numericValue.toLocaleString(
                    'id-ID'
                );

            }


            /*
             * =====================================================
             * FORMAT CURRENCY DISPLAY
             *
             * Contoh:
             *
             * 23000
             * →
             * Rp 23.000
             * =====================================================
             */

            function formatCurrency(value) {

                const numericValue =
                    cleanEstimatedValue(
                        value
                    );


                return (
                    'Rp ' +
                    numericValue.toLocaleString(
                        'id-ID'
                    )
                );

            }


            /* =====================================================
               QUANTITY
            ===================================================== */

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


            function updateQuantity() {

                quantityInput.value =
                    getQuantity();

                updatePreview();

            }


            /* =====================================================
               PRODUCT INFORMATION
            ===================================================== */

            function updateProductInformation(
                resetEstimatedPrice = false
            ) {

                const selectedOption =
                    productSelect.options[
                        productSelect.selectedIndex
                    ];


                if (
                    !selectedOption ||
                    !selectedOption.value
                ) {

                    productCode.textContent = '-';
                    productType.textContent = '-';
                    productUnit.textContent = '-';
                    productPrice.textContent = '-';

                    previewProductName.textContent = '-';
                    previewProductCode.textContent = '-';
                    previewType.textContent = '-';
                    previewUnit.textContent = '-';
                    previewMasterPrice.textContent = '-';

                    updatePreview();

                    return;

                }


                const code =
                    selectedOption.dataset.code || '-';

                const name =
                    selectedOption.dataset.name || '-';

                const rawPrice =
                    selectedOption.dataset.price || '';

                const type =
                    selectedOption.dataset.type || '-';

                const unit =
                    selectedOption.dataset.unit || '-';


                /*
                 * Harga Product berasal dari PostgreSQL.
                 *
                 * Gunakan cleanProductPrice()
                 * agar:
                 *
                 * 23000.00
                 * →
                 * 23000
                 */

                const numericPrice =
                    cleanProductPrice(
                        rawPrice
                    );


                /* Product Reference */

                productCode.textContent =
                    code;

                productType.textContent =
                    type;

                productUnit.textContent =
                    unit;


                /* Preview Product */

                previewProductName.textContent =
                    name;

                previewProductCode.textContent =
                    code;

                previewType.textContent =
                    type;

                previewUnit.textContent =
                    unit;


                /*
                 * Master Price
                 */

                if (
                    rawPrice !== '' &&
                    numericPrice >= 0
                ) {

                    productPrice.textContent =
                        formatCurrency(
                            numericPrice
                        );

                    previewMasterPrice.textContent =
                        formatCurrency(
                            numericPrice
                        );


                    /*
                     * HANYA reset Estimated Price
                     * jika Product benar-benar diganti.
                     *
                     * Initial page load menggunakan false.
                     */

                    if (
                        resetEstimatedPrice
                    ) {

                        estimatedPriceInput.value =
                            formatCurrencyInput(
                                numericPrice
                            );

                    }

                } else {

                    productPrice.textContent =
                        '-';

                    previewMasterPrice.textContent =
                        '-';

                }


                updatePreview();

            }


            /* =====================================================
               PREVIEW
            ===================================================== */

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


                /*
                 * Quantity
                 */

                previewQuantity.textContent =
                    quantity;


                /*
                 * Estimated Price
                 */

                previewEstimatedPrice.textContent =
                    estimatedPrice > 0
                        ? formatCurrency(
                            estimatedPrice
                        )
                        : 'Rp 0';


                /*
                 * Subtotal
                 */

                previewSubtotal.textContent =
                    formatCurrency(
                        subtotal
                    );


                subtotalValue.textContent =
                    formatCurrency(
                        subtotal
                    );


                /*
                 * Notes
                 */

                const notes =
                    notesInput.value.trim();


                previewNotes.textContent =
                    notes
                        ? notes
                        : 'No notes added.';

            }


            /* =====================================================
               QUANTITY MINUS
            ===================================================== */

            document
                .getElementById(
                    'quantityMinus'
                )
                .addEventListener(
                    'click',
                    function() {

                        const current =
                            getQuantity();


                        quantityInput.value =
                            Math.max(
                                1,
                                current - 1
                            );


                        updatePreview();

                    }
                );


            /* =====================================================
               QUANTITY PLUS
            ===================================================== */

            document
                .getElementById(
                    'quantityPlus'
                )
                .addEventListener(
                    'click',
                    function() {

                        const current =
                            getQuantity();


                        quantityInput.value =
                            current + 1;


                        updatePreview();

                    }
                );


            /* =====================================================
               QUANTITY INPUT
            ===================================================== */

            quantityInput.addEventListener(
                'input',
                function() {

                    updatePreview();

                }
            );


            quantityInput.addEventListener(
                'change',
                function() {

                    updateQuantity();

                }
            );


            /* =====================================================
               PRODUCT CHANGE
            ===================================================== */

            productSelect.addEventListener(
                'change',
                function() {

                    /*
                     * true berarti Product benar-benar
                     * diganti oleh user.
                     *
                     * Estimated Price akan mengikuti
                     * Master Price Product baru.
                     */

                    updateProductInformation(
                        true
                    );

                }
            );


            /* =====================================================
               ESTIMATED PRICE INPUT
            ===================================================== */

            estimatedPriceInput.addEventListener(
                'input',
                function() {

                    /*
                     * Ambil nilai yang sedang diketik.
                     */

                    const currentValue =
                        estimatedPriceInput.value;


                    /*
                     * Format otomatis:
                     *
                     * 23000
                     * →
                     * 23.000
                     *
                     * 2300000
                     * →
                     * 2.300.000
                     */

                    estimatedPriceInput.value =
                        formatCurrencyInput(
                            currentValue
                        );


                    updatePreview();

                }
            );


            /* =====================================================
               NOTES
            ===================================================== */

            notesInput.addEventListener(
                'input',
                function() {

                    updatePreview();

                }
            );


            /* =====================================================
               FORM SUBMIT
            ===================================================== */

            form.addEventListener(
                'submit',
                function() {

                    /*
                     * Tampilan:
                     *
                     * 23.000
                     *
                     * Backend:
                     *
                     * 23000
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
               INITIAL LOAD
            ===================================================== */

            /*
             * IMPORTANT:
             *
             * false berarti Estimated Price existing
             * TIDAK diganti dengan Master Price.
             *
             * Contoh:
             *
             * Master Price    = Rp 25.000
             * Estimated Price  = Rp 23.000
             *
             * Saat Edit dibuka:
             *
             * Estimated Price tetap Rp 23.000.
             */

            updateProductInformation(
                false
            );


            /*
             * Format Estimated Price existing.
             *
             * Database:
             *
             * 23000.00
             *
             * cleanEstimatedValue()
             *
             * →
             *
             * 23000
             *
             * →
             *
             * 23.000
             */

            if (
                estimatedPriceInput.value
            ) {

                estimatedPriceInput.value =
                    formatCurrencyInput(
                        estimatedPriceInput.value
                    );

            }


            /*
             * Refresh preview setelah
             * Estimated Price selesai diformat.
             */

            updatePreview();

        }
    );

</script>

@endsection
