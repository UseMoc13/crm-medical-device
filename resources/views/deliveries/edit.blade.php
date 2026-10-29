@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <h1>Edit Delivery</h1>
        <p>Perbarui informasi pengiriman.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terdapat kesalahan:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('deliveries.update', $delivery->delivery_id) }}"
          method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="delivery_number">Delivery Number</label>
            <input
                type="text"
                id="delivery_number"
                class="form-control"
                value="{{ $delivery->delivery_number }}"
                readonly
            >
            <small>Nomor delivery dibuat otomatis oleh sistem.</small>
        </div>

        <div class="form-group">
            <label for="sales_order_id">Sales Order</label>
            <select
                name="sales_order_id"
                id="sales_order_id"
                class="form-control"
                required
            >
                <option value="">-- Pilih Sales Order --</option>

                @foreach ($salesOrders as $salesOrder)
                    <option
                        value="{{ $salesOrder->sales_order_id }}"
                        {{ old(
                            'sales_order_id',
                            $delivery->sales_order_id
                        ) == $salesOrder->sales_order_id
                            ? 'selected'
                            : '' }}
                    >
                        {{ $salesOrder->sales_order_number }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="delivery_date">Delivery Date</label>
            <input
                type="date"
                name="delivery_date"
                id="delivery_date"
                class="form-control"
                value="{{ old(
                    'delivery_date',
                    $delivery->delivery_date
                        ? $delivery->delivery_date->format('Y-m-d')
                        : ''
                ) }}"
            >
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select
                name="status"
                id="status"
                class="form-control"
                required
            >
                @foreach ([
                    'pending' => 'Pending',
                    'in_transit' => 'In Transit',
                    'delivered' => 'Delivered',
                    'cancelled' => 'Cancelled'
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        {{ old('status', $delivery->status) === $value
                            ? 'selected'
                            : '' }}
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="delivery_address">Delivery Address</label>
            <textarea
                name="delivery_address"
                id="delivery_address"
                class="form-control"
                rows="3"
            >{{ old(
                'delivery_address',
                $delivery->delivery_address
            ) }}</textarea>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea
                name="notes"
                id="notes"
                class="form-control"
                rows="3"
            >{{ old('notes', $delivery->notes) }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>

            <a href="{{ route('deliveries.index') }}"
               class="btn btn-secondary">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection