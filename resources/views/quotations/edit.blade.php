```blade
@extends('layouts.app')

@section('title', 'Edit Quotation')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Quotation</h1>

        <p>
            Update quotation information and financial configuration.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('quotations.show', $quotation) }}"
            class="btn"
        >
            ← Back to Quotation
        </a>

    </div>

</div>


@if($errors->any())

<div class="alert error">

    <strong>Please check the following errors:</strong>

    <ul>

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif


@php

    $discountEnabled = old(
        'discount_enabled',
        ((float) $quotation->discount > 0)
    );

    $taxEnabled = old(
        'tax_enabled',
        ((float) $quotation->tax > 0)
    );

    $discountType = old(
        'discount_type',
        $quotation->discount_type ?? 'amount'
    );

    $taxType = old(
        'tax_type',
        $quotation->tax_type ?? 'amount'
    );

@endphp


<div class="card">

    <div class="card-head">

        <div>

            <h3>Quotation Information</h3>

            <p>
                Update the quotation information and financial settings.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('quotations.update', $quotation) }}"
            id="quotationEditForm"
        >

            @csrf
            @method('PUT')


            <div class="quotation-form-layout">


                {{-- =================================================
                     LEFT : FORM
                ================================================== --}}

                <div class="quotation-form-main">


                    {{-- Quotation Number --}}

                    <div class="form-group">

                        <label>
                            Quotation Number
                        </label>

                        <input
                            type="text"
                            value="{{ $quotation->quotation_number }}"
                            readonly
                        >

                        <span class="form-hint">
                            The quotation number is generated automatically and cannot be changed.
                        </span>

                    </div>


                    {{-- Opportunity --}}

                    <div class="form-group">

                        <label for="opportunity_id">
                            Opportunity
                            <span class="required">*</span>
                        </label>

                        <select
                            id="opportunity_id"
                            name="opportunity_id"
                            required
                        >

                            @foreach($opportunities as $opportunity)

                                <option
                                    value="{{ $opportunity->opportunity_id }}"
                                    @selected(
                                        old(
                                            'opportunity_id',
                                            $quotation->opportunity_id
                                        ) === $opportunity->opportunity_id
                                    )
                                >
                                    {{ $opportunity->opportunity_code }}
                                    —
                                    {{ $opportunity->name }}
                                </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Select the opportunity associated with this quotation.
                        </span>

                        @error('opportunity_id')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Dates --}}

                    <div class="quotation-two-column">


                        <div class="form-group">

                            <label for="quotation_date">
                                Quotation Date
                                <span class="required">*</span>
                            </label>

                            <input
                                type="date"
                                id="quotation_date"
                                name="quotation_date"
                                value="{{ old(
                                    'quotation_date',
                                    optional($quotation->quotation_date)->format('Y-m-d')
                                ) }}"
                                required
                            >

                            <span class="form-hint">
                                Date when the quotation is issued.
                            </span>

                            @error('quotation_date')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        <div class="form-group">

                            <label for="valid_until">
                                Valid Until
                            </label>

                            <input
                                type="date"
                                id="valid_until"
                                name="valid_until"
                                value="{{ old(
                                    'valid_until',
                                    optional($quotation->valid_until)->format('Y-m-d')
                                ) }}"
                            >

                            <span class="form-hint">
                                Optional expiration date.
                            </span>

                            @error('valid_until')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>

                    </div>


                    {{-- Status --}}

                    <div class="form-group">

                        <label for="status">
                            Status
                            <span class="required">*</span>
                        </label>

                        @php

                            $quotationStatuses = [
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ];

                        @endphp

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            @foreach($quotationStatuses as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old(
                                            'status',
                                            $quotation->status
                                        ) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Current status of this quotation.
                        </span>

                        @error('status')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         FINANCIAL SETTINGS
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Financial Configuration
                            </h4>

                            <p>
                                Optional discount and tax configuration.
                            </p>

                        </div>

                    </div>


                    {{-- Discount --}}

                    <div
                        class="financial-option"
                        id="discountOption"
                    >

                        <div class="financial-option-head">

                            <div class="financial-option-title">

                                <input
                                    type="checkbox"
                                    id="discount_enabled"
                                    name="discount_enabled"
                                    value="1"
                                    @checked($discountEnabled)
                                >

                                <label for="discount_enabled">
                                    Apply Discount
                                </label>

                            </div>

                            <span
                                class="option-status"
                                id="discountStatus"
                            >
                                {{ $discountEnabled ? 'Applied' : 'Not Applied' }}
                            </span>

                        </div>


                        <div
                            class="financial-option-body"
                            id="discountBody"
                        >

                            <div class="financial-input">

                                <label for="discount">
                                    Discount
                                </label>

                                <div class="input-with-prefix">

                                    <span
                                        class="input-prefix"
                                        id="discountPrefix"
                                    >
                                        {{ $discountType === 'percentage' ? '%' : 'Rp' }}
                                    </span>

                                    <input
                                        type="number"
                                        id="discount"
                                        name="discount"
                                        value="{{ old(
                                            'discount',
                                            $quotation->discount
                                        ) }}"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                                @error('discount')

                                    <span class="form-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>


                            <div class="financial-input">

                                <label for="discount_type">
                                    Type
                                </label>

                                <select
                                    id="discount_type"
                                    name="discount_type"
                                >

                                    <option
                                        value="percentage"
                                        @selected($discountType === 'percentage')
                                    >
                                        Percentage
                                    </option>

                                    <option
                                        value="amount"
                                        @selected($discountType === 'amount')
                                    >
                                        Amount
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    {{-- Tax --}}

                    <div
                        class="financial-option"
                        id="taxOption"
                    >

                        <div class="financial-option-head">

                            <div class="financial-option-title">

                                <input
                                    type="checkbox"
                                    id="tax_enabled"
                                    name="tax_enabled"
                                    value="1"
                                    @checked($taxEnabled)
                                >

                                <label for="tax_enabled">
                                    Apply Tax
                                </label>

                            </div>

                            <span
                                class="option-status"
                                id="taxStatus"
                            >
                                {{ $taxEnabled ? 'Applied' : 'Not Applied' }}
                            </span>

                        </div>


                        <div
                            class="financial-option-body"
                            id="taxBody"
                        >

                            <div class="financial-input">

                                <label for="tax">
                                    Tax
                                </label>

                                <div class="input-with-prefix">

                                    <span
                                        class="input-prefix"
                                        id="taxPrefix"
                                    >
                                        {{ $taxType === 'percentage' ? '%' : 'Rp' }}
                                    </span>

                                    <input
                                        type="number"
                                        id="tax"
                                        name="tax"
                                        value="{{ old(
                                            'tax',
                                            $quotation->tax
                                        ) }}"
                                        min="0"
                                        step="0.01"
                                    >

                                </div>

                                @error('tax')

                                    <span class="form-error">
                                        {{ $message }}
                                    </span>

                                @enderror

                            </div>


                            <div class="financial-input">

                                <label for="tax_type">
                                    Type
                                </label>

                                <select
                                    id="tax_type"
                                    name="tax_type"
                                >

                                    <option
                                        value="percentage"
                                        @selected($taxType === 'percentage')
                                    >
                                        Percentage
                                    </option>

                                    <option
                                        value="amount"
                                        @selected($taxType === 'amount')
                                    >
                                        Amount
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    {{-- Notes --}}

                    <div class="form-group quotation-notes">

                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Enter additional notes for this quotation..."
                        >{{ old(
                            'notes',
                            $quotation->notes
                        ) }}</textarea>

                        <span class="form-hint">
                            Optional notes or additional information.
                        </span>

                        @error('notes')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Actions --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('quotations.show', $quotation) }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Update Quotation
                        </button>

                    </div>

                </div>


                {{-- =================================================
                     RIGHT : PREVIEW
                ================================================== --}}

                <aside class="quotation-preview-panel">

                    <div class="preview-label">
                        QUOTATION PREVIEW
                    </div>


                    <div class="preview-quotation-head">

                        <div class="preview-document-icon">
                            Q
                        </div>

                        <div class="preview-quotation-main">

                            <h3>
                                {{ $quotation->quotation_number }}
                            </h3>

                            <span id="previewOpportunityCode">
                                {{ $quotation->opportunity?->opportunity_code ?? 'No Opportunity' }}
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-detail">

                        <span>
                            Opportunity
                        </span>

                        <strong id="previewOpportunity">
                            {{ $quotation->opportunity?->name ?? 'No Opportunity' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Quotation Date
                        </span>

                        <strong id="previewQuotationDate">
                            {{ optional($quotation->quotation_date)->format('d M Y') }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Valid Until
                        </span>

                        <strong id="previewValidUntil">
                            {{ optional($quotation->valid_until)->format('d M Y') ?? '-' }}
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Status
                        </span>

                        <strong
                            id="previewStatus"
                            class="preview-status {{ $quotation->status }}"
                        >
                            {{ $quotationStatuses[$quotation->status] ?? $quotation->status }}
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-financial">

                        <div class="preview-financial-row">

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                Rp {{ number_format(
                                    $quotation->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>

                        </div>


                        <div class="preview-financial-row">

                            <span>
                                Discount
                            </span>

                            <strong id="previewDiscount">
                                @if($discountEnabled)

                                    @if($discountType === 'percentage')

                                        {{ number_format($quotation->discount, 2, ',', '.') }}%

                                    @else

                                        Rp {{ number_format(
                                            $quotation->discount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    @endif

                                @else

                                    -

                                @endif
                            </strong>

                        </div>


                        <div class="preview-financial-row">

                            <span>
                                Tax
                            </span>

                            <strong id="previewTax">

                                @if($taxEnabled)

                                    @if($taxType === 'percentage')

                                        {{ number_format($quotation->tax, 2, ',', '.') }}%

                                    @else

                                        Rp {{ number_format(
                                            $quotation->tax,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    @endif

                                @else

                                    -

                                @endif

                            </strong>

                        </div>


                        <div class="preview-total">

                            <span>
                                Total
                            </span>

                            <strong id="previewTotal">
                                Rp {{ number_format(
                                    $quotation->total_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </strong>

                        </div>

                    </div>


                    <div class="preview-note">

                        <strong>
                            Quotation Information
                        </strong>

                        <p>
                            Quotation items can be managed after the quotation is created. Financial totals will be recalculated automatically when quotation items are available.
                        </p>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   QUOTATION FORM LAYOUT
========================================================= */

.quotation-form-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1.25fr)
        minmax(320px, .75fr);

    gap: 32px;

    align-items: start;
}

.quotation-form-main {
    min-width: 0;
}


/* =========================================================
   FORM
========================================================= */

.form-group {
    margin-bottom: 19px;
}

.form-group label,
.financial-input label {

    display: block;

    margin-bottom: 7px;

    color: #17284f;

    font-size: 13px;

    font-weight: 600;
}

.required {
    color: #c94a4a;
}

.form-group input,
.form-group select,
.financial-input input,
.financial-input select {

    width: 100%;

    height: 42px;

    box-sizing: border-box;

    padding: 0 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-size: 13px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-group input:focus,
.form-group select:focus,
.financial-input input:focus,
.financial-input select:focus,
.form-group textarea:focus {

    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.form-group input:read-only {

    background: #f7f9fb;

    color: #718096;

    cursor: not-allowed;
}

.form-hint {

    display: block;

    margin-top: 6px;

    color: #8a94a6;

    font-size: 11px;

    line-height: 1.5;
}

.form-error {

    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;
}


/* =========================================================
   TWO COLUMN
========================================================= */

.quotation-two-column {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 16px;
}


/* =========================================================
   SECTION DIVIDER
========================================================= */

.section-divider {

    display: flex;

    align-items: center;

    margin: 25px 0 18px;

    padding-top: 20px;

    border-top: 1px solid #edf0f5;
}

.section-divider h4 {

    margin: 0 0 4px;

    color: #17284f;

    font-size: 13px;
}

.section-divider p {

    margin: 0;

    color: #8a94a6;

    font-size: 11px;
}


/* =========================================================
   FINANCIAL OPTIONS
========================================================= */

.financial-option {

    margin-bottom: 12px;

    border: 1px solid #e3e7ee;

    border-radius: 9px;

    background: #fff;

    overflow: hidden;

    transition:
        border-color .18s ease,
        background .18s ease;
}

.financial-option.active {

    border-color: #b8ddd5;

    background: #fbfefd;
}

.financial-option-head {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    min-height: 48px;

    padding: 0 13px;
}

.financial-option-title {

    display: flex;

    align-items: center;

    gap: 9px;
}

.financial-option-title input {

    width: 16px;

    height: 16px;

    margin: 0;

    accent-color: #2ba7a0;

    cursor: pointer;
}

.financial-option-title label {

    margin: 0;

    color: #34415c;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}

.option-status {

    padding: 4px 8px;

    border-radius: 6px;

    background: #f1f3f6;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 600;
}

.financial-option.active .option-status {

    background: #eaf7f3;

    color: #167d70;
}

.financial-option-body {

    display: none;

    grid-template-columns: 1fr 150px;

    gap: 12px;

    padding: 0 13px 14px;

    border-top: 1px solid #edf0f5;
}

.financial-option.active .financial-option-body {

    display: grid;

    padding-top: 14px;
}

.input-with-prefix {

    display: flex;

    width: 100%;
}

.input-prefix {

    display: flex;

    align-items: center;

    justify-content: center;

    min-width: 42px;

    height: 42px;

    box-sizing: border-box;

    border: 1px solid #d9dee8;

    border-right: 0;

    border-radius: 8px 0 0 8px;

    background: #f7f9fb;

    color: #718096;

    font-size: 12px;

    font-weight: 600;
}

.input-with-prefix input {

    border-radius: 0 8px 8px 0;
}


/* =========================================================
   NOTES
========================================================= */

.quotation-notes {

    margin-top: 22px;
}

.form-group textarea {

    width: 100%;

    min-height: 105px;

    box-sizing: border-box;

    padding: 11px 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;

    font-size: 13px;

    resize: vertical;
}


/* =========================================================
   ACTIONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 8px;

    padding-top: 8px;

    margin-top: 8px;

    border-top: 1px solid #edf0f5;
}


/* =========================================================
   PREVIEW
========================================================= */

.quotation-preview-panel {

    position: sticky;

    top: 24px;

    padding: 24px;

    border: 1px solid #e6eaf0;

    border-radius: 12px;

    background: #fafbfd;
}

.preview-label {

    margin-bottom: 20px;

    color: #8a94a6;

    font-size: 10px;

    font-weight: 700;

    letter-spacing: 1.2px;
}

.preview-quotation-head {

    display: flex;

    align-items: center;

    gap: 13px;
}

.preview-document-icon {

    width: 48px;

    height: 48px;

    flex-shrink: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #eaf7f3;

    color: #167d70;

    font-size: 17px;

    font-weight: 700;
}

.preview-quotation-main {

    min-width: 0;
}

.preview-quotation-main h3 {

    margin: 0 0 4px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #17284f;

    font-size: 17px;
}

.preview-quotation-main span {

    display: block;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #7d8797;

    font-size: 11px;
}

.preview-divider {

    height: 1px;

    margin: 21px 0;

    background: #e5e9ef;
}

.preview-detail {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;

    padding: 9px 0;
}

.preview-detail span {

    color: #8a94a6;

    font-size: 11px;
}

.preview-detail strong {

    max-width: 190px;

    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;

    color: #34415c;

    font-size: 12px;

    text-align: right;
}


/* =========================================================
   STATUS
========================================================= */

.preview-status {

    display: inline-flex;

    padding: 4px 8px;

    border-radius: 6px;

    font-size: 10px !important;
}

.preview-status.draft {

    background: #f1f3f6;

    color: #7d8797 !important;
}

.preview-status.sent {

    background: #edf4fb;

    color: #4776a8 !important;
}

.preview-status.approved {

    background: #eaf7f3;

    color: #167d70 !important;
}

.preview-status.rejected,
.preview-status.cancelled {

    background: #fbeeee;

    color: #b84b4b !important;
}

.preview-status.expired {

    background: #fff5e8;

    color: #b2772e !important;
}


/* =========================================================
   FINANCIAL PREVIEW
========================================================= */

.preview-financial-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 8px 0;

    color: #8a94a6;

    font-size: 11px;
}

.preview-financial-row strong {

    color: #34415c;

    font-size: 11px;
}

.preview-total {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 8px;

    padding-top: 13px;

    border-top: 1px solid #e5e9ef;
}

.preview-total span {

    color: #34415c;

    font-size: 12px;

    font-weight: 600;
}

.preview-total strong {

    color: #17284f;

    font-size: 16px;
}


/* =========================================================
   PREVIEW NOTE
========================================================= */

.preview-note {

    margin-top: 20px;

    padding: 13px 14px;

    border-radius: 8px;

    background: #f1f5f7;
}

.preview-note strong {

    display: block;

    margin-bottom: 5px;

    color: #34415c;

    font-size: 11px;
}

.preview-note p {

    margin: 0;

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.6;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .quotation-form-layout {

        grid-template-columns: 1fr;
    }

    .quotation-preview-panel {

        position: static;

        order: -1;
    }

}

@media (max-width: 600px) {

    .quotation-two-column,
    .financial-option-body {

        grid-template-columns: 1fr;
    }

    .form-actions {

        flex-direction: column-reverse;
    }

    .form-actions .btn {

        width: 100%;

        justify-content: center;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const opportunitySelect =
            document.getElementById('opportunity_id');

        const quotationDate =
            document.getElementById('quotation_date');

        const validUntil =
            document.getElementById('valid_until');

        const statusSelect =
            document.getElementById('status');


        const discountEnabled =
            document.getElementById('discount_enabled');

        const discountInput =
            document.getElementById('discount');

        const discountType =
            document.getElementById('discount_type');

        const discountOption =
            document.getElementById('discountOption');

        const discountPrefix =
            document.getElementById('discountPrefix');

        const discountStatus =
            document.getElementById('discountStatus');


        const taxEnabled =
            document.getElementById('tax_enabled');

        const taxInput =
            document.getElementById('tax');

        const taxType =
            document.getElementById('tax_type');

        const taxOption =
            document.getElementById('taxOption');

        const taxPrefix =
            document.getElementById('taxPrefix');

        const taxStatus =
            document.getElementById('taxStatus');


        const previewOpportunity =
            document.getElementById('previewOpportunity');

        const previewOpportunityCode =
            document.getElementById('previewOpportunityCode');

        const previewQuotationDate =
            document.getElementById('previewQuotationDate');

        const previewValidUntil =
            document.getElementById('previewValidUntil');

        const previewStatus =
            document.getElementById('previewStatus');

        const previewDiscount =
            document.getElementById('previewDiscount');

        const previewTax =
            document.getElementById('previewTax');

        const previewTotal =
            document.getElementById('previewTotal');


        function formatDate(value) {

            if (!value) {
                return '-';
            }

            const date =
                new Date(value + 'T00:00:00');

            return date.toLocaleDateString(
                'id-ID',
                {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }
            );

        }


        function formatNumber(value) {

            return new Intl.NumberFormat(
                'id-ID'
            ).format(
                Number(value || 0)
            );

        }


        function updateFinancialOption(
            checkbox,
            option,
            input,
            typeSelect,
            prefix,
            status
        ) {

            const enabled =
                checkbox.checked;

            option.classList.toggle(
                'active',
                enabled
            );

            input.disabled =
                !enabled;

            typeSelect.disabled =
                !enabled;

            status.textContent =
                enabled
                    ? 'Applied'
                    : 'Not Applied';

            updateFinancialType(
                typeSelect,
                input,
                prefix
            );

        }


        function updateFinancialType(
            typeSelect,
            input,
            prefix
        ) {

            if (typeSelect.value === 'percentage') {

                prefix.textContent = '%';

                input.max = '100';

            } else {

                prefix.textContent = 'Rp';

                input.removeAttribute('max');

            }

        }


        function updatePreview() {

            const selectedOption =
                opportunitySelect.options[
                    opportunitySelect.selectedIndex
                ];

            const opportunityText =
                opportunitySelect.value
                    ? selectedOption.text
                    : 'No Opportunity';

            const parts =
                opportunityText.split(' — ');

            previewOpportunityCode.textContent =
                opportunitySelect.value
                    ? parts[0]
                    : 'No Opportunity';

            previewOpportunity.textContent =
                opportunitySelect.value
                    ? parts.slice(1).join(' — ')
                    : 'No Opportunity';


            previewQuotationDate.textContent =
                formatDate(
                    quotationDate.value
                );

            previewValidUntil.textContent =
                formatDate(
                    validUntil.value
                );


            const status =
                statusSelect.value || 'draft';

            const statusLabel =
                statusSelect.options[
                    statusSelect.selectedIndex
                ]?.text || 'Draft';

            previewStatus.textContent =
                statusLabel;

            previewStatus.className =
                'preview-status ' + status;


            if (discountEnabled.checked) {

                const value =
                    Number(
                        discountInput.value || 0
                    );

                previewDiscount.textContent =
                    discountType.value === 'percentage'
                        ? formatNumber(value) + '%'
                        : 'Rp ' + formatNumber(value);

            } else {

                previewDiscount.textContent =
                    '-';

            }


            if (taxEnabled.checked) {

                const value =
                    Number(
                        taxInput.value || 0
                    );

                previewTax.textContent =
                    taxType.value === 'percentage'
                        ? formatNumber(value) + '%'
                        : 'Rp ' + formatNumber(value);

            } else {

                previewTax.textContent =
                    '-';

            }


            /*
            |--------------------------------------------------------------------------
            | Current stored total
            |--------------------------------------------------------------------------
            |
            | Subtotal masih berasal dari quotation_items.
            | Karena item belum dihitung ulang di browser,
            | total tetap menggunakan total yang tersimpan.
            |
            */

        }


        discountEnabled.addEventListener(
            'change',
            function () {

                updateFinancialOption(
                    discountEnabled,
                    discountOption,
                    discountInput,
                    discountType,
                    discountPrefix,
                    discountStatus
                );

                updatePreview();

            }
        );


        discountType.addEventListener(
            'change',
            function () {

                updateFinancialType(
                    discountType,
                    discountInput,
                    discountPrefix
                );

                updatePreview();

            }
        );


        discountInput.addEventListener(
            'input',
            updatePreview
        );


        taxEnabled.addEventListener(
            'change',
            function () {

                updateFinancialOption(
                    taxEnabled,
                    taxOption,
                    taxInput,
                    taxType,
                    taxPrefix,
                    taxStatus
                );

                updatePreview();

            }
        );


        taxType.addEventListener(
            'change',
            function () {

                updateFinancialType(
                    taxType,
                    taxInput,
                    taxPrefix
                );

                updatePreview();

            }
        );


        taxInput.addEventListener(
            'input',
            updatePreview
        );


        opportunitySelect.addEventListener(
            'change',
            updatePreview
        );

        quotationDate.addEventListener(
            'change',
            updatePreview
        );

        validUntil.addEventListener(
            'change',
            updatePreview
        );

        statusSelect.addEventListener(
            'change',
            updatePreview
        );


        updateFinancialOption(
            discountEnabled,
            discountOption,
            discountInput,
            discountType,
            discountPrefix,
            discountStatus
        );

        updateFinancialOption(
            taxEnabled,
            taxOption,
            taxInput,
            taxType,
            taxPrefix,
            taxStatus
        );

        updatePreview();

    }
);

</script>

@endsection
```
