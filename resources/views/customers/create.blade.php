@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')

<div class="page-head">
    <div>
        <h1>New Customer</h1>
        <p>Add a new customer to the CRM database.</p>
    </div>

    <div class="actions">
        <a href="{{ route('customers.index') }}" class="btn">
            Back to Customers
        </a>
    </div>
</div>


@if ($errors->any())

<div class="alert">

    <strong>Please check the following errors:</strong>

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

            <h3>Customer Information</h3>

            <p>
                Enter the basic information for this customer.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            method="POST"
            action="{{ route('customers.store') }}"
        >

            @csrf


            <div class="form-grid">


                {{-- Customer Name --}}

                <div class="form-group">

                    <label for="customer_name">
                        Customer Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        id="customer_name"
                        name="customer_name"
                        value="{{ old('customer_name') }}"
                        placeholder="Enter customer name"
                        required
                    >

                </div>


                {{-- Customer Type --}}

                <div class="form-group">

                    <label for="customer_type">
                        Customer Type
                    </label>

                    <select
                        id="customer_type"
                        name="customer_type"
                    >

                        <option value="">
                            Select customer type
                        </option>

                        <option
                            value="Hospital"
                            {{ old('customer_type') === 'Hospital' ? 'selected' : '' }}
                        >
                            Hospital
                        </option>

                        <option
                            value="Clinic"
                            {{ old('customer_type') === 'Clinic' ? 'selected' : '' }}
                        >
                            Clinic
                        </option>

                        <option
                            value="Laboratory"
                            {{ old('customer_type') === 'Laboratory' ? 'selected' : '' }}
                        >
                            Laboratory
                        </option>

                        <option
                            value="Distributor"
                            {{ old('customer_type') === 'Distributor' ? 'selected' : '' }}
                        >
                            Distributor
                        </option>

                        <option
                            value="Government"
                            {{ old('customer_type') === 'Government' ? 'selected' : '' }}
                        >
                            Government
                        </option>

                        <option
                            value="Other"
                            {{ old('customer_type') === 'Other' ? 'selected' : '' }}
                        >
                            Other
                        </option>

                    </select>

                </div>


                {{-- Phone --}}

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

                </div>


                {{-- Email --}}

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

                </div>


                {{-- Province --}}

                <div class="form-group">

                    <label>
                        Province
                    </label>

                    <div
                        class="searchable-select"
                        data-name="province"
                        data-placeholder="Search province..."
                    >

                        <input
                            type="hidden"
                            name="province"
                            value="{{ old('province') }}"
                        >

                        <button
                            type="button"
                            class="searchable-select-trigger"
                        >
                            <span class="searchable-select-value">
                                {{ old('province') ?: 'Select Province' }}
                            </span>

                            <span class="searchable-select-arrow">
                                ▾
                            </span>
                        </button>


                        <div class="searchable-select-menu">

                            <input
                                type="text"
                                class="searchable-select-search"
                                placeholder="Search province..."
                                autocomplete="off"
                            >

                            <div class="searchable-select-options"></div>

                        </div>

                    </div>

                </div>


                {{-- City --}}

                <div class="form-group">

                    <label>
                        City
                    </label>

                    <div
                        class="searchable-select"
                        id="cityDropdown"
                        data-name="city"
                        data-placeholder="Search city..."
                    >

                        <input
                            type="hidden"
                            name="city"
                            value="{{ old('city') }}"
                        >

                        <button
                            type="button"
                            class="searchable-select-trigger"
                            disabled
                        >

                            <span class="searchable-select-value">
                                {{ old('city') ?: 'Select City' }}
                            </span>

                            <span class="searchable-select-arrow">
                                ▾
                            </span>

                        </button>


                        <div class="searchable-select-menu">

                            <input
                                type="text"
                                class="searchable-select-search"
                                placeholder="Search city..."
                                autocomplete="off"
                            >

                            <div class="searchable-select-options"></div>

                        </div>

                    </div>

                </div>


                {{-- Status --}}

                <div class="form-group">

                    <label for="status">
                        Status <span>*</span>
                    </label>

                    <select
                        id="status"
                        name="status"
                        required
                    >

                        <option
                            value="active"
                            {{ old('status', 'active') === 'active' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            {{ old('status') === 'inactive' ? 'selected' : '' }}
                        >
                            Inactive
                        </option>

                    </select>

                </div>


                {{-- Address --}}

                <div class="form-group full">

                    <label for="address">
                        Address
                    </label>

                    <textarea
                        id="address"
                        name="address"
                        rows="4"
                        placeholder="Enter customer address"
                    >{{ old('address') }}</textarea>

                </div>

            </div>


            {{-- Form Actions --}}

            <div class="form-actions">

                <a
                    href="{{ route('customers.index') }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn primary"
                >
                    Create Customer
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

    transition: border-color .2s ease,
                box-shadow .2s ease;
}

.searchable-select-trigger:hover {
    border-color: #aeb8c8;
}

.searchable-select-trigger:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .10);
}

