@extends('layouts.app')

@section('title', 'Create Lead')

@section('content')

<div class="page-head">

    <div>
        <h1>Create Lead</h1>

        <p>
            Add a new lead and assign it to a sales user.
        </p>
    </div>

    <div class="actions">
        <a
            href="{{ route('leads.index') }}"
            class="btn">
            Back to Leads
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

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</div>

@endif


<div class="card">

    <div class="card-head">

        <div>

            <h3>Lead Information</h3>

            <p>
                Enter the lead information below.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('leads.store') }}"
        >

            @csrf


            <div class="form-grid">


                {{-- =====================================================
                     SALES / USER
                ====================================================== --}}

                <div class="form-group">

                    <label>
                        Assigned Sales
                    </label>


                    <div
                        class="searchable-select"
                        data-name="user_id"
                        data-placeholder="Search sales..."
                    >

                        <input
                            type="hidden"
                            name="user_id"
                            value="{{ old('user_id') }}"
                        >


                        <button
                            type="button"
                            class="searchable-select-trigger"
                        >

                            <span
                                class="searchable-select-value"
                            >
                                Select Sales
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
                                placeholder="Search sales..."
                                autocomplete="off"
                            >


                            <div
                                class="searchable-select-options"
                            ></div>

                        </div>

                    </div>

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
                        data-name="customer_id"
                        data-placeholder="Search customer..."
                    >

                        <input
                            type="hidden"
                            name="customer_id"
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

                </div>


                {{-- =====================================================
                     COMPANY
                ====================================================== --}}

                <div class="form-group">

                    <label for="company_name">

                        Company Name
                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        value="{{ old('company_name') }}"
                        placeholder="Enter company name"
                        required
                    >

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
                        value="{{ old('contact_name') }}"
                        placeholder="Enter contact name"
                    >

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
                        placeholder="Enter phone number"
                    >


                    <small class="field-hint">

                        Customer phone will be used as the
                        default when selecting a customer.

                    </small>

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
                        placeholder="Enter email address"
                    >


                    <small class="field-hint">

                        Customer email will be used as the
                        default when selecting a customer.

                    </small>

                </div>


                {{-- =====================================================
                     SOURCE
                ====================================================== --}}

                <div class="form-group">

                    <label for="source">
                        Lead Source
                    </label>


                    <select
                        id="source"
                        name="source"
                    >

                        <option value="">
                            Select lead source
                        </option>


                        <option
                            value="Website"
                            @selected(
                                old('source') === 'Website'
                            )
                        >
                            Website
                        </option>


                        <option
                            value="Referral"
                            @selected(
                                old('source') === 'Referral'
                            )
                        >
                            Referral
                        </option>


                        <option
                            value="Social Media"
                            @selected(
                                old('source') === 'Social Media'
                            )
                        >
                            Social Media
                        </option>


                        <option
                            value="Email"
                            @selected(
                                old('source') === 'Email'
                            )
                        >
                            Email
                        </option>


                        <option
                            value="Phone"
                            @selected(
                                old('source') === 'Phone'
                            )
                        >
                            Phone
                        </option>


                        <option
                            value="Exhibition"
                            @selected(
                                old('source') === 'Exhibition'
                            )
                        >
                            Exhibition
                        </option>


                        <option
                            value="Sales Visit"
                            @selected(
                                old('source') === 'Sales Visit'
                            )
                        >
                            Sales Visit
                        </option>


                        <option
                            value="Other"
                            @selected(
                                old('source') === 'Other'
                            )
                        >
                            Other
                        </option>

                    </select>

                </div>


                {{-- =====================================================
                     STATUS
                ====================================================== --}}

                <div class="form-group">

                    <label for="status">

                        Status
                        <span>*</span>

                    </label>


                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option value="">
                            Select status
                        </option>


                        <option
                            value="New"
                            @selected(
                                old('status') === 'New'
                            )
                        >
                            New
                        </option>


                        <option
                            value="Contacted"
                            @selected(
                                old('status') === 'Contacted'
                            )
                        >
                            Contacted
                        </option>


                        <option
                            value="Qualified"
                            @selected(
                                old('status') === 'Qualified'
                            )
                        >
                            Qualified
                        </option>


                        <option
                            value="Unqualified"
                            @selected(
                                old('status') === 'Unqualified'
                            )
                        >
                            Unqualified
                        </option>


                        <option
                            value="Closed"
                            @selected(
                                old('status') === 'Closed'
                            )
                        >
                            Closed
                        </option>

                    </select>

                </div>


                {{-- =====================================================
                     QUALIFICATION
                ====================================================== --}}

                <div
                    class="form-group"
                    style="grid-column: 1 / -1;"
                >

                    <label for="qualification">
                        Qualification
                    </label>


                    <textarea
                        id="qualification"
                        name="qualification"
                        rows="5"
                        placeholder="Enter lead qualification notes..."
                    >{{ old('qualification') }}</textarea>


                    <small class="field-hint">

                        Add relevant information about the lead,
                        customer needs, potential opportunity,
                        budget, timeline, or qualification result.

                    </small>

                </div>

            </div>


            {{-- =====================================================
                 FORM ACTIONS
            ====================================================== --}}

            <div class="form-actions">

                <a
                    href="{{ route('leads.index') }}"
                    class="btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="btn primary"
                >
                    Save Lead
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

    padding: 0 12px;

    border: 1px solid #d9dee8;
    border-radius: 8px;

    background: #fff;

    color: #34415c;

    font-size: 13px;

    cursor: pointer;

    text-align: left;

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

    white-space: nowrap;

    text-overflow: ellipsis;
}

