
@extends('layouts.app')

@section('title', 'Serial Numbers')

@section('content')

<div class="page-head">

    <div>
        <h1>Serial Numbers</h1>

        <p>
            Manage medical device serial numbers, product assignments,
            and device registration records.
        </p>
    </div>

    <div class="actions">
        <a href="{{ url('/serial-numbers/create') }}" class="btn primary">
            + New Serial Number
        </a>
    </div>

</div>


{{-- =====================================================
     NOTIFICATIONS
====================================================== --}}

@if(session('success'))
    <div class="alert success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif


{{-- =====================================================
     MAIN CARD
====================================================== --}}

<div class="card">

    <div class="card-head">

        <div>
            <h3>Serial Number List</h3>

            <p>
                {{ method_exists($serialNumbers ?? [], 'total')
                    ? $serialNumbers->total()
                    : (isset($serialNumbers) ? count($serialNumbers) : 0)
                }}
                serial numbers found
            </p>
        </div>

    </div>


    <div class="card-body">

        {{-- =================================================
             FILTER BAR
        ================================================== --}}

        <form
            method="GET"
            action="{{ url('/serial-numbers') }}"
            class="serial-filter-bar"
        >

            {{-- Search --}}

            <div class="serial-search">

                <span class="serial-search-icon">⌕</span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search serial number, product, or customer..."
                    autocomplete="off"
                >

                @if(request('search'))
                <a
                    href="{{ route('serial-numbers.index', request()->except('search', 'page')) }}"
                    class="serial-search-clear"
                    title="Clear search"
                    aria-label="Clear search"
                >
                
                </a>
                @endif

            </div>


            {{-- Status --}}

            <div class="serial-filter-select">

                <select name="status">

                    <option value="">All Statuses</option>

                    @foreach(($statuses ?? []) as $statusOption)

                        <option
                            value="{{ $statusOption }}"
                            @selected(request('status') === $statusOption)
                        >
                            {{ ucwords(str_replace('_', ' ', $statusOption)) }}
                        </option>

                    @endforeach

                    {{-- Fallback untuk preview UI tanpa controller --}}

                    @if(empty($statuses))
                        <option value="available" @selected(request('status') === 'available')>
                            Available
                        </option>

                        <option value="assigned" @selected(request('status') === 'assigned')>
                            Assigned
                        </option>

                        <option value="installed" @selected(request('status') === 'installed')>
                            Installed
                        </option>

                        <option value="in_service" @selected(request('status') === 'in_service')>
                            In Service
                        </option>
                    @endif

                </select>

            </div>


            {{-- Product --}}

            <div class="serial-filter-select">

                <select name="product_id">

                    <option value="">All Products</option>

                    @foreach(($products ?? []) as $product)

                        <option
                            value="{{ $product->product_id }}"
                            @selected(request('product_id') == $product->product_id)
                        >
                            {{ $product->product_code }} — {{ $product->product_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Customer --}}

            <div class="serial-filter-select">

                <select name="customer_id">

                    <option value="">All Customers</option>

                    @foreach(($customers ?? []) as $customer)

                        <option
                            value="{{ $customer->customer_id }}"
                            @selected(request('customer_id') == $customer->customer_id)
                        >
                            {{ $customer->customer_name }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- Sort --}}

            <div class="serial-filter-select">

                <select name="sort">

                    <option value="created_at" @selected(($sort ?? 'created_at') === 'created_at')>
                        Created Date
                    </option>

                    <option value="serial_number" @selected(($sort ?? 'created_at') === 'serial_number')>
                        Serial Number
                    </option>

                    <option value="product_name" @selected(($sort ?? 'created_at') === 'product_name')>
                        Product Name
                    </option>

                    <option value="purchase_date" @selected(($sort ?? 'created_at') === 'purchase_date')>
                        Purchase Date
                    </option>

                    <option value="status" @selected(($sort ?? 'created_at') === 'status')>
                        Status
                    </option>

                </select>

            </div>


            {{-- Sort Direction --}}

            <div class="serial-filter-select sort-direction">

                <select name="direction">

                    <option value="asc" @selected(($direction ?? 'desc') === 'asc')>
                        ↑ Ascending
                    </option>

                    <option value="desc" @selected(($direction ?? 'desc') === 'desc')>
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply --}}

            <button type="submit" class="btn serial-filter-button">
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'status',
                'product_id',
                'customer_id',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ url('/serial-numbers') }}"
                    class="btn serial-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =================================================
             ACTIVE FILTER SUMMARY
        ================================================== --}}

        @if(
            request('search') ||
            request('status') ||
            request('product_id') ||
            request('customer_id')
        )

            <div class="serial-filter-summary">

                <span>Showing filtered results</span>

                @if(request('search'))
                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                    </span>
                @endif

                @if(request('status'))
                    <span class="filter-chip">
                        Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                    </span>
                @endif

                @if(request('product_id'))
                    <span class="filter-chip">Product selected</span>
                @endif

                @if(request('customer_id'))
                    <span class="filter-chip">Customer selected</span>
                @endif

            </div>

        @endif


        {{-- =================================================
             SERIAL NUMBER TABLE
        ================================================== --}}

        <div class="table-wrap serial-table-wrap">
            <table class="serial-table">

                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-serial-number">Serial Number</th>
                        <th class="col-product">Product</th>
                        <th class="col-customer">Customer</th>
                        <th class="col-purchase-date">Purchase Date</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse(($serialNumbers ?? []) as $serial)

                        <tr>

                            <td class="col-number">
                                {{ $serialNumbers->firstItem() + $loop->index }}
                            </td>

                            {{-- Serial Number --}}

                            <td>

                                <a
                                    href="{{ url('/serial-numbers/' . $serial->serial_number_id) }}"
                                    class="serial-number-link"
                                >
                                    <strong>{{ $serial->serial_number }}</strong>
                                </a>

                                <div class="muted">
                                    Registered
                                    {{ $serial->created_at
                                        ? \Carbon\Carbon::parse($serial->created_at)->format('d M Y')
                                        : '-'
                                    }}
                                </div>

                            </td>


                            {{-- Product --}}

                            <td>

                                <div class="serial-product-name">
                                    {{ $serial->product_name ?? 'Unknown Product' }}
                                </div>

                                <div class="muted">
                                    {{ $serial->product_code ?? '-' }}
                                </div>

                            </td>


                            {{-- Customer --}}

                            <td>

                                @if(!empty($serial->customer_name))

                                    <span class="serial-customer-name">
                                        {{ $serial->customer_name }}
                                    </span>

                                @else

                                    <span class="serial-unassigned">
                                        Not assigned
                                    </span>

                                @endif

                            </td>


                            {{-- Purchase Date --}}

                            <td>

                                @if($serial->purchase_date)

                                    {{ \Carbon\Carbon::parse($serial->purchase_date)->format('d M Y') }}

                                @else

                                    <span class="muted">Not recorded</span>

                                @endif

                            </td>


                            {{-- Status --}}

                            <td>

                                @php
                                    $statusValue = strtolower(
                                        str_replace(' ', '_', $serial->status ?? 'unknown')
                                    );
                                @endphp

                                <span class="serial-status-badge serial-status-{{ \Illuminate\Support\Str::slug($statusValue) }}">
                                    {{ ucwords(str_replace('_', ' ', $serial->status ?? 'Unknown')) }}
                                </span>

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="{{ url('/serial-numbers/' . $serial->serial_number_id) }}"
                                        class="action-btn view"
                                        title="View Serial Number"
                                        aria-label="View Serial Number"
                                    >
                                        👁
                                    </a>

                                    <a
                                        href="{{ url('/serial-numbers/' . $serial->serial_number_id . '/edit') }}"
                                        class="action-btn edit"
                                        title="Edit Serial Number"
                                        aria-label="Edit Serial Number"
                                    >
                                        ✎
                                    </a>

                                    <form
                                        action="{{ url('/serial-numbers/' . $serial->serial_number_id) }}"
                                        method="POST"
                                        class="serial-delete-form"
                                        data-serial-number="{{ $serial->serial_number }}"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete Serial Number"
                                            aria-label="Delete Serial Number"
                                        >
                                            🗑
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6">

                                <div class="empty">

                                    <strong>No serial numbers found.</strong>

                                    <p>
                                        Register a new device or adjust your
                                        search and filters.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}

        @if(
            isset($serialNumbers) &&
            is_object($serialNumbers) &&
            method_exists($serialNumbers, 'hasPages') &&
            $serialNumbers->hasPages()
        )

            <div class="pagination">
                {{ $serialNumbers->links() }}
            </div>

        @endif
        <div class="table-footer">
                    <div>
                        Showing {{ $serialNumbers->firstItem() ?? 0 }}
                        to {{ $serialNumbers->lastItem() ?? 0 }}
                        of {{ $serialNumbers->total() }} Serial Numbers
                    </div>
    </div>

</div>


{{-- =====================================================
     DELETE CONFIRMATION MODAL
====================================================== --}}

<div
    class="serial-delete-modal"
    id="deleteSerialModal"
    aria-hidden="true"
>

    <div
        class="serial-delete-overlay"
        data-close-serial-modal
    ></div>

    <div
        class="serial-delete-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteSerialModalTitle"
    >

        <div class="serial-delete-icon">!</div>

        <div class="serial-delete-content">

            <h3 id="deleteSerialModalTitle">
                Delete Serial Number?
            </h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteSerialNumber"></strong>?
            </p>

            <span>
                A serial number linked to installation, warranty,
                service, or transaction history may not be safe to delete.
            </span>

        </div>

        <div class="serial-delete-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteSerial"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn serial-delete-confirm"
                id="confirmDeleteSerial"
            >
                Delete Serial Number
            </button>

        </div>

        

    </div>

</div>


{{-- =====================================================
     STYLES
====================================================== --}}

<style>

/* =====================================================
   FILTER BAR
===================================================== */

.serial-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.serial-search {
    position: relative;
    flex: 1 1 270px;
    min-width: 220px;
}

.serial-search input {
    width: 100%;
    height: 40px;
    box-sizing: border-box;
    padding: 0 38px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.serial-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.serial-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.serial-search-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #7d8797;
    text-decoration: none;
    font-size: 18px;
}

