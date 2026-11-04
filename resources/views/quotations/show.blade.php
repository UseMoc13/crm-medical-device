@extends('layouts.app')

@section('title', 'Quotation Details')

@section('content')

@php

$subtotal = (float) ($quotation->subtotal ?? 0);

$discountValue =
(float) ($quotation->discount ?? 0);

$discountType =
$quotation->discount_type ?? 'amount';

$taxValue =
(float) ($quotation->tax ?? 0);

$taxType =
$quotation->tax_type ?? 'amount';


/*
|--------------------------------------------------------------------------
| Percentage Detection
|--------------------------------------------------------------------------
*/

$isDiscountPercentage = in_array(
$discountType,
['percentage', 'percent'],
true
);


$isTaxPercentage = in_array(
$taxType,
['percentage', 'percent'],
true
);


/*
|--------------------------------------------------------------------------
| Discount
|--------------------------------------------------------------------------
*/

if ($isDiscountPercentage) {

$discountAmount =
$subtotal * ($discountValue / 100);

} else {

$discountAmount =
$discountValue;

}


$discountAmount = min(
max(0, $discountAmount),
$subtotal
);


/*
|--------------------------------------------------------------------------
| After Discount
|--------------------------------------------------------------------------
*/

$afterDiscount =
max(
0,
$subtotal - $discountAmount
);


/*
|--------------------------------------------------------------------------
| Tax
|--------------------------------------------------------------------------
*/

if ($isTaxPercentage) {

$taxAmount =
$afterDiscount * ($taxValue / 100);

} else {

$taxAmount =
$taxValue;

}


$taxAmount =
max(0, $taxAmount);


/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

$calculatedTotal =
$afterDiscount + $taxAmount;


/*
|--------------------------------------------------------------------------
| Format Helpers
|--------------------------------------------------------------------------
*/

$formatNumber = function ($value) {

return number_format(
(float) $value,
0,
',',
'.'
);

};


$formatPercentage = function ($value) {

return rtrim(
rtrim(
number_format(
(float) $value,
2,
',',
'.'
),
'0'
),
','
);

};


/*
|--------------------------------------------------------------------------
| Opportunity
|--------------------------------------------------------------------------
*/

$opportunity =
$quotation->opportunity;


$customerName = '-';

if ($opportunity?->customer) {

$customerName =
$opportunity->customer->customer_name
?? $opportunity->customer->company_name
?? $opportunity->customer->name
?? '-';

}


$salesName =
$opportunity?->user?->name
?? '-';


$opportunityStage =
$opportunity?->stage
?? '-';


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$itemsTotal =
$quotationItems->total();

$itemsFrom =
$quotationItems->firstItem();

$itemsTo =
$quotationItems->lastItem();

@endphp


{{-- =========================================================
     PAGE HEADER
========================================================= --}}

<div class="page-head">
    <div>
        <div class="quotation-heading">
            <div>
                <h1>
                    {{ $quotation->quotation_number }}
                </h1>
                <p>
                    View quotation details and related opportunity information.
                </p>
            </div>
        </div>
    </div>


    <div class="actions">

        <a
            href="{{ route('quotations.edit', $quotation) }}"
            class="btn primary">
            ✎ Edit Quotation
        </a>

        <a
            href="{{ route('quotations.index') }}"
            class="btn">
            ← Back
        </a>

    </div>

</div>


@if(session('success'))

<div class="alert success">
    {{ session('success') }}
</div>

@endif

@if(session('error'))
<div class="alert error">{{ session('error') }}</div>
@endif

@php($quotationCurrentStatus = strtolower(trim((string) $quotation->status)))
@if(in_array($quotationCurrentStatus, ['draft', 'sent'], true))
<div class="card quotation-status-actions">
    <div class="card-head">
        <div>
            <h3>Customer Decision</h3>
            <p>Record what happened outside the CRM. This action does not send an email or provide customer self-approval.</p>
        </div>
    </div>
    <div class="card-body quotation-status-actions-body">
        @if($quotationCurrentStatus === 'draft')
            <form action="{{ route('quotations.change-status', $quotation) }}" method="POST" data-decision-form>
                @csrf
                <input type="hidden" name="action" value="mark_sent">
                <button type="submit" class="btn primary">Mark as Sent</button>
            </form>
        @elseif($quotationCurrentStatus === 'sent')
            <form action="{{ route('quotations.change-status', $quotation) }}" method="POST" data-decision-form>
                @csrf
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="btn primary">Record Customer Approval</button>
            </form>
            <form action="{{ route('quotations.change-status', $quotation) }}" method="POST" data-decision-form>
                @csrf
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="btn">Record Customer Rejection</button>
            </form>
        @endif
    </div>
