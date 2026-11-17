@php
    // Support both legacy single-link notes and the new multi-mention model.
    // All results stay scoped to the authenticated note owner.
    $linkedNotes = \App\Models\WorkspacePersonalNote::query()
        ->where('owner_user_id', (string) auth('web')->id())
        ->whereNull('archived_at')
        ->where(function ($query) use ($linkType, $linkId) {
            $legacy = $linkType === 'calendar' ? 'calendar_event_id' : 'activity_id';
            $query->where($legacy, $linkId)
                ->orWhereHas('mentions', fn ($mention) => $mention
                    ->where('target_type', $linkType)->where('target_id', $linkId));
        })
        ->orderByDesc('is_pinned')->orderByDesc('updated_at')->limit(12)->get();
    $newNoteParams = $linkType === 'calendar' ? ['calendar_event_id'=>$linkId] : ['activity_id'=>$linkId];
@endphp
<section class="card notes-linked-card">
    <div class="card-head"><div><h3>My Personal Notes</h3><p>Private notes mentioning this {{ $linkType === 'calendar' ? 'calendar event' : 'activity' }}. Never shared with collaborators.</p></div><a class="btn primary" href="{{ route('notes.create', $newNoteParams) }}">+ Add Note</a></div>
    <div class="card-body"><div class="notes-linked-list">
        @forelse($linkedNotes as $ln)
            @php $excerpt = preg_replace(\App\Services\WorkspaceNoteMentions::TOKEN, '@$2', (string) ($ln->body ?? '')) ?? ''; @endphp
            <a href="{{ route('notes.show', ['note'=>$ln->getKey()]) }}"><strong>{{ $ln->is_pinned ? '★ ' : '' }}{{ $ln->title }}</strong><small>{{ \Illuminate\Support\Str::limit($excerpt ?: 'No content yet', 95) }}</small></a>
        @empty <p>No private notes mentioning this record yet.</p>
        @endforelse
    </div></div>
</section>
