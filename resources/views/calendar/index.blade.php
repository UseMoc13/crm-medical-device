@extends('layouts.app')
@section('title', 'Calendar')
@section('content')
@php
    $offset = (int) $month->dayOfWeekIso - 1;
    $days = $month->daysInMonth;
    $cells = (int) ceil(($offset + $days) / 7) * 7;
    $today = now()->format('Y-m-d');
    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $prevMonth = $month->subMonth()->format('Y-m');
    $nextMonth = $month->addMonth()->format('Y-m');
    $currentYear = (int) $month->year;
    $yearFirst = min(2000, $currentYear);
    $yearLast = max(2100, $currentYear);
@endphp
<div class="page-head">
    <div><h1>Calendar</h1><p>Track work activities, TO-DO deadlines, and your private appointments in one place.</p></div>
    <div class="actions"><a class="btn" href="{{ route('notes.create') }}">+ New Personal Note</a><a class="btn primary" href="{{ route('calendar.create') }}">+ New Personal Event</a></div>
</div>
<div class="card calendar-main-card">
    <div class="card-head calendar-header">
        <div><h3>{{ $month->format('F Y') }}</h3><p>Private events remain visible only to your account.</p></div>
        <nav class="calendar-nav" aria-label="Month navigation">
            <a class="btn calendar-nav-arrow" href="{{ route('calendar.index', ['month' => $prevMonth, 'view' => $view]) }}" aria-label="Previous month" title="Previous month">&#8592;</a>
            <a class="btn" href="{{ route('calendar.index', ['month' => now()->format('Y-m'), 'view' => $view]) }}">Today</a>
            <a class="btn calendar-nav-arrow" href="{{ route('calendar.index', ['month' => $nextMonth, 'view' => $view]) }}" aria-label="Next month" title="Next month">&#8594;</a>
        </nav>
    </div>
    <div class="card-body">
        <div class="calendar-toolbar">
            <form class="calendar-month-picker" id="calendarMonthPicker" method="GET" action="{{ route('calendar.index') }}">
                <label for="calendarSelectMonth">Month</label>
                <select id="calendarSelectMonth" aria-label="Select month">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ sprintf('%02d', $m) }}" @selected((int) $month->month === $m)>{{ \Carbon\CarbonImmutable::create(2026, $m, 1)->format('F') }}</option>
                    @endfor
                </select>
                <label for="calendarSelectYear">Year</label>
                <select id="calendarSelectYear" aria-label="Select year">
                    @for($y = $yearFirst; $y <= $yearLast; $y++)
                        <option value="{{ $y }}" @selected($currentYear === $y)>{{ $y }}</option>
                    @endfor
                </select>
                <input type="hidden" name="month" id="calendarMonthValue" value="{{ $month->format('Y-m') }}">
                <input type="hidden" name="view" value="{{ $view }}">
                <button class="btn" type="submit">Go</button>
            </form>
            <nav class="calendar-view-toggle" aria-label="Calendar category">
                @foreach(['work' => 'Work', 'all' => 'All', 'personal' => 'Personal'] as $mode => $label)
                    <a href="{{ route('calendar.index', ['month' => $month->format('Y-m'), 'view' => $mode]) }}"
                       class="calendar-toggle-btn {{ $view === $mode ? 'active '.$mode : '' }}"
                       @if($view === $mode) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="calendar-legend"><span><i class="calendar-dot note"></i> Note Reminder</span><span><i class="calendar-dot activity"></i> Activity</span><span><i class="calendar-dot todo"></i> TO-DO</span><span><i class="calendar-dot personal"></i> Personal Event</span></div>
        </div>
        <div class="calendar-scroll">
            <div class="calendar-grid" role="grid" aria-label="{{ $month->format('F Y') }}">
                @foreach($labels as $label)<div class="calendar-weekday" role="columnheader">{{ $label }}</div>@endforeach
                @for($cell = 0; $cell < $cells; $cell++)
                    @php
                        $dayNumber = $cell - $offset + 1;
                        $current = $dayNumber >= 1 && $dayNumber <= $days ? $month->addDays($dayNumber - 1) : null;
                        $dateString = $current?->format('Y-m-d');
                        $daily = $dateString ? ($byDay[$dateString] ?? []) : [];
                        $remaining = max(0, count($daily) - 3);
                    @endphp
                    <div class="calendar-day {{ !$current ? 'outside' : '' }} {{ $dateString === $today ? 'today' : '' }}" role="gridcell" @if($current) data-calendar-date="{{ $dateString }}" tabindex="0" aria-label="View schedule for {{ $current->format('d F Y') }}, {{ count($daily) }} items" @endif>
                        @if($current)
                            <div class="calendar-day-head">
                                <button type="button" class="calendar-open-day" data-calendar-open="{{ $dateString }}" aria-label="View all events on {{ $current->format('d F Y') }}"><span class="calendar-day-number">{{ $dayNumber }}</span></button>
                                <a href="{{ route('calendar.create', ['date' => $dateString]) }}" title="Add personal event" aria-label="Add personal event on {{ $dateString }}">+</a>
                            </div>
                            <div class="calendar-day-events">
                                @foreach(array_slice($daily, 0, 3) as $item)
                                    <button class="calendar-event {{ $item['kind'] }}" type="button" data-calendar-open="{{ $dateString }}" title="{{ $item['title'] }} — {{ $item['subtitle'] }}">
                                        <span class="calendar-event-time">{{ $item['time_label'] }}</span>
                                        <span class="calendar-event-name">{{ $item['title'] }}</span>
                                    </button>
                                @endforeach
                                @if($remaining > 0)
                                    <button class="calendar-more" type="button" data-calendar-open="{{ $dateString }}">+{{ $remaining }} more</button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
        <p class="calendar-help">Select a date or an event to view the complete daily schedule and timeline. Calendar cells display up to three entries.</p>
    </div>
