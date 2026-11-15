@php
    $editing = isset($event);
    $startValue = old('starts_at', $editing ? $event->starts_at?->format('Y-m-d\TH:i') : ($prefillDate ?? now()->format('Y-m-d')) . 'T09:00');
    $endValue = old('ends_at', $editing ? $event->ends_at?->format('Y-m-d\TH:i') : '');
@endphp
<div class="card calendar-form-card">
  <div class="card-head"><div><h3>{{ $editing ? 'Edit Personal Event' : 'Event Information' }}</h3><p>Use the keyboard or the browser date/time picker.</p></div></div>
  <div class="card-body">
    <form action="{{ $editing ? route('calendar.update', $event->getKey()) : route('calendar.store') }}" method="POST" id="calendarEventForm">
      @csrf @if($editing) @method('PATCH') @endif
      <div class="calendar-form-layout">
        <div class="calendar-fields">
          <div class="calendar-section"><h4>Event Details</h4><p>Your event is private to your account.</p></div>
          <div class="calendar-field"><label for="eventTitle">Title <span>*</span></label><input id="eventTitle" name="title" maxlength="150" value="{{ old('title', $event->title ?? '') }}" required placeholder="e.g. Personal appointment"><small>@error('title'){{ $message }}@enderror</small></div>
          <div class="calendar-field"><label for="eventLocation">Location</label><input id="eventLocation" name="location" maxlength="255" value="{{ old('location', $event->location ?? '') }}" placeholder="Optional location"></div>
          <div class="calendar-section"><h4>Date & Time</h4><p>Time can be entered directly with the keyboard.</p></div>
          <div class="calendar-date-grid">
            <div class="calendar-field"><label for="eventStart">Start <span>*</span></label><input id="eventStart" type="datetime-local" step="60" name="starts_at" value="{{ $startValue }}" required><small>@error('starts_at'){{ $message }}@enderror</small></div>
            <div class="calendar-field"><label for="eventEnd">End</label><input id="eventEnd" type="datetime-local" step="60" name="ends_at" value="{{ $endValue }}"><small>@error('ends_at'){{ $message }}@enderror</small></div>
          </div>
          <div class="calendar-checkbox"><input type="checkbox" name="is_all_day" value="1" id="eventAllDay" @checked((bool) old('is_all_day', $event->is_all_day ?? false))><label for="eventAllDay">All-day event</label></div>
          <div class="calendar-field"><label for="eventDescription">Notes / Description</label><textarea name="description" id="eventDescription" rows="5" maxlength="10000" placeholder="Additional information for this event...">{{ old('description', $event->description ?? '') }}</textarea><small>@error('description'){{ $message }}@enderror</small></div>
          <div class="calendar-form-actions"><a class="btn" href="{{ $editing ? route('calendar.show', $event->getKey()) : route('calendar.index') }}">Cancel</a><button class="btn primary" type="submit">{{ $editing ? 'Save Changes' : 'Create Event' }}</button></div>
        </div>
        <aside class="calendar-preview" aria-label="Live event preview">
          <span class="calendar-preview-label">LIVE PREVIEW</span><div class="calendar-preview-icon">CE</div><h3 id="previewTitle">{{ old('title', $event->title ?? 'New Personal Event') }}</h3><small>Private Calendar Event</small><div class="calendar-preview-rule"></div>
          <div class="calendar-preview-item"><span>Start</span><strong id="previewStart">—</strong></div><div class="calendar-preview-item"><span>End</span><strong id="previewEnd">—</strong></div><div class="calendar-preview-item"><span>Location</span><strong id="previewLocation">—</strong></div><div class="calendar-preview-item"><span>All-day</span><strong id="previewAllDay">No</strong></div><div class="calendar-preview-item"><span>Description</span><strong id="previewDescription">—</strong></div>
          <div class="calendar-preview-note">Only you can see or edit this personal event.</div>
        </aside>
      </div>
    </form>
  </div>
</div>
<script>
(() => {
  const form = document.getElementById('calendarEventForm'); if (!form) return;
  const text = (name) => form.elements[name]?.value?.trim() || '';
  const set = (id, value) => { document.getElementById(id).textContent = value || '—'; };
  const render = () => {
    set('previewTitle', text('title') || 'New Personal Event');
    set('previewStart', text('starts_at').replace('T',' '));
    set('previewEnd', text('ends_at').replace('T',' '));
    set('previewLocation', text('location'));
    set('previewAllDay', document.getElementById('eventAllDay').checked ? 'Yes' : 'No');
    set('previewDescription', text('description'));
  };
  form.addEventListener('input', render); form.addEventListener('change', render); render();
})();
</script>
