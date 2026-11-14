@extends('layouts.app')
@section('title','New Activity')
@push('styles')@include('activities._style')@endpush
@section('content')
<div class="page-head"><div><h1>New Activity</h1><p>Record a CRM interaction or schedule a follow-up.</p></div><div class="actions"><a class="btn" href="{{ route('activities.index') }}">&larr; Back to Activities</a></div></div>
<div class="card"><div class="card-head"><div><h3>Activity Information</h3><p>Fields marked * are required.</p></div></div><div class="card-body"><form action="{{ route('activities.store') }}" method="POST">@csrf @include('activities._form')<div class="actions" style="justify-content:flex-end;margin-top:22px"><a class="btn" href="{{ route('activities.index') }}">Cancel</a><button class="btn primary" type="submit">Save Activity</button></div></form></div></div>
@endsection
