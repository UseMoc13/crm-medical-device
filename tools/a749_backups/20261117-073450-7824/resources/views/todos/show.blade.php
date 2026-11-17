@extends('layouts.app')
@section('title', 'To-Do Details')
@section('content')
<div class="page-head"><div><h1>To-Do Details</h1><p>Review assignments, submitted files, deadlines, and task completion.</p></div><div class="actions"><a class="btn" href="{{ route('todos.index') }}">&larr; Back to To-Do</a>@if($isOwner)<a class="btn primary" href="{{ route('todos.edit',['todo'=>$todo->getKey()]) }}">Edit Task</a>@endif</div></div>
@php $totalSubmissionFiles = $assignments->sum(fn ($assignment) => $assignment->files->count()); @endphp
@if($errors->any())<div class="alert error" role="alert"><strong>Unable to complete the requested action.</strong><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
<div class="todo-details-grid">
    <section class="card todo-info-card"><div class="card-head"><div><h3>Task Information</h3><p>Identification, schedule, and required deliverables.</p></div></div><div class="card-body">
        <div class="todo-identity"><div class="todo-identity-icon">☑</div><div><h2>{{ $todo->title }}</h2><span>{{ $isOwner ? 'Created by you' : 'Assigned by '.$todo->owner?->name }}</span></div><span class="todo-tag todo-status-{{ $todo->status }}">{{ ucwords(str_replace('_',' ',$todo->status)) }}</span></div>
        <div class="todo-info-grid"><div><span>Category</span><strong>{{ ucfirst($todo->visibility) }}</strong></div><div><span>Priority</span><strong>{{ ucfirst($todo->priority) }}</strong></div><div><span>Start Date & Time</span><strong>{{ $todo->starts_at?->format('d M Y, H:i') ?? 'Not specified' }}</strong></div><div><span>End Date & Time</span><strong>{{ $todo->due_at?->format('d M Y, H:i') ?? 'No deadline' }}</strong></div><div><span>Created by</span><strong>{{ $todo->owner?->name ?? '-' }}</strong></div><div><span>Completed</span><strong>{{ $todo->completed_at?->format('d M Y, H:i') ?? 'Not yet' }}</strong></div></div>
        <div class="todo-description"><span>Instructions / Description</span><p>{{ $todo->description ?: 'No additional instructions provided.' }}</p></div>
    </div></section>
    <section class="card todo-summary-card"><div class="card-head"><div><h3>Assignment Summary</h3><p>Work progress and next steps.</p></div></div><div class="card-body">
        <div class="todo-summary-item"><div class="todo-summary-icon">✓</div><div><span>Completed assignments</span><strong>{{ $completedCount }} / {{ $assignmentCount }}</strong><small>{{ $mandatoryCount }} mandatory assignee(s); optional assignees do not block closure</small></div></div>
        <div class="todo-summary-item"><div class="todo-summary-icon">◷</div><div><span>Deadline</span><strong>{{ $todo->due_at?->format('d M Y, H:i') ?? 'Not scheduled' }}</strong><small>Visible in Calendar across the scheduled range</small></div></div>
        <div class="todo-summary-item"><div class="todo-summary-icon">A</div><div><span>Related CRM Activity</span><strong>{{ $activity?->subject ?? ($todo->activity_id ? 'Linked (restricted)' : 'Not linked') }}</strong><small>Activity details follow their own access rules</small></div></div>
        @if($isOwner && !in_array($todo->status,['completed','cancelled'],true))
            <form method="POST" action="{{ route('todos.complete',['todo'=>$todo->getKey()]) }}">@csrf @method('PATCH')<button type="submit" class="btn primary" @disabled($mandatoryPendingCount > 0)>✓ Mark Entire Task Completed</button></form>
            @if($mandatoryPendingCount > 0)<p class="todo-form-footnote">{{ $mandatoryPendingCount }} mandatory assignment(s) must still be completed.</p>@endif
        @endif
    </div></section>
