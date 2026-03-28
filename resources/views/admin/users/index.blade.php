@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'All Users')
@section('content')

<form method="GET" action="{{ route('admin.users') }}" class="flex gap-3 mb-6 flex-wrap">
  <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name or email…"
         class="flex-1 min-w-0 px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">
  <select name="role" onchange="this.form.submit()"
          class="px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All roles</option>
    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
  </select>
  <select name="status" onchange="this.form.submit()"
          class="px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All</option>
    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    <option value="deleted"  {{ request('status') === 'deleted'  ? 'selected' : '' }}>Deleted</option>
  </select>
  <button type="submit" class="px-5 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
    Search
  </button>
</form>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Plan</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">2FA</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Joined</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($users as $user)
    <tr class="hover:bg-gray-800/30 transition-colors {{ $user->trashed() ? 'opacity-50' : '' }}">
      <td class="px-5 py-3.5">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-velour-800 flex items-center justify-center text-velour-300 text-xs font-bold flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
          </div>
          <div>
            <a href="{{ route('admin.users.show', $user->id) }}" class="font-medium text-gray-200 hover:text-white">
              {{ $user->name }}
            </a>
            @if($user->isSuperAdmin())
            <span class="ml-1.5 px-1.5 py-0.5 text-xs bg-red-900/60 text-red-300 rounded font-bold">ADMIN</span>
            @endif
            <p class="text-xs text-gray-500">{{ $user->email }}</p>
          </div>
        </div>
      </td>
      <td class="px-4 py-3.5 hidden md:table-cell text-xs text-gray-400 uppercase">{{ $user->plan ?? '—' }}</td>
      <td class="px-4 py-3.5 hidden sm:table-cell">
        @if($user->hasTwoFactorEnabled())
        <span class="text-xs text-green-400 font-medium">✓ {{ strtoupper($user->two_factor_method) }}</span>
        @else
        <span class="text-xs text-gray-600">Off</span>
        @endif
      </td>
      <td class="px-4 py-3.5">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold
              {{ $user->trashed() ? 'bg-gray-800 text-gray-500' : ($user->is_active ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400') }}">
          {{ $user->trashed() ? 'Deleted' : ($user->is_active ? 'Active' : 'Inactive') }}
        </span>
      </td>
      <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500">
        {{ $user->created_at->format('d M Y') }}
      </td>
      <td class="px-4 py-3.5">
        <a href="{{ route('admin.users.show', $user->id) }}"
           class="text-xs text-velour-400 hover:text-velour-300 font-medium">View</a>
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">No users found</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>

@endsection
