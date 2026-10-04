@extends('layouts.app')

@section('title', 'Edit Opportunity Item')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Opportunity Item</h1>

        <p>
            Update the product, quantity, estimated price, or notes
            for this opportunity item.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('opportunities.show', $opportunity) }}"
            class="btn"
        >
            ← Back
        </a>

    </div>

</div>


@if($errors->any())

    <div class="alert error">

        <strong>Please check the following:</strong>

        <ul>

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


{{-- =========================================================
     OPPORTUNITY INFORMATION
========================================================= --}}

<div class="card opportunity-info">

    <div class="card-head">

        <div>

            <h3>Opportunity</h3>

            <p>
                Opportunity associated with this item.
            </p>

        </div>

    </div>


    <div class="card-body">

        <div class="opportunity-info-grid">

            <div>

                <span class="info-label">
                    Opportunity Code
                </span>

                <span class="info-value highlight">
                    {{ $opportunity->opportunity_code }}
                </span>

            </div>


            <div>

                <span class="info-label">
                    Opportunity Name
                </span>

                <span class="info-value">
                    {{ $opportunity->name }}
                </span>

            </div>


            <div>

                <span class="info-label">
                    Customer
                </span>

                <span class="info-value">
                    {{ $opportunity->customer->customer_name ?? '-' }}
                </span>

            </div>


            <div>

                <span class="info-label">
                    Stage
                </span>

                <span class="info-value">
                    {{ $opportunity->stage }}
                </span>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     EDIT FORM
========================================================= --}}

<form
    method="POST"
    action="{{ route(
        'opportunities.items.update',
        [$opportunity, $item]
    ) }}"
>

    @csrf

    @method('PUT')


    <div class="card">

        <div class="card-head">

            <div>

                <h3>Item Information</h3>

                <p>
                    Update the information for this opportunity item.
                </p>

            </div>

        </div>


        <div class="card-body">

            <div class="form-grid">


                {{-- =================================================
                     PRODUCT
                ================================================== --}}

                <div class="form-group full">

                    <label for="product_id">

                        Product

                        <span class="required">
                            *
                        </span>

                    </label>


                    <select
                        name="product_id"
                        id="product_id"
                        required
                    >

                        <option value="">
                            Select Product
                        </option>


                        @foreach($products as $product)

                            <option
                                value="{{ $product->product_id }}"
                                data-price="{{ $product->price }}"
                                data-type="{{ $product->product_type }}"
                                data-unit="{{ $product->unit }}"
                                {{ old(
                                    'product_id',
                                    $item->product_id
                                ) == $product->product_id
                                    ? 'selected'
                                    : ''
                                }}
                            >

                                {{ $product->product_code }}
                                —
                                {{ $product->product_name }}

                                @if($product->status !== 'active')

                                    (Inactive)

                                @endif

                            </option>

                        @endforeach

                    </select>


                    @if($products->isEmpty())

                        <small class="form-hint warning">

                            No products are available.

                        </small>

                    @else

                        <small class="form-hint">

                            Select the product that will be included
                            in this opportunity.

                        </small>

                    @endif

                </div>


                {{-- =================================================
                     PRODUCT INFORMATION
                ================================================== --}}

                <div
                    class="product-preview full"
                    id="productPreview"
                    style="display: none;"
                >

                    <div>

                        <span class="preview-label">
                            Product Type
                        </span>

                        <span
                            class="preview-value"
                            id="productType"
                        >
                            -
                        </span>

                    </div>


                    <div>

                        <span class="preview-label">
                            Unit
                        </span>

                        <span
                            class="preview-value"
                            id="productUnit"
                        >
                            -
                        </span>

                    </div>


                    <div>

                        <span class="preview-label">
                            Master Price
                        </span>

                        <span
                            class="preview-value"
                            id="productPrice"
                        >
                            -
                        </span>

                    </div>

                </div>


                {{-- =================================================
                     QUANTITY
                ================================================== --}}

                <div class="form-group">

                    <label for="quantity">

                        Quantity

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        value="{{ old(
                            'quantity',
                            $item->quantity
                        ) }}"
                        min="1"
                        required
                    >


                    <small class="form-hint">

                        Number of units for this product.

                    </small>

                </div>


                {{-- =================================================
                     ESTIMATED PRICE
                ================================================== --}}

                <div class="form-group">

                    <label for="estimated_price">
                        Estimated Price
                    </label>


                    <input
                        type="number"
                        name="estimated_price"
                        id="estimated_price"
                        value="{{ old(
                            'estimated_price',
                            $item->estimated_price
                        ) }}"
                        min="0"
                        step="0.01"
                        placeholder="0"
                    >


                    <small class="form-hint">

                        Estimated selling price per unit.

                    </small>

                </div>


                {{-- =================================================
                     NOTES
                ================================================== --}}

                <div class="form-group full">

                    <label for="notes">
                        Notes
                    </label>


                    <textarea
                        name="notes"
                        id="notes"
                        rows="4"
                        placeholder="Additional notes about this item..."
                    >{{ old(
                        'notes',
                        $item->notes
                    ) }}</textarea>


                    <small class="form-hint">

                        Optional notes for this opportunity item.

                    </small>

                </div>

            </div>

        </div>


        {{-- =================================================
             FORM FOOTER
        ================================================== --}}

        <div class="form-footer">

            <a
                href="{{ route(
                    'opportunities.show',
                    $opportunity
                ) }}"
                class="btn"
            >
                Cancel
            </a>


            <button
                type="submit"
                class="btn primary"
            >
                Update Item
            </button>

        </div>

    </div>

