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

@if($recurringSource)
    <div class="mt-followup-notice" role="status">
        <strong>Next Preventive Maintenance · {{ $recurringMonths }} months</strong>
        <p>Scheduling the next preventive visit for device <b>{{ $recurringSource->serialNumber?->serial_number }}</b>.
        Suggested date: <b>{{ $recurringSuggestedDate }}</b> (calculated from the previous scheduled date).
        You may adjust the date before saving. The completed maintenance record remains unchanged.</p>
    </div>
@endif

@if($followupSource)
    <div class="mt-followup-notice" role="status">
        <strong>Follow-up Maintenance</strong>
        <p>Creating a new Corrective maintenance schedule for device <b>{{ $followupSource->serialNumber?->serial_number }}</b>,
        originating from <a href="{{ route('maintenance.show', $followupSource->getKey()) }}">{{ $followupSource->maintenance_type }} maintenance</a>.
        The original maintenance records will remain unchanged. Select a new date and confirm the assigned technician.</p>
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
