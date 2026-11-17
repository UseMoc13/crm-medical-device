@extends('layouts.app')
@section('title', 'Edit Personal Note')
@section('content')
<div class="page-head"><div><h1>Edit Personal Note</h1><p>Edit the title and content. Mention links are clickable directly within your note.</p></div><div class="actions"><a class="btn" href="{{ route('notes.show', ['note'=>$note->getKey()]) }}">&larr; Back to Note</a></div></div>
@include('notes._form')
@endsection
