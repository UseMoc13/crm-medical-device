@extends('layouts.app')

@section('title', 'Create Product')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Create Product</h1>
        <p>Register a new medical device product and configure its details.</p>
    </div>

    <div class="actions">
        <a href="{{ route('products.index') }}" class="btn">
            ← Back to Products
        </a>
    </div>
</div>

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="alert danger">
        <strong>Please correct the following errors:</strong>
        <ul class="validation-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- CREATE PRODUCT FORM --}}
<div class="card product-create-card">
    <div class="card-head">
        <div>
            <h3>Product Information</h3>
            <p>Enter the product identity, classification, commercial details, and specification.</p>
        </div>

        <span class="required-note">
            <span class="required-mark">*</span>
            Required fields
        </span>
    </div>

    <div class="card-body product-create-body">
        <form
            action="{{ route('products.store') }}"
            method="POST"
            id="productCreateForm"
        >
            @csrf

            <div class="product-form-layout">

                {{-- LEFT: PRODUCT FORM --}}
                <div class="product-form-main">

                    {{-- SECTION 1: PRODUCT IDENTITY --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <h4>Product Identity</h4>
                            <p>Basic information used to identify the product.</p>
                        </div>

                        <div class="product-form-grid">

                            {{-- PRODUCT CODE --}}
                            <div class="form-group">
                                <label for="product_code">
                                    Product Code
                                </label>

                                <div class="auto-number-field">
                                    <input
                                        type="text"
                                        id="product_code"
                                        class="form-control"
                                        value="Generated automatically"
                                        readonly
                                    >

                                    <span class="auto-number-badge">
                                        Auto
                                    </span>
                                </div>

                                <small class="form-hint">
                                    Product code will be generated automatically by the system when saved.
                                </small>
                            </div>

                            {{-- PRODUCT NAME --}}
                            <div class="form-group">
                                <label for="product_name">
                                    Product Name
                                    <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="product_name"
                                    id="product_name"
                                    class="form-control @error('product_name') is-invalid @enderror"
                                    value="{{ old('product_name') }}"
                                    placeholder="e.g. Patient Monitor"
                                    maxlength="255"
                                    required
                                >

                                @error('product_name')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 2: PRODUCT CLASSIFICATION --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <h4>Product Classification</h4>
                            <p>Specify the product category, brand, type, and unit.</p>
                        </div>

                        <div class="product-form-grid">

                            {{-- CATEGORY --}}
                            <div class="form-group">
                                <label for="category_id">
                                    Category
                                    <span class="required-mark">*</span>
                                </label>

                                <select
                                    name="category_id"
                                    id="category_id"
                                    class="form-control @error('category_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Select Category --</option>

                                    @foreach ($categories as $category)
                                        <option
                                            value="{{ $category->category_id }}"
                                            @selected((string) old('category_id') === (string) $category->category_id)
                                        >
                                            {{ $category->category_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('category_id')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror

                                @if ($categories->isEmpty())
                                    <small class="field-error">
                                        No categories available. Please create a category first.
                                    </small>
                                @endif
                            </div>

                            {{-- BRAND --}}
                            <div class="form-group">
                                <label for="brand_id">
                                    Brand
                                    <span class="required-mark">*</span>
                                </label>

                                <select
                                    name="brand_id"
                                    id="brand_id"
                                    class="form-control @error('brand_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">-- Select Brand --</option>

                                    @foreach ($brands as $brand)
                                        <option
                                            value="{{ $brand->brand_id }}"
                                            @selected((string) old('brand_id') === (string) $brand->brand_id)
                                        >
                                            {{ $brand->brand_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('brand_id')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror

                                @if ($brands->isEmpty())
                                    <small class="field-error">
                                        No brands available. Please create a brand first.
                                    </small>
                                @endif
                            </div>

                            {{-- PRODUCT TYPE --}}
                            <div class="form-group">
                                <label for="product_type">
                                    Product Type
                                    <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="product_type"
                                    id="product_type"
                                    class="form-control @error('product_type') is-invalid @enderror"
                                    value="{{ old('product_type') }}"
                                    placeholder="e.g. Medical Equipment"
                                    maxlength="100"
                                    required
                                >

                                @error('product_type')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- UNIT --}}
                            <div class="form-group">
                                <label for="unit">
                                    Unit
                                    <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="unit"
                                    id="unit"
                                    class="form-control @error('unit') is-invalid @enderror"
                                    value="{{ old('unit') }}"
                                    placeholder="e.g. Unit"
                                    maxlength="50"
                                    required
                                >

                                @error('unit')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 3: COMMERCIAL INFORMATION --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <h4>Commercial Information</h4>
                            <p>Configure the product price, warranty period, and availability status.</p>
                        </div>

                        <div class="product-form-grid">

                            {{-- PRICE --}}
                            <div class="form-group">
                                <label for="price">
                                    Price
                                </label>

                                <div class="stepper-input currency-input">
                                    <span class="currency-prefix">Rp</span>

                                    <input
                                        type="text"
                                        name="price"
                                        id="price"
                                        class="form-control @error('price') is-invalid @enderror"
                                        value="{{ old('price', '0') }}"
                                        placeholder="0"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >

                                    <div class="stepper-buttons">
                                        <button
                                            type="button"
                                            class="number-stepper"
                                            id="priceMinus"
                                            aria-label="Decrease price"
                                        >−</button>

                                        <button
                                            type="button"
                                            class="number-stepper"
                                            id="pricePlus"
                                            aria-label="Increase price"
                                        >+</button>
                                    </div>
                                </div>

                                <small class="form-hint">
                                    Base selling price in Indonesian Rupiah. Step adjustment: Rp 1,000.
                                </small>

                                @error('price')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- WARRANTY --}}
                            <div class="form-group">
                                <label for="warranty_period">
                                    Warranty Period
                                </label>

                                <div class="stepper-input warranty-input">
                                    <input
                                        type="number"
                                        name="warranty_period"
                                        id="warranty_period"
                                        class="form-control @error('warranty_period') is-invalid @enderror"
                                        value="{{ old('warranty_period', 0) }}"
                                        min="0"
                                        step="1"
                                    >

                                    <span class="warranty-suffix">Months</span>

                                    <div class="stepper-buttons">
                                        <button
                                            type="button"
                                            class="number-stepper"
                                            id="warrantyMinus"
                                            aria-label="Decrease warranty"
                                        >−</button>

                                        <button
                                            type="button"
                                            class="number-stepper"
                                            id="warrantyPlus"
                                            aria-label="Increase warranty"
                                        >+</button>
                                    </div>
                                </div>

                                <small class="form-hint">
                                    Product warranty duration in months.
                                </small>

                                @error('warranty_period')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- STATUS --}}
                            <div class="form-group">
                                <label for="status">
                                    Status
                                    <span class="required-mark">*</span>
                                </label>

                                <select
                                    name="status"
                                    id="status"
                                    class="form-control @error('status') is-invalid @enderror"
                                    required
                                >
                                    <option
                                        value="active"
                                        @selected(old('status', 'active') === 'active')
                                    >
                                        Active
                                    </option>

                                    <option
                                        value="inactive"
                                        @selected(old('status') === 'inactive')
                                    >
                                        Inactive
                                    </option>
                                </select>

                                @error('status')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 4: PRODUCT SPECIFICATION --}}
                    <div class="form-section">
                        <div class="section-heading">
                            <h4>Product Specification</h4>
                            <p>Provide technical specifications and additional product information.</p>
                        </div>

                        <div class="product-form-grid">
                            <div class="form-group full-width">
                                <label for="specification">
                                    Specification
                                </label>

                                <textarea
                                    name="specification"
                                    id="specification"
                                    rows="5"
                                    class="form-control @error('specification') is-invalid @enderror"
                                    placeholder="Enter technical specifications, features, dimensions, or other product information..."
                                >{{ old('specification') }}</textarea>

                                <small class="form-hint">
                                    Optional technical specification or additional product details.
                                </small>

                                @error('specification')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- FORM ACTIONS --}}
                    <div class="form-actions">
                        <a href="{{ route('products.index') }}" class="btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            id="createProductButton"
                            @disabled($categories->isEmpty() || $brands->isEmpty())
                        >
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
                            <span id="previewProductCode">Generated after saving</span>
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
                        <strong id="previewStatus" class="preview-status active">
                            Active
                        </strong>
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
                            The product code will be assigned by the system after saving.
                        </p>
                    </div>

                </aside>

            </div>
        </form>
    </div>
