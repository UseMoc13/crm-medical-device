@extends('layouts.app')

@section('title', 'Customers')

@section('content')

<div class="page-head">

    <div>
        <h1>Customers</h1>

        <p>
            Manage customer information and relationships.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('customers.create') }}"
            class="btn primary"
        >
            + New Customer
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">
    {{ session('success') }}
</div>

@endif


<div class="card">

    <div class="card-head">

        <div>
            <h3>Customer List</h3>

            <p>
                {{ $customers->total() }} customers found
            </p>
        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('customers.index') }}"
            class="customer-filter-bar"
        >

            {{-- Search --}}

            <div class="customer-search">

                <span class="customer-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search customers..."
                    autocomplete="off"
                >

                @if(request('search'))

                    <a
                        href="{{ route('customers.index', request()->except('search', 'page')) }}"
                        class="customer-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Customer Type --}}

            <div class="customer-filter-select">

                <select name="customer_type">

                    <option value="">
                        All Types
                    </option>

                    @foreach($customerTypes as $type)

                        <option
                            value="{{ $type }}"
                            @selected(request('customer_type') === $type)
                        >
                            {{ $type }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Status --}}

            <div class="customer-filter-select">

                <select name="status">

                    <option value="">
                        All Status
                    </option>

                    @foreach($statuses as $status)

                        <option
                            value="{{ $status }}"
                            @selected(request('status') === $status)
                        >
                            {{ ucfirst($status) }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Sort --}}

            <div class="customer-filter-select">

                <select name="sort">

                    <option value="created_at" @selected($sort === 'created_at')>
                        Created Date
                    </option>

                    <option value="customer_code" @selected($sort === 'customer_code')>
                        Customer Code
                    </option>

                    <option value="customer_name" @selected($sort === 'customer_name')>
                        Customer Name
                    </option>

                    <option value="customer_type" @selected($sort === 'customer_type')>
                        Customer Type
                    </option>

                    <option value="city" @selected($sort === 'city')>
                        City
                    </option>

                    <option value="province" @selected($sort === 'province')>
                        Province
                    </option>

                    <option value="status" @selected($sort === 'status')>
                        Status
                    </option>

                </select>

            </div>


            {{-- Direction --}}

            <div class="customer-filter-select sort-direction">

                <select name="direction">

                    <option
                        value="asc"
                        @selected($direction === 'asc')
                    >
                        ↑ Ascending
                    </option>

                    <option
                        value="desc"
                        @selected($direction === 'desc')
                    >
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply --}}

            <button
                type="submit"
                class="btn customer-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'status',
                'customer_type',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('customers.index') }}"
                    class="btn customer-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =====================================================
             ACTIVE FILTER INFORMATION
        ====================================================== --}}

        @if(request('search') || request('customer_type') || request('status'))

            <div class="customer-filter-summary">

                <span>
                    Showing filtered results
                </span>

                @if(request('search'))

                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                    </span>

                @endif

                @if(request('customer_type'))

                    <span class="filter-chip">
                        Type: {{ request('customer_type') }}
                    </span>

                @endif

                @if(request('status'))

                    <span class="filter-chip">
                        Status: {{ ucfirst(request('status')) }}
                    </span>

                @endif

            </div>

        @endif


        {{-- =====================================================
             CUSTOMER TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Code
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Location
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

                    @forelse($customers as $customer)

                    <tr>

                        {{-- Code --}}

                        <td>

                            <strong>
                                {{ $customer->customer_code }}
                            </strong>

                        </td>


                        {{-- Customer --}}

                        <td>

                            <a
                                href="{{ route('customers.show', $customer) }}"
                                class="customer-name-link"
                            >

                                <strong>
                                    {{ $customer->customer_name }}
                                </strong>

                            </a>

                            @if($customer->email)

                                <div class="muted">
                                    {{ $customer->email }}
                                </div>

                            @endif

                        </td>


                        {{-- Type --}}

                        <td>

                            @if($customer->customer_type)

                                <span class="customer-type-badge">
                                    {{ $customer->customer_type }}
                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Phone --}}

                        <td>
                            {{ $customer->phone ?? '-' }}
                        </td>


                        {{-- Location --}}

                        <td>

                            @if($customer->city || $customer->province)

                                {{ $customer->city }}

                                @if($customer->province)

                                    , {{ $customer->province }}

                                @endif

                            @else

                                -

                            @endif

                        </td>


                        {{-- Status --}}

                        <td>

                            <span class="status-badge status-{{ strtolower($customer->status) }}">

                                {{ ucfirst($customer->status) }}

                            </span>

                        </td>


                        {{-- Created --}}

                        <td>

                            {{ $customer->created_at?->format('d M Y') }}

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="table-actions">

                                <a
                                    href="{{ route('customers.show', $customer) }}"
                                    class="action-btn view"
                                    title="View Customer"
                                    aria-label="View Customer"
                                >
                                    👁
                                </a>


                                <a
                                    href="{{ route('customers.edit', $customer) }}"
                                    class="action-btn edit"
                                    title="Edit Customer"
                                    aria-label="Edit Customer"
                                >
                                    ✎
                                </a>


                                <form
                                    action="{{ route('customers.destroy', $customer) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-customer-name="{{ $customer->customer_name }}"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Customer"
                                        aria-label="Delete Customer"
                                    >
                                        🗑
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>


                    @empty

                    <tr>

                        <td colspan="8">

                            <div class="empty">

                                No customers found.

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

        @if($customers->hasPages())

            <div class="pagination">

                {{ $customers->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteCustomerModal"
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
        aria-labelledby="deleteModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteModalTitle">
                Delete Customer?
            </h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteCustomerName"></strong>?
            </p>

            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteCustomer"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteCustomer"
            >
                Delete Customer
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   CUSTOMER FILTER BAR
========================================================= */

.customer-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* Search */

.customer-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.customer-search input {
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

.customer-search input:focus {
    outline: none;
    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.customer-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.customer-search-clear {
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

.customer-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.customer-filter-select {
    position: relative;
}

.customer-filter-select select {
    min-width: 145px;
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

.customer-filter-select select:hover {
    border-color: #b7c0cf;
}

.customer-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.sort-direction select {
    min-width: 145px;
}


/* Buttons */

.customer-filter-button {
    height: 40px;
    white-space: nowrap;
}

.customer-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.customer-filter-summary {
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
   CUSTOMER NAME
========================================================= */

.customer-name-link {
    color: inherit;
    text-decoration: none;
}

.customer-name-link:hover {
    color: #223a70;
}


/* =========================================================
   CUSTOMER TYPE BADGE
========================================================= */

.customer-type-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 8px;

    border-radius: 6px;

    background: #f1f5f7;

    color: #34415c;

    font-size: 11px;

    font-weight: 600;
}


/* =========================================================
   STATUS
========================================================= */

.status-badge.status-active {
    background: #e8f5f0;
    color: #16805f;
}

.status-badge.status-inactive {
    background: #f2f3f5;
    color: #737b89;
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

@media (max-width: 900px) {

    .customer-search {
        flex: 1 1 100%;
    }

    .customer-filter-select {
        flex: 1 1 150px;
    }

    .customer-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .customer-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .customer-search,
    .customer-filter-select,
    .customer-filter-button,
    .customer-reset-button {
        width: 100%;
    }

    .customer-filter-button,
    .customer-reset-button {
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

document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById('deleteCustomerModal');

    const customerName =
        document.getElementById('deleteCustomerName');

    const confirmButton =
        document.getElementById('confirmDeleteCustomer');

    const cancelButton =
        document.getElementById('cancelDeleteCustomer');

    const closeOverlay =
        document.querySelector('[data-close-delete-modal]');

    let deleteForm = null;


    function openDeleteModal(form) {

        deleteForm = form;

        const name =
            form.dataset.customerName || 'this customer';

        customerName.textContent = name;

        modal.classList.add('open');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow = 'hidden';

    }


    function closeDeleteModal() {

        modal.classList.remove('open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.style.overflow = '';

        deleteForm = null;

    }


    document
        .querySelectorAll('.delete-form')
        .forEach(function (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();

                    openDeleteModal(form);

                }
            );

        });


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
                modal.classList.contains('open')
            ) {

                closeDeleteModal();

            }

        }
    );

});

</script>

@endsection