</div>
@endif



<div id="quotationDecisionModal" class="quotation-decision-modal" hidden role="dialog" aria-modal="true" aria-labelledby="quotationDecisionTitle">
    <div class="quotation-decision-dialog">
        <h3 id="quotationDecisionTitle">Confirm Action</h3>
        <p id="quotationDecisionText"></p>
        <div class="quotation-decision-actions">
            <button type="button" class="btn" id="quotationDecisionCancel">Cancel</button>
            <button type="button" class="btn primary" id="quotationDecisionConfirm">Confirm</button>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('quotationDecisionModal');
    if (!modal) return;
    const title = document.getElementById('quotationDecisionTitle');
    const description = document.getElementById('quotationDecisionText');
    const confirmButton = document.getElementById('quotationDecisionConfirm');
    const cancelButton = document.getElementById('quotationDecisionCancel');
    let pendingForm = null;
    const messages = {
        mark_sent: ['Mark as Sent?', 'Confirm that this quotation has already been delivered to the customer. No email will be sent by this action.'],
        approve: ['Record Customer Approval?', 'Confirm that the customer explicitly accepted this quotation. You can create a Sales Order after approval.'],
        reject: ['Record Customer Rejection?', 'Confirm that the customer rejected this quotation. A rejected quotation cannot be converted into a Sales Order.']
    };
    const close = () => { modal.hidden = true; pendingForm = null; document.body.style.overflow = ''; };
    document.querySelectorAll('form[data-decision-form]').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            pendingForm = form;
            const action = form.querySelector('[name="action"]').value;
            const details = messages[action] || ['Confirm Action?', 'Proceed with this change?'];
            title.textContent = details[0];
            description.textContent = details[1];
            confirmButton.textContent = action === 'reject' ? 'Yes, Reject' : 'Yes, Confirm';
            confirmButton.classList.toggle('danger', action === 'reject');
            modal.hidden = false;
            document.body.style.overflow = 'hidden';
            confirmButton.focus();
        });
    });
    confirmButton.addEventListener('click', () => { if (pendingForm) { confirmButton.disabled = true; pendingForm.submit(); } });
    cancelButton.addEventListener('click', close);
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) close(); });
});
</script>

{{-- =========================================================
     INFORMATION
========================================================= --}}

