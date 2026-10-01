@extends('layouts.app')

@section('title', 'Edit Opportunity')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Opportunity</h1>

        <p>
            Update information and sales details for this opportunity.
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

        <strong>Please check the following errors:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

    </div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>Opportunity Information</h3>

            <p>
                Update the information below.
            </p>

        </div>

        <div class="opportunity-code">
            {{ $opportunity->opportunity_code }}
        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('opportunities.update', $opportunity) }}"
        >

            @csrf

            @method('PUT')


            <div class="form-grid">


                {{-- =================================================
                     CUSTOMER
                ================================================== --}}

                <div class="form-group">

                    <label for="customer_id">
                        Customer <span>*</span>
                    </label>

                    <select
                        name="customer_id"
                        id="customer_id"
                        required
                    >

                        <option value="">
                            Select Customer
                        </option>

                        @foreach($customers as $customer)

                            <option
                                value="{{ $customer->customer_id }}"
                                @selected(
                                    old(
                                        'customer_id',
                                        $opportunity->customer_id
                                    ) == $customer->customer_id
                                )
                            >

                                {{ $customer->customer_name }}

                                @if($customer->customer_code)
                                    — {{ $customer->customer_code }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                    <small>
                        Customer associated with this opportunity.
                    </small>

                </div>


                {{-- =================================================
                     LEAD
                ================================================== --}}

                <div class="form-group">

                    <label for="lead_id">
                        Lead
                    </label>

                    <select
                        name="lead_id"
                        id="lead_id"
                    >

                        <option value="">
                            No Lead
                        </option>

                        @foreach($leads as $lead)

                            <option
                                value="{{ $lead->lead_id }}"
                                data-customer="{{ $lead->customer_id }}"
                                data-user="{{ $lead->user_id }}"
                                @selected(
                                    old(
                                        'lead_id',
                                        $opportunity->lead_id
                                    ) == $lead->lead_id
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

                    <small>
                        Optionally link this opportunity to a lead.
                    </small>

                </div>


                {{-- =================================================
                     SALES
                ================================================== --}}

                <div class="form-group">

                    <label for="user_id">
                        Sales <span>*</span>
                    </label>

                    <select
                        name="user_id"
                        id="user_id"
                        required
                    >

                        <option value="">
                            Select Sales
                        </option>

                        @foreach($users as $user)

                            <option
                                value="{{ $user->user_id }}"
                                @selected(
                                    old(
                                        'user_id',
                                        $opportunity->user_id
                                    ) == $user->user_id
                                )
                            >

                                {{ $user->name }}

                                @if($user->email)
                                    — {{ $user->email }}
                                @endif

                            </option>

                        @endforeach

                    </select>

                    <small>
                        Sales representative responsible for this opportunity.
                    </small>

                </div>


                {{-- =================================================
                     OPPORTUNITY NAME
                ================================================== --}}

                <div class="form-group">

                    <label for="name">
                        Opportunity Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        maxlength="255"
                        value="{{ old('name', $opportunity->name) }}"
                        placeholder="Enter opportunity name"
                        required
                    >

                </div>


                {{-- =================================================
                     STAGE
                ================================================== --}}

                <div class="form-group">

                    <label for="stage">
                        Stage <span>*</span>
                    </label>

                    @php
                        $currentStage = old(
                            'stage',
                            $opportunity->stage
                        );
                    @endphp

                    <select
                        name="stage"
                        id="stage"
                        required
                    >

                        <option
                            value="Qualification"
                            @selected($currentStage === 'Qualification')
                        >
                            Qualification
                        </option>

                        <option
                            value="Needs Analysis"
                            @selected($currentStage === 'Needs Analysis')
                        >
                            Needs Analysis
                        </option>

                        <option
                            value="Proposal"
                            @selected($currentStage === 'Proposal')
                        >
                            Proposal
                        </option>

                        <option
                            value="Negotiation"
                            @selected($currentStage === 'Negotiation')
                        >
                            Negotiation
                        </option>

                        <option
                            value="Closed Won"
                            @selected($currentStage === 'Closed Won')
                        >
                            Closed Won
                        </option>

                        <option
                            value="Closed Lost"
                            @selected($currentStage === 'Closed Lost')
                        >
                            Closed Lost
                        </option>

                    </select>

                </div>


                {{-- =================================================
                     STATUS
                ================================================== --}}

                <div class="form-group">

                    <label for="status">
                        Status <span>*</span>
                    </label>

                    @php
                        $currentStatus = old(
                            'status',
                            $opportunity->status
                        );
                    @endphp

                    <select
                        name="status"
                        id="status"
                        required
                    >

                        <option
                            value="Open"
                            @selected($currentStatus === 'Open')
                        >
                            Open
                        </option>

                        <option
                            value="Active"
                            @selected($currentStatus === 'Active')
                        >
                            Active
                        </option>

                        <option
                            value="Won"
                            @selected($currentStatus === 'Won')
                        >
                            Won
                        </option>

                        <option
                            value="Lost"
                            @selected($currentStatus === 'Lost')
                        >
                            Lost
                        </option>

                        <option
                            value="Closed"
                            @selected($currentStatus === 'Closed')
                        >
                            Closed
                        </option>

                    </select>

                </div>


                {{-- =================================================
                     ESTIMATED VALUE
                ================================================== --}}

                <div class="form-group">

                    <label for="estimated_value">
                        Estimated Value
                    </label>

                    <div class="input-prefix">

                        <span>Rp</span>

                        <input
                            type="number"
                            name="estimated_value"
                            id="estimated_value"
                            min="0"
                            step="0.01"
                            value="{{ old(
                                'estimated_value',
                                $opportunity->estimated_value
                            ) }}"
                            placeholder="0"
                        >

                    </div>

                </div>


                {{-- =================================================
                     EXPECTED CLOSE
                ================================================== --}}

                <div class="form-group">

                    <label for="expected_close_date">
                        Expected Close Date
                    </label>

                    <input
                        type="date"
                        name="expected_close_date"
                        id="expected_close_date"
                        value="{{ old(
                            'expected_close_date',
                            optional(
                                $opportunity->expected_close_date
                            )->format('Y-m-d')
                        ) }}"
                    >

                </div>


                {{-- =================================================
                     DESCRIPTION
                ================================================== --}}

                <div class="form-group description-group">

                    <label for="description">
                        Description
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="5"
                        placeholder="Enter additional information about this opportunity"
                    >{{ old('description', $opportunity->description) }}</textarea>

                </div>

            </div>


            {{-- =================================================
                 FORM ACTIONS
            ================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('opportunities.show', $opportunity) }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn primary"
                >
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   OPPORTUNITY CODE
========================================================= */

.opportunity-code {
    color: #2ba7a0;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: .05em;

    white-space: nowrap;
}


/* =========================================================
   FORM GRID
========================================================= */

.form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    column-gap: 20px;

    row-gap: 2px;
}