.serial-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECT
===================================================== */

.serial-filter-select select {
    width: 100%;
    min-width: 145px;
    height: 40px;
    padding: 0 32px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-size: 12px;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.serial-filter-select select:hover {
    border-color: #b7c0cf;
}

.serial-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.serial-filter-button,
.serial-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.serial-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.serial-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    color: #7d8797;
    font-size: 12px;
}

.filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   SERIAL NUMBER INFORMATION
===================================================== */

.serial-number-link {
    color: #223a70;
    text-decoration: none;
    transition: color .18s ease;
}

.serial-number-link:hover {
    color: #2ba7a0;
}

.serial-number-link strong {
    font-size: 13px;
    letter-spacing: .15px;
}

.serial-product-name {
    color: #34415c;
    font-size: 13px;
    font-weight: 500;
    line-height: 1.5;
}

.serial-customer-name {
    color: #34415c;
    font-size: 12px;
}

.serial-unassigned {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 6px;
    background: #f2f4f7;
    color: #8791a1;
    font-size: 11px;
}


/* =====================================================
   STATUS BADGES
===================================================== */

.serial-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef2f6;
    color: #596780;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.serial-status-available {
    background: #e8f5ef;
    color: #16805f;
}

.serial-status-assigned {
    background: #edf1fa;
    color: #385b9a;
}

.serial-status-installed {
    background: #e7f6f5;
    color: #168b85;
}

.serial-status-in-service,
.serial-status-maintenance {
    background: #fff3e7;
    color: #b97828;
}

.serial-status-inactive,
.serial-status-retired {
    background: #f0f1f4;
    color: #737d8d;
}

.serial-status-unknown {
    background: #f0f1f4;
    color: #737d8d;
}


/* =====================================================
   TABLE ACTIONS
===================================================== */

.table-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.table-actions form {
    display: inline-flex;
    margin: 0;
}

.table-actions .action-btn {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #e0e5ed;
    border-radius: 8px;
    background: #fff;
    color: #68758b;
    text-decoration: none;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    transition:
        color .18s ease,
        background .18s ease,
        border-color .18s ease;
}

.table-actions .action-btn.view:hover {
    background: #edf4ff;
    border-color: #cbdcf9;
    color: #3569b8;
}

.table-actions .action-btn.edit:hover {
    background: #e8f5ef;
    border-color: #c3e6d6;
    color: #16805f;
}

.table-actions .action-btn.delete:hover {
    background: #fff0f0;
    border-color: #f2caca;
    color: #c94a4a;
}


/* =====================================================
   DELETE MODAL
===================================================== */

.serial-delete-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.serial-delete-modal.open {
    display: flex;
}

.serial-delete-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 24, 42, .45);
    backdrop-filter: blur(2px);
}

.serial-delete-dialog {
    position: relative;
    width: min(420px, calc(100% - 32px));
    box-sizing: border-box;
    padding: 26px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 20px 60px rgba(23, 40, 79, .20);
    animation: serialModalIn .18s ease;
}

