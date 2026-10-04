@extends('layouts.app')

@section('title', 'Quotations')

@section('content')

<div class="page-head">

    <div>

        <h1>Quotations</h1>

        <p>
            Manage quotations and sales proposals for business opportunities.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('quotations.create') }}"
            class="btn primary"
        >
            + New Quotation
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


<div class="card">

    <div class="card-head">

        <div>

            <h3>Quotation List</h3>

            <p>
                {{ $quotations->total() }} quotations found
            </p>

        </div>

    </div>


    <div class="card-body">


        {{-- =====================================================
             FILTER BAR
        ====================================================== --}}

        <form
            method="GET"
            action="{{ route('quotations.index') }}"
            class="quotation-filter-bar"
        >


            {{-- Search --}}

            <div class="quotation-search">

                <span class="quotation-search-icon">
                    ⌕
                </span>


                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search quotations..."
                    autocomplete="off"
                >


                @if(request('search'))

                    <a
                        href="{{ route(
                            'quotations.index',
                            request()->except(
                                'search',
                                'page'
                            )
                        ) }}"
                        class="quotation-search-clear"
                        title="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Opportunity --}}

            <div class="quotation-filter-select">

                <select name="opportunity_id">

                    <option value="">
                        All Opportunities
                    </option>


                    @foreach($opportunities as $opportunity)

                        <option
                            value="{{ $opportunity->opportunity_id }}"
                            @selected(
                                request('opportunity_id') ===
                                $opportunity->opportunity_id
                            )
                        >
                            {{ $opportunity->opportunity_code }}
                            —
                            {{ $opportunity->name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Status --}}

            <div class="quotation-filter-select">

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

            <div class="quotation-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected($sort === 'created_at')
                    >
                        Created Date
                    </option>


                    <option
                        value="quotation_number"
                        @selected($sort === 'quotation_number')
                    >
                        Quotation Number
                    </option>


                    <option
                        value="quotation_date"
                        @selected($sort === 'quotation_date')
                    >
                        Quotation Date
                    </option>


                    <option
                        value="valid_until"
                        @selected($sort === 'valid_until')
                    >
                        Valid Until
                    </option>


                    <option
                        value="subtotal"
                        @selected($sort === 'subtotal')
                    >
                        Subtotal
                    </option>


                    <option
                        value="total_amount"
                        @selected($sort === 'total_amount')
                    >
                        Total Amount
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

            <div class="quotation-filter-select sort-direction">

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
                class="btn quotation-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'opportunity_id',
                'status',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('quotations.index') }}"
                    class="btn quotation-reset-button"
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
            request('opportunity_id') ||
            request('status')
        )

            <div class="quotation-filter-summary">

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


                {{-- Opportunity --}}

                @if(request('opportunity_id'))

                    @php

                        $selectedOpportunity =
                            $opportunities->firstWhere(
                                'opportunity_id',
                                request('opportunity_id')
                            );

                    @endphp


                    @if($selectedOpportunity)

                        <span class="filter-chip">

                            Opportunity:
                            {{ $selectedOpportunity->opportunity_code }}

                        </span>

                    @endif

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
             QUOTATION TABLE
        ====================================================== --}}

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Quotation
                        </th>

                        <th>
                            Opportunity
                        </th>

                        <th>
                            Quotation Date
                        </th>

                        <th>
                            Valid Until
                        </th>

                        <th>
                            Subtotal
                        </th>

                        <th>
                            Total Amount
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

                    @forelse($quotations as $quotation)

                    <tr>


                        {{-- Quotation --}}

                        <td>

                            <a
                                href="{{ route(
                                    'quotations.show',
                                    $quotation
                                ) }}"
                                class="quotation-number-link"
                            >

                                <strong>
                                    {{ $quotation->quotation_number }}
                                </strong>

                            </a>


                            <div class="muted">

                                Created:
                                {{ $quotation->created_at
                                    ? $quotation->created_at->format('d M Y')
                                    : '-'
                                }}

                            </div>

                        </td>


                        {{-- Opportunity --}}

                        <td>

                            @if($quotation->opportunity)

                                <a
                                    href="{{ route(
                                        'opportunities.show',
                                        $quotation->opportunity
                                    ) }}"
                                    class="quotation-opportunity-link"
                                >

                                    <strong>
                                        {{ $quotation->opportunity->opportunity_code }}
                                    </strong>

                                </a>


                                <div class="muted">

                                    {{ $quotation->opportunity->name }}

                                </div>

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Quotation Date --}}

                        <td>

                            @if($quotation->quotation_date)

                                {{ $quotation->quotation_date->format('d M Y') }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Valid Until --}}

                        <td>

                            @if($quotation->valid_until)

                                {{ $quotation->valid_until->format('d M Y') }}

                            @else

                                <span class="muted">
                                    -
                                </span>

                            @endif

                        </td>


                        {{-- Subtotal --}}

                        <td>

                            <strong class="quotation-value">

                                Rp
                                {{ number_format(
                                    (float) $quotation->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </td>


                        {{-- Total Amount --}}

                        <td>

                            <strong class="quotation-total">

                                Rp
                                {{ number_format(
                                    (float) $quotation->total_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </td>


                        {{-- Status --}}

                        <td>

                            @if($quotation->status)

                                <span
                                    class="quotation-status-badge
                                    quotation-status-{{ Str::slug($quotation->status) }}"
                                >

                                    {{ $quotation->status }}

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
                                        'quotations.show',
                                        $quotation
                                    ) }}"
                                    class="action-btn view"
                                    title="View Quotation"
                                    aria-label="View Quotation"
                                >
                                    👁
                                </a>


                                {{-- Edit --}}

                                <a
                                    href="{{ route(
                                        'quotations.edit',
                                        $quotation
                                    ) }}"
                                    class="action-btn edit"
                                    title="Edit Quotation"
                                    aria-label="Edit Quotation"
                                >
                                    ✎
                                </a>


                                {{-- Delete --}}

                                <form
                                    action="{{ route(
                                        'quotations.destroy',
                                        $quotation
                                    ) }}"
                                    method="POST"
                                    class="delete-form"
                                    data-quotation-number="{{ $quotation->quotation_number }}"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete Quotation"
                                        aria-label="Delete Quotation"
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

                                No quotations found.

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

        @if($quotations->hasPages())

            <div class="pagination">

                {{ $quotations->links() }}

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     DELETE CONFIRMATION MODAL
========================================================= --}}

