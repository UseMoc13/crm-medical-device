@extends('layouts.app')

@section('title', 'Create Contract')

@section('content')

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Create Contract</h1>
        <p>Create a new contract and associate it with a sales order.</p>
    </div>

    <div class="actions">
        <a href="{{ route('contracts.index') }}" class="btn">
            ← Back to Contracts
        </a>
    </div>
</div>

{{-- VALIDATION ERRORS --}}
@if ($errors->any())
    <div class="alert danger">
        <strong>Please check the following errors:</strong>

        <ul style="margin: 8px 0 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- CONTRACT FORM --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Contract Information</h3>
            <p>Fill in the contract details below.</p>
        </div>

        <span class="required-note">
            <span class="required-mark">*</span>
            Required fields
        </span>
    </div>

    <div class="card-body">

        <form
            action="{{ route('contracts.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            {{-- SECTION 1: GENERAL INFORMATION --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>General Information</h4>
                    <p>Contract identification and sales order details.</p>
                </div>

                <div class="contract-form-grid">

                    {{-- CONTRACT NUMBER --}}
                    <div class="form-group">
                        <label for="contract_number">
                            Contract Number
                        </label>

                        <div class="auto-number-field">
                            <input
                                type="text"
                                id="contract_number"
                                class="form-control"
                                value="Generated automatically by the system"
                                readonly
                            >

                            <span class="auto-number-badge">
                                Auto
                            </span>
                        </div>

                        <small class="form-hint">
                            The contract number will be generated automatically
                            when the contract is saved.
                        </small>
                    </div>

                    {{-- SALES ORDER --}}
                    <div class="form-group">
                        <label for="sales_order_id">
                            Sales Order
                            <span class="required-mark">*</span>
                        </label>

                        <select
                            name="sales_order_id"
                            id="sales_order_id"
                            class="form-control @error('sales_order_id') is-invalid @enderror"
                            required
                        >
                            <option value="">-- Select Sales Order --</option>

                            @foreach ($salesOrders as $salesOrder)
                                <option
                                    value="{{ $salesOrder->sales_order_id }}"
                                    {{ old('sales_order_id') == $salesOrder->sales_order_id ? 'selected' : '' }}
                                >
                                    {{ $salesOrder->sales_order_number ?? $salesOrder->sales_order_id }}
                                </option>
                            @endforeach
                        </select>

                        @error('sales_order_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- SECTION 2: CONTRACT PERIOD --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>Contract Period</h4>
                    <p>Specify the contract start and end dates.</p>
                </div>

                <div class="contract-form-grid">

                    {{-- START DATE --}}
                    <div class="form-group">
                        <label for="start_date">
                            Start Date
                            <span class="required-mark">*</span>
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{ old('start_date') }}"
                            required
                        >

                        @error('start_date')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- END DATE --}}
                    <div class="form-group">
                        <label for="end_date">
                            End Date
                        </label>

                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            class="form-control @error('end_date') is-invalid @enderror"
                            value="{{ old('end_date') }}"
                        >

                        @error('end_date')
                            <small class="field-error">{{ $message }}</small>
                        @enderror

                        <small class="form-hint">
                            Leave empty if the contract has no specified end date.
                        </small>
                    </div>

                </div>
            </div>

            {{-- SECTION 3: CONTRACT DETAILS --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>Contract Details</h4>
                    <p>Select the contract type and current status.</p>
                </div>

                <div class="contract-form-grid">

                    {{-- CONTRACT TYPE --}}
                    <div class="form-group">
                        <label for="contract_type">
                            Contract Type
                        </label>

                        <select
                            name="contract_type"
                            id="contract_type"
                            class="form-control @error('contract_type') is-invalid @enderror"
                        >
                            <option value="">
                                -- Select Contract Type --
                            </option>

                            <option
                                value="Sales Contract"
                                {{ old('contract_type') == 'Sales Contract' ? 'selected' : '' }}
                            >
                                Sales Contract
                            </option>

                            <option
                                value="Service Agreement"
                                {{ old('contract_type') == 'Service Agreement' ? 'selected' : '' }}
                            >
                                Service Agreement
                            </option>

                            <option
                                value="Maintenance Contract"
                                {{ old('contract_type') == 'Maintenance Contract' ? 'selected' : '' }}
                            >
                                Maintenance Contract
                            </option>

                            <option
                                value="Rental Agreement"
                                {{ old('contract_type') == 'Rental Agreement' ? 'selected' : '' }}
                            >
                                Rental Agreement
                            </option>

                            <option
                                value="Distribution Agreement"
                                {{ old('contract_type') == 'Distribution Agreement' ? 'selected' : '' }}
                            >
                                Distribution Agreement
                            </option>

                            <option
                                value="Other"
                                {{ old('contract_type') == 'Other' ? 'selected' : '' }}
                            >
                                Other
                            </option>
                        </select>

                        @error('contract_type')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- STATUS --}}
                    <div class="form-group">
                        <label for="status">
                            Status
                            <span class="required-mark">*</span>
                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-control @error('status') is-invalid @enderror"
                            required
                        >
                            <option
                                value="draft"
                                {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}
                            >
                                Draft
                            </option>

                            <option
                                value="active"
                                {{ old('status') == 'active' ? 'selected' : '' }}
                            >
                                Active
                            </option>

                            <option
                                value="expired"
                                {{ old('status') == 'expired' ? 'selected' : '' }}
                            >
                                Expired
                            </option>

                            <option
                                value="terminated"
                                {{ old('status') == 'terminated' ? 'selected' : '' }}
                            >
                                Terminated
                            </option>
                        </select>

                        @error('status')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>
            </div>

            {{-- SECTION 4: CONTRACT DOCUMENT --}}
            <div class="form-section">
                <div class="section-heading">
                    <h4>Contract Document</h4>
                    <p>Upload the contract document as a PDF file.</p>
                </div>

                <div class="form-group">
                    <label for="document">
                        Contract Document
                    </label>

                    <div class="upload-area">
                        <input
                            type="file"
                            name="document"
                            id="document"
                            class="form-control @error('document') is-invalid @enderror"
                            accept=".pdf,application/pdf"
                        >

                        <small class="form-hint">
                            Accepted format: PDF. Maximum file size: 5 MB.
                        </small>

                        @error('document')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- FORM ACTIONS --}}
            <div class="form-actions">
                <a
                    href="{{ route('contracts.index') }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    Save Contract
                </button>
            </div>

        </form>
    </div>