<div class="information-layout">


    {{-- QUOTATION INFORMATION --}}

    <div class="card information-card quotation-information-card">

        <div class="card-head">

            <div>

                <h3>
                    Quotation Information
                </h3>

                <p>
                    Basic quotation information.
                </p>

            </div>


            @if($quotation->status)

            <span
                class="quotation-status-badge quotation-status-{{ Str::slug($quotation->status) }}">
                {{ $quotation->status }}
            </span>

            @endif

        </div>


        <div class="card-body">

            <div class="quotation-information-grid">

                <div class="information-field">

                    <span class="information-label">
                        Quotation Number
                    </span>

                    <strong class="information-value quotation-number">
                        {{ $quotation->quotation_number }}
                    </strong>

                </div>


                <div class="information-field">

                    <span class="information-label">
                        Quotation Date
                    </span>

                    <strong class="information-value">

                        {{
                            $quotation->quotation_date
                                ? $quotation->quotation_date->format('d M Y')
                                : '-'
                        }}

                    </strong>

                </div>


                <div class="information-field">

                    <span class="information-label">
                        Valid Until
                    </span>

                    <strong class="information-value">

                        {{
                            $quotation->valid_until
                                ? $quotation->valid_until->format('d M Y')
                                : '-'
                        }}

                    </strong>

                </div>


                <div class="information-field">

                    <span class="information-label">
                        Status
                    </span>

                    @if($quotation->status)

                    <span
                        class="quotation-status-badge quotation-status-{{ Str::slug($quotation->status) }}">
                        {{ $quotation->status }}
                    </span>

                    @else

                    <span class="information-empty">
                        -
                    </span>

                    @endif

                </div>

            </div>

        </div>

    </div>



    {{-- OPPORTUNITY --}}

    <div class="card information-card opportunity-information-card">

        <div class="card-head">

            <div>

                <h3>
                    Opportunity
                </h3>

                <p>
                    Related sales opportunity.
                </p>

            </div>

        </div>


        <div class="card-body">

            @if($opportunity)

            <div class="opportunity-content">

                <a
                    href="{{ route('opportunities.show', $opportunity) }}"
                    class="opportunity-link">

                    <div class="opportunity-link-content">

                        <span class="opportunity-link-label">
                            Opportunity
                        </span>

                        <strong class="opportunity-code">
                            {{ $opportunity->opportunity_code }}
                        </strong>

                    </div>

                    <span class="opportunity-arrow">
                        →
                    </span>

                </a>


                <div class="opportunity-name">

                    {{ $opportunity->name }}

                </div>


                <div class="opportunity-meta">

                    <div class="opportunity-meta-item">

                        <span>
                            Customer
                        </span>

                        <strong title="{{ $customerName }}">
                            {{ $customerName }}
                        </strong>

                    </div>


                    <div class="opportunity-meta-item">

                        <span>
                            Sales
                        </span>

                        <strong title="{{ $salesName }}">
                            {{ $salesName }}
                        </strong>

                    </div>


                    <div class="opportunity-meta-item">

                        <span>
                            Stage
                        </span>

                        <strong title="{{ $opportunityStage }}">
                            {{ $opportunityStage }}
                        </strong>

                    </div>

                </div>

            </div>

            @else

            <div class="empty-small">
                No opportunity assigned.
            </div>

            @endif

        </div>

    </div>

</div>



{{-- =========================================================
     MAIN DETAIL
========================================================= --}}

