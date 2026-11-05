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
                        <th class="col-number">No.</th>
                        <th class="col-quotation">Quotation</th>
                        <th class="col-opportunity">Opportunity</th>
                        <th class="col-date">Quotation Date</th>
                        <th class="col-valid-until">Valid Until</th>
                        <th class="col-subtotal">Subtotal</th>
                        <th class="col-total">Total Amount</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                        <th class="col-sales-order">Sales Order</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($quotations as $quotation)

                    <tr>
                        <td class="col-number">
                            {{ $quotations->firstItem() + $loop->index }}
                        </td>

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


                        
                        {{-- ACTIONS --}}
                        <td>
                            <div class="table-actions">

                                {{-- VIEW --}}
                                <a
                                    href="{{ route('quotations.show', ['quotation' => $quotation->getRouteKey()]) }}"
                                    class="action-btn view"
                                    title="View"
                                    aria-label="View quotation"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </a>

                                {{-- EDIT --}}
                                <a
                                    href="{{ route('quotations.edit', ['quotation' => $quotation->getRouteKey()]) }}"
                                    class="action-btn edit"
                                    title="Edit"
                                    aria-label="Edit quotation"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 20h9"/>
                                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                    </svg>
                                </a>

                                {{-- DELETE --}}
                                <form
                                    action="{{ route('quotations.destroy', ['quotation' => $quotation->getRouteKey()]) }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this quotation?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="action-btn delete"
                                        title="Delete"
                                        aria-label="Delete quotation"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3 6h18"/>
                                            <path d="M8 6V4h8v2"/>
                                            <path d="m19 6-1 14H6L5 6"/>
                                            <path d="M10 11v5M14 11v5"/>
                                        </svg>
                                    </button>
                                </form>

                            </div>
                        </td>

                         
                            
                            {{-- Sales Order --}}
                            <td>
                            @php
                                $existingSalesOrder = \Illuminate\Support\Facades\DB::table('sales_orders')
                                    ->where('quotation_id', $quotation->quotation_id)
                                    ->first();
                            @endphp

                            @if ($existingSalesOrder)
                                <span class="quotation-status-badge quotation-status-approved">
                                    {{ $existingSalesOrder->sales_order_number }}
                                </span>
                            @elseif (strtolower(trim($quotation->status ?? '')) === 'approved')
                                <form
                                    method="POST"
                                    action="{{ route('sales_orders.from_quotation', $quotation->quotation_id) }}"
                                    data-sales-order-create-form
                                    data-quotation-number="{{ $quotation->quotation_number }}"
                                >
                                    @csrf
                                    <button type="submit" class="btn primary">
                                        Buat Sales Order
                                    </button>
                                </form>
                            @elseif (strtolower(trim($quotation->status ?? '')) === 'rejected')
                                <span class="quotation-status-badge quotation-status-rejected">Rejected - No Sales Order</span>
                            @elseif (in_array(strtolower(trim($quotation->status ?? '')), ['cancelled', 'expired'], true))
                                <span class="muted">Not Eligible</span>
                            @else
                                <span class="muted">Awaiting Customer Approval</span>
                            @endif
                        </td>


                        

                    </tr>


                    @empty

                    <tr>

                        <td colspan="10">

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


        <div class="table-footer">
            <div>
                Showing {{ $quotations->firstItem() ?? 0 }}
                to {{ $quotations->lastItem() ?? 0 }}
                of {{ $quotations->total() }} Quotations
            </div>
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


<div class="quotation-sales-modal" id="quotationSalesModal" hidden role="dialog" aria-modal="true" aria-labelledby="quotationSalesModalTitle">
    <div class="quotation-sales-dialog">
        <div class="quotation-sales-icon">SO</div>
        <h3 id="quotationSalesModalTitle">Create Sales Order?</h3>
        <p>Convert <strong id="quotationSalesNumber"></strong> into a new Sales Order? This action will copy the quotation products and totals. Only approved quotations are eligible.</p>
        <div class="quotation-sales-buttons">
            <button type="button" class="btn" id="quotationSalesCancel">Cancel</button>
            <button type="button" class="btn primary" id="quotationSalesConfirm">Yes, Create Sales Order</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('quotationSalesModal');
    const cancel = document.getElementById('quotationSalesCancel');
    const confirm = document.getElementById('quotationSalesConfirm');
    const number = document.getElementById('quotationSalesNumber');
    if (!modal || !cancel || !confirm) return;
    let pending = null;
    function close() {
        if (confirm.disabled) return;
        modal.hidden = true;
        pending = null;
        document.body.style.overflow = '';
    }
    document.querySelectorAll('form[data-sales-order-create-form]').forEach(form => {
        form.addEventListener('submit', event => {
            event.preventDefault();
            pending = form;
            number.textContent = form.dataset.quotationNumber || 'this quotation';
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            confirm.focus();
        });
    });
    confirm.addEventListener('click', () => {
        if (!pending) return;
        confirm.disabled = true;
        confirm.textContent = 'Creating...';
        pending.submit();
    });
    cancel.addEventListener('click', close);
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
});
</script>

@push('styles')
    @include('quotations._style')
@endpush
