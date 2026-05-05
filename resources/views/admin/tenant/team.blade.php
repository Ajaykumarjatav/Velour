@extends('layouts.app')
@section('title', 'Team Management')
@section('page-title', 'Team Management')
@section('content')

@php
    use App\Models\Staff;
@endphp

<div class="max-w-3xl space-y-6">

  {{-- Invite member (staff profile must exist first — Staff & HR) --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 p-6 shadow-sm dark:shadow-none" x-data="{ open: false }">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-heading">Invite a team member</h2>
      <button @click="open=!open"
              class="px-4 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        + Invite
      </button>
    </div>
    <p class="text-sm text-muted mt-1">Add the person in <strong class="text-heading">Staff &amp; HR</strong> first (with an email). Then send an invitation: they receive a temporary password, sign in, and must set a new password before using the app.</p>
    <p class="text-sm text-muted mt-2">The list below only includes people marked <strong class="text-heading">Profile only</strong> in the team list—profiles that already have app access (roles like Stylist, no “Profile only” label) are not shown because they already have a login. Use <strong class="text-heading">Forgot password</strong> on the sign-in page if they need to get back in.</p>

    <div x-show="open" x-cloak class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
      @if(($invitableStaff ?? collect())->isEmpty())
        <p class="text-sm text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/50 border border-amber-100 dark:border-amber-800/80 rounded-xl px-4 py-3">
          No staff profiles are ready to invite. Create a profile under <a href="{{ route('staff.index') }}" class="text-velour-600 dark:text-velour-400 font-medium underline">Staff &amp; HR</a> and enter an email address, then return here.
        </p>
      @else
      <form method="POST" action="{{ route('salon-admin.team.invite') }}" class="space-y-4" id="salon-invite-form">
        @csrf
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Staff profile <span class="text-red-500 dark:text-red-400">*</span></label>
          <select name="staff_id" id="invite-staff-id" required
                  class="form-select w-full @error('staff_id') form-input-error @enderror">
            <option value="">Choose someone already on the team (no login yet)…</option>
            @foreach($invitableStaff as $s)
            <option value="{{ $s->id }}" data-default-role="{{ Staff::defaultSpatieRoleForStaffJob($s->role) }}"
                    {{ (string) old('staff_id') === (string) $s->id ? 'selected' : '' }}>
              {{ $s->name }} — {{ $s->email }}
            </option>
            @endforeach
          </select>
          @error('staff_id')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">App role &amp; permissions <span class="text-red-500 dark:text-red-400">*</span></label>
          <select name="role" id="invite-role" required
                  class="form-select w-full">
            @foreach(['tenant_admin' => 'Admin — Full access', 'manager' => 'Manager — Operations', 'stylist' => 'Stylist — Own appointments', 'receptionist' => 'Receptionist — Front desk'] as $value => $label)
            <option value="{{ $value }}" {{ old('role', 'stylist') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <p class="text-xs text-muted mt-1">Suggested from their job role when you pick a profile; you can change it before sending.</p>
        </div>
        <div class="flex gap-3">
          <button type="submit"
                  class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
            Send invitation email
          </button>
          <button type="button" @click="open=false"
                  class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/70 text-gray-600 dark:text-gray-300 transition-colors">
            Cancel
          </button>
        </div>
      </form>
      @push('scripts')
      <script>
        (function () {
          var staffSel = document.getElementById('invite-staff-id');
          var roleSel = document.getElementById('invite-role');
          if (!staffSel || !roleSel) return;
          function syncRole() {
            var opt = staffSel.options[staffSel.selectedIndex];
            var dr = opt && opt.getAttribute('data-default-role');
            if (dr) roleSel.value = dr;
          }
          staffSel.addEventListener('change', syncRole);
          if (staffSel.value) syncRole();
        })();
      </script>
      @endpush
      @endif
    </div>
  </div>

  {{-- Team members list --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 overflow-hidden shadow-sm dark:shadow-none">
    <h2 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">
      Team members ({{ $members->count() + (($unlinkedStaff ?? collect())->count()) }})
    </h2>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @foreach($members as $member)
      <div class="px-6 py-4 flex items-center gap-4" x-data="{ editRole: false }">
        <div class="w-10 h-10 rounded-xl bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold flex-shrink-0">
          {{ strtoupper(substr($member->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-semibold text-heading">{{ $member->name }}</p>
            @if($member->id === $salon->owner_id)
            <span class="px-2 py-0.5 text-xs bg-velour-100 dark:bg-velour-900/50 text-velour-700 dark:text-velour-300 rounded-lg font-semibold">Owner</span>
            @endif
            @if($member->id !== $salon->owner_id)
            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg">App access</span>
            @endif
            @foreach($member->roles as $role)
            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg">{{ ucfirst(str_replace('_',' ',$role->name)) }}</span>
            @endforeach
          </div>
          <p class="text-sm text-muted">{{ $member->email }}</p>
        </div>
        @if($member->id !== $salon->owner_id)
        <div class="flex items-center gap-2 flex-shrink-0">
          {{-- Role change --}}
          <div x-show="!editRole">
            <button @click="editRole=true" class="text-xs text-velour-600 dark:text-velour-400 hover:text-velour-700 dark:hover:text-velour-300 font-medium">
              Edit role
            </button>
          </div>
          <div x-show="editRole" x-cloak>
            <form method="POST" action="{{ route('salon-admin.team.role', $member->id) }}" class="flex gap-2">
              @csrf @method('PATCH')
              <select name="role" class="px-3 py-1.5 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 max-w-[11rem] min-w-0 shrink focus:outline-none focus:ring-2 focus:ring-velour-500">
                @foreach(['tenant_admin','manager','stylist','receptionist'] as $r)
                <option value="{{ $r }}" {{ $member->hasRole($r) ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>
                @endforeach
              </select>
              <button type="submit" class="px-3 py-1.5 text-xs font-medium rounded-xl bg-velour-600 text-white hover:bg-velour-700">Save</button>
              <button type="button" @click="editRole=false" class="text-xs text-muted hover:text-heading">✕</button>
            </form>
          </div>

          {{-- Remove --}}
          <form method="POST" action="{{ route('salon-admin.team.remove', $member->id) }}"
                onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 dark:text-red-400 hover:text-red-600 dark:hover:text-red-300 font-medium">Remove</button>
          </form>
        </div>
        @endif
      </div>
      @endforeach

      @foreach(($unlinkedStaff ?? collect()) as $staffMember)
      <div class="px-6 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold flex-shrink-0">
          {{ strtoupper(substr($staffMember->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-semibold text-heading">{{ $staffMember->name }}</p>
            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg">Profile only</span>
            @if($staffMember->role)
            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg">{{ ucfirst(str_replace('_',' ', $staffMember->role)) }}</span>
            @endif
          </div>
          <p class="text-sm text-muted">{{ $staffMember->email ?: 'No login account linked' }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Permissions reference --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 p-6 shadow-sm dark:shadow-none">
    <h2 class="font-semibold text-heading mb-4">Role permissions</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-center">
        <thead>
        <tr class="text-muted uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
          <th class="text-left pb-3 pr-4">Feature</th>
          <th class="pb-3 px-3">Admin</th>
          <th class="pb-3 px-3">Manager</th>
          <th class="pb-3 px-3">Stylist</th>
          <th class="pb-3 px-3">Receptionist</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
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
          <td class="text-left py-2.5 pr-4 font-medium text-heading">{{ $feature }}</td>
          @foreach($perms as $p)
          <td class="py-2.5 px-3 {{ $p ? 'text-green-600 dark:text-green-400' : 'text-gray-300 dark:text-gray-600' }}">{{ $p ? '✓' : '—' }}</td>
          @endforeach
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>

@endsection
