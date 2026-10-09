@extends('layouts.app')

@section('title', 'Create Opportunity')

@section('content')

<div class="page-head">

    <div>

        <h1>Create Opportunity</h1>

        <p>
            Create a new sales opportunity and configure its customer, sales stage, value, and expected closing date.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('opportunities.index') }}"
            class="btn">
            ← Back to Opportunities
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


<div class="card opportunity-create-card">

    <div class="card-head">

        <div>

            <h3>
                Opportunity Information
            </h3>

            <p>
                Enter the opportunity information, sales details, and expected closing information.
            </p>

        </div>

    </div>


    <div class="card-body opportunity-create-body">

        <form
            method="POST"
            action="{{ route('opportunities.store') }}"
            id="opportunityCreateForm">

            @csrf


            <div class="opportunity-form-layout">


                {{-- =====================================================
                     LEFT : OPPORTUNITY FORM
                ====================================================== --}}

                <div class="opportunity-form-main">


                    {{-- =================================================
                         OPPORTUNITY INFORMATION
                    ================================================== --}}

                    <div class="section-divider first-section">

                        <div>

                            <h4>
                                Opportunity Information
                            </h4>

                            <p>
                                Enter the basic information used to identify this opportunity.
                            </p>

                        </div>

                    </div>


                    {{-- Customer --}}

                    <div class="form-group">

                        <label for="customer_id">

                            Customer

                            <span class="required">*</span>

                        </label>

                        <select
                            name="customer_id"
                            id="customer_id"
                            required>

                            <option value="">
                                Select Customer
                            </option>

                            @foreach($customers as $customer)

                            <option
                                value="{{ $customer->customer_id }}"
                                @selected(
                                old('customer_id')==$customer->customer_id
                                )
                                >

                                {{ $customer->customer_name }}

                                @if($customer->customer_code)

                                — {{ $customer->customer_code }}

                                @endif

                            </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Customer associated with this opportunity.
                        </span>

                        @error('customer_id')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- Lead --}}

                    <div class="form-group">

                        <label for="lead_id">
                            Lead
                        </label>

                        <select
                            name="lead_id"
                            id="lead_id">

                            <option value="">
                                No Lead
                            </option>

                            @foreach($leads as $lead)

                            <option
                                value="{{ $lead->lead_id }}"
                                data-customer="{{ $lead->customer_id }}"
                                data-user="{{ $lead->user_id }}"
                                @selected(
                                old('lead_id')==$lead->lead_id
                                )
                                >

                                {{ $lead->lead_code }}
                                —
                                {{ $lead->company_name }}

                                @if($lead->contact_name)

                                ({{ $lead->contact_name }})

                                @endif

                            </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Optionally link this opportunity to an existing lead.
                        </span>

                        @error('lead_id')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- Sales --}}

                    <div class="form-group">

                        <label for="user_id">

                            Sales

                            <span class="required">*</span>

                        </label>

                        <select
                            name="user_id"
                            id="user_id"
                            required>

                            <option value="">
                                Select Sales
                            </option>

                            @foreach($users as $user)

                            <option
                                value="{{ $user->user_id }}"
                                @selected(
                                old('user_id')==$user->user_id
                                )
                                >

                                {{ $user->name }}

                                @if($user->email)

                                — {{ $user->email }}

                                @endif

                            </option>

                            @endforeach

                        </select>

                        <span class="form-hint">
                            Sales representative responsible for this opportunity.
                        </span>

                        @error('user_id')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- Opportunity Name --}}

                    <div class="form-group">

                        <label for="name">

                            Opportunity Name

                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            maxlength="255"
                            value="{{ old('name') }}"
                            placeholder="Enter opportunity name"
                            required>

                        <span class="form-hint">
                            Descriptive name for this sales opportunity.
                        </span>

                        @error('name')

                        <span class="form-error">
                            {{ $message }}
                        </span>

                        @enderror

                    </div>


                    {{-- =================================================
                         SALES INFORMATION
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Sales Information
                            </h4>

                            <p>
                                Configure the current sales stage, status, and estimated opportunity value.
                            </p>

                        </div>

                    </div>


                    {{-- Stage + Status --}}

                    <div class="opportunity-two-column">


                        {{-- Stage --}}

                        <div class="form-group">

                            <label for="stage">

                                Stage

                                <span class="required">*</span>

                            </label>

                            <select
                                name="stage"
                                id="stage"
                                required>

                                <option
                                    value="Qualification"
                                    @selected(
                                    old('stage', 'Qualification' )==='Qualification'
                                    )>
                                    Qualification
                                </option>

                                <option
                                    value="Needs Analysis"
                                    @selected(
                                    old('stage')==='Needs Analysis'
                                    )>
                                    Needs Analysis
                                </option>

                                <option
                                    value="Proposal"
                                    @selected(
                                    old('stage')==='Proposal'
                                    )>
                                    Proposal
                                </option>

                                <option
                                    value="Negotiation"
                                    @selected(
                                    old('stage')==='Negotiation'
                                    )>
                                    Negotiation
                                </option>

                                <option
                                    value="Closed Won"
                                    @selected(
                                    old('stage')==='Closed Won'
                                    )>
                                    Closed Won
                                </option>

                                <option
                                    value="Closed Lost"
                                    @selected(
                                    old('stage')==='Closed Lost'
                                    )>
                                    Closed Lost
                                </option>

                            </select>

                            <span class="form-hint">
                                Current stage in the sales process.
                            </span>

                            @error('stage')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>


                        {{-- Status --}}

                        <div class="form-group">

                            <label for="status">

                                Status

                                <span class="required">*</span>

                            </label>

                            <select
                                name="status"
                                id="status"
                                required>

                                <option
                                    value="Open"
                                    @selected(
                                    old('status', 'Open' )==='Open'
                                    )>
                                    Open
                                </option>

                                <option
                                    value="Active"
                                    @selected(
                                    old('status')==='Active'
                                    )>
                                    Active
                                </option>

                                <option
                                    value="Won"
                                    @selected(
                                    old('status')==='Won'
                                    )>
                                    Won
                                </option>

                                <option
                                    value="Lost"
                                    @selected(
                                    old('status')==='Lost'
                                    )>
                                    Lost
                                </option>

                                <option
                                    value="Closed"
                                    @selected(
                                    old('status')==='Closed'
                                    )>
                                    Closed
                                </option>

                            </select>

                            <span class="form-hint">
                                Current status of this opportunity.
                            </span>

                            @error('status')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>

                    </div>


                    {{-- Estimated Value + Expected Close --}}

                    <div class="opportunity-two-column">


                        {{-- Estimated Value --}}

                        <div class="form-group">

                            <label for="estimated_value">
                                Estimated Value
                            </label>

                            <div class="currency-input">

                                <span class="currency-prefix">
                                    Rp
                                </span>

                                <button
                                    type="button"
                                    class="currency-stepper minus"
                                    id="estimatedValueMinus"
                                    aria-label="Decrease estimated value">
                                    −
                                </button>

                                <input
                                    type="text"
                                    name="estimated_value"
                                    id="estimated_value"
                                    value="{{ old('estimated_value') }}"
                                    placeholder="0"
                                    inputmode="numeric"
                                    autocomplete="off">

                                <button
                                    type="button"
                                    class="currency-stepper plus"
                                    id="estimatedValuePlus"
                                    aria-label="Increase estimated value">
                                    +
                                </button>

                            </div>

                            <span class="form-hint">
                                Estimated monetary value of this opportunity.
                            </span>

                            @error('estimated_value')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>


                        {{-- Expected Close --}}

                        <div class="form-group">

                            <label for="expected_close_date">
                                Expected Close Date
                            </label>

                            <input
                                type="date"
                                name="expected_close_date"
                                id="expected_close_date"
                                value="{{ old('expected_close_date') }}">

                            <span class="form-hint">
                                Expected date when the opportunity will be closed.
                            </span>

                            @error('expected_close_date')

                            <span class="form-error">
                                {{ $message }}
                            </span>

                            @enderror

                        </div>

                    </div>


                    {{-- =================================================
                         DESCRIPTION
                    ================================================== --}}

                    <div class="section-divider">

                        <div>

                            <h4>
                                Additional Information
                            </h4>

                            <p>
                                Add additional notes or information about this opportunity.
                            </p>

                        </div>

                    </div>


                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            name="description"
                            id="description"
                            rows="5"
                            placeholder="Enter additional information about this opportunity...">{{ old('description') }}</textarea>

                        <span class="form-hint">
                            Optional notes, requirements, or additional opportunity information.
                        </span>

                        @error('description')

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
                            href="{{ route('opportunities.index') }}"
                            class="btn">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary">
                            Create Opportunity
                        </button>

                    </div>


                </div>


                {{-- =====================================================
                     RIGHT : OPPORTUNITY PREVIEW
                ====================================================== --}}

                <aside class="opportunity-preview-panel">


                    <div class="preview-label">
                        OPPORTUNITY PREVIEW
                    </div>


                    {{-- Preview Header --}}

                    <div class="preview-opportunity-head">

                        <div class="preview-opportunity-icon">
                            O
                        </div>

                        <div class="preview-opportunity-main">

                            <h3 id="previewOpportunityName">
                                New Opportunity
                            </h3>

                            <span id="previewOpportunityCustomer">
                                No Customer Selected
                            </span>

                        </div>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- Sales --}}

                    <div class="preview-detail">

                        <span>
                            Sales
                        </span>

                        <strong id="previewSales">
                            No Sales
                        </strong>

                    </div>


                    {{-- Lead --}}

                    <div class="preview-detail">

                        <span>
                            Lead
                        </span>

                        <strong id="previewLead">
                            No Lead
                        </strong>

                    </div>


                    {{-- Stage --}}

                    <div class="preview-detail">

                        <span>
                            Stage
                        </span>

                        <strong
                            id="previewStage"
                            class="preview-stage qualification">
                            Qualification
                        </strong>

                    </div>


                    {{-- Status --}}

                    <div class="preview-detail">

                        <span>
                            Status
                        </span>

                        <strong
                            id="previewStatus"
                            class="preview-status open">
                            Open
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- Estimated Value --}}

                    <div class="preview-financial-row">

                        <span>
                            Estimated Value
                        </span>

                        <strong id="previewEstimatedValue">
                            Rp 0
                        </strong>

                    </div>


                    {{-- Expected Close --}}

                    <div class="preview-financial-row">

                        <span>
                            Expected Close
                        </span>

                        <strong id="previewExpectedClose">
                            No Date
                        </strong>

                    </div>


                    <div class="preview-divider"></div>


                    {{-- Description --}}

                    <div class="preview-description">

                        <div class="preview-description-title">
                            Description
                        </div>

                        <p id="previewDescription">
                            No description provided.
                        </p>

                    </div>


                    {{-- Information Note --}}

                    <div class="preview-note">

                        <strong>
                            Opportunity Information
                        </strong>

                        <p>
                            This preview updates automatically as you enter opportunity information. The opportunity can be edited later from the opportunity detail page.
                        </p>

                    </div>


                </aside>


            </div>

        </form>

    </div>

