@extends('layouts.app')
@section('title', 'To-Do')
@section('content')
<div class="page-head"><div><h1>To-Do</h1><p>Plan, prioritize, and track your personal and CRM work tasks.</p></div><div class="actions"><a href="{{ route('todos.create') }}" class="btn primary">+ New To-Do</a></div></div>
<div class="todo-stats">
    <div class="card todo-stat"><span>Total Tasks</span><strong>{{ (int) ($summary->total ?? 0) }}</strong><small>All your tasks</small></div>
    <div class="card todo-stat"><span>Completed</span><strong>{{ (int) ($summary->completed ?? 0) }}</strong><small>Finished tasks</small></div>
    <div class="card todo-stat"><span>Overdue</span><strong class="todo-danger">{{ (int) ($summary->overdue ?? 0) }}</strong><small>Unfinished past deadline</small></div>
</div>
<section class="card todo-list-card"><div class="card-head"><div><h3>My Task List</h3><p>{{ $todos->total() }} tasks found</p></div></div><div class="card-body">
<form class="todo-filter-bar" method="GET" action="{{ route('todos.index') }}">
 <div class="todo-search"><span aria-hidden="true">⌕</span><input name="search" placeholder="Search title or description..." value="{{ request('search') }}" aria-label="Search tasks"></div>
 <select name="visibility" aria-label="Category"><option value="">All Categories</option><option value="personal" @selected(request('visibility')==='personal')>Personal</option><option value="work" @selected(request('visibility')==='work')>Work</option></select>
 <select name="status" aria-label="Status"><option value="">All Statuses</option>@foreach(['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $code=>$label)<option value="{{ $code }}" @selected(request('status')===$code)>{{ $label }}</option>@endforeach</select>
 <select name="priority" aria-label="Priority"><option value="">All Priorities</option>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $code=>$label)<option value="{{ $code }}" @selected(request('priority')===$code)>{{ $label }}</option>@endforeach</select>
 <select name="sort" aria-label="Sort by"><option value="created_at" @selected(request('sort','created_at')==='created_at')>Created Date</option><option value="due_at" @selected(request('sort')==='due_at')>Due Date</option><option value="title" @selected(request('sort')==='title')>Task Title</option><option value="priority" @selected(request('sort')==='priority')>Priority</option><option value="status" @selected(request('sort')==='status')>Status</option></select>
 <select name="direction" aria-label="Sort direction"><option value="desc" @selected(request('direction','desc')==='desc')>↓ Descending</option><option value="asc" @selected(request('direction')==='asc')>↑ Ascending</option></select>
 <button class="btn" type="submit">Filter</button>@if(request()->hasAny(['search','visibility','status','priority','sort','direction']))<a class="btn" href="{{ route('todos.index') }}">Reset</a>@endif
</form>
@if(request()->filled('search') || request()->filled('visibility') || request()->filled('status') || request()->filled('priority'))<div class="todo-active-filters">Showing filtered results @foreach(['search'=>'Search','visibility'=>'Category','status'=>'Status','priority'=>'Priority'] as $field=>$label)@if(request()->filled($field))<span>{{ $label }}: {{ request($field) }}</span>@endif @endforeach</div>@endif
<div class="table-wrap todo-table-wrap"><table class="todo-table"><thead><tr><th class="todo-no">No.</th><th>Task Information</th><th>Category</th><th>Due Date</th><th>Priority</th><th>Status</th><th class="todo-actions-col">Actions</th></tr></thead><tbody>
@forelse($todos as $todo)
 @php
 $overdue = $todo->due_at && $todo->due_at->isPast() && !in_array($todo->status,['completed','cancelled'],true);
 @endphp
 <tr><td class="todo-no">{{ ($todos->firstItem() ?? 1) + $loop->index }}</td><td><a class="todo-title-link" href="{{ route('todos.show',['todo'=>$todo->getKey()]) }}">{{ $todo->title }}</a><small class="todo-subtitle">{{ \Illuminate\Support\Str::limit($todo->description ?: 'No additional description.',75) }}</small></td>
 <td><span class="todo-tag {{ $todo->visibility==='work'?'todo-tag-work':'todo-tag-personal' }}">{{ ucfirst($todo->visibility) }}</span></td>
 <td><strong>{{ $todo->due_at?->format('d M Y') ?? 'Not scheduled' }}</strong><small class="todo-subtitle">{{ $todo->due_at?->format('H:i') ?? '—' }} @if($overdue)<span class="todo-danger">· Overdue</span>@endif</small></td>
 <td><span class="todo-tag todo-priority-{{ $todo->priority }}">{{ ucfirst($todo->priority) }}</span></td>
 <td><span class="todo-tag todo-status-{{ $todo->status }}">{{ ucwords(str_replace('_',' ',$todo->status)) }}</span></td>
 <td><div class="todo-table-actions"><a class="todo-action view" title="View" aria-label="View task" href="{{ route('todos.show',['todo'=>$todo->getKey()]) }}"><svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a><a class="todo-action edit" title="Edit" aria-label="Edit task" href="{{ route('todos.edit',['todo'=>$todo->getKey()]) }}"><svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></a>
 <form method="POST" action="{{ route('todos.destroy',['todo'=>$todo->getKey()]) }}" data-delete-name="{{ $todo->title }}">@csrf @method('DELETE')<button class="todo-action delete" title="Delete" aria-label="Delete task" type="submit"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg></button></form>
 @if(!in_array($todo->status,['completed','cancelled'],true))<form method="POST" action="{{ route('todos.complete',['todo'=>$todo->getKey()]) }}">@csrf @method('PATCH')<button class="todo-action complete" title="Mark completed" aria-label="Mark task completed" type="submit">✓</button></form>@endif</div></td></tr>
@empty<tr><td colspan="7"><div class="todo-empty"><div class="todo-empty-icon">☑</div><h4>No tasks found</h4><p>Create your first task or adjust your search and filters.</p><a href="{{ route('todos.create') }}" class="btn primary">+ New To-Do</a></div></td></tr>@endforelse
</tbody></table></div>
@if($todos->hasPages())<div class="todo-pagination">{{ $todos->links() }}</div>@endif
<div class="todo-table-footer">Showing {{ $todos->firstItem() ?? 0 }} to {{ $todos->lastItem() ?? 0 }} of {{ $todos->total() }} To-Do tasks</div>
</div></section>
@include('todos._style')
@endsection
