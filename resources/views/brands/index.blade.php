@extends('layouts.app')

@section('title', 'Brands')

@section('content')

<div class="page-head">

    <div>

        <h1>Brands</h1>

        <p>
            Manage medical device brands used to organize product information.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('brands.create') }}"
            class="btn primary">
            + New Brand
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
BRAND LIST
========================================================= --}}

<div class="card">

    <div class="card-head">

        <div>

            <h3>Brand List</h3>

            <p>

                {{ $brands->total() }}

                {{ $brands->total() == 1
                    ? 'brand'
                    : 'brands'
                }}

                found

            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
         FILTER BAR
    ====================================================== --}}

        <form
            method="GET"
            action="{{ route('brands.index') }}"
            class="brand-filter-bar">


            {{-- Search --}}

            <div class="brand-search">

                <span class="brand-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search brands..."
                    autocomplete="off">


                @if(request('search'))

                <a
                    href="{{ route(
                        'brands.index',
                        request()->except(
                            'search',
                            'page'
                        )
                    ) }}"
                    class="brand-search-clear"
                    title="Clear search">

                    ×

                </a>

                @endif

            </div>


            {{-- Search Button --}}

            <button
                type="submit"
                class="btn brand-filter-button">

                Search

            </button>


            {{-- Reset --}}

            @if(request('search'))

            <a
                href="{{ route('brands.index') }}"
                class="btn brand-reset-button">

                Reset

            </a>

            @endif

        </form>


        {{-- =====================================================
         ACTIVE FILTER INFORMATION
    ====================================================== --}}

        @if(request('search'))

        <div class="brand-filter-summary">

            <span>
                Showing filtered results
            </span>


            <span class="filter-chip">

                Search:
                "{{ request('search') }}"

            </span>

        </div>

        @endif


        {{-- =====================================================
         BRAND TABLE
    ====================================================== --}}

        <div class="table-wrap brand-table-wrap">

            <table class="brand-table">

                <thead>

                    <tr>

                        <th>
                            Brand
                        </th>

                        <th>
                            Description
                        </th>

                        <th>
                            Products
                        </th>

                        <th>
                            Created
                        </th>

                        <th>
                            Updated
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($brands as $brand)

                    <tr>


                        {{-- =================================================
                         BRAND
                    ================================================== --}}

                        <td>

                            <a
                                href="{{ route(
                                    'brands.show',
                                    $brand
                                ) }}"
                                class="brand-name-link">

                                <strong>
                                    {{ $brand->brand_name }}
                                </strong>

                            </a>


                            <div class="brand-id">

                                ID:
                                {{ $brand->brand_id }}

                            </div>

                        </td>


                        {{-- =================================================
                         DESCRIPTION
                    ================================================== --}}

                        <td>

                            @if($brand->description)

                            <div
                                class="brand-description"
                                title="{{ $brand->description }}">

                                {{ $brand->description }}

                            </div>

                            @else

                            <span class="muted">
                                No description
                            </span>

                            @endif

                        </td>


                        {{-- =================================================
                         PRODUCTS
                    ================================================== --}}

                        <td>

                            @if(isset($brand->products_count))

                            @if($brand->products_count > 0)

                            <span class="brand-product-badge">

                                {{ $brand->products_count }}

                                {{ $brand->products_count == 1
                                    ? 'Product'
                                    : 'Products'
                                }}

                            </span>

                            @else

                            <span class="brand-product-badge empty">

                                No Products

                            </span>

                            @endif


                            @elseif(method_exists($brand, 'products'))

                            @php

                            $productCount =
                            $brand->products()->count();

                            @endphp


                            @if($productCount > 0)

                            <span class="brand-product-badge">

                                {{ $productCount }}

                                {{ $productCount == 1
                                    ? 'Product'
                                    : 'Products'
                                }}

                            </span>

                            @else

                            <span class="brand-product-badge empty">

                                No Products

                            </span>

                            @endif


                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- =================================================
                         CREATED
                    ================================================== --}}

                        <td>

                            @if($brand->created_at)

                            <div class="brand-date">

                                {{ $brand->created_at->format('d M Y') }}

                            </div>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- =================================================
                         UPDATED
                    ================================================== --}}

                        <td>

                            @if($brand->updated_at)

                            <div class="brand-date">

                                {{ $brand->updated_at->format('d M Y') }}

                            </div>

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- =================================================
                         ACTIONS
                    ================================================== --}}

                        <td>

                            <div class="table-actions">


                                {{-- View --}}

                                <a
                                    href="{{ route(
                                        'brands.show',
                                        $brand
                                    ) }}"
                                    class="action-btn view"
                                    title="View Brand"
                                    aria-label="View Brand">

                                    👁

                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'brands.edit',
                                        $brand
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Brand"
                                    aria-label="Edit Brand">

                                    ✎

                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'brands.destroy',
                                        $brand
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-brand-name="{{ $brand->brand_name }}">

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Brand"
                                        aria-label="Delete Brand">

                                        🗑

                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="6">

                            <div class="empty">

                                No brands found.

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

        @if($brands->hasPages())

        <div class="pagination">

            {{ $brands->links() }}

        </div>

        @endif

    </div>

</div>


