@extends('layouts.app')

@section('title', 'Create Quotation')

@section('content')

<div class="page-head">

    <div>

        <h1>Create Quotation</h1>

        <p>
            Create a quotation based on an existing opportunity and its products.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('quotations.index') }}"
            class="btn"
        >
            ← Back to Quotations
        </a>

    </div>

</div>


@if($errors->any())

    <div class="alert error">

        <strong>Please check the following errors:</strong>

        <ul>

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


<div class="card quotation-create-card">

    <div class="card-head">

        <div>

            <h3>Quotation Information</h3>

            <p>
                Configure the quotation details, pricing, discount, tax, and validity period.
            </p>

        </div>

    </div>


    <div class="card-body quotation-create-body">


        <form
            method="POST"
            action="{{ route('quotations.store') }}"
            id="quotationCreateForm"
        >

            @csrf


            <div class="quotation-form-layout">


                {{-- =====================================================
                     LEFT : QUOTATION FORM
                ====================================================== --}}

                <div class="quotation-form-main">


                    {{-- =================================================
                         OPPORTUNITY
                    ================================================== --}}

                    <div class="section-divider first-section">

                        <div>

                            <h4>Opportunity</h4>

                            <p>
                                Select the opportunity that will be used as the quotation source.
                            </p>

                        </div>

                    </div>


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

                            <option value="">
                                Select Opportunity
                            </option>


                            @foreach($opportunities as $opportunity)

                                <option
                                    value="{{ $opportunity->opportunity_id }}"
                                    @selected(
                                        old('opportunity_id') === $opportunity->opportunity_id
                                    )
                                >
                                    {{ $opportunity->opportunity_code }}
                                    —
                                    {{ $opportunity->name }}
                                </option>

                            @endforeach

                        </select>


                        <span class="form-hint">

                            Customer, sales, and opportunity items will be loaded automatically.

                        </span>


                        @error('opportunity_id')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         QUOTATION DATE
                    ================================================== --}}

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
                                value="{{ old('quotation_date', now()->format('Y-m-d')) }}"
                                required
                            >


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
                                value="{{ old('valid_until') }}"
                            >


                            <span class="form-hint">
                                Date until this quotation remains valid.
                            </span>


                            @error('valid_until')

                                <span class="form-error">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                    </div>


                    {{-- =================================================
                         OPPORTUNITY ITEMS
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>Quotation Items</h4>

                            <p>
                                Products from the selected opportunity will be included automatically.
                            </p>

                        </div>

                    </div>


                    <div
                        class="quotation-items-container"
                        id="quotationItemsContainer"
                    >

                        <div class="empty-items">

                            <div class="empty-items-icon">
                                +
                            </div>

                            <strong>
                                No Opportunity Selected
                            </strong>

                            <span>
                                Select an opportunity to load its products.
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                         DISCOUNT
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>Discount</h4>

                            <p>
                                Optionally apply a percentage or fixed amount discount.
                            </p>

                        </div>

                    </div>


                    <div class="financial-option">

                        <div class="financial-option-header" role="button" tabindex="0" aria-label="Toggle financial option">

                            <label class="checkbox-label">

                                <input
                                    type="checkbox"
                                    id="discount_enabled"
                                    name="discount_enabled"
                                    value="1"
                                    @checked(old('discount_enabled'))
                                >

                                <span>
                                    Apply Discount
                                </span>

                            </label>

                        </div>


                        <div
                            class="financial-option-body"
                            id="discountOptions"
                        >

                            <div class="quotation-two-column">


                                <div class="form-group">

                                    <label for="discount">
                                        Discount Value
                                    </label>


                                    <div
                                        class="input-with-prefix"
                                        id="discountInputWrapper"
                                    >

                                        <span
                                            class="input-prefix"
                                            id="discountPrefix"
                                        >
                                            %
                                        </span>


                                        <input
                                            type="number"
                                            id="discount"
                                            name="discount"
                                            value="{{ old('discount', 0) }}"
                                            min="0"
                                            step="0.01"
                                            disabled
                                        >

                                    </div>


                                    @error('discount')

                                        <span class="form-error">
                                            {{ $message }}
                                        </span>

                                    @enderror

                                </div>


                                <div class="form-group">

                                    <label for="discount_type">
                                        Discount Type
                                    </label>


                                    <select
                                        id="discount_type"
                                        name="discount_type"
                                        disabled
                                    >

                                        <option
                                            value="percentage"
                                            @selected(
                                                old('discount_type', 'percentage') === 'percentage'
                                            )
                                        >
                                            Percentage
                                        </option>


                                        <option
                                            value="amount"
                                            @selected(
                                                old('discount_type') === 'amount'
                                            )
                                        >
                                            Amount
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         TAX
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>Tax</h4>

                            <p>
                                Optionally apply a percentage or fixed amount tax.
                            </p>

                        </div>

                    </div>


                    <div class="financial-option">

                        <div class="financial-option-header" role="button" tabindex="0" aria-label="Toggle financial option">

                            <label class="checkbox-label">

                                <input
                                    type="checkbox"
                                    id="tax_enabled"
                                    name="tax_enabled"
                                    value="1"
                                    @checked(old('tax_enabled'))
                                >

                                <span>
                                    Apply Tax
                                </span>

                            </label>

                        </div>


                        <div
                            class="financial-option-body"
                            id="taxOptions"
                        >

                            <div class="quotation-two-column">


                                <div class="form-group">

                                    <label for="tax">
                                        Tax Value
                                    </label>


                                    <div
                                        class="input-with-prefix"
                                        id="taxInputWrapper"
                                    >

                                        <span
                                            class="input-prefix"
                                            id="taxPrefix"
                                        >
                                            %
                                        </span>


                                        <input
                                            type="number"
                                            id="tax"
                                            name="tax"
                                            value="{{ old('tax', 0) }}"
                                            min="0"
                                            step="0.01"
                                            disabled
                                        >

                                    </div>


                                    @error('tax')

                                        <span class="form-error">
                                            {{ $message }}
                                        </span>

                                    @enderror

                                </div>


                                <div class="form-group">

                                    <label for="tax_type">
                                        Tax Type
                                    </label>


                                    <select
                                        id="tax_type"
                                        name="tax_type"
                                        disabled
                                    >

                                        <option
                                            value="percentage"
                                            @selected(
                                                old('tax_type', 'percentage') === 'percentage'
                                            )
                                        >
                                            Percentage
                                        </option>


                                        <option
                                            value="amount"
                                            @selected(
                                                old('tax_type') === 'amount'
                                            )
                                        >
                                            Amount
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         STATUS
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>Quotation Status</h4>

                            <p>
                                Define the current status of this quotation.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="status">

                            Status

                            <span class="required">*</span>

                        </label>


                        <select id="status" name="status" required>
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                            <option value="sent" @selected(old('status') === 'sent')>Sent - Already Sent to Customer</option>
                        </select>
                        <span class="form-hint">Choose Sent only if the quotation has actually been sent. Customer approval or rejection is recorded in Quotation Details.</span>
                        @error('status')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         NOTES
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>Notes</h4>

                            <p>
                                Add additional information or remarks for this quotation.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="notes">
                            Notes
                        </label>


                        <textarea
                            id="notes"
                            name="notes"
                            placeholder="Enter quotation notes or additional information..."
                        >{{ old('notes') }}</textarea>


                        @error('notes')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         FORM ACTIONS
                    ================================================== --}}

                    <div class="form-actions">

                        <a
                            href="{{ route('quotations.index') }}"
                            class="btn"
                        >
                            Cancel
                        </a>


                        <button
                            type="submit"
                            class="btn primary"
                        >
                            Create Quotation
                        </button>

                    </div>


                </div>


                {{-- =====================================================
                     RIGHT : QUOTATION PREVIEW
                ====================================================== --}}

                <aside class="quotation-preview-panel">


                    <div class="preview-label">
                        QUOTATION PREVIEW
                    </div>


                    {{-- =================================================
                         PREVIEW HEADER
                    ================================================== --}}

                    <div class="preview-quotation-head">

                        <div class="preview-quotation-icon">
                            Q
                        </div>


                        <div class="preview-quotation-main">

                            <h3>
                                Quotation
                            </h3>

                            <span id="previewQuotationStatus">
                                Draft
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- =================================================
                         OPPORTUNITY
                    ================================================== --}}

                    <div class="preview-detail">

                        <span>
                            Opportunity
                        </span>


                        <strong id="previewOpportunityCode">
                            No Opportunity
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Name
                        </span>


                        <strong id="previewOpportunity">
                            No Opportunity
                        </strong>

                    </div>


                    {{-- =================================================
                         CUSTOMER
                    ================================================== --}}

                    <div class="preview-detail">

                        <span>
                            Customer
                        </span>


                        <strong id="previewCustomer">
                            No Customer
                        </strong>

                    </div>


                    {{-- =================================================
                         SALES
                    ================================================== --}}

                    <div class="preview-detail">

                        <span>
                            Sales
                        </span>


                        <strong id="previewSales">
                            No Sales
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- =================================================
                         DATE
                    ================================================== --}}

                    <div class="preview-detail">

                        <span>
                            Quotation Date
                        </span>


                        <strong id="previewQuotationDate">
                            -
                        </strong>

                    </div>


                    <div class="preview-detail">

                        <span>
                            Valid Until
                        </span>


                        <strong id="previewValidUntil">
                            -
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- =================================================
                         ITEMS
                    ================================================== --}}

                    <div class="preview-items-section">

                        <div class="preview-section-title">
                            Items
                        </div>


                        <div
                            id="previewItems"
                            class="preview-items"
                        >

                            <div class="preview-empty-items">
                                No items
                            </div>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- =================================================
                         FINANCIAL
                    ================================================== --}}

                    <div class="preview-financial-row">

                        <span>
                            Subtotal
                        </span>


                        <strong id="previewSubtotal">
                            Rp 0
                        </strong>

                    </div>


                    <div
                        class="preview-financial-row"
                        id="previewDiscountRow"
                    >

                        <span>
                            Discount
                        </span>


                        <strong id="previewDiscount">
                            Rp 0
                        </strong>

                    </div>


                    <div
                        class="preview-financial-row"
                        id="previewTaxRow"
                    >

                        <span>
                            Tax
                        </span>


                        <strong id="previewTax">
                            Rp 0
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    <div class="preview-total-row">

                        <span>
                            Total
                        </span>


                        <strong id="previewTotal">
                            Rp 0
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- =================================================
                         STATUS
                    ================================================== --}}

                    <div class="preview-detail preview-status-detail">

                        <span>
                            Status
                        </span>


                        <strong
                            id="previewStatus"
                            class="preview-status draft"
                        >
                            Draft
                        </strong>

                    </div>


                    {{-- =================================================
                         NOTE
                    ================================================== --}}

                    <div class="preview-note">

                        <strong>
                            Quotation Information
                        </strong>


                        <p>
                            This preview updates automatically based on the selected opportunity, products, discount, tax, and quotation information.
                        </p>

                    </div>


                </aside>


            </div>

        </form>

    </div>

