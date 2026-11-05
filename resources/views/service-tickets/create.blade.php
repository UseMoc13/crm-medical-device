@extends('layouts.app')

@section('title', 'Create Service Ticket')

@section('content')

<div class="page-head">
    <div>
        <h1>Create Service Ticket</h1>
        <p>Register a customer service request and assign it to a technician.</p>
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
        No customers are available. Create a customer before registering a service ticket.
    </div>
@endif

@if($serialNumbers->isEmpty())
    <div class="alert error">
        No serial numbers are available. Register a device serial number before creating a service ticket.
    </div>
@endif

@if(empty($statuses) || empty($priorities))
    <div class="alert error">
        Service ticket statuses or priorities are not configured.
        Please configure them before creating a service ticket.
    </div>
@endif

<div class="card ticket-create-card">

    <div class="card-head">
        <div>
            <h3>Service Ticket Information</h3>
            <p>Enter the customer request, device details, and service assignment.</p>
        </div>
    </div>

    <div class="card-body ticket-create-body">

        <form
            method="POST"
            action="{{ route('service-tickets.store') }}"
            id="ticketCreateForm"
        >
            @csrf

            <div class="ticket-form-layout">

                <div class="ticket-form-main">

                    {{-- TICKET INFORMATION --}}

                    <div class="section-divider first-section">
                        <div>
                            <h4>Ticket Information</h4>
                            <p>Describe the reported issue and service request.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title">
                            Ticket Title <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
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
                        >{{ old('description') }}</textarea>

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
                                    @selected(old('customer_id') == $customer->customer_id)
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
                                    @selected(old('serial_number_id') == $serialNumber->serial_number_id)
                                >
                                    {{ $serialNumber->serial_number }}
                                    — {{ $serialNumber->product?->product_name ?? 'Unknown Product' }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Select a device assigned to the customer. You can also choose a serial number first.
                        </span>

                        @error('serial_number_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    @include('service-tickets._warranty_coverage')

                    {{-- ASSIGNMENT AND PRIORITY --}}

                    <div class="section-divider">
                        <div>
                            <h4>Assignment & Priority</h4>
                            <p>Set the urgency and assign the service ticket to a technician.</p>
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
                                    @selected(old('user_id') == $user->user_id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            Optionally assign this ticket to a technician.
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
                                    @selected(old('priority', 'medium') === $priority)
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

                            @foreach(['open'] as $status)
                                <option
                                    value="{{ $status }}"
                                    @selected(old('status', 'open') === $status)
                                >
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>

                        <span class="form-hint">
                            New tickets must start as Open. Change progress later through the ticket workflow.
                        </span>

                        @error('status')
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
                            Create Service Ticket
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
                        Auto Generated
                    </div>

                    <div class="preview-caption">
                        Ticket Number
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-item">
                        <span>Ticket Title</span>
                        <strong id="previewTitle">Not specified</strong>
                    </div>

                    <div class="preview-item">
                        <span>Customer</span>
                        <strong id="previewCustomer">Not selected</strong>
                    </div>

                    <div class="preview-item">
                        <span>Device Serial Number</span>
                        <strong id="previewSerial">Not selected</strong>
                    </div>

                    <div class="preview-item">
                        <span>Product</span>
                        <strong id="previewProduct">Not selected</strong>
                    </div>

                    <div class="preview-item">
                        <span>Assigned Technician</span>
                        <strong id="previewTechnician">Unassigned</strong>
                    </div>

                    <div class="preview-item">
                        <span>Priority</span>
                        <strong
                            class="preview-priority"
                            id="previewPriority"
                        >
                            Not specified
                        </strong>
                    </div>

                    <div class="preview-item">
                        <span>Status</span>
                        <strong
                            class="preview-status"
                            id="previewStatus"
                        >
                            Not specified
                        </strong>
                    </div>

                    <div class="preview-divider"></div>

                    <div class="preview-note">
                        <strong>Service Request Guidelines</strong>
                        <p>
                            Ensure the selected customer and device information
                            are correct. Provide sufficient issue details to help
                            the technician diagnose and resolve the problem.
                        </p>
                    </div>

                </aside>

            </div>
        </form>

    </div>
</div>



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

@include('service-tickets._device_customer_filter')

@include('service-tickets._device_customer_filter')

@include('service-tickets._style')

@endsection
