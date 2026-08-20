@extends('layouts.auth')
@section('title', 'Setup — '.($meta['title'] ?? 'Opening hours'))
@section('auth_container_class', 'auth-container--wide')
@section('content')
@include('onboarding.steps._step', ['stepKey' => 'opening-hours'])
@endsection
