@extends('layouts.app')

@section('title', 'Create Opportunity')

@section('content')

<div class="page-head">

    <div>

        <h1>New Opportunity</h1>

        <p>
            Create a new sales opportunity for a potential business deal.
        </p>

    </div>


    <div class="actions">

        <a
            href="{{ route('opportunities.index') }}"
            class="btn"
        >
            ← Back to Opportunities
        </a>

    </div>

</div>


@if($errors->any())

<div class="alert error">

    <strong>Please fix the following errors:</strong>

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
                Fill in the information below to create a new opportunity.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            action="{{ route('opportunities.store') }}"
            method="POST"
            id="opportunityCreateForm"
        >

            @csrf


            {{-- =====================================================
                 CUSTOMER & LEAD
            ====================================================== --}}

            <div class="form-section">

                <div class="form-section-title">

                    <h4>Customer & Lead</h4>

                    <p>
                        Link this opportunity to an existing customer or lead.
                    </p>

                </div>


                <div class="form-grid">


                    {{-- Customer --}}

                    <div class="form-group">

                        <label>
                            Customer
                            <span class="required">*</span>
                        </label>


                        <div
                            class="searchable-select"
                            data-name="customer_id"
                        >

                            <input
                                type="hidden"
                                name="customer_id"
                                id="customer_id"
                                value="{{ old('customer_id') }}"
                            >


                            <button
                                type="button"
                                class="searchable-select-trigger"
                            >

                                <span
                                    class="searchable-select-value"
                                >
                                    Select Customer
                                </span>


                                <span
                                    class="searchable-select-arrow"
                                >
                                    ▾
                                </span>

                            </button>


                            <div class="searchable-select-menu">

                                <input
                                    type="text"
                                    class="searchable-select-search"
                                    placeholder="Search customer..."
                                    autocomplete="off"
                                >


                                <div
                                    class="searchable-select-options"
                                ></div>

                            </div>

                        </div>


                        @error('customer_id')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Lead --}}

                    <div class="form-group">

                        <label>
                            Lead
                        </label>


                        <div
                            class="searchable-select"
                            data-name="lead_id"
                        >

                            <input
                                type="hidden"
                                name="lead_id"
                                id="lead_id"
                                value="{{ old('lead_id') }}"
                            >


                            <button
                                type="button"
                                class="searchable-select-trigger"
                            >

                                <span
                                    class="searchable-select-value"
                                >
                                    Select Lead
                                </span>


                                <span
                                    class="searchable-select-arrow"
                                >
                                    ▾
                                </span>

                            </button>


                            <div class="searchable-select-menu">

                                <input
                                    type="text"
                                    class="searchable-select-search"
                                    placeholder="Search lead..."
                                    autocomplete="off"
                                >


                                <div
                                    class="searchable-select-options"
                                ></div>

                            </div>

                        </div>


                        @error('lead_id')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 OPPORTUNITY INFORMATION
            ====================================================== --}}

            <div class="form-section">

                <div class="form-section-title">

                    <h4>Opportunity Details</h4>

                    <p>
                        Define the main information about this opportunity.
                    </p>

                </div>


                <div class="form-grid">


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
                            value="{{ old('name') }}"
                            placeholder="e.g. Hospital Equipment Procurement"
                            required
                        >


                        @error('name')

                            <span class="field-error">
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
                            required
                        >

                            <option value="">
                                Select Sales
                            </option>


                            @foreach($users as $user)

                                <option
                                    value="{{ $user->user_id }}"
                                    @selected(
                                        old('user_id') ===
                                        $user->user_id
                                    )
                                >

                                    {{ $user->name }}

                                    @if($user->email)
                                        — {{ $user->email }}
                                    @endif

                                </option>

                            @endforeach

                        </select>


                        @if($users->isEmpty())

                            <span class="field-help warning">
                                No active sales user is currently available.
                            </span>

                        @endif


                        @error('user_id')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Stage --}}

                    <div class="form-group">

                        <label for="stage">

                            Stage
                            <span class="required">*</span>

                        </label>


                        <select
                            name="stage"
                            id="stage"
                            required
                        >

                            <option value="">
                                Select Stage
                            </option>


                            <option
                                value="Lead"
                                @selected(old('stage') === 'Lead')
                            >
                                Lead
                            </option>


                            <option
                                value="Qualified"
                                @selected(old('stage') === 'Qualified')
                            >
                                Qualified
                            </option>


                            <option
                                value="Proposal"
                                @selected(old('stage') === 'Proposal')
                            >
                                Proposal
                            </option>


                            <option
                                value="Negotiation"
                                @selected(old('stage') === 'Negotiation')
                            >
                                Negotiation
                            </option>


                            <option
                                value="Closed"
                                @selected(old('stage') === 'Closed')
                            >
                                Closed
                            </option>

                        </select>


                        @error('stage')

                            <span class="field-error">
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
                            required
                        >

                            <option value="">
                                Select Status
                            </option>


                            <option
                                value="Open"
                                @selected(old('status') === 'Open')
                            >
                                Open
                            </option>


                            <option
                                value="Active"
                                @selected(old('status') === 'Active')
                            >
                                Active
                            </option>


                            <option
                                value="Won"
                                @selected(old('status') === 'Won')
                            >
                                Won
                            </option>


                            <option
                                value="Lost"
                                @selected(old('status') === 'Lost')
                            >
                                Lost
                            </option>


                            <option
                                value="Closed"
                                @selected(old('status') === 'Closed')
                            >
                                Closed
                            </option>

                        </select>


                        @error('status')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Estimated Value --}}

                    <div class="form-group">

                        <label for="estimated_value">
                            Estimated Value
                        </label>


                        <div class="input-prefix">

                            <span>
                                Rp
                            </span>


                            <input
                                type="number"
                                name="estimated_value"
                                id="estimated_value"
                                value="{{ old('estimated_value') }}"
                                placeholder="0"
                                min="0"
                                step="0.01"
                            >

                        </div>


                        <span class="field-help">
                            Estimated value of the potential deal.
                        </span>


                        @error('estimated_value')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>


                    {{-- Expected Close Date --}}

                    <div class="form-group">

                        <label for="expected_close_date">
                            Expected Close Date
                        </label>


                        <input
                            type="date"
                            name="expected_close_date"
                            id="expected_close_date"
                            value="{{ old('expected_close_date') }}"
                        >


                        <span class="field-help">
                            Target date for closing the opportunity.
                        </span>


                        @error('expected_close_date')

                            <span class="field-error">
                                {{ $message }}
                            </span>

                        @enderror

                    </div>

                </div>


                {{-- Description --}}

                <div class="form-group full-width">

                    <label for="description">
                        Description
                    </label>


                    <textarea
                        name="description"
                        id="description"
                        rows="5"
                        placeholder="Describe the opportunity, customer needs, project requirements, or other relevant information..."
                    >{{ old('description') }}</textarea>


                    @error('description')

                        <span class="field-error">
                            {{ $message }}
                        </span>

                    @enderror

                </div>

            </div>


            {{-- =====================================================
                 FORM ACTIONS
            ====================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('opportunities.index') }}"
                    class="btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn primary"
                >
                    Create Opportunity
                </button>

            </div>

        </form>

    </div>

