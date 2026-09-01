<?php

namespace App\Services\Admin;

use App\Billing\Plan;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Subscription;

class AdminPlanAssignmentService
{
    public function __construct(private readonly AuditLogService $audit) {}

    /**
     * Assign any plan to a tenant owner — no payment gateway required.
     */
    public function assign(User $user, string $planKey, ?int $trialDays = null, ?string $note = null): void
    {
        $plan = Plan::findOrFail($planKey);
        $oldPlan = (string) ($user->plan ?? config('billing.default_plan', 'trial'));

        if ($plan->isTrial()) {
            $days = $trialDays ?? (int) config('billing.trial_days', 15);
            $user->update([
                'plan'          => 'trial',
                'trial_ends_at' => now()->addDays(max(1, $days)),
            ]);
            $this->cancelLocalSubscription($user);
        } else {
            $user->update([
                'plan'          => $planKey,
                'trial_ends_at' => null,
            ]);
            $this->ensureActiveSubscription($user, $planKey);
        }

        $this->audit->billing(
            'billing.plan_changed',
            "Admin assigned {$plan->name} to {$user->email} (was {$oldPlan})",
            $user,
            [
                'old_plan'    => $oldPlan,
                'new_plan'    => $planKey,
                'trial_days'  => $trialDays,
                'note'        => $note,
                'assigned_by' => Auth::id(),
            ]
        );
    }

    private function ensureActiveSubscription(User $user, string $planKey): void
    {
        $subscription = $user->subscription('default');

        $payload = [
            'stripe_status' => 'active',
            'stripe_price'  => "{$planKey}:monthly",
            'quantity'      => 1,
            'trial_ends_at' => null,
            'ends_at'       => null,
        ];

        if ($subscription) {
            $subscription->update($payload);

            return;
        }

        $user->subscriptions()->create(array_merge($payload, [
            'type'      => 'default',
            'stripe_id' => 'admin_'.$user->id.'_'.now()->timestamp,
        ]));
    }

    private function cancelLocalSubscription(User $user): void
    {
        Subscription::query()
            ->where('user_id', $user->id)
            ->where('type', 'default')
            ->whereNull('ends_at')
            ->update([
                'stripe_status' => 'canceled',
                'ends_at'       => now(),
            ]);
    }
}
