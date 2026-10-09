@extends('layouts.app')

@section('title', 'Product Details')

@section('content')

@php
$opportunityItems = $product->opportunityItems
->sortByDesc('created_at')
->values();

$itemCount = $opportunityItems->count();
@endphp

<div class="page-head">
    <div>
        <h1>Product Details</h1>
        <p>View product information, commercial details, and related opportunity items.</p>
    </div>

    <div class="actions">
        <a href="{{ route('products.index') }}" class="btn">
            &larr; Back to Products
        </a>

        <a href="{{ route('products.edit', $product) }}" class="btn primary">
            Edit Product
        </a>
    </div>
</div>

@if (session('success'))
<div class="alert success">{{ session('success') }}</div>
@endif

@if (session('error'))
<div class="alert error">{{ session('error') }}</div>
@endif

<div class="product-overview-grid">

    <section class="card product-information-card">
        <div class="card-head">
            <div>
                <h3>Product Information</h3>
                <p>Basic information and classification of this product.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="product-identity">
                <div class="product-icon" aria-hidden="true">P</div>

                <div class="product-identity-content">
                    <h2>{{ $product->product_name ?: 'Unnamed Product' }}</h2>
                    <span>{{ $product->product_code ?: 'No product code' }}</span>
                </div>

                <div class="product-status">
                    @if ($product->status === 'active')
                    <span class="status-badge active">Active</span>
                    @else
                    <span class="status-badge inactive">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="product-info-grid">
                <div class="info-item">
                    <span class="info-label">Product Code</span>
                    <strong>{{ $product->product_code ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Product Name</span>
                    <strong>{{ $product->product_name ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Category</span>
                    <strong>{{ $product->category?->category_name ?? '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Brand</span>
                    <strong>{{ $product->brand?->brand_name ?? '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Product Type</span>
                    <strong>{{ $product->product_type ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Unit</span>
                    <strong>{{ $product->unit ?: '-' }}</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Created</span>
                    <strong>
                        {{ $product->created_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>

                <div class="info-item">
                    <span class="info-label">Updated</span>
                    <strong>
                        {{ $product->updated_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>
                </div>
            </div>
        </div>
    </section>

    <section class="card commercial-summary-card">
        <div class="card-head">
            <div>
                <h3>Commercial Summary</h3>
                <p>Pricing and warranty information.</p>
            </div>
        </div>

        <div class="card-body">
            <div class="commercial-summary">

                <div class="commercial-item">
                    <div class="commercial-item-icon" aria-hidden="true">Rp</div>

                    <div>
                        <span>Product Price</span>
                        <strong>
                            Rp {{ number_format((float) ($product->price ?? 0), 2, ',', '.') }}
                        </strong>
                        <small>Base selling price</small>
                    </div>
                </div>

                <div class="commercial-item">
                    <div class="commercial-item-icon" aria-hidden="true">W</div>

                    <div>
                        <span>Warranty Period</span>
                        <strong>{{ $product->warranty_period ?? 0 }} Months</strong>
                        <small>Product warranty duration</small>
                    </div>
                </div>

                <div class="commercial-item">
                    <div class="commercial-item-icon" aria-hidden="true">✓</div>

                    <div>
                        <span>Product Status</span>

                        @if ($product->status === 'active')
                        <strong class="commercial-status active">Active</strong>
                        @else
                        <strong class="commercial-status inactive">Inactive</strong>
                        @endif

                        <small>Current product availability</small>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

<section class="card specification-card">
    <div class="card-head">
        <div>
            <h3>Product Specification</h3>
            <p>Technical specification and additional product information.</p>
        </div>
    </div>

    <div class="card-body">
        @if ($product->specification)
        <div class="specification-box">{!! nl2br(e($product->specification)) !!}</div>
        @else
        <div class="specification-empty">
            <span class="specification-empty-icon" aria-hidden="true">P</span>

            <div>
                <strong>No specification provided</strong>
                <p>Technical specification has not been added for this product.</p>
            </div>
        </div>
        @endif
    </div>
</section>

<section class="card opportunity-items-card">
    <div class="card-head opportunity-items-head">
        <div>
            <h3>Opportunity Items</h3>
            <p>Products included in sales opportunities.</p>
        </div>

        <div class="opportunity-header-actions">
            <span class="section-count">
                {{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}
            </span>

            <button class="btn-show-all"
                type="button"
                class="btn"
                id="openProductItemsButton"
                aria-haspopup="dialog"
                aria-controls="productItemsModal">
                Show All
            </button>
        </div>
    </div>

    <div class="card-body no-padding">
        @if ($itemCount > 0)
        <div class="table-wrap opportunity-table-wrap">
            <table class="opportunity-table">
                <thead>
                    <tr>
                        <th>Product / Opportunity</th>
                        <th>Quantity</th>
                        <th>Estimated Price</th>
                        <th>Subtotal</th>
                        <th>Notes</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($opportunityItems->take(5) as $item)
                    <tr>
                        <td>
                            <div class="item-product-cell">
                                <span class="item-product-icon" aria-hidden="true">P</span>

                                <div>
                                    <strong>{{ $product->product_name ?: 'Unnamed Product' }}</strong>
                                    <small>{{ $product->product_code ?: '-' }}</small>

                                    @if ($item->opportunity)
                                    <a
                                        class="item-opportunity-link"
                                        href="{{ route('opportunities.show', $item->opportunity) }}">
                                        {{ $item->opportunity->name
                                                        ?? $item->opportunity->opportunity_name
                                                        ?? 'Opportunity' }}
                                    </a>
                                    @else
                                    <span class="muted">Opportunity unavailable</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="quantity-value">
                                {{ $item->quantity ?? 0 }}
                            </span>
                        </td>

                        <td>
                            <span class="price-value">
                                Rp {{ number_format((float) ($item->estimated_price ?? 0), 0, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            <strong class="subtotal-value">
                                Rp {{ number_format(
                                            (float) ($item->quantity ?? 0)
                                            * (float) ($item->estimated_price ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                            </strong>
                        </td>

                        <td>
                            <div
                                class="opportunity-notes"
                                title="{{ $item->notes ?? '' }}">
                                {{ $item->notes ?: '-' }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($itemCount > 5)
        <div class="items-footer">
            <span>Showing 5 of {{ $itemCount }} items.</span>

            <button
                type="button"
                class="text-button"
                data-open-product-items>
                View all items
            </button>
        </div>
        @endif
        @else
        <div class="empty-state">
            <div class="empty-icon" aria-hidden="true">P</div>
            <h4>No Opportunity Items</h4>
            <p>This product has not been added to any opportunity yet.</p>

            <button
                type="button"
                class="btn empty-state-button"
                data-open-product-items>
                View Items
            </button>
        </div>
        @endif
    </div>
</section>

<div
    class="product-modal-overlay"
    id="productItemsModal"
    aria-hidden="true"
    inert>
    <section
        class="product-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="productItemsModalTitle"
        aria-describedby="productItemsModalDescription"
        tabindex="-1">
        <header class="product-modal-header">
            <div>
                <h3 id="productItemsModalTitle">All Opportunity Items</h3>

                <p id="productItemsModalDescription">
                    {{ $product->product_name ?: 'Unnamed Product' }}
                    &middot; {{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}
                </p>
            </div>

            <button
                type="button"
                class="modal-close"
                id="closeProductItemsButton"
                aria-label="Close opportunity items">
                &times;
            </button>
        </header>

        <div class="product-modal-toolbar">
            <div class="product-items-search-wrap">
                <span class="search-icon" aria-hidden="true">
                    ⌕
                </span>

                <input
                    type="search"
                    id="productItemsSearch"
                    class="product-items-search"
                    placeholder="Search opportunity, product, notes..."
                    autocomplete="off"
                    aria-label="Search opportunity items">
            </div>

            <div class="product-items-filter-wrap">
                <label for="productItemsSort">Sort by</label>

                <select
                    id="productItemsSort"
                    class="product-items-filter"
                    aria-label="Sort opportunity items">
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                    <option value="quantity_desc">Quantity: High to Low</option>
                    <option value="quantity_asc">Quantity: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="price_asc">Price: Low to High</option>
                </select>
            </div>

            <span
                id="productItemsResultCount"
                class="result-count"
                aria-live="polite"></span>
        </div>

        <div class="table-wrap modal-table-wrap">
            <table class="opportunity-table modal-items-table">
                <thead>
                    <tr>
                        <th>Product / Opportunity</th>
                        <th>Quantity</th>
                        <th>Estimated Price</th>
                        <th>Subtotal</th>
                        <th>Notes</th>
                    </tr>
                </thead>

                <tbody id="productItemsModalBody">
                    @foreach ($opportunityItems as $item)
                    <tr
                        class="product-modal-item-row"
                        data-quantity="{{ (float) ($item->quantity ?? 0) }}"
                        data-price="{{ (float) ($item->estimated_price ?? 0) }}"
                        data-created="{{ $item->created_at?->timestamp ?? 0 }}">
                        <td>
                            <div class="modal-product-name">
                                {{ $product->product_name ?: 'Unnamed Product' }}
                            </div>

                            <small class="modal-product-code">
                                {{ $product->product_code ?: '-' }}
                            </small>

                            <div class="modal-opportunity-name">
                                @if ($item->opportunity)
                                <a
                                    class="table-link"
                                    href="{{ route('opportunities.show', $item->opportunity) }}">
                                    {{ $item->opportunity->name
                                                ?? $item->opportunity->opportunity_name
                                                ?? 'Opportunity' }}
                                </a>
                                @else
                                <span class="muted">Opportunity unavailable</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="quantity-value">
                                {{ $item->quantity ?? 0 }}
                            </span>
                        </td>

                        <td>
                            <span class="price-value">
                                Rp {{ number_format((float) ($item->estimated_price ?? 0), 0, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            <strong class="subtotal-value">
                                Rp {{ number_format(
                                        (float) ($item->quantity ?? 0)
                                        * (float) ($item->estimated_price ?? 0),
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                            </strong>
                        </td>

                        <td>
                            <div
                                class="opportunity-notes"
                                title="{{ $item->notes ?? '' }}">
                                {{ $item->notes ?: '-' }}
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div
                id="productItemsNoResults"
                class="modal-no-results"
                hidden>
                <div class="empty-icon" aria-hidden="true">&#9906;</div>
                <strong>No matching items found</strong>
                <p>Try another keyword or change the sorting option.</p>
            </div>
        </div>

        <footer class="product-modal-footer">
            <span id="productItemsPaginationInfo" aria-live="polite"></span>

            <div class="pagination-actions">
                <button
                    type="button"
                    class="btn"
                    id="productItemsPrev"
                    disabled>
                    Previous
                </button>

                <span id="productItemsPageNumber">1 / 1</span>

                <button
                    type="button"
                    class="btn"
                    id="productItemsNext"
                    disabled>
                    Next
                </button>
            </div>
        </footer>
    </section>
</div>

<style>
    .product-overview-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.55fr) minmax(280px, .85fr);
        gap: 18px;
        align-items: stretch;
        margin-bottom: 18px;
    }

    .product-information-card,
    .commercial-summary-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .product-information-card .card-body,
    .commercial-summary-card .card-body {
        flex: 1;
        min-width: 0;
    }

    .product-identity {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-bottom: 18px;
        margin-bottom: 20px;
        border-bottom: 1px solid #edf0f5;
    }

    .product-icon {
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

    .product-identity-content {
        flex: 1;
        min-width: 0;
    }

    .product-identity-content h2 {
        margin: 0 0 4px;
        overflow: hidden;
        color: #17284f;
        font-size: 18px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .product-identity-content span {
        color: #7d8797;
        font-size: 11px;
    }

    .product-status {
        flex-shrink: 0;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
    }

    .status-badge.active {
        background: #eaf7f3;
        color: #167d70;
    }

    .status-badge.inactive {
        background: #f1f3f6;
        color: #7d8797;
    }


    .product-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 28px;
    }

    .info-item {
        min-width: 0;
    }

    .info-label {
        display: block;
        margin-bottom: 5px;
        color: #8a94a6;
        font-size: 10px;
        letter-spacing: .35px;
        text-transform: uppercase;
    }

    .info-item strong {
        display: block;
        overflow: hidden;
        color: #34415c;
        font-size: 12px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .commercial-summary {
        display: flex;
        flex-direction: column;
    }

    .commercial-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 0;
    }

    .commercial-item+.commercial-item {
        border-top: 1px solid #edf0f5;
    }

    .commercial-item-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: #f1f6f7;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
    }

    .commercial-item>div:last-child {
        min-width: 0;
    }

    .commercial-item span {
        display: block;
        margin-bottom: 3px;
        color: #8a94a6;
        font-size: 10px;
    }

    .commercial-item strong {
        display: block;
        margin-bottom: 2px;
        color: #17284f;
        font-size: 14px;
        font-weight: 700;
    }

    .commercial-item small {
        color: #9aa3b2;
        font-size: 10px;
    }

    .commercial-status.active {
        color: #167d70;
    }

    .commercial-status.inactive {
        color: #7d8797;
    }

    .specification-card,
    .opportunity-items-card {
        min-width: 0;
        margin-bottom: 18px;
    }

    .specification-box {
        padding: 16px;
        border: 1px solid #e6eaf0;
        border-radius: 9px;
        background: #fafbfd;
        color: #596579;
        font-size: 12px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .specification-empty {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border: 1px dashed #dfe4eb;
        border-radius: 9px;
        background: #fafbfd;
    }

    .specification-empty-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 36px;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f1f3f6;
        color: #8a94a6;
        font-size: 12px;
        font-weight: 700;
    }

    .specification-empty strong {
        display: block;
        margin-bottom: 3px;
        color: #596579;
        font-size: 12px;
    }

    .specification-empty p {
        margin: 0;
        color: #9aa3b2;
        font-size: 10px;
    }

    .opportunity-items-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .opportunity-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
    }

    .section-count {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 10px;
        border-radius: 6px;
        background: #f1f5f7;
        color: #596579;
        font-size: 10px;
        font-weight: 600;
        white-space: nowrap;
    }

    .no-padding {
        padding: 0 !important;
    }

    .opportunity-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .opportunity-table {
        width: 100%;
        min-width: 850px;
        border-collapse: collapse;
    }

    .opportunity-table th {
        padding: 12px 16px;
        border-bottom: 1px solid #e6eaf0;
        background: #fafbfd;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .opportunity-table td {
        padding: 13px 16px;
        border-bottom: 1px solid #edf0f5;
        color: #596579;
        font-size: 11px;
        vertical-align: middle;
    }

    .opportunity-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .opportunity-table tbody tr:hover {
        background: #fafbfd;
    }

    .item-product-cell {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 190px;
    }

    .item-product-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 12px;
        font-weight: 700;
    }

    .item-product-cell>div {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 0;
    }

    .item-product-cell strong,
    .modal-product-name {
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .item-product-cell small,
    .modal-product-code {
        color: #8a94a6;
        font-size: 10px;
    }

    .item-opportunity-link,
    .table-link {
        display: block;
        max-width: 260px;
        overflow: hidden;
        color: #167d70;
        font-size: 10px;
        font-weight: 600;
        text-decoration: none;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .item-opportunity-link:hover,
    .table-link:hover {
        color: #12685e;
        text-decoration: underline;
    }

    .quantity-value,
    .price-value,
    .subtotal-value {
        white-space: nowrap;
    }

    .quantity-value {
        color: #34415c;
        font-weight: 600;
    }

    .price-value {
        color: #17284f;
        font-weight: 600;
    }

    .subtotal-value {
        color: #15966f;
        font-size: 11px;
        font-weight: 700;
    }

    .opportunity-notes {
        max-width: 230px;
        overflow: hidden;
        color: #596579;
        line-height: 1.45;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .muted {
        color: #9aa3b2;
    }

    .items-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 10px;
    }

    .text-button {
        padding: 3px 0;
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

    .empty-state-button {
        margin-top: 14px;
    }

    .empty-state {
        padding: 45px 24px;
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

    .empty-state h4 {
        margin: 0 0 5px;
        color: #34415c;
        font-size: 13px;
    }

    .empty-state p {
        margin: 0;
        color: #9aa3b2;
        font-size: 11px;
    }

    .product-modal-overlay {
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

    .product-modal-overlay.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .product-modal {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 1080px;
        max-height: 88vh;
        overflow: hidden;
        border: 1px solid #e6eaf0;
        border-radius: 13px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(16, 27, 49, .22);
        transform: translateY(8px);
        transition: transform .18s ease;
    }

    .product-modal-overlay.is-open .product-modal {
        transform: translateY(0);
    }

    .product-modal-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .product-modal-header h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 15px;
    }

    .product-modal-header p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .modal-close {
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border: 0;
        border-radius: 7px;
        background: #f1f3f6;
        color: #596579;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
    }

    .modal-close:hover {
        background: #e6eaf0;
    }

    .product-modal-toolbar {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(190px, 240px) auto;
        align-items: end;
        gap: 12px;
        padding: 16px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .product-items-search-wrap {
        position: relative;
        min-width: 0;
    }

    .search-icon {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #8a94a6;
        font-size: 20px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .product-items-search,
    .product-items-filter {
        width: 100%;
        min-width: 0;
        min-height: 38px;
        padding: 9px 12px;
        border: 1px solid #dfe4eb;
        border-radius: 7px;
        outline: none;
        background: #fff;
        color: #34415c;
        font-family: inherit;
        font-size: 11px;
    }

    .product-items-search {
        padding-left: 37px;
    }

    .product-items-search:focus,
    .product-items-filter:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
    }

    .product-items-filter-wrap {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .product-items-filter-wrap label {
        color: #8a94a6;
        font-size: 10px;
        font-weight: 600;
    }

    .product-items-filter {
        cursor: pointer;
    }

    .result-count {
        padding-bottom: 11px;
        color: #8a94a6;
        font-size: 10px;
        white-space: nowrap;
    }

    .modal-table-wrap {
        flex: 1;
        min-height: 100px;
        overflow: auto;
    }

    .modal-items-table {
        min-width: 850px;
    }

    .modal-items-table thead {
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .modal-items-table td {
        padding: 12px 14px;
    }

    .modal-opportunity-name {
        margin-top: 4px;
    }

    .modal-no-results {
        padding: 35px 20px;
        color: #8a94a6;
        font-size: 12px;
        text-align: center;
    }

    .modal-no-results .empty-icon {
        margin-bottom: 12px;
    }

    .modal-no-results strong {
        display: block;
        margin-bottom: 5px;
        color: #34415c;
    }

    .modal-no-results p {
        margin: 0;
        font-size: 11px;
    }

    .modal-no-results[hidden] {
        display: none !important;
    }

    .product-modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 22px;
        border-top: 1px solid #edf0f5;
        color: #8a94a6;
        font-size: 10px;
    }

    .pagination-actions {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .pagination-actions .btn {
        min-height: 32px;
        padding: 7px 11px;
        font-size: 11px;
    }

    .pagination-actions button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    #productItemsPageNumber {
        min-width: 48px;
        color: #596579;
        text-align: center;
    }

    @media (max-width: 950px) {
        .product-overview-grid {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 800px) {
        .product-modal-toolbar {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .result-count {
            grid-column: 1 / -1;
            padding-bottom: 0;
        }
    }

    @media (max-width: 650px) {
        .product-info-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .product-identity {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .product-identity-content {
            flex-basis: calc(100% - 70px);
        }

        .product-identity-content h2 {
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .product-status {
            margin-left: 0;
        }

        .opportunity-items-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .opportunity-header-actions {
            justify-content: flex-start;
        }

        .items-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .product-modal-overlay {
            padding: 8px;
        }

        .product-modal {
            max-height: 94vh;
            border-radius: 10px;
        }

        .product-modal-header {
            padding: 15px;
        }

        .product-modal-toolbar {
            grid-template-columns: minmax(0, 1fr);
            padding: 14px 15px;
        }

        .result-count {
            grid-column: auto;
        }

        .product-modal-footer {
            align-items: stretch;
            flex-direction: column;
            padding: 13px 15px;
        }

        .pagination-actions {
            justify-content: space-between;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .product-modal,
        .product-modal-overlay {
            transition: none;
        }
    }

    .btn-show-all {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 5px 10px;
        border: 1px solid #d8e8e2;
        border-radius: 6px;
        background: #f5fbf8;
        color: #15966f;
        font-family: inherit;
        font-size: 9px;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s ease, border-color .15s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('productItemsModal');

        if (!modal) {
            return;
        }

        const dialog = modal.querySelector('[role="dialog"]');
        const openButton = document.getElementById('openProductItemsButton');
        const closeButton = document.getElementById('closeProductItemsButton');
        const searchInput = document.getElementById('productItemsSearch');
        const sortSelect = document.getElementById('productItemsSort');
        const tableBody = document.getElementById('productItemsModalBody');
        const table = tableBody.closest('table');
        const noResults = document.getElementById('productItemsNoResults');
        const resultCount = document.getElementById('productItemsResultCount');
        const paginationInfo = document.getElementById('productItemsPaginationInfo');
        const pageNumber = document.getElementById('productItemsPageNumber');
        const previousButton = document.getElementById('productItemsPrev');
        const nextButton = document.getElementById('productItemsNext');

        const allRows = Array.from(
            tableBody.querySelectorAll('.product-modal-item-row')
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
            const sortBy = sortSelect.value;

            filteredRows = allRows.filter(function(row) {
                return row.textContent.toLocaleLowerCase().includes(query);
            });

            filteredRows.sort(function(a, b) {
                const quantityA = getSortValue(a, 'quantity');
                const quantityB = getSortValue(b, 'quantity');
                const priceA = getSortValue(a, 'price');
                const priceB = getSortValue(b, 'price');
                const createdA = getSortValue(a, 'created');
                const createdB = getSortValue(b, 'created');

                switch (sortBy) {
                    case 'oldest':
                        return createdA - createdB;

                    case 'quantity_desc':
                        return quantityB - quantityA;

                    case 'quantity_asc':
                        return quantityA - quantityB;

                    case 'price_desc':
                        return priceB - priceA;

                    case 'price_asc':
                        return priceA - priceB;

                    case 'newest':
                    default:
                        return createdB - createdA;
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

            allRows.forEach(function(row) {
                row.hidden = true;
            });

            visibleRows.forEach(function(row) {
                row.hidden = false;
                tableBody.appendChild(row);
            });

            table.style.display = total === 0 ? 'none' : '';
            noResults.hidden = total !== 0;

            resultCount.textContent =
                `${total} ${total === 1 ? 'item' : 'items'} found`;

            paginationInfo.textContent = total === 0 ?
                'No items to display' :
                `Showing ${start + 1}–${end} of ${total} items`;

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
            sortSelect.value = 'newest';

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

            if (previousFocusedElement && typeof previousFocusedElement.focus === 'function') {
                previousFocusedElement.focus();
            }
        }

        if (openButton) {
            openButton.addEventListener('click', openModal);
        }

        document.querySelectorAll('[data-open-product-items]').forEach(function(button) {
            button.addEventListener('click', openModal);
        });

        closeButton.addEventListener('click', closeModal);

        searchInput.addEventListener('input', applyFilters);
        sortSelect.addEventListener('change', applyFilters);

        previousButton.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderItems();
            }
        });

        nextButton.addEventListener('click', function() {
            const totalPages = Math.max(
                1,
                Math.ceil(filteredRows.length / pageSize)
            );

            if (currentPage < totalPages) {
                currentPage++;
                renderItems();
            }
        });

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

        modal.addEventListener('keydown', function(event) {
            if (event.key !== 'Tab' || !modal.classList.contains('is-open')) {
                return;
            }

            const focusableElements = Array.from(
                dialog.querySelectorAll(
                    'button:not(:disabled), input:not(:disabled), select:not(:disabled), a[href], [tabindex]:not([tabindex="-1"])'
                )
            ).filter(function(element) {
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