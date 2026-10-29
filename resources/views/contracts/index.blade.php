@extends('layouts.app')

@section('title', 'Contracts')

@section('content')

<div class="page-head">
    <div>
        <h1>Contracts</h1>
        <p>Manage contracts and agreements associated with sales orders.</p>
    </div>

    <div class="actions">
        <a href="{{ route('contracts.create') }}" class="btn primary">
            + New Contract
        </a>
    </div>
</div>

{{-- Notifications --}}
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

@if($errors->any())
    <div class="alert error">
        <strong>Please correct the following errors:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Contracts Table --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Contract List</h3>
            <p>{{ $contracts->total() }} contracts found</p>
        </div>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table">
               
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Contract Number</th>
                        <th>Sales Order</th>
                        <th>Contract Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Document</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($contracts as $contract)
                        <tr>
                            <td>
                                {{ $contracts->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <strong>
                                    {{ $contract->contract_number }}
                                </strong>
                            </td>

                            <td>
                                {{ $contract->salesOrder->sales_order_number ?? '-' }}
                            </td>

                            <td>
                                {{ $contract->contract_type ?? '-' }}
                            </td>

                            <td>
                                {{ $contract->start_date?->format('d M Y') ?? '-' }}
                            </td>

                            <td>
                                {{ $contract->end_date?->format('d M Y') ?? '-' }}
                            </td>

                            <td>
                                <span class="contract-status-badge status-{{ strtolower($contract->status) }}">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </td>

                            
                            
                            
                            {{-- Actions --}}
                            <td class="contract-actions-cell">
                                <div class="table-actions">

                                    {{-- View --}}
                                    <a href="{{ route('contracts.show', $contract->contract_id) }}"
                                    class="action-btn view"
                                    title="View Contract"
                                    aria-label="View Contract">
                                        👁
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('contracts.edit', $contract->contract_id) }}"
                                    class="action-btn edit"
                                    title="Edit Contract"
                                    aria-label="Edit Contract">
                                        ✎
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('contracts.destroy', $contract->contract_id) }}"
                                        method="POST"
                                        class="delete-form contract-delete-form"
                                        data-contract-id="{{ $contract->contract_id }}"
                                        data-contract-number="{{ $contract->contract_number ?? $contract->contract_id }}">

                                        @csrf
                                        @method('DELETE')

                                    <button type="button"
                                        class="action-btn delete open-delete-contract-modal"
                                        title="Delete Contract"
                                        aria-label="Delete Contract">
                                        🗑
                                    </button>

                                    </form>

                                </div>
                            </td>

                            {{-- Document --}}
                            <td class="contract-document-cell">
                                @if ($contract->document_path)
                                    <a href="{{ route('contracts.download', $contract->contract_id) }}"
                                    class="contract-download-link">
                                        Download
                                    </a>
                                @else
                                    <span class="muted">No document</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty-state">
                                No contracts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        
        {{-- Pagination --}}
        @if($contracts->hasPages())
            <div class="pagination-wrapper">
                {{ $contracts->links() }}
            </div>
        @endif

    </div>
