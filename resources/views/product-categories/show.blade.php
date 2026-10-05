@extends('layouts.app')

@section('title', 'Product Category')

@section('content')

<div class="page-head">

    <div>

        <h1>
            {{ $productCategory->category_name }}
        </h1>

        <p>
            Product category details and associated products.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('product-categories.index') }}"
            class="btn"
        >
            ← Back to Categories
        </a>

        <a
            href="{{ route(
                'product-categories.edit',
                $productCategory
            ) }}"
            class="btn primary"
        >
            Edit Category
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


<div class="category-detail-layout">


    {{-- =================================================
         CATEGORY INFORMATION
    ================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>
                    Category Information
                </h3>

                <p>
                    Basic information about this product category.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="category-header">

                <div class="category-icon">
                    C
                </div>


                <div>

                    <h2>
                        {{ $productCategory->category_name }}
                    </h2>

                    <span>
                        Product Category
                    </span>

                </div>

            </div>


            <div class="detail-divider"></div>


            <div class="detail-grid">


                <div class="detail-item">

                    <span>
                        Category Name
                    </span>

                    <strong>
                        {{ $productCategory->category_name }}
                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Product Count
                    </span>

                    <strong>

                        {{ $productCategory->products->count() }}

                        {{
                            $productCategory->products->count() == 1
                                ? 'Product'
                                : 'Products'
                        }}

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Created At
                    </span>

                    <strong>

                        {{ $productCategory->created_at
                            ? $productCategory->created_at->format(
                                'd M Y H:i'
                            )
                            : '-'
                        }}

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Updated At
                    </span>

                    <strong>

                        {{ $productCategory->updated_at
                            ? $productCategory->updated_at->format(
                                'd M Y H:i'
                            )
                            : '-'
                        }}

                    </strong>

                </div>


            </div>


            <div class="detail-divider"></div>


            <div class="description-section">

                <h4>
                    Description
                </h4>

                @if($productCategory->description)

                    <p>
                        {{ $productCategory->description }}
                    </p>

                @else

                    <p class="muted">
                        No description provided.
                    </p>

                @endif

            </div>

        </div>

    </div>


    {{-- =================================================
         PRODUCTS
    ================================================== --}}

    <div class="card">

        <div class="card-head">

            <div>

                <h3>
                    Products in This Category
                </h3>

                <p>

                    {{ $productCategory->products->count() }}

                    {{
                        $productCategory->products->count() == 1
                            ? 'product'
                            : 'products'
                    }}

                    assigned to this category.

                </p>

            </div>


            <div>

                <a
                    href="{{ route('products.create') }}"
                    class="btn primary"
                >
                    + New Product
                </a>

            </div>

        </div>


        <div class="card-body">


            <div class="table-wrap">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Brand
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
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse(
                            $productCategory->products
                            as $product
                        )

                            <tr>

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


                                <td>

                                    @if($product->brand)

                                        {{ $product->brand->brand_name }}

                                    @else

                                        <span class="muted">
                                            -
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    {{ $product->product_type }}

                                </td>


                                <td>

                                    @if($product->price !== null)

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


                                <td>

                                    <span
                                        class="status-badge
                                        status-{{ Str::slug(
                                            $product->status
                                        ) }}"
                                    >

                                        {{ ucfirst(
                                            $product->status
                                        ) }}

                                    </span>

                                </td>


                                <td>

                                    <div class="row-actions">

                                        <a
                                            href="{{ route(
                                                'products.show',
                                                $product
                                            ) }}"
                                            class="action-btn"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route(
                                                'products.edit',
                                                $product
                                            ) }}"
                                            class="action-btn"
                                        >
                                            Edit
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="empty-table"
                                >

                                    <div class="empty-state">

                                        <div class="empty-icon">
                                            P
                                        </div>

                                        <strong>
                                            No products in this category
                                        </strong>

                                        <span>
                                            Create a product and assign this category.
                                        </span>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>


<style>

/* =====================================================
   LAYOUT
===================================================== */

.category-detail-layout {

    display: grid;

    gap: 18px;

}


/* =====================================================
   CATEGORY HEADER
===================================================== */

.category-header {

    display: flex;

    align-items: center;

    gap: 14px;

}


.category-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 54px;

    height: 54px;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 18px;

    font-weight: 700;

}


.category-header h2 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 18px;

}


.category-header span {

    color: #8a94a6;

    font-size: 11px;

}


/* =====================================================
   DIVIDER
===================================================== */

.detail-divider {

    height: 1px;

    margin: 22px 0;

    background: #edf0f5;

}


/* =====================================================
   DETAIL GRID
===================================================== */

.detail-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;

}


.detail-item {

    display: flex;

    flex-direction: column;

    gap: 5px;

}


.detail-item span {

    color: #8a94a6;

    font-size: 10px;

}


.detail-item strong {

    color: #34415c;

    font-size: 12px;

}


/* =====================================================
   DESCRIPTION
===================================================== */

.description-section h4 {

    margin: 0 0 8px;

    color: #34415c;

    font-size: 12px;

}


.description-section p {

    margin: 0;

    color: #536078;

    font-size: 11px;

    line-height: 1.7;

    white-space: pre-line;

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

    min-width: 850px;

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

}


.table-wrap tbody tr:last-child td {

    border-bottom: 0;

}


.product-link {

    color: #17284f;

    text-decoration: none;

}


.product-link:hover {

    color: #223a70;

}


.product-link strong {

    font-size: 11px;

}


.product-code {

    margin-top: 3px;

    color: #8a94a6;

    font-size: 9px;

}


.price {

    color: #17284f;

    white-space: nowrap;

}


/* =====================================================
   STATUS
===================================================== */

.status-badge {

    display: inline-flex;

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

    background: #fafbfd;

    color: #17284f;

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

    min-height: 180px;

    text-align: center;

}


.empty-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

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


.muted {

    color: #8a94a6;

    font-size: 10px;

}


@media (max-width: 650px) {

    .detail-grid {

        grid-template-columns: 1fr;

    }

}

</style>

@endsection