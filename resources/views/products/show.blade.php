@extends('layouts.app')

@section('title', 'Product Details')

@section('content')

<div class="page-head">

    <div>

        <h1>Product Details</h1>

        <p>
            View product information, commercial details, and related opportunity items.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('products.index') }}"
            class="btn">
            ← Back to Products
        </a>

        <a
            href="{{ route('products.edit', $product) }}"
            class="btn primary">
            Edit Product
        </a>

    </div>

</div>


{{-- =========================================================
ALERT
========================================================= --}}

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
PRODUCT OVERVIEW
========================================================= --}}

<div class="product-overview-grid">


    {{-- =====================================================
         PRODUCT INFORMATION
    ====================================================== --}}

    <div class="card product-information-card">

        <div class="card-head">

            <div>

                <h3>Product Information</h3>

                <p>
                    Basic information and classification of this product.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="product-identity">

                <div class="product-icon">
                    P
                </div>


                <div class="product-identity-content">

                    <h2>
                        {{ $product->product_name }}
                    </h2>

                    <span>
                        {{ $product->product_code }}
                    </span>

                </div>


                <div class="product-status">

                    @if($product->status === 'active')

                    <span class="status-badge active">
                        Active
                    </span>

                    @else

                    <span class="status-badge inactive">
                        Inactive
                    </span>

                    @endif

                </div>

            </div>


            <div class="product-info-grid">


                {{-- Product Code --}}

                <div class="info-item">

                    <span class="info-label">
                        Product Code
                    </span>

                    <strong>
                        {{ $product->product_code ?: '-' }}
                    </strong>

                </div>


                {{-- Product Name --}}

                <div class="info-item">

                    <span class="info-label">
                        Product Name
                    </span>

                    <strong>
                        {{ $product->product_name ?: '-' }}
                    </strong>

                </div>


                {{-- Category --}}

                <div class="info-item">

                    <span class="info-label">
                        Category
                    </span>

                    <strong>
                        {{ $product->category?->category_name ?? '-' }}
                    </strong>

                </div>


                {{-- Brand --}}

                <div class="info-item">

                    <span class="info-label">
                        Brand
                    </span>

                    <strong>
                        {{ $product->brand?->brand_name ?? '-' }}
                    </strong>

                </div>


                {{-- Product Type --}}

                <div class="info-item">

                    <span class="info-label">
                        Product Type
                    </span>

                    <strong>
                        {{ $product->product_type ?: '-' }}
                    </strong>

                </div>


                {{-- Unit --}}

                <div class="info-item">

                    <span class="info-label">
                        Unit
                    </span>

                    <strong>
                        {{ $product->unit ?: '-' }}
                    </strong>

                </div>


                {{-- Created --}}

                <div class="info-item">

                    <span class="info-label">
                        Created
                    </span>

                    <strong>
                        {{ $product->created_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>


                {{-- Updated --}}

                <div class="info-item">

                    <span class="info-label">
                        Updated
                    </span>

                    <strong>
                        {{ $product->updated_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         COMMERCIAL SUMMARY
    ====================================================== --}}

    <div class="card commercial-summary-card">

        <div class="card-head">

            <div>

                <h3>Commercial Summary</h3>

                <p>
                    Pricing and warranty information.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="commercial-summary">


                {{-- Price --}}

                <div class="commercial-item">

                    <div class="commercial-item-icon">
                        Rp
                    </div>

                    <div>

                        <span>
                            Product Price
                        </span>

                        <strong>
                            Rp {{ number_format((float) ($product->price ?? 0), 2, ',', '.') }}
                        </strong>

                        <small>
                            Base selling price
                        </small>

                    </div>

                </div>


                {{-- Warranty --}}

                <div class="commercial-item">

                    <div class="commercial-item-icon">
                        W
                    </div>

                    <div>

                        <span>
                            Warranty Period
                        </span>

                        <strong>
                            {{ $product->warranty_period ?? 0 }}
                            Months
                        </strong>

                        <small>
                            Product warranty duration
                        </small>

                    </div>

                </div>


                {{-- Product Status --}}

                <div class="commercial-item">

                    <div class="commercial-item-icon">
                        ✓
                    </div>

                    <div>

                        <span>
                            Product Status
                        </span>

                        @if($product->status === 'active')

                        <strong class="commercial-status active">
                            Active
                        </strong>

                        @else

                        <strong class="commercial-status inactive">
                            Inactive
                        </strong>

                        @endif

                        <small>
                            Current product availability
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
PRODUCT SPECIFICATION
========================================================= --}}

<div class="card specification-card">

    <div class="card-head">

        <div>

            <h3>Product Specification</h3>

            <p>
                Technical specification and additional product information.
            </p>

        </div>

    </div>


    <div class="card-body">

        @if($product->specification)

        <div class="specification-box">

            {!! nl2br(e($product->specification)) !!}

        </div>

        @else

        <div class="specification-empty">

            <span class="specification-empty-icon">
                P
            </span>

            <div>

                <strong>
                    No specification provided
                </strong>

                <p>
                    Technical specification has not been added for this product.
                </p>

            </div>

        </div>

        @endif

    </div>

</div>


{{-- =========================================================
OPPORTUNITY ITEMS
========================================================= --}}

