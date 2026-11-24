@extends('layouts.app')
@section('title', 'Create Branch')
@section('content')
<div class="page-head">
    <div><h1>Create Branch</h1><p>Register a new office or operational branch in the CRM.</p></div>
    <div class="actions"><a href="{{ route('branches.index') }}" class="btn">← Back to Branches</a></div>
</div>
@include('branches._form')
@include('branches._style')
@endsection
