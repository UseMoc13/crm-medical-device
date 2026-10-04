@extends('layouts.app')

@section('title', 'Quotation Details')

@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | Financial Calculation
    |--------------------------------------------------------------------------
    */

    $subtotal = (float) ($quotation->subtotal ?? 0);

    $discountValue = (float) ($quotation->discount ?? 0);
    $discountType = $quotation->discount_type ?? 'amount';

    $taxValue = (float) ($quotation->tax ?? 0);
    $taxType = $quotation->tax_type ?? 'amount';


    /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */

    if ($discountType === 'percent') {

        $discountAmount =
            $subtotal * ($discountValue / 100);

    } else {

        $discountAmount =
            $discountValue;

    }


    /*
    |--------------------------------------------------------------------------
    | Amount After Discount
    |--------------------------------------------------------------------------
    */

    $afterDiscount =
        max(0, $subtotal - $discountAmount);


    /*
    |--------------------------------------------------------------------------
    | Tax
    |--------------------------------------------------------------------------
    */

    if ($taxType === 'percent') {

        $taxAmount =
            $afterDiscount * ($taxValue / 100);

    } else {

        $taxAmount =
            $taxValue;

    }


    /*
    |--------------------------------------------------------------------------
    | Total
    |--------------------------------------------------------------------------
    */

    $calculatedTotal =
        $afterDiscount + $taxAmount;


    /*
    |--------------------------------------------------------------------------
    | Helper Formatting
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

@endphp


{{-- =====================================================
     PAGE HEADER
====================================================== --}}

<div class="page-head">

    <div>

        <div class="quotation-heading">

            <span class="quotation-document-icon">
                Q
            </span>

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
            class="btn primary"
        >
            ✎ Edit Quotation
        </a>


        <a
            href="{{ route('quotations.index') }}"
            class="btn"
        >
            ← Back
        </a>

    </div>

</div>


{{-- =====================================================
     ALERT
====================================================== --}}

@if(session('success'))

    <div class="alert success">

        {{ session('success') }}

    </div>

@endif


{{-- =====================================================
     QUOTATION + OPPORTUNITY
====================================================== --}}

<div class="top-detail-grid">


    {{-- =================================================
         QUOTATION INFORMATION
    ================================================== --}}

    <div class="card">

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
                    class="quotation-status-badge
                    quotation-status-{{ Str::slug($quotation->status) }}"
                >
                    {{ $quotation->status }}
                </span>

            @endif

        </div>


        <div class="card-body">

            <div class="detail-grid">


                <div class="detail-item">

                    <span>
                        Quotation Number
                    </span>

                    <strong class="quotation-number">

                        {{ $quotation->quotation_number }}

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Quotation Date
                    </span>

                    <strong>

                        {{ $quotation->quotation_date
                            ? $quotation->quotation_date->format('d M Y')
                            : '-'
                        }}

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Valid Until
                    </span>

                    <strong>

                        {{ $quotation->valid_until
                            ? $quotation->valid_until->format('d M Y')
                            : '-'
                        }}

                    </strong>

                </div>


                <div class="detail-item">

                    <span>
                        Status
                    </span>

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

                </div>

            </div>

        </div>

    </div>


    {{-- =================================================
         OPPORTUNITY
    ================================================== --}}

    <div class="card">

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

            @if($quotation->opportunity)

                <a
                    href="{{ route(
                        'opportunities.show',
                        $quotation->opportunity
                    ) }}"
                    class="opportunity-link"
                >

                    <span class="opportunity-code">

                        {{ $quotation->opportunity->opportunity_code }}

                    </span>

                    <span class="opportunity-arrow">
                        →
                    </span>

                </a>


                <div class="opportunity-name">

                    {{ $quotation->opportunity->name }}

                </div>


                <div class="opportunity-meta">


                    @if($quotation->opportunity->customer)

                        <div class="opportunity-meta-item">

                            <span>
                                Customer
                            </span>

                            <strong>
                                {{ $quotation->opportunity->customer->customer_name }}
                            </strong>

                        </div>

                    @endif


                    @if($quotation->opportunity->user)

                        <div class="opportunity-meta-item">

                            <span>
                                Sales
                            </span>

                            <strong>
                                {{ $quotation->opportunity->user->name }}
                            </strong>

                        </div>

                    @endif


                    @if($quotation->opportunity->stage)

                        <div class="opportunity-meta-item">

                            <span>
                                Stage
                            </span>

                            <strong>
                                {{ $quotation->opportunity->stage }}
                            </strong>

                        </div>

                    @endif

                </div>

            @else

                <div class="empty-small">

                    No opportunity assigned.

                </div>

            @endif

        </div>

    </div>


</div>


{{-- =====================================================
     ITEMS + FINANCIAL
====================================================== --}}

<div class="main-detail-grid">


    {{-- =================================================
         QUOTATION ITEMS
    ================================================== --}}

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


            <span class="items-count">

                {{ $quotation->items->count() }}
                {{ $quotation->items->count() == 1 ? 'Item' : 'Items' }}

            </span>

        </div>


        <div class="card-body no-padding">


            @if($quotation->items->count())

                <div class="items-list">

                    @foreach($quotation->items as $item)

                        <div class="quotation-item">


                            {{-- Product --}}

                            <div class="item-product">

                                <div class="product-icon">
                                    P
                                </div>

                                <div>

                                    @if($item->product)

                                        <strong>
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


                            {{-- Quantity --}}

                            <div class="item-detail">

                                <span>
                                    Quantity
                                </span>

                                <strong>
                                    {{ $item->quantity }}
                                </strong>

                            </div>


                            {{-- Unit Price --}}

                            <div class="item-detail">

                                <span>
                                    Unit Price
                                </span>

                                <strong>

                                    Rp
                                    {{ $formatNumber(
                                        $item->unit_price
                                    ) }}

                                </strong>

                            </div>


                            {{-- Discount --}}

                            <div class="item-detail">

                                <span>
                                    Discount
                                </span>


                                @if((float) ($item->discount ?? 0) > 0)

                                    <strong class="item-discount">

                                        − Rp
                                        {{ $formatNumber(
                                            $item->discount
                                        ) }}

                                    </strong>

                                @else

                                    <strong class="item-no-discount">
                                        -
                                    </strong>

                                @endif

                            </div>


                            {{-- Subtotal --}}

                            <div class="item-subtotal">

                                <span>
                                    Subtotal
                                </span>

                                <strong>

                                    Rp
                                    {{ $formatNumber(
                                        $item->subtotal
                                    ) }}

                                </strong>

                            </div>


                        </div>

                    @endforeach

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


    {{-- =================================================
         FINANCIAL SUMMARY
    ================================================== --}}

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


                {{-- Subtotal --}}

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

                        Rp
                        {{ $formatNumber($subtotal) }}

                    </strong>

                </div>


                {{-- Discount --}}

                <div class="financial-row discount-row">

                    <div>

                        <span>
                            Discount
                        </span>


                        @if($discountType === 'percent')

                            <small>
                                {{ $formatPercentage($discountValue) }}%
                                discount
                            </small>

                        @else

                            <small>
                                Fixed amount
                            </small>

                        @endif

                    </div>


                    <div class="financial-amount discount-amount">


                        @if($discountType === 'percent')

                            <span class="type-badge discount-badge">

                                {{ $formatPercentage($discountValue) }}%

                            </span>

                        @endif


                        <strong>

                            − Rp
                            {{ $formatNumber(
                                $discountAmount
                            ) }}

                        </strong>

                    </div>

                </div>


                {{-- After Discount --}}

                <div class="financial-row after-discount-row">

                    <div>

                        <span>
                            After Discount
                        </span>

                    </div>


                    <strong>

                        Rp
                        {{ $formatNumber(
                            $afterDiscount
                        ) }}

                    </strong>

                </div>


                {{-- Tax --}}

                <div class="financial-row tax-row">

                    <div>

                        <span>
                            Tax
                        </span>


                        @if($taxType === 'percent')

                            <small>
                                {{ $formatPercentage($taxValue) }}%
                                tax
                            </small>

                        @else

                            <small>
                                Fixed amount
                            </small>

                        @endif

                    </div>


                    <div class="financial-amount tax-amount">


                        @if($taxType === 'percent')

                            <span class="type-badge tax-badge">

                                {{ $formatPercentage($taxValue) }}%

                            </span>

                        @endif


                        <strong>

                            + Rp
                            {{ $formatNumber(
                                $taxAmount
                            ) }}

                        </strong>

                    </div>

                </div>


            </div>


            {{-- Total --}}

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

                    Rp
                    {{ $formatNumber(
                        $calculatedTotal
                    ) }}

                </strong>

            </div>


            {{-- Status Indicator --}}

            <div class="financial-status">

                <span class="status-dot"></span>

                <span>
                    Total calculated from quotation values
                </span>

            </div>

        </div>

    </div>


</div>


{{-- =====================================================
     NOTES
====================================================== --}}

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


{{-- =====================================================
     METADATA
====================================================== --}}

<div class="quotation-metadata">

    <span>

        Created:

        {{ $quotation->created_at
            ? $quotation->created_at->format('d M Y H:i')
            : '-'
        }}

    </span>


    <span>

        Updated:

        {{ $quotation->updated_at
            ? $quotation->updated_at->format('d M Y H:i')
            : '-'
        }}

    </span>

</div>


<style>

/* =====================================================
   TOP DETAIL
===================================================== */

