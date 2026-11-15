@extends('layouts.app')
@section('title', 'To-Do')
@section('content')
@php
    $activeScope = $scope ?? request('scope','all');
    $quickScopes = ['all'=>'All Tasks','created'=>'Created by Me','assigned'=>'Assigned to Me','personal'=>'Personal'];
@endphp
<div class="page-head todo-page-head">
    <div><h1>To-Do Management</h1><p>Plan assignments, track deadlines, and review work submissions in one workspace.</p></div>
    <div class="actions"><a href="{{ route('todos.create') }}" class="btn primary">+ New To-Do</a></div>
</div>
<div class="todo-dashboard-stats">
    <div class="card todo-dashboard-stat"><div class="todo-stat-icon total">☑</div><div><span>Visible Tasks</span><strong>{{ (int) ($summary->total ?? 0) }}</strong><small>My tasks and assigned work</small></div></div>
    <div class="card todo-dashboard-stat"><div class="todo-stat-icon upcoming">◷</div><div><span>Due This Week</span><strong>{{ (int) ($summary->upcoming ?? 0) }}</strong><small>Next seven days</small></div></div>
    <div class="card todo-dashboard-stat"><div class="todo-stat-icon overdue">!</div><div><span>Overdue</span><strong>{{ (int) ($summary->overdue ?? 0) }}</strong><small>Needs attention</small></div></div>
    <div class="card todo-dashboard-stat"><div class="todo-stat-icon done">✓</div><div><span>Completed</span><strong>{{ (int) ($summary->completed ?? 0) }}</strong><small>Finished work items</small></div></div>
