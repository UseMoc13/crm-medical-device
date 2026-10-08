@extends('layouts.app')

@section('title', 'Product Categories')

@section('content')

<div class="page-head">


    <div>

        <h1>Product Categories</h1>

        <p>
            Manage product categories used to organize medical device products.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('product-categories.create') }}"
            class="btn primary">
            + New Category
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
CATEGORY LIST
========================================================= --}}

<div class="card">


    <div class="card-head">

        <div>

            <h3>Category List</h3>

            <p>

                {{ $categories->total() }}

                {{ $categories->total() == 1
                ? 'category'
                : 'categories'
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
            action="{{ route('product-categories.index') }}"
            class="category-filter-bar">

            {{-- Search --}}

            <div class="category-search">

                <span class="category-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search categories..."
                    autocomplete="off">


                @if(request('search'))

                <a
                    href="{{ route(
                    'product-categories.index',
                    request()->except(
                        'search',
                        'page'
                    )
                ) }}"
                    class="category-search-clear"
                    title="Clear search">
                    ×
                </a>

                @endif

            </div>

            <button
                type="submit"
                class="btn category-filter-button">
                Search
            </button>


            {{-- Reset --}}

            @if(request('search'))

            <a
                href="{{ route('product-categories.index') }}"
                class="btn category-reset-button">
                Reset
            </a>

            @endif

        </form>


        {{-- =====================================================
         ACTIVE FILTER INFORMATION
    ====================================================== --}}

        @if(request('search'))

        <div class="category-filter-summary">

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
         CATEGORY TABLE
    ====================================================== --}}

        <div class="table-wrap category-table-wrap">

            <table class="category-table">

                <thead>

                    <tr>

                        <th>
                            Category
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

                    @forelse($categories as $category)

                    <tr>


                        {{-- =================================================
                         CATEGORY
                    ================================================== --}}

                        <td>

                            <a
                                href="{{ route(
                                'product-categories.show',
                                $category
                            ) }}"
                                class="category-name-link">

                                <strong>
                                    {{ $category->category_name }}
                                </strong>

                            </a>


                            <div class="category-id">

                                ID:
                                {{ $category->category_id }}

                            </div>

                        </td>


                        {{-- =================================================
                         DESCRIPTION
                    ================================================== --}}

                        <td>

                            @if($category->description)

                            <div
                                class="category-description"
                                title="{{ $category->description }}">

                                {{ $category->description }}

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

                            @if(isset($category->products_count))

                            @if($category->products_count > 0)

                            <span class="category-product-badge">

                                {{ $category->products_count }}

                                {{ $category->products_count == 1
                                    ? 'Product'
                                    : 'Products'
                                }}

                            </span>

                            @else

                            <span class="category-product-badge empty">

                                No Products

                            </span>

                            @endif

                            @elseif(method_exists($category, 'products'))

                            @php

                            $productCount =
                            $category->products()->count();

                            @endphp


                            @if($productCount > 0)

                            <span class="category-product-badge">

                                {{ $productCount }}

                                {{ $productCount == 1
                                    ? 'Product'
                                    : 'Products'
                                }}

                            </span>

                            @else

                            <span class="category-product-badge empty">

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

                            @if($category->created_at)

                            <div class="category-date">

                                {{ $category->created_at->format('d M Y') }}

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

                            @if($category->updated_at)

                            <div class="category-date">

                                {{ $category->updated_at->format('d M Y') }}

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
                                    'product-categories.show',
                                    $category
                                ) }}"
                                    class="action-btn view"
                                    title="View Category"
                                    aria-label="View Category">
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                    'product-categories.edit',
                                    $category
                                ) }}"
                                    class="action-btn edit"
                                    title="Edit Category"
                                    aria-label="Edit Category">
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                    'product-categories.destroy',
                                    $category
                                ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-category-name="{{ $category->category_name }}">

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Category"
                                        aria-label="Delete Category">
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

                                No product categories found.

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

        @if($categories->hasPages())

        <div class="pagination">

            {{ $categories->links() }}

        </div>

        @endif

    </div>


</div>

