@extends('layouts.app')

@section('content')



<div class="delivery-form-page">

    {{-- Header --}}
    <div class="delivery-form-header">
        <div>
            <h2>Create Delivery</h2>
            <p>Register a new product delivery for a sales order.</p>
        </div>

        <a href="{{ route('deliveries.index') }}"
           class="delivery-form-btn delivery-form-btn-secondary">
            Back to Deliveries
        </a>
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="delivery-form-alert">
            <strong>Please correct the following errors:</strong>

            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="delivery-form-card">

        <form action="{{ route('deliveries.store') }}"
              method="POST">

            @csrf

            {{-- Sales Order --}}
            <div class="delivery-form-group">
                <label for="sales_order_id">
                    Sales Order <span>*</span>
                </label>

                <select
                    name="sales_order_id"
                    id="sales_order_id"
                    class="delivery-form-control @error('sales_order_id') is-invalid @enderror"
                    required>

                    <option value="">-- Select Sales Order --</option>

                    @foreach ($salesOrders as $salesOrder)
                        <option
                            value="{{ $salesOrder->sales_order_id }}"
                            {{ old('sales_order_id') == $salesOrder->sales_order_id ? 'selected' : '' }}>

                            {{ $salesOrder->sales_order_number
                                ?? $salesOrder->sales_order_id }}

                        </option>
                    @endforeach

                </select>

                @error('sales_order_id')
                    <span class="delivery-form-error">
                        {{ $message }}
                    </span>
                @enderror

                @if ($salesOrders->isEmpty())
                    <span class="delivery-form-error">
                        Belum ada Sales Order yang tersedia.
                        Buat Sales Order terlebih dahulu.
                    </span>
                @endif
            </div>

            {{-- Delivery Number --}}
            <div class="delivery-form-group">
                
                <div class="delivery-form-group">
                    <label for="delivery_number">Delivery Number</label>

                    <input
                        type="text"
                        id="delivery_number"
                        class="delivery-form-control"
                        value="{{ $deliveryNumber }}"
                        readonly>

                    <small class="delivery-form-help">
                        Delivery number dibuat otomatis oleh sistem.
                    </small>
                </div>

                <small class="delivery-form-help">
                    Masukkan nomor pengiriman yang unik.
                </small>

                @error('delivery_number')
                    <span class="delivery-form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Date and Status --}}
            <div class="delivery-form-row">

                <div class="delivery-form-group">
                    <label for="delivery_date">
                        Delivery Date
                    </label>

                    <input
                        type="date"
                        name="delivery_date"
                        id="delivery_date"
                        class="delivery-form-control @error('delivery_date') is-invalid @enderror"
                        value="{{ old('delivery_date') }}">

                    @error('delivery_date')
                        <span class="delivery-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="delivery-form-group">
                    <label for="status">
                        Status <span>*</span>
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="delivery-form-control @error('status') is-invalid @enderror"
                        required>

                        <option value="pending"
                            {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>

                        <option value="in_transit"
                            {{ old('status') == 'in_transit' ? 'selected' : '' }}>
                            In Transit
                        </option>

                        <option value="delivered"
                            {{ old('status') == 'delivered' ? 'selected' : '' }}>
                            Delivered
                        </option>

                        <option value="cancelled"
                            {{ old('status') == 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>

                    </select>

                    @error('status')
                        <span class="delivery-form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

            </div>

            {{-- Delivery Address --}}
            <div class="delivery-form-group">
                <label for="delivery_address">
                    Delivery Address
                </label>

                <textarea
                    name="delivery_address"
                    id="delivery_address"
                    class="delivery-form-control @error('delivery_address') is-invalid @enderror"
                    rows="3"
                    placeholder="Masukkan alamat tujuan pengiriman">{{ old('delivery_address') }}</textarea>

                @error('delivery_address')
                    <span class="delivery-form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Notes --}}
            <div class="delivery-form-group">
                <label for="notes">
                    Notes
                </label>

                <textarea
                    name="notes"
                    id="notes"
                    class="delivery-form-control @error('notes') is-invalid @enderror"
                    rows="3"
                    placeholder="Tambahkan catatan pengiriman jika diperlukan">{{ old('notes') }}</textarea>

                @error('notes')
                    <span class="delivery-form-error">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="delivery-form-actions">

                <a href="{{ route('deliveries.index') }}"
                   class="delivery-form-btn delivery-form-btn-secondary">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="delivery-form-btn delivery-form-btn-primary"
                    {{ $salesOrders->isEmpty() ? 'disabled' : '' }}>
                    Save Delivery
                </button>

            </div>

        </form>

    </div>

</div>

<style>
    .delivery-form-page {
        padding: 24px;
    }

    .delivery-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .delivery-form-header h2 {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #1f2937;
    }

    .delivery-form-header p {
        margin: 6px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .delivery-form-card {
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 24px;
    }

    .delivery-form-group {
        margin-bottom: 20px;
    }

    .delivery-form-group label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .delivery-form-group label span {
        color: #dc2626;
    }

    .delivery-form-control {
        display: block;
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        background: #fff;
        color: #374151;
        font-size: 14px;
        box-sizing: border-box;
    }

    .delivery-form-control:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .delivery-form-control.is-invalid {
        border-color: #dc2626;
    }

    .delivery-form-error {
        display: block;
        margin-top: 6px;
        color: #dc2626;
        font-size: 12px;
    }

    .delivery-form-help {
        display: block;
        margin-top: 6px;
        color: #6b7280;
        font-size: 12px;
    }

    .delivery-form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .delivery-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .delivery-form-btn {
        display: inline-block;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .delivery-form-btn-primary {
        color: #fff;
        background: #16805f;
        border-color: #16805f;
    }

    .delivery-form-btn-primary:hover {
        color: #fff;
        background: #15966f;
    }

    .delivery-form-btn-secondary {
        color: #374151;
        background: #fff;
    }

    .delivery-form-btn-secondary:hover {
        background: #f9fafb;
    }

    .delivery-form-alert {
        margin-bottom: 20px;
        padding: 14px 16px;
        border: 1px solid #fecaca;
        border-radius: 6px;
        color: #991b1b;
        background: #fef2f2;
    }

    @media (max-width: 768px) {
        .delivery-form-row {
            grid-template-columns: 1fr;
            gap: 0;
        }

        .delivery-form-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .delivery-form-card {
            padding: 16px;
        }
    }
</style>
@endsection