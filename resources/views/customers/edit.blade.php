@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')

<div class="page-head">

    <div>

        <div class="breadcrumb">

            <a href="{{ route('customers.index') }}">
                Customers
            </a>

            <span>/</span>

            <a href="{{ route('customers.show', $customer) }}">
                {{ $customer->customer_code }}
            </a>

            <span>/</span>

            <span>Edit</span>

        </div>

        <h1>Edit Customer</h1>

        <p>
            Update customer information
        </p>

    </div>

</div>


@if($errors->any())

<div class="alert error">

    <strong>
        Please check the following:
    </strong>

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

            <h3>Customer Information</h3>

            <p>
                Update the customer information below.
            </p>

        </div>

    </div>


    <div class="card-body">

        <form
            action="{{ route('customers.update', $customer) }}"
            method="POST">

            @csrf

            @method('PUT')


            <div class="form-grid">


                {{-- Customer Code --}}

                <div class="form-group">

                    <label>
                        Customer Code
                    </label>

                    <input
                        type="text"
                        value="{{ $customer->customer_code }}"
                        disabled>

                    <small>
                        Customer code cannot be changed.
                    </small>

                </div>


                {{-- Customer Name --}}

                <div class="form-group">

                    <label>
                        Customer Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="customer_name"
                        value="{{ old('customer_name', $customer->customer_name) }}"
                        required>

                </div>


                {{-- Customer Type --}}

                <div class="form-group">

                    <label>
                        Customer Type
                    </label>

                    <select name="customer_type">

                        <option value="">
                            Select Customer Type
                        </option>

                        <option
                            value="Hospital"
                            @selected(old('customer_type', $customer->customer_type) === 'Hospital')
                            >
                            Hospital
                        </option>

                        <option
                            value="Clinic"
                            @selected(old('customer_type', $customer->customer_type) === 'Clinic')
                            >
                            Clinic
                        </option>

                        <option
                            value="Distributor"
                            @selected(old('customer_type', $customer->customer_type) === 'Distributor')
                            >
                            Distributor
                        </option>

                        <option
                            value="Laboratory"
                            @selected(old('customer_type', $customer->customer_type) === 'Laboratory')
                            >
                            Laboratory
                        </option>

                        <option
                            value="Government"
                            @selected(old('customer_type', $customer->customer_type) === 'Government')
                            >
                            Government
                        </option>

                        <option
                            value="Other"
                            @selected(old('customer_type', $customer->customer_type) === 'Other')
                            >
                            Other
                        </option>

                    </select>

                </div>


                {{-- Status --}}

                <div class="form-group">

                    <label>
                        Status
                        <span class="required">*</span>
                    </label>

                    <select name="status" required>

                        <option value="active"
                            @selected(old('status', $customer->status) === 'active')
                            >
                            Active
                        </option>

                        <option value="inactive"
                            @selected(old('status', $customer->status) === 'inactive')
                            >
                            Inactive
                        </option>

                    </select>

                </div>


                {{-- Phone --}}

                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone', $customer->phone) }}">

                </div>


                {{-- Email --}}

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $customer->email) }}">

                </div>


                {{-- Province --}}

                <div class="form-group">

                    <label>
                        Province
                    </label>

                    <div
                        class="searchable-select"
                        data-name="province"
                        data-placeholder="Search province...">

                        <input
                            type="hidden"
                            name="province"
                            value="{{ old('province', $customer->province) }}">

                        <button
                            type="button"
                            class="searchable-select-trigger">

                            <span class="searchable-select-value">
                                {{ old('province', $customer->province) ?: 'Select Province' }}
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
                                autocomplete="off">

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
                        data-name="city"
                        data-placeholder="Search city...">

                        <input
                            type="hidden"
                            name="city"
                            value="{{ old('city', $customer->city) }}">

                        <button
                            type="button"
                            class="searchable-select-trigger">

                            <span class="searchable-select-value">
                                {{ old('city', $customer->city) ?: 'Select City' }}
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
                                autocomplete="off">

                            <div class="searchable-select-options"></div>

                        </div>

                    </div>

                </div>


                {{-- Address --}}

                <div class="form-group full">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="4">{{ old('address', $customer->address) }}</textarea>

                </div>

            </div>


            <div class="form-actions">

                <a
                    href="{{ route('customers.show', $customer) }}"
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

    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');

    const currentProvince = @json(old('province', $customer - > province));
    const currentCity = @json(old('city', $customer - > city));


    Object.keys(wilayahIndonesia).forEach(province => {

        const option = document.createElement('option');

        option.value = province;
        option.textContent = province;

        if (province === currentProvince) {
            option.selected = true;
        }

        provinceSelect.appendChild(option);

    });


    function loadCities(province, selectedCity = '') {

        citySelect.innerHTML = '';

        const defaultOption = document.createElement('option');

        defaultOption.value = '';
        defaultOption.textContent = 'Select City';

        citySelect.appendChild(defaultOption);


        if (!province || !wilayahIndonesia[province]) {
            return;
        }


        wilayahIndonesia[province].forEach(city => {

            const option = document.createElement('option');

            option.value = city;
            option.textContent = city;

            if (city === selectedCity) {
                option.selected = true;
            }

            citySelect.appendChild(option);

        });

    }


    loadCities(currentProvince, currentCity);


    provinceSelect.addEventListener('change', function() {

        loadCities(this.value);

    });
</script>

@endsection