</div>

{{-- PAGE STYLES --}}
<style>
    /* FORM STRUCTURE */
    .contract-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-section + .form-section {
        margin-top: 30px;
        padding-top: 26px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    .section-heading {
        margin-bottom: 20px;
    }

    .section-heading h4 {
        margin: 0 0 6px;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-primary, #1e293b);
    }

    .section-heading p {
        margin: 0;
        font-size: 13px;
        color: var(--text-muted, #64748b);
    }

    /* FORM GROUPS */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
        margin-bottom: 18px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary, #334155);
    }

    .form-control {
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 7px;
        background: var(--surface, #ffffff);
        color: var(--text-primary, #1e293b);
        font-size: 13px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary, #2563eb);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .form-control[readonly] {
        background: var(--surface-secondary, #f8fafc);
        color: var(--text-muted, #64748b);
        cursor: default;
    }

    .form-control.is-invalid {
        border-color: #dc2626;
    }

    /* REQUIRED FIELDS */
    .required-mark {
        color: #dc2626;
        font-weight: 600;
    }

    .required-note {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: var(--text-muted, #64748b);
        white-space: nowrap;
    }

    /* AUTO CONTRACT NUMBER */
    .auto-number-field {
        position: relative;
    }

    .auto-number-field .form-control {
        padding-right: 65px;
    }

    .auto-number-badge {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        padding: 4px 7px;
        border-radius: 5px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* HELP TEXT */
    .form-hint {
        display: block;
        font-size: 11px;
        line-height: 1.6;
        color: var(--text-muted, #64748b);
    }

    .field-error {
        display: block;
        color: #dc2626;
        font-size: 12px;
    }

    /* DOCUMENT UPLOAD */
    .upload-area {
        padding: 16px;
        border: 1px dashed var(--border, #cbd5e1);
        border-radius: 8px;
        background: var(--surface-secondary, #f8fafc);
    }

    .upload-area .form-control {
        background: var(--surface, #ffffff);
    }

    .upload-area .form-hint {
        margin-top: 10px;
    }

    /* FORM ACTIONS */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        padding-top: 22px;
        border-top: 1px solid var(--border, #e2e8f0);
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .contract-form-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .page-head {
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-head .actions {
            width: 100%;
        }

        .required-note {
            margin-top: 8px;
        }
    }

    @media (max-width: 480px) {
        .form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .form-actions .btn {
            width: 100%;
            justify-content: center;
            text-align: center;
        }
    }
</style>

@endsection