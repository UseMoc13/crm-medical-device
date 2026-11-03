@extends('layouts.app')

@section('title', 'Edit Maintenance Schedule')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Maintenance Schedule</h1>
        <p>Update the device, schedule, technician assignment, or status.</p>
    </div>

    <div class="actions">
        <a href="{{ route('maintenance.show', $maintenance->maintenance_schedule_id) }}" class="btn">
            ← Back to Details
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
            <h3>Maintenance Information</h3>
            <p>Update the maintenance schedule details.</p>
        </div>
    </div>

    <div class="card-body">
        @include('maintenance._form')
    </div>
</div>

@include('maintenance._styles')

@endsection