.searchable-select-trigger:disabled {
    background: #f4f6f9;
    color: #9aa3b2;
    cursor: not-allowed;
}

.searchable-select-arrow {
    font-size: 13px;
    color: #7d8797;
    transition: transform .2s ease;
}

.searchable-select.open
.searchable-select-arrow {
    transform: rotate(180deg);
}

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

    transition: background .15s ease;
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

</style>


<script>

const wilayahIndonesia = {

    "Aceh": [
        "Banda Aceh",
        "Langsa",
        "Lhokseumawe",
        "Sabang",
        "Subulussalam"
    ],

    "Sumatera Utara": [
        "Medan",
        "Binjai",
        "Gunungsitoli",
        "Padangsidimpuan",
        "Pematangsiantar",
        "Sibolga",
        "Tanjungbalai",
        "Tebing Tinggi"
    ],

    "Sumatera Barat": [
        "Padang",
        "Bukittinggi",
        "Padangpanjang",
        "Pariaman",
        "Payakumbuh",
        "Sawahlunto",
        "Solok"
    ],

    "Riau": [
        "Pekanbaru",
        "Dumai"
    ],

    "Kepulauan Riau": [
        "Batam",
        "Tanjungpinang"
    ],

    "Jambi": [
        "Jambi",
        "Sungai Penuh"
    ],

    "Sumatera Selatan": [
        "Palembang",
        "Lubuklinggau",
        "Pagar Alam",
        "Prabumulih"
    ],

    "Bengkulu": [
        "Bengkulu"
    ],

    "Lampung": [
        "Bandar Lampung",
        "Metro"
    ],

    "Bangka Belitung": [
        "Pangkalpinang"
    ],

    "DKI Jakarta": [
        "Jakarta Barat",
        "Jakarta Pusat",
        "Jakarta Selatan",
        "Jakarta Timur",
        "Jakarta Utara"
    ],

    "Jawa Barat": [
        "Bandung",
        "Bekasi",
        "Bogor",
        "Cimahi",
        "Cirebon",
        "Depok",
        "Sukabumi",
        "Tasikmalaya",
        "Banjar"
    ],

    "Jawa Tengah": [
        "Semarang",
        "Magelang",
        "Pekalongan",
        "Salatiga",
        "Surakarta",
        "Tegal"
    ],

    "DI Yogyakarta": [
        "Yogyakarta"
    ],

    "Jawa Timur": [
        "Surabaya",
        "Batu",
        "Blitar",
        "Kediri",
        "Madiun",
        "Malang",
        "Mojokerto",
        "Pasuruan",
        "Probolinggo"
    ],

    "Banten": [
        "Cilegon",
        "Serang",
        "Tangerang",
        "Tangerang Selatan"
    ],

    "Bali": [
        "Denpasar"
    ],

    "Nusa Tenggara Barat": [
        "Mataram",
        "Bima"
    ],

    "Nusa Tenggara Timur": [
        "Kupang"
    ],

    "Kalimantan Barat": [
        "Pontianak",
        "Singkawang"
    ],

    "Kalimantan Tengah": [
        "Palangka Raya"
    ],

    "Kalimantan Selatan": [
        "Banjarmasin",
        "Banjarbaru"
    ],

    "Kalimantan Timur": [
        "Balikpapan",
        "Bontang",
        "Samarinda"
    ],

    "Kalimantan Utara": [
        "Tarakan"
    ],

    "Sulawesi Utara": [
        "Bitung",
        "Kotamobagu",
        "Manado",
        "Tomohon"
    ],

    "Sulawesi Tengah": [
        "Palu"
    ],

    "Sulawesi Selatan": [
        "Makassar",
        "Palopo",
        "Parepare"
    ],

    "Sulawesi Tenggara": [
        "Baubau",
        "Kendari"
    ],

    "Gorontalo": [
        "Gorontalo"
    ],

    "Maluku": [
        "Ambon",
        "Tual"
    ],

    "Maluku Utara": [
        "Ternate",
        "Tidore Kepulauan"
    ],

    "Papua": [
        "Jayapura"
    ],

    "Papua Barat": [
        "Manokwari",
        "Sorong"
    ]

};


