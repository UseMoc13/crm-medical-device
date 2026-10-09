@extends('layouts.app')

@section('title', 'Create Product')

@section('content')

<div class="page-head">
    <div>
        <h1>Create Product</h1>
        <p>Create a new product and configure its category, brand, pricing, and warranty.</p>
    </div>

    <div class="actions">
        <a href="{{ route('products.index') }}" class="btn">
            ← Back to Products
        </a>
    </div>
</div>

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

<div class="card product-create-card">
    <div class="card-head">
        <div>
            <h3>Product Information</h3>
            <p>Enter the product information, commercial details, and technical specification.</p>
        </div>
    </div>

    <div class="card-body product-create-body">
        <form
            method="POST"
            action="{{ route('products.store') }}"
            id="productCreateForm">
            @csrf

            <div class="product-form-layout">

                {{-- LEFT: PRODUCT FORM --}}
                <div class="product-form-main">

                    {{-- PRODUCT IDENTITY --}}
                    <div class="section-divider first-section">
                        <div>
                            <h4>Product Identity</h4>
                            <p>Enter the basic information used to identify this product.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="product_code">
                            Product Code <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="product_code"
                            name="product_code"
                            value="{{ old('product_code') }}"
                            placeholder="e.g. PROD-001"
                            maxlength="100"
                            autocomplete="off"
                            required>

                        <span class="form-hint">Unique code used to identify this product.</span>

                        @error('product_code')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="product_name">
                            Product Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="product_name"
                            name="product_name"
                            value="{{ old('product_name') }}"
                            placeholder="e.g. Patient Monitor"
                            maxlength="255"
                            required>

                        <span class="form-hint">Name of the medical device product.</span>

                        @error('product_name')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="product-two-column">

                        {{-- CATEGORY --}}
                        <div class="form-group">
                            <label for="category_id">
                                Category <span class="required">*</span>
                            </label>

                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>

                                @foreach($categories as $category)
                                <option
                                    value="{{ $category->category_id }}"
                                    @selected((string) old('category_id')===(string) $category->category_id)
                                    >
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="form-hint">Product category classification.</span>

                            @error('category_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- BRAND --}}
                        <div class="form-group">
                            <label for="brand_id">
                                Brand <span class="required">*</span>
                            </label>

                            <select id="brand_id" name="brand_id" required>
                                <option value="">Select Brand</option>

                                @foreach($brands as $brand)
                                <option
                                    value="{{ $brand->brand_id }}"
                                    @selected((string) old('brand_id')===(string) $brand->brand_id)
                                    >
                                    {{ $brand->brand_name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="form-hint">Manufacturer or product brand.</span>

                            @error('brand_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="product-two-column">

                        {{-- PRODUCT TYPE --}}
                        <div class="form-group">
                            <label for="product_type">
                                Product Type <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="product_type"
                                name="product_type"
                                value="{{ old('product_type') }}"
                                placeholder="e.g. Medical Equipment"
                                maxlength="100"
                                required>

                            <span class="form-hint">Type or classification of the product.</span>

                            @error('product_type')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- UNIT --}}
                        <div class="form-group">
                            <label for="unit">
                                Unit <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="unit"
                                name="unit"
                                value="{{ old('unit') }}"
                                placeholder="e.g. Unit"
                                maxlength="50"
                                required>

                            <span class="form-hint">Selling or inventory unit.</span>

                            @error('unit')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider">
                        <div>
                            <h4>Commercial Information</h4>
                            <p>Configure product pricing, warranty, and availability.</p>
                        </div>
                    </div>

                    <div class="product-two-column">

                        <div class="form-group">
                            <label for="price">Price</label>

                            <div class="stepper-input currency-input">
                                <span class="currency-prefix" aria-hidden="true">Rp</span>

                                <input
                                    type="text"
                                    id="price"
                                    name="price"
                                    value="{{ old('price', '0') }}"
                                    placeholder="0"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    spellcheck="false"
                                    aria-describedby="priceHint">

                                <div class="stepper-buttons">
                                    <button
                                        type="button"
                                        class="number-stepper"
                                        id="priceMinus"
                                        aria-label="Kurangi harga Rp 1.000">−</button>

                                    <button
                                        type="button"
                                        class="number-stepper"
                                        id="pricePlus"
                                        aria-label="Tambah harga Rp 1.000">+</button>
                                </div>
                            </div>

                            <span class="form-hint" id="priceHint">
                                Base selling price in Indonesian Rupiah. Use − and + to adjust by Rp 1,000.
                            </span>

                            @error('price')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="warranty_period">Warranty Period</label>

                            <div class="stepper-input warranty-input">
                                <input
                                    type="number"
                                    id="warranty_period"
                                    name="warranty_period"
                                    value="{{ old('warranty_period', 0) }}"
                                    placeholder="0"
                                    min="0"
                                    step="1"
                                    inputmode="numeric"
                                    aria-describedby="warrantyHint">

                                <span class="warranty-suffix">Months</span>

                                <div class="stepper-buttons">
                                    <button
                                        type="button"
                                        class="number-stepper"
                                        id="warrantyMinus"
                                        aria-label="Kurangi garansi satu bulan">−</button>

                                    <button
                                        type="button"
                                        class="number-stepper"
                                        id="warrantyPlus"
                                        aria-label="Tambah garansi satu bulan">+</button>
                                </div>
                            </div>

                            <span class="form-hint" id="warrantyHint">
                                Product warranty duration in months.
                            </span>

                            @error('warranty_period')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="form-group">
                        <label for="status">
                            Status <span class="required">*</span>
                        </label>

                        <select id="status" name="status" required>
                            <option
                                value="active"
                                @selected(old('status', 'active' )==='active' )>Active</option>

                            <option
                                value="inactive"
                                @selected(old('status')==='inactive' )>Inactive</option>
                        </select>

                        <span class="form-hint">Current availability status of the product.</span>

                        @error('status')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- PRODUCT SPECIFICATION --}}
                    <div class="section-divider">
                        <div>
                            <h4>Product Specification</h4>
                            <p>Enter technical specifications and additional product information.</p>
                        </div>
                    </div>

                    <div class="form-group product-specification">
                        <label for="specification">Specification</label>

                        <textarea
                            id="specification"
                            name="specification"
                            rows="5"
                            placeholder="Enter technical specifications, features, dimensions, or other product information...">{{ old('specification') }}</textarea>

                        <span class="form-hint">
                            Optional technical specification or additional product details.
                        </span>

                        @error('specification')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- FORM ACTIONS --}}
                    <div class="form-actions">
                        <a href="{{ route('products.index') }}" class="btn">
                            Cancel
                        </a>

                        <button type="submit" class="btn primary" id="createProductButton">
                            Create Product
                        </button>
                    </div>
                </div>

                {{-- RIGHT: PRODUCT PREVIEW --}}
                <aside class="product-preview-panel">
                    <div class="preview-label">PRODUCT PREVIEW</div>

                    <div class="preview-product-head">
                        <div class="preview-product-icon">P</div>

                        <div class="preview-product-main">
                            <h3 id="previewProductName">New Product</h3>
                            <span id="previewProductCode">No Product Code</span>
                        </div>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-detail">
                        <span>Category</span>
                        <strong id="previewCategory">No Category</strong>
                    </div>

                    <div class="preview-detail">
                        <span>Brand</span>
                        <strong id="previewBrand">No Brand</strong>
                    </div>

                    <div class="preview-detail">
                        <span>Product Type</span>
                        <strong id="previewProductType">-</strong>
                    </div>

                    <div class="preview-detail">
                        <span>Unit</span>
                        <strong id="previewUnit">-</strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-financial-row">
                        <span>Price</span>
                        <strong id="previewPrice">Rp 0</strong>
                    </div>

                    <div class="preview-financial-row">
                        <span>Warranty</span>
                        <strong id="previewWarranty">0 Months</strong>
                    </div>

                    <div class="preview-detail preview-status-detail">
                        <span>Status</span>
                        <strong id="previewStatus" class="preview-status active">Active</strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-specification">
                        <div class="preview-specification-title">Specification</div>
                        <p id="previewSpecification">No specification provided.</p>
                    </div>

                    <div class="preview-note">
                        <strong>Product Information</strong>
                        <p>
                            This preview updates automatically as you enter product information.
                            Product data can be edited later from the product detail page.
                        </p>
                    </div>
                </aside>

            </div>
        </form>
    </div>
