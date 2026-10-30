@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')

<div class="page-head">

    <div>
        <h1>Warehouses</h1>

        <p>
            Manage warehouses and product storage locations.
        </p>
    </div>

    <div class="actions">
        <a href="{{ route('warehouses.create') }}" class="btn primary">
            + New Warehouse
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


<div class="card">

    {{-- =================================================
         CARD HEADER
    ================================================== --}}

    <div class="card-head">

        <div>
            <h3>Warehouse List</h3>

            <p>
                {{ $warehouses->total() }}
                warehouses found
            </p>
        </div>

    </div>


    <div class="card-body">

        {{-- =============================================
             FILTER BAR
        ============================================== --}}

        <form
            method="GET"
            action="{{ route('warehouses.index') }}"
            class="warehouse-filter-bar"
        >

            {{-- Search --}}

            <div class="warehouse-search">

                <span class="warehouse-search-icon">
                    ⌕
                </span>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search warehouses..."
                    autocomplete="off"
                >

                @if(request('search'))

                    <a
                        href="{{ route(
                            'warehouses.index',
                            request()->except('search', 'page')
                        ) }}"
                        class="warehouse-search-clear"
                        title="Clear search"
                        aria-label="Clear search"
                    >
                        ×
                    </a>

                @endif

            </div>


            {{-- Status --}}

            <div class="warehouse-filter-select">

                <select name="status">

                    <option value="">
                        All Statuses
                    </option>

                    <option
                        value="Active"
                        @selected(request('status') === 'Active')
                    >
                        Active
                    </option>

                    <option
                        value="Inactive"
                        @selected(request('status') === 'Inactive')
                    >
                        Inactive
                    </option>

                </select>

            </div>


            {{-- Sort --}}

            <div class="warehouse-filter-select">

                <select name="sort">

                    <option
                        value="created_at"
                        @selected(($sort ?? 'created_at') === 'created_at')
                    >
                        Created Date
                    </option>

                    <option
                        value="warehouse_code"
                        @selected(($sort ?? 'created_at') === 'warehouse_code')
                    >
                        Warehouse Code
                    </option>

                    <option
                        value="warehouse_name"
                        @selected(($sort ?? 'created_at') === 'warehouse_name')
                    >
                        Warehouse Name
                    </option>

                    <option
                        value="status"
                        @selected(($sort ?? 'created_at') === 'status')
                    >
                        Status
                    </option>

                </select>

            </div>


            {{-- Sort Direction --}}

            <div class="warehouse-filter-select sort-direction">

                <select name="direction">

                    <option
                        value="asc"
                        @selected(($direction ?? 'asc') === 'asc')
                    >
                        ↑ Ascending
                    </option>

                    <option
                        value="desc"
                        @selected(($direction ?? 'asc') === 'desc')
                    >
                        ↓ Descending
                    </option>

                </select>

            </div>


            {{-- Apply --}}

            <button
                type="submit"
                class="btn warehouse-filter-button"
            >
                Filter
            </button>


            {{-- Reset --}}

            @if(request()->hasAny([
                'search',
                'status',
                'sort',
                'direction'
            ]))

                <a
                    href="{{ route('warehouses.index') }}"
                    class="btn warehouse-reset-button"
                >
                    Reset
                </a>

            @endif

        </form>


        {{-- =============================================
             ACTIVE FILTER SUMMARY
        ============================================== --}}

        @if(
            request('search') ||
            request('status')
        )

            <div class="warehouse-filter-summary">

                <span>
                    Showing filtered results
                </span>

                @if(request('search'))

                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                    </span>

                @endif

                @if(request('status'))

                    <span class="filter-chip">
                        Status: {{ request('status') }}
                    </span>

                @endif

            </div>

        @endif


        {{-- =============================================
             WAREHOUSE TABLE
        ============================================== --}}

        <div class="table-wrap warehouse-table-wrap">

            <table class="warehouse-table">

                <thead>
                    <tr>
                        <th class="col-number">No.</th>
                        <th class="col-warehouse">Warehouse</th>
                        <th class="col-address">Address</th>
                        <th class="col-inventory">Inventory Records</th>
                        <th class="col-movements">Stock Movements</th>
                        <th class="col-status">Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>


                <tbody>

                    @forelse($warehouses as $warehouse)

                        <tr>
                            <td class="col-number">
                                {{ $warehouses->firstItem() + $loop->index }}
                            </td>

                            {{-- Warehouse --}}

                            <td>

                                <a
                                    href="{{ route(
                                        'warehouses.show',
                                        $warehouse
                                    ) }}"
                                    class="warehouse-code-link"
                                >
                                    <strong>
                                        {{ $warehouse->warehouse_code }}
                                    </strong>
                                </a>

                                <div class="muted">
                                    {{ $warehouse->warehouse_name }}
                                </div>

                                <div class="muted">

                                    {{ $warehouse->created_at
                                        ? $warehouse->created_at->format('d M Y')
                                        : '-'
                                    }}

                                </div>

                            </td>


                            {{-- Address --}}

                            <td>

                                @if($warehouse->address)

                                    <span class="warehouse-address">
                                        {{ $warehouse->address }}
                                    </span>

                                @else

                                    <span class="muted">
                                        No address provided
                                    </span>

                                @endif

                            </td>


                            {{-- Inventory --}}

                            <td>
                                <span class="warehouse-count">

                                    {{ $warehouse->inventories_count ?? 0 }}

                                </span>
                            </td>


                            {{-- Stock Movements --}}

                            <td>
                                <span class="warehouse-count">

                                    {{ $warehouse->stock_movements_count ?? 0 }}

                                </span>
                            </td>


                            {{-- Status --}}

                            <td>

                                @if($warehouse->status)

                                    <span
                                        class="warehouse-status-badge
                                        warehouse-status-{{ \Illuminate\Support\Str::slug($warehouse->status) }}"
                                    >
                                        {{ $warehouse->status }}
                                    </span>

                                @else

                                    <span class="muted">-</span>

                                @endif

                            </td>


                            {{-- Actions --}}

                            <td>

                                <div class="table-actions">

                                    {{-- View --}}

                                    <a
                                        href="{{ route(
                                            'warehouses.show',
                                            $warehouse
                                        ) }}"
                                        class="action-btn view"
                                        title="View Warehouse"
                                        aria-label="View Warehouse"
                                    >
                                        👁
                                    </a>


                                    {{-- Edit --}}

                                    <a
                                        href="{{ route(
                                            'warehouses.edit',
                                            $warehouse
                                        ) }}"
                                        class="action-btn edit"
                                        title="Edit Warehouse"
                                        aria-label="Edit Warehouse"
                                    >
                                        ✎
                                    </a>


                                    {{-- Delete --}}

                                    <form
                                        action="{{ route(
                                            'warehouses.destroy',
                                            $warehouse
                                        ) }}"
                                        method="POST"
                                        class="delete-form"
                                        data-warehouse-name="{{ $warehouse->warehouse_name }}"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-btn delete"
                                            title="Delete Warehouse"
                                            aria-label="Delete Warehouse"
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

                                    <strong>
                                        No warehouses found.
                                    </strong>

                                    <p>
                                        Add a new warehouse or adjust
                                        your search and filters.
                                    </p>

                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =============================================
             PAGINATION
        ============================================== --}}

        @if($warehouses->hasPages())

            <div class="pagination">
                {{ $warehouses->links() }}
            </div>

        @endif

    </div>