</div>


        {{-- =========================================================
            DELETE CONFIRMATION MODAL - CONTRACT
        ========================================================= --}}

        <div class="delete-modal"
            id="deleteContractModal"
            aria-hidden="true">

            <div class="delete-modal-overlay"
                data-close-contract-modal></div>

            <div class="delete-modal-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="deleteContractModalTitle">

                <div class="delete-modal-icon">
                    !
                </div>

                <div class="delete-modal-content">
                    <h3 id="deleteContractModalTitle">
                        Delete Contract?
                    </h3>

                    <p>
                        Are you sure you want to delete
                        <strong id="deleteContractNumber"></strong>?
                    </p>

                    <span>
                        This action cannot be undone.
                    </span>
                </div>

                <div class="delete-modal-actions">
                    <button type="button"
                        class="btn"
                        id="cancelDeleteContract">
                        Cancel
                    </button>

                    <button type="button"
                        class="btn delete-confirm-button"
                        id="confirmDeleteContract">
                        Delete Contract
                    </button>
                </div>

            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deleteContractModal');
    const contractNumber = document.getElementById('deleteContractNumber');
    const cancelButton = document.getElementById('cancelDeleteContract');
    const confirmButton = document.getElementById('confirmDeleteContract');
    const closeElements = document.querySelectorAll('[data-close-contract-modal]');

    let selectedForm = null;

    if (!modal || !contractNumber || !cancelButton || !confirmButton) {
        return;
    }

    // Membuka modal saat tombol Delete ditekan
    document.querySelectorAll('.open-delete-contract-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            selectedForm = button.closest('.contract-delete-form');

            if (!selectedForm) {
                return;
            }

            contractNumber.textContent =
                selectedForm.dataset.contractNumber || 'this contract';

            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');

            cancelButton.focus();
        });
    });

    // Menutup modal
    function closeModal() {
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        selectedForm = null;
    }

    cancelButton.addEventListener('click', closeModal);

    closeElements.forEach(function (element) {
        element.addEventListener('click', closeModal);
    });

    // Menjalankan penghapusan setelah dikonfirmasi
    confirmButton.addEventListener('click', function () {
        if (selectedForm) {
            selectedForm.submit();
        }
    });

    // Menutup modal dengan tombol Escape
    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            modal.classList.contains('show')
        ) {
            closeModal();
        }
    });
});
</script>

@endsection

<style>
/* =========================================================
   CONTRACT TABLE - COMPLETE BORDERS
========================================================= */

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.table-responsive .table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    border: 1px solid #dce3ed;
}

/* Header */
.table-responsive .table thead th {
    padding: 14px 12px;
    background: #f8f9fc;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    text-align: left;
    white-space: nowrap;

    border: 1px solid #e1e6ef;
    border-top: 1px solid #dce3ed;
    border-bottom: 1px solid #dce3ed;
}

/* Isi tabel */
.table-responsive .table tbody td {
    padding: 13px 12px;
    font-size: 12px;
    color: #17284f;
    vertical-align: middle;

    border: 1px solid #e8ecf3;
}

.table-responsive .table thead th:first-child,
.table-responsive .table tbody td:first-child {
    border-left: 1px solid #dce3ed;
}

.table-responsive .table thead th:last-child,
.table-responsive .table tbody td:last-child {
    border-right: 1px solid #dce3ed;
}

.table-responsive .table thead tr:first-child th {
    border-top: 1px solid #dce3ed;
}

.table-responsive .table tbody tr:last-child td {
    border-bottom: 1px solid #dce3ed;
}

.table-responsive .table tbody tr:hover {
    background: #f8fafc;
}

.table-responsive .table th:nth-child(8),
.table-responsive .table td:nth-child(8) {
    text-align: center;
    min-width: 140px;
}

.table-responsive .table th:nth-child(9),
.table-responsive .table td:nth-child(9) {
    text-align: center;
    min-width: 100px;
}

@media (max-width: 768px) {
    .table-responsive .table {
        min-width: 1050px;
    }
}


/* DELETE CONTRACT MODAL */
#deleteContractModal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

#deleteContractModal.show {
    display: flex;
}

#deleteContractModal .delete-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
}

#deleteContractModal .delete-modal-dialog {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 420px;
    padding: 28px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    text-align: center;
}

#deleteContractModal .delete-modal-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: #fff0f0;
    color: #dc2626;
    font-size: 26px;
    font-weight: 700;
}

#deleteContractModal .delete-modal-content h3 {
    margin: 0 0 12px;
    color: #172033;
    font-size: 20px;
}

#deleteContractModal .delete-modal-content p {
    margin-bottom: 8px;
    color: #475569;
}

#deleteContractModal .delete-modal-content strong {
    overflow-wrap: anywhere;
}

#deleteContractModal .delete-modal-content span {
    color: #7b8494;
    font-size: 13px;
}

#deleteContractModal .delete-modal-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 24px;
}

#deleteContractModal .delete-confirm-button {
    border: none;
    background: #dc2626;
    color: #fff;
}

#deleteContractModal .delete-confirm-button:hover {
    background: #b91c1c;
}

#deleteContractModal .delete-modal-actions button {
    cursor: pointer;
    padding: 10px 16px;
    border-radius: 7px;
}
</style>