{{-- =========================================================
DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteCategoryModal"
    aria-hidden="true">


    <div
        class="delete-modal-overlay"
        data-close-delete-modal></div>


    <div
        class="delete-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteCategoryModalTitle">

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteCategoryModalTitle">
                Delete Product Category?
            </h3>


            <p>

                Are you sure you want to delete

                <strong id="deleteCategoryName"></strong>?

            </p>


            <span>
                A category that is currently associated with products
                may not be able to be deleted.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteCategory">
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteCategory">
                Delete Category
            </button>

        </div>

    </div>


</div>

<style>
    /* =========================================================
   CATEGORY FILTER BAR
========================================================= */

    .category-filter-bar {

        display: flex;

        align-items: center;

        gap: 10px;

        flex-wrap: wrap;

        margin-bottom: 18px;

    }


    /* =========================================================
   SEARCH
========================================================= */

    .category-search {

        position: relative;

        flex: 1 1 300px;

        min-width: 240px;

    }


    .category-search input {

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


    .category-search input:focus {

        outline: none;

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);

    }


    .category-search-icon {

        position: absolute;

        left: 13px;

        top: 50%;

        transform: translateY(-50%);

        color: #7d8797;

        font-size: 19px;

        pointer-events: none;

    }


    .category-search-clear {

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


    .category-search-clear:hover {

        background: #edf1f5;

        color: #17284f;

    }


    /* =========================================================
   FILTER BUTTON
========================================================= */

    .category-filter-button {

        height: 40px;

        white-space: nowrap;

    }


    .category-reset-button {

        height: 40px;

        display: inline-flex;

        align-items: center;

        white-space: nowrap;

    }


    /* =========================================================
   FILTER SUMMARY
========================================================= */

    .category-filter-summary {

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

    .category-table-wrap {

        width: 100%;

        overflow-x: auto;

    }


    .category-table {

        width: 100%;

        min-width: 1050px;

        border-collapse: collapse;

    }


    .category-table th {

        padding: 11px 14px;

        font-size: 10px;

        text-transform: uppercase;

        letter-spacing: .5px;

        text-align: left;

        color: var(--muted);

        background: #fafbfd;

    }


    .category-table td {

        padding: 15px 14px;

        border-bottom: 1px solid #edf0f4;

        color: #34415c;

        font-size: 13px;

        vertical-align: middle;

    }


    .category-table tbody tr:hover {

        background: #fafbfd;

    }


    /* =========================================================
   CATEGORY NAME
========================================================= */

    .category-name-link {

        color: inherit;

        text-decoration: none;

    }


    .category-name-link:hover {

        color: #223a70;

    }


    .category-name-link strong {

        color: #17284f;

        font-size: 13px;

    }


    .category-id {

        margin-top: 4px;

        color: #8a94a6;

        font-size: 11px;

    }


    /* =========================================================
   DESCRIPTION
========================================================= */

    .category-description {

        max-width: 330px;

        color: #4d5b70;

        font-size: 12px;

        line-height: 1.55;

        display: -webkit-box;

        -webkit-line-clamp: 2;

        -webkit-box-orient: vertical;

        overflow: hidden;

    }


    .category-description:hover {

        color: #34415c;

    }


    /* =========================================================
   PRODUCT BADGE
========================================================= */

    .category-product-badge {

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


    .category-product-badge.empty {

        background: #f1f3f6;

        color: #667085;

    }


    /* =========================================================
   DATE
========================================================= */

    .category-date {

        color: #34415c;

        font-size: 12px;

        white-space: nowrap;

    }


    .category-time {

        margin-top: 3px;

        color: #8a94a6;

        font-size: 11px;

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


    .delete-modal-content>span {

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

        .category-search {

            flex: 1 1 100%;

        }

    }


    @media (max-width: 600px) {

        .category-filter-bar {

            flex-direction: column;

            align-items: stretch;

        }


        .category-search,
        .category-filter-button,
        .category-reset-button {

            width: 100%;

        }


        .category-filter-button,
        .category-reset-button {

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
                    'deleteCategoryModal'
                );


            const categoryName =
                document.getElementById(
                    'deleteCategoryName'
                );


            const confirmButton =
                document.getElementById(
                    'confirmDeleteCategory'
                );


            const cancelButton =
                document.getElementById(
                    'cancelDeleteCategory'
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
                    form.dataset.categoryName ||
                    'this category';


                categoryName.textContent =
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