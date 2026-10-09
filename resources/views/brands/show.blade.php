@extends('layouts.app')

@section('title', 'Brand Details')

@section('content')
@php
$totalProducts = $products->count();
$brandInitial = strtoupper(substr(trim($brand->brand_name ?? 'B'), 0, 1));
$previewProducts = $products->take(5);
@endphp

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Brand Details</h1>
        <p>View brand information and products associated with this brand.</p>
    </div>

    <div class="actions">
        <a href="{{ route('brands.index') }}" class="btn">
            <span aria-hidden="true">&larr;</span>
            Back to Brands
        </a>

        <a href="{{ route('brands.edit', $brand) }}" class="btn primary">
            Edit Brand
        </a>
    </div>
</div>

{{-- ALERTS --}}
@if (session('success'))
<div class="alert success" role="status">
    {{ session('success') }}
</div>
@endif

@if (session('error'))
<div class="alert error" role="alert">
    {{ session('error') }}
</div>
@endif

{{-- BRAND OVERVIEW --}}
<div class="brand-overview-grid">
    <section class="card brand-information-card">
        <div class="card-head">
            <div>
                <h3>Brand Information</h3>
                <p>Basic information about this product brand.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="brand-profile">
                <div class="brand-profile-icon" aria-hidden="true">
                    {{ $brandInitial }}
                </div>

                <div class="brand-profile-content">
                    <h2>{{ $brand->brand_name }}</h2>
                    <span>Product Brand</span>
                </div>
            </div>

            <div class="brand-info-grid">
                <div class="detail-item">
                    <span class="detail-label">Brand Name</span>
                    <strong title="{{ $brand->brand_name }}">
                        {{ $brand->brand_name }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Total Products</span>
                    <strong>{{ number_format($totalProducts) }}</strong>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Created At</span>
                    <strong>
                        {{ $brand->created_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <strong>
                        {{ $brand->updated_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card brand-summary-card">
        <div class="card-head">
            <div>
                <h3>Brand Summary</h3>
                <p>Overview of products assigned to this brand.</p>
            </div>
        </div>

        @php
        $activeProducts = $products->filter(
        fn ($product) => strtolower(trim((string) $product->status)) === 'active'
        )->count();

        $inactiveProducts = $products->filter(
        fn ($product) => strtolower(trim((string) $product->status)) === 'inactive'
        )->count();
        @endphp

        <div class="card-body">
            <div class="brand-summary">
                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">P</div>

                    <div class="summary-content">
                        <span>Total Products</span>
                        <strong>{{ number_format($totalProducts) }}</strong>
                        <small>Products associated with this brand</small>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">A</div>

                    <div class="summary-content">
                        <span>Active Products</span>
                        <strong>{{ number_format($activeProducts) }}</strong>
                        <small>Currently marked as active</small>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">I</div>

                    <div class="summary-content">
                        <span>Inactive Products</span>
                        <strong>{{ number_format($inactiveProducts) }}</strong>
                        <small>Currently marked as inactive</small>
                    </div>
                </div>
            </div>

            <a
                href="{{ route('products.create') }}"
                class="btn primary brand-create-button">
                <span aria-hidden="true">+</span>
                Create New Product
            </a>
        </div>
    </section>
</div>

{{-- BRAND DESCRIPTION --}}
<section class="card brand-description-card">
    <div class="card-head">
        <div>
            <h3>Description</h3>
            <p>Additional information about this brand.</p>
        </div>
    </div>

    <div class="card-body">
        @if (filled($brand->description))
        <p class="brand-description">{{ $brand->description }}</p>
        @else
        <div class="description-empty">
            <span class="description-empty-icon" aria-hidden="true">i</span>

            <div>
                <strong>No description available</strong>
                <p>No additional information has been provided for this brand.</p>
            </div>
        </div>
        @endif
    </div>
</section>

{{-- ASSOCIATED PRODUCTS --}}
<section class="card brand-products-card">
    <div class="card-head products-card-head">
        <div>
            <h3>Associated Products</h3>
            <p>Products registered under this brand.</p>
        </div>

        <div class="associated-products-actions">
            <span class="section-count">
                {{ number_format($totalProducts) }}
                {{ $totalProducts === 1 ? 'product' : 'products' }}
            </span>

            @if ($totalProducts > 0)
            <button
                type="button"
                class="btn-show-all"
                id="openBrandProductsButton"
                aria-haspopup="dialog"
                aria-controls="brandProductsModal">
                Show All
            </button>
            @endif
        </div>
    </div>

    <div class="card-body no-padding">
        @if ($totalProducts > 0)
        <div class="table-wrap">
            <table class="brand-products-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product Code</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($previewProducts as $product)
                    @php
                    $productStatus = strtolower(trim((string) $product->status));

                    $statusClass = match ($productStatus) {
                    'active' => 'active',
                    'inactive' => 'inactive',
                    default => 'other',
                    };
                    @endphp

                    <tr>
                        <td>
                            <div class="product-name-cell">
                                <a
                                    href="{{ route('products.show', $product) }}"
                                    class="product-name"
                                    title="{{ $product->product_name }}">
                                    {{ $product->product_name ?: 'Unnamed Product' }}
                                </a>

                                <span class="product-id">
                                    {{ $product->product_id }}
                                </span>
                            </div>
                        </td>

                        <td>
                            <span class="product-code">
                                {{ $product->product_code ?: '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="table-text">
                                {{ $product->category?->category_name ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="table-text">
                                {{ $product->product_type ?: '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="product-price">
                                @if ($product->price !== null)
                                Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </span>
                        </td>

                        <td>
                            <span class="product-status {{ $statusClass }}">
                                <span class="status-dot"></span>
                                {{ ucfirst($productStatus ?: 'Unknown') }}
                            </span>
                        </td>

                        <td>
                            <span class="table-date">
                                {{ $product->created_at?->format('d M Y') ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <a
                                href="{{ route('products.show', $product) }}"
                                class="action-btn view"
                                title="View product details">
                                View <span aria-hidden="true">&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($totalProducts > 5)
        <div class="table-pagination">
            <div class="pagination-info">
                Showing <strong>{{ $previewProducts->count() }}</strong>
                of <strong>{{ number_format($totalProducts) }}</strong>
                products
            </div>

            <button
                type="button"
                class="text-button"
                data-open-brand-products>
                View all products &rarr;
            </button>
        </div>
        @endif
        @else
        <div class="empty-state">
            <div class="empty-state-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linejoin="round" />
                    <path
                        d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linejoin="round" />
                </svg>
            </div>

            <h4>No Products Found</h4>
            <p>There are currently no products associated with this brand.</p>

            <a href="{{ route('products.index') }}" class="btn">
                Browse Products
            </a>
        </div>
        @endif
    </div>
</section>

{{-- ALL ASSOCIATED PRODUCTS MODAL --}}
@if ($totalProducts > 0)
<div
    class="brand-modal-overlay"
    id="brandProductsModal"
    aria-hidden="true"
    inert>

    <section
        class="brand-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="brandProductsModalTitle"
        tabindex="-1">

        <header class="brand-modal-header">
            <div>
                <h3 id="brandProductsModalTitle">All Associated Products</h3>
                <p>
                    {{ $brand->brand_name }}
                    &middot;
                    {{ number_format($totalProducts) }} products
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                id="closeBrandProductsModal"
                aria-label="Close modal">
                &times;
            </button>
        </header>

        {{-- SEARCH AND FILTERS --}}
        <div class="brand-modal-toolbar">
            <div class="brand-search">
                <input
                    type="search"
                    id="brandProductSearch"
                    placeholder="Search product, code, category, type..."
                    aria-label="Search associated products"
                    autocomplete="off">
            </div>

            <div class="brand-filter">
                <label for="brandProductStatus">Status</label>
                <select id="brandProductStatus">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="brand-filter">
                <label for="brandProductSort">Sort by</label>
                <select id="brandProductSort">
                    <option value="name_asc">Name: A–Z</option>
                    <option value="name_desc">Name: Z–A</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>
        </div>

        {{-- MODAL TABLE --}}
        <div class="brand-modal-table-wrap">
            <table class="brand-products-table brand-modal-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product Code</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="brandModalProductRows">
                    @foreach ($products as $product)
                    @php
                    $productStatus = strtolower(trim((string) $product->status));

                    $statusClass = match ($productStatus) {
                    'active' => 'active',
                    'inactive' => 'inactive',
                    default => 'other',
                    };
                    @endphp

                    <tr
                        class="brand-modal-product-row"
                        data-name="{{ $product->product_name ?? '' }}"
                        data-code="{{ $product->product_code ?? '' }}"
                        data-category="{{ $product->category?->category_name ?? '' }}"
                        data-type="{{ $product->product_type ?? '' }}"
                        data-status="{{ in_array($productStatus, ['active', 'inactive'], true) ? $productStatus : 'other' }}"
                        data-price="{{ $product->price ?? 0 }}"
                        data-created="{{ $product->created_at?->timestamp ?? 0 }}">

                        <td>
                            <div class="product-name-cell">
                                <span class="product-name">
                                    {{ $product->product_name ?: 'Unnamed Product' }}
                                </span>

                                <span class="product-id">
                                    {{ $product->product_id }}
                                </span>
                            </div>
                        </td>

                        <td>{{ $product->product_code ?: '-' }}</td>

                        <td>
                            {{ $product->category?->category_name ?? '-' }}
                        </td>

                        <td>{{ $product->product_type ?: '-' }}</td>

                        <td>
                            @if ($product->price !== null)
                            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>

                        <td>
                            <span class="product-status {{ $statusClass }}">
                                <span class="status-dot"></span>
                                {{ ucfirst($productStatus ?: 'Unknown') }}
                            </span>
                        </td>

                        <td>
                            {{ $product->created_at?->format('d M Y') ?? '-' }}
                        </td>

                        <td>
                            <a
                                href="{{ route('products.show', $product) }}"
                                class="action-btn view"
                                title="View product details">
                                View <span aria-hidden="true">&rarr;</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div id="brandModalNoResults" class="brand-modal-empty" hidden>
                <strong>No Matching Products</strong>
                <p>Try another keyword or adjust the selected filter.</p>

                <button
                    type="button"
                    class="btn"
                    id="resetBrandProductFilters">
                    Reset Filters
                </button>
            </div>
        </div>

        {{-- MODAL PAGINATION --}}
        <footer class="brand-modal-footer">
            <span id="brandModalPaginationInfo" aria-live="polite"></span>

            <div class="brand-modal-pagination">
                <button
                    type="button"
                    class="btn"
                    id="brandModalPrevious">
                    Previous
                </button>

                <span id="brandModalPageNumber">1 / 1</span>

                <button
                    type="button"
                    class="btn"
                    id="brandModalNext">
                    Next
                </button>
            </div>
        </footer>
    </section>
</div>
@endif

<style>
    .brand-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, 0.85fr);
        align-items: stretch;
        gap: 18px;
        margin-bottom: 18px;
    }

    .brand-information-card,
    .brand-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .brand-information-card>.card-body,
    .brand-summary-card>.card-body {
        flex: 1;
        min-width: 0;
    }

    .brand-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 0;
        margin-bottom: 20px;
        padding-bottom: 19px;
        border-bottom: 1px solid #edf0f5;
    }

    .brand-profile-icon {
        display: flex;
        flex: 0 0 54px;
        align-items: center;
        justify-content: center;
        width: 54px;
        height: 54px;
        border: 1px solid #d9eee7;
        border-radius: 13px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 20px;
        font-weight: 700;
    }

    .brand-profile-content {
        min-width: 0;
    }

    .brand-profile-content h2 {
        margin: 0 0 5px;
        overflow-wrap: anywhere;
        color: #17284f;
        font-size: 19px;
        font-weight: 700;
    }

    .brand-profile-content>span {
        color: #7d8797;
        font-size: 11px;
    }

    .brand-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .brand-information-card .detail-item {
        min-width: 0;
        padding: 13px 14px;
        border: 1px solid #e7ebf1;
        border-radius: 9px;
        background: #fafbfd;
    }

    .brand-information-card .detail-label {
        display: block;
        margin-bottom: 7px;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 500;
    }

    .brand-information-card .detail-item strong {
        display: block;
        overflow-wrap: anywhere;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
    }


    /* =========================================================
   BRAND SUMMARY
   Layout: icon | label, number, helper text
========================================================= */

    .brand-summary-card {
        align-self: stretch;
    }

    .brand-summary-card>.card-body {
        display: flex;
        flex-direction: column;
        padding-top: 12px;
    }

    .brand-summary-card .brand-summary {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 0;
        margin: 0;
        padding: 0;
    }

    .brand-summary-card .brand-summary .summary-item {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-start;
        gap: 13px;
        width: 100%;
        min-width: 0;
        margin: 0;
        padding: 15px 0;
        border: 0;
        border-bottom: 1px solid #edf0f5;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        text-align: left;
    }

    .brand-summary-card .brand-summary .summary-item:first-child {
        padding-top: 5px;
    }

    .brand-summary-card .brand-summary .summary-item:last-child {
        padding-bottom: 15px;
        border-bottom: 0;
    }

    /* Ikon P, A, I */
    .brand-summary-card .brand-summary .summary-icon {
        display: flex;
        flex: 0 0 40px;
        align-items: center;
        justify-content: center;
        width: 40px;
        min-width: 40px;
        height: 40px;
        margin: 0;
        padding: 0;
        border: 1px solid #d9eee7;
        border-radius: 10px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 14px;
        font-weight: 700;
        line-height: 1;
    }

    /* Label, number, and helper text */
    .brand-summary-card .brand-summary .summary-content {
        display: flex;
        flex: 1 1 auto;
        flex-direction: column;
        align-items: flex-start;
        justify-content: center;
        gap: 3px;
        min-width: 0;
        margin: 0;
        padding: 0;
    }

    .brand-summary-card .brand-summary .summary-content>span {
        display: block;
        margin: 0;
        color: #7d8797;
        font-size: 11px;
        font-weight: 500;
        line-height: 1.4;
    }

    .brand-summary-card .brand-summary .summary-content>strong {
        display: block;
        margin: 0;
        padding: 0;
        color: #17284f;
        font-size: 21px;
        font-weight: 700;
        line-height: 1.3;
    }

    .brand-summary-card .brand-summary .summary-content>small {
        display: block;
        margin: 0;
        color: #9aa3b1;
        font-size: 10px;
        font-weight: 400;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    /* Create Product button */
    .brand-summary-card .brand-create-button {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        min-height: 40px;
        margin: auto 0 0;
        padding: 10px 14px;
        box-sizing: border-box;
        border-radius: 7px;
        text-align: center;
        text-decoration: none;
    }

    .brand-summary-card .brand-create-button>span {
        display: inline-block;
        margin: 0;
        font-size: 16px;
        font-weight: 500;
        line-height: 1;
    }


    /* =========================================================
   SHARED STATUS STYLES
========================================================= */

    .status-badge,
    .product-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: fit-content;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge {
        background: #eaf7f3;
        color: #167d70;
    }

    .status-dot {
        display: inline-block;
        flex: 0 0 6px;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .product-status.active {
        background: #eaf7f3;
        color: #167d70;
    }

    .product-status.inactive {
        background: #f1f3f6;
        color: #7d8797;
    }

    .product-status.other {
        background: #fff5e8;
        color: #996515;
    }


    /* =========================================================
   DESCRIPTION
========================================================= */

    .brand-description-card {
        margin-bottom: 18px;
    }

    .brand-description {
        margin: 0;
        color: #526078;
        font-size: 13px;
        line-height: 1.8;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .description-empty {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 4px 0;
    }

    .description-empty-icon {
        display: flex;
        flex: 0 0 19px;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        border-radius: 50%;
        background: #f1f4f8;
        color: #8a94a6;
        font-size: 11px;
        font-weight: 700;
    }

    .description-empty strong {
        display: block;
        margin-bottom: 4px;
        color: #526078;
        font-size: 12px;
    }

    .description-empty p {
        margin: 0;
        color: #9aa3b1;
        font-size: 11px;
        line-height: 1.6;
    }


    /* =========================================================
   ASSOCIATED PRODUCTS
========================================================= */

    .brand-products-card {
        min-width: 0;
        overflow: hidden;
    }

    .products-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .associated-products-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-count {
        flex-shrink: 0;
        padding: 6px 10px;
        border: 1px solid #e3e9f0;
        border-radius: 7px;
        background: #fafbfd;
        color: #667085;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-show-all {
        min-height: 32px;
        padding: 6px 12px;
        border: 1px solid #d8e8e2;
        border-radius: 6px;
        background: #f5fbf8;
        color: #15966f;
        font-family: inherit;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }

    .btn-show-all:hover {
        border-color: #15966f;
        background: #eaf7f3;
    }

    .brand-products-table {
        width: 100%;
        min-width: 1080px;
        border-collapse: collapse;
    }

    .brand-products-table th {
        padding: 13px 15px;
        border-bottom: 1px solid #e7ebf1;
        background: #fafbfd;
        color: #7d8797;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .brand-products-table td {
        padding: 13px 15px;
        border-bottom: 1px solid #edf0f5;
        color: #526078;
        font-size: 11px;
        vertical-align: middle;
    }

    .brand-products-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .brand-products-table tbody tr {
        transition: background .15s ease;
    }

    .brand-products-table tbody tr:hover {
        background: #fafcfe;
    }

    .product-name-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
        max-width: 220px;
    }

    .product-name {
        display: block;
        overflow: hidden;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    a.product-name:hover {
        color: #167d70;
    }

    .product-id {
        color: #a0a8b6;
        font-size: 10px;
    }

    .product-code {
        display: inline-block;
        color: #526078;
        font-size: 11px;
        white-space: nowrap;
    }

    .product-price {
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .table-text {
        display: inline-block;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .table-date {
        color: #7d8797;
        font-size: 11px;
        white-space: nowrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-width: 43px;
        min-height: 29px;
        padding: 0 9px;
        border: 1px solid #e2e7ef;
        border-radius: 6px;
        background: #fff;
        color: #667085;
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .action-btn:hover {
        border-color: #cfe8df;
        background: #eaf7f3;
        color: #167d70;
    }

    .table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 15px 18px;
        border-top: 1px solid #edf0f5;
    }

    .pagination-info {
        color: #8a94a6;
        font-size: 11px;
    }

    .pagination-info strong {
        color: #526078;
        font-weight: 600;
    }

    .text-button {
        border: 0;
        background: transparent;
        color: #167d70;
        font-family: inherit;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .text-button:hover {
        text-decoration: underline;
    }


    /* =========================================================
   EMPTY STATE
========================================================= */

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 52px 20px;
        text-align: center;
    }

    .empty-state-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-bottom: 14px;
        border: 1px solid #e7ebf1;
        border-radius: 12px;
        background: #f7f9fc;
        color: #8a94a6;
    }

    .empty-state-icon svg {
        width: 24px;
        height: 24px;
    }

    .empty-state h4 {
        margin: 0 0 7px;
        color: #34415c;
        font-size: 14px;
    }

    .empty-state p {
        max-width: 360px;
        margin: 0 0 18px;
        color: #9aa3b1;
        font-size: 11px;
        line-height: 1.7;
    }


    /* =========================================================
   PRODUCTS MODAL
========================================================= */

    .brand-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(16, 27, 49, 0.55);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .18s ease, visibility .18s ease;
    }

    .brand-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .brand-modal {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 1200px;
        max-height: 90vh;
        overflow: hidden;
        border: 1px solid #e6eaf0;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(16, 27, 49, 0.22);
        transform: translateY(8px);
        transition: transform .18s ease;
    }

    .brand-modal-overlay.is-open .brand-modal {
        transform: translateY(0);
    }

    .brand-modal-header,
    .brand-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 18px 22px;
    }

    .brand-modal-header {
        border-bottom: 1px solid #edf0f5;
    }

    .brand-modal-header h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
    }

    .brand-modal-header p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .modal-close {
        display: flex;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 7px;
        background: #f1f3f6;
        color: #596579;
        font-size: 22px;
        cursor: pointer;
    }

    .modal-close:hover {
        background: #e6eaf0;
    }

    /* Modal toolbar */

    .brand-modal-toolbar {
        display: grid;
        grid-template-columns: minmax(200px, 1fr) 160px 190px;
        align-items: end;
        gap: 12px;
        padding: 16px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .brand-search input,
    .brand-filter select {
        box-sizing: border-box;
        width: 100%;
        min-height: 39px;
        padding: 9px 12px;
        border: 1px solid #dfe4eb;
        border-radius: 7px;
        outline: none;
        background: #fff;
        color: #34415c;
        font-family: inherit;
        font-size: 11px;
    }

    .brand-search input:focus,
    .brand-filter select:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, 0.1);
    }

    .brand-filter {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .brand-filter label {
        color: #8a94a6;
        font-size: 10px;
        font-weight: 600;
    }

    .brand-filter select {
        cursor: pointer;
    }

    /* Modal table */

    .brand-modal-table-wrap {
        flex: 1;
        min-height: 120px;
        overflow: auto;
    }

    .brand-modal-table {
        min-width: 1050px;
    }

    .brand-modal-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .brand-modal-product-row[hidden] {
        display: none !important;
    }

    .brand-modal-empty {
        padding: 35px 20px;
        color: #8a94a6;
        text-align: center;
    }

    .brand-modal-empty[hidden] {
        display: none !important;
    }

    .brand-modal-empty strong {
        display: block;
        margin-bottom: 6px;
        color: #34415c;
        font-size: 12px;
    }

    .brand-modal-empty p {
        margin: 0 0 15px;
        font-size: 11px;
    }

    .brand-modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 11px;
    }

    .brand-modal-pagination {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand-modal-pagination .btn {
        min-height: 32px;
        padding: 7px 11px;
        font-size: 11px;
        cursor: pointer;
    }

    .brand-modal-pagination button:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    #brandModalPageNumber {
        min-width: 45px;
        color: #596579;
        text-align: center;
    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 950px) {
        .brand-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 760px) {
        .associated-products-actions {
            justify-content: flex-start;
        }

        .brand-modal-overlay {
            padding: 8px;
        }

        .brand-modal {
            max-height: 94vh;
        }

        .brand-modal-header,
        .brand-modal-footer {
            padding: 14px;
        }

        .brand-modal-toolbar {
            grid-template-columns: minmax(0, 1fr);
            padding: 14px;
        }

        .brand-modal-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .brand-modal-pagination {
            justify-content: space-between;
            width: 100%;
        }
    }

    @media (max-width: 640px) {
        .brand-info-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .products-card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .table-pagination {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 480px) {
        .brand-profile {
            align-items: flex-start;
        }

        .brand-profile-content h2 {
            font-size: 17px;
        }

        .brand-summary-card .brand-summary .summary-item {
            gap: 10px;
        }

        .brand-summary-card .brand-summary .summary-icon {
            flex-basis: 36px;
            width: 36px;
            min-width: 36px;
            height: 36px;
        }

        .brand-summary-card .brand-summary .summary-content>strong {
            font-size: 19px;
        }

        .section-count {
            padding: 5px 7px;
            font-size: 9px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .brand-modal,
        .brand-modal-overlay {
            transition: none;
        }
    }
</style>

{{-- MODAL INTERACTIONS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('brandProductsModal');

        if (!modal) {
            return;
        }

        const dialog = modal.querySelector('[role="dialog"]');
        const openButton = document.getElementById('openBrandProductsButton');
        const closeButton = document.getElementById('closeBrandProductsModal');
        const searchInput = document.getElementById('brandProductSearch');
        const statusFilter = document.getElementById('brandProductStatus');
        const sortSelect = document.getElementById('brandProductSort');
        const tableBody = document.getElementById('brandModalProductRows');
        const table = tableBody.closest('table');
        const noResults = document.getElementById('brandModalNoResults');
        const paginationInfo = document.getElementById('brandModalPaginationInfo');
        const pageNumber = document.getElementById('brandModalPageNumber');
        const previousButton = document.getElementById('brandModalPrevious');
        const nextButton = document.getElementById('brandModalNext');
        const resetButton = document.getElementById('resetBrandProductFilters');

        const allRows = Array.from(
            tableBody.querySelectorAll('.brand-modal-product-row')
        );

        const pageSize = 10;

        let filteredRows = [...allRows];
        let currentPage = 1;
        let previousFocusedElement = null;
        let previousBodyOverflow = '';

        function numericValue(row, key) {
            return Number(row.dataset[key] || 0);
        }

        function applyFilters() {
            const keyword = searchInput.value.trim().toLocaleLowerCase();
            const selectedStatus = statusFilter.value;
            const sortBy = sortSelect.value;

            filteredRows = allRows.filter(function(row) {
                const searchableText = [
                    row.dataset.name,
                    row.dataset.code,
                    row.dataset.category,
                    row.dataset.type
                ].join(' ').toLocaleLowerCase();

                const matchesKeyword = searchableText.includes(keyword);

                const matchesStatus =
                    selectedStatus === 'all' ||
                    row.dataset.status === selectedStatus;

                return matchesKeyword && matchesStatus;
            });

            filteredRows.sort(function(a, b) {
                const nameA = (a.dataset.name || '').toLocaleLowerCase();
                const nameB = (b.dataset.name || '').toLocaleLowerCase();

                switch (sortBy) {
                    case 'name_desc':
                        return nameB.localeCompare(nameA);

                    case 'price_desc':
                        return numericValue(b, 'price') - numericValue(a, 'price');

                    case 'price_asc':
                        return numericValue(a, 'price') - numericValue(b, 'price');

                    case 'newest':
                        return numericValue(b, 'created') - numericValue(a, 'created');

                    case 'oldest':
                        return numericValue(a, 'created') - numericValue(b, 'created');

                    case 'name_asc':
                    default:
                        return nameA.localeCompare(nameB);
                }
            });

            currentPage = 1;
            renderRows();
        }

        function renderRows() {
            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));

            currentPage = Math.min(Math.max(currentPage, 1), totalPages);

            const start = (currentPage - 1) * pageSize;
            const end = Math.min(start + pageSize, total);
            const visibleRows = filteredRows.slice(start, end);

            allRows.forEach(function(row) {
                row.hidden = true;
            });

            visibleRows.forEach(function(row) {
                row.hidden = false;
                tableBody.appendChild(row);
            });

            table.style.display = total === 0 ? 'none' : '';
            noResults.hidden = total !== 0;

            paginationInfo.textContent = total === 0 ?
                'No products to display' :
                `Showing ${start + 1}–${end} of ${total} products`;

            pageNumber.textContent = `${currentPage} / ${totalPages}`;

            previousButton.disabled = currentPage <= 1 || total === 0;
            nextButton.disabled = currentPage >= totalPages || total === 0;
        }

        function openModal() {
            if (modal.classList.contains('is-open')) {
                return;
            }

            previousFocusedElement = document.activeElement;
            previousBodyOverflow = document.body.style.overflow;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            modal.inert = false;

            document.body.style.overflow = 'hidden';

            searchInput.value = '';
            statusFilter.value = 'all';
            sortSelect.value = 'name_asc';

            applyFilters();
            searchInput.focus();
        }

        function closeModal() {
            if (!modal.classList.contains('is-open')) {
                return;
            }

            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            modal.inert = true;

            document.body.style.overflow = previousBodyOverflow;

            if (
                previousFocusedElement &&
                typeof previousFocusedElement.focus === 'function'
            ) {
                previousFocusedElement.focus();
            }
        }

        function resetFilters() {
            searchInput.value = '';
            statusFilter.value = 'all';
            sortSelect.value = 'name_asc';

            applyFilters();
            searchInput.focus();
        }

        // Open modal.
        if (openButton) {
            openButton.addEventListener('click', openModal);
        }

        document.querySelectorAll('[data-open-brand-products]').forEach(function(button) {
            button.addEventListener('click', openModal);
        });

        // Close modal.
        closeButton.addEventListener('click', closeModal);

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        // Search, filter, and sort.
        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        sortSelect.addEventListener('change', applyFilters);
        resetButton.addEventListener('click', resetFilters);

        // Pagination.
        previousButton.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderRows();
            }
        });

        nextButton.addEventListener('click', function() {
            const totalPages = Math.max(
                1,
                Math.ceil(filteredRows.length / pageSize)
            );

            if (currentPage < totalPages) {
                currentPage++;
                renderRows();
            }
        });

        // Keep keyboard focus inside the open modal.
        modal.addEventListener('keydown', function(event) {
            if (
                event.key !== 'Tab' ||
                !modal.classList.contains('is-open')
            ) {
                return;
            }

            const focusableElements = Array.from(
                dialog.querySelectorAll(
                    'button:not(:disabled), input:not(:disabled), select:not(:disabled), a[href]'
                )
            ).filter(function(element) {
                return !element.hidden && element.getClientRects().length > 0;
            });

            if (focusableElements.length === 0) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const first = focusableElements[0];
            const last = focusableElements[focusableElements.length - 1];

            if (
                event.shiftKey &&
                (
                    document.activeElement === first ||
                    !dialog.contains(document.activeElement)
                )
            ) {
                event.preventDefault();
                last.focus();
            } else if (
                !event.shiftKey &&
                document.activeElement === last
            ) {
                event.preventDefault();
                first.focus();
            }
        });

        renderRows();
    });
</script>
@endsection