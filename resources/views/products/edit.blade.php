@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

@php

$initialPrice = old('price', $product->price ?? 0);

$initialPriceRaw = is_numeric($initialPrice)
? (string) $initialPrice
: '0';

$initialPriceDisplay = number_format(
(float) $initialPriceRaw,
0,
',',
'.'
);

$initialWarranty = old(
'warranty_period',
$product->warranty_period ?? 0
);

$initialStatus = old(
'status',
$product->status ?? 'active'
);

$initialCategoryId = (string) old(
'category_id',
$product->category_id ?? ''
);

$initialBrandId = (string) old(
'brand_id',
$product->brand_id ?? ''
);
@endphp

<div class="page-head">
    <div>
        <h1>Edit Product</h1>
        <p>
            Update product information, pricing, warranty, and technical specification.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('products.show', $product) }}" class="btn">
            ← Back to Product
        </a>
    </div>
</div>

{{-- =========================================================
     VALIDATION ERRORS
========================================================= --}}

@if ($errors->any())
<div class="alert error">
    <strong>Please check the following errors:</strong>

    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- =========================================================
     PRODUCT FORM CARD
========================================================= --}}

<div class="card product-edit-card">

    <div class="card-head">
        <div>
            <h3>Product Information</h3>
            <p>Update the product information and commercial details.</p>
        </div>
    </div>

    <div class="card-body product-edit-body">

        <form
            method="POST"
            action="{{ route('products.update', $product) }}"
            id="productEditForm">
            @csrf
            @method('PUT')

            <div class="product-form-layout">

                {{-- =================================================
                     LEFT: FORM
                ================================================== --}}

                <div class="product-form-main">

                    {{-- PRODUCT IDENTITY --}}

                    <div class="section-divider first-section">
                        <div>
                            <h4>Product Identity</h4>
                            <p>
                                Update the basic information used to identify this product.
                            </p>
                        </div>
                    </div>

                    {{-- Product Code --}}

                    <div class="form-group">
                        <label for="product_code">
                            Product Code <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="product_code"
                            name="product_code"
                            value="{{ old('product_code', $product->product_code) }}"
                            placeholder="e.g. PROD-001"
                            maxlength="100"
                            autocomplete="off"
                            required>

                        <span class="form-hint">
                            Unique code used to identify this product.
                        </span>

                        @error('product_code')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Product Name --}}

                    <div class="form-group">
                        <label for="product_name">
                            Product Name <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="product_name"
                            name="product_name"
                            value="{{ old('product_name', $product->product_name) }}"
                            placeholder="e.g. Patient Monitor"
                            maxlength="255"
                            required>

                        <span class="form-hint">
                            Name of the medical device product.
                        </span>

                        @error('product_name')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- CATEGORY AND BRAND --}}

                    <div class="product-two-column">

                        <div class="form-group">
                            <label for="category_id">
                                Category <span class="required">*</span>
                            </label>

                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>

                                @foreach ($categories as $category)
                                <option
                                    value="{{ $category->category_id }}"
                                    @selected($initialCategoryId===(string) $category->category_id)
                                    >
                                    {{ $category->category_name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="form-hint">
                                Product category classification.
                            </span>

                            @error('category_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="brand_id">
                                Brand <span class="required">*</span>
                            </label>

                            <select id="brand_id" name="brand_id" required>
                                <option value="">Select Brand</option>

                                @foreach ($brands as $brand)
                                <option
                                    value="{{ $brand->brand_id }}"
                                    @selected($initialBrandId===(string) $brand->brand_id)
                                    >
                                    {{ $brand->brand_name }}
                                </option>
                                @endforeach
                            </select>

                            <span class="form-hint">
                                Manufacturer or product brand.
                            </span>

                            @error('brand_id')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- PRODUCT TYPE AND UNIT --}}

                    <div class="product-two-column">

                        <div class="form-group">
                            <label for="product_type">
                                Product Type <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="product_type"
                                name="product_type"
                                value="{{ old('product_type', $product->product_type) }}"
                                placeholder="e.g. Medical Equipment"
                                maxlength="100"
                                required>

                            <span class="form-hint">
                                Type or classification of the product.
                            </span>

                            @error('product_type')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="unit">
                                Unit <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="unit"
                                name="unit"
                                value="{{ old('unit', $product->unit) }}"
                                placeholder="e.g. Unit"
                                maxlength="50"
                                required>

                            <span class="form-hint">
                                Selling or inventory unit.
                            </span>

                            @error('unit')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                    </div>

                    {{-- COMMERCIAL INFORMATION --}}

                    <div class="section-divider">
                        <div>
                            <h4>Commercial Information</h4>
                            <p>
                                Update product pricing, warranty, and availability.
                            </p>
                        </div>
                    </div>

                    <div class="product-two-column">

                        {{-- PRICE --}}

                        <div class="form-group">
                            <label for="price_display">Price</label>

                            <div class="input-with-prefix price-input-group">

                                <span class="input-prefix">Rp</span>

                                {{-- Visible formatted price --}}
                                <input
                                    type="text"
                                    id="price_display"
                                    value="{{ $initialPriceDisplay }}"
                                    placeholder="0"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    aria-describedby="priceHint">

                                {{-- Raw numeric price sent to Laravel --}}
                                <input
                                    type="hidden"
                                    id="price"
                                    name="price"
                                    value="{{ $initialPriceRaw }}">

                                <div class="stepper-buttons price-stepper">
                                    <button
                                        type="button"
                                        id="priceMinus"
                                        class="stepper-button"
                                        aria-label="Kurangi harga Rp 1.000"
                                        title="Kurangi Rp 1.000">−</button>

                                    <button
                                        type="button"
                                        id="pricePlus"
                                        class="stepper-button"
                                        aria-label="Tambah harga Rp 1.000"
                                        title="Tambah Rp 1.000">+</button>
                                </div>

                            </div>

                            <span class="form-hint" id="priceHint">
                                Use − or + to adjust the price by Rp 1.000.
                            </span>

                            @error('price')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- WARRANTY --}}

                        <div class="form-group">
                            <label for="warranty_period">Warranty Period</label>

                            <div class="input-with-suffix warranty-input-group">

                                <input
                                    type="number"
                                    id="warranty_period"
                                    name="warranty_period"
                                    value="{{ $initialWarranty }}"
                                    placeholder="0"
                                    min="0"
                                    max="1000000"
                                    step="1">

                                <span class="input-suffix">Months</span>

                                <div class="stepper-buttons warranty-stepper">
                                    <button
                                        type="button"
                                        id="warrantyMinus"
                                        class="stepper-button"
                                        aria-label="Kurangi garansi satu bulan"
                                        title="Kurangi satu bulan">−</button>

                                    <button
                                        type="button"
                                        id="warrantyPlus"
                                        class="stepper-button"
                                        aria-label="Tambah garansi satu bulan"
                                        title="Tambah satu bulan">+</button>
                                </div>

                            </div>

                            <span class="form-hint">
                                Use − or + to adjust the warranty period by one month.
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
                                @selected($initialStatus==='active' )>
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected($initialStatus==='inactive' )>
                                Inactive
                            </option>
                        </select>

                        <span class="form-hint">
                            Current availability status of the product.
                        </span>

                        @error('status')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- PRODUCT SPECIFICATION --}}

                    <div class="section-divider">
                        <div>
                            <h4>Product Specification</h4>
                            <p>
                                Update technical specification and additional product information.
                            </p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="specification">Specification</label>

                        <textarea
                            id="specification"
                            name="specification"
                            placeholder="Enter technical specifications, features, dimensions, or other product information...">{{ old('specification', $product->specification) }}</textarea>

                        <span class="form-hint">
                            Optional technical specification or additional product details.
                        </span>

                        @error('specification')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- FORM ACTIONS --}}

                    <div class="form-actions">
                        <a
                            href="{{ route('products.show', $product) }}"
                            class="btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            id="updateProductButton">
                            Update Product
                        </button>
                    </div>

                </div>

                {{-- =================================================
                     RIGHT: PRODUCT PREVIEW
                ================================================== --}}

                <aside class="product-preview-panel">

                    <div class="preview-label">PRODUCT PREVIEW</div>

                    <div class="preview-product-head">
                        <div class="preview-product-icon">P</div>

                        <div class="preview-product-main">
                            <h3 id="previewProductName">
                                {{ old('product_name', $product->product_name) ?: 'New Product' }}
                            </h3>

                            <span id="previewProductCode">
                                {{ old('product_code', $product->product_code) ?: 'No Product Code' }}
                            </span>
                        </div>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-detail">
                        <span>Category</span>
                        <strong id="previewCategory">
                            {{ $categories->firstWhere('category_id', $initialCategoryId)?->category_name ?? 'No Category' }}
                        </strong>
                    </div>

                    <div class="preview-detail">
                        <span>Brand</span>
                        <strong id="previewBrand">
                            {{ $brands->firstWhere('brand_id', $initialBrandId)?->brand_name ?? 'No Brand' }}
                        </strong>
                    </div>

                    <div class="preview-detail">
                        <span>Product Type</span>
                        <strong id="previewProductType">
                            {{ old('product_type', $product->product_type) ?: '-' }}
                        </strong>
                    </div>

                    <div class="preview-detail">
                        <span>Unit</span>
                        <strong id="previewUnit">
                            {{ old('unit', $product->unit) ?: '-' }}
                        </strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-financial-row">
                        <span>Price</span>
                        <strong id="previewPrice">Rp {{ $initialPriceDisplay }}</strong>
                    </div>

                    <div class="preview-financial-row">
                        <span>Warranty</span>
                        <strong id="previewWarranty">
                            {{ $initialWarranty !== '' ? $initialWarranty : 0 }} Months
                        </strong>
                    </div>

                    <div class="preview-detail preview-status-detail">
                        <span>Status</span>

                        <strong
                            id="previewStatus"
                            class="preview-status {{ $initialStatus === 'inactive' ? 'inactive' : 'active' }}">
                            {{ $initialStatus === 'inactive' ? 'Inactive' : 'Active' }}
                        </strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-specification">
                        <div class="preview-specification-title">
                            Specification
                        </div>

                        <p id="previewSpecification">{{ old('specification', $product->specification) ?: 'No specification provided.' }}</p>
                    </div>

                    <div class="preview-note">
                        <strong>Product Information</strong>
                        <p>
                            Changes are reflected in this preview automatically before the product is updated.
                        </p>
                    </div>

                </aside>

            </div>
        </form>

    </div>
</div>

<style>
    /* =========================================================
   FORM LAYOUT
========================================================= */

    .product-edit-card,
    .product-edit-body,
    .product-form-layout,
    .product-form-main {
        overflow: visible !important;
    }

    .product-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(320px, .75fr);
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
   FORM ELEMENTS
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

    .form-group input:not([type="hidden"]),
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

    .form-group input:not([type="hidden"]),
    .form-group select {
        height: 42px;
        padding: 0 12px;
    }

    .form-group textarea {
        display: block;
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
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
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

    /* =========================================================
   TWO-COLUMN FORM ROWS
========================================================= */

    .product-two-column {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
    }

    /* =========================================================
   PRICE AND WARRANTY INPUTS
========================================================= */

    .input-with-prefix,
    .input-with-suffix {
        position: relative;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .input-with-prefix input#price_display {
        padding-left: 44px;
        padding-right: 82px;
        font-variant-numeric: tabular-nums;
    }

    .input-prefix {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        color: #718096;
        font-size: 12px;
        font-weight: 600;
        pointer-events: none;
    }

    .warranty-input-group input#warranty_period {
        padding-right: 150px;
    }

    .input-suffix {
        position: absolute;
        top: 50%;
        right: 83px;
        transform: translateY(-50%);
        color: #718096;
        font-size: 11px;
        pointer-events: none;
    }

    /* =========================================================
   STEPPER BUTTONS
========================================================= */

    .stepper-buttons {
        position: absolute;
        top: 50%;
        right: 5px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 4px;
        transform: translateY(-50%);
    }

    .stepper-button {
        display: inline-flex;
        flex: 0 0 32px;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 30px;
        padding: 0;
        border: 1px solid #d9dee8;
        border-radius: 6px;
        background: #f7f9fc;
        color: #17284f;
        font-family: inherit;
        line-height: 1;
        cursor: pointer;
        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;
    }

    .stepper-button:hover {
        border-color: #2ba7a0;
        background: #eaf7f3;
        color: #167d70;
    }

    .stepper-button:active {
        background: #d9f0e9;
    }

    .stepper-button:focus-visible {
        outline: 2px solid #2ba7a0;
        outline-offset: 2px;
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
   PRODUCT PREVIEW
========================================================= */

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
        z-index: 2;
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
        margin: 0 0 4px;
        overflow: hidden;
        color: #17284f;
        font-size: 17px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-product-main span {
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

    .preview-detail {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        padding: 9px 0;
    }

    .preview-detail>span {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .preview-detail>strong {
        max-width: 65%;
        overflow: hidden;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-financial-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 9px 0;
    }

    .preview-financial-row span {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .preview-financial-row strong {
        color: #17284f;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .preview-status {
        display: inline-flex;
        justify-content: center;
        align-items: center;
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
        margin-top: 20px;
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
            box-sizing: border-box;
        }
    }

    @media (max-width: 450px) {
        .product-preview-panel {
            padding: 20px;
        }

        .preview-detail {
            flex-direction: column;
            gap: 4px;
        }

        .preview-detail>strong {
            max-width: 100%;
            text-align: left;
            white-space: normal;
        }

        .stepper-button {
            flex-basis: 29px;
            width: 29px;
            height: 29px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const form = document.getElementById('productEditForm');

        const productCode = document.getElementById('product_code');
        const productName = document.getElementById('product_name');
        const category = document.getElementById('category_id');
        const brand = document.getElementById('brand_id');
        const productType = document.getElementById('product_type');
        const unit = document.getElementById('unit');

        const priceDisplay = document.getElementById('price_display');
        const priceHidden = document.getElementById('price');
        const priceMinus = document.getElementById('priceMinus');
        const pricePlus = document.getElementById('pricePlus');

        const warranty = document.getElementById('warranty_period');
        const warrantyMinus = document.getElementById('warrantyMinus');
        const warrantyPlus = document.getElementById('warrantyPlus');

        const status = document.getElementById('status');
        const specification = document.getElementById('specification');

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

        if (!form || !priceDisplay || !priceHidden || !warranty) {
            console.error('Product edit form initialization failed.');
            return;
        }

        const PRICE_STEP = 1000;
        const MAX_PRICE = Number.MAX_SAFE_INTEGER;
        const MAX_WARRANTY = 1000000;

        const rupiahFormatter = new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        });

        function getPriceValue(value) {
            const digits = String(value ?? '').replace(/\D/g, '');

            if (digits === '') {
                return 0;
            }

            const parsed = Number(digits);

            if (!Number.isSafeInteger(parsed)) {
                return MAX_PRICE;
            }

            return parsed;
        }

        function formatPrice(value) {
            const amount = Math.max(
                0,
                Math.min(MAX_PRICE, Math.trunc(Number(value) || 0))
            );

            return rupiahFormatter.format(amount);
        }

        function formatCurrency(value) {
            return 'Rp ' + formatPrice(value);
        }

        function syncPriceValue() {
            const amount = getPriceValue(priceDisplay.value);

            priceHidden.value = String(amount);

            return amount;
        }

        function normalizePriceDisplay() {
            const amount = syncPriceValue();

            priceDisplay.value = formatPrice(amount);
        }

        function setPrice(value) {
            const current = Number(value);

            const nextPrice = Math.max(
                0,
                Math.min(
                    MAX_PRICE,
                    Math.trunc(Number.isFinite(current) ? current : 0)
                )
            );

            priceDisplay.value = formatPrice(nextPrice);
            priceHidden.value = String(nextPrice);

            updatePreview();
        }

        function getWarrantyValue() {
            const parsed = Number.parseInt(warranty.value, 10);

            if (!Number.isFinite(parsed) || parsed < 0) {
                return 0;
            }

            return Math.min(parsed, MAX_WARRANTY);
        }

        function setWarranty(value) {
            const parsed = Number(value);

            const nextWarranty = Math.max(
                0,
                Math.min(
                    MAX_WARRANTY,
                    Math.trunc(Number.isFinite(parsed) ? parsed : 0)
                )
            );

            warranty.value = String(nextWarranty);

            updatePreview();
        }

        function normalizeWarranty() {
            warranty.value = String(getWarrantyValue());
        }

        function getSelectedText(element, fallback) {
            if (
                !element ||
                element.selectedIndex < 0 ||
                element.value === ''
            ) {
                return fallback;
            }

            return element.options[element.selectedIndex].text.trim();
        }

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
                formatCurrency(getPriceValue(priceDisplay.value));

            previewWarranty.textContent =
                getWarrantyValue() + ' Months';

            const currentStatus = status.value || 'active';

            previewStatus.textContent =
                currentStatus === 'inactive' ? 'Inactive' : 'Active';

            previewStatus.classList.toggle(
                'inactive',
                currentStatus === 'inactive'
            );

            previewStatus.classList.toggle(
                'active',
                currentStatus !== 'inactive'
            );

            previewSpecification.textContent =
                specification.value.trim() || 'No specification provided.';
        }

        priceDisplay.addEventListener('input', function() {
            syncPriceValue();
            updatePreview();
        });

        priceDisplay.addEventListener('blur', function() {
            normalizePriceDisplay();
            updatePreview();
        });

        priceDisplay.addEventListener('focus', function() {
            priceDisplay.select();
        });

        priceMinus.addEventListener('click', function() {
            const currentPrice = getPriceValue(priceDisplay.value);

            setPrice(Math.max(0, currentPrice - PRICE_STEP));
        });

        pricePlus.addEventListener('click', function() {
            const currentPrice = getPriceValue(priceDisplay.value);

            setPrice(Math.min(MAX_PRICE, currentPrice + PRICE_STEP));
        });

        warranty.addEventListener('input', function() {
            if (warranty.value !== '') {
                const current = Number(warranty.value);

                if (Number.isFinite(current) && current < 0) {
                    warranty.value = '0';
                } else if (Number.isFinite(current) && current > MAX_WARRANTY) {
                    warranty.value = String(MAX_WARRANTY);
                }
            }

            updatePreview();
        });

        warranty.addEventListener('change', function() {
            normalizeWarranty();
            updatePreview();
        });

        warrantyMinus.addEventListener('click', function() {
            setWarranty(getWarrantyValue() - 1);
        });

        warrantyPlus.addEventListener('click', function() {
            setWarranty(getWarrantyValue() + 1);
        });

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

        form.addEventListener('submit', function(event) {
            const amount = getPriceValue(priceDisplay.value);

            if (!Number.isSafeInteger(amount) || amount < 0) {
                event.preventDefault();

                alert('Please enter a valid product price.');
                priceDisplay.focus();

                return;
            }

            priceHidden.value = String(amount);

            normalizeWarranty();

            priceDisplay.value = formatPrice(amount);
        });

        normalizePriceDisplay();
        normalizeWarranty();
        updatePreview();
    });
</script>

@endsection