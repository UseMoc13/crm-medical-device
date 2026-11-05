@extends('layouts.app')
@section('title', 'Create Warranty')
@section('content')
@php($editing = false)
<div class="page-head"><div><h1>Create Warranty</h1><p>Register a new medical device warranty and its coverage period.</p></div><div class="actions"><a href="{{ route('warranties.index') }}" class="btn">← Back</a></div></div>
@include('warranties._toast')
<div class="warranty-editor-layout"><div class="card"><div class="card-head"><div><h3>Warranty Information</h3><p>Enter the device, customer and coverage details below.</p></div><span class="warranty-required-note"><span class="required-mark">*</span> Required fields</span></div><div class="card-body"><form method="POST" action="{{ route('warranties.store') }}" class="warranty-form">@csrf 
<div class="warranty-section-heading"><h4>General Information</h4><p>Warranty identity, device and coverage details.</p></div>
@include('warranties._form')
<div class="warranty-form-actions"><a class="btn" href="{{ route('warranties.index') }}">Cancel</a><button type="submit" class="btn primary">Save Warranty</button></div></form></div></div>
@include('warranties._preview')
</div>
@include('warranties._interaction')
@include('warranties._style')
@endsection