<div class="main-detail-grid">


    {{-- =====================================================
         QUOTATION ITEMS
    ====================================================== --}}

    <div class="card items-card">


        <div class="card-head">

            <div>

                <h3>
                    Quotation Items
                </h3>

                <p>
                    Products included in this quotation.
                </p>

            </div>


            <div class="items-header-actions">

                <span class="items-count">

                    {{ $itemsTotal }}

                    {{
                        $itemsTotal == 1
                            ? 'Item'
                            : 'Items'
                    }}

                </span>


                @if($itemsTotal > 0)

                <button
                    type="button"
                    class="btn-show-all"
                    onclick="openQuotationItemsModal()">
                    Show All
                </button>

                @endif

            </div>

        </div>


        <div class="card-body no-padding">

            @if($quotationItems->count())

            <div class="items-list">

                @foreach($quotationItems as $item)

                <div class="quotation-item">

                    <div class="item-product">

                        <div class="product-icon">
                            P
                        </div>

                        <div class="item-product-info">

                            @if($item->product)

                            <strong
                                title="{{ $item->product->product_name }}">
                                {{ $item->product->product_name }}
                            </strong>

                            <span>
                                {{ $item->product->product_code }}
                            </span>

                            @else

                            <strong>
                                Unknown Product
                            </strong>

                            <span>
                                Product unavailable
                            </span>

                            @endif

                        </div>

                    </div>


                    <div class="item-detail">

                        <span>
                            Quantity
                        </span>

                        <strong>
                            {{ $item->quantity }}
                        </strong>

                    </div>


                    <div class="item-detail">

                        <span>
                            Unit Price
                        </span>

                        <strong>
                            Rp {{ $formatNumber($item->unit_price) }}
                        </strong>

                    </div>


                    <div class="item-detail">

                        <span>
                            Discount
                        </span>

                        @if((float) ($item->discount ?? 0) > 0)

                        <strong class="item-discount">
                            − Rp {{ $formatNumber($item->discount) }}
                        </strong>

                        @else

                        <strong class="item-no-discount">
                            -
                        </strong>

                        @endif

                    </div>


                    <div class="item-subtotal">

                        <span>
                            Subtotal
                        </span>

                        <strong>
                            Rp {{ $formatNumber($item->subtotal) }}
                        </strong>

                    </div>

                </div>

                @endforeach

            </div>


            {{-- PAGINATION --}}

            <div class="items-footer">

                <div class="items-pagination-info">

                    Showing

                    <strong>
                        {{ $itemsFrom }}
                    </strong>

                    –

                    <strong>
                        {{ $itemsTo }}
                    </strong>

                    of

                    <strong>
                        {{ $itemsTotal }}
                    </strong>

                    items

                </div>


                @if($quotationItems->hasPages())

                <div class="quotation-pagination">

                    {{ $quotationItems->onEachSide(1)->links() }}

                </div>

                @endif

            </div>

            @else

            <div class="items-empty">

                <div class="empty-icon">
                    +
                </div>

                <strong>
                    No quotation items yet
                </strong>

                <span>
                    Products will appear here once quotation items are added.
                </span>

            </div>

            @endif

        </div>

    </div>



    {{-- =====================================================
         FINANCIAL SUMMARY
    ====================================================== --}}

    <div class="card financial-card">

        <div class="card-head">

            <div>

                <h3>
                    Financial Summary
                </h3>

                <p>
                    Final quotation amount.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="financial-list">


                <div class="financial-row">

                    <div>

                        <span>
                            Subtotal
                        </span>

                        <small>
                            Before adjustments
                        </small>

                    </div>

                    <strong>
                        Rp {{ $formatNumber($subtotal) }}
                    </strong>

                </div>


                <div class="financial-row discount-row">

                    <div>

                        <span>
                            Discount
                        </span>

                        @if($isDiscountPercentage)

                        <small>
                            {{ $formatPercentage($discountValue) }}% discount
                        </small>

                        @else

                        <small>
                            Fixed amount
                        </small>

                        @endif

                    </div>


                    <div class="financial-amount discount-amount">

                        @if($isDiscountPercentage)

                        <span class="type-badge discount-badge">
                            {{ $formatPercentage($discountValue) }}%
                        </span>

                        @endif

                        <strong>
                            − Rp {{ $formatNumber($discountAmount) }}
                        </strong>

                    </div>

                </div>


                <div class="financial-row after-discount-row">

                    <div>

                        <span>
                            After Discount
                        </span>

                    </div>

                    <strong>
                        Rp {{ $formatNumber($afterDiscount) }}
                    </strong>

                </div>


                <div class="financial-row tax-row">

                    <div>

                        <span>
                            Tax
                        </span>

                        @if($isTaxPercentage)

                        <small>
                            {{ $formatPercentage($taxValue) }}% tax
                        </small>

                        @else

                        <small>
                            Fixed amount
                        </small>

                        @endif

                    </div>


                    <div class="financial-amount tax-amount">

                        @if($isTaxPercentage)

                        <span class="type-badge tax-badge">
                            {{ $formatPercentage($taxValue) }}%
                        </span>

                        @endif

                        <strong>
                            + Rp {{ $formatNumber($taxAmount) }}
                        </strong>

                    </div>

                </div>

            </div>


            <div class="grand-total">

                <div>

                    <span>
                        Total Amount
                    </span>

                    <small>
                        Final quotation value
                    </small>

                </div>

                <strong>
                    Rp {{ $formatNumber($calculatedTotal) }}
                </strong>

            </div>


            <div class="financial-status">

                <span class="status-dot"></span>

                <span>
                    Total calculated from quotation values
                </span>

            </div>

        </div>

    </div>

</div>



{{-- =========================================================
     NOTES
========================================================= --}}

<div class="card notes-card">

    <div class="card-head">

        <div>

            <h3>
                Notes
            </h3>

            <p>
                Additional information for this quotation.
            </p>

        </div>

    </div>


    <div class="card-body">

        @if($quotation->notes)

        <div class="notes-content">

            {!! nl2br(e($quotation->notes)) !!}

        </div>

        @else

        <div class="notes-empty">
            No notes added to this quotation.
        </div>

        @endif

    </div>

