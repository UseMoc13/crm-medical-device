@extends('layouts.app')

@section('title', 'Edit Warehouse')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Warehouse</h1>
        <p>
            Update warehouse information, location, and operational status.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('warehouses.index') }}" class="btn">
            ← Back to Warehouses
        </a>
    </div>
</div>


{{-- VALIDATION ERRORS --}}

@if ($errors->any())
<div class="alert error warehouse-error-alert">
    <strong>Please check the following errors:</strong>

    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif


<div class="card warehouse-edit-card">

    <div class="card-head">
        <div>
            <h3>Warehouse Information</h3>
            <p>
                Review and update the warehouse details and operational settings.
            </p>
        </div>
    </div>

    <div class="card-body warehouse-edit-body">

        <form
            method="POST"
            action="{{ route('warehouses.update', $warehouse->warehouse_id) }}"
            id="warehouseEditForm">

            @csrf
            @method('PUT')

            <div class="warehouse-form-layout">

                {{-- LEFT: WAREHOUSE FORM --}}

                <div class="warehouse-form-main">

                    {{-- WAREHOUSE INFORMATION --}}

                    <div class="section-divider first-section">
                        <div>
                            <h4>Warehouse Information</h4>
                            <p>
                                Update the information used to identify this warehouse.
                            </p>
                        </div>
                    </div>


                    {{-- WAREHOUSE CODE --}}

                    <div class="form-group">
                        <label for="warehouse_code">
                            Warehouse Code
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="warehouse_code"
                            id="warehouse_code"
                            maxlength="30"
                            value="{{ old('warehouse_code', $warehouse->warehouse_code) }}"
                            placeholder="Example: WH-JKT-001"
                            autocomplete="off"
                            class="@error('warehouse_code') is-invalid @enderror"
                            required>

                        <span class="form-hint">
                            A unique code used to identify the warehouse.
                        </span>

                        @error('warehouse_code')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>


                    {{-- WAREHOUSE NAME --}}

                    <div class="form-group">
                        <label for="warehouse_name">
                            Warehouse Name
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="warehouse_name"
                            id="warehouse_name"
                            maxlength="100"
                            value="{{ old('warehouse_name', $warehouse->warehouse_name) }}"
                            placeholder="Enter warehouse name"
                            class="@error('warehouse_name') is-invalid @enderror"
                            required>

                        <span class="form-hint">
                            The official name of the warehouse or storage facility.
                        </span>

                        @error('warehouse_name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>


                    {{-- WAREHOUSE ADDRESS --}}

                    <div class="form-group">
                        <label for="address">
                            Warehouse Address
                        </label>

                        <textarea
                            name="address"
                            id="address"
                            rows="5"
                            placeholder="Enter the complete warehouse address..."
                            class="@error('address') is-invalid @enderror">{{ old('address', $warehouse->address) }}</textarea>

                        <span class="form-hint">
                            Include the street, city, and other relevant location details.
                        </span>

                        @error('address')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>


                    {{-- WAREHOUSE SETTINGS --}}

                    <div class="section-divider">
                        <div>
                            <h4>Warehouse Settings</h4>
                            <p>
                                Configure the operational status of this warehouse.
                            </p>
                        </div>
                    </div>


                    {{-- WAREHOUSE STATUS --}}

                    <div class="form-group">
                        <label for="status">
                            Warehouse Status
                            <span class="required">*</span>
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="@error('status') is-invalid @enderror"
                            required>

                            <option
                                value="Active"
                                @selected(old('status', $warehouse->status) === 'Active')>
                                Active
                            </option>

                            <option
                                value="Inactive"
                                @selected(old('status', $warehouse->status) === 'Inactive')>
                                Inactive
                            </option>
                        </select>

                        <span class="form-hint">
                            Active warehouses are available for operational use.
                            Inactive warehouses are marked as unavailable for new operations.
                        </span>

                        @error('status')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>


                    {{-- FORM ACTIONS --}}

                    <div class="warehouse-form-actions">

                        <a
                            href="{{ route('warehouses.index') }}"
                            class="btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            id="warehouseSubmitButton">
                            Update Warehouse
                        </button>

                    </div>

                </div>


                {{-- RIGHT: WAREHOUSE PREVIEW --}}

                <aside class="warehouse-preview-panel">

                    <div class="warehouse-preview-label">
                        WAREHOUSE PREVIEW
                    </div>


                    {{-- PREVIEW HEADER --}}

                    <div class="warehouse-preview-header">

                        <div class="warehouse-preview-icon">
                            W
                        </div>

                        <div class="warehouse-preview-main">
                            <h3 id="previewWarehouseName">
                                {{ old('warehouse_name', $warehouse->warehouse_name) ?: 'Warehouse Name' }}
                            </h3>

                            <span id="previewWarehouseCode">
                                {{ old('warehouse_code', $warehouse->warehouse_code) ?: 'No Code Assigned' }}
                            </span>
                        </div>

                    </div>


                    <div class="warehouse-preview-divider"></div>


                    {{-- STATUS --}}

                    <div class="warehouse-preview-detail">
                        <span>Operational Status</span>

                        <strong
                            id="previewWarehouseStatus"
                            class="warehouse-status-badge {{ old('status', $warehouse->status) === 'Inactive' ? 'inactive' : 'active' }}">

                            <span class="status-dot"></span>
                            <span id="previewStatusText">
                                {{ old('status', $warehouse->status) }}
                            </span>
                        </strong>
                    </div>


                    <div class="warehouse-preview-divider"></div>


                    {{-- ADDRESS --}}

                    <div class="warehouse-preview-address">

                        <div class="warehouse-preview-section-title">
                            Warehouse Location
                        </div>

                        <div class="warehouse-address-content">

                            <span class="warehouse-address-icon">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="17"
                                    height="17"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true">

                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                            </span>

                            <p id="previewWarehouseAddress">{{ old('address', $warehouse->address) ?: 'No address provided.' }}</p>

                        </div>

                    </div>


                    <div class="warehouse-preview-divider"></div>


                    {{-- SUMMARY --}}

                    <div class="warehouse-preview-summary">

                        <div class="warehouse-preview-section-title">
                            Warehouse Summary
                        </div>

                        <div class="warehouse-summary-item">
                            <span>Warehouse Code</span>
                            <strong id="previewSummaryCode">
                                {{ old('warehouse_code', $warehouse->warehouse_code) ?: '—' }}
                            </strong>
                        </div>

                        <div class="warehouse-summary-item">
                            <span>Warehouse Name</span>
                            <strong id="previewSummaryName">
                                {{ old('warehouse_name', $warehouse->warehouse_name) ?: '—' }}
                            </strong>
                        </div>

                        <div class="warehouse-summary-item">
                            <span>Address Status</span>
                            <strong id="previewAddressStatus">
                                {{ trim((string) old('address', $warehouse->address)) !== '' ? 'Provided' : 'Not Provided' }}
                            </strong>
                        </div>

                    </div>


                    {{-- INFORMATION NOTE --}}

                    <div class="warehouse-preview-note">

                        <div class="warehouse-note-icon">i</div>

                        <div>
                            <strong>Before You Update</strong>
                            <p>
                                Review the warehouse information before saving.
                                Make sure the warehouse code remains unique.
                            </p>
                        </div>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>


<style>
    /* =========================================================
       EDIT LAYOUT
    ========================================================= */

    .warehouse-edit-card,
    .warehouse-edit-body,
    .warehouse-form-layout,
    .warehouse-form-main {
        overflow: visible !important;
    }

    .warehouse-edit-card {
        width: 100%;
        box-sizing: border-box;
    }

    .warehouse-edit-card > .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 16px;
        font-weight: 700;
    }

    .warehouse-edit-card > .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.6;
    }

    .warehouse-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
        gap: 32px;
        align-items: start;
        width: 100%;
    }

    .warehouse-form-main {
        min-width: 0;
    }

    .warehouse-error-alert {
        margin-bottom: 20px;
    }

    .warehouse-error-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .warehouse-error-alert li {
        margin-top: 4px;
    }


    /* =========================================================
       SECTION DIVIDER
    ========================================================= */

    .warehouse-form-main .section-divider {
        display: flex;
        align-items: center;
        margin: 25px 0 18px;
        padding-top: 20px;
        border-top: 1px solid #edf0f5;
    }

    .warehouse-form-main .section-divider.first-section {
        margin-top: 0;
        padding-top: 0;
        border-top: 0;
    }

    .warehouse-form-main .section-divider h4 {
        margin: 0 0 4px;
        color: #17284f;
        font-size: 13px;
        font-weight: 700;
    }

    .warehouse-form-main .section-divider p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
       FORM GROUP
    ========================================================= */

    .warehouse-form-main .form-group {
        margin-bottom: 19px;
        min-width: 0;
    }

    .warehouse-form-main .form-group label {
        display: block;
        margin-bottom: 7px;
        color: #17284f;
        font-size: 13px;
        font-weight: 600;
    }

    .warehouse-form-main .required {
        color: #c94a4a;
    }


    /* =========================================================
       INPUT / SELECT / TEXTAREA
    ========================================================= */

    .warehouse-form-main .form-group input,
    .warehouse-form-main .form-group select,
    .warehouse-form-main .form-group textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #d9dee8;
        border-radius: 8px;
        background: #fff;
        color: #17284f;
        font-family: inherit;
        font-size: 13px;
        outline: none;
        transition: border-color .18s ease, box-shadow .18s ease;
    }

    .warehouse-form-main .form-group input,
    .warehouse-form-main .form-group select {
        height: 42px;
        padding: 0 12px;
    }

    .warehouse-form-main .form-group textarea {
        min-height: 120px;
        padding: 11px 12px;
        line-height: 1.6;
        resize: vertical;
    }

    .warehouse-form-main .form-group input:focus,
    .warehouse-form-main .form-group select:focus,
    .warehouse-form-main .form-group textarea:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .09);
    }

    .warehouse-form-main .form-group input::placeholder,
    .warehouse-form-main .form-group textarea::placeholder {
        color: #a0a8b6;
    }

    .warehouse-form-main .form-group .is-invalid {
        border-color: #c94a4a;
    }


    /* =========================================================
       HINT / ERROR
    ========================================================= */

    .warehouse-form-main .form-hint {
        display: block;
        margin-top: 6px;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6;
    }

    .warehouse-form-main .form-error {
        display: block;
        margin-top: 6px;
        color: #c94a4a;
        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
       FORM ACTIONS
    ========================================================= */

    .warehouse-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        padding-top: 17px;
        margin-top: 8px;
        border-top: 1px solid #edf0f5;
    }

    .warehouse-form-actions .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        box-sizing: border-box;
        text-decoration: none;
        cursor: pointer;
    }

    .warehouse-form-actions button:disabled {
        cursor: wait;
        opacity: .7;
    }


    /* =========================================================
       PREVIEW PANEL
    ========================================================= */

    .warehouse-preview-panel {
        position: sticky;
        top: 92px;
        align-self: start;
        min-width: 0;
        height: fit-content;
        padding: 24px;
        border: 1px solid #e6eaf0;
        border-radius: 12px;
        background: #fafbfd;
        box-sizing: border-box;
        z-index: 2;
    }

    .warehouse-preview-label {
        margin-bottom: 20px;
        color: #8a94a6;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1.2px;
    }


    /* =========================================================
       PREVIEW HEADER
    ========================================================= */

    .warehouse-preview-header {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .warehouse-preview-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 12px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 17px;
        font-weight: 700;
    }

    .warehouse-preview-main {
        min-width: 0;
        flex: 1;
    }

    .warehouse-preview-main h3 {
        margin: 0 0 5px;
        overflow-wrap: anywhere;
        color: #17284f;
        font-size: 16px;
        font-weight: 700;
        line-height: 1.4;
    }

    .warehouse-preview-main > span {
        display: block;
        overflow-wrap: anywhere;
        color: #7d8797;
        font-size: 11px;
        line-height: 1.5;
    }


    /* =========================================================
       PREVIEW DIVIDER
    ========================================================= */

    .warehouse-preview-divider {
        height: 1px;
        margin: 21px 0;
        background: #e5e9ef;
    }


    /* =========================================================
       STATUS
    ========================================================= */

    .warehouse-preview-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .warehouse-preview-detail > span {
        color: #8a94a6;
        font-size: 11px;
    }

    .warehouse-status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 9px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .warehouse-status-badge.active {
        background: #eaf7f3;
        color: #167d70;
    }

    .warehouse-status-badge.inactive {
        background: #fceeee;
        color: #b34b4b;
    }

    .warehouse-status-badge .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }


    /* =========================================================
       ADDRESS
    ========================================================= */

    .warehouse-preview-section-title {
        margin-bottom: 13px;
        color: #34415c;
        font-size: 11px;
        font-weight: 700;
    }

    .warehouse-address-content {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .warehouse-address-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #eef2ff;
        color: #5264a6;
    }

    .warehouse-address-content p {
        margin: 3px 0 0;
        color: #7d8797;
        font-size: 11px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }


    /* =========================================================
       SUMMARY
    ========================================================= */

    .warehouse-summary-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        padding: 10px 0;
        border-bottom: 1px solid #edf0f5;
    }

    .warehouse-summary-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .warehouse-summary-item > span {
        flex-shrink: 0;
        color: #8a94a6;
        font-size: 11px;
    }

    .warehouse-summary-item > strong {
        min-width: 0;
        max-width: 60%;
        color: #34415c;
        font-size: 11px;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }


    /* =========================================================
       INFORMATION NOTE
    ========================================================= */

    .warehouse-preview-note {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 23px;
        padding: 13px 14px;
        border-radius: 8px;
        background: #f1f5f7;
    }

    .warehouse-note-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #dce8ec;
        color: #526a78;
        font-size: 11px;
        font-weight: 700;
    }

    .warehouse-preview-note > div:last-child {
        min-width: 0;
    }

    .warehouse-preview-note strong {
        display: block;
        margin-bottom: 5px;
        color: #34415c;
        font-size: 11px;
    }

    .warehouse-preview-note p {
        margin: 0;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.7;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 1000px) {
        .warehouse-form-layout {
            grid-template-columns: minmax(0, 1fr);
            gap: 28px;
        }

        .warehouse-preview-panel {
            position: static;
            order: -1;
        }
    }

    @media (max-width: 600px) {
        .warehouse-preview-panel {
            padding: 20px;
        }

        .warehouse-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .warehouse-form-actions .btn {
            width: 100%;
        }

        .warehouse-preview-detail {
            align-items: flex-start;
            flex-direction: column;
            gap: 8px;
        }
    }
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('warehouseEditForm');

    if (!form) {
        return;
    }

    const warehouseCode = document.getElementById('warehouse_code');
    const warehouseName = document.getElementById('warehouse_name');
    const warehouseAddress = document.getElementById('address');
    const warehouseStatus = document.getElementById('status');

    const previewName = document.getElementById('previewWarehouseName');
    const previewCode = document.getElementById('previewWarehouseCode');
    const previewStatus = document.getElementById('previewWarehouseStatus');
    const previewStatusText = document.getElementById('previewStatusText');
    const previewAddress = document.getElementById('previewWarehouseAddress');

    const summaryCode = document.getElementById('previewSummaryCode');
    const summaryName = document.getElementById('previewSummaryName');
    const summaryAddressStatus = document.getElementById('previewAddressStatus');

    function updateWarehousePreview() {

        const code = warehouseCode.value.trim();
        const name = warehouseName.value.trim();
        const address = warehouseAddress.value.trim();
        const status = warehouseStatus.value;

        // Warehouse name
        previewName.textContent = name || 'Warehouse Name';
        summaryName.textContent = name || '—';

        // Warehouse code
        previewCode.textContent = code || 'No Code Assigned';
        summaryCode.textContent = code || '—';

        // Warehouse status
        previewStatus.classList.remove('active', 'inactive');
        previewStatus.classList.add(status === 'Inactive' ? 'inactive' : 'active');
        previewStatusText.textContent = status || 'Active';

        // Warehouse address
        previewAddress.textContent = address || 'No address provided.';

        summaryAddressStatus.textContent = address
            ? 'Provided'
            : 'Not Provided';
    }

    [
        warehouseCode,
        warehouseName,
        warehouseAddress,
        warehouseStatus
    ].forEach(function (field) {
        field.addEventListener('input', updateWarehousePreview);
        field.addEventListener('change', updateWarehousePreview);
    });

    // Prevent accidental double submission
    form.addEventListener('submit', function (event) {

        if (!form.checkValidity()) {
            return;
        }

        const submitButton = document.getElementById('warehouseSubmitButton');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Updating Warehouse...';
        }
    });

    // Initialize preview with the existing warehouse data
    updateWarehousePreview();

});
</script>

@endsection