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



<style>
    /* =========================================================
   INFORMATION
========================================================= */

    .information-layout {

        display: grid;

        grid-template-columns:
            minmax(0, 1.2fr) minmax(0, 1fr);

        gap: 18px;

        margin-bottom: 18px;

        align-items: stretch;

    }


    .information-card {

        display: flex;

        flex-direction: column;

        min-width: 0;

        overflow: hidden;

    }


    .information-card .card-head {

        min-height: 68px;

        box-sizing: border-box;

    }


    .information-card .card-body {

        flex: 1;

        min-width: 0;

    }


    /* =========================================================
   QUOTATION INFORMATION
========================================================= */

    .quotation-information-grid {

        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        column-gap: 34px;

        row-gap: 22px;

    }


    .information-field {

        display: flex;

        flex-direction: column;

        align-items: flex-start;

        justify-content: center;

        min-width: 0;

        min-height: 46px;

    }


    .information-label {

        margin-bottom: 6px;

        color: #8a94a6;

        font-size: 9px;

        font-weight: 500;

        text-transform: uppercase;

        letter-spacing: .03em;

    }


    .information-value {

        display: block;

        max-width: 100%;

        overflow: hidden;

        color: #34415c;

        font-size: 12px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .quotation-number {

        color: #15966f !important;

        font-size: 13px !important;

        font-weight: 700 !important;

    }


    /* =========================================================
   STATUS
========================================================= */

    .quotation-status-badge {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        width: fit-content;

        min-height: 23px;

        padding: 4px 9px;

        box-sizing: border-box;

        border-radius: 6px;

        background: #eef2f6;

        color: #4d5b70;

        font-size: 10px;

        font-weight: 600;

        line-height: 1;

        white-space: nowrap;

    }


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


    /* =========================================================
   OPPORTUNITY
========================================================= */

    .opportunity-content {

        width: 100%;

        min-width: 0;

    }


    .opportunity-link {

        display: flex;

        align-items: center;

        justify-content: space-between;

        width: 100%;

        min-width: 0;

        gap: 12px;

        padding: 10px 12px;

        box-sizing: border-box;

        border: 1px solid #e7edf3;

        border-radius: 8px;

        background: #fafbfd;

        text-decoration: none;

    }


    .opportunity-link-content {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .opportunity-link-label {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .opportunity-code {

        overflow: hidden;

        color: #17284f;

        font-size: 12px;

        font-weight: 700;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .opportunity-arrow {

        display: flex;

        align-items: center;

        justify-content: center;

        flex: 0 0 auto;

        width: 25px;

        height: 25px;

        border-radius: 6px;

        background: #e9f7f3;

        color: #15966f;

    }


    .opportunity-name {

        margin-top: 11px;

        overflow: hidden;

        color: #34415c;

        font-size: 12px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .opportunity-meta {

        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        margin-top: 17px;

        padding-top: 15px;

        border-top: 1px solid #edf0f5;

    }


    .opportunity-meta-item {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 5px;

        padding: 0 14px;

    }


    .opportunity-meta-item:first-child {

        padding-left: 0;

    }


    .opportunity-meta-item:last-child {

        padding-right: 0;

    }


    .opportunity-meta-item:not(:last-child) {

        border-right: 1px solid #edf0f5;

    }


    .opportunity-meta-item span {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .opportunity-meta-item strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    /* =========================================================
   MAIN GRID
========================================================= */

    .main-detail-grid {

        display: grid;

        grid-template-columns:
            minmax(0, 1.65fr) minmax(300px, .85fr);

        gap: 18px;

        align-items: stretch;

        margin-bottom: 18px;

    }


    .items-card,
    .financial-card {

        min-width: 0;

    }


    /*
|--------------------------------------------------------------------------
| Important
|--------------------------------------------------------------------------
|
| Kedua card dibuat stretch.
| Jadi ketika Quotation Items hanya memiliki 1 item,
| card tetap mengikuti tinggi Financial Summary.
|
*/

    .items-card {

        display: flex;

        flex-direction: column;

    }


    .items-card .card-body {

        flex: 1;

        display: flex;

        flex-direction: column;

    }


    .financial-card {

        position: static;

    }


    /* =========================================================
   ITEMS HEADER
========================================================= */

    .items-header-actions {

        display: flex;

        align-items: center;

        gap: 8px;

        flex: 0 0 auto;

    }


    .items-count {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 52px;

        padding: 5px 9px;

        border-radius: 6px;

        background: #f1f5f9;

        color: #64748b;

        font-size: 9px;

        font-weight: 600;

        white-space: nowrap;

    }


    .btn-show-all {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-height: 28px;

        padding: 5px 10px;

        border: 1px solid #d8e8e2;

        border-radius: 6px;

        background: #f5fbf8;

        color: #15966f;

        font-family: inherit;

        font-size: 9px;

        font-weight: 600;

        cursor: pointer;

        transition:
            background .15s ease,
            border-color .15s ease;

    }


    .btn-show-all:hover {

        border-color: #b9d9cd;

        background: #e9f7f3;

    }


    /* =========================================================
   ITEM LIST
========================================================= */

    .items-list {

        width: 100%;

    }


    .quotation-item {

        display: grid;

        grid-template-columns:
            minmax(190px, 1.5fr) minmax(65px, .55fr) minmax(125px, 1fr) minmax(105px, .8fr) minmax(130px, 1fr);

        gap: 16px;

        align-items: center;

        padding: 17px 20px;

        border-bottom: 1px solid #edf0f5;

        box-sizing: border-box;

    }


    .quotation-item:last-child {

        border-bottom: 0;

    }


    .item-product {

        display: flex;

        align-items: center;

        min-width: 0;

        gap: 11px;

    }


    .product-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        flex: 0 0 auto;

        width: 34px;

        height: 34px;

        border-radius: 8px;

        background: #edf3ff;

        color: #315caa;

        font-size: 11px;

        font-weight: 700;

    }


    .item-product-info {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .item-product-info strong {

        overflow: hidden;

        color: #34415c;

        font-size: 11px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-product-info span {

        overflow: hidden;

        color: #8a94a6;

        font-size: 9px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-detail,
    .item-subtotal {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 4px;

    }


    .item-detail span,
    .item-subtotal span {

        color: #8a94a6;

        font-size: 8px;

        text-transform: uppercase;

    }


    .item-detail strong,
    .item-subtotal strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .item-subtotal {

        text-align: right;

    }


    .item-subtotal strong {

        color: #17284f;

        font-size: 11px;

    }


    .item-discount {

        color: #b45353 !important;

    }


    .item-no-discount {

        color: #a0a8b5 !important;

    }


    /* =========================================================
   ITEMS FOOTER
========================================================= */

    .items-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        margin-top: auto;

        padding: 13px 20px;

        border-top: 1px solid #edf0f5;

        background: #fafbfd;

    }


    .items-pagination-info {

        color: #8a94a6;

        font-size: 9px;

    }


    .items-pagination-info strong {

        color: #5d6879;

    }


    /* =========================================================
   PAGINATION
========================================================= */

    .quotation-pagination {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav>div:first-child {

        display: none;

    }


    .quotation-pagination nav>div:last-child {

        display: flex;

        align-items: center;

    }


    .quotation-pagination nav a,
    .quotation-pagination nav span {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        min-width: 27px;

        height: 27px;

        margin-left: 4px;

        padding: 0 7px;

        border: 1px solid #e4e9ef;

        border-radius: 6px;

        background: #fff;

        color: #64748b;

        font-size: 9px;

        text-decoration: none;

    }


    .quotation-pagination nav a:hover {

        border-color: #cfe4dc;

        background: #f5fbf8;

        color: #15966f;

    }


    .quotation-pagination nav span[aria-current="page"] {

        border-color: #15966f;

        background: #15966f;

        color: #fff;

    }


    /* =========================================================
   EMPTY
========================================================= */

    .items-empty {

        display: flex;

        flex: 1;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        min-height: 180px;

        padding: 30px;

        text-align: center;

    }


    .empty-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 36px;

        height: 36px;

        margin-bottom: 10px;

        border-radius: 8px;

        background: #f1f5f9;

        color: #94a3b8;

        font-size: 18px;

    }


    .items-empty strong {

        color: #34415c;

        font-size: 12px;

    }


    .items-empty span {

        margin-top: 5px;

        color: #8a94a6;

        font-size: 10px;

    }


    /* =========================================================
   FINANCIAL
========================================================= */

    .financial-list {

        width: 100%;

    }


    .financial-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        padding: 12px 0;

        border-bottom: 1px solid #edf0f5;

    }


    .financial-row>div:first-child {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .financial-row span {

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

    }


    .financial-row small {

        color: #8a94a6;

        font-size: 8px;

    }


    .financial-row>strong {

        flex: 0 0 auto;

        color: #34415c;

        font-size: 10px;

        white-space: nowrap;

    }


    .financial-amount {

        display: flex;

        align-items: center;

        justify-content: flex-end;

        flex-wrap: wrap;

        gap: 7px;

    }


    .discount-amount strong {

        color: #b45353;

    }


    .tax-amount strong {

        color: #16805f;

    }


    .type-badge {

        display: inline-flex;

        padding: 3px 6px;

        border-radius: 5px;

        font-size: 8px;

        font-weight: 700;

    }


    .discount-badge {

        background: #fff0f0;

        color: #b45353;

    }


    .tax-badge {

        background: #e8f5f0;

        color: #16805f;

    }


    .after-discount-row {

        background: #fafbfd;

    }


    .grand-total {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        margin-top: 17px;

        padding: 15px;

        border-radius: 9px;

        background: #f4f8f7;

    }


    .grand-total>div {

        display: flex;

        flex-direction: column;

        gap: 4px;

    }


    .grand-total span {

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

    }


    .grand-total small {

        color: #8a94a6;

        font-size: 8px;

    }


    .grand-total strong {

        color: #15966f;

        font-size: 16px;

        white-space: nowrap;

    }


    .financial-status {

        display: flex;

        align-items: center;

        gap: 7px;

        margin-top: 13px;

        color: #8a94a6;

        font-size: 8px;

    }


    .status-dot {

        width: 6px;

        height: 6px;

        flex: 0 0 auto;

        border-radius: 50%;

        background: #1db887;

    }


    /* =========================================================
   NOTES
========================================================= */

    .notes-card {

        margin-bottom: 12px;

    }


    .notes-content {

        padding: 12px 14px;

        border: 1px solid #edf0f5;

        border-radius: 8px;

        background: #fafbfd;

        color: #4a5568;

        font-size: 11px;

        line-height: 1.7;

        word-break: break-word;

    }


    .notes-empty {

        color: #8a94a6;

        font-size: 10px;

    }


    /* =========================================================
   METADATA
========================================================= */

    .quotation-metadata {

        display: flex;

        align-items: center;

        justify-content: space-between;

        flex-wrap: wrap;

        gap: 8px 20px;

        padding: 2px 2px 20px;

        color: #9aa3b1;

        font-size: 8px;

    }


    /* =========================================================
   MODAL
========================================================= */

    .quotation-items-modal {

        position: fixed;

        inset: 0;

        z-index: 9999;

        display: none;

        align-items: center;

        justify-content: center;

        padding: 25px;

        box-sizing: border-box;

    }


    .quotation-items-modal.is-open {

        display: flex;

    }


    .quotation-items-modal-overlay {

        position: absolute;

        inset: 0;

        background: rgba(23, 40, 79, .42);

        backdrop-filter: blur(2px);

    }


    .quotation-items-dialog {

        position: relative;

        z-index: 1;

        display: flex;

        flex-direction: column;

        width: min(1100px, 100%);

        max-height: min(760px, 90vh);

        overflow: hidden;

        border: 1px solid #e5eaf0;

        border-radius: 12px;

        background: #fff;

        box-shadow: 0 20px 60px rgba(23, 40, 79, .18);

    }


    .quotation-items-modal-header {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        padding: 18px 22px;

        border-bottom: 1px solid #edf0f5;

    }


    .quotation-items-modal-header h3 {

        margin: 0;

        color: #17284f;

        font-size: 15px;

        font-weight: 700;

    }


    .quotation-items-modal-header p {

        margin: 4px 0 0;

        color: #8a94a6;

        font-size: 9px;

    }


    .modal-close-button {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;

        height: 30px;

        flex: 0 0 auto;

        border: 0;

        border-radius: 7px;

        background: #f4f6f8;

        color: #64748b;

        font-size: 20px;

        line-height: 1;

        cursor: pointer;

    }


    .modal-close-button:hover {

        background: #edf0f5;

        color: #17284f;

    }


    /* =========================================================
   MODAL TOOLBAR
========================================================= */

    .quotation-items-toolbar {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 13px 22px;

        border-bottom: 1px solid #edf0f5;

        background: #fafbfd;

    }


    .quotation-items-search {

        position: relative;

        flex: 1;

        min-width: 0;

    }


    .quotation-items-search-icon {

        position: absolute;

        top: 50%;

        left: 11px;

        transform: translateY(-50%);

        color: #7d8898;

        font-size: 18px;

        line-height: 1;

        pointer-events: none;

    }


    .quotation-items-search input {

        width: 100%;

        height: 34px;

        padding: 0 12px 0 34px;

        border: 1px solid #dfe5ec;

        border-radius: 7px;

        outline: none;

        background: #fff;

        color: #34415c;

        font-family: inherit;

        font-size: 10px;

        box-sizing: border-box;

    }


    .quotation-items-search input:focus {

        border-color: #9fcdbf;

        box-shadow: 0 0 0 3px rgba(21, 150, 111, .08);

    }


    .quotation-items-sort {

        width: 180px;

        height: 34px;

        padding: 0 10px;

        border: 1px solid #dfe5ec;

        border-radius: 7px;

        outline: none;

        background: #fff;

        color: #34415c;

        font-family: inherit;

        font-size: 10px;

    }


    /* =========================================================
   MODAL BODY
========================================================= */

    .quotation-items-modal-body {

        position: relative;

        min-height: 300px;

        overflow: auto;

    }


    .quotation-items-table-wrapper {

        width: 100%;

        overflow-x: auto;

    }


    .quotation-items-table {

        width: 100%;

        min-width: 760px;

        border-collapse: collapse;

    }


    .quotation-items-table th {

        position: sticky;

        top: 0;

        z-index: 2;

        padding: 11px 16px;

        border-bottom: 1px solid #e7ebf0;

        background: #f8fafc;

        color: #7d8898;

        font-size: 8px;

        font-weight: 600;

        text-align: left;

        text-transform: uppercase;

        letter-spacing: .03em;

    }


    .quotation-items-table td {

        padding: 12px 16px;

        border-bottom: 1px solid #edf0f5;

        color: #34415c;

        font-size: 10px;

        vertical-align: middle;

    }


    .quotation-items-table tbody tr:hover {

        background: #fafcfb;

    }


    .modal-product {

        display: flex;

        align-items: center;

        min-width: 0;

        gap: 10px;

    }


    .modal-product-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 30px;

        height: 30px;

        flex: 0 0 auto;

        border-radius: 7px;

        background: #edf3ff;

        color: #315caa;

        font-size: 9px;

        font-weight: 700;

    }


    .modal-product-info {

        display: flex;

        flex-direction: column;

        min-width: 0;

        gap: 3px;

    }


    .modal-product-info strong {

        overflow: hidden;

        color: #34415c;

        font-size: 10px;

        font-weight: 600;

        text-overflow: ellipsis;

        white-space: nowrap;

    }


    .modal-product-info span {

        color: #8a94a6;

        font-size: 8px;

    }


    .modal-number {

        white-space: nowrap;

    }


    .modal-discount {

        color: #b45353 !important;

    }


    .modal-subtotal {

        color: #17284f !important;

        font-weight: 700;

        white-space: nowrap;

    }


    /* =========================================================
   MODAL LOADING / EMPTY
========================================================= */

    .quotation-items-loading,
    .quotation-items-modal-empty {

        display: flex;

        align-items: center;

        justify-content: center;

        min-height: 300px;

        color: #8a94a6;

        font-size: 10px;

    }


    .quotation-items-loading {

        display: none;

    }


    /* =========================================================
   MODAL FOOTER
========================================================= */

    .quotation-items-modal-footer {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 15px;

        min-height: 54px;

        padding: 10px 22px;

        border-top: 1px solid #edf0f5;

        background: #fafbfd;

        box-sizing: border-box;

    }


    .quotation-items-modal-info {

        color: #8a94a6;

        font-size: 9px;

    }


    .quotation-items-modal-info strong {

        color: #5d6879;

    }


    .quotation-items-modal-pagination {

        display: flex;

        align-items: center;

        gap: 4px;

    }


    .modal-page-button {

        display: flex;

        align-items: center;

        justify-content: center;

        min-width: 27px;

        height: 27px;

        padding: 0 7px;

        border: 1px solid #e4e9ef;

        border-radius: 6px;

        background: #fff;

        color: #64748b;

        font-family: inherit;

        font-size: 9px;

        cursor: pointer;

    }


    .modal-page-button:hover {

        border-color: #cfe4dc;

        background: #f5fbf8;

        color: #15966f;

    }


    .modal-page-button.active {

        border-color: #15966f;

        background: #15966f;

        color: #fff;

    }


    .modal-page-button:disabled {

        opacity: .45;

        cursor: default;

    }


    /* =========================================================
   RESPONSIVE
========================================================= */

    @media (max-width: 1150px) {

        .main-detail-grid {

            grid-template-columns:
                minmax(0, 1.45fr) minmax(280px, .85fr);

        }


        .quotation-item {

            grid-template-columns:
                minmax(170px, 1.4fr) minmax(60px, .5fr) minmax(110px, .9fr) minmax(95px, .75fr) minmax(115px, .9fr);

            gap: 12px;

        }

    }


    @media (max-width: 950px) {

        .information-layout {

            grid-template-columns: 1fr;

        }


        .main-detail-grid {

            grid-template-columns: 1fr;

        }

    }


    @media (max-width: 750px) {

        .quotation-information-grid {

            grid-template-columns: 1fr 1fr;

            column-gap: 24px;

        }


        .quotation-item {

            grid-template-columns:
                minmax(0, 1.5fr) minmax(70px, .6fr) minmax(100px, .9fr);

        }


        .item-subtotal {

            grid-column: 3;

            text-align: left;

        }


        .items-footer {

            align-items: flex-start;

            flex-direction: column;

        }


        .quotation-pagination {

            width: 100%;

        }


        .quotation-items-toolbar {

            align-items: stretch;

            flex-direction: column;

        }


        .quotation-items-sort {

            width: 100%;

        }


        .quotation-items-modal {

            padding: 10px;

        }


        .quotation-items-dialog {

            max-height: 95vh;

        }

    }


    @media (max-width: 600px) {

        .quotation-information-grid {

            grid-template-columns: 1fr;

        }


        .opportunity-meta {

            grid-template-columns: 1fr;

            gap: 12px;

        }


        .opportunity-meta-item {

            padding: 0 !important;

        }


        .opportunity-meta-item:not(:last-child) {

            padding-bottom: 12px;

            border-right: 0;

            border-bottom: 1px solid #edf0f5;

        }


        .quotation-item {

            grid-template-columns: 1fr 1fr;

            gap: 15px;

            padding: 16px;

        }


        .item-product {

            grid-column: 1 / -1;

        }


        .item-subtotal {

            grid-column: auto;

        }


        .items-header-actions {

            flex-wrap: wrap;

        }


        .quotation-items-modal-footer {

            align-items: flex-start;

            flex-direction: column;

        }

    }


    @media (max-width: 480px) {

        .quotation-items-modal-header {

            padding: 15px;

        }


        .quotation-items-toolbar {

            padding: 11px 15px;

        }


        .quotation-items-modal-footer {

            padding: 10px 15px;

        }

    }
</style>



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