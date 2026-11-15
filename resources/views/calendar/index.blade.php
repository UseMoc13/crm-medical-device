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
@endphp
<div class="page-head">
  <div><h1>Calendar</h1><p>Manage personal appointments and track your CRM activities and task deadlines.</p></div>
  <div class="actions"><a class="btn primary" href="{{ route('calendar.create') }}">+ New Personal Event</a></div>
</div>
<div class="card calendar-main-card">
  <div class="card-head calendar-header">
    <div><h3>{{ $month->format('F Y') }}</h3><p>Calendar workspace · Personal events are visible only to you.</p></div>
    <div class="calendar-nav">
      <a class="btn" href="{{ route('calendar.index', ['month'=>$prevMonth, 'view'=>$view]) }}" aria-label="Previous month">‹</a>
      <a class="btn" href="{{ route('calendar.index', ['month'=>now()->format('Y-m'), 'view'=>$view]) }}">Today</a>
      <a class="btn" href="{{ route('calendar.index', ['month'=>$nextMonth, 'view'=>$view]) }}" aria-label="Next month">›</a>
    </div>
  </div>
  <div class="card-body">
    <form method="GET" action="{{ route('calendar.index') }}" class="calendar-filter-bar">
      <input type="month" name="month" value="{{ $month->format('Y-m') }}" aria-label="Select month">
      <select name="view" aria-label="Calendar view">
        <option value="all" @selected($view === 'all')>All — Work & Personal</option>
        <option value="work" @selected($view === 'work')>Work — Activities & To-Do</option>
        <option value="personal" @selected($view === 'personal')>Personal Events</option>
      </select>
      <button class="btn" type="submit">Apply</button>
      <div class="calendar-legend" aria-label="Legend"><span><i class="calendar-dot personal"></i> Personal</span><span><i class="calendar-dot activity"></i> Activity</span><span><i class="calendar-dot todo"></i> TO-DO</span></div>
    </form>
    <div class="calendar-scroll">
      <div class="calendar-grid" role="grid" aria-label="{{ $month->format('F Y') }}">
        @foreach($labels as $label)<div class="calendar-weekday">{{ $label }}</div>@endforeach
        @for($cell = 0; $cell < $cells; $cell++)
          @php
            $dayNumber = $cell - $offset + 1;
            $current = $dayNumber >= 1 && $dayNumber <= $days ? $month->addDays($dayNumber - 1) : null;
            $dateString = $current?->format('Y-m-d');
            $daily = $dateString ? ($byDay[$dateString] ?? []) : [];
          @endphp
          <div class="calendar-day {{ !$current ? 'outside' : '' }} {{ $dateString === $today ? 'today' : '' }}" role="gridcell">
            @if($current)
              <div class="calendar-day-head"><span class="calendar-day-number">{{ $dayNumber }}</span><a href="{{ route('calendar.create', ['date'=>$dateString]) }}" title="Add personal event on {{ $dateString }}" aria-label="Add personal event">+</a></div>
              <div class="calendar-day-events">
                @foreach(array_slice($daily, 0, 4) as $item)
                  <a class="calendar-event {{ $item['kind'] }}" href="{{ $item['url'] }}" title="{{ $item['title'] }} — {{ $item['subtitle'] }}">
                    <span class="calendar-event-time">{{ $item['all_day'] ? 'All day' : \Illuminate\Support\Carbon::parse($item['start'])->format('H:i') }}</span>
                    <span class="calendar-event-name">{{ $item['title'] }}</span>
                  </a>
                @endforeach
                @if(count($daily) > 4)<span class="calendar-more">+{{ count($daily) - 4 }} more</span>@endif
              </div>
            @endif
          </div>
        @endfor
      </div>
    </div>
  </div>
</div>
@include('calendar._style')
@endsection