.searchable-select-arrow {
    margin-left: 10px;

    color: #7d8797;

    font-size: 14px;
}

.searchable-select-menu {
    position: absolute;

    top: calc(100% + 6px);

    left: 0;
    right: 0;

    z-index: 100;

    display: none;

    padding: 8px;

    background: #fff;

    border: 1px solid #d9dee8;

    border-radius: 9px;

    box-shadow:
        0 12px 30px rgba(23, 40, 79, .12);
}

.searchable-select.open
.searchable-select-menu {
    display: block;
}

.searchable-select-search {
    width: 100%;

    height: 36px;

    box-sizing: border-box;

    padding: 0 10px;

    border: 1px solid #d9dee8;

    border-radius: 7px;

    color: #17284f;

    font-size: 13px;

    outline: none;
}

.searchable-select-search:focus {
    border-color: #2ba7a0;
}

.searchable-select-options {
    max-height: 220px;

    overflow-y: auto;

    margin-top: 6px;
}

.searchable-select-option {
    display: block;

    width: 100%;

    padding: 9px 10px;

    border: 0;

    border-radius: 7px;

    background: transparent;

    color: #34415c;

    text-align: left;

    cursor: pointer;

    font-size: 13px;
}

.searchable-select-option:hover {
    background: #f4f7fa;
}

.searchable-select-option strong {
    display: block;

    color: #17284f;

    font-size: 13px;
}

.searchable-select-option small {
    display: block;

    margin-top: 2px;

    color: #8791a1;

    font-size: 11px;
}

.searchable-select-empty {
    padding: 12px 10px;

    color: #8791a1;

    font-size: 12px;

    text-align: center;
}


/* =========================================================
   TEXTAREA
========================================================= */