</div>

{{-- PAGE STYLES --}}
<style>
    /* VALIDATION */
    .validation-list {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .validation-list li {
        margin-bottom: 4px;
    }

    /* FORM LAYOUT */
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

    .product-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .product-form-grid .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        margin-bottom: 4px;
    }

    .product-form-grid .full-width {
        grid-column: 1 / -1;
    }

    /* SECTION HEADINGS */
    .form-section + .form-section {
        margin-top: 30px;
        padding-top: 26px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .section-heading {
        margin-bottom: 20px;
    }

    .section-heading h4 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }

    .section-heading p {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    /* LABELS */
    .product-form-grid label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary, #334155);
    }

    .required-mark {
        color: #dc2626;
        font-weight: 600;
    }

    .required-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted, #64748b);
        white-space: nowrap;
    }

    /* INPUTS */
    .product-form-grid .form-control {
        box-sizing: border-box;
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #ffffff);
        color: var(--text-primary, #1e293b);
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
    }

    .product-form-grid textarea.form-control {
        min-height: auto;
        line-height: 1.6;
        resize: vertical;
    }

    .product-form-grid .form-control:focus {
        outline: none;
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    .product-form-grid .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
    }

    .product-form-grid .form-control.is-invalid {
        border-color: #dc2626;
    }

    /* AUTOMATIC PRODUCT CODE */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-badge {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        padding: 4px 7px;
        border-radius: 5px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* HELP TEXT AND ERRORS */
    .form-hint {
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    .field-error {
        color: #dc2626;
        font-size: 12px;
        line-height: 1.5;
    }

    /* PRICE AND WARRANTY STEPPERS */
    .stepper-input {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        min-height: 42px;
        box-sizing: border-box;
        padding: 0 8px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #ffffff);
    }

    .stepper-input:focus-within {
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
    }

    .stepper-input input {
        flex: 1;
        min-width: 0;
        width: 100%;
        height: 38px;
        padding: 0;
        border: 0 !important;
        outline: none;
        box-shadow: none !important;
        background: transparent;
        color: var(--text-primary, #1e293b);
        font-family: inherit;
        font-size: 13px;
    }

    .stepper-input input:focus {
        outline: none;
        border: 0;
    }

    .currency-prefix,
    .warranty-suffix {
        flex-shrink: 0;
        color: var(--text-muted, #64748b);
        font-size: 11px;
        font-weight: 600;
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
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 6px;
        background: var(--surface, #fff);
        color: var(--text-primary, #1e293b);
        font-size: 16px;
        cursor: pointer;
    }

    .number-stepper:hover {
        border-color: var(--primary, #2563eb);
        background: var(--surface-secondary, #f8fafc);
    }

    .number-stepper:active {
        transform: scale(.96);
    }

    .warranty-input input {
        appearance: textfield;
        -moz-appearance: textfield;
    }

    .warranty-input input::-webkit-inner-spin-button,
    .warranty-input input::-webkit-outer-spin-button {
        margin: 0;
        -webkit-appearance: none;
    }

    /* FORM ACTIONS */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .form-actions button:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    /* PRODUCT PREVIEW */
    .product-preview-panel {
        position: sticky;
        top: 92px;
        align-self: start;
        min-width: 0;
        height: fit-content;
        padding: 24px;
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 10px;
        background: var(--surface-secondary, #f8fafc);
        box-sizing: border-box;
        z-index: 2;
    }

    .preview-label {
        margin-bottom: 20px;
        color: var(--text-muted, #64748b);
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
        border-radius: 10px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 17px;
        font-weight: 700;
    }

    .preview-product-main {
        min-width: 0;
    }

    .preview-product-main h3 {
        overflow: hidden;
        margin: 0 0 4px;
        color: var(--text-primary, #1e293b);
        font-size: 16px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-product-main > span {
        display: block;
        overflow: hidden;
        color: var(--text-muted, #64748b);
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-divider {
        height: 1px;
        margin: 21px 0;
        background: var(--border, #e2e8f0);
    }

    .preview-detail,
    .preview-financial-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 9px 0;
    }

    .preview-detail > span,
    .preview-financial-row > span {
        flex-shrink: 0;
        color: var(--text-muted, #64748b);
        font-size: 11px;
    }

    .preview-detail > strong {
        max-width: 65%;
        overflow-wrap: anywhere;
        color: var(--text-primary, #334155);
        font-size: 12px;
        font-weight: 600;
        text-align: right;
    }

    .preview-financial-row > strong {
        max-width: 65%;
        color: var(--text-primary, #1e293b);
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
        background: #dcfce7;
        color: #15803d !important;
    }

    .preview-status.inactive {
        background: #f1f5f9;
        color: #64748b !important;
    }

    .preview-specification-title {
        margin-bottom: 8px;
        color: var(--text-primary, #334155);
        font-size: 11px;
        font-weight: 700;
    }

    .preview-specification p {
        margin: 0;
        color: var(--text-muted, #64748b);
        font-size: 11px;
        line-height: 1.6;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .preview-note {
        margin-top: 20px;
        padding: 13px 14px;
        border-radius: 7px;
        background: var(--surface, #fff);
        border: 1px solid var(--border, #e2e8f0);
    }

    .preview-note strong {
        display: block;
        margin-bottom: 5px;
        color: var(--text-primary, #334155);
        font-size: 11px;
    }

    .preview-note p {
        margin: 0;
        color: var(--text-muted, #64748b);
        font-size: 10px;
        line-height: 1.6;
    }

    /* RESPONSIVE */
    @media (max-width: 900px) {
        .product-form-layout {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-preview-panel {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 768px) {
        .product-form-grid {
            grid-template-columns: 1fr;
            gap: 18px;
        }

        .product-form-grid .full-width {
            grid-column: auto;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-head .actions {
            width: 100%;
        }

        .required-note {
            margin-top: 8px;
        }
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
        }

        .product-preview-panel {
            padding: 18px;
        }
    }
</style>

{{-- PAGE SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('productCreateForm');

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
    const previewCategory = document.getElementById('previewCategory');
    const previewBrand = document.getElementById('previewBrand');
    const previewProductType = document.getElementById('previewProductType');
    const previewUnit = document.getElementById('previewUnit');
    const previewPrice = document.getElementById('previewPrice');
    const previewWarranty = document.getElementById('previewWarranty');
    const previewStatus = document.getElementById('previewStatus');
    const previewSpecification = document.getElementById('previewSpecification');

    const PRICE_STEP = 1000;

    function getPriceValue(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        const parsed = Number(digits || 0);

        return Number.isSafeInteger(parsed)
            ? parsed
            : Number.MAX_SAFE_INTEGER;
    }

    function formatPrice(value) {
        return new Intl.NumberFormat('id-ID', {
            maximumFractionDigits: 0
        }).format(value);
    }

    function getWarrantyValue() {
        const parsed = Number.parseInt(warranty.value, 10);

        if (!Number.isFinite(parsed) || parsed < 0) {
            return 0;
        }

        return Math.min(parsed, 1000000);
    }

    function getSelectedText(element, fallback) {
        if (!element || element.selectedIndex < 0) {
            return fallback;
        }

        return element.value
            ? element.options[element.selectedIndex].text.trim()
            : fallback;
    }

    function updatePreview() {
        previewProductName.textContent =
            productName.value.trim() || 'New Product';

        previewCategory.textContent =
            getSelectedText(category, 'No Category');

        previewBrand.textContent =
            getSelectedText(brand, 'No Brand');

        previewProductType.textContent =
            productType.value.trim() || '-';

        previewUnit.textContent =
            unit.value.trim() || '-';

        previewPrice.textContent =
            'Rp ' + formatPrice(getPriceValue(price.value));

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

    function setPrice(value) {
        const safeValue = Math.max(
            0,
            Math.min(Number.MAX_SAFE_INTEGER, Math.trunc(value))
        );

        price.value = formatPrice(safeValue);
        updatePreview();
    }

    function setWarranty(value) {
        const safeValue = Math.max(
            0,
            Math.min(1000000, Math.trunc(value))
        );

        warranty.value = String(safeValue);
        updatePreview();
    }

    // PRICE INPUT
    price.addEventListener('input', function () {
        const numericValue = getPriceValue(price.value);
        price.value = formatPrice(numericValue);
        updatePreview();
    });

    priceMinus.addEventListener('click', function () {
        setPrice(Math.max(0, getPriceValue(price.value) - PRICE_STEP));
    });

    pricePlus.addEventListener('click', function () {
        setPrice(getPriceValue(price.value) + PRICE_STEP);
    });

    // WARRANTY INPUT
    warrantyMinus.addEventListener('click', function () {
        setWarranty(getWarrantyValue() - 1);
        warranty.focus();
    });

    warrantyPlus.addEventListener('click', function () {
        setWarranty(getWarrantyValue() + 1);
        warranty.focus();
    });

    warranty.addEventListener('input', function () {
        if (warranty.value !== '' && Number(warranty.value) < 0) {
            warranty.value = '0';
        }

        updatePreview();
    });

    warranty.addEventListener('change', function () {
        setWarranty(getWarrantyValue());
    });

    // LIVE PREVIEW
    [
        productName,
        category,
        brand,
        productType,
        unit,
        status,
        specification
    ].forEach(function (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    // NORMALIZE VALUES BEFORE SUBMIT
    form.addEventListener('submit', function () {
        price.value = String(getPriceValue(price.value));
        warranty.value = String(getWarrantyValue());
    });

    // INITIALIZE FORM VALUES
    setPrice(getPriceValue(price.value));
    setWarranty(getWarrantyValue());
    updatePreview();
});
</script>

@endsection
