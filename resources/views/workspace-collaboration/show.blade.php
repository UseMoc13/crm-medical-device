@extends('layouts.app')
@section('title', 'Shared '.($entityType === 'activity' ? 'Activity' : 'To-Do'))
@section('content')
@php
    $isActivity = $entityType === 'activity';
    $backName = $isActivity ? 'activities.shared' : 'todos.shared';
    $title = $isActivity ? $entity->subject : $entity->title;
    $schedule = $isActivity ? $entity->activity_date : $entity->due_at;
@endphp
<div class="page-head"><div><h1>{{ $isActivity ? 'Shared Activity' : 'Shared To-Do' }}</h1><p>Collaboration view with restricted CRM references and private files.</p></div><div class="actions"><a class="btn" href="{{ route($backName) }}">&larr; Shared with Me</a></div></div>
<div class="a748-shell">
    <section class="card"><div class="card-head"><div><h3>{{ $title }}</h3><p>{{ $isActivity ? 'Activity information' : 'Work task information' }}</p></div><span class="a748-badge">{{ ucfirst($role) }}</span></div>
        <div class="a748-body"><div class="a748-grid-info">
            <div><span>Category</span><strong>{{ $isActivity ? $entity->activity_type : 'Work task' }}</strong></div>
            <div><span>{{ $isActivity ? 'Scheduled' : 'Deadline' }}</span><strong>{{ $schedule?->format('d M Y, H:i') ?? 'Not scheduled' }}</strong></div>
            @unless($isActivity)<div><span>Status</span><strong>{{ ucwords(str_replace('_',' ',$entity->status)) }}</strong></div><div><span>Priority</span><strong>{{ ucfirst($entity->priority) }}</strong></div>@endunless
        </div><div style="margin-top:17px"><strong class="a748-strong">Description</strong><p class="a748-description">{{ $entity->description ?: 'No description provided.' }}</p></div>
        <p class="a748-caption">Customer, Lead, Opportunity, assignment recipients and submission files are deliberately omitted from this view.</p></div>
    </section>
</div>
@include('workspace-collaboration._panel')
@endsection
