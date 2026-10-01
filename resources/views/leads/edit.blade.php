@extends('layouts.app')

@section('title', 'Edit Lead')

@section('content')

<div class="page-head">

    <div>

        <h1>Edit Lead</h1>

        <p>
            Update lead information and customer relationship.
        </p>

    </div>

    <div class="actions">

        <a
            href="{{ route('leads.show', $lead) }}"
            class="btn">
            Back to Lead
        </a>

    </div>

</div>


@if ($errors->any())

<div class="alert">

    <strong>
        Please check the following errors:
    </strong>

    <ul style="margin: 8px 0 0 18px;">

        @foreach ($errors->all() as $error)

        <li>
            {{ $error }}
        </li>

        @endforeach

    </ul>

</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>
                Lead Information
            </h3>

            <p>
                Update lead information, customer relationship,
                and sales assignment.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('leads.update', $lead) }}">

            @csrf

            @method('PUT')


            <div class="form-grid">


                {{-- =====================================================
                     SALES
                ====================================================== --}}

                <div class="form-group">

                    <label>
                        Assigned Sales
                    </label>

                    <div
                        class="searchable-select"
                        data-name="user_id">

                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ old('user_id', $lead->user_id) }}">


                        <button
                            type="button"
                            class="searchable-select-trigger">

                            <span class="searchable-select-value">
                                Select Sales
                            </span>

                            <span class="searchable-select-arrow">
                                ▾
                            </span>

                        </button>


                        <div class="searchable-select-menu">

                            <input
                                type="text"
                                class="searchable-select-search"
                                placeholder="Search sales..."
                                autocomplete="off">

                            <div
                                class="searchable-select-options">
                            </div>

                        </div>

                    </div>

                    <small class="field-hint">
                        Sales user assigned to this lead.
                    </small>

                </div>


                {{-- =====================================================
                     CUSTOMER
                ====================================================== --}}

                <div class="form-group">

                    <label>
                        Customer
                    </label>

                    <div
                        class="searchable-select"
                        data-name="customer_id">

                        <input
                            type="hidden"
                            name="customer_id"
                            value="{{ old('customer_id', $lead->customer_id) }}">


                        <button
                            type="button"
                            class="searchable-select-trigger">

                            <span class="searchable-select-value">
                                Select Customer
                            </span>

                            <span class="searchable-select-arrow">
                                ▾
                            </span>

                        </button>


                        <div class="searchable-select-menu">

                            <input
                                type="text"
                                class="searchable-select-search"
                                placeholder="Search customer..."
                                autocomplete="off">

                            <div
                                class="searchable-select-options">
                            </div>

                        </div>

                    </div>

                    <small class="field-hint">
                        Customer can be assigned when the lead is already
                        associated with an existing customer.
                    </small>

                </div>


                {{-- =====================================================
                     COMPANY NAME
                ====================================================== --}}

                <div class="form-group">

                    <label for="company_name">
                        Company Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old(
                            'company_name',
                            $lead->company_name
                        ) }}"
                        placeholder="Enter company name"
                        required>

                </div>


                {{-- =====================================================
                     CONTACT NAME
                ====================================================== --}}

                <div class="form-group">

                    <label for="contact_name">
                        Contact Name
                    </label>

                    <input
                        type="text"
                        id="contact_name"
                        name="contact_name"
                        value="{{ old(
                            'contact_name',
                            $lead->contact_name
                        ) }}"
                        placeholder="Enter contact name">

                </div>


                {{-- =====================================================
                     PHONE
                ====================================================== --}}

                <div class="form-group">

                    <label for="phone">
                        Phone
                    </label>

                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old(
                            'phone',
                            $lead->phone
                        ) }}"
                        placeholder="Enter phone number">

                    <div
                        id="phone-source"
                        class="field-hint">
                    </div>

                </div>


                {{-- =====================================================
                     EMAIL
                ====================================================== --}}

                <div class="form-group">

                    <label for="email">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old(
                            'email',
                            $lead->email
                        ) }}"
                        placeholder="Enter email address">

                    <div
                        id="email-source"
                        class="field-hint">
                    </div>

                </div>


                {{-- =====================================================
                     SOURCE
                ====================================================== --}}

                <div class="form-group">

                    <label for="source">
                        Lead Source
                    </label>

                    <input
                        type="text"
                        id="source"
                        name="source"
                        value="{{ old(
                            'source',
                            $lead->source
                        ) }}"
                        placeholder="e.g. Website, Referral, Exhibition">

                </div>


                {{-- =====================================================
                     STATUS
                ====================================================== --}}

                <div class="form-group">

                    <label for="status">
                        Status <span>*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        required>

                        <option value="">
                            Select status
                        </option>

                        @php
                            $statuses = [
                                'New',
                                'Contacted',
                                'Qualified',
                                'Unqualified',
                                'Converted',
                                'Closed',
                            ];

                            $currentStatus = old(
                                'status',
                                $lead->status
                            );
                        @endphp

                        @foreach ($statuses as $status)

                        <option
                            value="{{ $status }}"
                            @selected(
                                $currentStatus === $status
                            )>
                            {{ $status }}
                        </option>

                        @endforeach

                    </select>

                </div>


                {{-- =====================================================
                     QUALIFICATION
                ====================================================== --}}

                <div
                    class="form-group"
                    style="grid-column: 1 / -1;">

                    <label for="qualification">
                        Qualification
                    </label>

                    <textarea
                        id="qualification"
                        name="qualification"
                        rows="5"
                        placeholder="Enter lead qualification notes...">{{ old(
                            'qualification',
                            $lead->qualification
                        ) }}</textarea>

                </div>


            </div>


            {{-- =====================================================
                 FORM ACTIONS
            ====================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('leads.show', $lead) }}"
                    class="btn">
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn primary">
                    Save Changes
                </button>

            </div>

        </form>

    </div>