.top-detail-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1.35fr)
        minmax(320px, 1fr);

    gap: 18px;

    margin-bottom: 18px;

}


/* =====================================================
   MAIN DETAIL
===================================================== */

.main-detail-grid {

    display: grid;

    grid-template-columns:
        minmax(0, 1.65fr)
        minmax(330px, 0.85fr);

    gap: 18px;

    align-items: start;

    margin-bottom: 18px;

}


/* =====================================================
   PAGE HEADING
===================================================== */

.quotation-heading {

    display: flex;

    align-items: center;

    gap: 12px;

}


.quotation-document-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 38px;

    height: 38px;

    border-radius: 9px;

    background: #e9f7f3;

    color: #15966f;

    font-size: 15px;

    font-weight: 700;

}


/* =====================================================
   DETAIL GRID
===================================================== */

.detail-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 22px;

}


.detail-item {

    display: flex;

    flex-direction: column;

    gap: 7px;

}


.detail-item > span:first-child {

    color: #7d8797;

    font-size: 11px;

}


.detail-item strong {

    color: #17284f;

    font-size: 13px;

}


.quotation-number {

    font-size: 14px !important;

}


/* =====================================================
   STATUS
===================================================== */

.quotation-status-badge {

    display: inline-flex;

    align-items: center;

    width: fit-content;

    padding: 4px 9px;

    border-radius: 6px;

    background: #eef2f6;

    color: #4d5b70;

    font-size: 10px !important;

    font-weight: 600;

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


/* =====================================================
   OPPORTUNITY
===================================================== */

.opportunity-link {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    color: #17284f;

    text-decoration: none;

}


.opportunity-code {

    font-size: 14px;

    font-weight: 700;

}


.opportunity-arrow {

    color: #15966f;

    font-size: 15px;

    transition: transform .15s ease;

}


.opportunity-link:hover .opportunity-arrow {

    transform: translateX(3px);

}


.opportunity-name {

    margin-top: 6px;

    color: #4d5b70;

    font-size: 12px;

    line-height: 1.5;

}


.opportunity-meta {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 12px;

    margin-top: 17px;

    padding-top: 14px;

    border-top: 1px solid #edf0f5;

}


.opportunity-meta-item {

    display: flex;

    flex-direction: column;

    gap: 4px;

}


.opportunity-meta-item span {

    color: #8a94a6;

    font-size: 9px;

}


.opportunity-meta-item strong {

    color: #34415c;

    font-size: 11px;

}


.empty-small {

    color: #8a94a6;

    font-size: 11px;

}


/* =====================================================
   ITEMS
===================================================== */

.no-padding {

    padding: 0 !important;

}


.items-count {

    display: inline-flex;

    align-items: center;

    padding: 5px 9px;

    border-radius: 6px;

    background: #f1f5f9;

    color: #64748b;

    font-size: 10px;

    font-weight: 600;

}


.items-list {

    width: 100%;

}


.quotation-item {

    display: grid;

    grid-template-columns:
        minmax(190px, 1.7fr)
        70px
        130px
        110px
        140px;

    align-items: center;

    gap: 12px;

    padding: 15px 18px;

    border-bottom: 1px solid #edf0f5;

}


.quotation-item:last-child {

    border-bottom: 0;

}


.item-product {

    display: flex;

    align-items: center;

    gap: 10px;

    min-width: 0;

}


.product-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    flex: 0 0 auto;

    width: 32px;

    height: 32px;

    border-radius: 7px;

    background: #eef3fb;

    color: #315caa;

    font-size: 11px;

    font-weight: 700;

}


