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
            class="btn primary"
        >
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

                <li>{{ $error }}</li>

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
                categor{{ $categories->total() != 1 ? 'ies' : 'y' }}
                available.
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             SEARCH
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('product-categories.index') }}"
            class="filter-bar"
        >

            <div class="search-box">

                <span class="product-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search categories..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'product-categories.index',
                            request()->except('search')
                        ) }}"
                        class="search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            <button
                type="submit"
                class="btn"
            >
                Search
            </button>


            @if(request('search'))

                <a
                    href="{{ route('product-categories.index') }}"
                    class="btn"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER
        ====================================================== --}}

        @if(request('search'))

            <div class="filter-summary">

                <span>
                    Active filter:
                </span>


                <span class="filter-chip">
                    Search:
                    {{ request('search') }}
                </span>

            </div>

        @endif


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Category
                        </th>

                        <th>
                            Products
                        </th>

                        <th>
                            Created
                        </th>

                        <th class="actions-column">
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($categories as $category)

                        <tr>

                            {{-- Category --}}

                            <td>

                                <a
                                    href="{{ route(
                                        'product-categories.show',
                                        $category
                                    ) }}"
                                    class="primary-link"
                                >
                                    {{ $category->category_name }}
                                </a>

                                <div class="secondary-text">

                                    ID:
                                    {{ $category->category_id }}

                                </div>

                            </td>


                            {{-- Product Count --}}

                            <td>

                                @if(method_exists($category, 'products'))

                                    <span class="count-badge">
                                        {{ $category->products()->count() }}
                                        product(s)
                                    </span>

                                @else

                                    <span class="muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Created --}}

                            <td>

                                @if($category->created_at)

                                    <div>
                                        {{ $category->created_at->format('d M Y') }}
                                    </div>

                                    <div class="secondary-text">
                                        {{ $category->created_at->format('H:i') }}
                                    </div>

                                @else

                                    <span class="muted">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="{{ route(
                                            'product-categories.show',
                                            $category
                                        ) }}"
                                        class="icon-btn"
                                        title="View Category"
                                    >
                                        👁
                                    </a>


                                    <a
                                        href="{{ route(
                                            'product-categories.edit',
                                            $category
                                        ) }}"
                                        class="icon-btn"
                                        title="Edit Category"
                                    >
                                        ✎
                                    </a>


                                    <button
                                        type="button"
                                        class="icon-btn danger delete-category-button"
                                        title="Delete Category"
                                        data-category-id="{{ $category->category_id }}"
                                        data-category-name="{{ $category->category_name }}"
                                    >
                                        🗑
                                    </button>

                                </div>


                                {{-- Hidden Delete Form --}}

                                <form
                                    id="delete-form-{{ $category->category_id }}"
                                    action="{{ route(
                                        'product-categories.destroy',
                                        $category
                                    ) }}"
                                    method="POST"
                                    style="display: none;"
                                >

                                    @csrf

                                    @method('DELETE')

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="4">

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

            <div class="pagination-wrap">

                {{ $categories->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteCategoryModal"
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
        aria-labelledby="deleteCategoryModalTitle"
    >

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
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteCategory"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteCategory"
            >
                Delete Category
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   FILTER
========================================================= */

.filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 260px;
}

.search-box input {
    width: 100%;
    height: 40px;

    box-sizing: border-box;

    padding: 0 36px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;
    font-size: 13px;
}

.search-box input:focus {
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

.search-clear {
    position: absolute;

    right: 11px;
    top: 50%;

    transform: translateY(-50%);

    color: #8a94a6;

    font-size: 18px;

    text-decoration: none;
}

.search-clear:hover {
    color: #17284f;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.filter-summary {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 7px;

    margin-bottom: 16px;

    color: #718096;

    font-size: 12px;
}

.filter-chip {
    padding: 4px 8px;

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

    min-width: 850px;

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
   CATEGORY
========================================================= */

.primary-link {
    display: inline-block;

    color: #17284f;

    font-size: 13px;
    font-weight: 700;

    text-decoration: none;
}

.primary-link:hover {
    color: #2ba7a0;
}

.secondary-text {
    margin-top: 4px;

    color: #8a94a6;

    font-size: 11px;
}

.muted {
    color: #9aa3b1;
}


/* =========================================================
   COUNT
========================================================= */

.count-badge {
    display: inline-flex;
    align-items: center;

    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;
    font-weight: 600;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions-column {
    text-align: center !important;
}

.table-actions {
    display: flex;

    align-items: center;
    justify-content: center;

    gap: 6px;
}

.icon-btn {
    width: 32px;
    height: 32px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 0;

    border: 1px solid #dfe4eb;
    border-radius: 7px;

    background: #fff;

    color: #4b5870;

    font-size: 13px;

    text-decoration: none;

    cursor: pointer;

    transition:
        background .18s ease,
        border-color .18s ease,
        color .18s ease;
}

.icon-btn:hover {
    background: #f4f7f9;

    border-color: #cbd3df;

    color: #17284f;
}

.icon-btn.danger:hover {
    background: #fff2f2;

    border-color: #e6b7b7;

    color: #c94a4a;
}


/* =========================================================
   EMPTY
========================================================= */

.empty {
    padding: 42px 20px;

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

    padding: 20px;
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

    padding: 26px;

    border-radius: 14px;

    background: #fff;

    box-shadow:
        0 20px 55px rgba(23, 40, 79, .22);

    animation: deleteModalIn .18s ease;
}

@keyframes deleteModalIn {

    from {
        opacity: 0;
        transform: translateY(8px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }

}

.delete-modal-icon {
    width: 44px;
    height: 44px;

    display: flex;

    align-items: center;
    justify-content: center;

    margin-bottom: 14px;

    border-radius: 50%;

    background: #fff0f0;

    color: #c94a4a;

    font-size: 20px;
    font-weight: 700;
}

.delete-modal-content h3 {
    margin: 0 0 8px;

    color: #17284f;

    font-size: 18px;
}

.delete-modal-content p {
    margin: 0;

    color: #718096;

    font-size: 13px;

    line-height: 1.6;
}

.delete-modal-content p strong {
    color: #34415c;
}

.delete-modal-content span {
    display: block;

    margin-top: 4px;

    color: #8a94a6;

    font-size: 12px;
}

.delete-modal-actions {
    display: flex;

    align-items: center;
    justify-content: flex-end;

    gap: 8px;

    margin-top: 24px;
}

.delete-confirm-button {
    border-color: #c94a4a !important;

    background: #c94a4a !important;

    color: #fff !important;
}

.delete-confirm-button:hover {
    border-color: #b64040 !important;

    background: #b64040 !important;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .search-box {
        width: 100%;

        min-width: 100%;

        flex-basis: 100%;
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

document.addEventListener('DOMContentLoaded', function () {

    let deleteForm = null;


    /* =====================================================
       OPEN DELETE MODAL
    ====================================================== */

    document.querySelectorAll(
        '.delete-category-button'
    ).forEach(function (button) {

        button.addEventListener(
            'click',
            function () {

                const categoryId =
                    this.dataset.categoryId;

                const categoryName =
                    this.dataset.categoryName;

                deleteForm =
                    document.getElementById(
                        'delete-form-' + categoryId
                    );


                document.getElementById(
                    'deleteCategoryName'
                ).textContent = categoryName;


                const modal =
                    document.getElementById(
                        'deleteCategoryModal'
                    );


                modal.classList.add('open');

                modal.setAttribute(
                    'aria-hidden',
                    'false'
                );

            }
        );

    });


    /* =====================================================
       CLOSE DELETE MODAL
    ====================================================== */

    function closeDeleteModal() {

        const modal =
            document.getElementById(
                'deleteCategoryModal'
            );


        modal.classList.remove('open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );


        deleteForm = null;

    }


    /* =====================================================
       CANCEL
    ====================================================== */

    document.getElementById(
        'cancelDeleteCategory'
    ).addEventListener(
        'click',
        closeDeleteModal
    );


    /* =====================================================
       OVERLAY
    ====================================================== */

    document.querySelector(
        '[data-close-delete-modal]'
    ).addEventListener(
        'click',
        closeDeleteModal
    );


    /* =====================================================
       CONFIRM DELETE
    ====================================================== */

    document.getElementById(
        'confirmDeleteCategory'
    ).addEventListener(
        'click',
        function () {

            if (deleteForm) {

                deleteForm.submit();

            }

        }
    );


    /* =====================================================
       ESCAPE
    ====================================================== */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape'
            ) {

                closeDeleteModal();

            }

        }
    );

});

</script>

@endsection