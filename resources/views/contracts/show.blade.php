@extends('layouts.app')

@section('title', 'Contract Details')

@section('content')

<div class="page-head">
    <div>
        <h1>Contract Details</h1>
        <p>View contract information and manage the associated document.</p>
    </div>

    <div class="actions">
        <a href="{{ route('contracts.index') }}" class="btn secondary">
            Back to Contracts
        </a>

        <a href="{{ route('contracts.edit', $contract->contract_id) }}"
           class="btn primary">
            Edit Contract
        </a>
    </div>
</div>

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

{{-- Contract Information --}}
<div class="card">
    <div class="card-head">
        <div>
            <h3>Contract Information</h3>
            <p>General information about this contract.</p>
        </div>

        <span class="contract-status-badge">
            {{ ucfirst($contract->status) }}
        </span>
    </div>

    <div class="card-body">
        <div class="form-grid">

            <div class="form-group">
                <label>Contract Number</label>
                <div class="contract-detail-value">
                    {{ $contract->contract_number }}
                </div>
            </div>

            <div class="form-group">
                <label>Sales Order</label>
                <div class="contract-detail-value">
                    {{ $contract->salesOrder->sales_order_number
                        ?? $contract->sales_order_id
                        ?? '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Contract Type</label>
                <div class="contract-detail-value">
                    {{ $contract->contract_type ?? '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <div class="contract-detail-value">
                    {{ ucfirst($contract->status) }}
                </div>
            </div>

            <div class="form-group">
                <label>Start Date</label>
                <div class="contract-detail-value">
                    {{ $contract->start_date
                        ? $contract->start_date->format('d M Y')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>End Date</label>
                <div class="contract-detail-value">
                    {{ $contract->end_date
                        ? $contract->end_date->format('d M Y')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Created At</label>
                <div class="contract-detail-value">
                    {{ $contract->created_at
                        ? $contract->created_at->format('d M Y, H:i')
                        : '-' }}
                </div>
            </div>

            <div class="form-group">
                <label>Last Updated</label>
                <div class="contract-detail-value">
                    {{ $contract->updated_at
                        ? $contract->updated_at->format('d M Y, H:i')
                        : '-' }}
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Contract Document --}}
<div class="card contract-document-card">
    <div class="card-head">
        <div>
            <h3>Contract Document</h3>
            <p>Manage the PDF document associated with this contract.</p>
        </div>
    </div>

    <div class="card-body">
        @if($contract->document_path)
            <div class="contract-document-info">
                <div>
                    <strong>Contract PDF</strong>
                    <p>Document has been uploaded and is ready to download.</p>
                </div>

                <a href="{{ route('contracts.download', $contract->contract_id) }}"
                   class="btn primary">
                    Download Document
                </a>
            </div>
        @else
            <p class="muted">
                No contract document has been uploaded.
            </p>

            <a href="{{ route('contracts.edit', $contract->contract_id) }}"
               class="btn primary">
                Upload Document
            </a>
        @endif
    </div>
</div>

@endsection