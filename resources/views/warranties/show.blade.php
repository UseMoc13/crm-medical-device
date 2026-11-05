@extends('layouts.app')
@section('title', 'Warranty Details')
@section('content')
@php
 $status = strtolower(trim((string)$warranty->status));
 $isExpired = $warranty->end_date && \Illuminate\Support\Carbon::parse($warranty->end_date)->lt(today());
 $badgeClass = match($status){'active'=>$isExpired?'danger':'success','expired'=>'danger','claimed'=>'warning','void'=>'secondary',default=>'secondary'};
@endphp
<div class="page-head"><div><h1>{{ $warranty->warranty_number }}</h1><p>View warranty coverage, registered device, and customer information.</p></div><div class="actions"><a href="{{ route('warranties.edit',$warranty->warranty_id) }}" class="btn primary">✎ Edit Warranty</a><a href="{{ route('warranties.index') }}" class="btn">← Back</a></div></div>
@include('warranties._toast')
<div class="warranty-detail-layout">
<div class="card warranty-information-card"><div class="card-head"><div><h3>Warranty Information</h3><p>Coverage identification, type and validity.</p></div><span class="badge {{ $badgeClass }}">{{ $warranty->status }} @if($status === 'active' && $isExpired)(Date Passed)@endif</span></div><div class="card-body"><div class="warranty-information-grid">
<div class="warranty-information-field"><span>Warranty Number</span><strong>{{ $warranty->warranty_number }}</strong></div>
<div class="warranty-information-field"><span>Warranty Type</span><strong>{{ $warranty->warranty_type ?: '—' }}</strong></div>
<div class="warranty-information-field"><span>Start Date</span><strong>{{ $warranty->start_date ? \Illuminate\Support\Carbon::parse($warranty->start_date)->format('d M Y') : '—' }}</strong></div>
<div class="warranty-information-field"><span>End Date</span><strong>{{ $warranty->end_date ? \Illuminate\Support\Carbon::parse($warranty->end_date)->format('d M Y') : '—' }}</strong></div>
<div class="warranty-information-field"><span>Coverage Period</span><strong>{{ $warranty->start_date && $warranty->end_date ? \Illuminate\Support\Carbon::parse($warranty->start_date)->diffInMonths(\Illuminate\Support\Carbon::parse($warranty->end_date)) : '—' }} months</strong></div>
<div class="warranty-information-field"><span>Status</span><strong><span class="badge {{ $badgeClass }}">{{ $warranty->status }}</span></strong></div>
</div></div></div>
<div class="card warranty-information-card"><div class="card-head"><div><h3>Product Information</h3><p>Device registered under this warranty.</p></div></div><div class="card-body"><div class="warranty-information-grid"><div class="warranty-information-field"><span>Serial Number</span><strong>{{ $warranty->serial_number ?? '—' }}</strong></div><div class="warranty-information-field"><span>Product Name</span><strong>{{ $warranty->product_name ?? '—' }}</strong></div></div></div></div>
<div class="card warranty-information-card"><div class="card-head"><div><h3>Customer Information</h3><p>Warranty ownership and customer details.</p></div></div><div class="card-body"><div class="warranty-information-grid"><div class="warranty-information-field"><span>Customer Name</span><strong>{{ $warranty->customer_name ?? '—' }}</strong></div><div class="warranty-information-field"><span>Customer ID</span><strong>{{ $warranty->customer_id }}</strong></div></div></div></div>
<div class="card warranty-information-card"><div class="card-head"><div><h3>Warranty Terms and Conditions</h3><p>Additional coverage conditions.</p></div></div><div class="card-body"><p class="warranty-terms">{{ $warranty->terms ?: 'No warranty terms or conditions have been provided.' }}</p></div></div>
</div>
<div class="warranty-detail-actions"><a href="{{ route('warranties.edit',$warranty->warranty_id) }}" class="btn primary">Edit Warranty</a><form method="POST" action="{{ route('warranties.destroy',$warranty->warranty_id) }}" class="warranty-delete-form" data-number="{{ $warranty->warranty_number }}">@csrf @method('DELETE')<button type="submit" class="btn warranty-danger-btn">Delete Warranty</button></form></div>
@include('warranties._confirm')
@include('warranties._style')
@endsection
