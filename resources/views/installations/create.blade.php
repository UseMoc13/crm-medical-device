@extends('layouts.app')

@section('title', 'Create Installation')
@section('content')
<div class="page-head">
    <div>
        <h1>Create Installation</h1>
        <p>Create a new medical device installation record.</p>
    </div>

    <a href="{{ route('installations.index') }}" class="btn">
        &larr; Back to Installations
    </a>
</div>

@if ($errors->any())
    <div class="alert danger">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="installation-editor-layout">
<div class="card">
    <div class="card-head">
        <div><h3>Installation Information</h3><p>Enter the installation and technician details below.</p></div>
        <span class="required-note">* Required fields</span>
    </div>

    <div class="card-body">
        @if ($deliveries->isEmpty())
            <div class="alert warning">
                No eligible Delivered shipments are available (or they already have installations). Complete a Delivery before
                registering an Installation.
            </div>
        @endif

        @if ($users->isEmpty())
            <div class="alert warning">
                No active users are available to assign to this installation.
            </div>
        @endif

        <form
            action="{{ route('installations.store') }}"
            method="POST"
        >
            @csrf

            <div class="installation-form-section">
                <div class="installation-section-heading"><h4>General Information</h4><p>Delivery and assigned technician.</p></div>

                <div class="installation-form-grid">
                    <div class="form-group">
                        <label for="delivery_id">
                            Delivery <span class="required">*</span>
                        </label>

                        <select
                            id="delivery_id"
                            name="delivery_id"
                            required
                        >
                            <option value="">Select Delivery</option>

                            @foreach ($deliveries as $delivery)
                                <option
                                    value="{{ $delivery->delivery_id }}"
                                    @selected(old('delivery_id', $prefillDeliveryId ?? '') == $delivery->delivery_id)
                                >
                                    {{ $delivery->delivery_number }}
                                    — {{ ucfirst(str_replace('_', ' ', $delivery->status)) }}
                                </option>
                            @endforeach
                        </select>

                        @error('delivery_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="user_id">
                            Assigned User <span class="required">*</span>
                        </label>

                        <select id="user_id" name="user_id" required>
                            <option value="">Select User</option>

                            @foreach ($users as $user)
                                <option
                                    value="{{ $user->user_id }}"
                                    @selected(old('user_id') == $user->user_id)
                                >
                                    {{ $user->name
                                        ?? $user->full_name
                                        ?? $user->username
                                        ?? $user->email
                                        ?? $user->user_id }}
                                </option>
                            @endforeach
                        </select>

                        @error('user_id')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="installation-form-section">
                <div class="installation-section-heading"><h4>Installation Details</h4><p>Schedule, location, and additional instructions.</p></div>

                <div class="installation-form-grid">
                    <div class="form-group">
                        <label for="installation_date">
                            Installation Date
                        </label>

                        <input
                            type="date"
                            id="installation_date"
                            name="installation_date"
                            value="{{ old('installation_date') }}"
                        >

                        @error('installation_date')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Initial Status</label>
                        <input type="text" value="Scheduled" readonly>
                        <small>Change status from Installation Details after scheduling.</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="location">Installation Location</label>

                        <textarea
                            id="location"
                            name="location"
                            rows="3"
                            placeholder="Enter the installation address or location"
                        >{{ old('location') }}</textarea>

                        @error('location')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Notes</label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            placeholder="Additional installation notes..."
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="installation-form-actions">
                <a href="{{ route('installations.index') }}" class="btn">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn primary"
                    @disabled($deliveries->isEmpty() || $users->isEmpty())
                >
                    Save Installation
                </button>
            </div>
        </form>
    </div>
</div>

@include('installations._preview')
</div>
@include('installations._toast')
@include('installations._style')
@endsection