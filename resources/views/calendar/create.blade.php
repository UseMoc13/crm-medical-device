@extends('layouts.app')
@section('title', 'Create Personal Event')
@section('content')
<div class="page-head"><div><h1>Create Personal Event</h1><p>Plan a private appointment or add notes to your calendar.</p></div><div class="actions"><a class="btn" href="{{ route('calendar.index') }}">← Back to Calendar</a></div></div>
@include('calendar._form')
@include('calendar._style')
@endsection