</div>

<style>
    /* =========================================================
   PRODUCT CREATE LAYOUT
========================================================= */

    .product-create-card,
    .product-create-body,
    .product-form-layout,
    .product-form-main {
        overflow: visible !important;
    }

    .product-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(300px, .75fr);
        gap: 32px;
        align-items: start;
        width: 100%;
    }

    .product-form-main {
        min-width: 0;
    }

    /* =========================================================
   SECTION HEADINGS
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
   FORM FIELDS
========================================================= */

    .form-group {
        min-width: 0;
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
        transition: border-color .18s ease, box-shadow .18s ease;
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
        outline: none;
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #a0a8b6;
    }

    .form-hint {
        display: block;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.5;
    }

    .form-error {
        display: block;
        margin-top: 6px;
        color: #c94a4a;
        font-size: 11px;
        line-height: 1.5;
    }

    .product-two-column {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
    }

    .stepper-input {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        height: 42px;
        box-sizing: border-box;
        padding: 0 8px;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
    }

    .stepper-input:focus-within {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
    }

    .stepper-input input {
        flex: 1;
        min-width: 0;
        height: 38px;
        padding: 0;
        border: 0;
        outline: none;
        box-shadow: none !important;
        background: transparent;
        color: #17284f;
        font-family: inherit;
        font-size: 13px;
    }

    .stepper-input input:focus {
        border: 0;
        outline: none;
    }

    .currency-prefix {
        flex-shrink: 0;
        color: #718096;
        font-size: 11px;
        font-weight: 700;
    }

    .stepper-buttons {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        gap: 4px;
    }

    .number-stepper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        padding: 0;
        border: 1px solid #dfe4eb;
        border-radius: 6px;
        background: #fff;
        color: #17284f;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
    }

    .number-stepper:hover {
        border-color: #2ba7a0;
        background: #f0faf7;
        color: #167d70;
    }

    .number-stepper:active {
        transform: scale(.96);
    }

    .warranty-input input {
        text-align: left;
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .warranty-input input::-webkit-inner-spin-button,
    .warranty-input input::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    .warranty-suffix {
        flex-shrink: 0;
        color: #718096;
        font-size: 11px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        padding-top: 16px;
        margin-top: 8px;
        border-top: 1px solid #edf0f5;
    }

    .product-preview-panel {
        position: sticky;
        top: 92px;
        align-self: start;
        min-width: 0;
        height: fit-content;
        padding: 24px;
        border: 1px solid #e6eaf0;
        border-radius: 12px;
        background: #fafbfd;
        box-sizing: border-box;
        z-index: 5;
    }

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
        flex: 0 0 48px;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
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
        overflow: hidden;
        margin: 0 0 4px;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-product-main>span {
        display: block;
        overflow: hidden;
        color: #7d8797;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-divider {
        height: 1px;
        margin: 21px 0;
        background: #e5e9ef;
    }

    .preview-detail,
    .preview-financial-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 9px 0;
    }

    .preview-detail>span,
    .preview-financial-row>span {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .preview-detail>strong {
        max-width: 65%;
        overflow-wrap: anywhere;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        text-align: right;
    }

    .preview-financial-row>strong {
        max-width: 65%;
        color: #17284f;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .preview-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px !important;
        font-weight: 700 !important;
    }

    .preview-status.active {
        background: #eaf7f3;
        color: #167d70 !important;
    }

    .preview-status.inactive {
        background: #f1f3f6;
        color: #7d8797 !important;
    }

    .preview-specification {
        margin-bottom: 20px;
    }

    .preview-specification-title {
        margin-bottom: 8px;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .preview-specification p {
        margin: 0;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6;
        white-space: pre-line;
        overflow-wrap: anywhere;
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

    @media (max-width: 900px) {
        .product-form-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-preview-panel {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 600px) {
        .product-two-column {
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
        }

        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
            text-align: center;
        }
    }

    @media (max-width: 450px) {
        .product-preview-panel {
            padding: 18px;
        }

        .preview-detail,
        .preview-financial-row {
            gap: 10px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const form = document.getElementById('productCreateForm');

        const productCode = document.getElementById('product_code');
        const productName = document.getElementById('product_name');
        const category = document.getElementById('category_id');
        const brand = document.getElementById('brand_id');
        const productType = document.getElementById('product_type');
        const unit = document.getElementById('unit');
        const price = document.getElementById('price');
        const warranty = document.getElementById('warranty_period');
        const status = document.getElementById('status');
        const specification = document.getElementById('specification');

        const priceMinus = document.getElementById('priceMinus');
        const pricePlus = document.getElementById('pricePlus');
        const warrantyMinus = document.getElementById('warrantyMinus');
        const warrantyPlus = document.getElementById('warrantyPlus');

        const previewProductName = document.getElementById('previewProductName');
        const previewProductCode = document.getElementById('previewProductCode');
        const previewCategory = document.getElementById('previewCategory');
        const previewBrand = document.getElementById('previewBrand');
        const previewProductType = document.getElementById('previewProductType');
        const previewUnit = document.getElementById('previewUnit');
        const previewPrice = document.getElementById('previewPrice');
        const previewWarranty = document.getElementById('previewWarranty');
        const previewStatus = document.getElementById('previewStatus');
        const previewSpecification = document.getElementById('previewSpecification');

        const rupiahFormatter = new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        });


        const PRICE_STEP = 1000;

        function getPriceValue(value) {
            const digits = String(value ?? '').replace(/\D/g, '');

            if (digits === '') {
                return 0;
            }

            const parsed = Number(digits);

            return Number.isSafeInteger(parsed) ?
                parsed :
                Number.MAX_SAFE_INTEGER;
        }

        function formatPriceInput(value) {
            return new Intl.NumberFormat('id-ID', {
                maximumFractionDigits: 0
            }).format(value);
        }

        function formatCurrency(value) {
            return 'Rp ' + formatPriceInput(getPriceValue(value));
        }

        function setPrice(value) {
            const safeValue = Math.max(
                0,
                Math.min(Number.MAX_SAFE_INTEGER, Math.trunc(value))
            );

            price.value = formatPriceInput(safeValue);
            updatePreview();
        }

        price.addEventListener('input', function() {
            const numericValue = getPriceValue(price.value);

            price.value = formatPriceInput(numericValue);
            updatePreview();
        });

        priceMinus.addEventListener('click', function() {
            const currentPrice = getPriceValue(price.value);
            setPrice(Math.max(0, currentPrice - PRICE_STEP));
        });

        pricePlus.addEventListener('click', function() {
            const currentPrice = getPriceValue(price.value);
            setPrice(currentPrice + PRICE_STEP);
        });

        function setWarranty(value) {
            const nextValue = Math.max(
                0,
                Math.min(1000000, Math.trunc(value))
            );

            warranty.value = String(nextValue);
            updatePreview();
        }

        function getWarrantyValue() {
            const parsed = Number.parseInt(warranty.value, 10);

            if (!Number.isFinite(parsed) || parsed < 0) {
                return 0;
            }

            return Math.min(parsed, 1000000);
        }

        warrantyMinus.addEventListener('click', function() {
            setWarranty(getWarrantyValue() - 1);
            warranty.focus();
        });

        warrantyPlus.addEventListener('click', function() {
            setWarranty(getWarrantyValue() + 1);
            warranty.focus();
        });

        warranty.addEventListener('input', function() {
            if (warranty.value !== '') {
                const parsed = Number.parseInt(warranty.value, 10);

                if (Number.isFinite(parsed) && parsed < 0) {
                    warranty.value = '0';
                }
            }

            updatePreview();
        });

        warranty.addEventListener('change', function() {
            setWarranty(getWarrantyValue());
        });

        function updatePreview() {
            previewProductName.textContent =
                productName.value.trim() || 'New Product';

            previewProductCode.textContent =
                productCode.value.trim() || 'No Product Code';

            previewCategory.textContent =
                getSelectedText(category, 'No Category');

            previewBrand.textContent =
                getSelectedText(brand, 'No Brand');

            previewProductType.textContent =
                productType.value.trim() || '-';

            previewUnit.textContent =
                unit.value.trim() || '-';

            previewPrice.textContent =
                formatCurrency(price.value);

            previewWarranty.textContent =
                getWarrantyValue() + ' Months';

            const statusValue = status.value || 'active';

            previewStatus.textContent =
                statusValue === 'inactive' ? 'Inactive' : 'Active';

            previewStatus.classList.toggle(
                'inactive',
                statusValue === 'inactive'
            );

            previewStatus.classList.toggle(
                'active',
                statusValue !== 'inactive'
            );

            previewSpecification.textContent =
                specification.value.trim() || 'No specification provided.';
        }

        [
            productCode,
            productName,
            category,
            brand,
            productType,
            unit,
            status,
            specification
        ].forEach(function(element) {
            element.addEventListener('input', updatePreview);
            element.addEventListener('change', updatePreview);
        });

        form.addEventListener('submit', function() {
            price.value = String(getPriceValue(price.value));
            warranty.value = String(getWarrantyValue());
        });

        normalizePriceField();

        if (warranty.value !== '') {
            setWarranty(getWarrantyValue());
        }

        updatePreview();
    });
</script>

@endsection