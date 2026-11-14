@extends('layouts.app')
@section('title','Activity Details')
@push('styles')@include('activities._style')@endpush
@section('content')
<div class="page-head"><div><h1>Activity Details</h1><p>View related CRM information and activity schedule.</p></div><div class="actions"><a class="btn" href="{{ route('activities.index') }}">&larr; Back to Activities</a><a class="btn primary" href="{{ route('activities.edit', $activity) }}">Edit Activity</a></div></div>
<div class="card"><div class="card-head"><div><h3>{{ $activity->subject }}</h3><p>Activity Information</p></div><span class="activity-type">{{ $activity->activity_type }}</span></div><div class="card-body"><div class="activity-details">
<div class="detail"><small>Scheduled Date &amp; Time</small><strong>{{ $activity->activity_date?->format('d M Y, H:i') }}</strong></div>
<div class="detail"><small>Owner</small><strong>{{ $activity->user?->name ?? '—' }}</strong></div>
<div class="detail"><small>Customer</small><strong>{{ $activity->customer?->customer_name ?? '—' }}</strong></div>
<div class="detail"><small>Lead</small><strong>{{ $activity->lead?->name ?? $activity->lead?->lead_name ?? '—' }}</strong></div>
<div class="detail"><small>Opportunity</small><strong>{{ $activity->opportunity?->name ?? $activity->opportunity?->opportunity_name ?? '—' }}</strong></div>
<div class="detail"><small>Created</small><strong>{{ $activity->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
<div class="detail" style="grid-column:1/-1"><small>Description</small><div style="white-space:pre-wrap;color:#4b5972">{{ $activity->description ?: 'No description provided.' }}</div></div>
</div></div></div>
@endsection
