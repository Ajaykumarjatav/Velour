<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * SuperAdminController
 *
 * Handles all landlord / platform-level admin views.
 * All routes are guarded by SuperAdminMiddleware.
 *
 * Routes prefix: /admin
 */
class SuperAdminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $stats = [
            'total_tenants'   => Tenant::withoutGlobalScopes()->count(),
            'active_tenants'  => Tenant::withoutGlobalScopes()->where('is_active', true)->count(),
            'total_users'     => User::withTrashed()->count(),
            'active_users'    => User::where('is_active', true)->count(),
            'new_this_month'  => Tenant::withoutGlobalScopes()
                                        ->whereMonth('created_at', now()->month)
                                        ->count(),
        ];

        $recentTenants = Tenant::withoutGlobalScopes()
            ->latest()
            ->take(10)
            ->get(['id','name','subdomain','is_active','created_at']);

        $recentUsers = User::latest()
            ->take(10)
            ->get(['id','name','email','plan','is_active','created_at']);

        return view('admin.dashboard', compact('stats', 'recentTenants', 'recentUsers'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tenants
    // ─────────────────────────────────────────────────────────────────────────

    public function tenants(Request $request)
    {
        $query = Tenant::withoutGlobalScopes()
            ->withCount(['staff', 'clients', 'appointments'])
            ->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->where('is_active', true);
        } elseif ($request->status === 'suspended') {
            $query->where('is_active', false);
        }

        $tenants = $query->paginate(25)->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function showTenant(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()
            ->withCount(['staff', 'clients', 'appointments', 'services'])
            ->findOrFail($id);

        $owner = User::find($tenant->owner_id);

        $recentAppointments = \App\Models\Appointment::withoutGlobalScopes()
            ->where('salon_id', $id)
            ->latest('starts_at')
            ->take(5)
            ->get();

        return view('admin.tenants.show', compact('tenant', 'owner', 'recentAppointments'));
    }

    public function toggleTenantStatus(Request $request, int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);
        $tenant->update(['is_active' => ! $tenant->is_active]);

        $status = $tenant->is_active ? 'activated' : 'suspended';
        return back()->with('success', "Tenant \"{$tenant->name}\" has been {$status}.");
    }

    public function updateTenantDomain(Request $request, int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($id);

        $data = $request->validate([
            'domain'    => 'nullable|string|max:253|unique:salons,domain,' . $id,
            'subdomain' => 'nullable|string|max:63|alpha_dash|unique:salons,subdomain,' . $id,
        ]);

        $tenant->update($data);

        return back()->with('success', 'Domain settings updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Users
    // ─────────────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = User::withTrashed()->latest();

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->role === 'super_admin') {
            $query->where('system_role', 'super_admin');
        }

        if ($request->status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status === 'deleted') {
            $query->onlyTrashed();
        }

        $users = $query->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function showUser(int $id)
    {
        $user   = User::withTrashed()->findOrFail($id);
        $salons = Tenant::withoutGlobalScopes()->where('owner_id', $id)->get();
        $roles  = $user->getRoleNames();
        $tokens = $user->tokens()->latest()->take(5)->get();

        return view('admin.users.show', compact('user', 'salons', 'roles', 'tokens'));
    }

    public function toggleUserStatus(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        // Cannot deactivate self
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User \"{$user->name}\" has been {$status}.");
    }

    public function impersonate(Request $request, int $id)
    {
        $target = User::findOrFail($id);

        // Store the admin's ID so we can return
        session([
            'impersonating'          => true,
            'impersonator_id'        => Auth::id(),
            'impersonator_name'      => Auth::user()->name,
        ]);

        Auth::login($target);

        return redirect()->route('dashboard')
            ->with('info', "You are now impersonating {$target->name}. Click \"Stop Impersonating\" to return.");
    }

    public function stopImpersonating(Request $request)
    {
        if (! session('impersonating')) {
            return redirect()->route('admin.dashboard');
        }

        $adminId = session('impersonator_id');
        $admin   = User::findOrFail($adminId);

        session()->forget(['impersonating', 'impersonator_id', 'impersonator_name']);

        Auth::login($admin);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Returned to your admin account.');
    }

    public function promoteToSuperAdmin(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['system_role' => 'super_admin']);
        $user->assignRole('super_admin');

        return back()->with('success', "{$user->name} is now a super admin.");
    }

    public function demoteFromSuperAdmin(Request $request, int $id)
    {
        if ($id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot demote yourself.']);
        }

        $user = User::findOrFail($id);
        $user->update(['system_role' => null]);
        $user->removeRole('super_admin');

        return back()->with('success', "{$user->name} is no longer a super admin.");
    }

    public function revokeAllTokens(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();

        return back()->with('success', "All API tokens revoked for {$user->name}.");
    }
}
