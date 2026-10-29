@extends('layouts.app')

@section('content')

<style>
    .delivery-page {
        padding: 24px;
    }

    .delivery-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
    }

    .delivery-header h2 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #1f2937;
    }

    .delivery-header p {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .delivery-btn {
        display: inline-block;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .delivery-btn-primary {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
    }

    .delivery-btn-primary:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .delivery-btn-detail {
        color: #2563eb;
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .delivery-btn-edit {
        color: #92400e;
        background: #fffbeb;
        border-color: #fde68a;
    }

    .delivery-btn-delete {
        color: #b91c1c;
        background: #fef2f2;
        border-color: #fecaca;
    }

    .delivery-table-wrapper {
        overflow-x: auto;
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
    }

    .delivery-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .delivery-table th,
    .delivery-table td {
        padding: 14px 16px;
        border: 1px solid #d1d5db;
        text-align: left;
        vertical-align: middle;
    }

    .delivery-table th {
        background: #f9fafb;
        color: #374151;
        font-weight: 700;
        white-space: nowrap;
    }

    .delivery-table td {
        color: #4b5563;
    }

    .delivery-table tbody tr:hover {
        background: #f9fafb;
    }

    .delivery-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .delivery-status-completed {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #86efac;
    }

    .delivery-status-progress {
        color: #1d4ed8;
        background: #dbeafe;
        border: 1px solid #93c5fd;
    }

    .delivery-status-pending {
        color: #92400e;
        background: #fef3c7;
        border: 1px solid #fcd34d;
    }

    .delivery-status-cancelled {
        color: #b91c1c;
        background: #fee2e2;
        border: 1px solid #fca5a5;
    }

    .delivery-status-default {
        color: #374151;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
    }

    .delivery-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .delivery-alert {
        padding: 12px 16px;
        margin-bottom: 18px;
        border: 1px solid #86efac;
        border-radius: 6px;
        color: #166534;
        background: #f0fdf4;
    }

    .delivery-empty {
        padding: 32px !important;
        text-align: center !important;
        color: #9ca3af !important;
    }

    .delivery-pagination {
        margin-top: 20px;
    }

    .delivery-delete-form {
        display: inline;
    }
</style>

<div class="delivery-page">

    {{-- Header --}}
    <div class="delivery-header">
        <div>
            <h2>Deliveries</h2>
            <p>Manage and monitor customer product deliveries.</p>
        </div>

        <a href="{{ route('deliveries.create') }}"
           class="delivery-btn delivery-btn-primary">
            + Add Delivery
        </a>
    </div>

    {{-- Success Message --}}
    @if (session('success'))
        <div class="delivery-alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="delivery-alert"
             style="color:#991b1b;background:#fef2f2;border-color:#fecaca;">
            <strong>Terjadi kesalahan:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Delivery Table --}}
    <div class="delivery-table-wrapper">
        <table class="delivery-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Delivery Number</th>
                    <th>Sales Order</th>
                    <th>Delivery Date</th>
                    <th>Delivery Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($deliveries as $delivery)
                    @php
                        $status = strtolower(
                            str_replace(
                                [' ', '-'],
                                '_',
                                trim($delivery->status ?? '')
                            )
                        );

                        $statusClass = match ($status) {
                            'completed', 'delivered' =>
                                'delivery-status-completed',

                            'in_progress', 'in_transit', 'shipped' =>
                                'delivery-status-progress',

                            'pending', 'processing' =>
                                'delivery-status-pending',

                            'cancelled', 'canceled' =>
                                'delivery-status-cancelled',

                            default => 'delivery-status-default',
                        };
                    @endphp

                    <tr>
                        <td>
                            {{ $deliveries->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <strong>
                                {{ $delivery->delivery_number }}
                            </strong>
                        </td>

                        <td>
                            {{ $delivery->salesOrder?->sales_order_number
                                ?? '—' }}
                        </td>

                        <td>
                            {{ $delivery->delivery_date
                                ? $delivery->delivery_date->format('d M Y')
                                : '—' }}
                        </td>

                        <td>
                            {{ $delivery->delivery_address ?: '—' }}
                        </td>

                        <td>
                            <span class="delivery-status {{ $statusClass }}">
                                {{ ucwords(str_replace(
                                    ['_', '-'],
                                    ' ',
                                    $delivery->status
                                )) }}
                            </span>
                        </td>

                        <td>
                            <div class="delivery-actions">

                                <a href="{{ route(
                                    'deliveries.show',
                                    $delivery->delivery_id
                                ) }}"
                                   class="delivery-btn delivery-btn-detail">
                                    Detail
                                </a>

                                <a href="{{ route(
                                    'deliveries.edit',
                                    $delivery->delivery_id
                                ) }}"
                                   class="delivery-btn delivery-btn-edit">
                                    Edit
                                </a>

                                <form
                                    action="{{ route(
                                        'deliveries.destroy',
                                        $delivery->delivery_id
                                    ) }}"
                                    method="POST"
                                    class="delivery-delete-form"
                                    onsubmit="return confirm(
                                        'Apakah kamu yakin ingin menghapus delivery ini?'
                                    )">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="delivery-btn delivery-btn-delete">
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="delivery-empty">
                            Belum ada data delivery.
                            Klik <strong>+ Add Delivery</strong>
                            untuk menambahkan pengiriman baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="delivery-pagination">
        {{ $deliveries->links() }}
    </div>

</div>

@endsection