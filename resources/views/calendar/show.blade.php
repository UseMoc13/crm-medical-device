@extends('layouts.app')
@section('title', 'Personal Event Details')
@section('content')
@php
    $firstDay = $event->starts_at->startOfDay();
    $lastDay = ($event->ends_at ?? $event->starts_at)->startOfDay();
    $preferredDate = request()->query('date');
    $preferredValid = is_string($preferredDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $preferredDate)
        && \Carbon\CarbonImmutable::parse($preferredDate)->betweenIncluded($firstDay, $lastDay);
    $dayValue = old('date', $preferredValid ? $preferredDate : $firstDay->format('Y-m-d'));
    $excludedDates = $event->excludedDays->map(fn ($item) => $item->excluded_date->format('Y-m-d'))->all();
@endphp
<div class="page-head">
    <div><h1>Personal Event Details</h1><p>View your appointment and manage its dates.</p></div>
    <div class="actions"><a class="btn" href="{{ route('calendar.index', ['month'=>$event->starts_at->format('Y-m'), 'view'=>'personal']) }}">&larr; Back to Calendar</a><a class="btn primary" href="{{ route('calendar.edit', ['event'=>$event->getKey()]) }}">Edit Event</a></div>
</div>
@if($errors->any())<div class="alert error">@foreach($errors->all() as $message)<div>{{ $message }}</div>@endforeach</div>@endif
<div class="calendar-show-grid">
    <section class="card">
        <div class="card-head"><div><h3>Event Information</h3><p>Personal event details and schedule.</p></div></div>
        <div class="card-body">
            <div class="calendar-event-identity"><div class="calendar-preview-icon">CE</div><div><h2>{{ $event->title }}</h2><span>Personal · Only visible to you</span></div></div>
            <div class="calendar-info-grid">
                <div><span>Start</span><strong>{{ $event->starts_at?->format('d M Y, H:i') }}</strong></div>
                <div><span>End</span><strong>{{ $event->ends_at?->format('d M Y, H:i') ?? 'Not specified' }}</strong></div>
                <div><span>Location</span><strong>{{ $event->location ?: 'Not specified' }}</strong></div>
                <div><span>Schedule</span><strong>{{ $event->is_all_day ? 'All day' : 'Timed event' }}</strong></div>
            </div>
            <div class="calendar-description"><span>Description / Notes</span><p>{{ $event->description ?: 'No notes for this event.' }}</p></div>
        </div>
    </section>
    <section class="card">
        <div class="card-head"><div><h3>Event Summary</h3><p>Only the owner can update or delete this event.</p></div></div>
        <div class="card-body">
            <div class="calendar-summary-row"><span>Visibility</span><strong>Personal</strong></div>
            <div class="calendar-summary-row"><span>Created</span><strong>{{ $event->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
            <div class="calendar-summary-row"><span>Updated</span><strong>{{ $event->updated_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
            @if($event->is_all_day)
                <div class="calendar-delete-section">
                    <h4>Remove from one day</h4>
                    <p>Remove this event from a selected day while keeping its other dates on your calendar.</p>
                    <form id="calendarDeleteOneDay" method="POST" action="{{ route('calendar.destroy-day', ['event' => $event->getKey()]) }}">
                        @csrf @method('DELETE')
                        <label for="calendarRemoveDate">Selected day</label>
                        <input type="date" id="calendarRemoveDate" name="date" value="{{ $dayValue }}" min="{{ $firstDay->format('Y-m-d') }}" max="{{ $lastDay->format('Y-m-d') }}" required>
                        <button type="button" class="btn calendar-delete" data-calendar-delete-mode="day">Remove This Day</button>
                    </form>
                    @if(count($excludedDates))
                        <div class="calendar-excluded-dates"><strong>Removed days:</strong> {{ collect($excludedDates)->map(fn ($d) => \Carbon\CarbonImmutable::parse($d)->format('d M Y'))->implode(', ') }}</div>
                    @endif
                </div>
            @endif
            <div class="calendar-delete-section">
                <h4>Delete entire event</h4>
                <p>Remove the whole event, including all remaining dates.</p>
                <form id="calendarDeleteEntire" method="POST" action="{{ route('calendar.destroy', ['event' => $event->getKey()]) }}">
                    @csrf @method('DELETE')
                    <button type="button" class="btn calendar-delete" data-calendar-delete-mode="all">Delete All Days</button>
                </form>
            </div>
        </div>
    </section>
</div>
<div class="calendar-confirm-backdrop" id="calendarConfirm" hidden>
    <div class="calendar-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="calendarDeleteTitle">
        <div class="calendar-confirm-icon">!</div>
        <h3 id="calendarDeleteTitle">Confirm deletion</h3>
        <p id="calendarDeleteDescription">Are you sure?</p>
        <div><button type="button" class="btn" id="cancelCalendarDelete">Cancel</button><button type="button" class="btn calendar-danger-btn" id="confirmCalendarDelete">Confirm Delete</button></div>
    </div>
</div>
<script>
(() => {
    const modal = document.getElementById('calendarConfirm');
    const cancel = document.getElementById('cancelCalendarDelete');
    const confirm = document.getElementById('confirmCalendarDelete');
    const date = document.getElementById('calendarRemoveDate');
    const excluded = @json($excludedDates);
    let target = null;
    let previousFocus = null;
    document.querySelectorAll('[data-calendar-delete-mode]').forEach(button => button.addEventListener('click', () => {
        const single = button.dataset.calendarDeleteMode === 'day';
        if (single && (!date?.value || !date.checkValidity())) { date?.reportValidity(); return; }
        if (single && excluded.includes(date.value)) { alert('This day has already been removed.'); return; }
        target = document.getElementById(single ? 'calendarDeleteOneDay' : 'calendarDeleteEntire');
        previousFocus = button;
        document.getElementById('calendarDeleteTitle').textContent = single ? 'Remove this day?' : 'Delete the entire event?';
        document.getElementById('calendarDeleteDescription').textContent = single
            ? `Remove this event only on ${date.value}? Other dates will remain.`
            : 'This will permanently remove the event on all its dates. This cannot be undone.';
        confirm.textContent = single ? 'Remove This Day' : 'Delete All Days';
        modal.hidden = false; cancel.focus();
    }));
    const dismiss = () => { modal.hidden = true; previousFocus?.focus(); };
    cancel.addEventListener('click', dismiss);
    modal.addEventListener('click', e => {if (e.target === modal) dismiss();});
    document.addEventListener('keydown', e => {if (!modal.hidden && e.key === 'Escape') dismiss();});
    // Submit natively after the single confirmation; prevent a second global submit-dialog.
    confirm.addEventListener('click', () => { if (target) HTMLFormElement.prototype.submit.call(target); });
})();
</script>
@include('calendar._style')
@endsection
