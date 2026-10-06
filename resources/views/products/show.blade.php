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
            class="btn"
        >
            ← Back to Products
        </a>

        <a
            href="{{ route('products.edit', $product) }}"
            class="btn primary"
        >
            Edit Product
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


<div class="product-show-layout">


    {{-- =====================================================
         PRODUCT INFORMATION
    ====================================================== --}}

    <div class="card">

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

                <div>

                    <h2>
                        {{ $product->product_name }}
                    </h2>

                    <span>
                        {{ $product->product_code }}
                    </span>

                </div>

            </div>


            <div class="info-grid">

                <div class="info-item">

                    <span class="info-label">
                        Product Code
                    </span>

                    <strong>
                        {{ $product->product_code }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Product Name
                    </span>

                    <strong>
                        {{ $product->product_name }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Category
                    </span>

                    <strong>
                        {{ $product->category?->category_name ?? '-' }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Brand
                    </span>

                    <strong>
                        {{ $product->brand?->brand_name ?? '-' }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Product Type
                    </span>

                    <strong>
                        {{ $product->product_type ?: '-' }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Unit
                    </span>

                    <strong>
                        {{ $product->unit ?: '-' }}
                    </strong>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Status
                    </span>

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


                <div class="info-item">

                    <span class="info-label">
                        Created
                    </span>

                    <strong>
                        {{ $product->created_at?->format('d M Y, H:i') ?? '-' }}
                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         COMMERCIAL INFORMATION
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Commercial Information</h3>

                <p>
                    Pricing and warranty information for this product.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="commercial-grid">

                <div class="commercial-card">

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


                <div class="commercial-card">

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

        </div>

    </div>


    {{-- =====================================================
         SPECIFICATION
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Product Specification</h3>

                <p>
                    Technical specification and additional product information.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="specification-box">

                @if($product->specification)

                    {!! nl2br(e($product->specification)) !!}

                @else

                    <span class="empty-text">
                        No specification provided.
                    </span>

                @endif

            </div>

        </div>

    </div>


    {{-- =====================================================
         OPPORTUNITY ITEMS
    ====================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>Opportunity Items</h3>

                <p>
                    Opportunities where this product has been included.
                </p>

            </div>

        </div>


        <div class="card-body no-padding">


            @if($product->opportunityItems->count())

                <div class="table-wrap">

                    <table>

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

                                    <td>

                                        @if($item->opportunity)

                                            <a
                                                href="{{ route('opportunities.show', $item->opportunity) }}"
                                                class="table-link"
                                            >
                                                {{ $item->opportunity->opportunity_name ?? 'Opportunity' }}
                                            </a>

                                        @else

                                            <span class="muted">
                                                -

                                            </span>

                                        @endif

                                    </td>


                                    <td>

                                        {{ $item->quantity }}

                                    </td>


                                    <td>

                                        Rp
                                        {{ number_format((float) ($item->estimated_price ?? 0), 2, ',', '.') }}

                                    </td>


                                    <td>

                                        {{ $item->notes ?: '-' }}

                                    </td>


                                    <td>

                                        {{ $item->created_at?->format('d M Y') ?? '-' }}

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


</div>


<style>

/* =========================================================
   PRODUCT SHOW
========================================================= */

.product-show-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 24px;

    align-items: start;

}


/* =========================================================
   PRODUCT IDENTITY
========================================================= */

.product-identity {

    display: flex;

    align-items: center;

    gap: 14px;

    padding-bottom: 24px;

    margin-bottom: 22px;

    border-bottom: 1px solid #edf0f5;

}

.product-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 52px;

    height: 52px;

    flex-shrink: 0;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 18px;

    font-weight: 700;

}

.product-identity h2 {

    margin: 0 0 5px;

    color: #17284f;

    font-size: 18px;

    font-weight: 700;

}

.product-identity span {

    color: #7d8797;

    font-size: 11px;

}


/* =========================================================
   INFORMATION GRID
========================================================= */

.info-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px 24px;

}

.info-item {

    min-width: 0;

}

.info-label {

    display: block;

    margin-bottom: 7px;

    color: #8a94a6;

    font-size: 11px;

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

    width: fit-content;

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
   COMMERCIAL
========================================================= */

.commercial-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 16px;

}

.commercial-card {

    padding: 18px;

    border: 1px solid #e6eaf0;

    border-radius: 10px;

    background: #fafbfd;

}

.commercial-card span {

    display: block;

    margin-bottom: 8px;

    color: #8a94a6;

    font-size: 11px;

}

.commercial-card strong {

    display: block;

    margin-bottom: 5px;

    color: #17284f;

    font-size: 16px;

    font-weight: 700;

}

.commercial-card small {

    color: #9aa3b2;

    font-size: 10px;

}


/* =========================================================
   SPECIFICATION
========================================================= */

.specification-box {

    min-height: 100px;

    padding: 16px;

    border: 1px solid #e6eaf0;

    border-radius: 9px;

    background: #fafbfd;

    color: #596579;

    font-size: 12px;

    line-height: 1.7;

    white-space: normal;

    word-break: break-word;

}

.empty-text {

    color: #9aa3b2;

    font-style: italic;

}


/* =========================================================
   TABLE
========================================================= */

.no-padding {

    padding: 0 !important;

}

.table-wrap {

    width: 100%;

    overflow-x: auto;

}

table {

    width: 100%;

    border-collapse: collapse;

}

th {

    padding: 12px 16px;

    border-bottom: 1px solid #e6eaf0;

    background: #fafbfd;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    text-align: left;

    white-space: nowrap;

}

td {

    padding: 13px 16px;

    border-bottom: 1px solid #edf0f5;

    color: #596579;

    font-size: 11px;

    vertical-align: middle;

}

tbody tr:last-child td {

    border-bottom: 0;

}

.table-link {

    color: #167d70;

    font-weight: 600;

    text-decoration: none;

}

.table-link:hover {

    text-decoration: underline;

}

.muted {

    color: #9aa3b2;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {

    padding: 40px 24px;

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


/* =========================================================
   DANGER ZONE
========================================================= */

.danger-zone {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-top: 24px;

    padding: 18px 20px;

    border: 1px solid #f0dada;

    border-radius: 10px;

    background: #fffafa;

}

.danger-zone strong {

    display: block;

    margin-bottom: 4px;

    color: #a94343;

    font-size: 12px;

}

.danger-zone p {

    margin: 0;

    color: #9a7777;

    font-size: 10px;

    line-height: 1.5;

}

.btn.danger {

    border: 1px solid #d96b6b;

    background: #fff;

    color: #b84949;

}

.btn.danger:hover {

    background: #fff1f1;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .product-show-layout {

        grid-template-columns: 1fr;

    }

}

@media (max-width: 600px) {

    .info-grid,
    .commercial-grid {

        grid-template-columns: 1fr;

    }

    .danger-zone {

        flex-direction: column;

        align-items: stretch;

    }

    .danger-zone .btn {

        width: 100%;

        justify-content: center;

    }

}

</style>

@endsection