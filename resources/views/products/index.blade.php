@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="page-head">

    <div>

        <h1>
            Products
        </h1>

        <p>
            Manage medical device products and their pricing information.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('products.create') }}"
            class="btn primary"
        >
            + New Product
        </a>

    </div>

</div>


{{-- =====================================================
     ALERT
====================================================== --}}

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


{{-- =====================================================
     PRODUCT CARD
====================================================== --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>
                Product List
            </h3>

            <p>
                {{ $products->total() }}
                {{ $products->total() == 1 ? 'product' : 'products' }}
                registered.
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =================================================
             FILTER
        ================================================== --}}

        <form
            method="GET"
            action="{{ route('products.index') }}"
            class="filter-bar"
        >


            {{-- Search --}}

            <div class="filter-search">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search product..."
                >

            </div>


            {{-- Category --}}

            <select
                name="category_id"
                class="filter-select"
            >

                <option value="">
                    All Categories
                </option>


                @foreach($categories as $category)

                    <option
                        value="{{ $category->category_id }}"
                        @selected(
                            request('category_id') ==
                            $category->category_id
                        )
                    >

                        {{ $category->category_name }}

                    </option>

                @endforeach

            </select>


            {{-- Brand --}}

            <select
                name="brand_id"
                class="filter-select"
            >

                <option value="">
                    All Brands
                </option>


                @foreach($brands as $brand)

                    <option
                        value="{{ $brand->brand_id }}"
                        @selected(
                            request('brand_id') ==
                            $brand->brand_id
                        )
                    >

                        {{ $brand->brand_name }}

                    </option>

                @endforeach

            </select>


            {{-- Product Type --}}

            <select
                name="product_type"
                class="filter-select"
            >

                <option value="">
                    All Types
                </option>


                @foreach($productTypes as $type)

                    <option
                        value="{{ $type }}"
                        @selected(
                            request('product_type') ==
                            $type
                        )
                    >

                        {{ $type }}

                    </option>

                @endforeach

            </select>


            {{-- Status --}}

            <select
                name="status"
                class="filter-select"
            >

                <option value="">
                    All Status
                </option>


                @foreach($statuses as $status)

                    <option
                        value="{{ $status }}"
                        @selected(
                            request('status') ==
                            $status
                        )
                    >

                        {{ ucfirst($status) }}

                    </option>

                @endforeach

            </select>


            <button
                type="submit"
                class="btn"
            >
                Filter
            </button>


            @if(
                request()->filled('search')
                ||
                request()->filled('category_id')
                ||
                request()->filled('brand_id')
                ||
                request()->filled('product_type')
                ||
                request()->filled('status')
            )

                <a
                    href="{{ route('products.index') }}"
                    class="btn"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =================================================
             ACTIVE FILTER SUMMARY
        ================================================== --}}

        @if(
            request()->filled('search')
            ||
            request()->filled('category_id')
            ||
            request()->filled('brand_id')
            ||
            request()->filled('product_type')
            ||
            request()->filled('status')
        )

            <div class="active-filters">

                <span>
                    Active filters:
                </span>


                @if(request('search'))

                    <span class="filter-tag">

                        Search:
                        <strong>
                            {{ request('search') }}
                        </strong>

                    </span>

                @endif


                @if(request('category_id'))

                    @php

                        $activeCategory =
                            $categories->firstWhere(
                                'category_id',
                                request('category_id')
                            );

                    @endphp


                    @if($activeCategory)

                        <span class="filter-tag">

                            Category:
                            <strong>
                                {{ $activeCategory->category_name }}
                            </strong>

                        </span>

                    @endif

                @endif


                @if(request('brand_id'))

                    @php

                        $activeBrand =
                            $brands->firstWhere(
                                'brand_id',
                                request('brand_id')
                            );

                    @endphp


                    @if($activeBrand)

                        <span class="filter-tag">

                            Brand:
                            <strong>
                                {{ $activeBrand->brand_name }}
                            </strong>

                        </span>

                    @endif

                @endif


                @if(request('product_type'))

                    <span class="filter-tag">

                        Type:
                        <strong>
                            {{ request('product_type') }}
                        </strong>

                    </span>

                @endif


                @if(request('status'))

                    <span class="filter-tag">

                        Status:
                        <strong>
                            {{ ucfirst(request('status')) }}
                        </strong>

                    </span>

                @endif

            </div>

        @endif


        {{-- =================================================
             TABLE
        ================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Brand
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Unit
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Warranty
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($products as $product)

                        <tr>


                            {{-- Product --}}

                            <td>

                                <a
                                    href="{{ route(
                                        'products.show',
                                        $product
                                    ) }}"
                                    class="product-link"
                                >

                                    <strong>

                                        {{ $product->product_name }}

                                    </strong>

                                </a>


                                <div class="product-code">

                                    {{ $product->product_code }}

                                </div>

                            </td>


                            {{-- Category --}}

                            <td>

                                @if($product->category)

                                    {{ $product->category->category_name }}

                                @else

                                    <span class="muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Brand --}}

                            <td>

                                @if($product->brand)

                                    {{ $product->brand->brand_name }}

                                @else

                                    <span class="muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Type --}}

                            <td>

                                {{ $product->product_type }}

                            </td>


                            {{-- Unit --}}

                            <td>

                                <span class="unit-badge">

                                    {{ $product->unit }}

                                </span>

                            </td>


                            {{-- Price --}}

                            <td>

                                @if(
                                    $product->price !== null
                                )

                                    <strong class="price">

                                        Rp
                                        {{ number_format(
                                            (float) $product->price,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </strong>

                                @else

                                    <span class="muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Warranty --}}

                            <td>

                                @if(
                                    $product->warranty_period !== null
                                )

                                    {{ $product->warranty_period }}

                                    <span class="muted">
                                        month
                                    </span>

                                @else

                                    <span class="muted">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}

                            <td>

                                <span
                                    class="status-badge
                                    status-{{ Str::slug($product->status) }}"
                                >

                                    {{ ucfirst($product->status) }}

                                </span>

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="row-actions">

                                    <a
                                        href="{{ route(
                                            'products.show',
                                            $product
                                        ) }}"
                                        class="action-btn"
                                        title="View"
                                    >
                                        View
                                    </a>


                                    <a
                                        href="{{ route(
                                            'products.edit',
                                            $product
                                        ) }}"
                                        class="action-btn"
                                        title="Edit"
                                    >
                                        Edit
                                    </a>

                                </div>

                            </td>


                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="9"
                                class="empty-table"
                            >

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        P
                                    </div>

                                    <strong>
                                        No products found
                                    </strong>

                                    <span>
                                        Try adjusting your filters or add a new product.
                                    </span>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}

        @if($products->hasPages())

            <div class="pagination-wrap">

                {{ $products->links() }}

            </div>

        @endif

    </div>

