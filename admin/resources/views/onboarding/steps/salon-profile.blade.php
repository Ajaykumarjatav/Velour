@extends('layouts.auth')
@section('title', 'Setup — '.($meta['title'] ?? 'Business profile'))
@section('auth_container_class', 'auth-container--wide')
@section('content')
@include('onboarding.steps._step', ['stepKey' => 'salon-profile'])
@endsection
