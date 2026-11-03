@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Invoice</h1>
        <p>Update the dates, items, and amounts of {{ $invoice->invoice_number }}.</p>
    </div>

    <div class="actions">
        <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="btn">← Back to Invoice</a>
    </div>
</div>

@if($errors->any())
    <div class="alert error">
        <strong>Please check the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card inv-card">
    <div class="card-head">
        <div>
            <h3>Invoice Information</h3>
            <p>Only Draft and Sent invoices can be edited.</p>
        </div>
    </div>

    <div class="card-body">
        @include('invoices._form')
    </div>
</div>

@include('invoices._styles')

@endsection