{{-- =========================================================
DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteBrandModal"
    aria-hidden="true">


    <div
        class="delete-modal-overlay"
        data-close-delete-modal></div>


    <div
        class="delete-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteBrandModalTitle">


        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteBrandModalTitle">
                Delete Brand?
            </h3>


            <p>

                Are you sure you want to delete

                <strong id="deleteBrandName"></strong>?

            </p>


            <span>

                A brand that is currently associated with products
                may not be able to be deleted.

            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteBrand">

                Cancel

            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteBrand">

                Delete Brand

            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   BRAND FILTER BAR
========================================================= */

.brand-filter-bar {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

    margin-bottom: 18px;

}


/* =========================================================
   SEARCH
========================================================= */

.brand-search {

    position: relative;

    flex: 1 1 300px;

    min-width: 240px;

}


.brand-search input {

    width: 100%;

    height: 40px;

    box-sizing: border-box;

    padding: 0 38px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;

}


.brand-search input:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);

}


.brand-search-icon {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;

}


.brand-search-clear {

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


.brand-search-clear:hover {

    background: #edf1f5;

    color: #17284f;

}


/* =========================================================
   FILTER BUTTON
========================================================= */

.brand-filter-button {

    height: 40px;

    white-space: nowrap;

}


.brand-reset-button {

    height: 40px;

    display: inline-flex;

    align-items: center;

    white-space: nowrap;

}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.brand-filter-summary {

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

.brand-table-wrap {

    width: 100%;

    overflow-x: auto;

}


.brand-table {

    width: 100%;

    min-width: 1050px;

    border-collapse: collapse;

}


.brand-table th {

    padding: 11px 14px;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;

    text-align: left;

    color: var(--muted);

    background: #fafbfd;

}


.brand-table td {

    padding: 15px 14px;

    border-bottom: 1px solid #edf0f4;

    color: #34415c;

    font-size: 13px;

    vertical-align: middle;

}


.brand-table tbody tr:hover {

    background: #fafbfd;

}


/* =========================================================
   BRAND NAME
========================================================= */

.brand-name-link {

    color: inherit;

    text-decoration: none;

}


.brand-name-link:hover {

    color: #223a70;

}


.brand-name-link strong {

    color: #17284f;

    font-size: 13px;

}


.brand-id {

    margin-top: 4px;

    color: #8a94a6;

    font-size: 11px;

}


/* =========================================================
   DESCRIPTION
========================================================= */

.brand-description {

    max-width: 330px;

    color: #4d5b70;

    font-size: 12px;

    line-height: 1.55;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;

}


.brand-description:hover {

    color: #34415c;

}


/* =========================================================
   PRODUCT BADGE
========================================================= */

.brand-product-badge {

    display: inline-flex;

    align-items: center;

    padding: 4px 9px;

    border-radius: 6px;

    background: #e8f5f0;

    color: #16805f;

    font-size: 11px;

    font-weight: 600;

    white-space: nowrap;

}


.brand-product-badge.empty {

    background: #f1f3f6;

    color: #667085;

}


/* =========================================================
   DATE
========================================================= */

.brand-date {

    color: #34415c;

    font-size: 12px;

    white-space: nowrap;

}


/* =========================================================
   MUTED
========================================================= */

.muted {

    color: #8a94a6;

    font-size: 12px;

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


.action-btn.view:hover {

    background: #eef7f7;

    border-color: #b8deda;

    color: #167d70;

}


.action-btn.edit:hover {

    background: #f2f5fa;

    border-color: #cbd5e4;

    color: #223a70;

}


.action-btn.delete:hover {

    background: #fff0f0;

    border-color: #e6b7b7;

    color: #c94a4a;

}


/* =========================================================
   DELETE FORM
========================================================= */

.delete-form {

    display: inline-flex;

    margin: 0;

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
            translateY(8px) scale(.98);

    }

    to {

        opacity: 1;

        transform:
            translateY(0) scale(1);

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

    line-height: 1.5;

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

@media (max-width: 1000px) {

    .brand-search {

        flex: 1 1 100%;

    }

}


@media (max-width: 600px) {

    .brand-filter-bar {

        flex-direction: column;

        align-items: stretch;

    }


    .brand-search,
    .brand-filter-button,
    .brand-reset-button {

        width: 100%;

    }


    .brand-filter-button,
    .brand-reset-button {

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
    function() {


        const modal =
            document.getElementById(
                'deleteBrandModal'
            );


        const brandName =
            document.getElementById(
                'deleteBrandName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteBrand'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteBrand'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        /* =====================================================
           OPEN MODAL
        ====================================================== */

        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.brandName ||
                'this brand';


            brandName.textContent =
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


        /* =====================================================
           CLOSE MODAL
        ====================================================== */

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


        /* =====================================================
           DELETE FORMS
        ====================================================== */

        document
            .querySelectorAll(
                '.delete-form'
            )
            .forEach(
                function(form) {

                    form.addEventListener(
                        'submit',
                        function(event) {

                            event.preventDefault();

                            openDeleteModal(
                                form
                            );

                        }
                    );

                }
            );


        /* =====================================================
           CONFIRM DELETE
        ====================================================== */

        confirmButton.addEventListener(
            'click',
            function() {

                if (deleteForm) {

                    deleteForm.submit();

                }

            }
        );


        /* =====================================================
           CANCEL
        ====================================================== */

        cancelButton.addEventListener(
            'click',
            closeDeleteModal
        );


        /* =====================================================
           OVERLAY
        ====================================================== */

        closeOverlay.addEventListener(
            'click',
            closeDeleteModal
        );


        /* =====================================================
           ESCAPE
        ====================================================== */

        document.addEventListener(
            'keydown',
            function(event) {

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