@extends('layouts.app')

@section('title', 'Edit Maintenance Record')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Maintenance Record</h1>
        <p>Update the details of this maintenance activity.</p>
    </div>

    <div class="actions">
        <a href="{{ route('maintenance.show', $maintenance->maintenance_schedule_id) }}" class="btn">
            ← Back to Maintenance
        </a>
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

<div class="card mt-card">
    <div class="card-head">
        <div>
            <h3>Maintenance Record Information</h3>
            <p>Update the result and details of the maintenance activity.</p>
        </div>
    </div>

    <div class="card-body">
        @include('maintenance.records._form')
    </div>
</div>

@include('maintenance._style')

@endsection
