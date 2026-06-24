<?php

namespace App\Billing;

use Illuminate\Support\Collection;

/**
 * Plan
 *
 * An immutable value object wrapping a plan entry from config/billing.php.
 *
 * Usage:
 *   $plan = Plan::find('pro');
 *   $plan->name              → "Pro"
 *   $plan->priceMonthly      → 49
 *   $plan->priceYearly       → 470
 *   $plan->stripePriceId('monthly') → "price_1Oabc..."
 *   $plan->allows('marketing')      → true
 *   $plan->limit('staff')           → 15
 *   $plan->isUnlimited('staff')     → false
 *
 *   Plan::all()   → Collection of all Plan objects
 *   Plan::find()  → Single Plan or null
 */
class Plan
{
    public readonly string  $key;
    public readonly string  $name;
    public readonly string  $tagline;
    public readonly int     $priceMonthly;
    public readonly int     $priceYearly;
    public readonly ?string $cashfreePlanMonthly;
    public readonly ?string $cashfreePlanYearly;
    public readonly ?string $stripePriceMonthly;
    public readonly ?string $stripePriceYearly;
    public readonly int     $trialDays;
    public readonly bool    $popular;
    public readonly string  $color;
    public readonly array   $features;
    public readonly array   $limits;

    private function __construct(string $key, array $config)
    {
        $this->key                = $key;
        $this->name               = $config['name'];
        $this->tagline            = $config['tagline'];
        $this->priceMonthly       = $config['price_monthly'];
        $this->priceYearly        = $config['price_yearly'];
        $this->cashfreePlanMonthly = $config['cashfree_monthly'] ?? null;
        $this->cashfreePlanYearly  = $config['cashfree_yearly'] ?? null;
        $this->stripePriceMonthly  = $config['stripe_monthly'] ?? null;
        $this->stripePriceYearly   = $config['stripe_yearly']  ?? null;
        $this->trialDays          = $config['trial_days'] ?? 0;
        $this->popular            = $config['popular'] ?? false;
        $this->color              = $config['color'] ?? 'gray';
        $this->features           = $config['features'] ?? [];
        $this->limits             = $config['limits'] ?? [];
    }

    // ── Factory ───────────────────────────────────────────────────────────────

    public static function find(string $key): ?static
    {
        $config = config("billing.plans.{$key}");
        return $config ? new static($key, $config) : null;
    }

    public static function findOrFail(string $key): static
    {
        $plan = static::find($key);
        if (! $plan) {
            throw new \InvalidArgumentException("Unknown plan: {$key}");
        }
        return $plan;
    }

    public static function all(): Collection
    {
        return collect(config('billing.plans', []))
            ->map(fn ($config, $key) => new static($key, $config));
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(config('billing.plans', []));
    }

    public static function labelFor(?string $key): string
    {
        $resolved = $key ?: (string) config('billing.default_plan', 'trial');

        return static::find($resolved)?->name ?? ucfirst($resolved);
    }

    public static function validationRule(): string
    {
        return 'in:'.implode(',', static::keys());
    }

    public static function paidValidationRule(): string
    {
        $keys = static::all()
            ->filter(fn (Plan $p) => $p->isPaid())
            ->pluck('key')
            ->all();

        return 'in:'.implode(',', $keys);
    }

    // ── Cashfree plan IDs ─────────────────────────────────────────────────────

    /**
     * Cashfree plan_id for the given billing interval.
     */
    public function cashfreePlanId(string $interval = 'monthly'): string
    {
        $configured = $interval === 'yearly' ? $this->cashfreePlanYearly : $this->cashfreePlanMonthly;

        return $configured ?: ('velor_'.$this->key.'_'.$interval);
    }

    public function hasCashfreePlan(string $interval = 'monthly'): bool
    {
        return $this->isPaid();
    }

    // ── Stripe Price IDs (legacy) ─────────────────────────────────────────────

    /**
     * Get the Stripe Price ID for the given billing interval.
     *
     * @param 'monthly'|'yearly' $interval
     */
    public function stripePriceId(string $interval = 'monthly'): ?string
    {
        return match ($interval) {
            'yearly'  => $this->stripePriceYearly,
            default   => $this->stripePriceMonthly,
        };
    }

    // ── Feature checks ────────────────────────────────────────────────────────

    /**
     * Whether this plan includes the given feature flag.
     */
    public function allows(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    // ── Limit checks ─────────────────────────────────────────────────────────

    /**
     * Get the numeric limit for a resource.
     * Returns -1 for unlimited.
     */
    public function limit(string $resource): int
    {
        return $this->limits[$resource] ?? 0;
    }

    /**
     * Is a given resource unlimited on this plan?
     */
    public function isUnlimited(string $resource): bool
    {
        return ($this->limits[$resource] ?? 0) === -1;
    }

    // ── Pricing helpers ───────────────────────────────────────────────────────

    /**
     * Monthly equivalent price when billed yearly (for display).
     */
    public function monthlyEquivalentYearly(): float
    {
        return round($this->priceYearly / 12, 2);
    }

    /**
     * Yearly saving compared to 12 × monthly price.
     */
    public function yearlySaving(): int
    {
        return ($this->priceMonthly * 12) - $this->priceYearly;
    }

    // ── Comparisons ───────────────────────────────────────────────────────────

    /**
     * Is this plan an upgrade from the given plan?
     */
    public function isUpgradeFrom(string $otherKey): bool
    {
        $order = ['trial', 'standard', 'premium'];
        return array_search($this->key, $order, true) > array_search($otherKey, $order, true);
    }

    public function isTrial(): bool
    {
        return $this->key === 'trial';
    }

    public function isFree(): bool
    {
        return $this->isTrial() || $this->priceMonthly === 0;
    }

    public function isPaid(): bool
    {
        return ! $this->isFree();
    }
}