</div>


<style>

/* =====================================================
   FILTER
===================================================== */

.filter-bar {

    display: grid;

    grid-template-columns:
        minmax(180px, 1.5fr)
        minmax(140px, 1fr)
        minmax(140px, 1fr)
        minmax(130px, 1fr)
        minmax(120px, 1fr)
        auto
        auto;

    gap: 8px;

    margin-bottom: 14px;

}


.filter-search input,
.filter-select {

    width: 100%;

    height: 36px;

    padding: 0 11px;

    border: 1px solid #dfe5ed;

    border-radius: 7px;

    background: #fff;

    color: #34415c;

    font-size: 11px;

    outline: none;

}


.filter-search input:focus,
.filter-select:focus {

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 2px
        rgba(43, 167, 160, .08);

}


.filter-bar .btn {

    height: 36px;

    white-space: nowrap;

}


/* =====================================================
   ACTIVE FILTERS
===================================================== */

.active-filters {

    display: flex;

    align-items: center;

    flex-wrap: wrap;

    gap: 6px;

    margin-bottom: 15px;

    color: #8a94a6;

    font-size: 10px;

}


.filter-tag {

    padding: 4px 8px;

    border: 1px solid #e3e8ef;

    border-radius: 5px;

    background: #fafbfd;

    color: #718096;

}