</div>


<style>

/* =========================================================
   FORM SECTION
========================================================= */

.form-section {
    padding-bottom: 26px;
    margin-bottom: 26px;

    border-bottom: 1px solid #edf0f4;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section-title {
    margin-bottom: 20px;
}

.form-section-title h4 {
    margin: 0 0 5px;

    color: #17284f;

    font-size: 15px;
    font-weight: 700;
}

.form-section-title p {
    margin: 0;

    color: #8a94a6;

    font-size: 12px;
}


/* =========================================================
   FORM GRID
========================================================= */

.form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;
}


/* =========================================================
   FORM GROUP
========================================================= */

.form-group {
    min-width: 0;
}

.form-group.full-width {
    margin-top: 20px;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    color: #34415c;

    font-size: 12px;
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
    padding: 11px 12px;

    resize: vertical;

    line-height: 1.6;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
    color: #a3abb8;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   INPUT PREFIX
========================================================= */

.input-prefix {
    position: relative;
}

.input-prefix > span {
    position: absolute;

    left: 12px;
    top: 50%;

    transform: translateY(-50%);

    color: #7d8797;

    font-size: 12px;
    font-weight: 600;

    pointer-events: none;
}

.input-prefix input {
    padding-left: 35px;
}


/* =========================================================
   HELP / ERROR
========================================================= */

.field-help {
    display: block;

    margin-top: 6px;

    color: #8a94a6;

    font-size: 11px;
}

.field-help.warning {
    color: #a66b12;
}

.field-error {
    display: block;

    margin-top: 6px;

    color: #c94a4a;

    font-size: 11px;
}


/* =========================================================
   SEARCHABLE SELECT
========================================================= */

.searchable-select {
    position: relative;
}

.searchable-select-trigger {
    width: 100%;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 12px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;
    font-size: 13px;

    cursor: pointer;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.searchable-select-trigger:hover {
    border-color: #b7c0cf;
}

.searchable-select.open
.searchable-select-trigger {
    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}

.searchable-select-value {
    overflow: hidden;

    text-overflow: ellipsis;

    white-space: nowrap;
}

.searchable-select-arrow {
    margin-left: 10px;

    color: #7d8797;

    font-size: 12px;
}

.searchable-select-menu {
    position: absolute;

    top: calc(100% + 6px);
    left: 0;
    right: 0;

    z-index: 100;

    display: none;

    padding: 8px;

    border: 1px solid #d9dee8;
    border-radius: 9px;

    background: #fff;

    box-shadow:
        0 12px 35px rgba(23, 40, 79, .14);
}

.searchable-select.open
.searchable-select-menu {
    display: block;
}

.searchable-select-search {
    width: 100%;

    height: 36px;

    box-sizing: border-box;

    margin-bottom: 7px;

    padding: 0 10px;

    border: 1px solid #d9dee8;
    border-radius: 7px;

    color: #17284f;

    font-family: inherit;
    font-size: 12px;
}

.searchable-select-search:focus {
    outline: none;

    border-color: #2ba7a0;
}

.searchable-select-options {
    max-height: 220px;

    overflow-y: auto;
}

.searchable-select-option {
    padding: 9px 10px;

    border-radius: 6px;

    cursor: pointer;

    color: #34415c;

    font-size: 12px;

    line-height: 1.4;
}

.searchable-select-option:hover {
    background: #f1f5f7;
    color: #17284f;
}

.searchable-select-option strong {
    display: block;

    color: #17284f;
}

.searchable-select-option span {
    display: block;

    margin-top: 2px;

    color: #8a94a6;

    font-size: 11px;
}

.searchable-select-empty {
    padding: 12px 10px;

    color: #8a94a6;

    font-size: 12px;

    text-align: center;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 28px;

    padding-top: 22px;

    border-top: 1px solid #edf0f4;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .form-grid {
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

/* =========================================================
   DATA
========================================================= */

const customers = @json($customers);

const leads = @json($leads);


/* =========================================================
   SEARCHABLE SELECT
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const searchableSelects =
            document.querySelectorAll(
                '.searchable-select'
            );


        searchableSelects.forEach(
            function (element) {

                const fieldName =
                    element.dataset.name;


                const hiddenInput =
                    element.querySelector(
                        'input[type="hidden"]'
                    );


                const trigger =
                    element.querySelector(
                        '.searchable-select-trigger'
                    );


                const valueDisplay =
                    element.querySelector(
                        '.searchable-select-value'
                    );


                const searchInput =
                    element.querySelector(
                        '.searchable-select-search'
                    );


                const optionsContainer =
                    element.querySelector(
                        '.searchable-select-options'
                    );


                let data = [];


                if (
                    fieldName ===
                    'customer_id'
                ) {

                    data = customers;

                }


                if (
                    fieldName ===
                    'lead_id'
                ) {

                    data = leads;

                }


                function renderOptions(
                    searchTerm = ''
                ) {

                    optionsContainer.innerHTML =
                        '';


                    const keyword =
                        searchTerm
                            .toLowerCase()
                            .trim();


                    const filtered =
                        data.filter(
                            function (item) {

                                if (!keyword) {
                                    return true;
                                }


                                if (
                                    fieldName ===
                                    'customer_id'
                                ) {

                                    return (
                                        (item.customer_name || '')
                                            .toLowerCase()
                                            .includes(keyword)
                                        ||
                                        (item.customer_code || '')
                                            .toLowerCase()
                                            .includes(keyword)
                                    );

                                }


                                if (
                                    fieldName ===
                                    'lead_id'
                                ) {

                                    return (
                                        (item.lead_code || '')
                                            .toLowerCase()
                                            .includes(keyword)
                                        ||
                                        (item.company_name || '')
                                            .toLowerCase()
                                            .includes(keyword)
                                        ||
                                        (item.contact_name || '')
                                            .toLowerCase()
                                            .includes(keyword)
                                    );

                                }


                                return false;

                            }
                        );


                    if (!filtered.length) {

                        const empty =
                            document.createElement(
                                'div'
                            );


                        empty.className =
                            'searchable-select-empty';


                        empty.textContent =
                            'No results found.';


                        optionsContainer.appendChild(
                            empty
                        );


                        return;

                    }


                    filtered.forEach(
                        function (item) {

                            const option =
                                document.createElement(
                                    'div'
                                );


                            option.className =
                                'searchable-select-option';


                            if (
                                fieldName ===
                                'customer_id'
                            ) {

                                option.innerHTML =
                                    `
                                    <strong>
                                        ${escapeHtml(
                                            item.customer_name || '-'
                                        )}
                                    </strong>

                                    <span>
                                        ${escapeHtml(
                                            item.customer_code || ''
                                        )}
                                    </span>
                                    `;


                                option.addEventListener(
                                    'click',
                                    function () {

                                        selectItem(
                                            item
                                        );

                                    }
                                );

                            }


                            if (
                                fieldName ===
                                'lead_id'
                            ) {

                                option.innerHTML =
                                    `
                                    <strong>
                                        ${escapeHtml(
                                            item.lead_code || '-'
                                        )}
                                    </strong>

                                    <span>
                                        ${escapeHtml(
                                            item.company_name || ''
                                        )}
                                        ${
                                            item.contact_name
                                                ? ' • ' +
                                                  escapeHtml(
                                                      item.contact_name
                                                  )
                                                : ''
                                        }
                                    </span>
                                    `;


                                option.addEventListener(
                                    'click',
                                    function () {

                                        selectItem(
                                            item
                                        );

                                    }
                                );

                            }


                            optionsContainer.appendChild(
                                option
                            );

                        }
                    );

                }


                function selectItem(item) {

                    hiddenInput.value =
                        item[
                            fieldName
                                .replace(
                                    '_id',
                                    ''
                                ) + '_id'
                        ] || '';


                    if (
                        fieldName ===
                        'customer_id'
                    ) {

                        hiddenInput.value =
                            item.customer_id;


                        valueDisplay.textContent =
                            item.customer_name +
                            ' (' +
                            item.customer_code +
                            ')';

                    }


                    if (
                        fieldName ===
                        'lead_id'
                    ) {

                        hiddenInput.value =
                            item.lead_id;


                        valueDisplay.textContent =
                            item.lead_code +
                            ' — ' +
                            item.company_name;

                    }


                    element.classList.remove(
                        'open'
                    );


                    searchInput.value =
                        '';


                    renderOptions();

                }


                trigger.addEventListener(
                    'click',
                    function (event) {

                        event.stopPropagation();


                        document
                            .querySelectorAll(
                                '.searchable-select.open'
                            )
                            .forEach(
                                function (other) {

                                    if (
                                        other !==
                                        element
                                    ) {

                                        other.classList.remove(
                                            'open'
                                        );

                                    }

                                }
                            );


                        element.classList.toggle(
                            'open'
                        );


                        if (
                            element.classList.contains(
                                'open'
                            )
                        ) {

                            searchInput.focus();

                        }

                    }
                );


                searchInput.addEventListener(
                    'input',
                    function () {

                        renderOptions(
                            searchInput.value
                        );

                    }
                );


                const oldValue =
                    hiddenInput.value;


                if (oldValue) {

                    const oldItem =
                        data.find(
                            function (item) {

                                return (
                                    item[
                                        fieldName
                                    ] ===
                                    oldValue
                                );

                            }
                        );


                    if (oldItem) {

                        selectItem(
                            oldItem
                        );

                    }

                }


                renderOptions();

            }
        );


        document.addEventListener(
            'click',
            function (event) {

                if (
                    !event.target.closest(
                        '.searchable-select'
                    )
                ) {

                    document
                        .querySelectorAll(
                            '.searchable-select.open'
                        )
                        .forEach(
                            function (element) {

                                element.classList.remove(
                                    'open'
                                );

                            }
                        );

                }

            }
        );

    }
);


/* =========================================================
   HTML ESCAPE
========================================================= */

function escapeHtml(value) {

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

</script>

@endsection