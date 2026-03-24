<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Client;
use App\Models\Appointment;
use App\Models\Voucher;
use App\Models\Invoice;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\SignatureVerificationException;

class PosController extends Controller
{
    public function __construct(private PosService $posService) {}

    /* ── GET /pos ───────────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        $transactions = PosTransaction::with(['client', 'staff', 'items'])
            ->where('salon_id', $salonId)
            ->when($request->from && $request->to, fn($q) =>
                $q->whereBetween('completed_at', [$request->from, $request->to . ' 23:59:59'])
            )
            ->when($request->status,         fn($q) => $q->where('status', $request->status))
            ->when($request->payment_method, fn($q) => $q->where('payment_method', $request->payment_method))
            ->when($request->client_id,      fn($q) => $q->where('client_id', $request->client_id))
            ->when($request->staff_id,       fn($q) => $q->where('staff_id', $request->staff_id))
            ->orderByDesc('completed_at')
            ->paginate($request->per_page ?? 50);

        return response()->json($transactions);
    }

    /* ── POST /pos ──────────────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id'        => 'nullable|integer',
            'staff_id'         => 'required|integer',
            'appointment_id'   => 'nullable|integer',
            'items'            => 'required|array|min:1',
            'items.*.name'     => 'required|string|max:255',
            'items.*.type'     => 'required|in:service,product,voucher,tip',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price'=> 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.staff_id' => 'nullable|integer',
            'discount_code'    => 'nullable|string|max:50',
            'tip_amount'       => 'nullable|numeric|min:0',
            'payment_method'   => 'required|in:cash,card,split,voucher,account',
            'amount_tendered'  => 'nullable|numeric|min:0',
            'stripe_payment_intent_id' => 'nullable|string',
            'notes'            => 'nullable|string|max:500',
        ]);

        $salonId = $request->attributes->get('salon_id');

        try {
            $transaction = $this->posService->process($salonId, $data);

            return response()->json([
                'message'     => 'Transaction completed.',
                'transaction' => $transaction->load(['client', 'staff', 'items']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /* ── GET /pos/{id} ──────────────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $transaction = PosTransaction::with(['client', 'staff', 'items', 'appointment'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        return response()->json($transaction);
    }

    /* ── PUT /pos/{id} ──────────────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        // POS transactions are mostly immutable; only notes editable
        $data = $request->validate(['notes' => 'nullable|string|max:500']);
        $tx = PosTransaction::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $tx->update($data);
        return response()->json(['message' => 'Updated.', 'transaction' => $tx]);
    }

    /* ── DELETE /pos/{id} ───────────────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tx = PosTransaction::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        if ($tx->status === 'completed') {
            return response()->json(['message' => 'Completed transactions cannot be deleted. Use void instead.'], 422);
        }
        $tx->delete();
        return response()->json(['message' => 'Transaction deleted.']);
    }

    /* ── POST /pos/{id}/refund ──────────────────────────────────────────── */
    public function refund(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        $tx = PosTransaction::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (! in_array($tx->status, ['completed'])) {
            return response()->json(['message' => 'Only completed transactions can be refunded.'], 422);
        }

        $amount = $data['amount'] ?? $tx->total;

        try {
            $result = $this->posService->refund($tx, $amount, $data['reason'] ?? null);
            return response()->json(['message' => "Refund of \${$amount} processed.", 'transaction' => $result]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /* ── POST /pos/{id}/void ────────────────────────────────────────────── */
    public function void(Request $request, int $id): JsonResponse
    {
        $tx = PosTransaction::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if ($tx->status !== 'completed') {
            return response()->json(['message' => 'Only completed transactions can be voided.'], 422);
        }

        $tx->update(['status' => 'voided']);

        if ($tx->stripe_payment_intent_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                PaymentIntent::retrieve($tx->stripe_payment_intent_id)->cancel();
            } catch (\Exception) {}
        }

        return response()->json(['message' => 'Transaction voided.', 'transaction' => $tx]);
    }

    /* ── GET /pos/{id}/receipt ──────────────────────────────────────────── */
    public function receipt(Request $request, int $id): JsonResponse
    {
        $tx = PosTransaction::with(['client', 'staff', 'items', 'appointment'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        $salon = $tx->salon;

        return response()->json([
            'receipt' => [
                'salon'        => $salon->only(['name','address_line1','city','phone','email']),
                'transaction'  => $tx,
                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /* ── POST /pos/stripe/intent ────────────────────────────────────────── */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'   => 'required|integer|min:50',
            'currency' => 'nullable|string|size:3',
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount'              => $data['amount'],
            'currency'            => strtolower($data['currency'] ?? 'gbp'),
            'payment_method_types'=> ['card'],
            'metadata'            => ['salon_id' => $request->attributes->get('salon_id')],
        ]);

        return response()->json([
            'client_secret'      => $intent->client_secret,
            'payment_intent_id'  => $intent->id,
        ]);
    }

    /* ── POST /webhooks/stripe ──────────────────────────────────────────── */
    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded' => $this->posService->handlePaymentSuccess($event->data->object),
            'payment_intent.payment_failed' => $this->posService->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    /* ── GET /pos/summary/today ─────────────────────────────────────────── */
    public function todaySummary(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $today   = now()->toDateString();

        $transactions = PosTransaction::where('salon_id', $salonId)
            ->whereDate('completed_at', $today)
            ->where('status', 'completed')
            ->get();

        return response()->json([
            'date'               => $today,
            'transaction_count'  => $transactions->count(),
            'total_revenue'      => $transactions->sum('total'),
            'total_tips'         => $transactions->sum('tip_amount'),
            'cash_total'         => $transactions->where('payment_method', 'cash')->sum('total'),
            'card_total'         => $transactions->where('payment_method', 'card')->sum('total'),
            'avg_transaction'    => $transactions->count() > 0
                                    ? round($transactions->sum('total') / $transactions->count(), 2)
                                    : 0,
        ]);
    }

    /* ── POST /vouchers/validate ────────────────────────────────────────── */
    public function validateVoucher(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'  => 'required|string|max:50',
            'spend' => 'required|numeric|min:0',
        ]);

        $voucher = Voucher::where('salon_id', $request->attributes->get('salon_id'))
            ->where('code', strtoupper($data['code']))
            ->first();

        if (! $voucher) {
            return response()->json(['message' => 'Voucher not found.'], 404);
        }

        if (! $voucher->is_usable) {
            return response()->json(['message' => 'This voucher is expired or has been fully used.'], 422);
        }

        if ($data['spend'] < $voucher->min_spend) {
            return response()->json([
                'message' => "Minimum spend of \${$voucher->min_spend} required.",
            ], 422);
        }

        $discount = match ($voucher->type) {
            'percentage' => round($data['spend'] * ($voucher->value / 100), 2),
            'fixed'      => min($voucher->value, $data['spend']),
            'gift_card'  => min($voucher->remaining_balance, $data['spend']),
            default      => 0,
        };

        return response()->json([
            'valid'        => true,
            'voucher'      => $voucher,
            'discount'     => $discount,
            'new_total'    => max(0, $data['spend'] - $discount),
        ]);
    }

    /* ── GET /pos/export ────────────────────────────────────────────────── */
    public function export(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $from    = $request->from ?? now()->startOfMonth()->toDateString();
        $to      = $request->to   ?? now()->endOfMonth()->toDateString();

        $transactions = PosTransaction::with(['client', 'staff', 'items'])
            ->where('salon_id', $salonId)
            ->whereBetween('completed_at', [$from, $to . ' 23:59:59'])
            ->orderByDesc('completed_at')
            ->get();

        return response()->json(['count' => $transactions->count(), 'data' => $transactions]);
    }
}