</div>


<style>

    /* =========================================================
       SEARCHABLE SELECT
    ========================================================= */

    .searchable-select {

        position: relative;

        width: 100%;

    }


    .searchable-select-trigger {

        width: 100%;

        min-height: 42px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 10px 12px;

        border: 1px solid #d9dee8;

        border-radius: 8px;

        background: #fff;

        color: #17284f;

        font-size: 14px;

        cursor: pointer;

        text-align: left;

        transition:
            border-color .2s ease,
            box-shadow .2s ease;

    }


    .searchable-select-trigger:hover {

        border-color: #aeb8c8;

    }


    .searchable-select-trigger:focus {

        outline: none;

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .10);

    }


    .searchable-select-arrow {

        font-size: 13px;

        color: #7d8797;

        transition:
            transform .2s ease;

    }


    .searchable-select.open
    .searchable-select-arrow {

        transform: rotate(180deg);

    }


    /* =========================================================
       DROPDOWN
    ========================================================= */

    .searchable-select-menu {

        display: none;

        position: absolute;

        left: 0;

        right: 0;

        top: calc(100% + 5px);

        z-index: 100;

        background: #fff;

        border: 1px solid #d9dee8;

        border-radius: 10px;

        box-shadow:
            0 10px 30px rgba(23, 40, 79, .12);

        padding: 8px;

    }


    .searchable-select.open
    .searchable-select-menu {

        display: block;

    }


    .searchable-select-search {

        width: 100%;

        box-sizing: border-box;

        padding: 9px 10px;

        border: 1px solid #e0e4eb;

        border-radius: 7px;

        font-size: 13px;

        margin-bottom: 7px;

    }


    .searchable-select-search:focus {

        outline: none;

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .08);

    }


    .searchable-select-options {

        max-height: 220px;

        overflow-y: auto;

    }


    .searchable-select-option {

        padding: 9px 10px;

        border-radius: 7px;

        font-size: 13px;

        color: #26344f;

        cursor: pointer;

        transition:
            background .15s ease;

    }


    .searchable-select-option:hover {

        background: #f1f6f8;

    }


    .searchable-select-option.selected {

        background: #e8f5f3;

        color: #167d70;

        font-weight: 600;

    }


    .searchable-select-empty {

        padding: 12px 10px;

        color: #8a94a6;

        font-size: 13px;

        text-align: center;

    }


    /* =========================================================
       FIELD HINT
    ========================================================= */

    .field-hint {

        min-height: 17px;

        margin-top: 5px;

        color: #8a94a6;

        font-size: 11px;

    }


    .field-hint.active {

        color: #2ba7a0;

    }


    /* =========================================================
       TEXTAREA
    ========================================================= */

    textarea {

        width: 100%;

        box-sizing: border-box;

        padding: 11px 12px;

        border: 1px solid #d9dee8;

        border-radius: 8px;

        background: #fff;

        color: #26344f;

        font-family: inherit;

        font-size: 14px;

        line-height: 1.5;

        resize: vertical;

        transition:
            border-color .2s ease,
            box-shadow .2s ease;

    }


    textarea:hover {

        border-color: #aeb8c8;

    }


    textarea:focus {

        outline: none;

        border-color: #2ba7a0;

        box-shadow:
            0 0 0 3px rgba(43, 167, 160, .10);

    }

