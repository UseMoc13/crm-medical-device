@extends('layouts.app')
@section('title', 'New Activity')
@push('styles')
    @include('activities._style')
@endpush
@section('content')
<div class="page-head activity-page-head">
    <div><h1>New Activity</h1><p>Schedule an interaction, visit, or follow-up with a live summary.</p></div>
    <div class="actions"><a class="btn" href="{{ route('activities.index') }}">&larr; Back</a></div>
</div>
<div class="activity-editor-layout">
    <section class="card activity-editor-card">
        <div class="card-head"><div><h3>Activity Information</h3><p>Fill in the details below. Fields marked * are required.</p></div></div>
        <form method="POST" action="{{ route('activities.store') }}" id="activityEditorForm">
            @csrf
            
            <div class="card-body">
                @include('activities._form')
            </div>
            <div class="activity-editor-actions"><a class="btn" href="{{ route('activities.index') }}">Cancel</a><button class="btn primary" type="submit">Create Activity</button></div>
        </form>
    </section>
    @include('activities._preview')
</div>
@include('activities._preview_script')
@endsection
