<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\InviteTeamMemberRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Staff;
use App\Notifications\StaffInviteCredentialsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * TenantAdminController
 *
 * Salon-level admin operations.
 * Routes prefix: /salon-admin
 * Guards: auth, verified, tenant, tenant_admin
 *
 * Provides:
 *  - Team member management (invite, role changes, removal)
 *  - Subscription / plan overview
 *  - Audit log
 *  - Danger zone (delete salon, transfer ownership)
 */
class TenantAdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Team Management
    // ─────────────────────────────────────────────────────────────────────────

    public function team(Request $request)
    {
        $salon = $this->currentSalon();

        $members = User::whereHas('staffProfile', function ($q) use ($salon) {
            $q->where('salon_id', $salon->id);
        })->orWhere('id', $salon->owner_id)
          ->with(['staffProfile', 'roles'])
          ->get();

        // Staff profiles created in Settings can exist without a linked user account.
        // Include them so team counts/listing match Staff & HR.
        $unlinkedStaff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereNull('user_id')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $invitableStaff = $unlinkedStaff->filter(fn (Staff $s) => filled($s->email))->values();

        $availableRoles = Role::whereIn('name', ['tenant_admin','manager','stylist','receptionist'])->get();

        return view('admin.tenant.team', compact('salon', 'members', 'unlinkedStaff', 'invitableStaff', 'availableRoles'));
    }

    public function invite(InviteTeamMemberRequest $request)
    {
        $salon = $this->currentSalon();
        $data  = $request->validated();

        $staff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereKey((int) $data['staff_id'])
            ->whereNull('user_id')
            ->firstOrFail();

        $email = mb_strtolower(trim((string) $staff->email));

        $temporaryPassword = Str::password(12);
        $existingUser      = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existingUser !== null) {
            // Orphan user rows (e.g. after team remove + new profile with same email): clear stale staff pointers.
            Staff::withoutGlobalScopes()
                ->where('user_id', $existingUser->id)
                ->where('id', '!=', $staff->id)
                ->update(['user_id' => null]);

            $existingUser->update([
                'name'                  => $staff->name,
                'password'              => Hash::make($temporaryPassword),
                'is_active'             => true,
                'email_verified_at'     => $existingUser->email_verified_at ?? now(),
                'force_password_change' => true,
            ]);
            $user = $existingUser->fresh();
        } else {
            $user = User::create([
                'name'                  => $staff->name,
                'email'                 => $email,
                'password'              => Hash::make($temporaryPassword),
                'is_active'             => true,
                'email_verified_at'     => now(),
                'force_password_change' => true,
            ]);
        }

        $user->syncRoles([$data['role']]);

        $staff->update([
            'user_id'   => $user->id,
            'is_active' => true,
        ]);
        if ($staff->deleted_at !== null) {
            $staff->restore();
        }

        $user->notify(new StaffInviteCredentialsNotification($salon->name, $temporaryPassword));

        $message = $existingUser !== null
            ? "A new temporary password was sent to {$email}. They must change it after signing in."
            : "Invitation sent to {$email} with a temporary password. They must change it after first login.";

        return back()->with('success', $message);
    }

    public function updateMemberRole(Request $request, int $userId)
    {
        $request->validate(['role' => 'required|in:tenant_admin,manager,stylist,receptionist']);

        $salon  = $this->currentSalon();
        $user   = User::findOrFail($userId);

        // Cannot demote the owner
        if ($user->id === $salon->owner_id) {
            return back()->withErrors(['role' => 'Cannot change the role of the salon owner.']);
        }

        // Replace all tenant roles with the new one
        $user->syncRoles([$request->role]);

        return back()->with('success', "{$user->name}'s role updated to " . ucfirst($request->role) . '.');
    }

    public function removeMember(Request $request, int $userId)
    {
        $salon = $this->currentSalon();

        if ($userId === $salon->owner_id) {
            return back()->withErrors(['error' => 'Cannot remove the salon owner.']);
        }

        if ($userId === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot remove yourself.']);
        }

        Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('user_id', $userId)
            ->delete();

        // Disable login for removed member to avoid orphaned access.
        User::query()->where('id', $userId)->update(['is_active' => false]);

        return back()->with('success', 'Team member removed.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Subscription / Plan
    // ─────────────────────────────────────────────────────────────────────────

    public function subscription(Request $request)
    {
        $user  = Auth::user();
        $salon = $this->currentSalon();

        $plans = [
            'starter'    => ['name' => 'Starter',    'price' => 29,  'staff' => 2,  'clients' => 200],
            'growth'     => ['name' => 'Growth',     'price' => 59,  'staff' => 5,  'clients' => 1000],
            'pro'        => ['name' => 'Pro',         'price' => 99,  'staff' => 15, 'clients' => 5000],
            'enterprise' => ['name' => 'Enterprise',  'price' => 199, 'staff' => 999, 'clients' => 999999],
        ];

        return view('admin.tenant.subscription', compact('user', 'salon', 'plans'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Danger Zone
    // ─────────────────────────────────────────────────────────────────────────

    public function transferOwnership(Request $request)
    {
        $request->validate([
            'new_owner_id' => 'required|exists:users,id',
            'password'     => 'required',
        ]);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $salon    = $this->currentSalon();
        $newOwner = User::findOrFail($request->new_owner_id);

        $salon->update(['owner_id' => $newOwner->id]);
        $newOwner->assignRole('tenant_admin');

        return back()->with('success', "Ownership transferred to {$newOwner->name}.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helper
    // ─────────────────────────────────────────────────────────────────────────

    private function currentSalon(): Tenant
    {
        return Tenant::current()
            ?? Auth::user()->salons()->firstOrFail();
    }
}
