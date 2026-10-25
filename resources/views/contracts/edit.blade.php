@extends('layouts.app')

@section('title', 'Edit Contract')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Contract</h1>
        <p>Update contract information and manage its document.</p>
    </div>

    <div class="actions">
        <a href="{{ route('contracts.show', $contract->contract_id) }}"
           class="btn secondary">
            Back to Contract Details
        </a>
    </div>
</div>

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

@if(session('error'))
    <div class="alert error">
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-head">
        <div>
            <h3>Contract Information</h3>
            <p>Update the details of the selected contract.</p>
        </div>
    </div>

    <div class="card-body">

        <form action="{{ route('contracts.update', $contract->contract_id) }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- Contract Number --}}
                <div class="form-group">
                    <label>Contract Number</label>

                    <input type="text"
                           value="{{ $contract->contract_number }}"
                           readonly>

                    <small class="muted">
                        Automatically generated and cannot be changed.
                    </small>
                </div>

                {{-- Sales Order --}}
                <div class="form-group">
                    <label for="sales_order_id">
                        Sales Order <span>*</span>
                    </label>

                    <select name="sales_order_id"
                            id="sales_order_id"
                            required>

                        @foreach($salesOrders as $salesOrder)
                            <option
                                value="{{ $salesOrder->sales_order_id }}"
                                @selected(
                                    old(
                                        'sales_order_id',
                                        $contract->sales_order_id
                                    ) == $salesOrder->sales_order_id
                                )>

                                {{ $salesOrder->sales_order_number
                                    ?? $salesOrder->sales_order_id }}

                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Contract Type --}}
                <div class="form-group">
                    <label for="contract_type">Contract Type</label>

                    <select name="contract_type" id="contract_type">
                        <option value="">Select Contract Type</option>

                        @foreach([
                            'Sales Contract',
                            'Service Agreement',
                            'Maintenance Contract',
                            'Rental Agreement',
                            'Distribution Agreement',
                            'Other'
                        ] as $type)

                            <option
                                value="{{ $type }}"
                                @selected(
                                    old(
                                        'contract_type',
                                        $contract->contract_type
                                    ) === $type
                                )>

                                {{ $type }}

                            </option>

                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="form-group">
                    <label for="status">Status <span>*</span></label>

                    <select name="status" id="status" required>
                        @foreach([
                            'draft',
                            'active',
                            'expired',
                            'terminated'
                        ] as $status)

                            <option
                                value="{{ $status }}"
                                @selected(
                                    old('status', $contract->status)
                                    === $status
                                )>

                                {{ ucfirst($status) }}

                            </option>

                        @endforeach
                    </select>
                </div>

                {{-- Start Date --}}
                <div class="form-group">
                    <label for="start_date">
                        Start Date <span>*</span>
                    </label>

                    <input type="date"
                           name="start_date"
                           id="start_date"
                           value="{{ old(
                               'start_date',
                               $contract->start_date?->format('Y-m-d')
                           ) }}"
                           required>
                </div>

                {{-- End Date --}}
                <div class="form-group">
                    <label for="end_date">End Date</label>

                    <input type="date"
                           name="end_date"
                           id="end_date"
                           value="{{ old(
                               'end_date',
                               $contract->end_date?->format('Y-m-d')
                           ) }}">
                </div>

                {{-- Document --}}
                <div class="form-group full">
                    <label>Current Contract Document</label>

                    @if($contract->document_path)
                        <div class="contract-current-document">
                            <div>
                                <strong>PDF document available</strong>
                                <p class="muted">
                                    Upload a new PDF only if you want
                                    to replace the existing document.
                                </p>
                            </div>

                            <a href="{{ route(
                                'contracts.download',
                                $contract->contract_id
                            ) }}"
                               class="btn secondary">
                                Download Current Document
                            </a>
                        </div>
                    @else
                        <p class="muted">
                            No document has been uploaded.
                        </p>
                    @endif
                </div>

                <div class="form-group full">
                    <label for="document">
                        Replace Contract Document (PDF)
                    </label>

                    <input type="file"
                           name="document"
                           id="document"
                           accept=".pdf,application/pdf">

                    <small class="muted">
                        PDF only, maximum 5 MB. Leave empty to keep
                        the existing document.
                    </small>
                </div>

            </div>

            <div class="form-actions">
                <a href="{{ route(
                    'contracts.show',
                    $contract->contract_id
                ) }}"
                   class="btn secondary">
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    Save Changes
                </button>
            </div>

        </form>

    </div>
</div>

@endsection