</div>


{{-- =====================================================
     DELETE CONFIRMATION MODAL
====================================================== --}}

<div
    class="delete-modal"
    id="deleteWarehouseModal"
    aria-hidden="true"
>

    <div
        class="delete-modal-overlay"
        data-close-delete-modal
    ></div>


    <div
        class="delete-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteWarehouseModalTitle"
    >

        <div class="delete-modal-icon">
            !
        </div>


        <div class="delete-modal-content">

            <h3 id="deleteWarehouseModalTitle">
                Delete Warehouse?
            </h3>

            <p>
                Are you sure you want to delete
                <strong id="deleteWarehouseName"></strong>?
            </p>

            <span>
                A warehouse with inventory records or stock movement
                history cannot be deleted.
            </span>

        </div>


        <div class="delete-modal-actions">

            <button
                type="button"
                class="btn"
                id="cancelDeleteWarehouse"
            >
                Cancel
            </button>

            <button
                type="button"
                class="btn delete-confirm-button"
                id="confirmDeleteWarehouse"
            >
                Delete Warehouse
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

.warehouse-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}


/* =====================================================
   SEARCH
===================================================== */

.warehouse-search {
    position: relative;
    flex: 1 1 260px;
    min-width: 220px;
}

.warehouse-search input {
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

.warehouse-search input:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.warehouse-search-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #7d8797;
    font-size: 19px;
    pointer-events: none;
}

