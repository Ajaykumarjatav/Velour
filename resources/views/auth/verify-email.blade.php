@extends('layouts.auth')
@section('title', 'Verify Your Email')
@section('content')

<div class="max-w-md w-full mx-auto">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-velour-100 mb-4">
      <svg class="w-8 h-8 text-velour-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Check your inbox</h1>
    <p class="text-gray-500 mt-2">We sent a verification link to<br>
      <strong class="text-gray-800">{{ Auth::user()->email }}</strong></p>
  </div>

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm">
    {{ session('success') }}
  </div>
  @endif

  @if(session('email_error'))
  <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-xl mb-4 text-sm" role="alert">
    {{ session('email_error') }}
  </div>
  @endif

  <div class="bg-white rounded-2xl border border-gray-200 p-6 text-center space-y-4">
    <p class="text-sm text-gray-500">Didn't receive the email? Check your spam folder, or request a new link.</p>

    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit"
              class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Resend verification email
      </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="text-sm text-gray-400 hover:text-gray-600">
        Sign out
      </button>
    </form>
  </div>
</div>

@endsection
