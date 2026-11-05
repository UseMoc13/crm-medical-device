@extends('layouts.app')

@section('content')
<div class="page-head">
    <div>
        <h1>Edit Installation</h1>
        <p>Update installation information.</p>
    </div>

    <a
        href="{{ route('installations.show', $installation->installation_id) }}"
        class="btn"
    >
        &larr; Back to Detail
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

<div class="card">
    <div class="card-head">
        <h2>Installation Information</h2>
        <span class="required-note">* Required fields</span>
    </div>

    <div class="card-body">
        <form
            action="{{ route('installations.update', $installation->installation_id) }}"
            method="POST"
        >
            @csrf
            @method('PUT')

            <div class="form-section">
                <h3>General Information</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="delivery_id">
                            Delivery <span class="required">*</span>
                        </label>

                        <select id="delivery_id" name="delivery_id" required disabled>
                            <option value="">Select Delivery</option>

                            @foreach ($deliveries as $delivery)
                                <option
                                    value="{{ $delivery->delivery_id }}"
                                    @selected(
                                        old('delivery_id', $installation->delivery_id)
                                        == $delivery->delivery_id
                                    )
                                >
                                    {{ $delivery->delivery_number }}
                                </option>
                            @endforeach
                        </select>

                        <input type="hidden" name="delivery_id" value="{{ $installation->delivery_id }}">
                        <small class="form-hint">Delivery cannot be changed after scheduling.</small>

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
                                    @selected(
                                        old('user_id', $installation->user_id)
                                        == $user->user_id
                                    )
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

            <div class="form-section">
                <h3>Installation Details</h3>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="installation_date">Installation Date</label>

                        <input
                            type="date"
                            id="installation_date"
                            name="installation_date"
                            value="{{ old(
                                'installation_date',
                                $installation->installation_date?->format('Y-m-d')
                            ) }}"
                        >

                        @error('installation_date')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">
                            Status <span class="required">*</span>
                        </label>

                        <select id="status" name="status" required>
                            @foreach ([
                                'Scheduled' => 'Scheduled',
                                'In Progress' => 'In Progress',
                                'Completed' => 'Completed',
                                'Cancelled' => 'Cancelled',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old('status', $installation->status) === $value
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-hint">Scheduled → In Progress → Completed. Complete every checklist item before marking Completed. Cancel is available only while Scheduled.</small>

                        @error('status')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="location">Installation Location</label>

                        <textarea
                            id="location"
                            name="location"
                            rows="3"
                        >{{ old('location', $installation->location) }}</textarea>

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
                        >{{ old('notes', $installation->notes) }}</textarea>

                        @error('notes')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a
                    href="{{ route('installations.show', $installation->installation_id) }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    Update Installation
                </button>
            </div>
        </form>
    </div>
</div>

@include('installations._style', ['page' => 'edit'])
@endsection