.warehouse-search-clear {
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

.warehouse-search-clear:hover {
    background: #edf1f5;
    color: #17284f;
}


/* =====================================================
   FILTER SELECT
===================================================== */

.warehouse-filter-select select {
    min-width: 145px;
    height: 40px;
    padding: 0 34px 0 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #34415c;
    font-size: 13px;
    cursor: pointer;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.warehouse-filter-select select:hover {
    border-color: #b7c0cf;
}

.warehouse-filter-select select:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}


/* =====================================================
   FILTER BUTTONS
===================================================== */

.warehouse-filter-button,
.warehouse-reset-button {
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.warehouse-reset-button {
    text-decoration: none;
}


/* =====================================================
   FILTER SUMMARY
===================================================== */

.warehouse-filter-summary {
    display: flex;
    align-items: center;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 15px;
    font-size: 12px;
    color: #7d8797;
}

.filter-chip {
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f5f7;
    color: #34415c;
    font-size: 11px;
}


/* =====================================================
   WAREHOUSE INFORMATION
===================================================== */

.warehouse-code-link {
    color: inherit;
    text-decoration: none;
}

.warehouse-code-link:hover {
    color: #223a70;
}

.warehouse-address {
    display: block;
    min-width: 140px;
    max-width: 260px;
    color: #596780;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.warehouse-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 5px 9px;
    border-radius: 6px;
    background: #f1f4fa;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}


/* =====================================================
   STATUS BADGES
===================================================== */

.warehouse-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 9px;
    border-radius: 6px;
    background: #eef2f6;
    color: #4d5b70;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.warehouse-status-active {
    background: #e8f5f0;
    color: #16805f;
}

.warehouse-status-inactive {
    background: #fff0f0;
    color: #a14d4d;
}


/* =====================================================
   DELETE MODAL
===================================================== */

.delete-modal {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
}

.delete-modal.open {
    display: flex;
}

.delete-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 24, 42, .45);
    backdrop-filter: blur(2px);
}

.delete-modal-dialog {
    position: relative;
    width: min(420px, calc(100% - 32px));
    box-sizing: border-box;
    background: #fff;
    border-radius: 14px;
    padding: 26px;
    box-shadow: 0 20px 60px rgba(23, 40, 79, .20);
    animation: warehouseModalIn .18s ease;
}

