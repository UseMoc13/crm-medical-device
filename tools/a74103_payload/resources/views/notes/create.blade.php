@extends('layouts.app')
@section('title', 'New Personal Note')
@section('content')
<div class="page-head"><div><h1>New Personal Note</h1><p>Create a private note and link related work directly in the text.</p></div><div class="actions"><a class="btn" href="{{ route('notes.index') }}">&larr; Back to Notes</a></div></div>
@include('notes._form')
@endsection
