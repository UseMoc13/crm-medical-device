{{-- Shared Maintenance Schedule form for Create and Edit. The Record form is maintenance.records._form. --}}
@php
    $isEdit = isset($maintenance) && $maintenance !== null;
    $followupSource = $followupSource ?? null;
    $selectedSerial = (string) old('serial_number_id', $isEdit ? $maintenance->serial_number_id : ($followupSource?->serial_number_id ?? request('serial_number_id', '')));
    $selectedUser = (string) old('user_id', $isEdit ? $maintenance->user_id : ($followupSource?->user_id ?? ''));
    $selectedType = (string) old('maintenance_type', $isEdit ? $maintenance->maintenance_type : ($followupSource ? 'Corrective' : ''));
    $selectedStatus = (string) old('status', $isEdit ? $maintenance->status : 'Scheduled');
    $scheduledDate = old('scheduled_date', $isEdit ? $maintenance->scheduled_date?->format('Y-m-d') : '');
    $scheduleNotes = old('notes', $isEdit ? $maintenance->notes : ($followupSource ? 'Follow-up required after ' . $followupSource->maintenance_type . ' maintenance (latest result: ' . ($followupSource->records()->orderByDesc('performed_at')->first()?->result ?? 'Unknown') . ').' : ''));
@endphp

<form method="POST" action="{{ $isEdit ? route('maintenance.update', $maintenance->maintenance_schedule_id) : route('maintenance.store') }}" id="maintenanceScheduleForm">
    @csrf
    @if(!$isEdit && $followupSource)
        <input type="hidden" name="followup_from" value="{{ $followupSource->maintenance_schedule_id }}">
    @endif
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mt-form-layout">
        <div class="mt-form-main">
            <div class="mt-section first">
                <h4>Device & Maintenance Details</h4>
                <p>Select the medical device and planned maintenance activity.</p>
            </div>

            <div class="mt-field">
                <label for="serial_number_id">Device Serial Number <span class="required">*</span></label>
                <select id="serial_number_id" name="serial_number_id" required>
                    <option value="">Select a registered device</option>
                    @foreach($serialNumbers as $serial)
                        <option value="{{ $serial->serial_number_id }}"
                                data-serial="{{ $serial->serial_number }}"
                                data-product="{{ $serial->product?->product_name ?? 'Unknown Product' }}"
                                data-customer="{{ $serial->customer?->customer_name ?? 'Unassigned' }}"
                                @selected($selectedSerial === (string) $serial->serial_number_id)>
                            {{ $serial->serial_number }} — {{ $serial->product?->product_name ?? 'Unknown Product' }}
                        </option>
                    @endforeach
                </select>
                @error('serial_number_id') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-field">
                <label for="maintenance_type">Maintenance Type <span class="required">*</span></label>
                <select id="maintenance_type" name="maintenance_type" required>
                    <option value="">Select maintenance type</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected($selectedType === (string) $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('maintenance_type') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-section">
                <h4>Schedule & Assignment</h4>
                <p>Choose the planned date, responsible technician, and current status.</p>
            </div>

            <div class="mt-field">
                <label for="scheduled_date">Scheduled Date <span class="required">*</span></label>
                <input type="date" id="scheduled_date" name="scheduled_date" value="{{ $scheduledDate }}" required>
                @error('scheduled_date') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-field">
                <label for="user_id">Assigned Technician</label>
                <select id="user_id" name="user_id">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}"
                                data-name="{{ $user->name }}"
                                @selected($selectedUser === (string) $user->user_id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <span class="mt-hint">Assign a technician before starting maintenance.</span>
                @error('user_id') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-field">
                <label for="status">Status <span class="required">*</span></label>
                <input type="text" value="{{ $isEdit ? $maintenance->status : 'Scheduled' }}" readonly aria-label="Current maintenance status">
                <input type="hidden" id="status" name="status" value="{{ $isEdit ? $maintenance->status : 'Scheduled' }}">
                <span class="mt-hint">Status changes are managed from the Maintenance Workflow on the detail page.</span>
                @error('status') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-field">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Maintenance plan or additional instructions...">{{ $scheduleNotes }}</textarea>
                @error('notes') <span class="mt-error">{{ $message }}</span> @enderror
            </div>

            <div class="mt-form-actions">
                <a href="{{ $isEdit ? route('maintenance.show', $maintenance->maintenance_schedule_id) : route('maintenance.index') }}" class="btn">Cancel</a>
                <button type="submit" class="btn primary">{{ $isEdit ? 'Save Changes' : 'Create Maintenance Schedule' }}</button>
            </div>
        </div>

        <aside class="mt-preview">
            <div class="mt-preview-label">MAINTENANCE PREVIEW</div>
            <div class="mt-preview-icon">MT</div>
            <div class="mt-preview-title">{{ $isEdit ? 'Edit Schedule' : 'New Schedule' }}</div>
            <div class="mt-preview-caption">Maintenance Schedule</div>
            <div class="mt-preview-divider"></div>

            <div class="mt-preview-item"><span>Serial Number</span><strong id="mtPreviewSerial">Not selected</strong></div>
            <div class="mt-preview-item"><span>Product</span><strong id="mtPreviewProduct">Not selected</strong></div>
            <div class="mt-preview-item"><span>Customer</span><strong id="mtPreviewCustomer">Not selected</strong></div>
            <div class="mt-preview-item"><span>Maintenance Type</span><strong id="mtPreviewType">Not selected</strong></div>
            <div class="mt-preview-item"><span>Scheduled Date</span><strong id="mtPreviewDate">Not scheduled</strong></div>
            <div class="mt-preview-item"><span>Technician</span><strong id="mtPreviewUser">Unassigned</strong></div>
            <div class="mt-preview-item"><span>Status</span><strong id="mtPreviewStatus">Scheduled</strong></div>

            <div class="mt-preview-divider"></div>
            <div class="mt-preview-note">
                <strong>Schedule Guidelines</strong>
                <p>Confirm the device and planned activity. Maintenance records are entered separately after scheduling.</p>
            </div>
        </aside>
    </div>
</form>

<script>
(() => {
    const get = (id) => document.getElementById(id);
    const fields = ['serial_number_id', 'maintenance_type', 'scheduled_date', 'user_id', 'status'];
    const refresh = () => {
        const serial = get('serial_number_id')?.selectedOptions[0];
        const user = get('user_id')?.selectedOptions[0];
        const type = get('maintenance_type')?.selectedOptions[0];
        const status = get('status')?.selectedOptions[0];
        get('mtPreviewSerial').textContent = serial?.dataset.serial || 'Not selected';
        get('mtPreviewProduct').textContent = serial?.dataset.product || 'Not selected';
        get('mtPreviewCustomer').textContent = serial?.dataset.customer || 'Not selected';
        get('mtPreviewType').textContent = type?.value ? type.textContent.trim() : 'Not selected';
        get('mtPreviewDate').textContent = get('scheduled_date')?.value || 'Not scheduled';
        get('mtPreviewUser').textContent = user?.value ? (user.dataset.name || user.textContent.trim()) : 'Unassigned';
        get('mtPreviewStatus').textContent = status?.value || 'Scheduled';
    };
    fields.forEach(id => {
        get(id)?.addEventListener('change', refresh);
        get(id)?.addEventListener('input', refresh);
    });
    refresh();
})();
</script>