@keyframes warehouseModalIn {
    from {
        opacity: 0;
        transform: translateY(8px) scale(.98);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.delete-modal-icon {
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

.delete-modal-content h3 {
    margin: 0 0 8px;
    color: #17284f;
    font-size: 18px;
}

.delete-modal-content p {
    margin: 0 0 8px;
    color: #34415c;
    font-size: 13px;
    line-height: 1.6;
}

.delete-modal-content p strong {
    color: #17284f;
}

.delete-modal-content > span {
    display: block;
    color: #8a94a6;
    font-size: 12px;
    line-height: 1.6;
}

.delete-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 24px;
}

.delete-confirm-button {
    background: #c94a4a !important;
    color: #fff !important;
    border-color: #c94a4a !important;
}

.delete-confirm-button:hover {
    background: #b83f3f !important;
    border-color: #b83f3f !important;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1100px) {

    .warehouse-search {
        flex: 1 1 100%;
    }

    .warehouse-filter-select {
        flex: 1 1 150px;
    }

    .warehouse-filter-select select {
        width: 100%;
    }

}

@media (max-width: 600px) {

    .warehouse-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .warehouse-search,
    .warehouse-filter-select,
    .warehouse-filter-button,
    .warehouse-reset-button {
        width: 100%;
        box-sizing: border-box;
    }

    .warehouse-filter-select select {
        box-sizing: border-box;
    }

    .delete-modal-dialog {
        padding: 22px;
    }

    .delete-modal-actions {
        flex-direction: column-reverse;
    }

    .delete-modal-actions .btn {
        width: 100%;
        justify-content: center;
    }

}

/* =====================================================
   WAREHOUSE TABLE
===================================================== */

.warehouse-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    border: 1px solid #dce3ed;
    border-radius: 8px;
    background: #fff;
    box-sizing: border-box;
}

.warehouse-table {
    width: 100%;
    min-width: 1050px;
    margin: 0;
    border-collapse: collapse;
    border-spacing: 0;
    background: #fff;
}

/* TABLE HEADER */

.warehouse-table thead th {
    padding: 14px 12px;
    background: #f3f5f9;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
    white-space: nowrap;
    border: 1px solid #dce3ed;
}

/* TABLE BODY */

.warehouse-table tbody td {
    padding: 14px 12px;
    color: #34415c;
    font-size: 12px;
    vertical-align: middle;
    border: 1px solid #e1e6ef;
}

.warehouse-table tbody tr {
    transition: background-color 0.15s ease;
}

.warehouse-table tbody tr:hover {
    background: #f8fafc;
}

/* WAREHOUSE CODE AND NAME */

.warehouse-table .warehouse-code-link {
    color: #223a70;
    text-decoration: none;
}

.warehouse-table .warehouse-code-link strong {
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.warehouse-table .warehouse-code-link:hover {
    color: #2ba7a0;
}

.warehouse-table .muted {
    margin-top: 4px;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

/* ADDRESS */

.warehouse-table .warehouse-address {
    display: block;
    min-width: 140px;
    max-width: 260px;
    color: #4d5b70;
    font-size: 12px;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

/* INVENTORY AND STOCK MOVEMENT COUNTS */

.warehouse-table .warehouse-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef2f8;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

/* STATUS BADGES */

.warehouse-table .warehouse-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.warehouse-table .warehouse-status-active {
    background: #e8f5f0;
    color: #16805f;
}

.warehouse-table .warehouse-status-inactive {
    background: #fff0f0;
    color: #a14d4d;
}

/* EMPTY STATE */

.warehouse-table tbody td[colspan="6"] {
    padding: 0;
    text-align: center;
}

.warehouse-table .empty {
    padding: 45px 20px;
    color: #718096;
    font-size: 13px;
    text-align: center;
}

.warehouse-table .empty strong {
    color: #34415c;
    font-size: 14px;
}

.warehouse-table .empty p {
    margin: 8px 0 0;
    color: #8a94a6;
    font-size: 12px;
}

/* ACTION BUTTONS */

.warehouse-table thead th:last-child,
.warehouse-table tbody td:last-child {
    min-width: 120px;
    text-align: center;
    white-space: nowrap;
}

.warehouse-table .table-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    white-space: nowrap;
}

.warehouse-table .action-btn {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    border: 1px solid #dfe4eb;
    border-radius: 7px;
    background: #fff;
    color: #4d5b70;
    font-size: 13px;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    transition:
        background-color 0.18s ease,
        border-color 0.18s ease,
        color 0.18s ease;
}

.warehouse-table .action-btn:hover {
    background: #f4f7f9;
    border-color: #cbd3df;
    color: #17284f;
}

.warehouse-table .action-btn.view:hover {
    background: #eef7f7;
    border-color: #b8deda;
    color: #167d70;
}

.warehouse-table .action-btn.edit:hover {
    background: #f2f5fa;
    border-color: #cbd5e4;
    color: #223a70;
}

.warehouse-table .action-btn.delete:hover {
    background: #fff0f0;
    border-color: #e6b7b7;
    color: #c94a4a;
}

/* RESPONSIVE */

@media (max-width: 768px) {
    .warehouse-table {
        min-width: 1050px;
    }

}

.col-number {
    width: 55px;
    min-width: 55px;
    max-width: 55px;
    text-align: center !important;
    white-space: nowrap;
}

.col-warehouse {
    min-width: 160px;
}

.col-address {
    min-width: 220px;
}

.col-inventory,
.col-movements {
    min-width: 150px;
    text-align: center;
}

.col-status {
    min-width: 100px;
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

    const modal = document.getElementById('deleteWarehouseModal');
    const warehouseName = document.getElementById('deleteWarehouseName');
    const confirmButton = document.getElementById('confirmDeleteWarehouse');
    const cancelButton = document.getElementById('cancelDeleteWarehouse');
    const closeOverlay = document.querySelector('[data-close-delete-modal]');

    if (
        !modal ||
        !warehouseName ||
        !confirmButton ||
        !cancelButton ||
        !closeOverlay
    ) {
        return;
    }

    let deleteForm = null;

    function openDeleteModal(form) {

        deleteForm = form;

        warehouseName.textContent =
            form.dataset.warehouseName || 'this warehouse';

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

    document.querySelectorAll('.delete-form').forEach(function (form) {

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