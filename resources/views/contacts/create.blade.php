@extends('layouts.app')

@section('title', 'Create Contact')

@section('content')

<div class="page-head">

    <div>
        <h1>New Contact</h1>

        <p>
            Add a new contact to a customer.
        </p>
    </div>

    <div class="actions">

        <a
            href="{{ route('contacts.index') }}"
            class="btn">
            Back to Contacts
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
                Contact Information
            </h3>

            <p>
                Enter the contact information and assign the contact to a customer.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('contacts.store') }}">

            @csrf


            <div class="form-grid">


                {{-- =====================================================
                 CUSTOMER
            ====================================================== --}}

                <div class="form-group">

                    <label>
                        Customer <span>*</span>
                    </label>

                    <div
                        class="searchable-select"
                        data-name="customer_id"
                        data-placeholder="Search customer...">

                        <input
                            type="hidden"
                            name="customer_id"
                            value="{{ old('customer_id') }}">


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


                            <div class="searchable-select-options"></div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                 CONTACT NAME
            ====================================================== --}}

                <div class="form-group">

                    <label for="name">
                        Contact Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Enter contact name"
                        required>

                </div>


                {{-- =====================================================
                 POSITION
            ====================================================== --}}

                <div class="form-group">

                    <label for="position">
                        Position
                    </label>

                    <input
                        type="text"
                        id="position"
                        name="position"
                        value="{{ old('position') }}"
                        placeholder="Enter position">

                </div>


                {{-- =====================================================
                 DEPARTMENT
            ====================================================== --}}

                <div class="form-group">

                    <label for="department">
                        Department
                    </label>

                    <input
                        type="text"
                        id="department"
                        name="department"
                        value="{{ old('department') }}"
                        placeholder="Enter department">

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
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number">

                    <div
                        id="phone-source"
                        class="field-hint"></div>

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
                        value="{{ old('email') }}"
                        placeholder="Enter email address">

                    <div
                        id="email-source"
                        class="field-hint"></div>

                </div>


                {{-- =====================================================
                 CONTACT TYPE
            ====================================================== --}}

                <div class="form-group">

                    <label for="contact_type">
                        Contact Type
                    </label>

                    <select
                        id="contact_type"
                        name="contact_type">

                        <option value="">
                            Select contact type
                        </option>

                        <option
                            value="Decision Maker"
                            {{ old('contact_type') === 'Decision Maker' ? 'selected' : '' }}>
                            Decision Maker
                        </option>

                        <option
                            value="Purchasing"
                            {{ old('contact_type') === 'Purchasing' ? 'selected' : '' }}>
                            Purchasing
                        </option>

                        <option
                            value="Finance"
                            {{ old('contact_type') === 'Finance' ? 'selected' : '' }}>
                            Finance
                        </option>

                        <option
                            value="Technical"
                            {{ old('contact_type') === 'Technical' ? 'selected' : '' }}>
                            Technical
                        </option>

                        <option
                            value="Management"
                            {{ old('contact_type') === 'Management' ? 'selected' : '' }}>
                            Management
                        </option>

                        <option
                            value="Other"
                            {{ old('contact_type') === 'Other' ? 'selected' : '' }}>
                            Other
                        </option>

                    </select>

                </div>


                {{-- =====================================================
                 PRIMARY CONTACT
            ====================================================== --}}

                <div class="form-group">

                    <label>
                        Primary Contact
                    </label>

                    <label class="primary-contact-option">

                        <input
                            type="checkbox"
                            name="is_primary"
                            value="1"
                            {{ old('is_primary') ? 'checked' : '' }}>

                        <span>
                            Set as primary contact
                        </span>

                    </label>

                </div>


            </div>


            {{-- =====================================================
             FORM ACTIONS
        ====================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('contacts.index') }}"
                    class="btn">
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn primary">
                    Create Contact
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


    .searchable-select.open .searchable-select-arrow {

        transform: rotate(180deg);

    }


    /* =========================================================
   DROPDOWN MENU
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


    .searchable-select.open .searchable-select-menu {

        display: block;

    }


    /* =========================================================
   SEARCH INPUT
========================================================= */

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


    /* =========================================================
   OPTIONS
========================================================= */

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
   PRIMARY CONTACT
========================================================= */

    .primary-contact-option {

        display: flex;

        align-items: center;

        gap: 10px;

        min-height: 42px;

        cursor: pointer;

        font-weight: 400;

    }


    .primary-contact-option input[type="checkbox"] {

        width: 17px;

        height: 17px;

        margin: 0;

        accent-color: #2ba7a0;

        cursor: pointer;

    }
</style>

