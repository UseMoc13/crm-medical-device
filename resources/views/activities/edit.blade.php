@extends('layouts.app')
@section('title', 'Edit Activity')
@push('styles')
    @include('activities._style')
@endpush
@section('content')
<div class="page-head activity-page-head">
    <div><h1>Edit Activity</h1><p>Update the activity details and review changes before saving.</p></div>
    <div class="actions"><a class="btn" href="{{ route('activities.show', $activity) }}">&larr; Back</a></div>
</div>
<div class="activity-editor-layout">
    <section class="card activity-editor-card">
        <div class="card-head"><div><h3>Activity Information</h3><p>Fill in the details below. Fields marked * are required.</p></div></div>
        <form method="POST" action="{{ route('activities.update', $activity) }}" id="activityEditorForm">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('activities._form')
            </div>
            <div class="activity-editor-actions"><a class="btn" href="{{ route('activities.show', $activity) }}">Cancel</a><button class="btn primary" type="submit">Save Changes</button></div>
        </form>
    </section>
    @include('activities._preview')
</div>
@include('activities._preview_script')
@endsection
