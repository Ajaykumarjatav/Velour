@extends('layouts.admin')
@section('title', $user->name)
@section('page-title', $user->name)
@section('content')

<div class="space-y-5">

  {{-- Header --}}
  <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 flex flex-col sm:flex-row items-start gap-5">
    <div class="w-16 h-16 rounded-2xl bg-velour-800 flex items-center justify-center text-velour-200 text-2xl font-black flex-shrink-0">
      {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div class="flex-1">
      <div class="flex flex-wrap items-center gap-2 mb-1">
        <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
        @if($user->isSuperAdmin())
        <span class="px-2 py-0.5 text-xs bg-red-900/60 text-red-300 rounded-lg font-bold border border-red-800/50">SUPER ADMIN</span>
        @endif
        @foreach($roles as $role)
        <span class="px-2 py-0.5 text-xs bg-velour-900/60 text-velour-300 rounded-lg border border-velour-800/50">{{ $role }}</span>
        @endforeach
      </div>
      <p class="text-gray-400">{{ $user->email }}</p>
      <div class="flex flex-wrap gap-4 mt-2 text-xs text-gray-500">
        <span>Plan: <strong class="text-gray-300">{{ ucfirst($user->plan ?? '—') }}</strong></span>
        <span>2FA: <strong class="{{ $user->hasTwoFactorEnabled() ? 'text-green-400' : 'text-gray-500' }}">
          {{ $user->hasTwoFactorEnabled() ? strtoupper($user->two_factor_method) : 'Disabled' }}
        </strong></span>
        <span>Verified: <strong class="{{ $user->hasVerifiedEmail() ? 'text-green-400' : 'text-red-400' }}">
          {{ $user->hasVerifiedEmail() ? 'Yes' : 'No' }}
        </strong></span>
        <span>Last login: <strong class="text-gray-300">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</strong></span>
      </div>
    </div>
    <div class="flex flex-col gap-2 min-w-[140px]">
      {{-- Impersonate --}}
      @if(! $user->isSuperAdmin() && $user->id !== Auth::id())
      <form method="POST" action="{{ route('admin.users.impersonate', $user->id) }}">
        @csrf
        <button type="submit"
                class="w-full px-4 py-2 text-sm font-medium rounded-xl border border-velour-700 text-velour-400 hover:bg-velour-900/30 transition-colors">
          Impersonate
        </button>
      </form>
      @endif

      {{-- Toggle active --}}
      @if($user->id !== Auth::id())
      <form method="POST" action="{{ route('admin.users.toggle', $user->id) }}">
        @csrf @method('PATCH')
        <button type="submit"
                class="w-full px-4 py-2 text-sm font-medium rounded-xl transition-colors
                       {{ $user->is_active ? 'border border-red-700 text-red-400 hover:bg-red-900/20' : 'border border-green-700 text-green-400 hover:bg-green-900/20' }}">
          {{ $user->is_active ? 'Deactivate' : 'Activate' }}
        </button>
      </form>
      @endif

      {{-- Revoke tokens --}}
      <form method="POST" action="{{ route('admin.users.revoke-tokens', $user->id) }}"
            onsubmit="return confirm('Revoke all API tokens for this user?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="w-full px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors">
          Revoke tokens
        </button>
      </form>
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-5">

    {{-- Salons --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
      <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Owned Salons</h3>
      @forelse($salons as $salon)
      <div class="flex items-center justify-between py-2 border-b border-gray-800/50 last:border-0">
        <div>
          <a href="{{ route('admin.tenants.show', $salon->id) }}" class="font-medium text-gray-200 hover:text-white text-sm">
            {{ $salon->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $salon->subdomain }}.velour.app</p>
        </div>
        <span class="text-xs {{ $salon->is_active ? 'text-green-400' : 'text-red-400' }}">
          {{ $salon->is_active ? 'Active' : 'Suspended' }}
        </span>
      </div>
      @empty
      <p class="text-sm text-gray-500">No owned salons.</p>
      @endforelse
    </div>

    {{-- Super Admin Promotion --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
      <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">System Role</h3>
      @if($user->isSuperAdmin())
        @if($user->id !== Auth::id())
        <p class="text-sm text-gray-400 mb-4">This user is a super admin.</p>
        <form method="POST" action="{{ route('admin.users.demote', $user->id) }}"
              onsubmit="return confirm('Remove super admin privileges from this user?')">
          @csrf @method('DELETE')
          <button type="submit"
                  class="px-4 py-2 text-sm font-medium rounded-xl border border-red-700 text-red-400 hover:bg-red-900/20 transition-colors">
            Remove super admin
          </button>
        </form>
        @else
        <p class="text-sm text-gray-500">You cannot modify your own role.</p>
        @endif
      @else
        <p class="text-sm text-gray-400 mb-4">Grant this user full platform-wide admin access.</p>
        <form method="POST" action="{{ route('admin.users.promote', $user->id) }}"
              onsubmit="return confirm('Grant super admin privileges to {{ $user->name }}? This gives full platform access.')">
          @csrf
          <button type="submit"
                  class="px-4 py-2 text-sm font-medium rounded-xl border border-amber-700 text-amber-400 hover:bg-amber-900/20 transition-colors">
            ⚠ Grant super admin
          </button>
        </form>
      @endif
    </div>

  </div>

  {{-- Recent API tokens --}}
  @if($tokens->isNotEmpty())
  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <h3 class="px-5 py-4 text-sm font-semibold text-gray-300 border-b border-gray-800">Recent API Tokens</h3>
    <table class="w-full text-sm">
      <tbody class="divide-y divide-gray-800/50">
      @foreach($tokens as $token)
      <tr class="hover:bg-gray-800/30">
        <td class="px-5 py-3 text-gray-300 font-medium">{{ $token->name }}</td>
        <td class="px-4 py-3 text-xs text-gray-500">{{ $token->last_used_at?->diffForHumans() ?? 'Never used' }}</td>
        <td class="px-4 py-3 text-xs text-gray-500">Created {{ $token->created_at->format('d M Y') }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <a href="{{ route('admin.users') }}" class="inline-block text-sm text-gray-500 hover:text-gray-300">← All users</a>

</div>

@endsection
