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

                        <select id="delivery_id" name="delivery_id" required>
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

<style>
    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
    }

    .page-head h1 {
        margin: 0 0 6px;
        font-size: 26px;
    }

    .page-head p {
        margin: 0;
        color: #6b7280;
    }

    .card {
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
    }

    .card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 22px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-head h2 {
        margin: 0;
        font-size: 18px;
    }

    .required-note {
        color: #6b7280;
        font-size: 12px;
    }

    .card-body {
        padding: 22px;
    }

    .form-section + .form-section {
        margin-top: 28px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .form-section h3 {
        margin: 0 0 20px;
        font-size: 16px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
    }

    .required {
        color: #dc2626;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        box-sizing: border-box;
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #111827;
        font: inherit;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgb(37 99 235 / 12%);
    }

    .field-error {
        color: #dc2626;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 7px;
        background: #fff;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn.primary {
        background: #15966f;
        border-color: #15966f;
        color: #fff;
    }

    .alert {
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 8px;
    }

    .alert.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 700px) {
        .page-head,
        .card-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full-width {
            grid-column: auto;
        }

        .card-body {
            padding: 16px;
        }
    }
</style>
@endsection