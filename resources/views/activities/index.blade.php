@extends('layouts.app')
@section('title', 'Activities')
@push('styles')
    @include('activities._style')
@endpush
@section('content')
<div class="page-head">
    <div>
        <h1>Activities</h1>
        <p>Manage customer interactions, follow-ups, meetings, and other CRM activities.</p>
    </div>
    <div class="actions">
        <a href="{{ route('activities.create') }}" class="btn primary">+ New Activity</a>
    </div>
</div>

<section class="card activity-list-card">
    <div class="card-head">
        <div>
            <h3>Activity List</h3>
            <p>{{ $activities->total() }} {{ $activities->total() === 1 ? 'activity' : 'activities' }} found</p>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activities.index') }}" class="activity-list-filter" role="search">
            <div class="activity-list-search">
                <span class="activity-search-icon" aria-hidden="true">⌕</span>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search subject or description..." aria-label="Search activities" maxlength="150" autocomplete="off">
                @if(request('search'))
                    <a href="{{ route('activities.index', request()->except('search', 'page')) }}" class="activity-search-clear" aria-label="Clear search" title="Clear search">×</a>
                @endif
            </div>
            <div class="activity-filter-select">
                <select name="type" aria-label="Activity type">
                    <option value="">All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="activity-date-filter">
                <label for="activity-from">From</label>
                <input id="activity-from" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
            </div>
            <div class="activity-date-filter">
                <label for="activity-to">To</label>
                <input id="activity-to" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
            </div>
            <button type="submit" class="btn activity-filter-button">Filter</button>
            @if(request()->hasAny(['search', 'type', 'from', 'to']))
                <a class="btn activity-reset-button" href="{{ route('activities.index') }}">Reset</a>
            @endif
        </form>

        @if(request()->filled('search') || request()->filled('type') || request()->filled('from') || request()->filled('to'))
            <div class="activity-list-filter-summary">
                <span>Showing filtered results</span>
                @if(request()->filled('search'))<span class="activity-filter-chip">Search: “{{ request('search') }}”</span>@endif
                @if(request()->filled('type'))<span class="activity-filter-chip">Type: {{ request('type') }}</span>@endif
                @if(request()->filled('from'))<span class="activity-filter-chip">From: {{ request('from') }}</span>@endif
                @if(request()->filled('to'))<span class="activity-filter-chip">To: {{ request('to') }}</span>@endif
            </div>
        @endif

        <div class="activity-list-table-wrap table-wrap">
            <table class="activity-list-table">
                <thead><tr>
                    <th class="activity-col-no">No.</th>
                    <th class="activity-col-title">Activity Information</th>
                    <th>Type</th>
                    <th>Schedule</th>
                    <th>Related CRM</th>
                    <th>Owner</th>
                    <th class="activity-col-actions">Actions</th>
                </tr></thead>
                <tbody>
                @forelse($activities as $activity)
                    @php
                        $activityId = $activity->getKey();
                        $activityType = trim((string) ($activity->activity_type ?: 'Other'));
                        $typeSlug = \Illuminate\Support\Str::slug($activityType);
                        $related = array_filter([
                            $activity->customer?->customer_name ? 'Customer: '.$activity->customer->customer_name : null,
                            ($activity->lead?->contact_name ?: $activity->lead?->company_name) ? 'Lead: '.($activity->lead?->contact_name ?: $activity->lead?->company_name) : null,
                            ($activity->opportunity?->opportunity_name ?? $activity->opportunity?->name) ? 'Opportunity: '.($activity->opportunity?->opportunity_name ?? $activity->opportunity?->name) : null,
                        ]);
                    @endphp
                    <tr>
                        <td class="activity-col-no">{{ ($activities->firstItem() ?? 1) + $loop->index }}</td>
                        <td>
                            <a class="activity-list-subject" href="{{ route('activities.show', ['activity' => $activityId]) }}">{{ $activity->subject }}</a>
                            <div class="activity-list-subtitle">{{ \Illuminate\Support\Str::limit($activity->description ?: 'No description provided.', 78) }}</div>
                        </td>
                        <td><span class="activity-list-type activity-list-type-{{ $typeSlug }}">{{ $activityType }}</span></td>
                        <td class="activity-list-schedule">
                            <strong>{{ $activity->activity_date?->format('d M Y') ?? 'Not scheduled' }}</strong>
                            <small>{{ $activity->activity_date?->format('H:i') ?? '—' }}</small>
                        </td>
                        <td>
                            @if(count($related))
                                @foreach($related as $relatedLabel)
                                    <div class="activity-related-line">{{ $relatedLabel }}</div>
                                @endforeach
                            @else
                                <span class="activity-list-muted">No CRM reference</span>
                            @endif
                        </td>
                        <td><span class="activity-list-owner">{{ $activity->user?->name ?? '—' }}</span></td>
                        <td>
                            <div class="activity-list-actions">
                                <a class="activity-action-btn view" href="{{ route('activities.show', ['activity' => $activityId]) }}" title="View" aria-label="View activity">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                @if((string) $activity->user_id === (string) auth()->id())
                                    <a class="activity-action-btn edit" href="{{ route('activities.edit', ['activity' => $activityId]) }}" title="Edit" aria-label="Edit activity">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('activities.destroy', ['activity' => $activityId]) }}" data-delete-name="{{ $activity->subject }}" class="activity-list-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button class="activity-action-btn delete" type="submit" title="Delete" aria-label="Delete activity">
                                            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="activity-empty-state"><div class="activity-empty-icon">◷</div><h4>No activities found</h4><p>Create a new activity or adjust your search and filters.</p><a href="{{ route('activities.create') }}" class="btn primary">+ New Activity</a></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="activity-list-pagination">{{ $activities->links() }}</div>
        @endif
        <div class="activity-list-footer">Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} Activities</div>
    </div>
</section>

@endsection
