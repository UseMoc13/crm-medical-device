@extends('layouts.app')

@section('title', 'Audit Trail')

@push('styles')
    @include('workspace-audit._style')
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>Audit Trail</h1>
        <p>Read-only history of important workspace actions. Sensitive note contents, review comments and files are not recorded here.</p>
    </div>
</div>

<div class="card wsa-card">
    <div class="card-head">
        <div><h3>Workspace events</h3><p>{{ $events->total() }} matching events</p></div>
        <span class="wsa-readonly">Read-only</span>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('workspace-audit.index') }}" class="wsa-filters">
            <label>Event type
                <select name="event_type">
                    <option value="">All events</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" @selected(($filters['event_type'] ?? '') === $type)>{{ str_replace(['.', '_'], ' ', ucwords($type, '.')) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Subject
                <select name="subject_type">
                    <option value="">All subjects</option>
                    @foreach($subjectTypes as $type)
                        <option value="{{ $type }}" @selected(($filters['subject_type'] ?? '') === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Record UUID
                <input type="text" name="subject_id" placeholder="Filter by record ID" value="{{ $filters['subject_id'] ?? '' }}" maxlength="36">
            </label>
            <label>From
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            </label>
            <label>To
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            </label>
            <label>Actor UUID
                <input type="text" name="actor_user_id" placeholder="Filter by user ID" value="{{ $filters['actor_user_id'] ?? '' }}" maxlength="36">
            </label>
            <div class="wsa-filter-actions">
                <button type="submit" class="wsa-button">Apply</button>
                <a href="{{ route('workspace-audit.index') }}" class="wsa-reset">Reset</a>
            </div>
        </form>
    </div>
    <div class="wsa-table-wrap">
        <table class="wsa-table">
            <thead><tr><th>Time</th><th>Event</th><th>Actor</th><th>Subject</th><th>Safe details</th></tr></thead>
            <tbody>
                @forelse($events as $event)
                    <tr>
                        <td class="wsa-nowrap">{{ $event->created_at?->format('d M Y, H:i:s') ?? '—' }}</td>
                        <td><span class="wsa-event">{{ str_replace(['.', '_'], ' ', $event->event_type) }}</span></td>
                        <td>
                            <strong>{{ $event->actor?->name ?? 'System / deleted user' }}</strong>
                            <small>{{ $event->actor_user_id ?? '—' }}</small>
                        </td>
                        <td>
                            <strong>{{ ucfirst(str_replace('_', ' ', $event->subject_type)) }}</strong>
                            <small>{{ $event->subject_id ?? '—' }}</small>
                        </td>
                        <td><code>{{ json_encode($event->details ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="wsa-empty">No audit events match these filters yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="wsa-pager">{{ $events->links() }}</div>
</div>
@endsection
