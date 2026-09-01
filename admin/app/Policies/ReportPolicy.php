<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ReportPolicy
 *
 * Reports are gated by two things:
 *   1. Plan tier — must be on Pro or Enterprise (feature: reports)
 *   2. Role — only managers and above can view reports
 *
 * Super-admins bypass both checks.
 */
class ReportPolicy
{
    use HandlesAuthorization;

    private function canAccessReports(User $user): bool
    {
        if ($user->isSuperAdmin()) return true;

        return $user->planAllows('reports')
            && $user->hasAnyRole(['tenant_admin', 'manager']);
    }

    public function viewAny(User $user): bool
    {
        return $this->canAccessReports($user);
    }

    public function view(User $user): bool
    {
        return $this->canAccessReports($user);
    }

    public function export(User $user): bool
    {
        return $this->canAccessReports($user)
            && $user->hasRole(['tenant_admin', 'manager']);
    }

    public function viewFinancial(User $user): bool
    {
        // Financial reports: tenant_admin only
        return $user->isSuperAdmin()
            || ($user->planAllows('reports') && $user->hasRole('tenant_admin'));
    }

    public function viewStaff(User $user): bool
    {
        return $this->canAccessReports($user);
    }
}
