@extends('layouts.app')
@section('title', 'Edit Personal Event')
@section('content')
<div class="page-head"><div><h1>Edit Personal Event</h1><p>Update your personal appointment.</p></div><div class="actions"><a class="btn" href="{{ route('calendar.show', $event->getKey()) }}">← Back to Details</a></div></div>
@include('calendar._form')
@include('calendar._style')
@endsection