.item-product > div:last-child {

    display: flex;

    flex-direction: column;

    gap: 3px;

    min-width: 0;

}


.item-product strong {

    overflow: hidden;

    color: #17284f;

    font-size: 11px;

    font-weight: 600;

    text-overflow: ellipsis;

    white-space: nowrap;

}


.item-product span {

    color: #8a94a6;

    font-size: 9px;

}


.item-detail {

    display: flex;

    flex-direction: column;

    gap: 4px;

}


.item-detail span,
.item-subtotal span {

    color: #8a94a6;

    font-size: 9px;

}


.item-detail strong {

    color: #34415c;

    font-size: 11px;

    white-space: nowrap;

}


.item-discount {

    color: #b7791f !important;

}


.item-no-discount {

    color: #a0a8b5 !important;

}


.item-subtotal {

    display: flex;

    flex-direction: column;

    align-items: flex-end;

    gap: 4px;

}


.item-subtotal strong {

    color: #17284f;

    font-size: 12px;

    white-space: nowrap;

}


.items-empty {

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 190px;

    padding: 30px;

    text-align: center;

}


.empty-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 36px;

    height: 36px;

    margin-bottom: 3px;

    border-radius: 50%;

    background: #f1f5f9;

    color: #8a94a6;

    font-size: 17px;

}


