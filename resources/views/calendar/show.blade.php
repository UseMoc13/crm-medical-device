@extends('layouts.app')
@section('title', 'Personal Event Details')
@section('content')
<div class="page-head"><div><h1>Personal Event Details</h1><p>View and manage your private calendar event.</p></div><div class="actions"><a class="btn" href="{{ route('calendar.index', ['month'=>$event->starts_at->format('Y-m')]) }}">← Back to Calendar</a><a class="btn primary" href="{{ route('calendar.edit', $event->getKey()) }}">Edit Event</a></div></div>
<div class="calendar-show-grid">
  <section class="card"><div class="card-head"><div><h3>Event Information</h3><p>Personal event details and schedule.</p></div></div><div class="card-body">
    <div class="calendar-event-identity"><div class="calendar-preview-icon">CE</div><div><h2>{{ $event->title }}</h2><span>Personal · Only visible to you</span></div></div>
    <div class="calendar-info-grid"><div><span>Start</span><strong>{{ $event->starts_at?->format('d M Y, H:i') }}</strong></div><div><span>End</span><strong>{{ $event->ends_at?->format('d M Y, H:i') ?? 'Not specified' }}</strong></div><div><span>Location</span><strong>{{ $event->location ?: 'Not specified' }}</strong></div><div><span>Schedule</span><strong>{{ $event->is_all_day ? 'All day' : 'Timed event' }}</strong></div></div>
    <div class="calendar-description"><span>Description / Notes</span><p>{{ $event->description ?: 'No notes for this event.' }}</p></div>
  </div></section>
  <section class="card"><div class="card-head"><div><h3>Event Summary</h3><p>Overview of this private appointment.</p></div></div><div class="card-body"><div class="calendar-summary-row"><span>Visibility</span><strong>Personal</strong></div><div class="calendar-summary-row"><span>Created</span><strong>{{ $event->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div><div class="calendar-summary-row"><span>Updated</span><strong>{{ $event->updated_at?->format('d M Y, H:i') ?? '—' }}</strong></div><form method="POST" action="{{ route('calendar.destroy', $event->getKey()) }}" id="deleteCalendarEventForm">@csrf @method('DELETE')<button type="button" class="btn calendar-delete" id="openCalendarDelete">Delete Event</button></form></div></section>
</div>
<div class="calendar-confirm-backdrop" id="calendarConfirm" hidden><div class="calendar-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="calendarDeleteTitle"><div class="calendar-confirm-icon">!</div><h3 id="calendarDeleteTitle">Delete Personal Event?</h3><p>This will permanently delete <strong>{{ $event->title }}</strong>. This cannot be undone.</p><div><button class="btn" type="button" id="cancelCalendarDelete">Cancel</button><button class="btn calendar-danger-btn" type="button" id="confirmCalendarDelete">Delete Event</button></div></div></div>
<script>
(() => {
  const modal = document.getElementById('calendarConfirm');
  const open = document.getElementById('openCalendarDelete');
  const cancel = document.getElementById('cancelCalendarDelete');
  const confirm = document.getElementById('confirmCalendarDelete');
  if (!modal || !open) return;
  open.addEventListener('click', () => {modal.hidden = false; cancel.focus();});
  cancel.addEventListener('click', () => {modal.hidden = true;open.focus();});
  modal.addEventListener('click', (e) => {if (e.target === modal) modal.hidden = true;});
  document.addEventListener('keydown', (e) => {if (e.key === 'Escape') modal.hidden = true;});
  confirm.addEventListener('click', () => document.getElementById('deleteCalendarEventForm').submit());
})();
</script>
@include('calendar._style')
@endsection
