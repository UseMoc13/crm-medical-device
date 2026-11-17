@extends('layouts.app')
@section('title', 'Edit Personal Note')
@section('content')
<div class="page-head"><div><h1>Edit Personal Note</h1><p>Update your private note and linked schedule.</p></div><div class="actions"><a class="btn" href="{{ route('notes.show', ['note'=>$note->getKey()]) }}">&larr; Back to Note</a></div></div>
@include('notes._form')
@endsection
