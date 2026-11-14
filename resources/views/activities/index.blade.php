@extends('layouts.app')
@section('title', 'Activities')
@push('styles')
@include('activities._style')
@endpush
@section('content')
<div class="page-head"><div><h1>Activities</h1><p>Manage customer interactions, follow-ups, meetings, and other CRM activities.</p></div><div class="actions"><a class="btn primary" href="{{ route('activities.create') }}">+ New Activity</a></div></div>
<div class="card"><div class="card-head"><div><h3>Activity List</h3><p>{{ $activities->total() }} activities found</p></div></div><div class="card-body">
<form method="GET" action="{{ route('activities.index') }}" class="activity-filter">
<div class="field search"><label for="act-search">Search</label><input id="act-search" name="search" value="{{ request('search') }}" placeholder="Search subject or description..."></div>
<div class="field"><label for="act-type">Type</label><select id="act-type" name="type"><option value="">All Types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>@endforeach</select></div>
<div class="field"><label for="act-from">From</label><input type="date" id="act-from" name="from" value="{{ request('from') }}"></div>
<div class="field"><label for="act-to">To</label><input type="date" id="act-to" name="to" value="{{ request('to') }}"></div>
<button class="btn primary" type="submit">Filter</button><a class="btn" href="{{ route('activities.index') }}">Reset</a>
</form></div><div class="activity-table-wrap"><table class="activity-table"><thead><tr><th>Activity</th><th>Type</th><th>Date</th><th>Customer / Lead / Opportunity</th><th>Owner</th><th>Actions</th></tr></thead><tbody>
@forelse($activities as $activity)<tr><td><a class="subject" href="{{ route('activities.show', $activity) }}">{{ $activity->subject }}</a><div class="muted">{{ \Illuminate\Support\Str::limit($activity->description, 75) }}</div></td><td><span class="activity-type">{{ $activity->activity_type }}</span></td><td>{{ $activity->activity_date?->format('d M Y, H:i') }}</td><td>{{ $activity->customer?->customer_name ?? $activity->lead?->name ?? $activity->opportunity?->name ?? '—' }}</td><td>{{ $activity->user?->name ?? '—' }}</td><td><div class="activity-actions"><a class="btn" href="{{ route('activities.show', $activity) }}">View</a><a class="btn" href="{{ route('activities.edit', $activity) }}">Edit</a><button type="button" class="btn" data-activity-delete="{{ route('activities.destroy', $activity) }}">Delete</button></div></td></tr>
@empty<tr><td colspan="6" class="activity-empty">No activities found.</td></tr>@endforelse
</tbody></table></div><div class="card-body">{{ $activities->links() }}</div></div>
<dialog id="activityDeleteConfirm" class="activity-confirm" aria-labelledby="activityDeleteHeading"><h3 id="activityDeleteHeading">Delete Activity?</h3><p>This action will permanently delete the selected activity.</p><form id="activityDeleteForm" method="POST">@csrf @method('DELETE')<div class="actions"><button type="button" class="btn" id="cancelActivityDelete">Cancel</button><button class="btn primary" type="submit">Confirm Delete</button></div></form></dialog>
<script>document.addEventListener('DOMContentLoaded',()=>{const dialog=document.getElementById('activityDeleteConfirm');const form=document.getElementById('activityDeleteForm');document.querySelectorAll('[data-activity-delete]').forEach(button=>button.addEventListener('click',()=>{form.action=button.dataset.activityDelete;dialog.showModal();}));document.getElementById('cancelActivityDelete').addEventListener('click',()=>dialog.close());});</script>
@endsection
