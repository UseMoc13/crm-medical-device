@php
    $current = $activity ?? null;
    $selectedDate = old('activity_day', $current?->activity_date?->format('Y-m-d') ?? '');
    $selectedTime = old('activity_time', $current?->activity_date?->format('H:i') ?? '09:00');
@endphp
<div class="activity-form" id="activityFormFields">
    <div class="span-all">
        <label for="subject">Activity Subject <span class="activity-required">*</span></label>
        <input id="subject" name="subject" maxlength="150" required autocomplete="off"
               placeholder="e.g. Follow up hospital procurement" value="{{ old('subject', $current?->subject ?? '') }}">
        @error('subject') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="activity_type">Activity Type <span class="activity-required">*</span></label>
        <select id="activity_type" name="activity_type" required>
            <option value="">Choose activity type</option>
            @foreach($types as $type)
                <option value="{{ $type }}" @selected(old('activity_type', $current?->activity_type ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        @error('activity_type') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
    <div class="activity-time-group">
        <div>
            <label for="activity_day">Date <span class="activity-required">*</span></label>
            <input id="activity_day" name="activity_day" type="date" required value="{{ $selectedDate }}">
            @error('activity_day') <small class="activity-field-error">{{ $message }}</small> @enderror
        </div>
        <div>
            <label for="activity_time">Time <span class="activity-required">*</span></label>
            <input id="activity_time" name="activity_time" type="time" step="60" required value="{{ $selectedTime }}">
            @error('activity_time') <small class="activity-field-error">{{ $message }}</small> @enderror
        </div>
    </div>
    @error('activity_date') <small class="activity-field-error span-all">{{ $message }}</small> @enderror
    <div>
        <label for="customer_id">Customer <span class="activity-optional">Optional</span></label>
        <select id="customer_id" name="customer_id">
            <option value="">No customer selected</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->getKey() }}" @selected(old('customer_id', $current?->customer_id ?? '') == $customer->getKey())>{{ $customer->customer_name }}</option>
            @endforeach
        </select>
        @error('customer_id') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
    <div>
        <label for="lead_id">Lead <span class="activity-optional">Optional</span></label>
        <select id="lead_id" name="lead_id">
            <option value="">No lead selected</option>
            @foreach($leads as $lead)
                <option value="{{ $lead->getKey() }}" @selected(old('lead_id', $current?->lead_id ?? '') == $lead->getKey())>{{ $lead->name ?? $lead->lead_name ?? $lead->getKey() }}</option>
            @endforeach
        </select>
        @error('lead_id') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
    <div class="span-all">
        <label for="opportunity_id">Opportunity <span class="activity-optional">Optional</span></label>
        <select id="opportunity_id" name="opportunity_id">
            <option value="">No opportunity selected</option>
            @foreach($opportunities as $opportunity)
                <option value="{{ $opportunity->getKey() }}" @selected(old('opportunity_id', $current?->opportunity_id ?? '') == $opportunity->getKey())>{{ $opportunity->name ?? $opportunity->opportunity_name ?? $opportunity->getKey() }}</option>
            @endforeach
        </select>
        @error('opportunity_id') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
    <div class="span-all">
        <label for="description">Description <span class="activity-optional">Optional</span></label>
        <textarea id="description" name="description" rows="6" maxlength="5000" placeholder="Add important details, expected outcome, or preparation notes...">{{ old('description', $current?->description ?? '') }}</textarea>
        @error('description') <small class="activity-field-error">{{ $message }}</small> @enderror
    </div>
</div>