.items-empty strong {

    color: #4d5b70;

    font-size: 12px;

}


.items-empty span {

    max-width: 280px;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.5;

}


/* =====================================================
   FINANCIAL
===================================================== */

.financial-card {

    position: sticky;

    top: 20px;

}


.financial-list {

    border: 1px solid #e4e9f1;

    border-radius: 8px;

    overflow: hidden;

}


.financial-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    min-height: 61px;

    padding: 12px 14px;

    border-bottom: 1px solid #edf0f5;

    background: #fff;

}


.financial-row > div:first-child {

    display: flex;

    flex-direction: column;

    gap: 4px;

}


.financial-row span {

    color: #34415c;

    font-size: 11px;

    font-weight: 600;

}


.financial-row small {

    color: #8a94a6;

    font-size: 9px;

}


.financial-row > strong {

    color: #17284f;

    font-size: 12px;

    white-space: nowrap;

}


.discount-row {

    background: #fffdf9;

}


.discount-amount,
.tax-amount {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

}


.discount-amount strong {

    color: #b7791f;

}


.tax-row {

    background: #fafdff;

}


.tax-amount strong {

    color: #315caa;

}


.after-discount-row {

    background: #fafbfd;

}


.type-badge {

    display: inline-flex;

    align-items: center;

    padding: 3px 6px;

    border-radius: 4px;

    font-size: 9px;

    font-weight: 700;

}


.discount-badge {

    background: #fff0d1;

    color: #9a6a18;

}


.tax-badge {

    background: #edf3ff;

    color: #315caa;

}


/* =====================================================
   GRAND TOTAL
===================================================== */

.grand-total {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-top: 12px;

    padding: 17px 15px;

    border-radius: 8px;

    background: #f1faf7;

}


.grand-total > div {

    display: flex;

    flex-direction: column;

    gap: 4px;

}


.grand-total span {

    color: #15966f;

    font-size: 12px;

    font-weight: 700;

}


.grand-total small {

    color: #6c9a8c;

    font-size: 9px;

}


.grand-total strong {

    color: #15966f;

    font-size: 18px;

    white-space: nowrap;

}


/* =====================================================
   FINANCIAL STATUS
===================================================== */

.financial-status {

    display: flex;

    align-items: center;

    gap: 6px;

    margin-top: 10px;

    color: #8a94a6;

    font-size: 9px;

}


.status-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background: #2ba7a0;

}


/* =====================================================
   NOTES
===================================================== */

.notes-card {

    margin-bottom: 0;

}


.notes-content {

    color: #34415c;

    font-size: 12px;

    line-height: 1.7;

}


.notes-empty {

    color: #8a94a6;

    font-size: 11px;

}


/* =====================================================
   METADATA
===================================================== */

.quotation-metadata {

    display: flex;

    justify-content: flex-end;

    gap: 20px;

    margin-top: 14px;

    color: #8a94a6;

    font-size: 9px;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1150px) {

    .quotation-item {

        grid-template-columns:
            minmax(180px, 1.5fr)
            60px
            115px
            100px
            125px;

        gap: 8px;

    }

}


@media (max-width: 950px) {

    .top-detail-grid {

        grid-template-columns: 1fr;

    }


    .main-detail-grid {

        grid-template-columns: 1fr;

    }


    .financial-card {

        position: static;

    }

}


@media (max-width: 700px) {

    .detail-grid {

        grid-template-columns: 1fr;

        gap: 17px;

    }


    .quotation-item {

        grid-template-columns: 1fr 1fr;

        gap: 14px;

        padding: 15px;

    }


    .item-product {

        grid-column: 1 / -1;

    }


    .item-subtotal {

        align-items: flex-start;

    }


    .opportunity-meta {

        grid-template-columns: 1fr;

    }


    .grand-total {

        align-items: flex-start;

        flex-direction: column;

    }


    .grand-total strong {

        font-size: 17px;

    }


    .quotation-metadata {

        flex-direction: column;

        align-items: flex-start;

        gap: 5px;

    }

}


@media (max-width: 600px) {

    .page-head {

        align-items: flex-start;

        flex-direction: column;

        gap: 14px;

    }


    .quotation-heading {

        align-items: flex-start;

    }


    .actions {

        width: 100%;

    }


    .actions .btn {

        flex: 1;

    }


    .financial-row {

        padding: 12px;

    }

}

</style>

@endsection