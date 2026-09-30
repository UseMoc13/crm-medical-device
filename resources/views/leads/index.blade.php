@extends('layouts.app')

@section('title', 'Leads')

@section('content')

<div class="page-head">

    <div>

        <h1>Leads</h1>

        <p>
            Manage potential customers and sales opportunities.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('leads.create') }}"
            class="btn primary"
        >
            + New Lead
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

            <h3>Lead List</h3>

            <p>
                {{ $leads->total() }} leads found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('leads.index') }}"
            class="lead-filter-bar"
        >


            {{-- Search --}}

            <div class="lead-search">

                <span class="lead-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search leads..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'leads.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="lead-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- User / Sales --}}

            <div class="lead-filter-select">

                <select name="user_id">

                    <option value="">
                        All Sales
                    </option>


                    @foreach($users as $user)

                        <option
                            value="{{ $user->user_id }}"
                            @selected(
                                request('user_id') ===
                                $user->user_id
                            )
                        >
                            {{ $user->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Customer --}}

            <div class="lead-filter-select">

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


            {{-- Status --}}

            <div class="lead-filter-select">

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


            {{-- Source --}}

            <div class="lead-filter-select">

                <select name="source">

                    <option value="">
                        All Sources
                    </option>


                    @foreach($sources as $source)

                        <option
                            value="{{ $source }}"
                            @selected(
                                request('source') === $source
                            )
                        >
                            {{ $source }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Sort --}}

            <div class="lead-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>


                    <option
                        value="lead_code"
                        @selected($sort === 'lead_code')
                    >
                        Lead Code
                    </option>


                    <option
                        value="company_name"
                        @selected($sort === 'company_name')
                    >
                        Company
                    </option>


                    <option
                        value="contact_name"
                        @selected($sort === 'contact_name')
                    >
                        Contact
                    </option>


                    <option
                        value="source"
                        @selected($sort === 'source')
                    >
                        Source
                    </option>


                    <option
                        value="status"
                        @selected($sort === 'status')
                    >
                        Status
                    </option>

                </select>

            </div>


            {{-- Direction --}}

            <div class="lead-filter-select sort-direction">

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
                class="btn lead-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'user_id',
                'customer_id',
                'status',
                'source',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('leads.index') }}"
                    class="btn lead-reset-button"
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
            request('user_id') ||
            request('customer_id') ||
            request('status') ||
            request('source')
        )

            <div class="lead-filter-summary">

                <span>
                    Showing filtered results
                </span>


                @if(request('search'))

                    <span class="filter-chip">

                        Search:
                        "{{ request('search') }}"

                    </span>

                @endif


                @if(request('user_id'))

                    @php

                        $selectedUser =
                            $users->firstWhere(
                                'user_id',
                                request('user_id')
                            );

                    @endphp


                    @if($selectedUser)

                        <span class="filter-chip">

                            Sales:
                            {{ $selectedUser->name }}

                        </span>

                    @endif

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


                @if(request('status'))

                    <span class="filter-chip">

                        Status:
                        {{ request('status') }}

                    </span>

                @endif


                @if(request('source'))

                    <span class="filter-chip">

                        Source:
                        {{ request('source') }}

                    </span>

                @endif

            </div>

        @endif


        {{-- =====================================================
             LEAD TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Lead
                        </th>

                        <th>
                            Company
                        </th>

                        <th>
                            Contact
                        </th>

                        <th>
                            Phone
                        </th>

                        <th>
                            Source
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Assigned To
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($leads as $lead)

                    <tr>


                        {{-- Lead --}}

                        <td>

                            <a
                                href="{{ route(
                                    'leads.show',
                                    $lead
                                ) }}"
                                class="lead-code-link"
                            >

                                <strong>
                                    {{ $lead->lead_code }}
                                </strong>

                            </a>


                            <div class="muted">

                                {{ $lead->created_at
                                    ? $lead->created_at->format('d M Y')
                                    : '-'
                                }}

                            </div>

                        </td>


                        {{-- Company --}}

                        <td>

                            <strong>
                                {{ $lead->company_name }}
                            </strong>


                            @if($lead->customer)

                                <div class="muted">

                                    Customer:
                                    {{ $lead->customer->customer_name }}

                                </div>

                            @endif

                        </td>


                        {{-- Contact --}}

                        <td>

                            @if($lead->contact_name)

                                <strong>
                                    {{ $lead->contact_name }}
                                </strong>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif


                            @if($lead->email)

                                <div class="muted">

                                    {{ $lead->email }}

                                </div>

                            @endif

                        </td>


                        {{-- Phone --}}

                        <td>

                            {{ $lead->phone ?? '-' }}

                        </td>


                        {{-- Source --}}

                        <td>

                            @if($lead->source)

                                <span class="lead-source-badge">

                                    {{ $lead->source }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($lead->status)

                                <span
                                    class="lead-status-badge
                                    lead-status-{{ Str::slug($lead->status) }}"
                                >

                                    {{ $lead->status }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Assigned User --}}

                        <td>

                            @if($lead->user)

                                <strong>
                                    {{ $lead->user->name }}
                                </strong>

                                @if($lead->user->email)

                                    <div class="muted">

                                        {{ $lead->user->email }}

                                    </div>

                                @endif

                            @else

                                <span class="unassigned-badge">
                                    Unassigned
                                </span>

                            @endif

                        </td>


                        {{-- Actions --}}

                        <td>

                            <div class="table-actions">


                                {{-- View --}}

                                <a
                                    href="{{ route(
                                        'leads.show',
                                        $lead
                                    ) }}"
                                    class="action-btn view"
                                    title="View Lead"
                                    aria-label="View Lead"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'leads.edit',
                                        $lead
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Lead"
                                    aria-label="Edit Lead"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'leads.destroy',
                                        $lead
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-lead-name="{{ $lead->company_name }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Lead"
                                        aria-label="Delete Lead"
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

                                No leads found.

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

        @if($leads->hasPages())

            <div class="pagination">

                {{ $leads->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteLeadModal"
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
        aria-labelledby="deleteLeadModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteLeadModalTitle">
                Delete Lead?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteLeadName"></strong>?

            </p>


            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteLead"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteLead"
            >
                Delete Lead
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   LEAD FILTER BAR
========================================================= */

.lead-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.lead-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.lead-search input {
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

.lead-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.lead-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.lead-search-clear {
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

.lead-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.lead-filter-select {
    position: relative;
}

.lead-filter-select select {
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

.lead-filter-select select:hover {
    border-color: #b7c0cf;
}

.lead-filter-select select:focus {
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

.lead-filter-button {
    height: 40px;
    white-space: nowrap;
}

.lead-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.lead-filter-summary {
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
   LEAD CODE
========================================================= */

.lead-code-link {
    color: inherit;
    text-decoration: none;
}

.lead-code-link:hover {
    color: #223a70;
}


/* =========================================================
   SOURCE
========================================================= */

.lead-source-badge {
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

.lead-status-badge {
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

.lead-status-new {
    background: #edf3ff;
    color: #315caa;
}

.lead-status-open {
    background: #eaf7f3;
    color: #167d70;
}

.lead-status-contacted {
    background: #fff6e5;
    color: #a66b12;
}

.lead-status-qualified {
    background: #e8f5f0;
    color: #16805f;
}

.lead-status-unqualified {
    background: #f3f0f0;
    color: #7a6262;
}

.lead-status-closed {
    background: #f1f1f1;
    color: #707070;
}


/* =========================================================
   UNASSIGNED
========================================================= */

.unassigned-badge {
    display: inline-flex;

    align-items: center;

    padding: 4px 8px;

    border-radius: 6px;

    background: #fff4e5;

    color: #9a6a18;

    font-size: 11px;

    font-weight: 600;
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

    .lead-search {
        flex: 1 1 100%;
    }

    .lead-filter-select {
        flex: 1 1 150px;
    }

    .lead-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .lead-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .lead-search,
    .lead-filter-select,
    .lead-filter-button,
    .lead-reset-button {
        width: 100%;
    }

    .lead-filter-button,
    .lead-reset-button {
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
                'deleteLeadModal'
            );


        const leadName =
            document.getElementById(
                'deleteLeadName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteLead'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteLead'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.leadName ||
                'this lead';


            leadName.textContent =
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