</div>
<div class="calendar-day-backdrop" id="calendarDayBackdrop" hidden>
    <section class="calendar-day-dialog" role="dialog" aria-modal="true" aria-labelledby="calendarDayTitle" tabindex="-1">
        <div class="calendar-day-dialog-head">
            <div><span class="calendar-day-kicker">DAILY SCHEDULE</span><h3 id="calendarDayTitle">Day details</h3><p id="calendarDayCount"></p></div>
            <button type="button" class="calendar-day-close" id="calendarDayClose" aria-label="Close schedule">&times;</button>
        </div>
        <div class="calendar-day-dialog-body" id="calendarDayTimeline"></div>
        <div class="calendar-day-dialog-foot"><a class="btn" id="calendarDayNote" href="{{ route('notes.create') }}">+ New Note</a><a class="btn" id="calendarDayAdd" href="{{ route('calendar.create') }}">+ New Personal Event</a><button type="button" class="btn primary" id="calendarDayDone">Done</button></div>
    </section>
</div>
<script>
(() => {
    const byDay = {{ \Illuminate\Support\Js::from($byDay) }};
    const monthSelect = document.getElementById('calendarSelectMonth');
    const yearSelect = document.getElementById('calendarSelectYear');
    const monthValue = document.getElementById('calendarMonthValue');
    document.getElementById('calendarMonthPicker')?.addEventListener('submit', () => {
        monthValue.value = `${yearSelect.value}-${monthSelect.value}`;
    });

    const backdrop = document.getElementById('calendarDayBackdrop');
    const dialog = backdrop.querySelector('.calendar-day-dialog');
    const timeline = document.getElementById('calendarDayTimeline');
    const title = document.getElementById('calendarDayTitle');
    const count = document.getElementById('calendarDayCount');
    const add = document.getElementById('calendarDayAdd');
    const noteAdd = document.getElementById('calendarDayNote');
    const closeBtn = document.getElementById('calendarDayClose');
    let focusReturn = null;
    const node = (tag, css, content) => {
        const el = document.createElement(tag);
        if (css) el.className = css;
        if (content !== undefined) el.textContent = String(content);
        return el;
    };
    const showDay = (date) => {
        const list = byDay[date] || [];
        focusReturn = document.activeElement;
        title.textContent = new Date(`${date}T12:00:00`).toLocaleDateString('en-US', {weekday:'long',month:'long',day:'numeric',year:'numeric'});
        count.textContent = `${list.length} scheduled ${list.length === 1 ? 'item' : 'items'}`;
        const base = @json(route('calendar.create'));
        add.href = `${base}?date=${encodeURIComponent(date)}`;
        noteAdd.href = `{{ route('notes.create') }}?date=${encodeURIComponent(date)}`;
        timeline.replaceChildren();
        if (!list.length) {
            timeline.append(node('div', 'calendar-day-empty', 'No events for this date. Add a personal event to get started.'));
        }
        for (const item of list) {
            const entry = node('div', 'calendar-timeline-entry');
            const clock = node('div', 'calendar-timeline-clock', item.time_label || '—');
            const bar = node('div', `calendar-timeline-bar ${item.kind || ''}`);
            const content = node('div', 'calendar-timeline-content');
            const line = node('div', 'calendar-timeline-topline');
            line.append(node('span', `calendar-timeline-kind ${item.kind || ''}`, item.kind === 'todo' ? 'TO-DO' : item.kind === 'activity' ? 'Activity' : 'Personal'));
            if (item.ongoing && !item.all_day) line.append(node('span', 'calendar-timeline-ongoing', 'Continues from earlier'));
            const name = node('a', 'calendar-timeline-name', item.title);
            name.href = item.url;
            content.append(line, name, node('p', 'calendar-timeline-subtitle', item.subtitle || ''));
            if (item.kind === 'personal' && item.all_day) {
                const eventLink = node('a', 'calendar-timeline-manage', 'Manage days / Delete');
                eventLink.href = `${item.url}?date=${encodeURIComponent(date)}`;
                content.append(eventLink);
            }
            entry.append(clock, bar, content);
            timeline.append(entry);
        }
        backdrop.hidden = false;
        document.body.classList.add('calendar-modal-open');
        closeBtn.focus();
    };
    const dismiss = () => {
        backdrop.hidden = true;
        document.body.classList.remove('calendar-modal-open');
        focusReturn?.focus?.();
    };
    document.querySelectorAll('[data-calendar-open]').forEach(el => el.addEventListener('click', e => { e.stopPropagation(); showDay(el.dataset.calendarOpen); }));
    document.querySelectorAll('[data-calendar-date]').forEach(el => {
        el.addEventListener('click', e => { if (e.target.closest('a,button')) return; showDay(el.dataset.calendarDate); });
        el.addEventListener('keydown', e => { if (e.target === el && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); showDay(el.dataset.calendarDate); } });
    });
    closeBtn.addEventListener('click', dismiss);
    document.getElementById('calendarDayDone').addEventListener('click', dismiss);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) dismiss(); });
    document.addEventListener('keydown', e => {
        if (backdrop.hidden) return;
        if (e.key === 'Escape') dismiss();
        if (e.key === 'Tab') {
            const focusable = [...dialog.querySelectorAll('a[href],button:not([disabled])')];
            if (!focusable.length) return;
            const first = focusable[0], last = focusable[focusable.length-1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
})();
</script>
@include('calendar._style')
@endsection