<script>
    /* =========================================================
   CUSTOMER DATA
========================================================= */

    const customers = @json($customers);


    /* =========================================================
       CUSTOMER DROPDOWN
    ========================================================= */

    function createCustomerDropdown(element, items) {

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
                items.filter(customer => {

                    const name =
                        String(
                            customer.customer_name || ''
                        ).toLowerCase();


                    const code =
                        String(
                            customer.customer_code || ''
                        ).toLowerCase();


                    return (
                        name.includes(search) ||
                        code.includes(search)
                    );

                });


            if (filtered.length === 0) {

                const empty =
                    document.createElement('div');

                empty.className =
                    'searchable-select-empty';

                empty.textContent =
                    'No customers found';

                optionsContainer.appendChild(
                    empty
                );

                return;

            }


            filtered.forEach(customer => {

                const option =
                    document.createElement('div');

                option.className =
                    'searchable-select-option';


                option.innerHTML = `
                <div style="
                    font-weight: 600;
                    color: #26344f;
                ">
                    ${escapeHtml(customer.customer_name)}
                </div>

                <div style="
                    margin-top: 2px;
                    font-size: 11px;
                    color: #8a94a6;
                ">
                    ${escapeHtml(customer.customer_code)}
                </div>
            `;


                if (
                    hiddenInput.value ===
                    String(customer.customer_id)
                ) {

                    option.classList.add(
                        'selected'
                    );

                }


                option.addEventListener(
                    'click',
                    function() {

                        selectCustomer(
                            customer
                        );

                    }
                );


                optionsContainer.appendChild(
                    option
                );

            });

        }


        function selectCustomer(customer) {

            hiddenInput.value =
                customer.customer_id;


            valueDisplay.textContent =
                customer.customer_name +
                ' (' +
                customer.customer_code +
                ')';


            element.classList.remove(
                'open'
            );


            searchInput.value =
                '';


            renderOptions();


            populateCustomerData(
                customer
            );

        }


        trigger.addEventListener(
            'click',
            function() {

                document
                    .querySelectorAll(
                        '.searchable-select.open'
                    )
                    .forEach(other => {

                        if (
                            other !== element
                        ) {

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

                    searchInput.value =
                        '';

                    renderOptions();


                    setTimeout(
                        () => {

                            searchInput.focus();

                        },
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

            setValue(customerId) {

                const customer =
                    items.find(
                        item =>
                        String(
                            item.customer_id
                        ) === String(customerId)
                    );


                if (!customer) {

                    hiddenInput.value =
                        '';

                    valueDisplay.textContent =
                        'Select Customer';

                    return;

                }


                hiddenInput.value =
                    customer.customer_id;


                valueDisplay.textContent =
                    customer.customer_name +
                    ' (' +
                    customer.customer_code +
                    ')';


                renderOptions();


                populateCustomerData(
                    customer,
                    true
                );

            }

        };

    }


    /* =========================================================
       POPULATE CUSTOMER PHONE & EMAIL
    ========================================================= */

    function populateCustomerData(
        customer,
        restoringOldInput = false
    ) {

        const phoneInput =
            document.getElementById('phone');


        const emailInput =
            document.getElementById('email');


        const phoneSource =
            document.getElementById('phone-source');


        const emailSource =
            document.getElementById('email-source');


        /*
         * Saat validation error terjadi,
         * nilai old() harus dipertahankan.
         */

        if (!restoringOldInput) {

            phoneInput.value =
                customer.phone || '';

            emailInput.value =
                customer.email || '';

        }


        /* =====================================================
           PHONE SOURCE
        ===================================================== */

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


        /* =====================================================
           EMAIL SOURCE
        ===================================================== */

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
       HTML ESCAPE
    ========================================================= */

    function escapeHtml(value) {

        return String(value)

            .replaceAll(
                '&',
                '&amp;'
            )

            .replaceAll(
                '<',
                '&lt;'
            )

            .replaceAll(
                '>',
                '&gt;'
            )

            .replaceAll(
                '"',
                '&quot;'
            )

            .replaceAll(
                "'",
                '&#039;'
            );

    }


    /* =========================================================
       INITIALIZE CUSTOMER DROPDOWN
    ========================================================= */

    const customerElement =
        document.querySelector(
            '[data-name="customer_id"]'
        );


    const customerDropdown =
        createCustomerDropdown(
            customerElement,
            customers
        );


    /* =========================================================
       RESTORE OLD CUSTOMER AFTER VALIDATION ERROR
    ========================================================= */

    const oldCustomerId =
        @json(old('customer_id'));


    if (oldCustomerId) {

        customerDropdown.setValue(
            oldCustomerId
        );

    }


    /* =========================================================
       CLOSE DROPDOWN WHEN CLICKING OUTSIDE
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