@extends('layouts.app')
@section('title','Edit Activity')
@push('styles')@include('activities._style')@endpush
@section('content')
<div class="page-head"><div><h1>Edit Activity</h1><p>Update your CRM activity information.</p></div><div class="actions"><a class="btn" href="{{ route('activities.show', $activity) }}">&larr; Back to Details</a></div></div>
<div class="card"><div class="card-head"><div><h3>Activity Information</h3><p>Update fields below.</p></div></div><div class="card-body"><form action="{{ route('activities.update', $activity) }}" method="POST">@csrf @method('PUT') @include('activities._form')<div class="actions" style="justify-content:flex-end;margin-top:22px"><a class="btn" href="{{ route('activities.show', $activity) }}">Cancel</a><button class="btn primary" type="submit">Save Changes</button></div></form></div></div>
@endsection
