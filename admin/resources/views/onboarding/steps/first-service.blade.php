@extends('layouts.auth')
@section('title', 'Setup — '.($meta['title'] ?? 'Service selection'))
@section('auth_container_class', 'auth-container--wide')
@section('content')
@include('onboarding.steps._step', ['stepKey' => 'first-service'])
@endsection
