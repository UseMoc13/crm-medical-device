{{--
    Form bersama untuk Add / Edit Maintenance Record.
    Variabel: $maintenance, $users, $results, $record (opsional)
--}}
@php
    $isEdit = isset($record) && $record;
    $serial = $maintenance->serialNumber;

    $performedAt = old(
        'performed_at',
        $isEdit
            ? $record->performed_at?->format('Y-m-d\TH:i')
            : now()->format('Y-m-d\TH:i')
    );
@endphp

<form
    method="POST"
    action="{{ $isEdit
        ? route('maintenance.records.update', [$maintenance->maintenance_schedule_id, $record->maintenance_record_id])
        : route('maintenance.records.store', $maintenance->maintenance_schedule_id) }}"
>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mt-form-layout">

        <div class="mt-form-main">

            <div class="mt-section first">
                <h4>Activity Information</h4>
                <p>Record when the maintenance was performed and who performed it.</p>
            </div>

            <div class="mt-field">
                <label for="performed_at">
                    Performed At <span class="required">*</span>
                </label>

                <input
                    type="datetime-local"
                    id="performed_at"
                    name="performed_at"
                    value="{{ $performedAt }}"
                    required
                >

                @error('performed_at')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-field">
                <label for="user_id">
                    Performed By <span class="required">*</span>
                </label>

                <select id="user_id" name="user_id" required>
                    <option value="">Select technician</option>

                    @foreach($users as $user)
                        <option
                            value="{{ $user->user_id }}"
                            data-name="{{ $user->name }}"
                            @selected(old('user_id', $isEdit ? $record->user_id : $maintenance->user_id) == $user->user_id)
                        >
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>

                @error('user_id')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-field">
                <label for="result">
                    Result <span class="required">*</span>
                </label>

                <select id="result" name="result" required>
                    <option value="">Select result</option>

                    @foreach($results as $result)
                        <option
                            value="{{ $result }}"
                            @selected(old('result', $isEdit ? $record->result : null) === $result)
                        >
                            {{ $result }}
                        </option>
                    @endforeach
                </select>

                @error('result')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-section">
                <h4>Device Condition & Work Performed</h4>
                <p>Describe the condition of the device and the work that was carried out.</p>
            </div>

            <div class="mt-field">
                <label for="condition_before">Condition Before</label>
                <textarea
                    id="condition_before"
                    name="condition_before"
                    rows="3"
                    placeholder="Device condition before maintenance..."
                >{{ old('condition_before', $isEdit ? $record->condition_before : '') }}</textarea>
                @error('condition_before')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-field">
                <label for="action_taken">Action Taken</label>
                <textarea
                    id="action_taken"
                    name="action_taken"
                    rows="3"
                    placeholder="Cleaning, part replacement, calibration steps..."
                >{{ old('action_taken', $isEdit ? $record->action_taken : '') }}</textarea>
                @error('action_taken')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-field">
                <label for="condition_after">Condition After</label>
                <textarea
                    id="condition_after"
                    name="condition_after"
                    rows="3"
                    placeholder="Device condition after maintenance..."
                >{{ old('condition_after', $isEdit ? $record->condition_after : '') }}</textarea>
                @error('condition_after')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-field">
                <label for="notes">Notes</label>
                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    placeholder="Additional notes..."
                >{{ old('notes', $isEdit ? $record->notes : '') }}</textarea>
                @error('notes')
                    <span class="mt-error">{{ $message }}</span>
                @enderror
            </div>

            @if($maintenance->status !== 'Completed' && $maintenance->status !== 'Cancelled')
                <label class="mt-check">
                    <input
                        type="checkbox"
                        name="complete_schedule"
                        value="1"
                        @checked(old('complete_schedule'))
                    >
                    Mark the maintenance schedule as Completed
                </label>
            @endif

            <div class="mt-form-actions">
                <a
                    href="{{ route('maintenance.show', $maintenance->maintenance_schedule_id) }}"
                    class="btn"
                >
                    Cancel
                </a>

                <button type="submit" class="btn primary">
                    {{ $isEdit ? 'Save Changes' : 'Save Record' }}
                </button>
            </div>

        </div>

        {{-- SCHEDULE SUMMARY --}}
        <aside class="mt-preview">

            <div class="mt-preview-label">MAINTENANCE SCHEDULE</div>
            <div class="mt-preview-icon">MT</div>
            <div class="mt-preview-title">{{ $maintenance->maintenance_type }}</div>
            <div class="mt-preview-caption">Maintenance Type</div>

            <div class="mt-preview-divider"></div>

            <div class="mt-preview-item">
                <span>Device Serial Number</span>
                <strong>{{ $serial?->serial_number ?? '-' }}</strong>
            </div>

            <div class="mt-preview-item">
                <span>Product</span>
                <strong>{{ $serial?->product?->product_name ?? 'Unknown Product' }}</strong>
            </div>

            <div class="mt-preview-item">
                <span>Customer</span>
                <strong>{{ $serial?->customer?->customer_name ?? '-' }}</strong>
            </div>

            <div class="mt-preview-item">
                <span>Scheduled Date</span>
                <strong>{{ $maintenance->scheduled_date?->format('d M Y') ?? '-' }}</strong>
            </div>

            <div class="mt-preview-item">
                <span>Schedule Status</span>
                <strong>{{ $maintenance->status }}</strong>
            </div>

            <div class="mt-preview-divider"></div>

            <div class="mt-preview-note">
                <strong>Record Guidelines</strong>
                <p>
                    Describe the device condition before and after the work so
                    the service history stays complete and traceable.
                </p>
            </div>

        </aside>

    </div>
</form>
