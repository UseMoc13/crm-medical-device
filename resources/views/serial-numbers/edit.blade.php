@extends('layouts.app')

@section('title', 'Edit Serial Number')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Serial Number</h1>
        <p>Update device identification, assignment, and lifecycle status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('serial-numbers.index') }}" class="btn">
            ← Back to Serial Numbers
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

@if($products->isEmpty())
    <div class="alert error">
        No products are available. Create a product before updating this serial number.
    </div>
@endif

@if(empty($statuses))
    <div class="alert error">
        No serial number statuses are configured.
    </div>
@endif

<div class="card serial-edit-card">
    <div class="card-head">
        <div>
            <h3>Serial Number Information</h3>
            <p>Review and update the registered device information.</p>
        </div>
    </div>

    <div class="card-body serial-edit-body">
        <form
            method="POST"
            action="{{ route('serial-numbers.update', $serialNumber->serial_number_id) }}"
            id="serialEditForm"
        >
            @csrf
            @method('PUT')

            <div class="serial-form-layout">
                <div class="serial-form-main">

                    <div class="section-divider first-section">
                        <div>
                            <h4>Device Information</h4>
                            <p>Update the product identification and serial number.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="serial_number">
                            Serial Number <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="serial_number"
                            name="serial_number"
                            value="{{ old('serial_number', $serialNumber->serial_number) }}"
                            placeholder="e.g. SN-MD-2026-0001"
                            maxlength="255"
                            autocomplete="off"
                            required
                        >

                        <span class="form-hint">
                            Enter the unique serial number printed on the device.
                        </span>

                        @error('serial_number')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="product_id">
                            Product <span class="required">*</span>
                        </label>

                        <select id="product_id" name="product_id" required>
                            <option value="">Select a product</option>

                            @foreach($products as $product)
                                <option
                                    value="{{ $product->product_id }}"
                                    data-name="{{ $product->product_name }}"
                                    data-code="{{ $product->product_code }}"
                                    @selected(
                                        old('product_id', $serialNumber->product_id) == $product->product_id
                                    )
                                >
                                    {{ $product->product_code }} — {{ $product->product_name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Select the product associated with this serial number.
                        </span>

                        @error('product_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="section-divider">
                        <div>
                            <h4>Assignment Details</h4>
                            <p>Manage the customer assignment and device status.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer_id">Customer</label>

                        <select id="customer_id" name="customer_id">
                            <option value="">No customer assigned</option>

                            @foreach($customers as $customer)
                                <option
                                    value="{{ $customer->customer_id }}"
                                    data-name="{{ $customer->customer_name }}"
                                    @selected(
                                        old('customer_id', $serialNumber->customer_id) == $customer->customer_id
                                    )
                                >
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Leave empty if the device has not been assigned to a customer.
                        </span>

                        @error('customer_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">
                            Status <span class="required">*</span>
                        </label>

                        <select id="status" name="status" required>
                            <option value="">Select a status</option>

                            @foreach($statuses as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(
                                        old('status', $serialNumber->status) === $status
                                    )
                                >
                                    {{ $status }}
                                </option>
                            @endforeach

                            @if(
                                !in_array($serialNumber->status, $statuses, true)
                                && !old('status')
                            )
                                <option value="{{ $serialNumber->status }}" selected>
                                    {{ $serialNumber->status }} (Current)
                                </option>
                            @endif
                        </select>

                        <span class="form-hint">
                            Update the lifecycle status according to the device's actual condition.
                        </span>

                        @error('status')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>

                        <input
                            type="date"
                            id="purchase_date"
                            name="purchase_date"
                            value="{{ old('purchase_date', $serialNumber->purchase_date) }}"
                        >

                        <span class="form-hint">
                            Optional. Leave empty if the purchase date is unknown.
                        </span>

                        @error('purchase_date')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('serial-numbers.index') }}" class="btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            @disabled($products->isEmpty() || empty($statuses))
                        >
                            Update Serial Number
                        </button>
                    </div>
                </div>

                <aside class="serial-preview-panel">
                    <div class="preview-label">SERIAL NUMBER PREVIEW</div>

                    <div class="preview-device-icon">SN</div>

                    <div class="preview-serial-number" id="previewSerial">
                        {{ $serialNumber->serial_number }}
                    </div>

                    <div class="preview-caption">Device Serial Number</div>

                    <div class="preview-divider"></div>

                    <div class="preview-item">
                        <span>Product Code</span>
                        <strong id="previewProductCode">Not selected</strong>
                    </div>

                    <div class="preview-item">
                        <span>Product Name</span>
                        <strong id="previewProductName">Not selected</strong>
                    </div>

                    <div class="preview-item">
                        <span>Customer</span>
                        <strong id="previewCustomer">Not assigned</strong>
                    </div>

                    <div class="preview-item">
                        <span>Status</span>
                        <strong class="preview-status" id="previewStatus">
                            {{ $serialNumber->status }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Purchase Date</span>
                        <strong id="previewPurchaseDate">
                            {{ $serialNumber->purchase_date ?: 'Not specified' }}
                        </strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-note">
                        <strong>Device Record</strong>
                        <p>
                            Changes will update the existing serial number record.
                            Verify the physical device information before saving.
                        </p>
                    </div>
                </aside>
            </div>
        </form>
    </div>
</div>

<style>
.serial-edit-card,
.serial-edit-body,
.serial-form-layout,
.serial-form-main {
    overflow: visible !important;
}

.serial-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
    gap: 32px;
    align-items: start;
    width: 100%;
}

.serial-form-main {
    min-width: 0;
}

.section-divider {
    margin: 25px 0 18px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.section-divider h4 {
    margin: 0 0 4px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.section-divider p {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

.serial-form-main .form-group {
    margin-bottom: 19px;
}

.serial-form-main .form-group label {
    display: block;
    margin-bottom: 7px;
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.serial-form-main .required {
    color: #c94a4a;
}

.serial-form-main .form-group input,
.serial-form-main .form-group select {
    display: block;
    width: 100%;
    height: 42px;
    box-sizing: border-box;
    padding: 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.serial-form-main .form-group input:focus,
.serial-form-main .form-group select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.serial-form-main .form-group input::placeholder {
    color: #a0a8b6;
}

.serial-form-main .form-hint,
.serial-form-main .form-error {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    line-height: 1.5;
}

.serial-form-main .form-hint {
    color: #8a94a6;
}

.serial-form-main .form-error {
    color: #c94a4a;
}

.serial-form-main .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #edf0f5;
}

.serial-preview-panel {
    position: sticky;
    top: 92px;
    align-self: start;
    min-width: 0;
    height: fit-content;
    padding: 24px;
    box-sizing: border-box;
    border: 1px solid #e6eaf0;
    border-radius: 12px;
    background: #fafbfd;
    z-index: 5;
}

.serial-preview-panel .preview-label {
    margin-bottom: 20px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.serial-preview-panel .preview-device-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    margin-bottom: 16px;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 16px;
    font-weight: 700;
}

.serial-preview-panel .preview-serial-number {
    overflow-wrap: anywhere;
    color: #17284f;
    font-size: 17px;
    font-weight: 700;
}

.serial-preview-panel .preview-caption {
    margin-top: 5px;
    color: #7d8797;
    font-size: 11px;
}

.serial-preview-panel .preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e5e9ef;
}

.serial-preview-panel .preview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 17px;
}

.serial-preview-panel .preview-item span {
    color: #8a94a6;
    font-size: 11px;
}

.serial-preview-panel .preview-item strong {
    overflow-wrap: anywhere;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.serial-preview-panel .preview-item .preview-status {
    display: inline-block;
    width: fit-content;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 11px;
}

.serial-preview-panel .preview-note {
    padding: 13px 14px;
    border-radius: 8px;
    background: #f1f5f7;
}

.serial-preview-panel .preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #34415c;
    font-size: 11px;
}

.serial-preview-panel .preview-note p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}

@media (max-width: 900px) {
    .serial-form-layout {
        grid-template-columns: 1fr;
    }

    .serial-preview-panel {
        position: static;
        order: -1;
    }
}

@media (max-width: 600px) {
    .serial-form-main .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .serial-form-main .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const serialInput = document.getElementById('serial_number');
    const productSelect = document.getElementById('product_id');
    const customerSelect = document.getElementById('customer_id');
    const statusSelect = document.getElementById('status');
    const purchaseDateInput = document.getElementById('purchase_date');

    const previewSerial = document.getElementById('previewSerial');
    const previewProductCode = document.getElementById('previewProductCode');
    const previewProductName = document.getElementById('previewProductName');
    const previewCustomer = document.getElementById('previewCustomer');
    const previewStatus = document.getElementById('previewStatus');
    const previewPurchaseDate = document.getElementById('previewPurchaseDate');

    function updatePreview() {
        const productOption = productSelect.selectedOptions[0];
        const customerOption = customerSelect.selectedOptions[0];
        const statusOption = statusSelect.selectedOptions[0];

        previewSerial.textContent =
            serialInput.value.trim() || 'Not assigned';

        previewProductCode.textContent =
            productOption?.dataset.code || 'Not selected';

        previewProductName.textContent =
            productOption?.dataset.name || 'Not selected';

        previewCustomer.textContent =
            customerOption?.dataset.name || 'Not assigned';

        previewStatus.textContent =
            statusOption && statusOption.value
                ? statusOption.textContent.trim()
                : 'Not specified';

        previewPurchaseDate.textContent =
            purchaseDateInput.value || 'Not specified';
    }

    [
        serialInput,
        productSelect,
        customerSelect,
        statusSelect,
        purchaseDateInput
    ].forEach(function (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    updatePreview();
});
</script>

@endsection