textarea {
    width: 100%;

    box-sizing: border-box;

    resize: vertical;

    min-height: 120px;

    padding: 11px 12px;

    border: 1px solid #d9dee8;

    border-radius: 8px;

    background: #fff;

    color: #17284f;

    font-family: inherit;

    font-size: 13px;

    line-height: 1.6;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

textarea:focus {
    outline: none;

    border-color: #2ba7a0;

    box-shadow:
        0 0 0 3px rgba(43, 167, 160, .08);
}


/* =========================================================
   FIELD HINT
========================================================= */

.field-hint {
    display: block;

    margin-top: 6px;

    color: #8791a1;

    font-size: 11px;

    line-height: 1.5;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .form-grid {
        grid-template-columns: 1fr !important;
    }

}

</style>


<script>

/* =========================================================
   DATA
========================================================= */

const leadUsers = @json($users);

const leadCustomers = @json($customers);


/* =========================================================
   HTML ESCAPE
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
   SEARCHABLE DROPDOWN
========================================================= */

function createLeadSearchableDropdown(
    element,
    items,
    config
) {

    const hiddenInput =
        element.querySelector(
            'input[type="hidden"]'
        );


    const trigger =
        element.querySelector(
            '.searchable-select-trigger'
        );


    const valueElement =
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


    const placeholder =
        element.dataset.placeholder ||
        'Select...';


    let selectedValue =
        hiddenInput.value || '';


    function getLabel(item) {

        return config.label(item);

    }


    function getSearchText(item) {

        return config.searchText(item)
            .toLowerCase();

    }


    function renderOptions(
        search = ''
    ) {

        const keyword =
            search.toLowerCase().trim();


        const filtered =
            items.filter(
                function (item) {

                    return getSearchText(item)
                        .includes(keyword);

                }
            );


        if (!filtered.length) {

            optionsContainer.innerHTML = `
                <div class="searchable-select-empty">
                    No results found.
                </div>
            `;

            return;

        }


        optionsContainer.innerHTML =
            filtered.map(
                function (item) {

                    const value =
                        config.value(item);

                    const label =
                        getLabel(item);

                    const description =
                        config.description
                            ? config.description(item)
                            : '';

                    return `
                        <button
                            type="button"
                            class="searchable-select-option"
                            data-value="${escapeHtml(value)}"
                        >

                            <strong>
                                ${escapeHtml(label)}
                            </strong>

                            ${
                                description
                                ? `
                                    <small>
                                        ${escapeHtml(
                                            description
                                        )}
                                    </small>
                                `
                                : ''
                            }

                        </button>
                    `;

                }
            ).join('');

    }


    function setValue(value) {

        selectedValue =
            value || '';


        hiddenInput.value =
            selectedValue;


        const selectedItem =
            items.find(
                function (item) {

                    return String(
                        config.value(item)
                    ) === String(
                        selectedValue
                    );

                }
            );


        if (selectedItem) {

            valueElement.textContent =
                getLabel(selectedItem);

        } else {

            valueElement.textContent =
                placeholder;

        }

    }


    trigger.addEventListener(
        'click',
        function () {

            const isOpen =
                element.classList.contains(
                    'open'
                );


            document
                .querySelectorAll(
                    '.searchable-select.open'
                )
                .forEach(
                    function (dropdown) {

                        dropdown.classList.remove(
                            'open'
                        );

                    }
                );


            if (!isOpen) {

                element.classList.add(
                    'open'
                );


                searchInput.value = '';


                renderOptions();


                setTimeout(
                    function () {

                        searchInput.focus();

                    },
                    50
                );

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


    optionsContainer.addEventListener(
        'click',
        function (event) {

            const option =
                event.target.closest(
                    '.searchable-select-option'
                );


            if (!option) {
                return;
            }


            const value =
                option.dataset.value;


            setValue(value);


            element.classList.remove(
                'open'
            );


            if (config.onChange) {

                const selectedItem =
                    items.find(
                        function (item) {

                            return String(
                                config.value(item)
                            ) === String(value);

                        }
                    );


                config.onChange(
                    selectedItem
                );

            }

        }
    );


    setValue(
        selectedValue
    );


    return {
        setValue: setValue
    };

}


/* =========================================================
   USER / SALES DROPDOWN
========================================================= */

const userElement =
    document.querySelector(
        '[data-name="user_id"]'
    );


const userDropdown =
    createLeadSearchableDropdown(
        userElement,
        leadUsers,
        {
            value: function (user) {
                return user.user_id;
            },

            label: function (user) {
                return user.name;
            },

            description: function (user) {
                return user.email || '';
            },

            searchText: function (user) {

                return [
                    user.name,
                    user.email
                ]
                    .filter(Boolean)
                    .join(' ');

            }
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
    createLeadSearchableDropdown(
        customerElement,
        leadCustomers,
        {
            value: function (customer) {
                return customer.customer_id;
            },

            label: function (customer) {
                return customer.customer_name;
            },

            description: function (customer) {

                return [
                    customer.customer_code,
                    customer.phone,
                    customer.email
                ]
                    .filter(Boolean)
                    .join(' • ');

            },

            searchText: function (customer) {

                return [
                    customer.customer_name,
                    customer.customer_code,
                    customer.phone,
                    customer.email
                ]
                    .filter(Boolean)
                    .join(' ');

            },

            onChange: function (customer) {

                if (!customer) {
                    return;
                }


                /*
                | Only use customer data as default.
                | User can still edit the values.
                */

                const phoneInput =
                    document.getElementById(
                        'phone'
                    );


                const emailInput =
                    document.getElementById(
                        'email'
                    );


                phoneInput.value =
                    customer.phone || '';


                emailInput.value =
                    customer.email || '';

            }
        }
    );


/* =========================================================
   CLOSE DROPDOWN WHEN CLICKING OUTSIDE
========================================================= */

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
                    function (dropdown) {

                        dropdown.classList.remove(
                            'open'
                        );

                    }
                );

        }

    }
);

</script>

@endsection