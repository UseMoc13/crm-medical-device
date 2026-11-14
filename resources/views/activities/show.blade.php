@extends('layouts.app')
@section('title', 'Activity Details')
@push('styles')
    @include('activities._style')
@endpush
@section('content')
@php
    $activityType = trim((string) ($activity->activity_type ?: 'Other'));
    $typeSlug = \Illuminate\Support\Str::slug($activityType);
    $ownerName = $activity->user?->name ?? 'Unassigned';
    $customerName = $activity->customer?->customer_name;
    $leadName = $activity->lead?->lead_name ?? $activity->lead?->name;
    $opportunityName = $activity->opportunity?->opportunity_name ?? $activity->opportunity?->name;
    $schedule = $activity->activity_date;
    $isOwner = (string) $activity->user_id === (string) auth()->id();
@endphp
<div class="page-head">
    <div>
        <h1>Activity Details</h1>
        <p>Review scheduled activity details, ownership, and related CRM records.</p>
    </div>
    <div class="actions">
        <a class="btn" href="{{ route('activities.index') }}">&larr; Back to Activities</a>
        @if($isOwner)
            <a class="btn primary" href="{{ route('activities.edit', ['activity' => $activity->getKey()]) }}">Edit Activity</a>
        @endif
    </div>
</div>

<div class="activity-show-overview">
    <section class="card activity-show-information-card">
        <div class="card-head"><div><h3>Activity Information</h3><p>Identification and scheduling information.</p></div></div>
        <div class="card-body">
            <div class="activity-show-identity">
                <div class="activity-show-identity-icon" aria-hidden="true">A</div>
                <div class="activity-show-identity-content">
                    <h2>{{ $activity->subject }}</h2>
                    <span>Activity record</span>
                </div>
                <span class="activity-list-type activity-list-type-{{ $typeSlug }}">{{ $activityType }}</span>
            </div>
            <div class="activity-show-info-grid">
                <div class="activity-show-info"><span>Subject</span><strong>{{ $activity->subject }}</strong></div>
                <div class="activity-show-info"><span>Activity Type</span><strong>{{ $activityType }}</strong></div>
                <div class="activity-show-info"><span>Scheduled Date</span><strong>{{ $schedule?->format('d M Y') ?? 'Not scheduled' }}</strong></div>
                <div class="activity-show-info"><span>Scheduled Time</span><strong>{{ $schedule?->format('H:i') ?? '—' }}</strong></div>
                <div class="activity-show-info"><span>Activity Owner</span><strong>{{ $ownerName }}</strong></div>
                <div class="activity-show-info"><span>Created At</span><strong>{{ $activity->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
            </div>
            <div class="activity-show-description">
                <span>Description</span>
                <p>{{ $activity->description ?: 'No description has been recorded for this activity.' }}</p>
            </div>
        </div>
    </section>

    <section class="card activity-show-summary-card">
        <div class="card-head"><div><h3>Activity Summary</h3><p>Quick overview of this CRM activity.</p></div></div>
        <div class="card-body">
            <div class="activity-show-summary-list">
                <div class="activity-show-summary-item"><div class="activity-summary-icon type">A</div><div><span>Activity Type</span><strong>{{ $activityType }}</strong><small>Recorded interaction category</small></div></div>
                <div class="activity-show-summary-item"><div class="activity-summary-icon date">◷</div><div><span>Scheduled For</span><strong>{{ $schedule?->format('d M Y, H:i') ?? 'Not scheduled' }}</strong><small>Date and time of this activity</small></div></div>
                <div class="activity-show-summary-item"><div class="activity-summary-icon owner">U</div><div><span>Activity Owner</span><strong>{{ $ownerName }}</strong><small>Responsible CRM user</small></div></div>
                <div class="activity-show-summary-item"><div class="activity-summary-icon related">↗</div><div><span>Related CRM Records</span><strong>{{ (int) filled($customerName) + (int) filled($leadName) + (int) filled($opportunityName) }} linked</strong><small>Customer, Lead, and Opportunity references</small></div></div>
            </div>
        </div>
    </section>
</div>

<div class="activity-show-relations">
    <section class="card activity-show-related-card">
        <div class="card-head"><div><h3>Customer Information</h3><p>Customer associated with this activity.</p></div></div>
        <div class="card-body">
            @if($customerName)
                <div class="activity-show-related-top"><div class="activity-related-icon customer">C</div><div><span>Linked Customer</span><h4>{{ $customerName }}</h4></div></div>
                <div class="activity-show-related-meta"><span>Customer Record ID</span><strong>{{ $activity->customer_id }}</strong></div>
            @else
                <div class="activity-show-empty"><div class="activity-empty-icon">C</div><h4>No Customer Linked</h4><p>This activity is not associated with a customer.</p></div>
            @endif
        </div>
    </section>
    <section class="card activity-show-related-card">
        <div class="card-head"><div><h3>Lead Information</h3><p>Lead associated with this activity.</p></div></div>
        <div class="card-body">
            @if($leadName)
                <div class="activity-show-related-top"><div class="activity-related-icon lead">L</div><div><span>Linked Lead</span><h4>{{ $leadName }}</h4></div></div>
                <div class="activity-show-related-meta"><span>Lead Record ID</span><strong>{{ $activity->lead_id }}</strong></div>
            @else
                <div class="activity-show-empty"><div class="activity-empty-icon">L</div><h4>No Lead Linked</h4><p>This activity is not associated with a lead.</p></div>
            @endif
        </div>
    </section>
    <section class="card activity-show-related-card">
        <div class="card-head"><div><h3>Opportunity Information</h3><p>Opportunity associated with this activity.</p></div></div>
        <div class="card-body">
            @if($opportunityName)
                <div class="activity-show-related-top"><div class="activity-related-icon opportunity">O</div><div><span>Linked Opportunity</span><h4>{{ $opportunityName }}</h4></div></div>
                <div class="activity-show-related-meta"><span>Opportunity Record ID</span><strong>{{ $activity->opportunity_id }}</strong></div>
            @else
                <div class="activity-show-empty"><div class="activity-empty-icon">O</div><h4>No Opportunity Linked</h4><p>This activity is not associated with an opportunity.</p></div>
            @endif
        </div>
    </section>
</div>

<section class="card activity-show-registration-card">
    <div class="card-head"><div><h3>Registration Information</h3><p>System identifiers and activity record reference.</p></div></div>
    <div class="card-body"><div class="activity-show-info-grid">
        <div class="activity-show-info"><span>Activity Record ID</span><strong class="activity-show-uuid">{{ $activity->getKey() }}</strong></div>
        <div class="activity-show-info"><span>Owner User ID</span><strong class="activity-show-uuid">{{ $activity->user_id ?: '—' }}</strong></div>
        <div class="activity-show-info"><span>Scheduled At</span><strong>{{ $schedule?->format('d M Y, H:i') ?? '—' }}</strong></div>
        <div class="activity-show-info"><span>Created At</span><strong>{{ $activity->created_at?->format('d M Y, H:i') ?? '—' }}</strong></div>
    </div></div>
</section>
@endsection
