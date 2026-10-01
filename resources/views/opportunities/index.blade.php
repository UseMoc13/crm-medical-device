@extends('layouts.app')

@section('title', 'Opportunities')

@section('content')

<div class="page-head">

    <div>

        <h1>Opportunities</h1>

        <p>
            Manage sales opportunities and potential business deals.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('opportunities.create') }}"
            class="btn primary"
        >
            + New Opportunity
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

            <h3>Opportunity List</h3>

            <p>
                {{ $opportunities->total() }} opportunities found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('opportunities.index') }}"
            class="opportunity-filter-bar"
        >


            {{-- Search --}}

            <div class="opportunity-search">

                <span class="opportunity-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search opportunities..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'opportunities.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="opportunity-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Sales --}}

            <div class="opportunity-filter-select">

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

            <div class="opportunity-filter-select">

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


            {{-- Stage --}}

            <div class="opportunity-filter-select">

                <select name="stage">

                    <option value="">
                        All Stages
                    </option>


                    @foreach($stages as $stage)

                        <option
                            value="{{ $stage }}"
                            @selected(
                                request('stage') === $stage
                            )
                        >
                            {{ $stage }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Status --}}

            <div class="opportunity-filter-select">

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


            {{-- Sort --}}

            <div class="opportunity-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>


                    <option
                        value="opportunity_code"
                        @selected($sort === 'opportunity_code')
                    >
                        Opportunity Code
                    </option>


                    <option
                        value="name"
                        @selected($sort === 'name')
                    >
                        Name
                    </option>


                    <option
                        value="stage"
                        @selected($sort === 'stage')
                    >
                        Stage
                    </option>


                    <option
                        value="estimated_value"
                        @selected($sort === 'estimated_value')
                    >
                        Estimated Value
                    </option>


                    <option
                        value="expected_close_date"
                        @selected($sort === 'expected_close_date')
                    >
                        Expected Close
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

            <div class="opportunity-filter-select sort-direction">

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
                class="btn opportunity-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'user_id',
                'customer_id',
                'stage',
                'status',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('opportunities.index') }}"
                    class="btn opportunity-reset-button"
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
            request('stage') ||
            request('status')
        )

            <div class="opportunity-filter-summary">

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


                {{-- Sales --}}

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


                {{-- Customer --}}

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


                {{-- Stage --}}

                @if(request('stage'))

                    <span class="filter-chip">

                        Stage:
                        {{ request('stage') }}

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
             OPPORTUNITY TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Opportunity
                        </th>

                        <th>
                            Customer
                        </th>

                        <th>
                            Sales
                        </th>

                        <th>
                            Stage
                        </th>

                        <th>
                            Estimated Value
                        </th>

                        <th>
                            Expected Close
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

                    @forelse($opportunities as $opportunity)

                    <tr>


                        {{-- Opportunity --}}

                        <td>

                            <a
                                href="{{ route(
                                    'opportunities.show',
                                    $opportunity
                                ) }}"
                                class="opportunity-code-link"
                            >

                                <strong>
                                    {{ $opportunity->opportunity_code }}
                                </strong>

                            </a>


                            <div class="muted">

                                {{ $opportunity->name }}

                            </div>


                            <div class="muted">

                                {{ $opportunity->created_at
                                    ? $opportunity->created_at->format('d M Y')
                                    : '-'
                                }}

                            </div>

                        </td>


                        {{-- Customer --}}

                        <td>

                            @if($opportunity->customer)

                                <strong>
                                    {{ $opportunity->customer->customer_name }}
                                </strong>


                                @if($opportunity->customer->customer_code)

                                    <div class="muted">

                                        {{ $opportunity->customer->customer_code }}

                                    </div>

                                @endif

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Sales --}}

                        <td>

                            @if($opportunity->user)

                                <strong>
                                    {{ $opportunity->user->name }}
                                </strong>


                                @if($opportunity->user->email)

                                    <div class="muted">

                                        {{ $opportunity->user->email }}

                                    </div>

                                @endif

                            @else

                                <span class="unassigned-badge">
                                    Unassigned
                                </span>

                            @endif

                        </td>


                        {{-- Stage --}}

                        <td>

                            @if($opportunity->stage)

                                <span
                                    class="opportunity-stage-badge
                                    opportunity-stage-{{ Str::slug($opportunity->stage) }}"
                                >

                                    {{ $opportunity->stage }}

                                </span>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Estimated Value --}}

                        <td>

                            @if($opportunity->estimated_value !== null)

                                <strong class="opportunity-value">

                                    Rp
                                    {{ number_format(
                                        $opportunity->estimated_value,
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


                        {{-- Expected Close --}}

                        <td>

                            @if($opportunity->expected_close_date)

                                {{ $opportunity->expected_close_date->format('d M Y') }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($opportunity->status)

                                <span
                                    class="opportunity-status-badge
                                    opportunity-status-{{ Str::slug($opportunity->status) }}"
                                >

                                    {{ $opportunity->status }}

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
                                        'opportunities.show',
                                        $opportunity
                                    ) }}"
                                    class="action-btn view"
                                    title="View Opportunity"
                                    aria-label="View Opportunity"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'opportunities.edit',
                                        $opportunity
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Opportunity"
                                    aria-label="Edit Opportunity"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'opportunities.destroy',
                                        $opportunity
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-opportunity-name="{{ $opportunity->name }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Opportunity"
                                        aria-label="Delete Opportunity"
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

                                No opportunities found.

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

        @if($opportunities->hasPages())

            <div class="pagination">

                {{ $opportunities->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteOpportunityModal"
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
        aria-labelledby="deleteOpportunityModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteOpportunityModalTitle">
                Delete Opportunity?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteOpportunityName"></strong>?

            </p>


            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteOpportunity"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteOpportunity"
            >
                Delete Opportunity
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   OPPORTUNITY FILTER BAR
========================================================= */

.opportunity-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.opportunity-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.opportunity-search input {
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

.opportunity-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.opportunity-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.opportunity-search-clear {
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

.opportunity-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.opportunity-filter-select {
    position: relative;
}

.opportunity-filter-select select {
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

.opportunity-filter-select select:hover {
    border-color: #b7c0cf;
}

.opportunity-filter-select select:focus {
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

.opportunity-filter-button {
    height: 40px;
    white-space: nowrap;
}

.opportunity-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.opportunity-filter-summary {
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
   OPPORTUNITY CODE
========================================================= */

.opportunity-code-link {
    color: inherit;
    text-decoration: none;
}

.opportunity-code-link:hover {
    color: #223a70;
}


/* =========================================================
   VALUE
========================================================= */

.opportunity-value {
    color: #17284f;
    white-space: nowrap;
}


/* =========================================================
   STAGE
========================================================= */

.opportunity-stage-badge {
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
   STAGE VARIANTS
========================================================= */

.opportunity-stage-lead {
    background: #edf3ff;
    color: #315caa;
}

.opportunity-stage-qualified {
    background: #eaf7f3;
    color: #167d70;
}

.opportunity-stage-proposal {
    background: #fff6e5;
    color: #a66b12;
}

.opportunity-stage-negotiation {
    background: #eeeafd;
    color: #6552a6;
}

.opportunity-stage-closed {
    background: #f1f1f1;
    color: #707070;
}


/* =========================================================
   STATUS
========================================================= */

.opportunity-status-badge {
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

.opportunity-status-open {
    background: #eaf7f3;
    color: #167d70;
}

.opportunity-status-active {
    background: #edf3ff;
    color: #315caa;
}

.opportunity-status-won {
    background: #e8f5f0;
    color: #16805f;
}

.opportunity-status-lost {
    background: #fff0f0;
    color: #a14d4d;
}

.opportunity-status-closed {
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

    .opportunity-search {
        flex: 1 1 100%;
    }

    .opportunity-filter-select {
        flex: 1 1 150px;
    }

    .opportunity-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .opportunity-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .opportunity-search,
    .opportunity-filter-select,
    .opportunity-filter-button,
    .opportunity-reset-button {
        width: 100%;
    }

    .opportunity-filter-button,
    .opportunity-reset-button {
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
                'deleteOpportunityModal'
            );


        const opportunityName =
            document.getElementById(
                'deleteOpportunityName'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteOpportunity'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteOpportunity'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const name =
                form.dataset.opportunityName ||
                'this opportunity';


            opportunityName.textContent =
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