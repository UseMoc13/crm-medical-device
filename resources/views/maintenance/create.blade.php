@extends('layouts.app')

@section('title', 'Create Maintenance Schedule')

@section('content')

<div class="page-head">
    <div>
        <h1>Create Maintenance Schedule</h1>
        <p>Schedule preventive or corrective maintenance for a registered medical device.</p>
    </div>

    <div class="actions">
        <a href="{{ route('maintenance.index') }}" class="btn">
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

@if($serialNumbers->isEmpty())
    <div class="alert error">
        No serial numbers are available. Register a device serial number before scheduling maintenance.
    </div>
@endif

<div class="card mt-card">
    <div class="card-head">
        <div>
            <h3>Maintenance Information</h3>
            <p>Enter the device, maintenance type, schedule, and technician assignment.</p>
        </div>
    </div>

    <div class="card-body">
        @include('maintenance._form')
    </div>
</div>

@include('maintenance._style')

@endsection
