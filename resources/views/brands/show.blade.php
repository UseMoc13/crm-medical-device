@extends('layouts.app')

@section('title', 'Brand Details')

@section('content')

<div class="page-head">

    <div>
        <h1>Brand Details</h1>

        <p>
            View brand information and products associated with this brand.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('brands.index') }}"
            class="btn"
        >
            ← Back to Brands
        </a>

        <a
            href="{{ route('brands.edit', $brand) }}"
            class="btn primary"
        >
            Edit Brand
        </a>

    </div>

</div>


@if(session('success'))

    <div class="alert success">
        {{ session('success') }}
    </div>

@endif


@if(session('error'))

    <div class="alert error">
        {{ session('error') }}
    </div>

@endif


{{-- =========================================================
     BRAND OVERVIEW
========================================================= --}}

<div class="brand-overview-grid">


    {{-- Brand Information --}}

    <div class="card brand-information-card">

        <div class="card-head">

            <div>

                <h3>Brand Information</h3>

                <p>
                    Basic information about this product brand.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="brand-profile">

                <div class="brand-profile-icon">
                    B
                </div>

                <div class="brand-profile-content">

                    <h2>
                        {{ $brand->brand_name }}
                    </h2>

                    <span>
                        Product Brand
                    </span>

                </div>

            </div>


            <div class="brand-info-grid">

                <div class="detail-item">

                    <span>
                        Brand Name
                    </span>

                    <strong>
                        {{ $brand->brand_name }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Products
                    </span>

                    <strong>
                        {{ $products->total() }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Created
                    </span>

                    <strong>
                        {{ optional($brand->created_at)->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Last Updated
                    </span>

                    <strong>
                        {{ optional($brand->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- Brand Summary --}}

    <div class="card brand-summary-card">

        <div class="card-head">

            <div>

                <h3>Brand Summary</h3>

                <p>
                    Quick overview of this brand.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="summary-list">


                <div class="summary-item">

                    <div class="summary-label">
                        Brand
                    </div>

                    <div class="summary-value">
                        {{ $brand->brand_name }}
                    </div>

                </div>


                <div class="summary-item">

                    <div class="summary-label">
                        Total Products
                    </div>

                    <div class="summary-value highlight">
                        {{ $products->total() }}
                    </div>

                </div>


                <div class="summary-item">

                    <div class="summary-label">
                        Status
                    </div>

                    <div class="summary-value">

                        <span class="status-badge">
                            Active
                        </span>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     DESCRIPTION
========================================================= --}}

<div class="card brand-description-card">

    <div class="card-head">

        <div>

            <h3>Description</h3>

            <p>
                Additional information about this brand.
            </p>

        </div>

    </div>


    <div class="card-body">

        @if($brand->description)

            <p class="brand-description">
                {{ $brand->description }}
            </p>

        @else

            <p class="brand-description muted">
                No description provided.
            </p>

        @endif

    </div>

</div>


{{-- =========================================================
     PRODUCTS
========================================================= --}}

<div class="card brand-products-card">

    <div class="card-head">

        <div>

            <h3>Products</h3>

            <p>
                Products associated with this brand.
            </p>

        </div>


        <div class="section-count">

            {{ $products->total() }}

            {{ $products->total() == 1 ? 'product' : 'products' }}

        </div>

    </div>


    <div class="card-body no-padding">

        @if($products->count())

            <div class="table-wrap">

                <table class="brand-products-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Product Code
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Price
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($products as $product)

                            <tr>


                                {{-- Product --}}

                                <td>

                                    <div class="product-name-cell">

                                        <strong
                                            class="product-name"
                                            title="{{ $product->product_name }}"
                                        >
                                            {{ $product->product_name }}
                                        </strong>

                                        <span>
                                            {{ $product->product_id }}
                                        </span>

                                    </div>

                                </td>


                                {{-- Product Code --}}

                                <td>

                                    <span class="product-code">

                                        {{ $product->product_code ?? '-' }}

                                    </span>

                                </td>


                                {{-- Category --}}

                                <td>

                                    @if($product->category)

                                        {{ $product->category->category_name }}

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- Type --}}

                                <td>

                                    {{ $product->product_type ?? '-' }}

                                </td>


                                {{-- Price --}}

                                <td>

                                    @if($product->price !== null)

                                        Rp {{ number_format($product->price, 0, ',', '.') }}

                                    @else

                                        -

                                    @endif

                                </td>


                                {{-- Status --}}

                                <td>

                                    @if($product->status)

                                        <span
                                            class="product-status
                                            {{ strtolower($product->status) === 'active'
                                                ? 'active'
                                                : 'inactive' }}"
                                        >
                                            {{ ucfirst($product->status) }}
                                        </span>

                                    @else

                                        <span class="product-status inactive">
                                            -
                                        </span>

                                    @endif

                                </td>


                                {{-- Created --}}

                                <td>

                                    {{ optional($product->created_at)->format('d M Y') ?? '-' }}

                                </td>


                                {{-- Actions --}}

                                <td>

                                    <div class="table-actions">

                                        <a
                                            href="{{ route('products.show', $product) }}"
                                            class="action-btn view"
                                            title="View Product"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route('products.edit', $product) }}"
                                            class="action-btn edit"
                                            title="Edit Product"
                                        >
                                            Edit
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}

            <div class="pagination">

                {{ $products->links() }}

            </div>


        @else

            <div class="empty-state">

                <div class="empty-state-icon">
                    P
                </div>

                <h4>
                    No Products Found
                </h4>

                <p>
                    There are currently no products associated with this brand.
                </p>

            </div>

        @endif

    </div>

