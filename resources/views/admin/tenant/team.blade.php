@extends('layouts.app')
@section('title', 'Team Management')
@section('page-title', 'Team Management')
@section('content')

<div class="max-w-3xl space-y-6">

  {{-- Invite member --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6" x-data="{ open: false }">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-gray-900">Invite a team member</h2>
      <button @click="open=!open"
              class="px-4 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        + Invite
      </button>
    </div>

    <div x-show="open" x-cloak class="mt-5 border-t border-gray-100 pt-5">
      <form method="POST" action="{{ route('salon-admin.team.invite') }}" class="space-y-4">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Full name <span class="text-red-500">*</span></label>
            <input type="text" name="name" required value="{{ old('name') }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address <span class="text-red-500">*</span></label>
            <input type="email" name="email" required value="{{ old('email') }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('email') border-red-400 @else border-gray-200 @enderror text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Role <span class="text-red-500">*</span></label>
          <select name="role" required
                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
            @foreach(['tenant_admin' => 'Admin — Full access', 'manager' => 'Manager — Operations', 'stylist' => 'Stylist — Own appointments', 'receptionist' => 'Receptionist — Front desk'] as $value => $label)
            <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex gap-3">
          <button type="submit"
                  class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
            Send invitation
          </button>
          <button type="button" @click="open=false"
                  class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Team members list --}}
  <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <h2 class="px-6 py-4 font-semibold text-gray-900 border-b border-gray-100">
      Team members ({{ $members->count() }})
    </h2>
    <div class="divide-y divide-gray-50">
      @foreach($members as $member)
      <div class="px-6 py-4 flex items-center gap-4" x-data="{ editRole: false }">
        <div class="w-10 h-10 rounded-xl bg-velour-100 flex items-center justify-center text-velour-700 font-bold flex-shrink-0">
          {{ strtoupper(substr($member->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-semibold text-gray-900">{{ $member->name }}</p>
            @if($member->id === $salon->owner_id)
            <span class="px-2 py-0.5 text-xs bg-velour-100 text-velour-700 rounded-lg font-semibold">Owner</span>
            @endif
            @foreach($member->roles as $role)
            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-lg">{{ ucfirst(str_replace('_',' ',$role->name)) }}</span>
            @endforeach
          </div>
          <p class="text-sm text-gray-400">{{ $member->email }}</p>
        </div>
        @if($member->id !== $salon->owner_id)
        <div class="flex items-center gap-2 flex-shrink-0">
          {{-- Role change --}}
          <div x-show="!editRole">
            <button @click="editRole=true" class="text-xs text-velour-600 hover:text-velour-700 font-medium">
              Edit role
            </button>
          </div>
          <div x-show="editRole" x-cloak>
            <form method="POST" action="{{ route('salon-admin.team.role', $member->id) }}" class="flex gap-2">
              @csrf @method('PATCH')
              <select name="role" class="px-3 py-1.5 text-xs rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">
                @foreach(['tenant_admin','manager','stylist','receptionist'] as $r)
                <option value="{{ $r }}" {{ $member->hasRole($r) ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                @endforeach
              </select>
              <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-xl bg-velour-600 text-white hover:bg-velour-700">Save</button>
              <button type="button" @click="editRole=false" class="text-xs text-gray-400 hover:text-gray-600">✕</button>
            </form>
          </div>

          {{-- Remove --}}
          <form method="POST" action="{{ route('salon-admin.team.remove', $member->id) }}"
                onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 hover:text-red-600 font-medium">Remove</button>
          </form>
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>

  {{-- Permissions reference --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-900 mb-4">Role permissions</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-center">
        <thead>
        <tr class="text-gray-500 uppercase tracking-wider border-b border-gray-100">
          <th class="text-left pb-3 pr-4">Feature</th>
          <th class="pb-3 px-3">Admin</th>
          <th class="pb-3 px-3">Manager</th>
          <th class="pb-3 px-3">Stylist</th>
          <th class="pb-3 px-3">Receptionist</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-50 text-sm">
        @foreach([
          'Appointments'    => [1,1,1,1],
          'Clients'         => [1,1,1,1],
          'Staff'           => [1,1,0,0],
          'Services'        => [1,1,0,1],
          'Inventory'       => [1,1,0,1],
          'POS / Sales'     => [1,1,1,1],
          'Marketing'       => [1,1,0,0],
          'Reports'         => [1,1,0,1],
          'Reviews'         => [1,1,0,1],
          'Settings'        => [1,0,0,0],
          'User Management' => [1,0,0,0],
          'Billing'         => [1,0,0,0],
        ] as $feature => $perms)
        <tr>
          <td class="text-left py-2.5 pr-4 font-medium text-gray-700">{{ $feature }}</td>
          @foreach($perms as $p)
          <td class="py-2.5 px-3 {{ $p ? 'text-green-500' : 'text-gray-200' }}">{{ $p ? '✓' : '—' }}</td>
          @endforeach
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>

@endsection