</div>


<style>
    /* =========================================================
   CREATE LAYOUT
========================================================= */

    .opportunity-create-card,
    .opportunity-create-body,
    .opportunity-form-layout,
    .opportunity-form-main {
        overflow: visible !important;
    }

    .opportunity-form-layout {

        display: grid;

        grid-template-columns:
            minmax(0, 1.25fr) minmax(320px, .75fr);

        gap: 32px;

        align-items: start;

        width: 100%;

    }

    .opportunity-form-main {
        min-width: 0;
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


    /* =========================================================
   FORM GROUP
========================================================= */

    .form-group {
        margin-bottom: 19px;
    }

    .form-group label {

        display: block;

        margin-bottom: 7px;

        color: #17284f;

        font-size: 13px;

        font-weight: 600;

    }

    .required {
        color: #c94a4a;
    }


    /* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

    .form-group input,
    .form-group select,
    .form-group textarea {

        width: 100%;

        box-sizing: border-box;

        border: 1px solid #d9dee8;

        border-radius: 8px;

        background: #fff;

        color: #17284f;

        font-family: inherit;

        font-size: 13px;

        outline: none;

        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;

    }

    .form-group input,
    .form-group select {

        height: 42px;

        padding: 0 12px;

    }

    .form-group textarea {

        min-height: 120px;

        padding: 11px 12px;

        line-height: 1.6;

        resize: vertical;

    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);

    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #a0a8b6;
    }


    /* =========================================================
   HINT / ERROR
========================================================= */

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

        line-height: 1.5;

    }


    /* =========================================================
   TWO COLUMN
========================================================= */

    .opportunity-two-column {

        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 16px;

    }


    /* =========================================================
   CURRENCY INPUT
========================================================= */

    .currency-input {

        position: relative;

        width: 100%;

        height: 42px;

        box-sizing: border-box;

    }

    .currency-input input {

        width: 100%;

        height: 42px;

        box-sizing: border-box;

        padding: 0 76px 0 35px;

        border: 1px solid #d9dee8;

        border-radius: 8px;

        background: #fff;

        color: #17284f;

        font-family: inherit;

        font-size: 13px;

        font-weight: 500;

        text-align: left;

        outline: none;

        transition:
            border-color .18s ease,
            box-shadow .18s ease;

    }

    .currency-input input:focus {

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);

    }

    .currency-prefix {

        position: absolute;

        left: 12px;

        top: 50%;

        transform: translateY(-50%);

        color: #718096;

        font-size: 11px;

        font-weight: 600;

        pointer-events: none;

        z-index: 2;

    }

    .currency-stepper {

        position: absolute;

        top: 50%;

        transform: translateY(-50%);

        width: 25px;

        height: 25px;

        padding: 0;

        border: 1px solid #dfe4eb;

        border-radius: 6px;

        background: #fff;

        color: #17284f;

        font-family: inherit;

        font-size: 16px;

        font-weight: 500;

        line-height: 23px;

        text-align: center;

        cursor: pointer;

        z-index: 3;

        transition:
            background .18s ease,
            border-color .18s ease,
            color .18s ease;

    }

    .currency-stepper.minus {
        right: 40px;
    }

    .currency-stepper.plus {
        right: 10px;
    }

    .currency-stepper:hover {

        background: #f5f7fb;

        border-color: #2ba7a0;

        color: #2ba7a0;

    }

    .currency-stepper:active {

        transform:
            translateY(-50%) scale(.96);

    }


    /* =========================================================
   FORM ACTIONS
========================================================= */

    .form-actions {

        display: flex;

        justify-content: flex-end;

        align-items: center;

        gap: 8px;

        padding-top: 16px;

        margin-top: 8px;

        border-top: 1px solid #edf0f5;

    }


    /* =========================================================
   PREVIEW PANEL
========================================================= */

    .opportunity-preview-panel {

        position: -webkit-sticky;

        position: sticky;

        top: 92px;

        align-self: start;

        height: fit-content;

        padding: 24px;

        border: 1px solid #e6eaf0;

        border-radius: 12px;

        background: #fafbfd;

        box-sizing: border-box;

        min-width: 0;

        z-index: 5;

    }


    /* =========================================================
   PREVIEW LABEL
========================================================= */

    .preview-label {

        margin-bottom: 20px;

        color: #8a94a6;

        font-size: 10px;

        font-weight: 700;

        letter-spacing: 1.2px;

    }


    /* =========================================================
   PREVIEW HEADER
========================================================= */

    .preview-opportunity-head {

        display: flex;

        align-items: center;

        gap: 13px;

    }

    .preview-opportunity-icon {

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

    .preview-opportunity-main {

        min-width: 0;

    }

    .preview-opportunity-main h3 {

        margin: 0 0 4px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        color: #17284f;

        font-size: 17px;

        font-weight: 700;

    }

    .preview-opportunity-main>span {

        display: block;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        color: #7d8797;

        font-size: 11px;

    }


    /* =========================================================
   PREVIEW DIVIDER
========================================================= */

    .preview-divider {

        height: 1px;

        margin: 21px 0;

        background: #e5e9ef;

    }


    /* =========================================================
   PREVIEW DETAIL
========================================================= */

    .preview-detail {

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        gap: 20px;

        padding: 9px 0;

    }

    .preview-detail>span {

        color: #8a94a6;

        font-size: 11px;

    }

    .preview-detail>strong {

        max-width: 190px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;

        color: #34415c;

        font-size: 12px;

        font-weight: 600;

        text-align: right;

    }


    /* =========================================================
   PREVIEW STAGE
========================================================= */

    .preview-stage {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        max-width: 150px;

        padding: 4px 8px;

        border-radius: 6px;

        font-size: 10px !important;

        font-weight: 700 !important;

    }

    .preview-stage.qualification {

        background: #eef2ff;

        color: #5264a6 !important;

    }

    .preview-stage.needs-analysis {

        background: #f1f5f9;

        color: #52606d !important;

    }

    .preview-stage.proposal {

        background: #eaf7f3;

        color: #167d70 !important;

    }

    .preview-stage.negotiation {

        background: #fff6df;

        color: #9a7515 !important;

    }

    .preview-stage.closed-won {

        background: #eaf7f3;

        color: #167d70 !important;

    }

    .preview-stage.closed-lost {

        background: #fceeee;

        color: #b34b4b !important;

    }


    /* =========================================================
   PREVIEW STATUS
========================================================= */

    .preview-status {

        display: inline-flex;

        align-items: center;

        justify-content: center;

        padding: 4px 8px;

        border-radius: 6px;

        font-size: 10px !important;

        font-weight: 700 !important;

    }

    .preview-status.open,
    .preview-status.active,
    .preview-status.won {

        background: #eaf7f3;

        color: #167d70 !important;

    }

    .preview-status.lost,
    .preview-status.closed {

        background: #fceeee;

        color: #b34b4b !important;

    }


    /* =========================================================
   PREVIEW FINANCIAL
========================================================= */

    .preview-financial-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        padding: 9px 0;

    }

    .preview-financial-row span {

        color: #8a94a6;

        font-size: 11px;

    }

    .preview-financial-row strong {

        color: #17284f;

        font-size: 13px;

        font-weight: 700;

        text-align: right;

    }


    /* =========================================================
   PREVIEW DESCRIPTION
========================================================= */

    .preview-description {

        margin-bottom: 20px;

    }

    .preview-description-title {

        margin-bottom: 8px;

        color: #34415c;

        font-size: 11px;

        font-weight: 700;

    }

    .preview-description p {

        margin: 0;

        color: #8a94a6;

        font-size: 10px;

        line-height: 1.6;

        white-space: pre-line;

        word-break: break-word;

    }


    /* =========================================================
   PREVIEW NOTE
========================================================= */

    .preview-note {

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

        .opportunity-form-layout {

            grid-template-columns: 1fr;

        }

        .opportunity-preview-panel {

            position: static;

            order: -1;

        }

    }


    @media (max-width: 600px) {

        .opportunity-two-column {

            grid-template-columns: 1fr;

        }

        .form-actions {

            flex-direction: column-reverse;

            align-items: stretch;

        }

        .form-actions .btn {

            width: 100%;

            justify-content: center;

        }

    }


    @media (max-width: 450px) {

        .opportunity-preview-panel {

            padding: 20px;

        }

        .preview-opportunity-head {

            align-items: flex-start;

        }

        .preview-detail {

            flex-direction: column;

            gap: 4px;

        }

        .preview-detail>strong {

            max-width: 100%;

            text-align: left;

        }

        .preview-financial-row {

            align-items: flex-start;

            flex-direction: column;

            gap: 4px;

        }

        .preview-financial-row strong {

            text-align: left;

        }

    }