</div>





<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {


        const opportunities =
            @json($opportunityData);

        const quotationForm =
            document.getElementById(
                'quotationCreateForm'
            );


        const opportunitySelect =
            document.getElementById(
                'opportunity_id'
            );


        const quotationDate =
            document.getElementById(
                'quotation_date'
            );


        const validUntil =
            document.getElementById(
                'valid_until'
            );


        const discountEnabled =
            document.getElementById(
                'discount_enabled'
            );


        const discount =
            document.getElementById(
                'discount'
            );


        const discountType =
            document.getElementById(
                'discount_type'
            );


        const taxEnabled =
            document.getElementById(
                'tax_enabled'
            );


        const tax =
            document.getElementById(
                'tax'
            );


        const taxType =
            document.getElementById(
                'tax_type'
            );


        const status =
            document.getElementById(
                'status'
            );


        const quotationItemsContainer =
            document.getElementById(
                'quotationItemsContainer'
            );


        /* =====================================================
           PREVIEW ELEMENTS
        ====================================================== */

        const previewOpportunityCode =
            document.getElementById(
                'previewOpportunityCode'
            );


        const previewOpportunity =
            document.getElementById(
                'previewOpportunity'
            );


        const previewCustomer =
            document.getElementById(
                'previewCustomer'
            );


        const previewSales =
            document.getElementById(
                'previewSales'
            );


        const previewQuotationDate =
            document.getElementById(
                'previewQuotationDate'
            );


        const previewValidUntil =
            document.getElementById(
                'previewValidUntil'
            );


        const previewItems =
            document.getElementById(
                'previewItems'
            );


        const previewSubtotal =
            document.getElementById(
                'previewSubtotal'
            );


        const previewDiscount =
            document.getElementById(
                'previewDiscount'
            );


        const previewTax =
            document.getElementById(
                'previewTax'
            );


        const previewTotal =
            document.getElementById(
                'previewTotal'
            );


        const previewStatus =
            document.getElementById(
                'previewStatus'
            );


        const previewQuotationStatus =
            document.getElementById(
                'previewQuotationStatus'
            );


        /* =====================================================
           FORMAT CURRENCY
        ====================================================== */

        function formatCurrency(value) {

            const number =
                Number(value);


            if (
                Number.isNaN(number)
            ) {

                return 'Rp 0';

            }


            return 'Rp ' +
                new Intl.NumberFormat(
                    'id-ID',
                    {
                        maximumFractionDigits: 2
                    }
                ).format(number);

        }


        /* =====================================================
           FORMAT DATE
        ====================================================== */

        function formatDate(value) {

            if (!value) {

                return '-';

            }


            const date =
                new Date(
                    value + 'T00:00:00'
                );


            if (
                Number.isNaN(
                    date.getTime()
                )
            ) {

                return value;

            }


            return new Intl.DateTimeFormat(
                'id-ID',
                {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                }
            ).format(date);

        }


        /* =====================================================
           GET SELECTED OPPORTUNITY
        ====================================================== */

        function getSelectedOpportunity() {

            const id =
                opportunitySelect.value;


            if (!id) {

                return null;

            }


            return opportunities[id]
                ?? null;

        }


        /* =====================================================
           UPDATE OPPORTUNITY PREVIEW
        ====================================================== */

        function updateOpportunityPreview() {

            const opportunity =
                getSelectedOpportunity();


            if (!opportunity) {

                previewOpportunityCode.textContent =
                    'No Opportunity';

                previewOpportunity.textContent =
                    'No Opportunity';

                previewCustomer.textContent =
                    'No Customer';

                previewSales.textContent =
                    'No Sales';

                return;

            }


            previewOpportunityCode.textContent =
                opportunity.code
                || 'No Opportunity';


            previewOpportunity.textContent =
                opportunity.name
                || 'No Opportunity';


            previewCustomer.textContent =
                opportunity.customer
                || 'No Customer';


            previewSales.textContent =
                opportunity.sales
                || 'No Sales';

        }


        /* =====================================================
           RENDER QUOTATION ITEMS
        ====================================================== */

        function renderQuotationItems() {

            const opportunity =
                getSelectedOpportunity();


            quotationItemsContainer.innerHTML =
                '';


            previewItems.innerHTML =
                '';


            if (
                !opportunity ||
                !opportunity.items ||
                opportunity.items.length === 0
            ) {

                quotationItemsContainer.innerHTML = `

                    <div class="empty-items">

                        <div class="empty-items-icon">
                            +
                        </div>

                        <strong>
                            No Opportunity Items
                        </strong>

                        <span>
                            This opportunity does not have any products.
                        </span>

                    </div>

                `;


                previewItems.innerHTML = `

                    <div class="preview-empty-items">
                        No items
                    </div>

                `;


                updateFinancialPreview();

                return;

            }


            opportunity.items.forEach(
                function (item, index) {


                    const quantity =
                        Number(
                            item.quantity || 1
                        );


                    const unitPrice =
                        Number(
                            item.estimated_price || 0
                        );


                    const itemSubtotal =
                        quantity *
                        unitPrice;


                    /*
                    |--------------------------------------------------------------------------
                    | Hidden Inputs
                    |--------------------------------------------------------------------------
                    */

                    const hiddenFields = `

                        <input
                            type="hidden"
                            name="items[${index}][opportunity_item_id]"
                            value="${escapeHtml(item.opportunity_item_id)}"
                        >

                        <input
                            type="hidden"
                            name="items[${index}][product_id]"
                            value="${escapeHtml(item.product_id)}"
                        >

                        <input
                            type="hidden"
                            name="items[${index}][quantity]"
                            value="${quantity}"
                        >

                        <input
                            type="hidden"
                            name="items[${index}][unit_price]"
                            value="${unitPrice}"
                        >

                    `;


                    /*
                    |--------------------------------------------------------------------------
                    | Form Item
                    |--------------------------------------------------------------------------
                    */

                    const itemCard =
                        document.createElement(
                            'div'
                        );


                    itemCard.className =
                        'quotation-item-card';


                    itemCard.innerHTML = `

                        ${hiddenFields}

                        <div class="quotation-item-header">

                            <div class="quotation-item-product">

                                <strong>
                                    ${escapeHtml(item.product_name)}
                                </strong>

                                <span>
                                    ${escapeHtml(item.product_code)}
                                </span>

                            </div>

                            <span class="quotation-item-index">
                                #${index + 1}
                            </span>

                        </div>


                        <div class="quotation-item-grid">

                            <div class="quotation-item-field">

                                <span>
                                    Quantity
                                </span>

                                <strong>
                                    ${quantity} ${escapeHtml(item.unit)}
                                </strong>

                            </div>


                            <div class="quotation-item-field">

                                <span>
                                    Unit Price
                                </span>

                                <strong class="quotation-item-price">
                                    ${formatCurrency(unitPrice)}
                                </strong>

                            </div>


                            <div class="quotation-item-field">

                                <span>
                                    Subtotal
                                </span>

                                <strong>
                                    ${formatCurrency(itemSubtotal)}
                                </strong>

                            </div>

                        </div>

                    `;


                    quotationItemsContainer.appendChild(
                        itemCard
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Preview Item
                    |--------------------------------------------------------------------------
                    */

                    const previewItem =
                        document.createElement(
                            'div'
                        );


                    previewItem.className =
                        'preview-item';


                    previewItem.innerHTML = `

                        <div class="preview-item-head">

                            <span class="preview-item-name">

                                ${escapeHtml(
                                    item.product_name
                                )}

                            </span>


                            <strong class="preview-item-total">

                                ${formatCurrency(
                                    itemSubtotal
                                )}

                            </strong>

                        </div>


                        <div class="preview-item-meta">

                            <span>
                                ${quantity} × ${formatCurrency(unitPrice)}
                            </span>

                            <span>
                                ${escapeHtml(item.unit)}
                            </span>

                        </div>

                    `;


                    previewItems.appendChild(
                        previewItem
                    );

                }
            );


            updateFinancialPreview();

        }


        /* =====================================================
           ESCAPE HTML
        ====================================================== */

        function escapeHtml(value) {

            if (
                value === null ||
                value === undefined
            ) {

                return '';

            }


            return String(value)
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


        /* =====================================================
           GET SUBTOTAL
        ====================================================== */

        function getSubtotal() {

            const opportunity =
                getSelectedOpportunity();


            if (
                !opportunity ||
                !opportunity.items
            ) {

                return 0;

            }


            return opportunity.items.reduce(
                function (total, item) {

                    const quantity =
                        Number(
                            item.quantity || 1
                        );


                    const price =
                        Number(
                            item.estimated_price || 0
                        );


                    return total +
                        (
                            quantity *
                            price
                        );

                },
                0
            );

        }


        /* =====================================================
           UPDATE FINANCIAL PREVIEW
        ====================================================== */

        function updateFinancialPreview() {

            const subtotalValue =
                getSubtotal();


            let discountValue =
                Number(
                    discount.value || 0
                );


            let taxValue =
                Number(
                    tax.value || 0
                );


            let discountAmount =
                0;


            let taxAmount =
                0;


            /*
            |--------------------------------------------------------------------------
            | Discount
            |--------------------------------------------------------------------------
            */

            if (
                discountEnabled.checked
            ) {

                if (
                    discountType.value ===
                    'percentage'
                ) {

                    discountAmount =
                        subtotalValue *
                        (
                            discountValue /
                            100
                        );

                } else {

                    discountAmount =
                        discountValue;

                }

            }


            discountAmount =
                Math.min(
                    discountAmount,
                    subtotalValue
                );


            const afterDiscount =
                Math.max(
                    0,
                    subtotalValue -
                    discountAmount
                );


            /*
            |--------------------------------------------------------------------------
            | Tax
            |--------------------------------------------------------------------------
            */

            if (
                taxEnabled.checked
            ) {

                if (
                    taxType.value ===
                    'percentage'
                ) {

                    taxAmount =
                        afterDiscount *
                        (
                            taxValue /
                            100
                        );

                } else {

                    taxAmount =
                        taxValue;

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Total
            |--------------------------------------------------------------------------
            */

            const totalValue =
                afterDiscount +
                taxAmount;


            /*
            |--------------------------------------------------------------------------
            | Preview
            |--------------------------------------------------------------------------
            */

            previewSubtotal.textContent =
                formatCurrency(
                    subtotalValue
                );


            if (
                discountEnabled.checked &&
                discountValue > 0
            ) {

                if (
                    discountType.value ===
                    'percentage'
                ) {

                    previewDiscount.textContent =
                        '- ' +
                        formatCurrency(
                            discountAmount
                        ) +
                        ' (' +
                        discountValue +
                        '%)';

                } else {

                    previewDiscount.textContent =
                        '- ' +
                        formatCurrency(
                            discountAmount
                        );

                }

            } else {

                previewDiscount.textContent =
                    'Rp 0';

            }


            if (
                taxEnabled.checked &&
                taxValue > 0
            ) {

                if (
                    taxType.value ===
                    'percentage'
                ) {

                    previewTax.textContent =
                        '+ ' +
                        formatCurrency(
                            taxAmount
                        ) +
                        ' (' +
                        taxValue +
                        '%)';

                } else {

                    previewTax.textContent =
                        '+ ' +
                        formatCurrency(
                            taxAmount
                        );

                }

            } else {

                previewTax.textContent =
                    'Rp 0';

            }


            previewTotal.textContent =
                formatCurrency(
                    totalValue
                );

        }


        /* =====================================================
           UPDATE DATE PREVIEW
        ====================================================== */

        function updateDatePreview() {

            previewQuotationDate.textContent =
                formatDate(
                    quotationDate.value
                );


            previewValidUntil.textContent =
                formatDate(
                    validUntil.value
                );

        }


        /* =====================================================
           UPDATE STATUS PREVIEW
        ====================================================== */

        function updateStatusPreview() {

            const value =
                status.value || 'draft';


            const label =
                value
                    .charAt(0)
                    .toUpperCase() +
                value.slice(1);


            previewStatus.textContent =
                label;


            previewQuotationStatus.textContent =
                label;


            previewStatus.classList.remove(
                'draft',
                'sent',
                'accepted',
                'rejected',
                'expired'
            );


            previewStatus.classList.add(
                value
            );

        }


        /* =====================================================
           FINANCIAL OPTION
        ====================================================== */

        function updateFinancialOption(
            checkbox,
            input,
            select
        ) {

            const enabled =
                checkbox.checked;


            input.disabled =
                !enabled;


            select.disabled =
                !enabled;

            const container = checkbox.closest('.financial-option');
            if (container) {
                container.classList.toggle('active', enabled);
            }


            if (!enabled) {

                input.value =
                    input.value || 0;

            }


            updateFinancialType(
                select,
                input
            );

            updateFinancialPreview();

        }


        /* =====================================================
           FINANCIAL TYPE
        ====================================================== */

        function updateFinancialType(
            select,
            input
        ) {

            const prefixId =
                select.id ===
                'discount_type'
                    ? 'discountPrefix'
                    : 'taxPrefix';


            const prefix =
                document.getElementById(
                    prefixId
                );


            if (
                select.value ===
                'percentage'
            ) {

                prefix.textContent =
                    '%';


                input.max =
                    '100';

            } else {

                prefix.textContent =
                    'Rp';


                input.removeAttribute(
                    'max'
                );

            }


            updateFinancialPreview();

        }


        /* =====================================================
           OPPORTUNITY CHANGE
        ====================================================== */

        opportunitySelect.addEventListener(
            'change',
            function () {

                updateOpportunityPreview();

                renderQuotationItems();

            }
        );


        /* =====================================================
           DATE CHANGE
        ====================================================== */

        quotationDate.addEventListener(
            'input',
            updateDatePreview
        );


        quotationDate.addEventListener(
            'change',
            updateDatePreview
        );


        validUntil.addEventListener(
            'input',
            updateDatePreview
        );


        validUntil.addEventListener(
            'change',
            updateDatePreview
        );


        /* =====================================================
           DISCOUNT CHANGE
        ====================================================== */

        document.querySelectorAll('.financial-option-header').forEach(header => {
            const checkbox = header.querySelector('input[type="checkbox"]');
            if (!checkbox) return;
            header.addEventListener('click', event => {
                if (event.target.closest('label, input')) return;
                checkbox.click();
            });
            header.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    checkbox.click();
                }
            });
        });

        discountEnabled.addEventListener(
            'change',
            function () {

                updateFinancialOption(
                    discountEnabled,
                    discount,
                    discountType
                );

            }
        );


        discountType.addEventListener(
            'change',
            function () {

                updateFinancialType(
                    discountType,
                    discount
                );

            }
        );


        discount.addEventListener(
            'input',
            updateFinancialPreview
        );


        /* =====================================================
           TAX CHANGE
        ====================================================== */

        taxEnabled.addEventListener(
            'change',
            function () {

                updateFinancialOption(
                    taxEnabled,
                    tax,
                    taxType
                );

            }
        );


        taxType.addEventListener(
            'change',
            function () {

                updateFinancialType(
                    taxType,
                    tax
                );

            }
        );


        tax.addEventListener(
            'input',
            updateFinancialPreview
        );


        /* =====================================================
           STATUS CHANGE
        ====================================================== */

        status.addEventListener(
            'change',
            updateStatusPreview
        );


        /* =====================================================
           INITIAL STATE
        ====================================================== */

        updateFinancialOption(
            discountEnabled,
            discount,
            discountType
        );


        updateFinancialOption(
            taxEnabled,
            tax,
            taxType
        );


        updateOpportunityPreview();

        renderQuotationItems();

        updateDatePreview();

        updateStatusPreview();

        updateFinancialPreview();

    }
);

</script>

@endsection

@push('styles')
    @include('quotations._style')
@endpush