</div>



{{-- =========================================================
     METADATA
========================================================= --}}

<div class="quotation-metadata">

    <span>

        Created:

        {{
            $quotation->created_at
                ? $quotation->created_at->format('d M Y H:i')
                : '-'
        }}

    </span>


    <span>

        Updated:

        {{
            $quotation->updated_at
                ? $quotation->updated_at->format('d M Y H:i')
                : '-'
        }}

    </span>

</div>



{{-- =========================================================
     SHOW ALL ITEMS MODAL
========================================================= --}}

<div
    id="quotationItemsModal"
    class="quotation-items-modal"
    aria-hidden="true">

    <div
        class="quotation-items-modal-overlay"
        onclick="closeQuotationItemsModal()"></div>


    <div
        class="quotation-items-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="quotationItemsModalTitle">


        {{-- Modal Header --}}

        <div class="quotation-items-modal-header">

            <div>

                <h3 id="quotationItemsModalTitle">
                    All Quotation Items
                </h3>

                <p>
                    {{ $quotation->quotation_number }}
                </p>

            </div>


            <button
                type="button"
                class="modal-close-button"
                onclick="closeQuotationItemsModal()"
                aria-label="Close">
                ×
            </button>

        </div>


        {{-- Modal Toolbar --}}

        <div class="quotation-items-toolbar">


            <div class="quotation-items-search">

                <span class="quotation-items-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    id="quotationItemsSearch"
                    placeholder="Search product or product code..."
                    autocomplete="off">

            </div>


            <select
                id="quotationItemsSort"
                class="quotation-items-sort">

                <option value="created_at|asc">
                    Newest Position
                </option>

                <option value="quantity|desc">
                    Quantity: High to Low
                </option>

                <option value="quantity|asc">
                    Quantity: Low to High
                </option>

                <option value="unit_price|desc">
                    Price: High to Low
                </option>

                <option value="unit_price|asc">
                    Price: Low to High
                </option>

                <option value="subtotal|desc">
                    Subtotal: High to Low
                </option>

                <option value="subtotal|asc">
                    Subtotal: Low to High
                </option>

            </select>

        </div>


        {{-- Modal Body --}}

        <div class="quotation-items-modal-body">

            <div
                id="quotationItemsLoading"
                class="quotation-items-loading">
                Loading quotation items...
            </div>


            <div
                id="quotationItemsEmpty"
                class="quotation-items-modal-empty"
                style="display:none;">
                No quotation items found.
            </div>


            <div
                id="quotationItemsTable"
                class="quotation-items-table-wrapper"
                style="display:none;">

                <table class="quotation-items-table">

                    <thead>

                        <tr>

                            <th>
                                Product
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Unit Price
                            </th>

                            <th>
                                Discount
                            </th>

                            <th>
                                Subtotal
                            </th>

                        </tr>

                    </thead>

                    <tbody
                        id="quotationItemsTableBody">
                    </tbody>

                </table>

            </div>

        </div>


        {{-- Modal Footer --}}

        <div class="quotation-items-modal-footer">

            <div
                id="quotationItemsModalInfo"
                class="quotation-items-modal-info">
            </div>


            <div
                id="quotationItemsModalPagination"
                class="quotation-items-modal-pagination">
            </div>

        </div>

    </div>

</div>







