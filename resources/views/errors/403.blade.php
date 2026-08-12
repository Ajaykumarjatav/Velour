@extends('errors.layout')

@php
    $raw = trim((string) ($exception->getMessage() ?? ''));
    $isSignature = str_contains(strtolower($raw), 'signature') || str_contains(strtolower($raw), 'expired');
    $friendly = $raw !== '' && ! $isSignature
        ? $raw
        : ($isSignature
            ? 'This secure link is invalid or has expired. Please request a new link and try again.'
            : 'You don’t have permission to access this page. If you believe this is a mistake, contact your salon administrator.');
@endphp

@section('code', '403')
@section('title', $isSignature ? 'Link expired or invalid' : 'Access Forbidden')
@section('message', $friendly)

@section('actions')
    @if($isSignature)
        <a href="{{ route('login') }}"
           class="px-6 py-3 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
            Sign In
        </a>
        <a href="{{ route('password.request') }}"
           class="px-6 py-3 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors">
            Forgot password?
        </a>
    @else
        @if(auth()->check())
            <a href="{{ \App\Support\AuthPanel::homeUrl(auth()->user()) }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
                Back to Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="px-6 py-3 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
                Sign In
            </a>
        @endif
        <a href="javascript:history.back()"
           class="px-6 py-3 text-sm font-medium rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 transition-colors">
            Go Back
        </a>
    @endif
@endsection
