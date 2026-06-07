<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function create(Request $request, Salon $salon)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.5'],
            'currency' => ['nullable', 'string', 'size:3'],
            'customer_email' => ['nullable', 'email'],
        ]);

        $gateway = $salon->paymentGateway;
        if (! $gateway || ! $gateway->isStripe() || ! $gateway->secret_key) {
            abort(503, 'Payment gateway not configured for this salon.');
        }

        Stripe::setApiKey($gateway->secret_key);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $request->input('currency', 'usd'),
                    'product_data' => [
                        'name' => $salon->name . ' Payment',
                    ],
                    'unit_amount' => (int) round($request->input('amount') * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'customer_email' => $request->input('customer_email'),
            'success_url' => route('checkout.success', ['salon' => $salon->id]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', ['salon' => $salon->id]),
        ]);

        return redirect($session->url);
    }

    public function success(Request $request, Salon $salon)
    {
        return view('payments.success', compact('salon'));
    }

    public function cancel(Request $request, Salon $salon)
    {
        return view('payments.cancel', compact('salon'));
    }
}
