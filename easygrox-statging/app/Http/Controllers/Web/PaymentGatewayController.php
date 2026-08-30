<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\PaymentGateway;
use App\Services\Billing\CashfreeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            ->where('provider', 'cashfree')
            ->first();
    }

    public function edit()
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon() ?? new PaymentGateway(['provider' => 'cashfree', 'salon_id' => $salon->id]);

        return view('payments.gateway', compact('salon', 'gateway'));
    }

    public function update(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'publishable_key' => ['required', 'string', 'max:255'],
            'secret_key'      => ['nullable', 'string', 'max:255'],
            'webhook_secret'  => ['nullable', 'string', 'max:255'],
        ]);

        $existing = $this->gatewayForSalon();

        if ($existing) {
            if (empty($data['secret_key']) || $data['secret_key'] === '••••••••') {
                unset($data['secret_key']);
            }
            if (empty($data['webhook_secret']) || $data['webhook_secret'] === '••••••••') {
                unset($data['webhook_secret']);
            }
        } else {
            $request->validate(['secret_key' => ['required', 'string', 'max:255']]);
        }

        PaymentGateway::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'provider' => 'cashfree'],
            $data
        );

        return back()->with('success', 'Cashfree settings saved.');
    }

    public function showCharge(Request $request)
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon();

        if (! $gateway?->isConfigured()) {
            return redirect()->route('payments.gateway')
                ->with('error', 'Configure your Cashfree keys before taking payments.');
        }

        return view('payments.charge', [
            'salon'        => $salon,
            'sdkMode'      => config('cashfree.sdk_mode', 'sandbox'),
            'amount'       => $request->query('amount', ''),
            'description'  => $request->query('description', ''),
            'client_email' => $request->query('email', ''),
        ]);
    }

    public function charge(Request $request)
    {
        $salon   = $this->salon();
        $gateway = $this->gatewayForSalon();

        if (! $gateway?->isConfigured()) {
            return back()->with('error', 'Payment gateway is not configured.');
        }

        $data = $request->validate([
            'amount'       => ['required', 'numeric', 'min:1'],
            'currency'     => ['required', 'string', 'size:3'],
            'description'  => ['nullable', 'string', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'client_phone' => ['nullable', 'string', 'max:20'],
            'client_name'  => ['nullable', 'string', 'max:100'],
        ]);

        $cashfree = CashfreeService::forSalonGateway(
            (string) $gateway->publishable_key,
            (string) $gateway->secret_key,
        );

        $orderId = 'salon_'.$salon->id.'_'.Str::lower(Str::random(8));
        $phone   = preg_replace('/\D+/', '', (string) ($data['client_phone'] ?? ''));
        if (strlen($phone) !== 10) {
            $phone = '9999999999';
        }

        try {
            $order = $cashfree->createOrder(
                $orderId,
                (float) $data['amount'],
                $data['currency'],
                [
                    'customer_id'    => 'client_'.Str::lower(Str::random(6)),
                    'customer_name'  => $data['client_name'] ?: 'Walk-in client',
                    'customer_email' => $data['client_email'] ?: 'client@example.com',
                    'customer_phone' => $phone,
                ],
                route('payments.charge.return', ['order_id' => $orderId]),
            );
        } catch (\Throwable $e) {
            Log::warning('[Payment] Cashfree order failed', ['salon_id' => $salon->id, 'error' => $e->getMessage()]);

            return back()->with('error', 'Could not create payment: '.$e->getMessage());
        }

        $sessionId = (string) ($order['payment_session_id'] ?? '');

        if ($sessionId === '') {
            return back()->with('error', 'Cashfree did not return a payment session.');
        }

        return view('payments.cashfree-checkout', [
            'sessionId' => $sessionId,
            'mode'      => $cashfree->sdkMode(),
            'amount'    => $data['amount'],
            'currency'  => strtoupper($data['currency']),
            'salon'     => $salon,
        ]);
    }

    public function chargeReturn(Request $request)
    {
        return redirect()->route('payments.charge')
            ->with('success', 'Payment completed. Check your Cashfree dashboard for confirmation.');
    }
}
