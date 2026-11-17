@extends('layouts.app')
@section('title', 'Personal Notes')
@section('content')
<div class="page-head">
    <div><h1>Personal Notes</h1><p>Your private notes, available from any page through the topbar.</p></div>
    <div class="actions"><a href="{{ route('notes.create') }}" class="btn primary">+ New Note</a></div>
</div>
<section class="card"><div class="card-head"><div><h3>My Notes</h3><p>{{ $notes->total() }} matching notes</p></div></div><div class="card-body">
    <form class="notes-filter" method="GET" action="{{ route('notes.index') }}">
        <input type="search" name="search" maxlength="150" aria-label="Search personal notes" placeholder="Search title or content..." value="{{ request('search') }}">
        <select name="view" aria-label="Note status"><option value="active" @selected($view === 'active')>Active Notes</option><option value="archived" @selected($view === 'archived')>Archived</option><option value="all" @selected($view === 'all')>All</option></select>
        <button class="btn" type="submit">Search</button><a class="btn" href="{{ route('notes.index') }}">Reset</a>
    </form>
    <div class="notes-cards">@forelse($notes as $note)
        @php $excerpt = preg_replace(\App\Services\WorkspaceNoteMentions::TOKEN, '@$2', (string) ($note->body ?? '')) ?? ''; @endphp
        <article class="notes-item">
            <div class="notes-item-head"><span class="notes-icon">▤</span><div class="notes-badges">@if($note->is_pinned)<span class="notes-badge pinned">★ Pinned</span>@endif @if($note->archived_at)<span class="notes-badge">Archived</span>@endif</div></div>
            <a class="notes-item-title" href="{{ route('notes.show',['note'=>$note->getKey()]) }}">{{ $note->title }}</a>
            <p>{{ \Illuminate\Support\Str::limit($excerpt ?: 'No content yet.', 155) }}</p>
            <div class="notes-item-foot"><small>Updated {{ $note->updated_at?->format('d M Y') ?? 'recently' }}</small><a href="{{ route('notes.show',['note'=>$note->getKey()]) }}">Open Note</a></div>
        </article>
    @empty
        <div class="notes-empty"><div>▤</div><h3>No notes found</h3><p>Create your first note or change the search.</p><a class="btn primary" href="{{ route('notes.create') }}">+ New Note</a></div>
    @endforelse</div>
    @if($notes->hasPages())<div class="notes-pagination">{{ $notes->links() }}</div>@endif
</div></section>
@endsection
