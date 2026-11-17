@php
    $linkedNotes = \App\Models\WorkspacePersonalNote::query()
        ->where('owner_user_id', (string)auth('web')->id())
        ->whereNull('archived_at')
        ->where($linkType === 'calendar' ? 'calendar_event_id' : 'activity_id', $linkId)
        ->orderByDesc('is_pinned')->orderByDesc('updated_at')->limit(12)->get();
    $newNoteParams = $linkType === 'calendar' ? ['calendar_event_id'=>$linkId] : ['activity_id'=>$linkId];
@endphp
<section class="card notes-linked-card"><div class="card-head"><div><h3>My Personal Notes</h3><p>Private notes linked to this {{ $linkType === 'calendar' ? 'calendar event' : 'activity' }}. They are not shared with collaborators.</p></div><a class="btn primary" href="{{ route('notes.create', $newNoteParams) }}">+ Add Note</a></div><div class="card-body"><div class="notes-linked-list">@forelse($linkedNotes as $ln)<a href="{{ route('notes.show', ['note'=>$ln->getKey()]) }}"><strong>{{ $ln->is_pinned ? '★ ' : '' }}{{ $ln->title }}</strong><small>{{ \Illuminate\Support\Str::limit($ln->body ?: 'No text', 95) }}{{ $ln->remind_at ? ' · Reminder '.$ln->remind_at->format('d M Y H:i') : '' }}</small></a>@empty<p>No private notes linked yet. Add one to keep personal context for this record.</p>@endforelse</div></div></section>
