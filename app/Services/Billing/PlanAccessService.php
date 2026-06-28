<?php

namespace App\Services\Billing;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Tenant;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Support\AuthPanel;

class PlanAccessService
{
    /** Account that owns billing (salon owner for staff members). */
    public function resolveBillingAccount(User $user): User
    {
        if ($user->salons()->exists()) {
            return $user;
        }

        $salonId = $this->resolveSalonId($user);
        if (! $salonId) {
            return $user;
        }

        $ownerId = Salon::withoutGlobalScopes()->whereKey($salonId)->value('owner_id');

        return $ownerId
            ? User::query()->find($ownerId) ?? $user
            : $user;
    }

    public function hasActiveAccess(?User $account): bool
    {
        if (! $account || ! config('billing.subscriptions_enabled')) {
            return true;
        }

        if ($account->isSuperAdmin()) {
            return true;
        }

        if ($this->hasActiveOverride($account)) {
            return true;
        }

        $subscription = $account->subscription('default');
        if ($subscription && $subscription->valid()) {
            return true;
        }

        if ($account->trial_ends_at?->isFuture()) {
            return true;
        }

        return false;
    }

    public function isExpired(User $user): bool
    {
        if (! config('billing.subscriptions_enabled')) {
            return false;
        }

        if ($user->isSuperAdmin() || AuthPanel::isAdminStoreBrowse()) {
            return false;
        }

        return ! $this->hasActiveAccess($this->resolveBillingAccount($user));
    }

    public function renewalUrl(): string
    {
        return route('billing.plans');
    }

    /**
     * Banner data for tenant panel — days left on trial / subscription period.
     *
     * @return array<string, mixed>|null
     */
    public function reminderFor(User $user): ?array
    {
        if (! config('billing.subscriptions_enabled')) {
            return null;
        }

        if ($user->isSuperAdmin() || AuthPanel::isAdminStoreBrowse()) {
            return null;
        }

        $account = $this->resolveBillingAccount($user);
        $plan = $account->currentPlan();
        $sub = $account->subscription('default');

        if ($this->isExpired($user)) {
            return [
                'kind'           => 'expired',
                'plan_label'     => $plan->name,
                'days_remaining' => 0,
                'ends_at'        => null,
                'urgent'         => true,
                'renew_url'      => $this->renewalUrl(),
            ];
        }

        $endsAt = $this->resolveAccessEndsAt($account, $sub);
        $kind = 'trial';

        if ($account->trial_ends_at?->isFuture()) {
            $kind = 'trial';
        } elseif ($sub?->onGracePeriod() && $sub->ends_at?->isFuture()) {
            $kind = 'grace';
        } elseif ($sub?->onTrial() && $sub->trial_ends_at?->isFuture()) {
            $kind = 'trial';
        } elseif ($endsAt && $this->activeOverrideExpiresAt($account)?->equalTo($endsAt)) {
            $kind = 'override';
        } elseif ($plan->isPaid() && $sub?->active()) {
            return [
                'kind'           => 'active',
                'plan_label'     => $plan->name,
                'days_remaining' => null,
                'ends_at'        => null,
                'urgent'         => false,
                'renew_url'      => route('billing.dashboard'),
            ];
        }

        if (! $endsAt) {
            return null;
        }

        $daysRemaining = max(0, (int) now()->startOfDay()->diffInDays($endsAt->copy()->startOfDay(), false));

        $message = null;
        if ($account->scheduled_plan && $account->scheduled_plan_starts_at?->isFuture()) {
            $scheduled = \App\Billing\Plan::find($account->scheduled_plan);
            $message = ($scheduled?->name ?? 'Paid plan').' starts '.$account->scheduled_plan_starts_at->format('d M Y');
        }

        return [
            'kind'           => $kind,
            'plan_label'     => $plan->name,
            'days_remaining' => $daysRemaining,
            'ends_at'        => $endsAt,
            'urgent'         => $daysRemaining <= 3,
            'warning'        => $daysRemaining <= 7,
            'renew_url'      => $kind === 'grace' ? route('billing.dashboard') : $this->renewalUrl(),
            'scheduled_note' => $message,
        ];
    }

    private function resolveAccessEndsAt(User $account, $sub): ?\Illuminate\Support\Carbon
    {
        if ($account->trial_ends_at?->isFuture()) {
            return $account->trial_ends_at;
        }

        if ($sub?->onGracePeriod() && $sub->ends_at?->isFuture()) {
            return $sub->ends_at;
        }

        if ($sub?->onTrial() && $sub->trial_ends_at?->isFuture()) {
            return $sub->trial_ends_at;
        }

        return $this->activeOverrideExpiresAt($account);
    }

    private function activeOverrideExpiresAt(User $account): ?\Illuminate\Support\Carbon
    {
        $salonIds = Salon::withoutGlobalScopes()
            ->where('owner_id', $account->id)
            ->pluck('id');

        if ($salonIds->isEmpty()) {
            return null;
        }

        $override = TenantPlanOverride::query()
            ->whereIn('salon_id', $salonIds)
            ->active()
            ->whereNotNull('expires_at')
            ->orderBy('expires_at')
            ->first();

        return $override?->expires_at;
    }

    private function hasActiveOverride(User $account): bool
    {
        $salonIds = Salon::withoutGlobalScopes()
            ->where('owner_id', $account->id)
            ->pluck('id');

        if ($salonIds->isEmpty()) {
            return false;
        }

        return TenantPlanOverride::query()
            ->whereIn('salon_id', $salonIds)
            ->active()
            ->exists();
    }

    private function resolveSalonId(User $user): ?int
    {
        if (Tenant::checkCurrent()) {
            return (int) Tenant::current()->getKey();
        }

        $sessionSalonId = (int) session('active_salon_id', 0);
        if ($sessionSalonId > 0) {
            return $sessionSalonId;
        }

        $staffSalonId = Staff::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->value('salon_id');

        return $staffSalonId ? (int) $staffSalonId : null;
    }
}
