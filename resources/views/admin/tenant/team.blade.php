@extends('layouts.app')
@section('title', 'Team Management')
@section('page-title', 'Team Management')
@section('content')

@php
    use App\Models\Staff;
    $loginRoles = $permissionRoles ?? [];
@endphp

<div class="max-w-5xl space-y-6">

  {{-- Invite member --}}
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 p-6 shadow-sm dark:shadow-none" x-data="{ open: false }">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-heading">Invite a team member</h2>
      <button @click="open=!open"
              class="px-4 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        + Invite
      </button>
    </div>
    <p class="text-sm text-muted mt-1">Add the person in <strong class="text-heading">Staff &amp; HR</strong> first (with an email). Then send an invitation with an app login role and permissions.</p>

    <div x-show="open" x-cloak class="mt-5 border-t border-gray-100 dark:border-gray-800 pt-5">
      @if(($invitableStaff ?? collect())->isEmpty())
        <p class="text-sm text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/50 border border-amber-100 dark:border-amber-800/80 rounded-xl px-4 py-3">
          No staff profiles are ready to invite. Create a profile under <a href="{{ route('staff.index') }}" class="text-velour-600 dark:text-velour-400 font-medium underline">Staff &amp; HR</a> and enter an email address, then return here.
        </p>
      @else
      <form method="POST" action="{{ route('salon-admin.team.invite') }}" class="space-y-4" id="salon-invite-form">
        @csrf
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">Staff profile <span class="text-red-500">*</span></label>
          <select name="staff_id" id="invite-staff-id" required class="form-select w-full">
            <option value="">Choose someone already on the team (no login yet)…</option>
            @foreach($invitableStaff as $s)
            <option value="{{ $s->id }}" data-default-role="{{ Staff::defaultSpatieRoleForStaffJob($s->role) }}"
                    {{ (string) old('staff_id') === (string) $s->id ? 'selected' : '' }}>
              {{ $s->name }} — {{ $s->email }}
            </option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-heading mb-1.5">App role &amp; permissions <span class="text-red-500">*</span></label>
          <select name="role" id="invite-role" required class="form-select w-full">
            @foreach($loginRoles as $value => $label)
            <option value="{{ $value }}" {{ old('role', 'hair_stylist') === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
          <p class="text-xs text-muted mt-1">Suggested from their job title when you pick a profile. Permissions follow the role matrix below.</p>
        </div>
        <div class="flex gap-3">
          <button type="submit" class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white">Send invitation email</button>
          <button type="button" @click="open=false" class="btn-outline">Cancel</button>
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

  {{-- Team members --}}
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
            @foreach($member->roles as $role)
            <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg">
              {{ $loginRoles[$role->name] ?? ucfirst(str_replace('_', ' ', $role->name)) }}
            </span>
            @endforeach
          </div>
          <p class="text-sm text-muted">{{ $member->email }}</p>
        </div>
        @if($member->id !== $salon->owner_id)
        <div class="flex items-center gap-2 flex-shrink-0">
          <div x-show="!editRole">
            <button @click="editRole=true" class="text-xs text-velour-600 dark:text-velour-400 font-medium">Edit role</button>
          </div>
          <div x-show="editRole" x-cloak>
            <form method="POST" action="{{ route('salon-admin.team.role', $member->id) }}" class="flex gap-2">
              @csrf @method('PATCH')
              <select name="role" class="form-select text-xs max-w-[11rem]">
                @foreach($loginRoles as $r => $rlabel)
                <option value="{{ $r }}" {{ $member->hasRole($r) ? 'selected' : '' }}>{{ $rlabel }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn-primary text-xs py-1.5">Save</button>
              <button type="button" @click="editRole=false" class="text-xs text-muted">✕</button>
            </form>
          </div>
          <form method="POST" action="{{ route('salon-admin.team.remove', $member->id) }}"
                onsubmit="return confirm('Remove {{ $member->name }} from the team?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-500 font-medium">Remove</button>
          </form>
        </div>
        @endif
      </div>
      @endforeach

      @foreach(($unlinkedStaff ?? collect()) as $staffMember)
      <div class="px-6 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 font-bold flex-shrink-0">
          {{ strtoupper(substr($staffMember->name, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-semibold text-heading">{{ $staffMember->name }} <span class="text-xs font-normal text-muted">Profile only</span></p>
          <p class="text-sm text-muted">{{ $staffMember->email ?: 'No email' }} · {{ \App\Support\StaffJobRoles::label($staffMember->role) }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Permissions: per-module action toggles (AJAX) --}}
  @php
    $authRoleKey = auth()->user()->roles->first()?->name;
    $defaultRoleKey = ($authRoleKey && isset($loginRoles[$authRoleKey]))
        ? $authRoleKey
        : (array_key_first($loginRoles) ?? 'tenant_admin');
    $authRoleLabel = $authRoleKey ? ($loginRoles[$authRoleKey] ?? \App\Support\StaffJobRoles::label($authRoleKey)) : null;
  @endphp
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('salonRolePermissions', (config) => ({
        activeRole: config.activeRole,
        roleLabels: config.roleLabels,
        rolePermissions: config.rolePermissions,
        toggleUrl: config.toggleUrl,
        csrf: config.csrf,
        canEdit: config.canEdit,
        pending: null,
        roleLabel() {
          return this.roleLabels[this.activeRole] ?? this.activeRole;
        },
        permissionsForActiveRole() {
          if (!this.rolePermissions[this.activeRole]) {
            this.rolePermissions[this.activeRole] = [];
          }
          return this.rolePermissions[this.activeRole];
        },
        hasPermission(key) {
          return this.permissionsForActiveRole().includes(key);
        },
        notify(message, type = 'success') {
          if (typeof window.showToast === 'function') {
            window.showToast(message, type);
          }
        },
        async togglePermission(permission) {
          if (!this.canEdit || this.pending) return;
          const role = this.activeRole;
          const enabled = !this.hasPermission(permission);
          const previous = [...this.permissionsForActiveRole()];
          if (enabled) {
            this.rolePermissions[role] = [...previous, permission];
          } else {
            this.rolePermissions[role] = previous.filter((p) => p !== permission);
          }
          this.pending = permission;
          try {
            const res = await fetch(this.toggleUrl, {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
              },
              body: JSON.stringify({ role, permission, enabled }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
              throw new Error(data.message || 'Could not update permission.');
            }
            this.rolePermissions[role] = data.permissions ?? this.rolePermissions[role];
            this.notify(data.message || 'Saved', 'success');
          } catch (err) {
            this.rolePermissions[role] = previous;
            this.notify(err.message || 'Something went wrong.', 'error');
          } finally {
            this.pending = null;
          }
        },
      }));
    });
  </script>
  <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 shadow-sm dark:shadow-none overflow-hidden"
       x-data="salonRolePermissions({
         activeRole: @js($defaultRoleKey),
         roleLabels: @js($loginRoles),
         rolePermissions: @js($rolePermissions),
         toggleUrl: @js(route('salon-admin.team.role-permission-toggle')),
         csrf: @js(csrf_token()),
         canEdit: @json($canEditPermissions ?? false),
       })">

    <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-gray-800">
      <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
          <h2 class="font-semibold text-heading">Permissions</h2>
          <p class="text-sm text-muted mt-1 max-w-2xl">
            @if($canEditPermissions ?? false)
              Click an action to turn it on or off for each role. Changes save instantly.
            @else
              View-only: you do not have permission to edit role access.
            @endif
          </p>
          @if($authRoleLabel)
          <p class="text-xs text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 rounded-lg px-3 py-2 mt-3 max-w-2xl">
            Your login uses the <strong class="text-heading">{{ $authRoleLabel }}</strong> role.
            Settings and other access follow the permissions on that tab — not Hair Stylist or Admin unless that is your role.
          </p>
          @endif
        </div>
        <p class="text-xs text-muted sm:text-right shrink-0">
          Editing: <span class="font-semibold text-heading" x-text="roleLabel()"></span>
        </p>
      </div>
    </div>

    <div class="px-6 border-b border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-950/40">
      <div class="flex gap-0 overflow-x-auto -mb-px" role="tablist" aria-label="Select role">
        @foreach($loginRoles as $roleKey => $roleLabel)
        <button type="button"
                role="tab"
                :aria-selected="activeRole === '{{ $roleKey }}'"
                @click="activeRole='{{ $roleKey }}'"
                class="shrink-0 px-4 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors"
                :class="activeRole === '{{ $roleKey }}'
                  ? 'border-velour-600 text-velour-600 dark:text-velour-400 dark:border-velour-500'
                  : 'border-transparent text-muted hover:text-heading hover:border-gray-300 dark:hover:border-gray-600'">
          {{ $roleLabel }}
          @if($authRoleKey === $roleKey)
          <span class="ml-1.5 text-[10px] font-semibold uppercase tracking-wide text-velour-600 dark:text-velour-400">You</span>
          @endif
        </button>
        @endforeach
      </div>
    </div>

    <div class="p-6 space-y-4">
      @forelse($permissionGroups as $moduleKey => $group)
        @include('admin.tenant.partials.permission-group', ['group' => $group, 'moduleKey' => $moduleKey])
      @empty
        <p class="text-sm text-muted">No permission modules are configured.</p>
      @endforelse

      <p class="text-xs text-muted pt-1">
        Need another role? Assign that job title in
        <a href="{{ route('staff.index') }}" class="text-velour-600 dark:text-velour-400 font-medium hover:underline">Staff &amp; HR</a> first.
      </p>
    </div>

  </div>

</div>
@endsection
