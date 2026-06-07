<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentGatewayController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    private function gatewayForSalon(): ?PaymentGateway
    {
        return PaymentGateway::withoutGlobalScopes()
            ->where('salon_id', $this->activeSalon()->id)
            ->where('provider', 'stripe')
            ->first();
    }

    // ── Settings page ─────────────────────────────────────────────────────────

    public function edit()
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon() ?? new PaymentGateway(['provider' => 'stripe', 'salon_id' => $salon->id]);

        return view('payments.gateway', compact('salon', 'gateway'));
    }

    public function update(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'publishable_key' => ['nullable', 'string', 'max:255'],
            'secret_key'      => ['nullable', 'string', 'max:255'],
            'webhook_secret'  => ['nullable', 'string', 'max:255'],
        ]);

        // Don't overwrite existing secret if the masked placeholder was submitted
        $existing = $this->gatewayForSalon();

        if ($existing) {
            if (empty($data['secret_key']) || $data['secret_key'] === '••••••••') {
                unset($data['secret_key']);
            }
            if (empty($data['webhook_secret']) || $data['webhook_secret'] === '••••••••') {
                unset($data['webhook_secret']);
            }
        }

        PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'provider' => 'stripe'],
            $data
        );

        return back()->with('success', 'Payment gateway settings saved.');
    }

    // ── Charge a client ───────────────────────────────────────────────────────

    /**
     * Show the charge form (used from POS / appointment checkout).
     */
    public function showCharge(Request $request)
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon();

        if (! $gateway?->isConfigured()) {
            return redirect()->route('payments.gateway')
                ->with('error', 'Configure your Stripe keys before taking payments.');
        }

        return view('payments.charge', [
            'salon'           => $salon,
            'publishable_key' => $gateway->publishable_key,
            'amount'          => $request->query('amount', ''),
            'description'     => $request->query('description', ''),
            'client_email'    => $request->query('email', ''),
        ]);
    }

    /**
     * Process a card charge using the tenant's own Stripe secret key.
     */
    public function charge(Request $request)
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon();

        if (! $gateway?->isConfigured()) {
            return back()->with('error', 'Payment gateway is not configured.');
        }

        $data = $request->validate([
            'amount'       => ['required', 'numeric', 'min:0.50'],
            'currency'     => ['required', 'string', 'size:3'],
            'description'  => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'payment_method_id' => ['required', 'string'],
        ]);

        try {
            $stripe = new StripeClient($gateway->secret_key);

            $amountInCents = (int) round($data['amount'] * 100);

            $intent = $stripe->paymentIntents->create([
                'amount'               => $amountInCents,
                'currency'             => strtolower($data['currency']),
                'payment_method'       => $data['payment_method_id'],
                'description'          => $data['description'] ?? 'Payment to ' . $salon->name,
                'receipt_email'        => $data['client_email'] ?? null,
                'confirm'              => true,
                'automatic_payment_methods' => [
                    'enabled'          => true,
                    'allow_redirects'  => 'never',
                ],
                'metadata' => [
                    'salon_id'   => $salon->id,
                    'salon_name' => $salon->name,
                ],
            ]);

            Log::info('[Payment] Charge succeeded', [
                'salon_id' => $salon->id,
                'intent'   => $intent->id,
                'amount'   => $data['amount'],
            ]);

            return back()->with('success', 'Payment of ' . $salon->currency . number_format($data['amount'], 2) . ' collected successfully.');

        } catch (ApiErrorException $e) {
            Log::warning('[Payment] Stripe charge failed', [
                'salon_id' => $salon->id,
                'error'    => $e->getMessage(),
            ]);

            return back()->with('error', 'Payment failed: ' . $e->getMessage());
        }
    }
}
