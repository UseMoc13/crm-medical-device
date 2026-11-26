@extends('layouts.app')
@section('title', 'New Activity')
@push('styles')
    @include('activities._style')
@endpush
@section('content')
<div class="page-head activity-page-head">
    <div><h1>New Activity</h1><p>Schedule an interaction, visit, or follow-up with a live summary.</p></div>
    <div class="actions">{{-- A7103:activities.index --}}@if(\App\Support\CrmViewAction::allows('activities.index'))
<a class="btn" href="{{ route('activities.index') }}">&larr; Back</a>
@endif{{-- /A7103 --}}</div>
</div>
<div class="activity-editor-layout">
    <section class="card activity-editor-card">
        <div class="card-head"><div><h3>Activity Information</h3><p>Fill in the details below. Fields marked * are required.</p></div></div>
        {{-- A7103:activities.store --}}@if(\App\Support\CrmViewAction::allows('activities.store'))
<form method="POST" action="{{ route('activities.store') }}" id="activityEditorForm">
            @csrf
            
            <div class="card-body">
                @include('activities._form')
            </div>
            <div class="activity-editor-actions">{{-- A7103:activities.index --}}@if(\App\Support\CrmViewAction::allows('activities.index'))
<a class="btn" href="{{ route('activities.index') }}">Cancel</a>
@endif{{-- /A7103 --}}<button class="btn primary" type="submit">Create Activity</button></div>
        </form>
@endif{{-- /A7103 --}}
    </section>
    @include('activities._preview')
</div>
@include('activities._preview_script')
@endsection
