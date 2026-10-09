
@extends('layouts.app')

@section('title', 'Product Category Details')

@section('content')

@php
    $products = $productCategory->products
        ->sortBy('product_name')
        ->values();

    $productCount = $products->count();
@endphp

<div class="page-head">
    <div>
        <h1>Product Category Details</h1>
        <p>View category information and manage products assigned to this category.</p>
    </div>

    <div class="actions">
        <a href="{{ route('product-categories.index') }}" class="btn">
            &larr; Back to Categories
        </a>

        <a href="{{ route('product-categories.edit', $productCategory) }}" class="btn primary">
            Edit Category
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert error">{{ session('error') }}</div>
@endif

<div class="category-overview-grid">
    <section class="card category-information-card">
        <div class="card-head">
            <div>
                <h3>Category Information</h3>
                <p>Basic information and classification of this product category.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="category-identity">
                <div class="category-icon" aria-hidden="true">C</div>

                <div class="category-identity-content">
                    <h2>{{ $productCategory->category_name ?: 'Unnamed Category' }}</h2>
                    <span>Product Category</span>
                </div>

                <div class="category-status">
                    <span class="category-type-badge">Category</span>
                </div>
            </div>

            <div class="category-info-grid">
                <div class="info-item">
                    <span class="info-label">Category Name</span>
                    <strong>{{ $productCategory->category_name ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Assigned Products</span>
                    <strong>{{ number_format($productCount) }} {{ $productCount === 1 ? 'Product' : 'Products' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Created At</span>
                    <strong>{{ $productCategory->created_at?->format('d M Y, H:i') ?? '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Last Updated</span>
                    <strong>{{ $productCategory->updated_at?->format('d M Y, H:i') ?? '-' }}</strong>
                </div>
            </div>

            <div class="category-description">
                <div class="description-heading">Description</div>

                @if ($productCategory->description)
                    <p>{!! nl2br(e($productCategory->description)) !!}</p>
                @else
                    <div class="description-empty">
                        <span class="description-empty-icon" aria-hidden="true">i</span>
                        <div>
                            <strong>No description provided</strong>
                            <span>A description has not been added to this category.</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="card category-summary-card">
        <div class="card-head">
            <div>
                <h3>Category Summary</h3>
                <p>Overview of products assigned to this category.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="category-summary">
                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">P</div>
                    <div class="summary-content">
                        <span>Total Products</span>
                        <strong>{{ number_format($productCount) }}</strong>
                        <small>Products in this category</small>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">A</div>
                    <div class="summary-content">
                        <span>Active Products</span>
                        <strong>{{ number_format($products->filter(fn ($product) => strtolower((string) $product->status) === 'active')->count()) }}</strong>
                        <small>Currently marked as active</small>
                    </div>
                </div>

                <div class="summary-item">
                    <div class="summary-icon" aria-hidden="true">I</div>
                    <div class="summary-content">
                        <span>Inactive Products</span>
                        <strong>{{ number_format($products->filter(fn ($product) => strtolower((string) $product->status) === 'inactive')->count()) }}</strong>
                        <small>Currently marked as inactive</small>
                    </div>
                </div>
            </div>

            <a href="{{ route('products.create') }}" class="btn primary category-create-button">
                <span aria-hidden="true">+</span>
                Create New Product
            </a>
        </div>
    </section>
</div>

<section class="card category-products-card">
    <div class="card-head category-products-head">
        <div>
            <h3>Products in This Category</h3>
            <p>Browse products assigned to this category and view their details.</p>
        </div>

        <div class="category-products-actions">
            <span class="section-count">
                {{ number_format($productCount) }} {{ $productCount === 1 ? 'product' : 'products' }}
            </span>

            <button
                type="button"
                class="btn-show-all"
                id="openCategoryProductsButton"
                aria-haspopup="dialog"
                aria-controls="categoryProductsModal">
                Show All
            </button>
        </div>
    </div>

    <div class="card-body no-padding">
        @if ($productCount > 0)
            <div class="table-wrap category-table-wrap">
                <table class="category-products-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products->take(5) as $product)
                            <tr>
                                <td>
                                    <div class="category-product-cell">
                                        <span class="product-icon" aria-hidden="true">P</span>

                                        <div class="category-product-info">
                                            <a href="{{ route('products.show', $product) }}" class="product-name-link">
                                                {{ $product->product_name ?: 'Unnamed Product' }}
                                            </a>
                                            <span class="product-code">{{ $product->product_code ?: 'No product code' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="table-primary-text">{{ $product->brand?->brand_name ?? '-' }}</span>
                                </td>

                                <td>
                                    <span class="table-secondary-text">{{ $product->product_type ?: '-' }}</span>
                                </td>

                                <td>
                                    @if ($product->price !== null)
                                        <strong class="product-price">
                                            Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                        </strong>
                                    @else
                                        <span class="table-muted">-</span>
                                    @endif
                                </td>

                                <td>
                                    @php
                                        $productStatus = strtolower((string) $product->status);
                                    @endphp

                                    <span class="product-status-badge {{ $productStatus === 'active' ? 'is-active' : ($productStatus === 'inactive' ? 'is-inactive' : 'is-other') }}">
                                        <span class="status-indicator"></span>
                                        {{ ucfirst($productStatus ?: 'Unknown') }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('products.show', $product) }}" class="action-view-button">
                                        <span>View</span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($productCount > 5)
                <div class="category-table-footer">
                    <span>Showing 5 of {{ $productCount }} products.</span>

                    <button type="button" class="text-button" data-open-category-products>
                        View all products
                    </button>
                </div>
            @endif
        @else
            <div class="category-empty-state">
                <div class="empty-icon" aria-hidden="true">P</div>
                <h4>No Products in This Category</h4>
                <p>There are currently no products assigned to this category.</p>

                <a href="{{ route('products.create') }}" class="btn primary empty-state-button">
                    + Create Product
                </a>
            </div>
        @endif
    </div>
</section>

<div
    class="category-modal-overlay"
    id="categoryProductsModal"
    aria-hidden="true"
    inert>
    <section
        class="category-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="categoryProductsModalTitle"
        aria-describedby="categoryProductsModalDescription"
        tabindex="-1">
        <header class="category-modal-header">
            <div class="category-modal-heading">
                <span class="category-modal-icon" aria-hidden="true">P</span>

                <div>
                    <h3 id="categoryProductsModalTitle">All Category Products</h3>
                    <p id="categoryProductsModalDescription">
                        {{ $productCategory->category_name }}
                        &middot;
                        {{ $productCount }} {{ $productCount === 1 ? 'product' : 'products' }}
                    </p>
                </div>
            </div>

            <button
                type="button"
                class="modal-close"
                id="closeCategoryProductsButton"
                aria-label="Close category products">
                &times;
            </button>
        </header>

        <div class="category-modal-toolbar">
            <div class="category-search-wrap">
                <span class="search-icon" aria-hidden="true">&#9906;</span>

                <input
                    type="search"
                    id="categoryProductsSearch"
                    class="category-search-input"
                    placeholder="Search product, code, brand, type..."
                    autocomplete="off"
                    aria-label="Search category products">
            </div>

            <div class="category-filter-group">
                <label for="categoryProductsStatus">Status</label>

                <select id="categoryProductsStatus" class="category-filter-select">
                    <option value="all">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="category-filter-group">
                <label for="categoryProductsSort">Sort by</label>

                <select id="categoryProductsSort" class="category-filter-select">
                    <option value="name_asc">Product Name: A–Z</option>
                    <option value="name_desc">Product Name: Z–A</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

            <span id="categoryProductsResultCount" class="category-result-count" aria-live="polite"></span>
        </div>

        <div class="table-wrap category-modal-table-wrap">
            <table class="category-products-table category-modal-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="categoryProductsModalBody">
                    @foreach ($products as $product)
                        @php
                            $productStatus = strtolower((string) $product->status);
                        @endphp

                        <tr
                            class="category-modal-product-row"
                            data-name="{{ $product->product_name ?? '' }}"
                            data-code="{{ $product->product_code ?? '' }}"
                            data-brand="{{ $product->brand?->brand_name ?? '' }}"
                            data-type="{{ $product->product_type ?? '' }}"
                            data-status="{{ in_array($productStatus, ['active', 'inactive'], true) ? $productStatus : 'other' }}"
                            data-price="{{ (float) ($product->price ?? 0) }}"
                            data-created="{{ $product->created_at?->timestamp ?? 0 }}">
                            <td>
                                <div class="category-product-cell">
                                    <span class="product-icon" aria-hidden="true">P</span>

                                    <div class="category-product-info">
                                        <span class="modal-product-name">
                                            {{ $product->product_name ?: 'Unnamed Product' }}
                                        </span>
                                        <span class="product-code">{{ $product->product_code ?: 'No product code' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="table-primary-text">{{ $product->brand?->brand_name ?? '-' }}</span>
                            </td>

                            <td>
                                <span class="table-secondary-text">{{ $product->product_type ?: '-' }}</span>
                            </td>

                            <td>
                                @if ($product->price !== null)
                                    <strong class="product-price">
                                        Rp {{ number_format((float) $product->price, 0, ',', '.') }}
                                    </strong>
                                @else
                                    <span class="table-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="product-status-badge {{ $productStatus === 'active' ? 'is-active' : ($productStatus === 'inactive' ? 'is-inactive' : 'is-other') }}">
                                    <span class="status-indicator"></span>
                                    {{ ucfirst($productStatus ?: 'Unknown') }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('products.show', $product) }}" class="action-view-button">
                                    <span>View</span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div id="categoryProductsNoResults" class="category-no-results" hidden>
                <div class="empty-icon" aria-hidden="true">&#9906;</div>
                <strong>No Matching Products</strong>
                <p>Try another keyword or change the selected filters.</p>
                <button type="button" class="btn" id="resetCategoryProductFilters">Reset Filters</button>
            </div>
        </div>

        <footer class="category-modal-footer">
            <span id="categoryProductsPaginationInfo" aria-live="polite"></span>

            <div class="category-pagination-actions">
                <button type="button" class="btn" id="categoryProductsPrev" disabled>
                    Previous
                </button>

                <span id="categoryProductsPageNumber">1 / 1</span>

                <button type="button" class="btn" id="categoryProductsNext" disabled>
                    Next
                </button>
            </div>
        </footer>
    </section>
</div>

<style>
    .category-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .category-information-card,
    .category-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .category-information-card .card-body,
    .category-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .category-identity {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 20px;
        margin-bottom: 20px;
        border-bottom: 1px solid #edf0f5;
    }

    .category-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 50px;
        width: 50px;
        height: 50px;
        border-radius: 11px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 17px;
        font-weight: 700;
    }

    .category-identity-content {
        flex: 1;
        min-width: 0;
    }

    .category-identity-content h2 {
        margin: 0 0 5px;
        overflow-wrap: anywhere;
        color: #17284f;
        font-size: 18px;
        font-weight: 700;
    }

    .category-identity-content span {
        color: #7d8797;
        font-size: 11px;
    }

    .category-status {
        flex-shrink: 0;
    }

    .category-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 6px;
        background: #eef3fa;
        color: #344d78;
        font-size: 10px;
        font-weight: 700;
    }

    .category-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 28px;
    }

    .info-item {
        min-width: 0;
    }

    .info-label {
        display: block;
        margin-bottom: 6px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .info-item strong {
        display: block;
        overflow-wrap: anywhere;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
    }

    .category-description {
        padding-top: 20px;
        margin-top: 22px;
        border-top: 1px solid #edf0f5;
    }

    .description-heading {
        margin-bottom: 9px;
        color: #34415c;
        font-size: 12px;
        font-weight: 700;
    }

    .category-description > p {
        margin: 0;
        color: #596579;
        font-size: 12px;
        line-height: 1.8;
        overflow-wrap: anywhere;
    }

    .description-empty {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 13px;
        border: 1px dashed #dfe4eb;
        border-radius: 9px;
        background: #fafbfd;
    }

    .description-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 13px;
        font-weight: 700;
    }

    .description-empty strong,
    .description-empty span {
        display: block;
    }

    .description-empty strong {
        margin-bottom: 4px;
        color: #596579;
        font-size: 11px;
    }

    .description-empty div span {
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
    }

    .category-summary {
        display: flex;
        flex-direction: column;
    }

    .summary-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 0;
    }

    .summary-item + .summary-item {
        border-top: 1px solid #edf0f5;
    }

    .summary-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 9px;
        background: #f1f6f7;
        color: #167d70;
        font-size: 12px;
        font-weight: 700;
    }

    .summary-content {
        min-width: 0;
    }

    .summary-content > span {
        display: block;
        margin-bottom: 4px;
        color: #8a94a6;
        font-size: 10px;
    }

    .summary-content strong {
        display: block;
        margin-bottom: 3px;
        color: #17284f;
        font-size: 20px;
        font-weight: 700;
    }

    .summary-content small {
        display: block;
        color: #9aa3b2;
        font-size: 10px;
        line-height: 1.5;
    }

    .category-create-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        width: 100%;
        margin-top: 14px;
        text-align: center;
    }

    .category-products-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .category-products-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .category-products-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-count {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 10px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #596579;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .btn-show-all {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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

    .category-new-product-button {
        white-space: nowrap;
    }

    .no-padding {
        padding: 0 !important;
    }

    .category-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .category-products-table {
        width: 100%;
        min-width: 790px;
        border-collapse: collapse;
    }

    .category-products-table th {
        padding: 13px 15px;
        border-bottom: 1px solid #e6eaf0;
        background: #fafbfd;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .category-products-table td {
        padding: 13px 15px;
        border-bottom: 1px solid #edf0f5;
        color: #596579;
        font-size: 11px;
        vertical-align: middle;
    }

    .category-products-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .category-products-table tbody tr:hover {
        background: #fafbfd;
    }

    .category-product-cell {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 165px;
    }

    .product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 12px;
        font-weight: 700;
    }

    .category-product-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .product-name-link {
        max-width: 250px;
        overflow: hidden;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-name-link:hover {
        color: #167d70;
    }

    .modal-product-name {
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .product-code {
        color: #8a94a6;
        font-size: 10px;
        overflow-wrap: anywhere;
    }

    .table-primary-text {
        color: #34415c;
        font-size: 11px;
    }

    .table-secondary-text {
        color: #596579;
        font-size: 11px;
    }

    .table-muted {
        color: #9aa3b2;
        font-size: 11px;
    }

    .product-price {
        color: #17284f;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .product-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .product-status-badge.is-active {
        background: #eaf7f3;
        color: #167d70;
    }

    .product-status-badge.is-inactive {
        background: #f1f3f6;
        color: #7d8797;
    }

    .product-status-badge.is-other {
        background: #fff5e8;
        color: #996515;
    }

    .status-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .action-view-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 30px;
        padding: 6px 10px;
        border: 1px solid #d8e8e2;
        border-radius: 6px;
        background: #fff;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease;
    }

    .action-view-button:hover {
        border-color: #15966f;
        background: #f5fbf8;
    }

    .category-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 16px;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 10px;
    }

    .text-button {
        padding: 4px 0;
        border: 0;
        background: transparent;
        color: #167d70;
        font-family: inherit;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
    }

    .text-button:hover {
        text-decoration: underline;
    }

    .category-empty-state {
        padding: 46px 24px;
        text-align: center;
    }

    .empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        margin: 0 auto 12px;
        border-radius: 10px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 15px;
        font-weight: 700;
    }

    .category-empty-state h4 {
        margin: 0 0 6px;
        color: #34415c;
        font-size: 13px;
    }

    .category-empty-state p {
        margin: 0;
        color: #9aa3b2;
        font-size: 11px;
        line-height: 1.6;
    }

    .empty-state-button {
        display: inline-flex;
        margin-top: 16px;
    }

    .category-modal-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(16, 27, 49, .52);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .18s ease, visibility .18s ease;
    }

    .category-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .category-modal {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 1120px;
        max-height: 88vh;
        overflow: hidden;
        border: 1px solid #e6eaf0;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(16, 27, 49, .22);
        transform: translateY(8px);
        transition: transform .18s ease;
    }

    .category-modal-overlay.is-open .category-modal {
        transform: translateY(0);
    }

    .category-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .category-modal-heading {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .category-modal-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 13px;
        font-weight: 700;
    }

    .category-modal-header h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
    }

    .category-modal-header p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .modal-close {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border: 0;
        border-radius: 7px;
        background: #f1f3f6;
        color: #596579;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        transition: background .15s ease;
    }

    .modal-close:hover {
        background: #e6eaf0;
    }

    .category-modal-toolbar {
        display: grid;
        grid-template-columns: minmax(200px, 1fr) minmax(130px, 170px) minmax(180px, 210px) auto;
        align-items: end;
        gap: 12px;
        padding: 16px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .category-search-wrap {
        position: relative;
        min-width: 0;
    }

    .search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #8a94a6;
        font-size: 19px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .category-search-input,
    .category-filter-select {
        width: 100%;
        min-width: 0;
        min-height: 39px;
        padding: 9px 12px;
        border: 1px solid #dfe4eb;
        border-radius: 7px;
        outline: none;
        background: #fff;
        color: #34415c;
        font-family: inherit;
        font-size: 11px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .category-search-input {
        padding-left: 36px;
    }

    .category-search-input:focus,
    .category-filter-select:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .1);
    }

    .category-filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
    }

    .category-filter-group label {
        color: #8a94a6;
        font-size: 10px;
        font-weight: 600;
    }

    .category-filter-select {
        cursor: pointer;
    }

    .category-result-count {
        padding-bottom: 11px;
        color: #8a94a6;
        font-size: 10px;
        white-space: nowrap;
    }

    .category-modal-table-wrap {
        flex: 1;
        min-height: 100px;
        overflow: auto;
    }

    .category-modal-table {
        min-width: 790px;
    }

    .category-modal-table thead {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .category-modal-table th {
        padding: 12px 14px;
    }

    .category-modal-table td {
        padding: 12px 14px;
    }

    .category-modal-table tr[hidden] {
        display: none !important;
    }

    .category-no-results {
        padding: 35px 20px;
        color: #8a94a6;
        text-align: center;
    }

    .category-no-results[hidden] {
        display: none !important;
    }

    .category-no-results strong {
        display: block;
        margin-bottom: 6px;
        color: #34415c;
        font-size: 12px;
    }

    .category-no-results p {
        margin: 0 0 15px;
        font-size: 11px;
        line-height: 1.6;
    }

    .category-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 22px;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 10px;
    }

    .category-pagination-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .category-pagination-actions .btn {
        min-height: 32px;
        padding: 7px 11px;
        font-size: 11px;
    }

    .category-pagination-actions button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    #categoryProductsPageNumber {
        min-width: 48px;
        color: #596579;
        text-align: center;
    }

    @media (max-width: 1100px) {
        .category-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .category-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .summary-item {
            align-items: flex-start;
            padding: 12px 0;
        }

        .summary-item + .summary-item {
            border-top: 0;
        }

        .category-create-button {
            margin-top: 12px;
        }

        .category-modal-toolbar {
            grid-template-columns: minmax(180px, 1fr) minmax(130px, 160px);
        }

        .category-result-count {
            padding-bottom: 0;
        }
    }

    @media (max-width: 700px) {
        .category-products-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .category-products-actions {
            justify-content: flex-start;
            width: 100%;
        }

        .category-summary {
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
        }

        .summary-item + .summary-item {
            border-top: 1px solid #edf0f5;
        }

        .category-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .category-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .category-status {
            margin-left: 0;
        }

        .category-info-grid {
            gap: 18px 16px;
        }

        .category-modal-overlay {
            padding: 8px;
        }

        .category-modal {
            max-height: 94vh;
            border-radius: 10px;
        }

        .category-modal-header {
            padding: 15px;
        }

        .category-modal-toolbar {
            grid-template-columns: minmax(0, 1fr);
            padding: 14px 15px;
        }

        .category-result-count {
            padding-bottom: 0;
        }

        .category-modal-footer {
            align-items: stretch;
            flex-direction: column;
            padding: 13px 15px;
        }

        .category-pagination-actions {
            justify-content: space-between;
        }

        .category-table-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        .category-info-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .category-products-actions {
            align-items: flex-start;
        }

        .category-new-product-button {
            white-space: normal;
        }

        .category-modal-heading {
            align-items: flex-start;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .category-modal,
        .category-modal-overlay,
        .btn-show-all,
        .action-view-button,
        .modal-close {
            transition: none;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('categoryProductsModal');

        if (!modal) {
            return;
        }

        const dialog = modal.querySelector('[role="dialog"]');
        const openButton = document.getElementById('openCategoryProductsButton');
        const closeButton = document.getElementById('closeCategoryProductsButton');
        const searchInput = document.getElementById('categoryProductsSearch');
        const statusFilter = document.getElementById('categoryProductsStatus');
        const sortSelect = document.getElementById('categoryProductsSort');
        const tableBody = document.getElementById('categoryProductsModalBody');
        const table = tableBody.closest('table');
        const noResults = document.getElementById('categoryProductsNoResults');
        const resultCount = document.getElementById('categoryProductsResultCount');
        const paginationInfo = document.getElementById('categoryProductsPaginationInfo');
        const pageNumber = document.getElementById('categoryProductsPageNumber');
        const previousButton = document.getElementById('categoryProductsPrev');
        const nextButton = document.getElementById('categoryProductsNext');
        const resetButton = document.getElementById('resetCategoryProductFilters');

        const allRows = Array.from(
            tableBody.querySelectorAll('.category-modal-product-row')
        );

        const pageSize = 10;

        let currentPage = 1;
        let filteredRows = [...allRows];
        let previousFocusedElement = null;

        function getSortValue(row, key) {
            return Number(row.dataset[key] || 0);
        }

        function applyFilters() {
            const query = searchInput.value.trim().toLocaleLowerCase();
            const selectedStatus = statusFilter.value;
            const sortBy = sortSelect.value;

            filteredRows = allRows.filter(function (row) {
                const searchableText = [
                    row.dataset.name,
                    row.dataset.code,
                    row.dataset.brand,
                    row.dataset.type,
                    row.textContent
                ].join(' ').toLocaleLowerCase();

                const matchesSearch = searchableText.includes(query);
                const matchesStatus = selectedStatus === 'all' ||
                    row.dataset.status === selectedStatus;

                return matchesSearch && matchesStatus;
            });

            filteredRows.sort(function (a, b) {
                const nameA = (a.dataset.name || '').toLocaleLowerCase();
                const nameB = (b.dataset.name || '').toLocaleLowerCase();
                const priceA = getSortValue(a, 'price');
                const priceB = getSortValue(b, 'price');
                const createdA = getSortValue(a, 'created');
                const createdB = getSortValue(b, 'created');

                switch (sortBy) {
                    case 'name_desc':
                        return nameB.localeCompare(nameA);

                    case 'price_desc':
                        return priceB - priceA;

                    case 'price_asc':
                        return priceA - priceB;

                    case 'newest':
                        return createdB - createdA;

                    case 'oldest':
                        return createdA - createdB;

                    case 'name_asc':
                    default:
                        return nameA.localeCompare(nameB);
                }
            });

            currentPage = 1;
            renderItems();
        }

        function renderItems() {
            const total = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / pageSize));

            currentPage = Math.min(Math.max(currentPage, 1), totalPages);

            const start = (currentPage - 1) * pageSize;
            const end = Math.min(start + pageSize, total);
            const visibleRows = filteredRows.slice(start, end);

            allRows.forEach(function (row) {
                row.hidden = true;
            });

            visibleRows.forEach(function (row) {
                row.hidden = false;
                tableBody.appendChild(row);
            });

            table.style.display = total === 0 ? 'none' : '';
            noResults.hidden = total !== 0;

            resultCount.textContent =
                `${total} ${total === 1 ? 'product' : 'products'} found`;

            paginationInfo.textContent = total === 0
                ? 'No products to display'
                : `Showing ${start + 1}–${end} of ${total} products`;

            pageNumber.textContent = `${currentPage} / ${totalPages}`;

            previousButton.disabled = currentPage <= 1 || total === 0;
            nextButton.disabled = currentPage >= totalPages || total === 0;
        }

        function openModal() {
            previousFocusedElement = document.activeElement;

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
            document.body.style.overflow = '';

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

        if (openButton) {
            openButton.addEventListener('click', openModal);
        }

        document.querySelectorAll('[data-open-category-products]').forEach(function (button) {
            button.addEventListener('click', openModal);
        });

        closeButton.addEventListener('click', closeModal);
        searchInput.addEventListener('input', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        sortSelect.addEventListener('change', applyFilters);

        previousButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderItems();
            }
        });

        nextButton.addEventListener('click', function () {
            const totalPages = Math.max(
                1,
                Math.ceil(filteredRows.length / pageSize)
            );

            if (currentPage < totalPages) {
                currentPage++;
                renderItems();
            }
        });

        resetButton.addEventListener('click', resetFilters);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                closeModal();
            }
        });

        modal.addEventListener('keydown', function (event) {
            if (event.key !== 'Tab' || !modal.classList.contains('is-open')) {
                return;
            }

            const focusableElements = Array.from(
                dialog.querySelectorAll(
                    'button:not(:disabled), input:not(:disabled), select:not(:disabled), a[href], [tabindex]:not([tabindex="-1"])'
                )
            ).filter(function (element) {
                return !element.hidden && element.getClientRects().length > 0;
            });

            if (focusableElements.length === 0) {
                event.preventDefault();
                dialog.focus();
                return;
            }

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === firstElement) {
                event.preventDefault();
                lastElement.focus();
            } else if (!event.shiftKey && document.activeElement === lastElement) {
                event.preventDefault();
                firstElement.focus();
            }
        });

        renderItems();
    });
</script>

@endsection