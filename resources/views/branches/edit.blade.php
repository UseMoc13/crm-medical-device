@extends('layouts.app')
@section('title', 'Edit Branch')
@section('content')
<div class="page-head">
    <div><h1>Edit Branch</h1><p>Update branch information without changing user or team assignments.</p></div>
    <div class="actions"><a href="{{ route('branches.show', $branch) }}" class="btn">← Branch Details</a></div>
</div>
@include('branches._form')
@include('branches._style')
@endsection