</style>


<script>

    const users = @json($users);

    const customers = @json($customers);


    /* =========================================================
       ESCAPE HTML
    ========================================================= */

    function escapeHtml(value) {

        return String(value)

            .replaceAll('&', '&amp;')

            .replaceAll('<', '&lt;')

            .replaceAll('>', '&gt;')

            .replaceAll('"', '&quot;')

            .replaceAll("'", '&#039;');

    }


    /* =========================================================
       GENERIC SEARCHABLE DROPDOWN
    ========================================================= */

    function createSearchableDropdown(
        element,
        items,
        config
    ) {

        const trigger =
            element.querySelector(
                '.searchable-select-trigger'
            );


        const hiddenInput =
            element.querySelector(
                'input[type="hidden"]'
            );


        const searchInput =
            element.querySelector(
                '.searchable-select-search'
            );


        const optionsContainer =
            element.querySelector(
                '.searchable-select-options'
            );


        const valueDisplay =
            element.querySelector(
                '.searchable-select-value'
            );


        function renderOptions(filter = '') {

            optionsContainer.innerHTML = '';


            const search =
                filter
                    .toLowerCase()
                    .trim();


            const filtered =
                items.filter(item => {

                    const text =
                        config.search(item);

                    return text
                        .toLowerCase()
                        .includes(search);

                });


            if (filtered.length === 0) {

                const empty =
                    document.createElement('div');

                empty.className =
                    'searchable-select-empty';

                empty.textContent =
                    'No options found';

                optionsContainer.appendChild(
                    empty
                );

                return;

            }


            filtered.forEach(item => {

                const option =
                    document.createElement('div');

                option.className =
                    'searchable-select-option';


                option.innerHTML =
                    config.render(item);


                if (
                    String(hiddenInput.value) ===
                    String(config.id(item))
                ) {

                    option.classList.add(
                        'selected'
                    );

                }


                option.addEventListener(
                    'click',
                    function() {

                        hiddenInput.value =
                            config.id(item);


                        valueDisplay.textContent =
                            config.label(item);


                        element.classList.remove(
                            'open'
                        );


                        searchInput.value =
                            '';


                        renderOptions();


                        if (config.onSelect) {

                            config.onSelect(item);

                        }

                    }
                );


                optionsContainer.appendChild(
                    option
                );

            });

        }


        trigger.addEventListener(
            'click',
            function() {

                document
                    .querySelectorAll(
                        '.searchable-select.open'
                    )
                    .forEach(other => {

                        if (other !== element) {

                            other.classList.remove(
                                'open'
                            );

                        }

                    });


                element.classList.toggle(
                    'open'
                );


                if (
                    element.classList.contains(
                        'open'
                    )
                ) {

                    searchInput.value = '';

                    renderOptions();

                    setTimeout(
                        () => searchInput.focus(),
                        50
                    );

                }

            }
        );


        searchInput.addEventListener(
            'input',
            function() {

                renderOptions(
                    this.value
                );

            }
        );


        return {

            setValue(id) {

                const item =
                    items.find(
                        item =>
                            String(
                                config.id(item)
                            ) === String(id)
                    );


                if (!item) {

                    hiddenInput.value = '';

                    valueDisplay.textContent =
                        config.placeholder;

                    renderOptions();

                    return;

                }


                hiddenInput.value =
                    config.id(item);


                valueDisplay.textContent =
                    config.label(item);


                renderOptions();

            }

        };

    }


    /* =========================================================
       SALES DROPDOWN
    ========================================================= */

    const salesElement =
        document.querySelector(
            '[data-name="user_id"]'
        );


    const salesDropdown =
        createSearchableDropdown(
            salesElement,
            users,
            {

                id: user =>
                    user.user_id,

                label: user =>
                    user.name,

                search: user =>
                    `${user.name} ${user.email || ''}`,

                render: user => `
                    <div style="
                        font-weight: 600;
                        color: #26344f;
                    ">
                        ${escapeHtml(user.name)}
                    </div>

                    <div style="
                        margin-top: 2px;
                        font-size: 11px;
                        color: #8a94a6;
                    ">
                        ${escapeHtml(user.email || '')}
                    </div>
                `,

                placeholder:
                    'Select Sales'

            }
        );


    /* =========================================================
       CUSTOMER DROPDOWN
    ========================================================= */

    const customerElement =
        document.querySelector(
            '[data-name="customer_id"]'
        );


    const customerDropdown =
        createSearchableDropdown(
            customerElement,
            customers,
            {

                id: customer =>
                    customer.customer_id,

                label: customer =>
                    `${customer.customer_name} (${customer.customer_code})`,

                search: customer =>
                    `${customer.customer_name} ${customer.customer_code}`,

                render: customer => `
                    <div style="
                        font-weight: 600;
                        color: #26344f;
                    ">
                        ${escapeHtml(
                            customer.customer_name
                        )}
                    </div>

                    <div style="
                        margin-top: 2px;
                        font-size: 11px;
                        color: #8a94a6;
                    ">
                        ${escapeHtml(
                            customer.customer_code
                        )}
                    </div>
                `,

                placeholder:
                    'Select Customer',

                onSelect: customer => {

                    /*
                     * Ketika customer diganti,
                     * gunakan phone dan email customer
                     * sebagai default.
                     */

                    document.getElementById(
                        'phone'
                    ).value =
                        customer.phone || '';


                    document.getElementById(
                        'email'
                    ).value =
                        customer.email || '';


                    updateContactSource(
                        customer
                    );

                }

            }
        );


    /* =========================================================
       CUSTOMER CONTACT INFORMATION
    ========================================================= */

    function updateContactSource(customer) {

        const phoneSource =
            document.getElementById(
                'phone-source'
            );


        const emailSource =
            document.getElementById(
                'email-source'
            );


        if (customer.phone) {

            phoneSource.textContent =
                'Customer phone • Editable';

            phoneSource.classList.add(
                'active'
            );

        } else {

            phoneSource.textContent =
                'Customer has no phone number';

            phoneSource.classList.remove(
                'active'
            );

        }


        if (customer.email) {

            emailSource.textContent =
                'Customer email • Editable';

            emailSource.classList.add(
                'active'
            );

        } else {

            emailSource.textContent =
                'Customer has no email address';

            emailSource.classList.remove(
                'active'
            );

        }

    }


    /* =========================================================
       RESTORE SALES
    ========================================================= */

    const currentUserId =
        @json(old('user_id',$lead->user_id)
        );


    if (currentUserId) {

        salesDropdown.setValue(
            currentUserId
        );

    }


    /* =========================================================
       RESTORE CUSTOMER
    ========================================================= */

    const currentCustomerId =
        @json(old('customer_id', $lead->customer_id)
        );


    if (currentCustomerId) {

        customerDropdown.setValue(
            currentCustomerId
        );


        /*
         * Jangan overwrite phone/email ketika
         * membuka halaman edit.
         */

        const selectedCustomer =
            customers.find(
                customer =>
                    String(
                        customer.customer_id
                    ) === String(
                        currentCustomerId
                    )
            );


        if (selectedCustomer) {

            updateContactSource(
                selectedCustomer
            );

        }

    }


    /* =========================================================
       CLOSE DROPDOWN OUTSIDE
    ========================================================= */

    document.addEventListener(
        'click',
        function(event) {

            if (
                !event.target.closest(
                    '.searchable-select'
                )
            ) {

                document
                    .querySelectorAll(
                        '.searchable-select.open'
                    )
                    .forEach(dropdown => {

                        dropdown.classList.remove(
                            'open'
                        );

                    });

            }

        }
    );

</script>

@endsection