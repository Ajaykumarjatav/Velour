@extends('layouts.auth')
@section('title', 'Setup — '.($meta['title'] ?? 'Team setup'))
@section('auth_container_class', 'auth-container--wide')
@section('content')
@include('onboarding.steps._step', ['stepKey' => 'invite-staff'])
@endsection
