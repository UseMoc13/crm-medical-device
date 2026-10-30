
@extends('layouts.app')

@section('title', 'Edit Service Ticket')

@section('content')

<div class="page-head">
    <div>
        <h1>Edit Service Ticket</h1>
        <p>Update customer service request, device details, and service assignment.</p>
    </div>

    <div class="actions">
        <a href="{{ route('service-tickets.index') }}" class="btn">
            ← Back to Service Tickets
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

@if($customers->isEmpty())
    <div class="alert error">
        No customers are available. Create a customer before updating a service ticket.
    </div>
@endif

@if($serialNumbers->isEmpty())
    <div class="alert error">
        No serial numbers are available. Register a device serial number before updating a service ticket.
    </div>
@endif

@if(empty($statuses) || empty($priorities))
    <div class="alert error">
        Service ticket statuses or priorities are not configured.
        Please configure them before updating a service ticket.
    </div>
@endif

<div class="card ticket-create-card">

    <div class="card-head">
        <div>
            <h3>Service Ticket Information</h3>
            <p>Update the customer request, device details, and service assignment.</p>
        </div>
    </div>

    <div class="card-body ticket-create-body">

        <form
            method="POST"
            action="{{ route('service-tickets.update', $serviceTicket) }}"
            id="ticketEditForm"
        >
            @csrf
            @method('PUT')

            <div class="ticket-form-layout">

                <div class="ticket-form-main">

                    {{-- TICKET INFORMATION --}}

                    <div class="section-divider first-section">
                        <div>
                            <h4>Ticket Information</h4>
                            <p>Update the reported issue and service request details.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ticket Number</label>

                        <input
                            type="text"
                            value="{{ $serviceTicket->ticket_number }}"
                            readonly
                        >

                        <span class="form-hint">
                            Ticket number is automatically generated and cannot be changed.
                        </span>
                    </div>

                    <div class="form-group">
                        <label for="title">
                            Ticket Title <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $serviceTicket->title) }}"
                            placeholder="e.g. Medical Device Not Powering On"
                            maxlength="150"
                            autocomplete="off"
                            required
                        >

                        <span class="form-hint">
                            Provide a short and clear description of the reported issue.
                        </span>

                        @error('title')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Issue Description</label>

                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Describe the issue reported by the customer..."
                        >{{ old('description', $serviceTicket->description) }}</textarea>

                        <span class="form-hint">
                            Include relevant symptoms, error messages, or other details.
                        </span>

                        @error('description')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- CUSTOMER AND DEVICE --}}

                    <div class="section-divider">
                        <div>
                            <h4>Customer & Device Information</h4>
                            <p>Associate the service request with a customer and registered device.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="customer_id">
                            Customer <span class="required">*</span>
                        </label>

                        <select
                            id="customer_id"
                            name="customer_id"
                            required
                        >
                            <option value="">Select a customer</option>

                            @foreach($customers as $customer)
                                <option
                                    value="{{ $customer->customer_id }}"
                                    data-name="{{ $customer->customer_name }}"
                                    @selected(old('customer_id', $serviceTicket->customer_id) == $customer->customer_id)
                                >
                                    {{ $customer->customer_name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Select the customer who reported the service issue.
                        </span>

                        @error('customer_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="serial_number_id">
                            Device Serial Number <span class="required">*</span>
                        </label>

                        <select
                            id="serial_number_id"
                            name="serial_number_id"
                            required
                        >
                            <option value="">Select a device</option>

                            @foreach($serialNumbers as $serialNumber)
                                <option
                                    value="{{ $serialNumber->serial_number_id }}"
                                    data-serial="{{ $serialNumber->serial_number }}"
                                    data-product="{{ $serialNumber->product?->product_name ?? 'Unknown Product' }}"
                                    data-customer-id="{{ $serialNumber->customer_id ?? '' }}"
                                    @selected(old('serial_number_id', $serviceTicket->serial_number_id) == $serialNumber->serial_number_id)
                                >
                                    {{ $serialNumber->serial_number }}
                                    — {{ $serialNumber->product?->product_name ?? 'Unknown Product' }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Select the registered device that requires service.
                        </span>

                        @error('serial_number_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- ASSIGNMENT AND PRIORITY --}}

                    <div class="section-divider">
                        <div>
                            <h4>Assignment & Priority</h4>
                            <p>Update the urgency and technician responsible for this service ticket.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_id">Assigned Technician</label>

                        <select
                            id="user_id"
                            name="user_id"
                        >
                            <option value="">Unassigned</option>

                            @foreach($users as $user)
                                <option
                                    value="{{ $user->user_id }}"
                                    data-name="{{ $user->name }}"
                                    @selected(old('user_id', $serviceTicket->user_id) == $user->user_id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Optionally assign or change the technician responsible for this ticket.
                        </span>

                        @error('user_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="priority">
                            Priority <span class="required">*</span>
                        </label>

                        <select
                            id="priority"
                            name="priority"
                            required
                        >
                            <option value="">Select priority</option>

                            @foreach($priorities as $priority)
                                <option
                                    value="{{ $priority }}"
                                    @selected(old('priority', $serviceTicket->priority) === $priority)
                                >
                                    {{ ucfirst(str_replace('_', ' ', $priority)) }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Set the urgency based on the severity of the reported issue.
                        </span>

                        @error('priority')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">
                            Status <span class="required">*</span>
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >
                            <option value="">Select status</option>

                            @foreach($statuses as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(old('status', $serviceTicket->status) === $status)
                                >
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Update the current progress of this service request.
                        </span>

                        @error('status')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- RESOLUTION INFORMATION --}}

                    <div class="section-divider">
                        <div>
                            <h4>Resolution Information</h4>
                            <p>Record the solution or action taken to address the reported issue.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="resolution">Resolution</label>

                        <textarea
                            id="resolution"
                            name="resolution"
                            rows="5"
                            placeholder="Describe the resolution or action taken..."
                        >{{ old('resolution', $serviceTicket->resolution) }}</textarea>

                        <span class="form-hint">
                            Provide details about the repair, troubleshooting, or resolution performed.
                        </span>

                        @error('resolution')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- FORM ACTIONS --}}

                    <div class="form-actions">
                        <a
                            href="{{ route('service-tickets.index') }}"
                            class="btn"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn primary"
                            @disabled(
                                $customers->isEmpty() ||
                                $serialNumbers->isEmpty() ||
                                empty($statuses) ||
                                empty($priorities)
                            )
                        >
                            Save Changes
                        </button>
                    </div>

                </div>

                {{-- LIVE PREVIEW --}}

                <aside class="ticket-preview-panel">

                    <div class="preview-label">
                        SERVICE TICKET PREVIEW
                    </div>

                    <div class="preview-ticket-icon">
                        SRV
                    </div>

                    <div class="preview-ticket-number">
                        {{ $serviceTicket->ticket_number }}
                    </div>

                    <div class="preview-caption">
                        Ticket Number
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-item">
                        <span>Ticket Title</span>
                        <strong id="previewTitle">
                            {{ $serviceTicket->title ?: 'Not specified' }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Customer</span>
                        <strong id="previewCustomer">
                            {{ $serviceTicket->customer?->customer_name ?? 'Not selected' }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Device Serial Number</span>
                        <strong id="previewSerial">
                            {{ $serviceTicket->serialNumber?->serial_number ?? 'Not selected' }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Product</span>
                        <strong id="previewProduct">
                            {{ $serviceTicket->serialNumber?->product?->product_name ?? 'Not selected' }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Assigned Technician</span>
                        <strong id="previewTechnician">
                            {{ $serviceTicket->user?->name ?? 'Unassigned' }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Priority</span>
                        <strong
                            class="preview-priority"
                            id="previewPriority"
                        >
                            {{ ucfirst(str_replace('_', ' ', old('priority', $serviceTicket->priority))) }}
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Status</span>
                        <strong
                            class="preview-status"
                            id="previewStatus"
                        >
                            {{ ucfirst(str_replace('_', ' ', old('status', $serviceTicket->status))) }}
                        </strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-note">
                        <strong>Service Request Guidelines</strong>
                        <p>
                            Ensure the selected customer and device information
                            are correct. Update the ticket status and provide
                            resolution details when the issue has been addressed.
                        </p>
                    </div>

                </aside>

            </div>
        </form>

    </div>
</div>

<style>
/* =========================================================
   SERVICE TICKET EDIT
========================================================= */

.ticket-create-card,
.ticket-create-body,
.ticket-form-layout,
.ticket-form-main {
    overflow: visible !important;
}

/* =========================================================
   FORM LAYOUT
========================================================= */

.ticket-form-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr);
    gap: 32px;
    align-items: start;
    width: 100%;
}

.ticket-form-main {
    min-width: 0;
}

/* =========================================================
   SECTION DIVIDER
========================================================= */

.ticket-form-main .section-divider {
    margin: 25px 0 18px;
    padding-top: 20px;
    border-top: 1px solid #edf0f5;
}

.ticket-form-main .section-divider.first-section {
    margin-top: 0;
    padding-top: 0;
    border-top: 0;
}

.ticket-form-main .section-divider h4 {
    margin: 0 0 4px;
    color: #17284f;
    font-size: 13px;
    font-weight: 700;
}

.ticket-form-main .section-divider p {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   FORM GROUP
========================================================= */

.ticket-form-main .form-group {
    margin-bottom: 19px;
}

.ticket-form-main .form-group label {
    display: block;
    margin-bottom: 7px;
    color: #17284f;
    font-size: 13px;
    font-weight: 600;
}

.ticket-form-main .required {
    color: #c94a4a;
}

/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.ticket-form-main .form-group input,
.ticket-form-main .form-group select,
.ticket-form-main .form-group textarea {
    display: block;
    width: 100%;
    box-sizing: border-box;
    padding: 11px 12px;
    border: 1px solid #d9dee8;
    border-radius: 8px;
    background: #fff;
    color: #17284f;
    font-family: inherit;
    font-size: 13px;
    transition: border-color .18s ease, box-shadow .18s ease;
}

.ticket-form-main .form-group input,
.ticket-form-main .form-group select {
    height: 42px;
}

.ticket-form-main .form-group textarea {
    min-height: 115px;
    resize: vertical;
    line-height: 1.6;
}

.ticket-form-main .form-group input:focus,
.ticket-form-main .form-group select:focus,
.ticket-form-main .form-group textarea:focus {
    outline: none;
    border-color: #2ba7a0;
    box-shadow: 0 0 0 3px rgba(43, 167, 160, .08);
}

.ticket-form-main .form-group input::placeholder,
.ticket-form-main .form-group textarea::placeholder {
    color: #a0a8b6;
}

.ticket-form-main .form-group input[readonly] {
    background: #f5f7fa;
    color: #788397;
    cursor: not-allowed;
}

/* =========================================================
   HINT / ERROR
========================================================= */

.ticket-form-main .form-hint,
.ticket-form-main .form-error {
    display: block;
    margin-top: 6px;
    font-size: 11px;
    line-height: 1.5;
}

.ticket-form-main .form-hint {
    color: #8a94a6;
}

.ticket-form-main .form-error {
    color: #c94a4a;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.ticket-form-main .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 8px;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid #edf0f5;
}

/* =========================================================
   PREVIEW PANEL
========================================================= */

.ticket-preview-panel {
    position: sticky;
    top: 92px;
    align-self: start;
    min-width: 0;
    height: fit-content;
    padding: 24px;
    box-sizing: border-box;
    border: 1px solid #e6eaf0;
    border-radius: 12px;
    background: #fafbfd;
    z-index: 5;
}

.ticket-preview-panel .preview-label {
    margin-bottom: 20px;
    color: #8a94a6;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.ticket-preview-panel .preview-ticket-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px;
    height: 52px;
    margin-bottom: 16px;
    border-radius: 12px;
    background: #eaf7f3;
    color: #167d70;
    font-size: 14px;
    font-weight: 700;
}

.ticket-preview-panel .preview-ticket-number {
    color: #17284f;
    font-size: 17px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.ticket-preview-panel .preview-caption {
    margin-top: 5px;
    color: #7d8797;
    font-size: 11px;
}

.ticket-preview-panel .preview-divider {
    height: 1px;
    margin: 21px 0;
    background: #e5e9ef;
}

.ticket-preview-panel .preview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 17px;
}

.ticket-preview-panel .preview-item span {
    color: #8a94a6;
    font-size: 11px;
}

.ticket-preview-panel .preview-item strong {
    overflow-wrap: anywhere;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
}

.ticket-preview-panel .preview-item .preview-priority,
.ticket-preview-panel .preview-item .preview-status {
    display: inline-block;
    width: fit-content;
    padding: 5px 9px;
    border-radius: 6px;
    background: #eef1f6;
    color: #596579;
    font-size: 11px;
}

.ticket-preview-panel .preview-item .preview-priority.priority-low {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-priority.priority-medium {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-priority.priority-high {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-priority.priority-urgent {
    background: #fdebec;
    color: #bd4148;
}

.ticket-preview-panel .preview-item .preview-status.status-open {
    background: #eef3fb;
    color: #41638f;
}

.ticket-preview-panel .preview-item .preview-status.status-in-progress {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-item .preview-status.status-on-hold {
    background: #fff3df;
    color: #a66b12;
}

.ticket-preview-panel .preview-item .preview-status.status-resolved,
.ticket-preview-panel .preview-item .preview-status.status-closed {
    background: #eaf7f3;
    color: #167d70;
}

.ticket-preview-panel .preview-note {
    padding: 13px 14px;
    border-radius: 8px;
    background: #f1f5f7;
}

.ticket-preview-panel .preview-note strong {
    display: block;
    margin-bottom: 5px;
    color: #34415c;
    font-size: 11px;
}

.ticket-preview-panel .preview-note p {
    margin: 0;
    color: #8a94a6;
    font-size: 10px;
    line-height: 1.6;
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {
    .ticket-form-layout {
        grid-template-columns: 1fr;
    }

    .ticket-preview-panel {
        position: static;
        order: -1;
    }
}

@media (max-width: 600px) {
    .ticket-form-main .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .ticket-form-main .form-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const titleInput = document.getElementById('title');
    const customerSelect = document.getElementById('customer_id');
    const serialSelect = document.getElementById('serial_number_id');
    const technicianSelect = document.getElementById('user_id');
    const prioritySelect = document.getElementById('priority');
    const statusSelect = document.getElementById('status');

    const previewTitle = document.getElementById('previewTitle');
    const previewCustomer = document.getElementById('previewCustomer');
    const previewSerial = document.getElementById('previewSerial');
    const previewProduct = document.getElementById('previewProduct');
    const previewTechnician = document.getElementById('previewTechnician');
    const previewPriority = document.getElementById('previewPriority');
    const previewStatus = document.getElementById('previewStatus');

    function formatLabel(value) {
        if (!value) return 'Not specified';

        return value
            .replace(/[_-]/g, ' ')
            .replace(/\b\w/g, function (letter) {
                return letter.toUpperCase();
            });
    }

    function updatePreview() {
        const customerOption = customerSelect.selectedOptions[0];
        const serialOption = serialSelect.selectedOptions[0];
        const technicianOption = technicianSelect.selectedOptions[0];
        const priorityOption = prioritySelect.selectedOptions[0];
        const statusOption = statusSelect.selectedOptions[0];

        previewTitle.textContent =
            titleInput.value.trim() || 'Not specified';

        previewCustomer.textContent =
            customerOption?.dataset.name || 'Not selected';

        previewSerial.textContent =
            serialOption?.dataset.serial || 'Not selected';

        previewProduct.textContent =
            serialOption?.dataset.product || 'Not selected';

        previewTechnician.textContent =
            technicianOption?.dataset.name || 'Unassigned';

        const priority = priorityOption?.value || '';

        previewPriority.textContent = formatLabel(priority);
        previewPriority.className =
            'preview-priority' + (priority ? ' priority-' + priority : '');

        const status = statusOption?.value || '';

        previewStatus.textContent = formatLabel(status);
        previewStatus.className =
            'preview-status' + (status
                ? ' status-' + status.replace(/_/g, '-')
                : '');
    }

    [
        titleInput,
        customerSelect,
        serialSelect,
        technicianSelect,
        prioritySelect,
        statusSelect
    ].forEach(function (element) {
        element.addEventListener('input', updatePreview);
        element.addEventListener('change', updatePreview);
    });

    updatePreview();
});
</script>

@endsection