<div
    class="delete-modal"
    id="deleteQuotationModal"
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
        aria-labelledby="deleteQuotationModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteQuotationModalTitle">
                Delete Quotation?
            </h3>


            <p>

                Are you sure you want to delete
                <strong id="deleteQuotationNumber"></strong>?

            </p>


            <span>
                This action cannot be undone.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteQuotation"
            >
                Cancel
            </button>


            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteQuotation"
            >
                Delete Quotation
            </button>

        </div>

    </div>

</div>


<style>

/* =========================================================
   QUOTATION FILTER BAR
========================================================= */

.quotation-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =========================================================
   SEARCH
========================================================= */

.quotation-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.quotation-search input {
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

.quotation-search input:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.quotation-search-icon {
    position: absolute;

    left: 13px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 19px;

    pointer-events: none;
}

.quotation-search-clear {
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

.quotation-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =========================================================
   FILTER SELECT
========================================================= */

.quotation-filter-select {
    position: relative;
}

.quotation-filter-select select {
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

.quotation-filter-select select:hover {
    border-color: #b7c0cf;
}

.quotation-filter-select select:focus {
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

.quotation-filter-button {
    height: 40px;
    white-space: nowrap;
}

.quotation-reset-button {
    height: 40px;

    display: inline-flex;
    align-items: center;

    white-space: nowrap;
}


/* =========================================================
   FILTER SUMMARY
========================================================= */

.quotation-filter-summary {
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
   QUOTATION NUMBER
========================================================= */

.quotation-number-link {
    color: inherit;
    text-decoration: none;
}

.quotation-number-link:hover {
    color: #223a70;
}


/* =========================================================
   OPPORTUNITY
========================================================= */

.quotation-opportunity-link {
    color: inherit;
    text-decoration: none;
}

.quotation-opportunity-link:hover {
    color: #223a70;
}


/* =========================================================
   AMOUNT
========================================================= */

.quotation-value {
    color: #17284f;
    white-space: nowrap;
}

.quotation-total {
    color: #15966f;
    white-space: nowrap;
}


/* =========================================================
   STATUS
========================================================= */

.quotation-status-badge {
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

.quotation-status-draft {
    background: #f1f3f6;
    color: #667085;
}

.quotation-status-sent {
    background: #edf3ff;
    color: #315caa;
}

.quotation-status-approved {
    background: #e8f5f0;
    color: #16805f;
}

.quotation-status-rejected {
    background: #fff0f0;
    color: #a14d4d;
}

.quotation-status-expired {
    background: #fff4e5;
    color: #9a6a18;
}

.quotation-status-cancelled {
    background: #f1f1f1;
    color: #707070;
}

.quotation-status-pending {
    background: #fff6e5;
    color: #a66b12;
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

    .quotation-search {
        flex: 1 1 100%;
    }

    .quotation-filter-select {
        flex: 1 1 150px;
    }

    .quotation-filter-select select {
        width: 100%;
    }

}


@media (max-width: 600px) {

    .quotation-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .quotation-search,
    .quotation-filter-select,
    .quotation-filter-button,
    .quotation-reset-button {
        width: 100%;
    }

    .quotation-filter-button,
    .quotation-reset-button {
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
                'deleteQuotationModal'
            );


        const quotationNumber =
            document.getElementById(
                'deleteQuotationNumber'
            );


        const confirmButton =
            document.getElementById(
                'confirmDeleteQuotation'
            );


        const cancelButton =
            document.getElementById(
                'cancelDeleteQuotation'
            );


        const closeOverlay =
            document.querySelector(
                '[data-close-delete-modal]'
            );


        let deleteForm = null;


        function openDeleteModal(form) {

            deleteForm = form;


            const number =
                form.dataset.quotationNumber ||
                'this quotation';


            quotationNumber.textContent =
                number;


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