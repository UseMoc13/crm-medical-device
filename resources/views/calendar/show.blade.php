@extends('layouts.app')
@section('title', 'Personal Event Details')
@section('content')
@php
    $firstDay = \Carbon\CarbonImmutable::parse($event->starts_at)->startOfDay();
    $lastDay = \Carbon\CarbonImmutable::parse($event->ends_at ?? $event->starts_at)->startOfDay();
    $preferredDate = request()->query('date');
    $preferredValid = false;
    if (is_string($preferredDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $preferredDate)) {
        try {
            $candidate = \Carbon\CarbonImmutable::createFromFormat('!Y-m-d', $preferredDate);
            $preferredValid = $candidate && $candidate->format('Y-m-d') === $preferredDate
                && $candidate->betweenIncluded($firstDay, $lastDay);
        } catch (\Exception $exception) {
            $preferredValid = false;
        }
    }
    $excludedDates = $event->excludedDays->map(fn ($item) => $item->excluded_date->format('Y-m-d'))->all();
    $initialSelect = $preferredValid && !in_array($preferredDate, $excludedDates, true) ? $preferredDate : null;
    $oldSelectedDates = old('dates', []);
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
                <div class="calendar-delete-section" id="calendarManageDates">
                    <h4>Remove selected dates</h4>
                    <p>Select one or more days in the mini calendar. The event remains on the other days.</p>
                    <form id="calendarDeleteSelected" method="POST" action="{{ route('calendar.destroy-days', ['event' => $event->getKey()]) }}" data-calendar-bulk-form>
                        @csrf @method('DELETE')
                        <div class="calendar-mini-calendar" aria-label="Select event days to remove">
                            <div class="calendar-mini-header">
                                <button type="button" id="calendarMiniPrev" class="calendar-mini-arrow" aria-label="Previous month" title="Previous month">&#8592;</button>
                                <strong id="calendarMiniMonth">Month Year</strong>
                                <button type="button" id="calendarMiniNext" class="calendar-mini-arrow" aria-label="Next month" title="Next month">&#8594;</button>
                            </div>
                            <div class="calendar-mini-weekdays" aria-hidden="true">
                                @foreach(['M','T','W','T','F','S','S'] as $weekday)
                                    <span>{{ $weekday }}</span>
                                @endforeach
                            </div>
                            <div class="calendar-mini-days" id="calendarMiniDays" role="group" aria-label="Event date choices"></div>
                            <div class="calendar-mini-legend">
                                <span><i class="calendar-mini-legend-dot available"></i> Available</span>
                                <span><i class="calendar-mini-legend-dot selected"></i> Selected</span>
                                <span><i class="calendar-mini-legend-dot removed"></i> Removed</span>
                            </div>
                        </div>
                        <div class="calendar-mini-actions">
                            <button type="button" class="calendar-mini-textbtn" id="calendarMiniSelectMonth">Select available days this month</button>
                            <button type="button" class="calendar-mini-textbtn" id="calendarMiniClear">Clear selection</button>
                        </div>
                        <p id="calendarMiniSelectedStatus" class="calendar-mini-selection-status" role="status" aria-live="polite">No dates selected.</p>
                        <div id="calendarSelectedInputs"></div>
                        <button type="button" class="btn calendar-delete calendar-remove-selected" data-calendar-delete-mode="selected" id="calendarRemoveSelected" disabled>Remove Selected Days</button>
                    </form>
                    @if(count($excludedDates))
                        <div class="calendar-excluded-dates"><strong>Already removed:</strong> {{ count($excludedDates) }} day(s). These appear crossed out in the mini calendar.</div>
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
@include('notes._linked', ['linkType'=>'calendar', 'linkId'=>$event->getKey()])
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
    const selectedForm = document.getElementById('calendarDeleteSelected');
    const firstDate = @json($firstDay->format('Y-m-d'));
    const lastDate = @json($lastDay->format('Y-m-d'));
    const excluded = new Set(@json($excludedDates));
    const originalSelected = @json($oldSelectedDates);
    const initiallySelected = @json($initialSelect);
    const selected = new Set();
    const maxSelected = 366; // Matches server-side array validation.
    let visibleMonth = (initiallySelected || firstDate).slice(0, 7);
    let target = null;
    let previousFocus = null;

    const ymIndex = (date) => Number(date.slice(0, 4)) * 12 + Number(date.slice(5, 7)) - 1;
    const monthText = (year, month) => new Date(year, month - 1, 1).toLocaleDateString('en-US', {month: 'long', year: 'numeric'});
    const dateKey = (year, month, day) => `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    const isAllowed = (date) => date >= firstDate && date <= lastDate && !excluded.has(date);
    const minMonth = ymIndex(firstDate);
    const maxMonth = ymIndex(lastDate);
    const days = document.getElementById('calendarMiniDays');
    const miniTitle = document.getElementById('calendarMiniMonth');
    const previous = document.getElementById('calendarMiniPrev');
    const next = document.getElementById('calendarMiniNext');
    const selectMonth = document.getElementById('calendarMiniSelectMonth');
    const clear = document.getElementById('calendarMiniClear');
    const status = document.getElementById('calendarMiniSelectedStatus');
    const remove = document.getElementById('calendarRemoveSelected');
    const inputs = document.getElementById('calendarSelectedInputs');

    const formatShort = (date) => {
        const [y, m, d] = date.split('-').map(Number);
        return new Date(y, m - 1, d).toLocaleDateString('en-US', {day: 'numeric', month: 'short', year: 'numeric'});
    };
    const updateSelection = () => {
        if (!selectedForm) return;
        const values = [...selected].sort();
        remove.disabled = values.length === 0;
        if (!values.length) status.textContent = 'No dates selected. Choose days in the calendar above.';
        else {
            const summary = values.slice(0, 4).map(formatShort).join(', ');
            status.textContent = `${values.length} day(s) selected: ${summary}${values.length > 4 ? `, +${values.length - 4} more` : ''}`;
        }
        inputs.replaceChildren();
        for (const date of values) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'dates[]';
            input.value = date;
            inputs.appendChild(input);
        }
    };
    const render = () => {
        if (!selectedForm) return;
        const [year, month] = visibleMonth.split('-').map(Number);
        const thisMonthIndex = ymIndex(visibleMonth);
        miniTitle.textContent = monthText(year, month);
        previous.disabled = thisMonthIndex <= minMonth;
        next.disabled = thisMonthIndex >= maxMonth;
        days.replaceChildren();
        const monthStartDay = new Date(year, month - 1, 1).getDay();
        const preceding = (monthStartDay + 6) % 7;
        for (let i = 0; i < preceding; i++) {
            const empty = document.createElement('span');
            empty.className = 'calendar-mini-blank';
            empty.setAttribute('aria-hidden', 'true');
            days.appendChild(empty);
        }
        const daysInMonth = new Date(year, month, 0).getDate();
        let availableCount = 0;
        for (let day = 1; day <= daysInMonth; day++) {
            const date = dateKey(year, month, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'calendar-mini-day';
            button.textContent = String(day);
            button.title = formatShort(date);
            button.setAttribute('aria-label', formatShort(date));
            const inRange = date >= firstDate && date <= lastDate;
            if (!inRange) {
                button.classList.add('outside');
                button.disabled = true;
                button.title += ' (outside event)';
            } else if (excluded.has(date)) {
                button.classList.add('removed');
                button.disabled = true;
                button.title += ' (already removed)';
            } else {
                availableCount++;
                button.setAttribute('aria-pressed', selected.has(date) ? 'true' : 'false');
                if (selected.has(date)) button.classList.add('selected');
                button.addEventListener('click', () => {
                    if (selected.has(date)) selected.delete(date);
                    else if (selected.size < maxSelected) selected.add(date);
                    else { status.textContent = `Maximum ${maxSelected} days per submission.`; return; }
                    render();
                    updateSelection();
                    days.querySelectorAll('.calendar-mini-day').forEach(item => {
                        if (item.textContent === String(day) && !item.disabled) item.focus();
                    });
                });
            }
            days.appendChild(button);
        }
        selectMonth.disabled = !availableCount || selected.size >= maxSelected;
        clear.disabled = selected.size === 0;
    };

    if (selectedForm) {
        if (Array.isArray(originalSelected)) {
            for (const date of originalSelected) {
                if (typeof date === 'string' && isAllowed(date) && selected.size < maxSelected) selected.add(date);
            }
        }
        if (!selected.size && initiallySelected && isAllowed(initiallySelected)) selected.add(initiallySelected);
        previous.addEventListener('click', () => {
            const [y, m] = visibleMonth.split('-').map(Number);
            const date = new Date(y, m - 2, 1);
            visibleMonth = dateKey(date.getFullYear(), date.getMonth() + 1, 1).slice(0, 7);
            render();
        });
        next.addEventListener('click', () => {
            const [y, m] = visibleMonth.split('-').map(Number);
            const date = new Date(y, m, 1);
            visibleMonth = dateKey(date.getFullYear(), date.getMonth() + 1, 1).slice(0, 7);
            render();
        });
        selectMonth.addEventListener('click', () => {
            const [year, month] = visibleMonth.split('-').map(Number);
            const maxDay = new Date(year, month, 0).getDate();
            for (let day = 1; day <= maxDay; day++) {
                const date = dateKey(year, month, day);
                if (isAllowed(date) && selected.size < maxSelected) selected.add(date);
            }
            render(); updateSelection();
        });
        clear.addEventListener('click', () => { selected.clear(); render(); updateSelection(); });
        render(); updateSelection();
    }

    const dismiss = () => {
        modal.hidden = true;
        target = null;
        previousFocus?.focus?.();
    };
    document.querySelectorAll('[data-calendar-delete-mode]').forEach(button => button.addEventListener('click', () => {
        const bulk = button.dataset.calendarDeleteMode === 'selected';
        if (bulk && selected.size === 0) return;
        target = document.getElementById(bulk ? 'calendarDeleteSelected' : 'calendarDeleteEntire');
        previousFocus = button;
        document.getElementById('calendarDeleteTitle').textContent = bulk
            ? `Remove ${selected.size} selected day(s)?` : 'Delete the entire event?';
        document.getElementById('calendarDeleteDescription').textContent = bulk
            ? 'Only the selected dates will be excluded. Other dates remain in your Calendar. If no dates remain, the event itself will be deleted.'
            : 'This permanently deletes the whole event and all remaining dates. This action cannot be undone.';
        confirm.textContent = bulk ? `Remove ${selected.size} Day(s)` : 'Delete All Days';
        modal.hidden = false;
        cancel.focus();
    }));
    cancel?.addEventListener('click', dismiss);
    modal?.addEventListener('click', event => { if (event.target === modal) dismiss(); });
    document.addEventListener('keydown', event => {
        if (modal?.hidden) return;
        if (event.key === 'Escape') dismiss();
        if (event.key === 'Tab') {
            const buttons = [...modal.querySelectorAll('button:not([disabled])')];
            if (!buttons.length) return;
            if (event.shiftKey && document.activeElement === buttons[0]) { event.preventDefault(); buttons[buttons.length - 1].focus(); }
            else if (!event.shiftKey && document.activeElement === buttons[buttons.length - 1]) { event.preventDefault(); buttons[0].focus(); }
        }
    });
    // Bypass any global form-submit confirmation: this page already has one explicit dialog.
    confirm?.addEventListener('click', () => { if (target) HTMLFormElement.prototype.submit.call(target); });
})();
</script>
@include('calendar._style')
@endsection