</div>
<section class="card todo-list-card">
    <div class="card-head todo-list-card-heading"><div><h3>Task Workspace</h3><p>{{ $todos->total() }} matching tasks · {{ (int)$assignedCount }} total assignments received</p></div><a class="btn todo-calendar-link" href="{{ route('calendar.index') }}">View Calendar ↗</a></div>
    <div class="card-body">
        <nav class="todo-view-tabs" aria-label="Task views">
            @foreach($quickScopes as $code => $label)
                <a href="{{ route('todos.index', array_merge(request()->except('page'), ['scope'=>$code])) }}" class="todo-view-tab {{ $activeScope === $code ? 'active' : '' }}" @if($activeScope === $code) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <form class="todo-filter-bar" method="GET" action="{{ route('todos.index') }}">
            <input type="hidden" name="scope" value="{{ $activeScope }}">
            <div class="todo-search"><span aria-hidden="true">⌕</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Search task title or description..." aria-label="Search tasks"></div>
            <select name="visibility" aria-label="Category"><option value="">All Categories</option><option value="work" @selected(request('visibility')==='work')>Work</option><option value="personal" @selected(request('visibility')==='personal')>Personal</option></select>
            <select name="status" aria-label="Status"><option value="">All Statuses</option>@foreach(['todo'=>'To Do','in_progress'=>'In Progress','completed'=>'Completed','cancelled'=>'Cancelled'] as $code=>$label)<option value="{{ $code }}" @selected(request('status')===$code)>{{ $label }}</option>@endforeach</select>
            <select name="priority" aria-label="Priority"><option value="">All Priorities</option>@foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $code=>$label)<option value="{{ $code }}" @selected(request('priority')===$code)>{{ $label }}</option>@endforeach</select>
            <select name="sort" aria-label="Sort"><option value="created_at" @selected(request('sort','created_at')==='created_at')>Created Date</option><option value="due_at" @selected(request('sort')==='due_at')>Deadline</option><option value="title" @selected(request('sort')==='title')>Task Title</option><option value="priority" @selected(request('sort')==='priority')>Priority</option><option value="status" @selected(request('sort')==='status')>Status</option></select>
            <select name="direction" aria-label="Direction"><option value="desc" @selected(request('direction','desc')==='desc')>↓ Desc</option><option value="asc" @selected(request('direction')==='asc')>↑ Asc</option></select>
            <button type="submit" class="btn todo-filter-button">Filter</button>
            @if(request()->hasAny(['search','visibility','status','priority','sort','direction']))<a class="btn" href="{{ route('todos.index', ['scope'=>$activeScope]) }}">Reset</a>@endif
        </form>
        <div class="table-wrap todo-table-wrap"><table class="todo-table">
            <thead><tr><th class="todo-no">No.</th><th>Task Information</th><th>Category</th><th>Assigned To</th><th>Schedule</th><th>Priority</th><th>Progress</th><th>Status</th><th class="todo-actions-col">Actions</th></tr></thead>
            <tbody>
            @forelse($todos as $todo)
                @php
                    $isOwner = (string)$todo->owner_user_id === (string) auth()->id();
                    $people = $todo->assignments;
                    $all = $people->count();
                    $finished = $people->where('status','completed')->count();
                    $mandatory = $people->where('completion_required',true);
                    $mandatoryPending = $mandatory->where('status','!=','completed')->count();
                    $mandatoryDone = $mandatory->where('status','completed')->count();
                    $overdue = $todo->due_at && $todo->due_at->isPast() && !in_array($todo->status,['completed','cancelled'],true);
                    $progress = $mandatory->count() ? (int) round($mandatoryDone * 100 / $mandatory->count()) : ($todo->status === 'completed' ? 100 : 0);
                @endphp
                <tr>
                    <td class="todo-no">{{ ($todos->firstItem() ?? 1) + $loop->index }}</td>
                    <td class="todo-task-cell"><a href="{{ route('todos.show',['todo'=>$todo->getKey()]) }}" class="todo-title-link">{{ $todo->title }}</a><small class="todo-subtitle">{{ \Illuminate\Support\Str::limit($todo->description ?: 'No additional notes.', 70) }}</small><small class="todo-owner-line">{{ $isOwner ? 'Created by you' : 'Assigned by '.$todo->owner?->name }}</small></td>
                    <td><span class="todo-tag todo-tag-{{ $todo->visibility }}">{{ ucfirst($todo->visibility) }}</span></td>
                    <td>
                        @if($todo->visibility === 'personal')<span class="todo-assignee-muted">Private — only me</span>
                        @elseif(!$all)<span class="todo-assignee-muted">Solo work task</span>
                        @else
                            <div class="todo-assignee-stack">
                                <div class="todo-avatar-group" aria-hidden="true">
                                    @foreach($people->take(3) as $member)<span title="{{ $member->assignee?->name ?? 'User' }}">{{ mb_strtoupper(mb_substr((string)($member->assignee?->name ?? 'U'),0,1)) }}</span>@endforeach
                                    @if($all > 3)<span>+{{ $all - 3 }}</span>@endif
                                </div><small>{{ $all }} {{ $all === 1 ? 'assignee' : 'assignees' }}</small>
                            </div>
                        @endif
                    </td>
                    <td><span class="todo-date-value {{ $overdue ? 'overdue' : '' }}">{{ $todo->due_at?->format('d M Y') ?? 'Unscheduled' }}</span><small class="todo-date-caption">{{ $todo->starts_at?->format('d M') ? 'Starts '.$todo->starts_at->format('d M') : ($todo->due_at?->format('H:i') ?? 'No deadline') }}</small></td>
                    <td><span class="todo-tag todo-priority-{{ $todo->priority }}">{{ ucfirst($todo->priority) }}</span></td>
                    <td>
                        @if($all)
                            <div class="todo-progress-top"><strong>{{ $mandatory->count() ? $mandatoryDone.'/'.$mandatory->count() : 'Optional' }}</strong><small>{{ $mandatory->count() ? 'mandatory done' : 'no required' }}</small></div><div class="todo-progress-track"><span style="width:{{ $progress }}%"></span></div>
                        @else<span class="todo-assignee-muted">{{ $todo->status === 'completed' ? 'Done' : 'Individual' }}</span>@endif
                    </td>
                    <td><span class="todo-tag todo-status-{{ $todo->status }}">{{ ucwords(str_replace('_',' ',$todo->status)) }}</span></td>
                    <td><div class="todo-table-actions">
                        <a class="todo-action view" title="View" aria-label="View task" href="{{ route('todos.show',['todo'=>$todo->getKey()]) }}"><svg viewBox="0 0 24 24"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a>
                        @if($isOwner)
                            <a class="todo-action edit" title="Edit" aria-label="Edit task" href="{{ route('todos.edit',['todo'=>$todo->getKey()]) }}"><svg viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></a>
                            <form method="POST" action="{{ route('todos.destroy',['todo'=>$todo->getKey()]) }}" data-delete-name="{{ $todo->title }}">@csrf @method('DELETE')<button class="todo-action delete" type="submit" aria-label="Delete task" title="Delete"><svg viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v5M14 11v5"/></svg></button></form>
                            @if(!in_array($todo->status,['completed','cancelled'],true))
                                <form method="POST" action="{{ route('todos.complete',['todo'=>$todo->getKey()]) }}">@csrf @method('PATCH')<button class="todo-action complete" type="submit" aria-label="Mark task completed" title="{{ $mandatoryPending > 0 ? 'Complete mandatory assignments first' : 'Mark Completed' }}" @disabled($mandatoryPending > 0)>✓</button></form>
                            @endif
                        @endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="9"><div class="todo-empty"><div class="todo-empty-icon">☑</div><h4>No tasks found</h4><p>Create a new To-Do or adjust the filters to find your work.</p><a class="btn primary" href="{{ route('todos.create') }}">+ New To-Do</a></div></td></tr>
            @endforelse
            </tbody>
        </table></div>
        @if($todos->hasPages())<div class="todo-pagination">{{ $todos->links() }}</div>@endif
        <div class="todo-table-footer">Showing {{ $todos->firstItem() ?? 0 }} to {{ $todos->lastItem() ?? 0 }} of {{ $todos->total() }} tasks</div>
    </div>
</section>
@include('todos._style')
@endsection