.filter-tag strong {

    color: #34415c;

}


/* =====================================================
   TABLE
===================================================== */

.table-wrap {

    width: 100%;

    overflow-x: auto;

}


.table-wrap table {

    width: 100%;

    min-width: 1050px;

    border-collapse: collapse;

}


.table-wrap th {

    padding: 11px 12px;

    border-bottom: 1px solid #e4e9f1;

    background: #fafbfd;

    color: #718096;

    font-size: 10px;

    font-weight: 600;

    text-align: left;

    white-space: nowrap;

}


.table-wrap td {

    padding: 12px;

    border-bottom: 1px solid #edf0f5;

    color: #4d5b70;

    font-size: 11px;

    vertical-align: middle;

}


.table-wrap tbody tr {

    transition:
        background .15s ease;

}


.table-wrap tbody tr:hover {

    background: #fafcfe;

}


.table-wrap tbody tr:last-child td {

    border-bottom: 0;

}


/* =====================================================
   PRODUCT
===================================================== */

.product-link {

    color: #17284f;

    text-decoration: none;

}


.product-link:hover {

    color: #223a70;

}


.product-link strong {

    font-size: 11px;

    font-weight: 600;

}


.product-code {

    margin-top: 3px;

    color: #8a94a6;

    font-size: 9px;

}


/* =====================================================
   PRICE
===================================================== */

.price {

    color: #17284f;

    font-size: 11px;

    white-space: nowrap;

}


/* =====================================================
   UNIT
===================================================== */

.unit-badge {

    display: inline-flex;

    padding: 4px 7px;

    border-radius: 5px;

    background: #f1f5f9;

    color: #64748b;

    font-size: 9px;

    font-weight: 600;

}


/* =====================================================
   STATUS
===================================================== */

.status-badge {

    display: inline-flex;

    align-items: center;

    padding: 4px 8px;

    border-radius: 5px;

    font-size: 9px;

    font-weight: 600;

}


.status-active {

    background: #e8f5f0;

    color: #16805f;

}


.status-inactive {

    background: #f1f3f6;

    color: #667085;

}


/* =====================================================
   ACTIONS
===================================================== */

.row-actions {

    display: flex;

    align-items: center;

    gap: 5px;

}


.action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 27px;

    padding: 0 8px;

    border: 1px solid #dfe5ed;

    border-radius: 5px;

    background: #fff;

    color: #536078;

    font-size: 9px;

    text-decoration: none;

}


.action-btn:hover {

    border-color: #cbd4df;

    background: #fafbfd;

    color: #17284f;

}


/* =====================================================
   MUTED
===================================================== */

.muted {

    color: #8a94a6;

    font-size: 10px;

}


/* =====================================================
   EMPTY
===================================================== */

.empty-table {

    padding: 0 !important;

}


.empty-state {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    gap: 6px;

    min-height: 210px;

    text-align: center;

}


.empty-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

    margin-bottom: 3px;

    border-radius: 50%;

    background: #eef3f8;

    color: #718096;

    font-size: 13px;

    font-weight: 700;

}


.empty-state strong {

    color: #4d5b70;

    font-size: 12px;

}


.empty-state span {

    color: #8a94a6;

    font-size: 10px;

}


/* =====================================================
   PAGINATION
===================================================== */

.pagination-wrap {

    display: flex;

    justify-content: flex-end;

    margin-top: 16px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1200px) {

    .filter-bar {

        grid-template-columns:
            repeat(3, 1fr);

    }


    .filter-search {

        grid-column: span 3;

    }

}


@media (max-width: 700px) {

    .filter-bar {

        grid-template-columns: 1fr;

    }


    .filter-search {

        grid-column: auto;

    }


    .filter-bar .btn {

        width: 100%;

    }


    .active-filters {

        align-items: flex-start;

        flex-direction: column;

    }


    .pagination-wrap {

        justify-content: flex-start;

    }

}

</style>

@endsection