<script>
    const quotationItemsUrl =
        @json(route('quotations.items', $quotation));


    let quotationItemsCurrentPage = 1;

    let quotationItemsSearchTimer = null;


    /* =========================================================
       OPEN MODAL
    ========================================================= */

    function openQuotationItemsModal() {

        const modal =
            document.getElementById(
                'quotationItemsModal'
            );


        modal.classList.add('is-open');

        modal.setAttribute(
            'aria-hidden',
            'false'
        );


        document.body.style.overflow = 'hidden';


        quotationItemsCurrentPage = 1;


        loadQuotationItems(
            quotationItemsCurrentPage
        );

    }


    /* =========================================================
       CLOSE MODAL
    ========================================================= */

    function closeQuotationItemsModal() {

        const modal =
            document.getElementById(
                'quotationItemsModal'
            );


        modal.classList.remove('is-open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );


        document.body.style.overflow = '';

    }


    /* =========================================================
       ESC KEY
    ========================================================= */

    document.addEventListener(
        'keydown',
        function(event) {

            if (
                event.key === 'Escape'
            ) {

                closeQuotationItemsModal();

            }

        }
    );


    /* =========================================================
       LOAD ITEMS
    ========================================================= */

    async function loadQuotationItems(
        page = 1
    ) {

        const loading =
            document.getElementById(
                'quotationItemsLoading'
            );


        const empty =
            document.getElementById(
                'quotationItemsEmpty'
            );


        const table =
            document.getElementById(
                'quotationItemsTable'
            );


        const body =
            document.getElementById(
                'quotationItemsTableBody'
            );


        const info =
            document.getElementById(
                'quotationItemsModalInfo'
            );


        const pagination =
            document.getElementById(
                'quotationItemsModalPagination'
            );


        const search =
            document.getElementById(
                'quotationItemsSearch'
            ).value.trim();


        const sortValue =
            document.getElementById(
                'quotationItemsSort'
            ).value;


        const [
            sort,
            direction
        ] = sortValue.split('|');


        loading.style.display = 'flex';

        empty.style.display = 'none';

        table.style.display = 'none';

        body.innerHTML = '';

        pagination.innerHTML = '';

        info.innerHTML = '';


        try {

            const params =
                new URLSearchParams({

                    page: page,

                    search: search,

                    sort: sort,

                    direction: direction,

                });


            const response =
                await fetch(
                    `${quotationItemsUrl}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    'Failed to load quotation items.'
                );

            }


            const result =
                await response.json();


            quotationItemsCurrentPage =
                result.current_page;


            loading.style.display = 'none';


            if (!result.data.length) {

                empty.style.display = 'flex';

                info.innerHTML =
                    'No items found.';

                return;

            }


            table.style.display = 'block';


            result.data.forEach(
                function(item) {

                    const row =
                        document.createElement('tr');


                    const discount =
                        Number(
                            item.discount || 0
                        );


                    row.innerHTML = `

                    <td>

                        <div class="modal-product">

                            <div class="modal-product-icon">
                                P
                            </div>

                            <div class="modal-product-info">

                                <strong
                                    title="${escapeHtml(item.product_name)}"
                                >
                                    ${escapeHtml(item.product_name)}
                                </strong>

                                <span>
                                    ${escapeHtml(item.product_code)}
                                </span>

                            </div>

                        </div>

                    </td>


                    <td class="modal-number">
                        ${formatNumber(item.quantity)}
                    </td>


                    <td class="modal-number">
                        Rp ${formatNumber(item.unit_price)}
                    </td>


                    <td class="modal-number">

                        ${
                            discount > 0
                                ? `<span class="modal-discount">
                                    − Rp ${formatNumber(discount)}
                                   </span>`
                                : '-'
                        }

                    </td>


                    <td class="modal-subtotal">
                        Rp ${formatNumber(item.subtotal)}
                    </td>

                `;


                    body.appendChild(row);

                }
            );


            info.innerHTML = `

            Showing
            <strong>
                ${result.from}
            </strong>
            –
            <strong>
                ${result.to}
            </strong>
            of
            <strong>
                ${result.total}
            </strong>
            items

        `;


            renderQuotationItemsPagination(
                result.current_page,
                result.last_page
            );


        } catch (error) {

            loading.style.display = 'none';

            empty.style.display = 'flex';

            empty.textContent =
                'Failed to load quotation items.';

            console.error(error);

        }

    }


    /* =========================================================
       PAGINATION
    ========================================================= */

    function renderQuotationItemsPagination(
        currentPage,
        lastPage
    ) {

        const container =
            document.getElementById(
                'quotationItemsModalPagination'
            );


        container.innerHTML = '';


        if (lastPage <= 1) {

            return;

        }


        const previous =
            document.createElement('button');


        previous.type =
            'button';

        previous.className =
            'modal-page-button';

        previous.textContent =
            '‹';

        previous.disabled =
            currentPage <= 1;


        previous.onclick =
            function() {

                if (
                    currentPage > 1
                ) {

                    loadQuotationItems(
                        currentPage - 1
                    );

                }

            };


        container.appendChild(
            previous
        );


        let startPage =
            Math.max(
                1,
                currentPage - 2
            );


        let endPage =
            Math.min(
                lastPage,
                currentPage + 2
            );


        if (startPage > 1) {

            addQuotationPageButton(
                1,
                currentPage,
                container
            );


            if (startPage > 2) {

                const dots =
                    document.createElement(
                        'span'
                    );

                dots.textContent =
                    '...';

                dots.style.padding =
                    '0 4px';

                dots.style.color =
                    '#9aa3b1';

                container.appendChild(
                    dots
                );

            }

        }


        for (
            let page = startPage; page <= endPage; page++
        ) {

            addQuotationPageButton(
                page,
                currentPage,
                container
            );

        }


        if (endPage < lastPage) {

            if (
                endPage < lastPage - 1
            ) {

                const dots =
                    document.createElement(
                        'span'
                    );

                dots.textContent =
                    '...';

                dots.style.padding =
                    '0 4px';

                dots.style.color =
                    '#9aa3b1';

                container.appendChild(
                    dots
                );

            }


            addQuotationPageButton(
                lastPage,
                currentPage,
                container
            );

        }


        const next =
            document.createElement('button');


        next.type =
            'button';

        next.className =
            'modal-page-button';

        next.textContent =
            '›';

        next.disabled =
            currentPage >= lastPage;


        next.onclick =
            function() {

                if (
                    currentPage < lastPage
                ) {

                    loadQuotationItems(
                        currentPage + 1
                    );

                }

            };


        container.appendChild(
            next
        );

    }


    /* =========================================================
       PAGE BUTTON
    ========================================================= */

    function addQuotationPageButton(
        page,
        currentPage,
        container
    ) {

        const button =
            document.createElement(
                'button'
            );


        button.type =
            'button';

        button.className =
            'modal-page-button';


        if (
            page === currentPage
        ) {

            button.classList.add(
                'active'
            );

        }


        button.textContent =
            page;


        button.onclick =
            function() {

                loadQuotationItems(
                    page
                );

            };


        container.appendChild(
            button
        );

    }


    /* =========================================================
       SEARCH
    ========================================================= */

    document
        .getElementById(
            'quotationItemsSearch'
        )
        .addEventListener(
            'input',
            function() {

                clearTimeout(
                    quotationItemsSearchTimer
                );


                quotationItemsSearchTimer =
                    setTimeout(
                        function() {

                            if (
                                document
                                .getElementById(
                                    'quotationItemsModal'
                                )
                                .classList
                                .contains(
                                    'is-open'
                                )
                            ) {

                                quotationItemsCurrentPage =
                                    1;

                                loadQuotationItems(
                                    1
                                );

                            }

                        },
                        350
                    );

            }
        );


    /* =========================================================
       SORT
    ========================================================= */

    document
        .getElementById(
            'quotationItemsSort'
        )
        .addEventListener(
            'change',
            function() {

                if (
                    document
                    .getElementById(
                        'quotationItemsModal'
                    )
                    .classList
                    .contains(
                        'is-open'
                    )
                ) {

                    quotationItemsCurrentPage =
                        1;

                    loadQuotationItems(
                        1
                    );

                }

            }
        );


    /* =========================================================
       NUMBER FORMAT
    ========================================================= */

    function formatNumber(
        value
    ) {

        return new Intl.NumberFormat(
            'id-ID', {
                maximumFractionDigits: 0
            }
        ).format(
            Number(value || 0)
        );

    }


    /* =========================================================
       HTML ESCAPE
    ========================================================= */

    function escapeHtml(
        value
    ) {

        return String(value ?? '')
            .replace(
                /&/g,
                '&amp;'
            )
            .replace(
                /</g,
                '&lt;'
            )
            .replace(
                />/g,
                '&gt;'
            )
            .replace(
                /"/g,
                '&quot;'
            )
            .replace(
                /'/g,
                '&#039;'
            );

    }
</script>

@endsection

@push('styles')
    @include('quotations._style')
@endpush
