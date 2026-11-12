@extends('layouts.app')
@section('title','Contract Details')
@section('content')
<div class="page-head"><div><h1>Contract Details</h1><p>Review contract information, agreement period, and linked documents.</p></div><div class="actions"><a href="{{ route('contracts.index') }}" class="btn">← Back to Contracts</a><a href="{{ route('contracts.edit',$contract->contract_id) }}" class="btn primary">Edit Contract</a></div></div>
<div class="card contract-details-card"><div class="card-head"><div><h3>Contract Information</h3><p>Details associated with this sales agreement.</p></div><span class="contract-status {{ strtolower($contract->status) }}">{{ ucfirst($contract->status) }}</span></div><div class="card-body">
<div class="contract-details-section"><h4>General Information</h4><div class="contract-detail-grid"><div><span>Contract Number</span><strong>{{ $contract->contract_number }}</strong></div><div><span>Sales Order</span><strong>{{ $contract->salesOrder->sales_order_number ?? '—' }}</strong></div><div><span>Contract Type</span><strong>{{ $contract->contract_type ?: '—' }}</strong></div><div><span>Status</span><strong>{{ ucfirst($contract->status) }}</strong></div></div></div>
<div class="contract-details-section"><h4>Contract Period</h4><div class="contract-detail-grid"><div><span>Start Date</span><strong>{{ $contract->start_date?->format('d M Y') ?? '—' }}</strong></div><div><span>End Date</span><strong>{{ $contract->end_date?->format('d M Y') ?? '—' }}</strong></div></div></div>
<div class="contract-details-section"><h4>Document</h4><div class="contract-document-box"><div><strong>Contract PDF</strong><p>{{ $contract->document_path ? 'A private document is attached to this contract.' : 'No document has been attached.' }}</p></div>@if($contract->document_path)<a class="btn" href="{{ route('contracts.download',$contract->contract_id) }}">Download PDF</a>@endif</div></div>
<div class="contract-details-section"><h4>Record Information</h4><div class="contract-detail-grid"><div><span>Created At</span><strong>{{ $contract->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div><div><span>Updated At</span><strong>{{ $contract->updated_at?->format('d M Y, H:i') ?? '—' }}</strong></div></div></div>
</div></div>
@include('contracts._style')
@endsection
