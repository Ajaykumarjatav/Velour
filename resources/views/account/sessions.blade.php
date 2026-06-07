@extends('layouts.app')
@section('title', 'Active Sessions & Security')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

  <div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900" style="font-family:'Playfair Display',serif">
      Sessions & Security
    </h1>
    <p class="text-gray-500 text-sm mt-1">Manage your active sessions and API tokens</p>
  </div>

  @if(session('success'))
  <div class="bg-green-50 text-green-700 border border-green-200 rounded-xl px-4 py-3 text-sm mb-6">
    ✅ {{ session('success') }}
  </div>
  @endif

  {{-- Active Sessions --}}
  <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <h2 class="font-semibold text-gray-800">Active Sessions ({{ $sessions->count() }})</h2>
      <form method="POST" action="{{ route('account.sessions.revoke-all') }}">
        @csrf
        @method('DELETE')
        <input type="hidden" name="password" id="revoke-all-password">
        <button type="button" onclick="confirmRevokeAll()"
          class="text-xs text-red-600 hover:underline font-medium">
          Revoke all other sessions
        </button>
      </form>
    </div>
    <div class="divide-y divide-gray-50">
      @foreach($sessions as $session)
      <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-gray-100 rounded-xl flex items-center justify-center text-base">
            {{ str_contains($session['user_agent'], 'iOS') || str_contains($session['user_agent'], 'Android') ? '📱' : '💻' }}
          </div>
          <div>
            <p class="text-sm font-medium text-gray-800">
              {{ $session['user_agent'] }}
              @if($session['is_current'])
              <span class="ml-2 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Current</span>
              @endif
            </p>
            <p class="text-xs text-gray-400 mt-0.5">
              {{ $session['ip_address'] }} · {{ $session['last_activity'] }}
            </p>
          </div>
        </div>
        @unless($session['is_current'])
        <form method="POST" action="{{ route('account.sessions.revoke', $session['id']) }}">
          @csrf @method('DELETE')
          <button class="text-xs text-red-500 hover:underline">Revoke</button>
        </form>
        @endunless
      </div>
      @endforeach
    </div>
  </div>

  {{-- API Tokens --}}
  @if($apiTokens->isNotEmpty())
  <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="font-semibold text-gray-800">API Tokens</h2>
    </div>
    <div class="divide-y divide-gray-50">
      @foreach($apiTokens as $token)
      <div class="flex items-center justify-between px-6 py-4">
        <div>
          <p class="text-sm font-medium text-gray-800">{{ $token->name }}</p>
          <p class="text-xs text-gray-400 mt-0.5">
            Created {{ $token->created_at->diffForHumans() }}
            @if($token->last_used_at)
            · Last used {{ $token->last_used_at->diffForHumans() }}
            @else
            · Never used
            @endif
          </p>
        </div>
        <form method="POST" action="{{ route('account.tokens.revoke', $token->id) }}">
          @csrf @method('DELETE')
          <button class="text-xs text-red-500 hover:underline">Revoke</button>
        </form>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Recent login history --}}
  <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="font-semibold text-gray-800">Recent Login History</h2>
    </div>
    <div class="divide-y divide-gray-50">
      @forelse($loginHistory as $attempt)
      <div class="flex items-center gap-4 px-6 py-3">
        <span class="text-base">{{ $attempt->succeeded ? '✅' : '❌' }}</span>
        <div class="flex-1">
          <p class="text-sm text-gray-700">
            {{ $attempt->succeeded ? 'Successful login' : 'Failed attempt: ' . ($attempt->failure_reason ?? 'unknown') }}
          </p>
          <p class="text-xs text-gray-400 mt-0.5">
            {{ $attempt->ip_address }} · {{ \Carbon\Carbon::parse($attempt->attempted_at)->diffForHumans() }}
          </p>
        </div>
      </div>
      @empty
      <p class="px-6 py-4 text-sm text-gray-400">No login history yet.</p>
      @endforelse
    </div>
  </div>

  {{-- Danger zone --}}
  <div class="mt-8 border border-red-100 rounded-2xl overflow-hidden">
    <div class="bg-red-50 px-6 py-3 border-b border-red-100">
      <h2 class="font-semibold text-red-700 text-sm">Danger Zone</h2>
    </div>
    <div class="px-6 py-5 flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-800">Delete Account</p>
        <p class="text-xs text-gray-500 mt-0.5">Permanently delete your account and all associated data.</p>
      </div>
      <a href="{{ route('account.delete') }}"
        class="text-sm text-red-600 border border-red-200 px-4 py-2 rounded-xl hover:bg-red-50 transition font-medium">
        Delete Account
      </a>
    </div>
  </div>

</div>
@endsection
