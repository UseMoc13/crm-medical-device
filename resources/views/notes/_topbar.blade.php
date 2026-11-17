@auth
@php
    $topbarNotes = \App\Models\WorkspacePersonalNote::query()
        ->where('owner_user_id', (string) auth('web')->id())
        ->whereNull('archived_at')
        ->orderByDesc('is_pinned')->orderByDesc('updated_at')
        ->limit(5)->get(['note_id','title','body','is_pinned','updated_at']);
@endphp
<details class="notes-topbar-menu" id="crmPersonalNotesMenu">
    <summary class="icon-btn notes-topbar-toggle" title="Personal Notes" aria-label="Personal Notes" aria-haspopup="true">
        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h9l4 4v14H4V3h4Z"/><path d="M16 3v5h5M8 12h9M8 16h7"/></svg>
    </summary>
    <div class="notes-topbar-panel" role="group" aria-label="Your private notes">
        <div class="notes-topbar-head"><div><strong>Personal Notes</strong><small>Only visible to you</small></div><a class="btn primary" href="{{ route('notes.create') }}">+ New</a></div>
        <div class="notes-topbar-items">
            @forelse($topbarNotes as $quickNote)
                <a href="{{ route('notes.show', ['note'=>$quickNote->getKey()]) }}" class="notes-topbar-item"><strong>{{ $quickNote->is_pinned ? '★ ' : '' }}{{ $quickNote->title }}</strong><small>{{ \Illuminate\Support\Str::limit($quickNote->body ?: 'No additional text', 70) }}</small></a>
            @empty
                <p class="notes-topbar-empty">No notes yet. Create a private note to keep important details close.</p>
            @endforelse
        </div>
        <a class="notes-topbar-all" href="{{ route('notes.index') }}">View all personal notes</a>
    </div>
</details>
<script>
(() => {
    const menu = document.getElementById('crmPersonalNotesMenu');
    if (!menu) return;
    document.addEventListener('click', event => { if (!menu.contains(event.target)) menu.open = false; });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && menu.open) { menu.open = false; menu.querySelector('summary')?.focus(); }});
})();
</script>
@endauth
