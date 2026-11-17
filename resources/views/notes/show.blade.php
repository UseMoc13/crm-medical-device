@extends('layouts.app')
@section('title', 'Personal Note')
@section('content')
<div class="page-head"><div><h1>Personal Note</h1><p>Private note · Only visible to your account.</p></div><div class="actions"><a class="btn" href="{{ route('notes.index') }}">&larr; Back to Notes</a><a class="btn primary" href="{{ route('notes.edit',['note'=>$note->getKey()]) }}">Edit Note</a></div></div>
<section class="card notes-simple-detail">
    <div class="card-head"><div><h3>{{ $note->title }}</h3><p>Updated {{ $note->updated_at?->format('d M Y, H:i') ?? 'recently' }} · Private</p></div></div>
    <div class="card-body"><div class="notes-note-body">{!! app(\App\Services\WorkspaceNoteMentions::class)->render((string)($note->body ?: 'No content yet.'), (string)auth('web')->id()) !!}</div></div>
</section>
<section class="card notes-actions-card"><div class="card-head"><div><h3>Manage Note</h3><p>Manage this note without changing its content.</p></div></div><div class="card-body"><div class="notes-action-row"><form method="POST" action="{{ route('notes.pin',['note'=>$note->getKey()]) }}">@csrf @method('PATCH')<button class="btn" type="submit">{{ $note->is_pinned ? 'Unpin' : '★ Pin' }}</button></form>@if($note->archived_at)<form method="POST" action="{{ route('notes.restore',['note'=>$note->getKey()]) }}">@csrf @method('PATCH')<button class="btn" type="submit">Restore</button></form>@else<form method="POST" action="{{ route('notes.archive',['note'=>$note->getKey()]) }}">@csrf @method('PATCH')<button class="btn" type="submit">Archive</button></form>@endif<form method="POST" action="{{ route('notes.destroy',['note'=>$note->getKey()]) }}" data-delete-name="{{ $note->title }}">@csrf @method('DELETE')<button class="btn notes-danger" type="submit">Delete Note</button></form></div></div></section>
@endsection
