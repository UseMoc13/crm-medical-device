@extends('layouts.app')

@section('title', 'Contacts')

@section('content')

<div class="page-head">

    <div>
        <h1>Contacts</h1>

        <p>
            Manage contact information and customer relationships.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('contacts.create') }}"
            class="btn primary"
        >
            + New Contact
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

            <h3>Contact List</h3>

            <p>
                {{ $contacts->total() }} contacts found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('contacts.index') }}"
            class="contact-filter-bar"
        >

            {{-- Search --}}

            <div class="contact-search">

                <span class="contact-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search contacts..."
                    autocomplete="off"
                >

                @if(request('search'))

                    <a
                        href="{{ route(
                            'contacts.index',
                            request()->except('search', 'page')
                        ) }}"
                        class="contact-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Customer --}}

            <div class="contact-filter-select">

                <select name="customer_id">

                    <option value="">
                        All Customers
                    </option>

                    @foreach($customers as $customer)

                        <option
                            value="{{ $customer->customer_id }}"
                            @selected(
                                request('customer_id') ===
                                $customer->customer_id
                            )
                        >
                            {{ $customer->customer_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Contact Type --}}

            <div class="contact-filter-select">

                <select name="contact_type">

                    <option value="">
                        All Types
                    </option>

                    @foreach($contactTypes as $type)

                        <option
                            value="{{ $type }}"
                            @selected(
                                request('contact_type') === $type
                            )
                        >
                            {{ $type }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Primary --}}

            <div class="contact-filter-select">

                <select name="is_primary">

                    <option value="">
                        All Contacts
                    </option>

                    <option
                        value="1"
                        @selected(request('is_primary') === '1')
                    >
                        Primary
                    </option>

                    <option
                        value="0"
                        @selected(request('is_primary') === '0')
                    >
                        Non-primary
                    </option>

                </select>

            </div>


            {{-- Sort --}}

            <div class="contact-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>

                    <option
                        value="name"
                        @selected($sort === 'name')
                    >
                        Contact Name
                    </option>

                    <option
                        value="position"
                        @selected($sort === 'position')
                    >
                        Position
                    </option>

                    <option
                        value="department"
                        @selected($sort === 'department')
                    >
                        Department
                    </option>

                    <option
                        value="contact_type"
                        @selected($sort === 'contact_type')
                    >
                        Contact Type
                    </option>

                </select>

            </div>


            {{-- Direction --}}

            <div class="contact-filter-select sort-direction">

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
                class="btn contact-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'customer_id',
                'contact_type',
                'is_primary',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('contacts.index') }}"
                    class="btn contact-reset-button"
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
            request('customer_id') ||
            request('contact_type') ||
            request('is_primary')
        )

            <div class="contact-filter-summary">

                <span>
                    Showing filtered results
                </span>


                @if(request('search'))

                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                    </span>

                @endif


                @if(request('customer_id'))

                    @php

                        $selectedCustomer =
                            $customers->firstWhere(
                                'customer_id',
                                request('customer_id')
                            );

                    @endphp

                    @if($selectedCustomer)

                        <span class="filter-chip">

                            Customer:
                            {{ $selectedCustomer->customer_name }}

                        </span>

                    @endif

                @endif


                @if(request('contact_type'))

                    <span class="filter-chip">

                        Type:
                        {{ request('contact_type') }}

                    </span>

                @endif


                @if(
                    request('is_primary') !== null &&
                    request('is_primary') !== ''
                )

                    <span class="filter-chip">

                        Primary:

                        {{
                            request('is_primary') === '1'
                                ? 'Primary'
                                : 'Non-primary'
                        }}

                    </span>

                @endif

            </div>

        @endif


        {{-- =====================================================
             CONTACT TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Contact
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Position
                        </th>

                        <th>
                            Department
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Primary
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($contacts as $contact)

                    <tr>


                        {{-- Contact --}}

                        <td>

                            <a
                                href="{{ route(
                                    'contacts.show',
                                    $contact
                                ) }}"
                                class="contact-name-link"
                            >

                                <strong>
                                    {{ $contact->name }}
                                </strong>

                            </a>


                            @if($contact->email)

                                <div class="muted">
                                    {{ $contact->email }}
                                </div>

                            @endif

                        </td>


                        {{-- Customer --}}

                        <td>

                            @if($contact->customer)

                                <a
                                    href="{{ route(
                                        'customers.show',
                                        $contact->customer
                                    ) }}"
                                    class="customer-link"
                                >

                                    <strong>
                                        {{ $contact->customer->customer_name }}
                                    </strong>

                                </a>


                                <div class="muted">

                                    {{ $contact->customer->customer_code }}

                                </div>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Position --}}

                        <td>

                            {{ $contact->position ?? '-' }}

                        </td>


                        {{-- Department --}}

                        <td>

                            {{ $contact->department ?? '-' }}

                        </td>


                        {{-- Phone --}}

                        <td>

                            {{ $contact->phone ?? '-' }}

                        </td>


                        {{-- Type --}}

                        <td>

                            @if($contact->contact_type)

                                <span class="contact-type-badge">

                                    {{ $contact->contact_type }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Primary --}}

                        <td>

                            @if($contact->is_primary)

                                <span class="status-badge status-primary">
                                    Primary
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

                                <a
                                    href="{{ route(
                                        'contacts.show',
                                        $contact
                                    ) }}"
                                    class="action-btn view"
                                    title="View Contact"
                                    aria-label="View Contact"
                                >
                                    👁
                                </a>


                                <a
                                    href="{{ route(
                                        'contacts.edit',
                                        $contact
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Contact"
                                    aria-label="Edit Contact"
                                >
                                    ✎
                                </a>


                                <form
                                    action="{{ route(
                                        'contacts.destroy',
                                        $contact
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-contact-name="{{ $contact->name }}"
                                >

                                    @csrf

                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Contact"
                                        aria-label="Delete Contact"
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

                                No contacts found.

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

        @if($contacts->hasPages())

            <div class="pagination">

                {{ $contacts->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteContactModal"
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
        aria-labelledby="deleteContactModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteContactModalTitle">
                Delete Contact?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteContactName"></strong>?

            </p>


            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteContact"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteContact"
            >
                Delete Contact
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   CONTACT FILTER BAR
========================================================= */

.contact-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.contact-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.contact-search input {
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

.contact-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.contact-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.contact-search-clear {
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

.contact-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.contact-filter-select {
    position: relative;
}

.contact-filter-select select {
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

.contact-filter-select select:hover {
    border-color: #b7c0cf;
}

.contact-filter-select select:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.sort-direction select {
    min-width: 145px;
}


/* =========================================================
   FILTER BUTTONS
========================================================= */

.contact-filter-button {
    height: 40px;
    white-space: nowrap;
}

.contact-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.contact-filter-summary {
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
   CONTACT NAME
========================================================= */

.contact-name-link {
    color: inherit;
    text-decoration: none;
}

.contact-name-link:hover {
    color: #223a70;
}


/* =========================================================
   CUSTOMER LINK
========================================================= */

.customer-link {
    color: inherit;
    text-decoration: none;
}

.customer-link:hover {
    color: #223a70;
}


/* =========================================================
   CONTACT TYPE
========================================================= */

.contact-type-badge {
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
   PRIMARY STATUS
========================================================= */

.status-badge.status-primary {
    background: #e8f5f0;
    color: #16805f;
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

@media (max-width: 1100px) {

    .contact-search {
        flex: 1 1 100%;
    }

    .contact-filter-select {
        flex: 1 1 150px;
    }

    .contact-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .contact-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .contact-search,
    .contact-filter-select,
    .contact-filter-button,
    .contact-reset-button {
        width: 100%;
    }

    .contact-filter-button,
    .contact-reset-button {
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
        document.getElementById('deleteContactModal');

    const contactName =
        document.getElementById('deleteContactName');

    const confirmButton =
        document.getElementById('confirmDeleteContact');

    const cancelButton =
        document.getElementById('cancelDeleteContact');

    const closeOverlay =
        document.querySelector('[data-close-delete-modal]');

    let deleteForm = null;


    function openDeleteModal(form) {

        deleteForm = form;

        const name =
            form.dataset.contactName || 'this contact';

        contactName.textContent = name;

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