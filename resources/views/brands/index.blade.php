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
                {{ $brands->total() }} brands found
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

        <div class="table-wrap">

            <table>

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


                        {{-- Brand --}}

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


                            <div class="secondary-text">

                                ID:
                                {{ $brand->brand_id }}

                            </div>

                        </td>


                        {{-- Description --}}

                        <td>

                            @if($brand->description)

                            <span class="brand-description">

                                {{ $brand->description }}

                            </span>

                            @else

                            <span class="muted">
                                No description
                            </span>

                            @endif

                        </td>


                        {{-- Products --}}

                        <td>

                            @php
                            $productCount = $brand->products()->count();
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

                        </td>


                        {{-- Created --}}

                        <td>

                            @if($brand->created_at)

                            {{ $brand->created_at->format('d M Y') }}

                            @else

                            <span class="muted">
                                -
                            </span>

                            @endif

                        </td>


                        {{-- Updated --}}

                        <td>

                            @if($brand->updated_at)

                            {{ $brand->updated_at->format('d M Y') }}

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
                A brand that is currently associated with products may not be deleted.
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
   FILTER BUTTONS
========================================================= */

    .brand-filter-button,
    .brand-reset-button {

        height: 40px;

        white-space: nowrap;

    }

    .brand-reset-button {

        display: inline-flex;

        align-items: center;

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

    .table-wrap {

        width: 100%;

        overflow-x: auto;

    }

    .table-wrap table {

        width: 100%;

        min-width: 950px;

        border-collapse: collapse;

    }

    .table-wrap th {

        padding: 13px 14px;

        border-bottom: 1px solid #e7ebf1;

        color: #718096;

        font-size: 10px;

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


    /* =========================================================
   SECONDARY TEXT
========================================================= */

    .secondary-text {

        margin-top: 4px;

        color: #8a94a6;

        font-size: 11px;

    }


    /* =========================================================
   DESCRIPTION
========================================================= */

    .brand-description {

        display: block;

        max-width: 340px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        color: #34415c;

    }


    /* =========================================================
   MUTED
========================================================= */

    .muted {

        color: #8a94a6;

        font-size: 12px;

    }


    /* =========================================================
   PRODUCT BADGE
========================================================= */

    .brand-product-badge {

        display: inline-flex;

        align-items: center;

        padding: 4px 9px;

        border-radius: 6px;

        background: #eaf7f3;

        color: #167d70;

        font-size: 11px;

        font-weight: 600;

        white-space: nowrap;

    }

    .brand-product-badge.empty {

        background: #f1f3f6;

        color: #7d8797;

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
   EMPTY
========================================================= */

    .empty {

        padding: 55px 20px;

        text-align: center;

        color: #718096;

        font-size: 13px;

    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1100px) {

        .brand-search {

            flex: 1 1 100%;

        }

    }

    @media (max-width: 700px) {

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

        .brand-description {

            max-width: 220px;

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


            confirmButton.addEventListener(
                'click',
                function() {

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