</style>


<script>
    document.addEventListener(
        'DOMContentLoaded',
        function() {


            /* =====================================================
               FORM ELEMENTS
            ====================================================== */

            const customer =
                document.getElementById('customer_id');

            const lead =
                document.getElementById('lead_id');

            const sales =
                document.getElementById('user_id');

            const opportunityName =
                document.getElementById('name');

            const stage =
                document.getElementById('stage');

            const status =
                document.getElementById('status');

            const estimatedValue =
                document.getElementById('estimated_value');

            const expectedClose =
                document.getElementById('expected_close_date');

            const description =
                document.getElementById('description');

            const minusButton =
                document.getElementById('estimatedValueMinus');

            const plusButton =
                document.getElementById('estimatedValuePlus');


            /* =====================================================
               PREVIEW ELEMENTS
            ====================================================== */

            const previewOpportunityName =
                document.getElementById(
                    'previewOpportunityName'
                );

            const previewOpportunityCustomer =
                document.getElementById(
                    'previewOpportunityCustomer'
                );

            const previewSales =
                document.getElementById(
                    'previewSales'
                );

            const previewLead =
                document.getElementById(
                    'previewLead'
                );

            const previewStage =
                document.getElementById(
                    'previewStage'
                );

            const previewStatus =
                document.getElementById(
                    'previewStatus'
                );

            const previewEstimatedValue =
                document.getElementById(
                    'previewEstimatedValue'
                );

            const previewExpectedClose =
                document.getElementById(
                    'previewExpectedClose'
                );

            const previewDescription =
                document.getElementById(
                    'previewDescription'
                );


            /* =====================================================
               FORMAT CURRENCY
            ====================================================== */

            function formatCurrency(value) {

                const numericValue =
                    String(value ?? '')
                    .replace(/\D/g, '');

                if (!numericValue) {

                    return 'Rp 0';

                }

                return 'Rp ' +
                    Number(numericValue)
                    .toLocaleString('id-ID');

            }


            /* =====================================================
               GET SELECTED TEXT
            ====================================================== */

            function getSelectedText(selectElement) {

                if (
                    !selectElement ||
                    !selectElement.value
                ) {

                    return '';

                }

                return selectElement.options[
                    selectElement.selectedIndex
                ].text.trim();

            }


            /* =====================================================
               GET STAGE CLASS
            ====================================================== */

            function getStageClass(value) {

                return String(value)
                    .toLowerCase()
                    .replace(/\s+/g, '-');

            }


            /* =====================================================
               GET STATUS CLASS
            ====================================================== */

            function getStatusClass(value) {

                return String(value)
                    .toLowerCase()
                    .replace(/\s+/g, '-');

            }


            /* =====================================================
               FORMAT DATE
            ====================================================== */

            function formatDate(value) {

                if (!value) {

                    return 'No Date';

                }

                const date =
                    new Date(value + 'T00:00:00');

                if (Number.isNaN(date.getTime())) {

                    return 'No Date';

                }

                return date.toLocaleDateString(
                    'en-GB', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }
                );

            }


            /* =====================================================
               GET NUMERIC VALUE
            ====================================================== */

            function getEstimatedNumericValue() {

                const value =
                    String(
                        estimatedValue.value || ''
                    );

                return Number(
                    value.replace(/\D/g, '')
                ) || 0;

            }


            /* =====================================================
               FORMAT ESTIMATED VALUE
            ====================================================== */

            function formatEstimatedInput(value) {

                const numericValue =
                    String(value ?? '')
                    .replace(/\D/g, '');

                if (!numericValue) {

                    return '';

                }

                return Number(numericValue)
                    .toLocaleString('id-ID');

            }


            /* =====================================================
               UPDATE PREVIEW
            ====================================================== */

            function updatePreview() {


                /* Opportunity Name */

                previewOpportunityName.textContent =
                    opportunityName.value.trim() ||
                    'New Opportunity';


                /* Customer */

                const customerText =
                    getSelectedText(customer);

                previewOpportunityCustomer.textContent =
                    customerText ||
                    'No Customer Selected';


                /* Sales */

                const salesText =
                    getSelectedText(sales);

                previewSales.textContent =
                    salesText ||
                    'No Sales';


                /* Lead */

                const leadText =
                    getSelectedText(lead);

                previewLead.textContent =
                    leadText ||
                    'No Lead';


                /* Stage */

                const stageText =
                    getSelectedText(stage);

                previewStage.textContent =
                    stageText ||
                    'Qualification';

                previewStage.className =
                    'preview-stage ' +
                    getStageClass(
                        stage.value ||
                        'Qualification'
                    );


                /* Status */

                const statusText =
                    getSelectedText(status);

                previewStatus.textContent =
                    statusText ||
                    'Open';

                previewStatus.className =
                    'preview-status ' +
                    getStatusClass(
                        status.value ||
                        'Open'
                    );


                /* Estimated Value */

                previewEstimatedValue.textContent =
                    formatCurrency(
                        estimatedValue.value
                    );


                /* Expected Close */

                previewExpectedClose.textContent =
                    formatDate(
                        expectedClose.value
                    );


                /* Description */

                previewDescription.textContent =
                    description.value.trim() ||
                    'No description provided.';

            }


            /* =====================================================
               FORMAT INITIAL ESTIMATED VALUE
            ====================================================== */

            if (estimatedValue.value) {

                estimatedValue.value =
                    formatEstimatedInput(
                        estimatedValue.value
                    );

            }


            /* =====================================================
               ESTIMATED VALUE INPUT
            ====================================================== */

            estimatedValue.addEventListener(
                'input',
                function() {

                    estimatedValue.value =
                        formatEstimatedInput(
                            estimatedValue.value
                        );

                    updatePreview();

                }
            );


            /* =====================================================
               PLUS
            ====================================================== */

            plusButton.addEventListener(
                'click',
                function() {

                    const currentValue =
                        getEstimatedNumericValue();

                    const newValue =
                        currentValue + 1000;

                    estimatedValue.value =
                        formatEstimatedInput(
                            newValue
                        );

                    estimatedValue.focus();

                    updatePreview();

                }
            );


            /* =====================================================
               MINUS
            ====================================================== */

            minusButton.addEventListener(
                'click',
                function() {

                    const currentValue =
                        getEstimatedNumericValue();

                    const newValue =
                        Math.max(
                            0,
                            currentValue - 1000
                        );

                    estimatedValue.value =
                        formatEstimatedInput(
                            newValue
                        );

                    estimatedValue.focus();

                    updatePreview();

                }
            );


            /* =====================================================
               FORM SUBMIT
            ====================================================== */

            const form =
                document.getElementById(
                    'opportunityCreateForm'
                );

            form.addEventListener(
                'submit',
                function() {

                    estimatedValue.value =
                        getEstimatedNumericValue() || '';

                }
            );


            /* =====================================================
               PREVIEW LISTENERS
            ====================================================== */

            const previewFields = [

                customer,
                lead,
                sales,
                opportunityName,
                stage,
                status,
                estimatedValue,
                expectedClose,
                description

            ];


            previewFields.forEach(
                function(element) {

                    element.addEventListener(
                        'input',
                        updatePreview
                    );

                    element.addEventListener(
                        'change',
                        updatePreview
                    );

                }
            );


            /* =====================================================
               INITIAL PREVIEW
            ====================================================== */

            updatePreview();

        }
    );
</script>

@endsection