</div>


<style>

/* =========================================================
   BRAND OVERVIEW
========================================================= */

.brand-overview-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1.55fr)
        minmax(320px, .85fr);

    gap: 18px;

    align-items: stretch;

    margin-bottom: 18px;

}


.brand-information-card,
.brand-summary-card {

    display: flex;

    flex-direction: column;

    min-width: 0;

}


.brand-information-card .card-body,
.brand-summary-card .card-body {

    flex: 1;

}


/* =========================================================
   BRAND PROFILE
========================================================= */

.brand-profile {

    display: flex;

    align-items: center;

    gap: 15px;

    margin-bottom: 20px;

    padding-bottom: 18px;

    border-bottom: 1px solid #edf0f5;

}


.brand-profile-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 54px;

    height: 54px;

    flex-shrink: 0;

    border-radius: 13px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 19px;

    font-weight: 700;

}


.brand-profile-content {

    min-width: 0;

}


.brand-profile h2 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 19px;

    font-weight: 700;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.brand-profile span {

    color: #7d8797;

    font-size: 11px;

}


/* =========================================================
   BRAND INFORMATION GRID
========================================================= */

.brand-info-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 14px;

}


.detail-item {

    min-width: 0;

    padding: 13px 15px;

    border: 1px solid #e7ebf1;

    border-radius: 9px;

    background: #fafbfd;

}


.detail-item span {

    display: block;

    margin-bottom: 6px;

    color: #8a94a6;

    font-size: 10px;

}


.detail-item strong {

    display: block;

    color: #34415c;

    font-size: 13px;

    font-weight: 600;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


/* =========================================================
   BRAND SUMMARY
========================================================= */

.summary-list {

    display: flex;

    flex-direction: column;

}


.summary-item {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 18px;

    min-width: 0;

    padding: 15px 0;

    border-bottom: 1px solid #edf0f5;

}


.summary-item:first-child {

    padding-top: 3px;

}


.summary-item:last-child {

    border-bottom: 0;

    padding-bottom: 3px;

}


.summary-label {

    color: #8a94a6;

    font-size: 11px;

}


.summary-value {

    min-width: 0;

    color: #34415c;

    font-size: 13px;

    font-weight: 600;

    text-align: right;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.summary-value.highlight {

    color: #167d70;

    font-size: 16px;

}


.status-badge {

    display: inline-flex;

    align-items: center;

    padding: 5px 9px;

    border-radius: 999px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 10px;

    font-weight: 600;

}


/* =========================================================
   DESCRIPTION
========================================================= */

.brand-description-card {

    margin-bottom: 18px;

}


.brand-description {

    margin: 0;

    color: #718096;

    font-size: 13px;

    line-height: 1.7;

    white-space: pre-line;

    overflow-wrap: anywhere;

}


.brand-description.muted {

    color: #a0a8b6;

}


/* =========================================================
   PRODUCTS
========================================================= */

.brand-products-card {

    overflow: hidden;

}


.brand-products-table {

    width: 100%;

    min-width: 1100px;

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

    padding: 14px 15px;

    border-bottom: 1px solid #edf0f5;

    color: #526078;

    font-size: 12px;

    vertical-align: middle;

}


.brand-products-table tbody tr:last-child td {

    border-bottom: 0;

}


.brand-products-table tbody tr:hover {

    background: #fafcfe;

}


/* =========================================================
   PRODUCT NAME
========================================================= */

.product-name-cell {

    display: flex;

    flex-direction: column;

    gap: 3px;

    min-width: 0;

    max-width: 220px;

}


.product-name {

    display: block;

    color: #34415c;

    font-size: 12px;

    font-weight: 600;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.product-name-cell span {

    color: #a0a8b6;

    font-size: 10px;

    white-space: nowrap;

}


.product-code {

    color: #526078;

    font-size: 11px;

    white-space: nowrap;

}


/* =========================================================
   PRODUCT STATUS
========================================================= */

.product-status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 5px 9px;

    border-radius: 999px;

    font-size: 10px;

    font-weight: 600;

    white-space: nowrap;

}


.product-status.active {

    background: #eaf7f3;

    color: #167d70;

}


.product-status.inactive {

    background: #f1f3f6;

    color: #7d8797;

}


/* =========================================================
   TABLE ACTIONS
========================================================= */

.table-actions {

    display: flex;

    align-items: center;

    gap: 6px;

    white-space: nowrap;

}


.action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 48px;

    height: 29px;

    padding: 0 9px;

    border: 1px solid #e2e7ef;

    border-radius: 6px;

    background: #fff;

    color: #667085;

    font-size: 10px;

    font-weight: 600;

    text-decoration: none;

    transition: .15s ease;

}


.action-btn:hover {

    background: #f7f9fc;

    color: #17284f;

}


.action-btn.edit:hover {

    border-color: #cfe8df;

    background: #eaf7f3;

    color: #167d70;

}


/* =========================================================
   SECTION COUNT
========================================================= */

.section-count {

    flex-shrink: 0;

    color: #8a94a6;

    font-size: 11px;

    font-weight: 600;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    padding: 55px 20px;

    text-align: center;

}


.empty-state-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 44px;

    height: 44px;

    margin-bottom: 12px;

    border-radius: 11px;

    background: #f1f4f8;

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

    color: #9aa3b1;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {

    .brand-overview-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .brand-info-grid {

        grid-template-columns: 1fr;

    }


    .brand-profile h2 {

        max-width: 220px;

    }


    .summary-item {

        gap: 10px;

    }

}

</style>

@endsection