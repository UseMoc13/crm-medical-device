@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="page-head">

    <div>

        <h1>Products</h1>

        <p>
            Manage medical device products, pricing, warranty, and product information.
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


@if($errors->any())

    <div class="alert error">

        <strong>Please fix the following errors:</strong>

        <ul>

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


{{-- =========================================================
     PRODUCT LIST
========================================================= --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>Product List</h3>

            <p>
                {{ $products->total() }} products found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('products.index') }}"
            class="product-filter-bar"
        >


            {{-- Search --}}

            <div class="product-search">

                <span class="product-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search products..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'products.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="product-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Category --}}

            <div class="product-filter-select">

                <select name="category_id">

                    <option value="">
                        All Categories
                    </option>


                    @foreach($categories as $category)

                        <option
                            value="{{ $category->category_id }}"
                            @selected(
                                request('category_id') ===
                                $category->category_id
                            )
                        >
                            {{ $category->category_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Brand --}}

            <div class="product-filter-select">

                <select name="brand_id">

                    <option value="">
                        All Brands
                    </option>


                    @foreach($brands as $brand)

                        <option
                            value="{{ $brand->brand_id }}"
                            @selected(
                                request('brand_id') ===
                                $brand->brand_id
                            )
                        >
                            {{ $brand->brand_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Product Type --}}

            <div class="product-filter-select">

                <select name="product_type">

                    <option value="">
                        All Types
                    </option>


                    @foreach($productTypes as $type)

                        <option
                            value="{{ $type }}"
                            @selected(
                                request('product_type') === $type
                            )
                        >
                            {{ $type }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Status --}}

            <div class="product-filter-select">

                <select name="status">

                    <option value="">
                        All Statuses
                    </option>


                    @foreach($statuses as $status)

                        <option
                            value="{{ $status }}"
                            @selected(
                                request('status') === $status
                            )
                        >
                            {{ $status }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Filter --}}

            <button
                type="submit"
                class="btn product-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'category_id',
                'brand_id',
                'product_type',
                'status'
            ]))

                <a
                    href="{{ route('products.index') }}"
                    class="btn product-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER INFORMATION
        ====================================================== --}}

        @if(
            request('search') ||
            request('category_id') ||
            request('brand_id') ||
            request('product_type') ||
            request('status')
        )

            <div class="product-filter-summary">

                <span>
                    Showing filtered results
                </span>


                {{-- Search --}}

                @if(request('search'))

                    <span class="filter-chip">

                        Search:
                        "{{ request('search') }}"

                    </span>

                @endif


                {{-- Category --}}

                @if(request('category_id'))

                    @php

                        $selectedCategory =
                            $categories->firstWhere(
                                'category_id',
                                request('category_id')
                            );

                    @endphp


                    @if($selectedCategory)

                        <span class="filter-chip">

                            Category:
                            {{ $selectedCategory->category_name }}

                        </span>

                    @endif

                @endif


                {{-- Brand --}}

                @if(request('brand_id'))

                    @php

                        $selectedBrand =
                            $brands->firstWhere(
                                'brand_id',
                                request('brand_id')
                            );

                    @endphp


                    @if($selectedBrand)

                        <span class="filter-chip">

                            Brand:
                            {{ $selectedBrand->brand_name }}

                        </span>

                    @endif

                @endif


                {{-- Product Type --}}

                @if(request('product_type'))

                    <span class="filter-chip">

                        Type:
                        {{ request('product_type') }}

                    </span>

                @endif


                {{-- Status --}}

                @if(request('status'))

                    <span class="filter-chip">

                        Status:
                        {{ request('status') }}

                    </span>

                @endif

            </div>

        @endif


        {{-- =====================================================
             PRODUCT TABLE
        ====================================================== --}}

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
                                class="product-name-link"
                            >

                                <strong>
                                    {{ $product->product_name }}
                                </strong>

                            </a>


                            <div class="muted">

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

                            @if($product->product_type)

                                {{ $product->product_type }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Unit --}}

                        <td>

                            @if($product->unit)

                                <span class="product-unit-badge">

                                    {{ $product->unit }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Price --}}

                        <td>

                            @if($product->price !== null)

                                <strong class="product-price">

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

                            @if($product->warranty_period !== null)

                                {{ $product->warranty_period }}
                                month{{ $product->warranty_period != 1 ? 's' : '' }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($product->status)

                                <span
                                    class="product-status-badge
                                    product-status-{{ Str::slug($product->status) }}"
                                >

                                    {{ $product->status }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="table-actions">


                                {{-- View --}}

                                <a
                                    href="{{ route(
                                        'products.show',
                                        $product
                                    ) }}"
                                    class="action-btn view"
                                    title="View Product"
                                    aria-label="View Product"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'products.edit',
                                        $product
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Product"
                                    aria-label="Edit Product"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'products.destroy',
                                        $product
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-product-name="{{ $product->product_name }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Product"
                                        aria-label="Delete Product"
                                    >
                                        🗑
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="9">

                            <div class="empty">

                                No products found.

                            </div>

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =====================================================
             PAGINATION
        ====================================================== --}}

        @if($products->hasPages())

            <div class="pagination">

                {{ $products->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteProductModal"
    aria-hidden="true"
>

    <div
        class="delete-modal-overlay"
        data-close-delete-modal
    ></div>


    <div
        class="delete-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteProductModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteProductModalTitle">
                Delete Product?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteProductName"></strong>?

            </p>


            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteProduct"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteProduct"
            >
                Delete Product
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   PRODUCT FILTER BAR
========================================================= */

.product-filter-bar {
    display: flex;
    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.product-search {
    position: relative;

    flex: 1 1 260px;

    min-width: 220px;
}

.product-search input {
    width: 100%;
    height: 40px;

    box-sizing: border-box;

    padding: 0 38px 0 38px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.product-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.product-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.product-search-clear {
    position: absolute;

    right: 10px;
    top: 50%;

    transform: translateY(-50%);

    width: 22px;
    height: 22px;

    display: flex;

    align-items: center;
    justify-content: center;

    border-radius: 50%;

    color: #7d8797;

    text-decoration: none;

    font-size: 18px;
}

.product-search-clear:hover {
    background: #edf1f5;

    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.product-filter-select {
    position: relative;
}

.product-filter-select select {
    min-width: 155px;
    height: 40px;

    padding: 0 34px 0 12px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background-color: #fff;

    color: #34415c;

    font-size: 13px;

    cursor: pointer;

    appearance: auto;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.product-filter-select select:hover {
    border-color: #b7c0cf;
}

.product-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   FILTER BUTTONS
========================================================= */

.product-filter-button {
    height: 40px;

    white-space: nowrap;
}

.product-reset-button {
    height: 40px;

    display: inline-flex;

    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.product-filter-summary {
    display: flex;

    align-items: center;

    gap: 7px;

    flex-wrap: wrap;

    margin-bottom: 15px;

    font-size: 12px;

    color: #7d8797;
}

.filter-chip {
    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;
}


/* =========================================================
   TABLE
========================================================= */

.table-wrap {
    width: 100%;

    overflow-x: auto;
}

.table-wrap table {
    width: 100%;

    min-width: 1180px;

    border-collapse: collapse;
}

.table-wrap th {
    padding: 13px 14px;

    border-bottom: 1px solid #e7ebf1;

    color: #718096;

    font-size: 11px;

    font-weight: 700;

    text-align: left;

    white-space: nowrap;
}

.table-wrap td {
    padding: 15px 14px;

    border-bottom: 1px solid #edf0f4;

    color: #34415c;

    font-size: 13px;

    vertical-align: middle;
}

.table-wrap tbody tr:hover {
    background: #fafbfd;
}


/* =========================================================
   PRODUCT NAME
========================================================= */

.product-name-link {
    color: inherit;

    text-decoration: none;
}

.product-name-link:hover {
    color: #223a70;
}

.product-name-link strong {
    color: #17284f;

    font-size: 13px;
}

.muted {
    color: #8a94a6;

    font-size: 12px;
}


/* =========================================================
   PRICE
========================================================= */

.product-price {
    color: #17284f;

    white-space: nowrap;

    font-size: 13px;
}


/* =========================================================
   UNIT
========================================================= */

.product-unit-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.product-status-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #eef2f6;

    color: #4d5b70;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   STATUS VARIANTS
========================================================= */

.product-status-active {
    background: #e8f5f0;

    color: #16805f;
}

.product-status-inactive {
    background: #f1f3f6;

    color: #667085;
}

.product-status-discontinued {
    background: #fff0f0;

    color: #a14d4d;
}


/* =========================================================
   ACTIONS
========================================================= */

.table-actions {
    display: flex;

    align-items: center;

    gap: 6px;
}

.action-btn {
    width: 30px;
    height: 30px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 0;

    border: 1px solid #dfe4eb;

    border-radius: 7px;

    background: #fff;

    color: #4d5b70;

    font-size: 13px;

    line-height: 1;

    text-decoration: none;

    cursor: pointer;

    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease;
}

.action-btn:hover {
    background: #f4f7f9;

    border-color: #cbd3df;

    color: #17284f;
}

.action-btn.delete:hover {
    background: #fff0f0;

    border-color: #e6b7b7;

    color: #c94a4a;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty {
    padding: 55px 20px;

    text-align: center;

    color: #718096;

    font-size: 13px;
}


/* =========================================================
   DELETE MODAL
========================================================= */

.delete-modal {
    position: fixed;

    inset: 0;

    z-index: 9999;

    display: none;

    align-items: center;
    justify-content: center;
}

.delete-modal.open {
    display: flex;
}

.delete-modal-overlay {
    position: absolute;

    inset: 0;

    background: rgba(15, 24, 42, .45);

    backdrop-filter: blur(2px);
}

.delete-modal-dialog {
    position: relative;

    width: min(420px, calc(100% - 32px));

    background: #fff;

    border-radius: 14px;

    padding: 26px;

    box-shadow:
        0 20px 60px rgba(23, 40, 79, .20);

    animation: deleteModalIn .18s ease;
}

@keyframes deleteModalIn {

    from {
        opacity: 0;

        transform:
            translateY(8px)
            scale(.98);
    }

    to {
        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }

}

.delete-modal-icon {
    width: 44px;
    height: 44px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin-bottom: 16px;

    border-radius: 50%;

    background: #fff0f0;

    color: #c94a4a;

    font-size: 21px;

    font-weight: 700;
}

.delete-modal-content h3 {
    margin: 0 0 8px;

    color: #17284f;

    font-size: 18px;
}

.delete-modal-content p {
    margin: 0 0 6px;

    color: #34415c;

    font-size: 13px;

    line-height: 1.6;
}

.delete-modal-content p strong {
    color: #17284f;
}

.delete-modal-content > span {
    color: #8a94a6;

    font-size: 12px;
}

.delete-modal-actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;

    margin-top: 24px;
}

.delete-confirm-button {
    background: #c94a4a !important;

    color: #fff !important;

    border-color: #c94a4a !important;
}

.delete-confirm-button:hover {
    background: #b83f3f !important;

    border-color: #b83f3f !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .product-search {
        flex: 1 1 100%;
    }

    .product-filter-select {
        flex: 1 1 150px;
    }

    .product-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .product-filter-bar {
        flex-direction: column;

        align-items: stretch;
    }

    .product-search,
    .product-filter-select,
    .product-filter-button,
    .product-reset-button {
        width: 100%;
    }

    .product-filter-button,
    .product-reset-button {
        justify-content: center;
    }

    .delete-modal-dialog {
        padding: 22px;
    }

    .delete-modal-actions {
        flex-direction: column-reverse;
    }

    .delete-modal-actions .btn {
        width: 100%;

        justify-content: center;
    }

}

</style>


<script>

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const modal =
            document.getElementById(
                'deleteProductModal'
            );


        const productName =
            document.getElementById(
                'deleteProductName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteProduct'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteProduct'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.productName ||
                'this product';


            productName.textContent =
                name;


            modal.classList.add(
                'open'
            );


            modal.setAttribute(
                'aria-hidden',
                'false'
            );


            document.body.style.overflow =
                'hidden';

        }


        function closeDeleteModal() {

            modal.classList.remove(
                'open'
            );


            modal.setAttribute(
                'aria-hidden',
                'true'
            );


            document.body.style.overflow =
                '';


            deleteForm = null;

        }


        document
            .querySelectorAll(
                '.delete-form'
            )
            .forEach(
                function (form) {

                    form.addEventListener(
                        'submit',
                        function (event) {

                            event.preventDefault();

                            openDeleteModal(
                                form
                            );

                        }
                    );

                }
            );


        confirmButton.addEventListener(
            'click',
            function () {

                if (deleteForm) {

                    deleteForm.submit();

                }

            }
        );


        cancelButton.addEventListener(
            'click',
            closeDeleteModal
        );


        closeOverlay.addEventListener(
            'click',
            closeDeleteModal
        );


        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape' &&
                    modal.classList.contains(
                        'open'
                    )
                ) {

                    closeDeleteModal();

                }

            }
        );

    }
);

</script>

@endsection