/* =========================================================
   SEARCHABLE DROPDOWN
========================================================= */

function createSearchableDropdown(element, items) {

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
            items.filter(item =>
                item
                    .toLowerCase()
                    .includes(search)
            );


        if (filtered.length === 0) {

            const empty =
                document.createElement('div');

            empty.className =
                'searchable-select-empty';

            empty.textContent =
                'No results found';

            optionsContainer.appendChild(empty);

            return;
        }


        filtered.forEach(item => {

            const option =
                document.createElement('div');

            option.className =
                'searchable-select-option';

            option.textContent = item;

            if (
                hiddenInput.value === item
            ) {

                option.classList.add(
                    'selected'
                );

            }


            option.addEventListener(
                'click',
                function () {

                    hiddenInput.value = item;

                    valueDisplay.textContent =
                        item;

                    element.classList.remove(
                        'open'
                    );

                    searchInput.value = '';

                    renderOptions();

                    element.dispatchEvent(
                        new CustomEvent(
                            'valueChanged',
                            {
                                detail: item
                            }
                        )
                    );

                }
            );


            optionsContainer.appendChild(
                option
            );

        });

    }


    trigger.addEventListener(
        'click',
        function () {

            if (trigger.disabled) {
                return;
            }

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


            if (element.classList.contains('open')) {

                searchInput.value = '';

                renderOptions();

                setTimeout(() => {

                    searchInput.focus();

                }, 50);

            }

        }
    );


    searchInput.addEventListener(
        'input',
        function () {

            renderOptions(
                this.value
            );

        }
    );


    return {

        setItems(newItems) {

            items = newItems;

            renderOptions();

        },

        setValue(value) {

            hiddenInput.value =
                value || '';

            valueDisplay.textContent =
                value || element.dataset.placeholder;

            renderOptions();

        }

    };

}


/* =========================================================
   INITIALIZE PROVINCE
========================================================= */

const provinceElement =
    document.querySelector(
        '[data-name="province"]'
    );

const cityElement =
    document.querySelector(
        '[data-name="city"]'
    );


const provinceDropdown =
    createSearchableDropdown(
        provinceElement,
        Object.keys(wilayahIndonesia)
    );


/* =========================================================
   CITY DROPDOWN
========================================================= */

const cityTrigger =
    cityElement.querySelector(
        '.searchable-select-trigger'
    );

const cityDropdown =
    createSearchableDropdown(
        cityElement,
        []
    );


/* =========================================================
   LOAD CITY BASED ON PROVINCE
========================================================= */

function updateCities(province) {

    const cities =
        wilayahIndonesia[province] || [];

    cityDropdown.setItems(
        cities
    );


    cityElement
        .querySelector(
            'input[type="hidden"]'
        )
        .value = '';


    cityElement
        .querySelector(
            '.searchable-select-value'
        )
        .textContent =
            cities.length
                ? 'Select City'
                : 'Select Province First';


    cityTrigger.disabled =
        cities.length === 0;

}


/* =========================================================
   PROVINCE CHANGE
========================================================= */

provinceElement.addEventListener(
    'valueChanged',
    function (event) {

        updateCities(
            event.detail
        );

    }
);


/* =========================================================
   CLOSE DROPDOWN WHEN CLICK OUTSIDE
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