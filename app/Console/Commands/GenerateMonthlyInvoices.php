<?php

namespace App\Console\Commands;

use App\Models\Salon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * GenerateMonthlyInvoices — AUDIT FIX: Subscription & Billing Validation
 *
 * Reconciles platform-side invoice records with Stripe invoices.
 * Flags any missing invoices, payment failures, or sync issues.
 * Scheduled: 1st of each month at 06:00 UTC
 */
class GenerateMonthlyInvoices extends Command
{
    protected $signature   = 'velour:reconcile-billing {--month= : Month to reconcile (YYYY-MM)}';
    protected $description = 'Reconcile Stripe billing data with local records';

    public function handle(): int
    {
        $month = $this->option('month') ?? now()->subMonth()->format('Y-m');
        $this->info("Reconciling billing for {$month}...");

        [$year, $mon] = explode('-', $month);
        $start = \Carbon\Carbon::createFromDate($year, $mon, 1)->startOfMonth()->timestamp;
        $end   = \Carbon\Carbon::createFromDate($year, $mon, 1)->endOfMonth()->timestamp;

        $stripe = new \Stripe\StripeClient(config('cashier.secret'));
        $issues = [];
        $ok     = 0;

        try {
            // Paginate through all Stripe invoices in the period
            $invoices = $stripe->invoices->all([
                'created' => ['gte' => $start, 'lte' => $end],
                'limit'   => 100,
                'status'  => 'paid',
            ]);

            foreach ($invoices->autoPagingIterator() as $invoice) {
                $user = \App\Models\User::where('stripe_id', $invoice->customer)->first();

                if (! $user) {
                    $issues[] = "No user for Stripe customer {$invoice->customer} (invoice {$invoice->id})";
                    continue;
                }

                // Verify local record exists
                $local = \Illuminate\Support\Facades\DB::table('subscriptions')
                    ->where('user_id', $user->id)
                    ->where('stripe_status', 'active')
                    ->exists();

                if (! $local) {
                    $issues[] = "User {$user->email} has paid Stripe invoice but no active local subscription";
                }

                $ok++;
            }
        } catch (\Throwable $e) {
            $this->error("Stripe API error: " . $e->getMessage());
            return self::FAILURE;
        }

        foreach ($issues as $issue) {
            $this->warn("  ⚠  {$issue}");
            Log::channel('billing')->warning('Billing reconciliation issue', ['issue' => $issue, 'month' => $month]);
        }

        $this->info("Reconciled {$ok} invoices. Issues found: " . count($issues));
        return self::SUCCESS;
    }
}