</form>


{{-- =========================================================
     STYLES
========================================================= --}}

<style>

/* =========================================================
   OPPORTUNITY INFORMATION
========================================================= */

.opportunity-info {
    margin-bottom: 20px;
}

.opportunity-info-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 20px;
}

.opportunity-info-grid > div {
    display: flex;

    flex-direction: column;

    gap: 5px;
}

.info-label {
    color: #8a94a6;

    font-size: 10px;
}

.info-value {
    color: #17284f;

    font-size: 12px;
    font-weight: 600;
}

.info-value.highlight {
    color: #2ba7a0;
}


/* =========================================================
   FORM
========================================================= */

.form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px 20px;
}

.form-group {
    display: flex;

    flex-direction: column;

    gap: 7px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    color: #17284f;

    font-size: 11px;
    font-weight: 700;
}

.required {
    color: #d35c5c;
}


/* =========================================================
   INPUT
========================================================= */

.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    box-sizing: border-box;

    padding: 9px 11px;

    border: 1px solid #dfe4ec;

    border-radius: 8px;

    background: #ffffff;

    color: #17284f;

    font-family: inherit;

    font-size: 12px;

    outline: none;

    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}

.form-group input,
.form-group select {
    min-height: 38px;
}

.form-group textarea {

    resize: vertical;

    min-height: 100px;

    line-height: 1.5;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   HINT
========================================================= */

.form-hint {

    color: #8a94a6;

    font-size: 10px;

    line-height: 1.5;
}

.form-hint.warning {
    color: #bd7b35;
}


/* =========================================================
   PRODUCT PREVIEW
========================================================= */

.product-preview {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 15px;

    padding: 13px 15px;

    border: 1px solid #e3e9ef;

    border-radius: 8px;

    background: #f8fafc;
}

.product-preview > div {

    display: flex;

    flex-direction: column;

    gap: 4px;
}

.preview-label {

    color: #8a94a6;

    font-size: 10px;
}

.preview-value {

    color: #17284f;

    font-size: 12px;

    font-weight: 600;
}


/* =========================================================
   FORM FOOTER
========================================================= */

.form-footer {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

    padding: 15px 20px;

    border-top: 1px solid #edf0f4;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .opportunity-info-grid {

        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


@media (max-width: 700px) {

    .form-grid {

        grid-template-columns: 1fr;
    }

    .form-group.full {

        grid-column: auto;
    }

    .product-preview {

        grid-template-columns: 1fr;
    }

}


@media (max-width: 500px) {

    .opportunity-info-grid {

        grid-template-columns: 1fr;
    }

    .form-footer {

        flex-direction: column-reverse;

        align-items: stretch;
    }

    .form-footer .btn {

        width: 100%;
    }

}

</style>


{{-- =========================================================
     PRODUCT PREVIEW SCRIPT
========================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const productSelect =
            document.getElementById(
                'product_id'
            );

        const estimatedPrice =
            document.getElementById(
                'estimated_price'
            );

        const productPreview =
            document.getElementById(
                'productPreview'
            );

        const productType =
            document.getElementById(
                'productType'
            );

        const productUnit =
            document.getElementById(
                'productUnit'
            );

        const productPrice =
            document.getElementById(
                'productPrice'
            );


        function updateProductInfo() {

            const selectedOption =
                productSelect.options[
                    productSelect.selectedIndex
                ];


            if (
                !selectedOption ||
                !selectedOption.value
            ) {

                productPreview.style.display =
                    'none';

                productType.textContent =
                    '-';

                productUnit.textContent =
                    '-';

                productPrice.textContent =
                    '-';

                return;
            }


            const price =
                selectedOption.dataset.price || '';

            const type =
                selectedOption.dataset.type || '-';

            const unit =
                selectedOption.dataset.unit || '-';


            productType.textContent =
                type;

            productUnit.textContent =
                unit;


            if (price !== '') {

                const numericPrice =
                    Number(price);

                productPrice.textContent =
                    'Rp ' +
                    new Intl.NumberFormat(
                        'id-ID'
                    ).format(numericPrice);

            } else {

                productPrice.textContent =
                    '-';

            }


            productPreview.style.display =
                'grid';
        }


        productSelect.addEventListener(
            'change',
            updateProductInfo
        );


        updateProductInfo();

    }
);

</script>

@endsection