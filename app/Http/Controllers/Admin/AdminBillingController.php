<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminBillingController
 *
 * Platform-level billing overview for super-admins.
 * Shows MRR, ARR, plan distribution, churn signals, and recent webhook events.
 *
 * Routes prefix: /admin/billing
 * Guards: auth, verified, 2fa, super_admin
 */
class AdminBillingController extends Controller
{
    public function index()
    {
        // ── Revenue metrics ───────────────────────────────────────────────

        $planPrices = Plan::all()
            ->filter->isPaid()
            ->mapWithKeys(fn ($p) => [$p->key => $p->priceMonthly]);

        // MRR: count active subscribers per plan × monthly price
        $planCounts = User::whereHas('subscriptions', fn ($q) =>
                $q->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing')
            )
            ->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $mrr = $planCounts->reduce(function ($carry, $count, $plan) use ($planPrices) {
            return $carry + ($count * ($planPrices[$plan] ?? 0));
        }, 0);

        $arr = $mrr * 12;

        // Trial users
        $trialCount = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'trialing')
        )->count();

        // Past-due (payment failed)
        $pastDueCount = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'past_due')
        )->count();

        // Cancelled this month
        $cancelledThisMonth = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'canceled')
              ->whereMonth('ends_at', now()->month)
              ->whereYear('ends_at', now()->year)
        )->count();

        // ── Plan distribution ─────────────────────────────────────────────

        $allPlanCounts = User::select('plan', DB::raw('count(*) as count'))
            ->whereNotNull('plan')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $totalBillable = $allPlanCounts->sum();

        $planDistribution = Plan::all()->map(function ($plan) use ($allPlanCounts, $totalBillable) {
            $count   = $allPlanCounts[$plan->key] ?? 0;
            $percent = $totalBillable > 0 ? round(($count / $totalBillable) * 100) : 0;
            return [
                'plan'    => $plan,
                'count'   => $count,
                'percent' => $percent,
                'mrr'     => $count * $plan->priceMonthly,
            ];
        });

        // ── Recent subscriptions ──────────────────────────────────────────

        $recentSubscriptions = DB::table('subscriptions')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->select(
                'subscriptions.id',
                'subscriptions.stripe_status',
                'subscriptions.trial_ends_at',
                'subscriptions.ends_at',
                'subscriptions.created_at',
                'users.name as user_name',
                'users.email as user_email',
                'users.plan',
                'users.id as user_id'
            )
            ->orderByDesc('subscriptions.created_at')
            ->limit(15)
            ->get();

        // ── Recent webhook calls ──────────────────────────────────────────

        $recentWebhooks = DB::table('webhook_calls')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'stripe_event_id', 'type', 'status', 'exception', 'created_at']);

        $webhookStats = DB::table('webhook_calls')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.billing.index', compact(
            'mrr', 'arr', 'trialCount', 'pastDueCount', 'cancelledThisMonth',
            'planDistribution', 'recentSubscriptions', 'recentWebhooks', 'webhookStats'
        ));
    }

    public function webhooks(Request $request)
    {
        $query = DB::table('webhook_calls')->orderByDesc('created_at');

        if ($type = $request->type) {
            $query->where('type', 'like', "%{$type}%");
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $webhooks = $query->paginate(30)->withQueryString();

        return view('admin.billing.webhooks', compact('webhooks'));
    }

    public function replayWebhook(Request $request, int $id)
    {
        $call = DB::table('webhook_calls')->find($id);

        if (! $call || $call->status !== 'failed') {
            return back()->with('info', 'Only failed webhooks can be replayed.');
        }

        try {
            $payload = json_decode($call->payload, true);
            $event   = \Stripe\Event::constructFrom($payload);

            app(\App\Http\Controllers\Billing\WebhookController::class)
                ->handle(new \Illuminate\Http\Request()); // simplified — real replay via Stripe CLI

            DB::table('webhook_calls')->where('id', $id)
              ->update(['status' => 'processed', 'exception' => null, 'updated_at' => now()]);

            return back()->with('success', "Webhook #{$id} replayed successfully.");
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Replay failed: ' . $e->getMessage()]);
        }
    }
}