</div>
@if($todo->visibility === 'work' && ($assignmentCount > 0))
<section class="card todo-related-card"><div class="card-head"><div><h3>{{ $isOwner ? 'Assignees & Submission Files' : 'My Assignment & Submissions' }}</h3><p>Mandatory assignees must finish before closing the task; file submission is an independent requirement.</p></div>
    @if($isOwner && $totalSubmissionFiles > 0)<a class="btn primary" href="{{ route('todos.submissions.download-all', ['todo'=>$todo->getKey()]) }}">Download All · ZIP</a>@endif</div>
    <div class="card-body">
        <div class="todo-assignment-list">
            @foreach($assignments as $assignment)
                <div class="todo-assignment-card">
                    <div class="todo-assignment-head">
                        <div class="todo-assignment-person"><div class="todo-person-icon">{{ mb_strtoupper(mb_substr((string)($assignment->assignee?->name ?? 'U'),0,1)) }}</div><div><strong>{{ $assignment->assignee?->name ?? 'User' }}</strong><small>{{ $assignment->submission_required ? 'At least one file required to submit' : 'Files are optional for this person' }}</small></div></div>
                        <div class="todo-assignment-badges"><span class="todo-tag {{ $assignment->completion_required ? 'todo-priority-high':'todo-tag-work' }}">{{ $assignment->completion_required ? 'Mandatory' : 'Optional' }}</span>@if($assignment->submission_required)<span class="todo-tag todo-priority-medium">File required</span>@endif<span class="todo-tag {{ $assignment->status === 'completed' ? 'todo-status-completed':'todo-status-todo' }}">{{ $assignment->status === 'completed' ? 'Completed' : 'Assigned' }}</span></div>
                    </div>
                    <div class="todo-assignment-file-list">
                        @forelse($assignment->files as $file)
                            <div class="todo-assignment-file"><div class="todo-file-mark">▤</div><div class="todo-file-label"><strong>{{ $file->original_name }}</strong><small>{{ number_format($file->file_size / 1024, 1) }} KB · {{ $file->created_at?->format('d M Y, H:i') }}</small></div>
                                <a class="btn" href="{{ route('todos.submission.files.download', ['todo'=>$todo->getKey(),'file'=>$file->getKey()]) }}" title="Download with username prefixed">Download</a>
                                @if((string)$assignment->assigned_user_id === (string)auth()->id() && $assignment->status !== 'completed' && !in_array($todo->status,['completed','cancelled'],true))
                                    <form method="POST" action="{{ route('todos.submission.files.destroy',['todo'=>$todo->getKey(),'file'=>$file->getKey()]) }}" data-delete-name="{{ $file->original_name }}">@csrf @method('DELETE')<button class="btn" type="submit">Remove</button></form>
                                @endif
                            </div>
                        @empty
                            <p class="todo-assignment-no-files">No files uploaded yet.</p>
                        @endforelse
                    </div>
                    @if($isOwner && $assignment->files->count() > 0)
                        <a class="btn todo-download-per-person" href="{{ route('todos.assignments.download',['todo'=>$todo->getKey(),'assignment'=>$assignment->getKey()]) }}">{{ $assignment->files->count() > 1 ? 'Download '.$assignment->files->count().' Files · ZIP' : 'Download Submission' }}</a>
                    @endif
                    @if((string)$assignment->assigned_user_id === (string)auth()->id() && !in_array($todo->status,['completed','cancelled'],true))
                        @if($assignment->status !== 'completed')
                            <div class="todo-submission-controls">
                                <form method="POST" action="{{ route('todos.submission.upload',['todo'=>$todo->getKey()]) }}" enctype="multipart/form-data" class="todo-upload-form">@csrf
                                    <label for="todo-upload-{{ $assignment->getKey() }}">Upload Assignment Files</label><input id="todo-upload-{{ $assignment->getKey() }}" type="file" name="files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt" required>
                                    <small>Up to 5 files per upload, 10 MB per file, 30 files total. Files remain private.</small>
                                    <button type="submit" class="btn">Upload Files</button>
                                </form>
                                <form method="POST" action="{{ route('todos.submission.complete',['todo'=>$todo->getKey()]) }}">@csrf @method('PATCH')<button type="submit" class="btn primary">✓ Complete My Assignment</button></form>
                            </div>
                        @else<p class="todo-submission-locked">Assignment submitted on {{ $assignment->completed_at?->format('d M Y, H:i') ?? 'recorded date' }}.</p>@endif
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
<section class="card todo-related-card"><div class="card-head"><div><h3>Related CRM Activity</h3><p>Task context is linked only when you have access to that Activity.</p></div></div><div class="card-body">@if($activity)<div class="todo-related"><div class="todo-identity-icon">A</div><div><span>Linked Activity</span><h4>{{ $activity->subject }}</h4><p>{{ $activity->activity_date?->format('d M Y, H:i') ?? 'No schedule' }}</p></div><a class="btn" href="{{ route('activities.show',['activity'=>$activity->getKey()]) }}">View Activity</a></div>@else<div class="todo-empty compact"><h4>{{ $todo->activity_id ? 'Linked Activity Restricted' : 'No Related Activity' }}</h4><p>{{ $todo->activity_id ? 'This Activity is not available to your account.' : 'This task is not linked to a CRM Activity.' }}</p></div>@endif</div></section>
<section class="card todo-related-card"><div class="card-head"><div><h3>Registration Information</h3><p>Task identifiers and audit timestamps.</p></div></div><div class="card-body"><div class="todo-info-grid"><div><span>Task UUID</span><strong class="todo-mono">{{ $todo->getKey() }}</strong></div><div><span>Owner</span><strong>{{ $todo->owner?->name ?? 'Unknown' }}</strong></div><div><span>Created At</span><strong>{{ $todo->created_at?->format('d M Y, H:i') ?? '-' }}</strong></div><div><span>Updated At</span><strong>{{ $todo->updated_at?->format('d M Y, H:i') ?? '-' }}</strong></div></div></div></section>
@if($todo->visibility === 'work')
@include('workspace-collaboration._panel', ['entityType'=>'todo', 'entity'=>$todo])
@endif
@include('todos._style')
@endsection