@keyframes serialModalIn {
    from {
        opacity: 0;
        transform: translateY(8px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.serial-delete-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    border-radius: 50%;
    background: #fff0f0;
    color: #c94a4a;
    font-size: 21px;
    font-weight: 700;
}

.serial-delete-content h3 {
    margin: 0 0 8px;
    color: #17284f;
    font-size: 18px;
}

.serial-delete-content p {
    margin: 0 0 8px;
    color: #34415c;
    font-size: 13px;
    line-height: 1.6;
}

.serial-delete-content p strong {
    color: #17284f;
}

.serial-delete-content > span {
    display: block;
    color: #8a94a6;
    font-size: 12px;
    line-height: 1.6;
}

.serial-delete-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 24px;
}

.serial-delete-confirm {
    background: #c94a4a !important;
    color: #fff !important;
    border-color: #c94a4a !important;
}

.serial-delete-confirm:hover {
    background: #b83f3f !important;
    border-color: #b83f3f !important;
}


/* =====================================================
   RESPONSIVE
===================================================== */


        .table-footer {
            align-items: flex-start;
            flex-direction: column;
        }

        .table-footer {
        display: flex;
        justify-content: space-between;
        align-items: left;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 18px;
        color: #6b7280;
        font-size: 13px;
        }

@media (max-width: 1200px) {

    .serial-search {
        flex: 1 1 100%;
    }

    .serial-filter-select {
        flex: 1 1 150px;
        min-width: 0;
    }

}

@media (max-width: 700px) {

    .serial-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .serial-search,
    .serial-filter-select,
    .serial-filter-button,
    .serial-reset-button {
        width: 100%;
        box-sizing: border-box;
    }

    .serial-filter-select select {
        box-sizing: border-box;
    }

    .serial-delete-dialog {
        padding: 22px;
    }

    .serial-delete-actions {
        flex-direction: column-reverse;
    }

    .serial-delete-actions .btn {
        width: 100%;
        justify-content: center;
    }

}
.table-wrap.serial-table-wrap {
    width: 100% !important;
    overflow-x: auto !important;
    border: 1px solid #cbd5e1 !important;
    border-radius: 8px;
}

.table-wrap.serial-table-wrap table.serial-table {
    width: 100% !important;
    min-width: 1050px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: 1px solid #cbd5e1 !important;
}

.table-wrap.serial-table-wrap table.serial-table thead th {
    border: 1px solid #cbd5e1 !important;
    background-color: #f3f5f9 !important;
}

.table-wrap.serial-table-wrap table.serial-table tbody td {
    border: 1px solid #d5dce6 !important;
}

/* Garis horizontal antarbaris */
.table-wrap.serial-table-wrap table.serial-table tbody tr {
    border-bottom: 1px solid #d5dce6 !important;
}

/* Garis vertikal antar kolom */
.table-wrap.serial-table-wrap table.serial-table th,
.table-wrap.serial-table-wrap table.serial-table td {
    border-right: 1px solid #d5dce6 !important;
    border-bottom: 1px solid #d5dce6 !important;
}

/* Hover baris */
.table-wrap.serial-table-wrap table.serial-table tbody tr:hover {
    background-color: #f8fafc !important;
}

/* Hilangkan border ganda pada tepi terakhir */
.table-wrap.serial-table-wrap table.serial-table tr:last-child td {
    border-bottom: 1px solid #d5dce6 !important;

}
.col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.col-serial-number {
    min-width: 160px;
}

.col-product,
.col-customer {
    min-width: 170px;
}

.col-purchase-date {
    min-width: 140px;
    white-space: nowrap;
}

.col-status {
    min-width: 110px;
}

.col-actions {
    min-width: 120px;
    text-align: center;
}

</style>


{{-- =====================================================
     DELETE CONFIRMATION SCRIPT
====================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('deleteSerialModal');
    const serialNumber = document.getElementById('deleteSerialNumber');
    const confirmButton = document.getElementById('confirmDeleteSerial');
    const cancelButton = document.getElementById('cancelDeleteSerial');
    const closeOverlay = document.querySelector('[data-close-serial-modal]');

    if (
        !modal ||
        !serialNumber ||
        !confirmButton ||
        !cancelButton ||
        !closeOverlay
    ) {
        return;
    }

    let deleteForm = null;

    function openDeleteModal(form) {

        deleteForm = form;

        serialNumber.textContent =
            form.dataset.serialNumber || 'this serial number';

        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';

        confirmButton.focus();
    }

    function closeDeleteModal() {

        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';

        deleteForm = null;
    }

    document.querySelectorAll('.serial-delete-form').forEach(function (form) {

        form.addEventListener('submit', function (event) {

            event.preventDefault();

            openDeleteModal(form);
        });

    });

    confirmButton.addEventListener('click', function () {

        if (deleteForm) {
            deleteForm.submit();
        }

    });

    cancelButton.addEventListener('click', closeDeleteModal);
    closeOverlay.addEventListener('click', closeDeleteModal);

    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            modal.classList.contains('open')
        ) {
            closeDeleteModal();
        }

    });

});
</script>

@endsection
