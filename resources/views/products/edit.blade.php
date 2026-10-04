@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="page-head">

    <div>
        <h1>Edit Product</h1>

        <p>
            Update product information, pricing, warranty, and technical specification.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('products.show', $product) }}"
            class="btn"
        >
            ← Back to Product
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


<div class="card product-edit-card">

    <div class="card-head">

        <div>

            <h3>Product Information</h3>

            <p>
                Update the product information and commercial details.
            </p>

        </div>

    </div>


    <div class="card-body product-edit-body">

        <form
            method="POST"
            action="{{ route('products.update', $product) }}"
            id="productEditForm"
        >

            @csrf

            @method('PUT')


            <div class="product-form-layout">


                {{-- =================================================
                     LEFT : FORM
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

                            Product Code

                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            id="product_code"
                            name="product_code"
                            value="{{ old('product_code', $product->product_code) }}"
                            placeholder="e.g. PROD-001"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >

                        <span class="form-hint">
                            Unique code used to identify this product.
                        </span>

                        @error('product_code')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Product Name --}}

                    <div class="form-group">

                        <label for="product_name">

                            Product Name

                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            id="product_name"
                            name="product_name"
                            value="{{ old('product_name', $product->product_name) }}"
                            placeholder="e.g. Patient Monitor"
                            maxlength="255"
                            required
                        >

                        <span class="form-hint">
                            Name of the medical device product.
                        </span>

                        @error('product_name')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Category + Brand --}}

                    <div class="product-two-column">


                        {{-- Category --}}

                        <div class="form-group">

                            <label for="category_id">

                                Category

                                <span class="required">*</span>

                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                @foreach($categories as $category)

                                    <option
                                        value="{{ $category->category_id }}"
                                        @selected(
                                            old(
                                                'category_id',
                                                $product->category_id
                                            ) === $category->category_id
                                        )
                                    >
                                        {{ $category->category_name }}
                                    </option>

                                @endforeach

                            </select>

                            <span class="form-hint">
                                Product category classification.
                            </span>

                            @error('category_id')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Brand --}}

                        <div class="form-group">

                            <label for="brand_id">

                                Brand

                                <span class="required">*</span>

                            </label>

                            <select
                                id="brand_id"
                                name="brand_id"
                                required
                            >

                                <option value="">
                                    Select Brand
                                </option>

                                @foreach($brands as $brand)

                                    <option
                                        value="{{ $brand->brand_id }}"
                                        @selected(
                                            old(
                                                'brand_id',
                                                $product->brand_id
                                            ) === $brand->brand_id
                                        )
                                    >
                                        {{ $brand->brand_name }}
                                    </option>

                                @endforeach

                            </select>

                            <span class="form-hint">
                                Manufacturer or product brand.
                            </span>

                            @error('brand_id')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>

                    </div>


                    {{-- Product Type + Unit --}}

                    <div class="product-two-column">


                        {{-- Product Type --}}

                        <div class="form-group">

                            <label for="product_type">

                                Product Type

                                <span class="required">*</span>

                            </label>

                            <input
                                type="text"
                                id="product_type"
                                name="product_type"
                                value="{{ old('product_type', $product->product_type) }}"
                                placeholder="e.g. Medical Equipment"
                                maxlength="100"
                                required
                            >

                            <span class="form-hint">
                                Type or classification of the product.
                            </span>

                            @error('product_type')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Unit --}}

                        <div class="form-group">

                            <label for="unit">

                                Unit

                                <span class="required">*</span>

                            </label>

                            <input
                                type="text"
                                id="unit"
                                name="unit"
                                value="{{ old('unit', $product->unit) }}"
                                placeholder="e.g. Unit"
                                maxlength="50"
                                required
                            >

                            <span class="form-hint">
                                Selling or inventory unit.
                            </span>

                            @error('unit')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

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


                    {{-- Price + Warranty --}}

                    <div class="product-two-column">


                        {{-- Price --}}

                        <div class="form-group">

                            <label for="price">
                                Price
                            </label>

                            <div class="input-with-prefix">

                                <span class="input-prefix">
                                    Rp
                                </span>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    value="{{ old('price', $product->price ?? 0) }}"
                                    placeholder="0"
                                    min="0"
                                    step="0.01"
                                >

                            </div>

                            <span class="form-hint">
                                Base selling price of the product.
                            </span>

                            @error('price')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Warranty --}}

                        <div class="form-group">

                            <label for="warranty_period">
                                Warranty Period
                            </label>

                            <div class="input-with-suffix">

                                <input
                                    type="number"
                                    id="warranty_period"
                                    name="warranty_period"
                                    value="{{ old('warranty_period', $product->warranty_period ?? 0) }}"
                                    placeholder="0"
                                    min="0"
                                    step="1"
                                >

                                <span class="input-suffix">
                                    Months
                                </span>

                            </div>

                            <span class="form-hint">
                                Product warranty duration in months.
                            </span>

                            @error('warranty_period')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>

                    </div>


                    {{-- Status --}}

                    <div class="form-group">

                        <label for="status">

                            Status

                            <span class="required">*</span>

                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option
                                value="active"
                                @selected(
                                    old(
                                        'status',
                                        $product->status
                                    ) === 'active'
                                )
                            >
                                Active
                            </option>

                            <option
                                value="inactive"
                                @selected(
                                    old(
                                        'status',
                                        $product->status
                                    ) === 'inactive'
                                )
                            >
                                Inactive
                            </option>

                        </select>

                        <span class="form-hint">
                            Current availability status of the product.
                        </span>

                        @error('status')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- SPECIFICATION --}}

                    <div class="section-divider">

                        <div>

                            <h4>Product Specification</h4>

                            <p>
                                Update technical specification and additional product information.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="specification">
                            Specification
                        </label>

                        <textarea
                            id="specification"
                            name="specification"
                            placeholder="Enter technical specifications, features, dimensions, or other product information..."
                        >{{ old('specification', $product->specification) }}</textarea>

                        <span class="form-hint">
                            Optional technical specification or additional product details.
                        </span>

                        @error('specification')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- ACTIONS --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('products.show', $product) }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Update Product
                        </button>

                    </div>


                </div>


                {{-- =================================================
                     RIGHT : PREVIEW
                ================================================== --}}

                <aside class="product-preview-panel">

                    <div class="preview-label">
                        PRODUCT PREVIEW
                    </div>


                    <div class="preview-product-head">

                        <div class="preview-product-icon">
                            P
                        </div>


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

                        <span>
                            Category
                        </span>

                        <strong id="previewCategory">
                            {{ $product->category?->category_name ?? 'No Category' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Brand
                        </span>

                        <strong id="previewBrand">
                            {{ $product->brand?->brand_name ?? 'No Brand' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Product Type
                        </span>

                        <strong id="previewProductType">
                            {{ old('product_type', $product->product_type) ?: '-' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Unit
                        </span>

                        <strong id="previewUnit">
                            {{ old('unit', $product->unit) ?: '-' }}
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-financial-row">

                        <span>
                            Price
                        </span>

                        <strong id="previewPrice">
                            Rp {{ number_format((float) old('price', $product->price ?? 0), 2, ',', '.') }}
                        </strong>

                    </div>


                    <div class="preview-financial-row">

                        <span>
                            Warranty
                        </span>

                        <strong id="previewWarranty">
                            {{ old('warranty_period', $product->warranty_period ?? 0) }}
                            Months
                        </strong>

                    </div>


                    <div class="preview-detail preview-status-detail">

                        <span>
                            Status
                        </span>

                        <strong
                            id="previewStatus"
                            class="preview-status {{ old('status', $product->status) === 'inactive' ? 'inactive' : 'active' }}"
                        >
                            {{ old('status', $product->status) === 'inactive' ? 'Inactive' : 'Active' }}
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-specification">

                        <div class="preview-specification-title">
                            Specification
                        </div>

                        <p id="previewSpecification">

                            {{ old('specification', $product->specification) ?: 'No specification provided.' }}

                        </p>

                    </div>


                    <div class="preview-note">

                        <strong>
                            Product Information
                        </strong>

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
   PRODUCT EDIT
========================================================= */

.product-edit-card,
.product-edit-body,
.product-form-layout,
.product-form-main {

    overflow: visible !important;

}


/* =========================================================
   FORM LAYOUT
========================================================= */

.product-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;

    width: 100%;

}

.product-form-main {

    min-width: 0;

}


/* =========================================================
   SECTION
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
   FORM GROUP
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

    transition:
        border-color .18s ease,
        box-shadow .18s ease;

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

.form-error {

    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;

    line-height: 1.5;

}


/* =========================================================
   TWO COLUMN
========================================================= */

.product-two-column {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 16px;

}


/* =========================================================
   PRICE / WARRANTY
========================================================= */

.input-with-prefix,
.input-with-suffix {

    position: relative;

    display: flex;

    width: 100%;

}

.input-with-prefix input {

    padding-left: 44px;

}

.input-prefix {

    position: absolute;

    left: 0;

    top: 0;

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

.input-with-suffix input {

    padding-right: 76px;

}

.input-suffix {

    position: absolute;

    right: 12px;

    top: 50%;

    transform: translateY(-50%);

    color: #718096;

    font-size: 11px;

    pointer-events: none;

}


/* =========================================================
   ACTIONS
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
   PREVIEW
========================================================= */

.product-preview-panel {

    position: -webkit-sticky;

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

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 9px 0;

}

.preview-detail > span {

    color: #8a94a6;

    font-size: 11px;

}

.preview-detail > strong {

    max-width: 190px;

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

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 9px 0;

}

.preview-financial-row span {

    color: #8a94a6;

    font-size: 11px;

}

.preview-financial-row strong {

    color: #17284f;

    font-size: 13px;

    font-weight: 700;

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

    word-break: break-word;

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

@media (max-width: 900px) {

    .product-form-layout {

        grid-template-columns: 1fr;

    }

    .product-preview-panel {

        position: static;

        order: -1;

    }

}

@media (max-width: 600px) {

    .product-two-column {

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

    .product-preview-panel {

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

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        /* =====================================================
           FORM ELEMENTS
        ====================================================== */

        const productCode =
            document.getElementById('product_code');

        const productName =
            document.getElementById('product_name');

        const category =
            document.getElementById('category_id');

        const brand =
            document.getElementById('brand_id');

        const productType =
            document.getElementById('product_type');

        const unit =
            document.getElementById('unit');

        const price =
            document.getElementById('price');

        const warranty =
            document.getElementById('warranty_period');

        const status =
            document.getElementById('status');

        const specification =
            document.getElementById('specification');


        /* =====================================================
           PREVIEW ELEMENTS
        ====================================================== */

        const previewProductName =
            document.getElementById(
                'previewProductName'
            );

        const previewProductCode =
            document.getElementById(
                'previewProductCode'
            );

        const previewCategory =
            document.getElementById(
                'previewCategory'
            );

        const previewBrand =
            document.getElementById(
                'previewBrand'
            );

        const previewProductType =
            document.getElementById(
                'previewProductType'
            );

        const previewUnit =
            document.getElementById(
                'previewUnit'
            );

        const previewPrice =
            document.getElementById(
                'previewPrice'
            );

        const previewWarranty =
            document.getElementById(
                'previewWarranty'
            );

        const previewStatus =
            document.getElementById(
                'previewStatus'
            );

        const previewSpecification =
            document.getElementById(
                'previewSpecification'
            );


        /* =====================================================
           CURRENCY
        ====================================================== */

        function formatCurrency(value) {

            if (
                value === '' ||
                value === null ||
                value === undefined
            ) {

                return 'Rp 0';

            }

            const number =
                Number(value);

            if (Number.isNaN(number)) {

                return 'Rp 0';

            }

            return 'Rp ' +
                new Intl.NumberFormat(
                    'id-ID',
                    {
                        maximumFractionDigits: 2
                    }
                ).format(number);

        }


        /* =====================================================
           UPDATE PREVIEW
        ====================================================== */

        function updatePreview() {


            previewProductName.textContent =
                productName.value.trim() ||
                'New Product';


            previewProductCode.textContent =
                productCode.value.trim() ||
                'No Product Code';


            if (category.value) {

                previewCategory.textContent =
                    category.options[
                        category.selectedIndex
                    ].text;

            } else {

                previewCategory.textContent =
                    'No Category';

            }


            if (brand.value) {

                previewBrand.textContent =
                    brand.options[
                        brand.selectedIndex
                    ].text;

            } else {

                previewBrand.textContent =
                    'No Brand';

            }


            previewProductType.textContent =
                productType.value.trim() ||
                '-';


            previewUnit.textContent =
                unit.value.trim() ||
                '-';


            previewPrice.textContent =
                formatCurrency(
                    price.value
                );


            const warrantyValue =
                warranty.value.trim();

            previewWarranty.textContent =
                warrantyValue
                    ? warrantyValue + ' Months'
                    : '0 Months';


            const statusValue =
                status.value || 'active';


            if (
                statusValue === 'inactive'
            ) {

                previewStatus.textContent =
                    'Inactive';

                previewStatus.classList.remove(
                    'active'
                );

                previewStatus.classList.add(
                    'inactive'
                );

            } else {

                previewStatus.textContent =
                    'Active';

                previewStatus.classList.remove(
                    'inactive'
                );

                previewStatus.classList.add(
                    'active'
                );

            }


            previewSpecification.textContent =
                specification.value.trim() ||
                'No specification provided.';

        }


        /* =====================================================
           EVENT LISTENERS
        ====================================================== */

        [

            productCode,
            productName,
            category,
            brand,
            productType,
            unit,
            price,
            warranty,
            status,
            specification

        ].forEach(
            function (element) {

                element.addEventListener(
                    'input',
                    updatePreview
                );

                element.addEventListener(
                    'change',
                    updatePreview
                );

            }
        );


        /* =====================================================
           INITIAL PREVIEW
        ====================================================== */

        updatePreview();

    }
);

</script>

@endsection
