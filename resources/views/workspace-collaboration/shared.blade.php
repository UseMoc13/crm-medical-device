@extends('layouts.app')
@section('title', 'Shared with Me')
@section('content')
@php
    $isActivity = $entityType === 'activity';
    $prefix = $isActivity ? 'activities' : 'todos';
    $key = $isActivity ? 'activity' : 'todo';
@endphp
<div class="page-head"><div><h1>{{ $isActivity ? 'Shared Activities' : 'Shared Work To-Dos' }}</h1><p>Records explicitly shared with you for viewing or collaboration.</p></div><div class="actions"><a href="{{ route($prefix.'.index') }}" class="btn">&larr; {{ $isActivity ? 'Activities' : 'To-Do' }}</a></div></div>
@include('workspace-collaboration._style')
<div class="a748-shell"><section class="card"><div class="card-head"><div><h3>Shared with Me</h3><p>{{ $entities->total() }} shared record(s). Team membership alone never creates access.</p></div></div><div class="a748-body"><div class="a748-table-wrap"><table class="a748-table"><thead><tr><th>Record</th><th>{{ $isActivity ? 'Type' : 'Priority' }}</th><th>{{ $isActivity ? 'Scheduled' : 'Due' }}</th><th>Open</th></tr></thead><tbody>
    @forelse($entities as $entity)
        <tr><td><strong>{{ $isActivity ? $entity->subject : $entity->title }}</strong></td><td>{{ $isActivity ? $entity->activity_type : ucfirst($entity->priority) }}</td><td>{{ $isActivity ? $entity->activity_date?->format('d M Y, H:i') : ($entity->due_at?->format('d M Y, H:i') ?? 'No due date') }}</td><td><a href="{{ route($prefix.'.show', [$key => $entity->getKey()]) }}" class="btn">View Details</a></td></tr>
    @empty
        <tr><td colspan="4"><div class="a748-empty">Nothing has been explicitly shared with you yet.</div></td></tr>
    @endforelse
</tbody></table></div><div class="a748-pagination">{{ $entities->withQueryString()->links() }}</div></div></section></div>
@endsection
