@extends('layouts.app')
@section('title', 'Edit Warranty')
@section('content')
@php($editing = true)
<div class="page-head"><div><h1>Edit Warranty</h1><p>Update warranty information and coverage details.</p></div><div class="actions"><a href="{{ route('warranties.show', $warranty->warranty_id) }}" class="btn">← Back</a></div></div>
@include('warranties._toast')
<div class="warranty-editor-layout"><div class="card"><div class="card-head"><div><h3>Warranty Information</h3><p>Enter the device, customer and coverage details below.</p></div><span class="warranty-required-note"><span class="required-mark">*</span> Required fields</span></div><div class="card-body"><form method="POST" action="{{ route('warranties.update', $warranty->warranty_id) }}" class="warranty-form">@csrf @method('PUT')
<div class="warranty-section-heading"><h4>General Information</h4><p>Warranty identity, device and coverage details.</p></div>
@include('warranties._form')
<div class="warranty-form-actions"><a class="btn" href="{{ route('warranties.show', $warranty->warranty_id) }}">Cancel</a><button type="submit" class="btn primary">Save Changes</button></div></form></div></div>
@include('warranties._preview')
</div>
@include('warranties._interaction')
@include('warranties._style')
@endsection