/* =========================================================
   FORM GROUP
========================================================= */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    color: #17284f;

    font-size: 11px;
    font-weight: 700;
}

.form-group label span {
    color: #d9534f;
}


/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;

    box-sizing: border-box;

    border: 1px solid #dfe4eb;

    border-radius: 8px;

    background: #ffffff;

    color: #17284f;

    font-family: inherit;

    font-size: 12px;

    outline: none;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.form-group input,
.form-group select {
    height: 40px;

    padding: 0 12px;
}

.form-group textarea {
    min-height: 110px;

    padding: 11px 12px;

    resize: vertical;

    line-height: 1.6;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .10);
}

.form-group small {
    display: block;

    margin-top: 5px;

    color: #8a94a6;

    font-size: 10px;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.description-group {
    grid-column: 1 / -1;
}

.description-group textarea {
    min-height: 120px;
}


/* =========================================================
   CURRENCY INPUT
========================================================= */

.input-prefix {
    position: relative;
}

.input-prefix span {
    position: absolute;

    left: 12px;

    top: 50%;

    transform: translateY(-50%);

    color: #8a94a6;

    font-size: 11px;

    pointer-events: none;
}

.input-prefix input {
    padding-left: 34px;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 8px;

    padding-top: 20px;

    border-top: 1px solid #edf0f4;
}


/* =========================================================
   ERROR ALERT
========================================================= */

.alert.error ul {
    margin: 8px 0 0;

    padding-left: 18px;
}

.alert.error li {
    margin-bottom: 3px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .form-grid {
        grid-template-columns: 1fr;
    }

    .description-group {
        grid-column: auto;
    }

}


@media (max-width: 600px) {

    .card-head {
        align-items: flex-start;

        flex-direction: column;
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

@endsection