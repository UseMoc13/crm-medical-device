@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Create Contract</h1>

        <a href="{{ route('contracts.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('contracts.store') }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf


                <div class="mb-3">
                    <label class="form-label">Contract Number</label>
        
                    <small class="text-muted">
                        *Nomor kontrak akan dibuat secara otomatis oleh sistem.
                    </small>
                </div>

                <div class="mb-3">
                    <label for="sales_order_id" class="form-label">
                        Sales Order <span class="text-danger">*</span>
                    </label>

                    <select
                        name="sales_order_id"
                        id="sales_order_id"
                        class="form-select"
                        required
                    >
                        <option value="">-- Select Sales Order --</option>

                        @foreach ($salesOrders as $salesOrder)
                            <option
                                value="{{ $salesOrder->sales_order_id }}"
                                {{ old('sales_order_id') == $salesOrder->sales_order_id ? 'selected' : '' }}
                            >
                                {{ $salesOrder->sales_order_number ?? $salesOrder->sales_order_id }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">
                            Start Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            class="form-control"
                            value="{{ old('start_date') }}"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">
                            End Date
                        </label>

                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            class="form-control"
                            value="{{ old('end_date') }}"
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="contract_type" class="form-label">Contract Type</label>

                    <select
                        name="contract_type"
                        id="contract_type"
                        class="form-select"
                    >
                        <option value="">-- Pilih Contract Type --</option>
                        <option value="Sales Contract">Sales Contract</option>
                        <option value="Service Agreement">Service Agreement</option>
                        <option value="Maintenance Contract">Maintenance Contract</option>
                        <option value="Rental Agreement">Rental Agreement</option>
                        <option value="Distribution Agreement">Distribution Agreement</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">
                        Status <span class="text-danger">*</span>
                    </label>

                    <select name="status" id="status" class="form-select" required>
                        <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>
                            Expired
                        </option>
                        <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>
                            Terminated
                        </option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="document" class="form-label">
                        Contract Document (PDF)
                    </label>

                    <input
                        type="file"
                        name="document"
                        id="document"
                        class="form-control @error('document') is-invalid @enderror"
                        accept=".pdf,application/pdf"
                    >

                    @error('document')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted">
                        Upload dokumen kontrak dalam format PDF, maksimal 5 MB.
                    </small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Save Contract
                    </button>

                    <a href="{{ route('contracts.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection