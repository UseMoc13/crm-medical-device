@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')

<div class="page-head">
    <div>
        <h1>Create Invoice</h1>
        <p>Generate an invoice from a sales order and review its items before saving.</p>
    </div>

    <div class="actions">
        <a href="{{ route('invoices.index') }}" class="btn">← Back to Invoices</a>
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

@if($salesOrders->isEmpty())
    <div class="alert error">
        No sales orders are available. Create a sales order before issuing an invoice.
    </div>
@endif

<div class="card inv-card">
    <div class="card-head">
        <div>
            <h3>Invoice Information</h3>
            <p>Select the sales order, set the dates, and review the billed items.</p>
        </div>
    </div>

    <div class="card-body">
        @include('invoices._form')
    </div>
</div>

@include('invoices._styles')

@endsection