<div class="card opportunity-items-card">

    <div class="card-head">

        <div>

            <h3>Opportunity Items</h3>

            <p>
                Opportunities where this product has been included.
            </p>

        </div>


        <div class="section-count">

            {{ $product->opportunityItems->count() }}

            {{ $product->opportunityItems->count() == 1
                ? 'item'
                : 'items'
            }}

        </div>

    </div>


    <div class="card-body no-padding">


        @if($product->opportunityItems->count())


        <div class="table-wrap opportunity-table-wrap">

            <table class="opportunity-table">

                <thead>

                    <tr>

                        <th>
                            Opportunity
                        </th>

                        <th>
                            Quantity
                        </th>

                        <th>
                            Estimated Price
                        </th>

                        <th>
                            Notes
                        </th>

                        <th>
                            Created
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($product->opportunityItems as $item)

                    <tr>


                        {{-- Opportunity --}}

                        <td>

                            @if($item->opportunity)

                            <a
                                href="{{ route(
                                    'opportunities.show',
                                    $item->opportunity
                                ) }}"
                                class="table-link">

                                {{ $item->opportunity->opportunity_name ?? 'Opportunity' }}

                            </a>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- Quantity --}}

                        <td>

                            <span class="quantity-value">
                                {{ $item->quantity }}
                            </span>

                        </td>


                        {{-- Estimated Price --}}

                        <td>

                            <span class="price-value">

                                Rp
                                {{ number_format(
                                    (float) ($item->estimated_price ?? 0),
                                    2,
                                    ',',
                                    '.'
                                ) }}

                            </span>

                        </td>


                        {{-- Notes --}}

                        <td>

                            @if($item->notes)

                            <div
                                class="opportunity-notes"
                                title="{{ $item->notes }}">

                                {{ $item->notes }}

                            </div>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- Created --}}

                        <td>

                            <span class="date-value">

                                {{ $item->created_at?->format('d M Y') ?? '-' }}

                            </span>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>


        @else


        <div class="empty-state">

            <div class="empty-icon">
                P
            </div>

            <h4>
                No Opportunity Items
            </h4>

            <p>
                This product has not been added to any opportunity yet.
            </p>

        </div>


        @endif

    </div>

</div>


<style>

/* =========================================================
   PRODUCT OVERVIEW
========================================================= */

.product-overview-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1.55fr)
        minmax(320px, .85fr);

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

}


/* =========================================================
   PRODUCT IDENTITY
========================================================= */

.product-identity {

    display: flex;

    align-items: center;

    gap: 14px;

    padding-bottom: 18px;

    margin-bottom: 20px;

    border-bottom: 1px solid #edf0f5;

}


.product-icon {

    width: 50px;

    height: 50px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;

}


.product-identity-content {

    min-width: 0;

    flex: 1;

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


/* =========================================================
   INFORMATION GRID
========================================================= */

.product-info-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    column-gap: 28px;

    row-gap: 18px;

}


.info-item {

    min-width: 0;

}


.info-label {

    display: block;

    margin-bottom: 5px;

    color: #8a94a6;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .35px;

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


/* =========================================================
   STATUS
========================================================= */

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


/* =========================================================
   COMMERCIAL SUMMARY
========================================================= */

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


.commercial-item + .commercial-item {

    border-top: 1px solid #edf0f5;

}


.commercial-item-icon {

    width: 38px;

    height: 38px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    background: #f1f6f7;

    color: #167d70;

    font-size: 10px;

    font-weight: 700;

}


.commercial-item > div:last-child {

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


/* =========================================================
   SPECIFICATION
========================================================= */

.specification-card {

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

    word-break: break-word;

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

    width: 36px;

    height: 36px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

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


/* =========================================================
   OPPORTUNITY ITEMS
========================================================= */

.opportunity-items-card {

    min-width: 0;

}


.opportunity-items-card .card-head {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

}


.section-count {

    flex-shrink: 0;

    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #596579;

    font-size: 10px;

    font-weight: 600;

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

    min-width: 900px;

    border-collapse: collapse;

}


.opportunity-table th {

    padding: 11px 16px;

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


/* =========================================================
   OPPORTUNITY DATA
========================================================= */

.table-link {

    display: block;

    max-width: 280px;

    overflow: hidden;

    color: #167d70;

    font-weight: 600;

    text-decoration: none;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.table-link:hover {

    color: #12685e;

    text-decoration: underline;

}


.quantity-value {

    color: #34415c;

    font-weight: 600;

    white-space: nowrap;

}


.price-value {

    color: #17284f;

    font-weight: 600;

    white-space: nowrap;

}


.opportunity-notes {

    max-width: 280px;

    overflow: hidden;

    color: #596579;

    line-height: 1.45;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.date-value {

    color: #718096;

    white-space: nowrap;

}


.muted {

    color: #9aa3b2;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    padding: 45px 24px;

    text-align: center;

}


.empty-icon {

    width: 44px;

    height: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

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


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {

    .product-overview-grid {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 600px) {

    .product-info-grid {

        grid-template-columns: 1fr;

    }


    .product-identity {

        align-items: flex-start;

    }


    .product-status {

        margin-left: auto;

    }


    .product-identity-content h2 {

        white-space: normal;

    }


    .opportunity-items-card .card-head {

        align-items: flex-start;

        flex-direction: column;

    }

}

</style>

@endsection