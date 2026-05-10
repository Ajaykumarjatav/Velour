<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\PosTransaction;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\Service;
use App\Models\InventoryItem;
use App\Models\Appointment;
use App\Models\Staff;
use App\Mail\PosTransactionInvoiceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    use ResolvesActiveSalon;

    public function index(Request $request)
    {
        $salon  = $this->activeSalon();
        $search = $request->get('search');
        $from   = $request->get('from');
        $to     = $request->get('to');
        $method = $request->get('payment_method');

        $query = PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->with('client')
            ->latest();

        if ($search) {
            $query->where(fn($q) =>
                $q->where('reference', 'like', "%$search%")
                  ->orWhereHas('client', fn($q2) =>
                      $q2->where('first_name', 'like', "%$search%")
                         ->orWhere('last_name',  'like', "%$search%")
                  )
            );
        }

        if ($from) { $query->whereDate('created_at', '>=', $from); }
        if ($to)   { $query->whereDate('created_at', '<=', $to); }
        if ($method){ $query->where('payment_method', $method); }

        $transactions = $query->paginate(25)->withQueryString();

        // Today's summary
        $todayRevenue  = PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)->whereDate('created_at', today())->where('status','completed')->sum('total');
        $todayCount    = PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)->whereDate('created_at', today())->count();

        return view('pos.index', compact('salon', 'transactions', 'search', 'from', 'to', 'method', 'todayRevenue', 'todayCount'));
    }

    public function create()
    {
        $salon    = $this->activeSalon();
        $clients  = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(50)
            ->get(['id', 'first_name', 'last_name', 'phone']);
        $services = Service::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->active()
            ->with('category:id,name')
            ->orderBy('sort_order')
            ->get();
        $products = InventoryItem::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->where('stock_quantity', '>', 0)
            ->get(['id', 'name', 'retail_price', 'stock_quantity']);

        // Group services by category for the filter tabs
        $categories = $services->pluck('category.name', 'category_id')
            ->filter()
            ->unique()
            ->values();

        // Recent transactions for the bottom panel
        $recentTransactions = PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->with('client')
            ->latest()
            ->limit(5)
            ->get();

        $clientQuickCreateLoyaltyTiers = LoyaltyTier::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return view('pos.create', compact('salon', 'clients', 'services', 'products', 'categories', 'recentTransactions', 'clientQuickCreateLoyaltyTiers'));
    }

    public function store(Request $request)
    {
        $salon = $this->activeSalon();
        $user = Auth::user();

        $data = $request->validate([
            'client_id'       => ['nullable', 'exists:clients,id'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.type'    => ['required', 'in:service,product'],
            'items.*.id'      => ['required', 'integer'],
            'items.*.qty'     => ['required', 'integer', 'min:1'],
            'items.*.price'   => ['required', 'numeric', 'min:0'],
            'items.*.name'    => ['required', 'string', 'max:200'],
            'payment_method'  => ['required', 'in:cash,card,bank_transfer,voucher'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_mode'        => ['nullable', 'in:excluded,included'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        if (! empty($data['client_id'])) {
            $clientOk = Client::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->whereKey($data['client_id'])
                ->exists();
            if (! $clientOk) {
                throw ValidationException::withMessages([
                    'client_id' => __('The selected client is not valid for this salon.'),
                ]);
            }
        }

        // Catalogue prices only — do not trust browser-submitted line prices.
        $resolvedItems = [];
        foreach ($data['items'] as $idx => $line) {
            if ($line['type'] === 'service') {
                $svc = Service::withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->whereKey($line['id'])
                    ->first();
                if (! $svc || $svc->status !== 'active') {
                    throw ValidationException::withMessages([
                        "items.{$idx}.id" => __('One or more services are missing or inactive. Refresh and try again.'),
                    ]);
                }
                $resolvedItems[] = [
                    'type' => 'service',
                    'id' => (int) $svc->id,
                    'name' => $svc->name,
                    'qty' => (int) $line['qty'],
                    'price' => (float) $svc->price,
                ];
            } else {
                $prod = InventoryItem::withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->whereKey($line['id'])
                    ->first();
                if (! $prod || ! $prod->is_active) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.id" => __('One or more products are missing or inactive.'),
                    ]);
                }
                if ((int) $prod->stock_quantity < (int) $line['qty']) {
                    throw ValidationException::withMessages([
                        "items.{$idx}.qty" => __('Not enough stock for :name.', ['name' => $prod->name]),
                    ]);
                }
                $resolvedItems[] = [
                    'type' => 'product',
                    'id' => (int) $prod->id,
                    'name' => $prod->name,
                    'qty' => (int) $line['qty'],
                    'price' => (float) $prod->retail_price,
                ];
            }
        }

        $subtotal = collect($resolvedItems)->sum(fn ($i) => $i['qty'] * $i['price']);
        $discount = $data['discount_amount'] ?? 0;
        $taxRate  = $data['tax_rate'] ?? 18;
        $taxMode  = $data['tax_mode'] ?? 'excluded';
        $gross    = max(0, $subtotal - $discount);
        if ($taxMode === 'included') {
            $taxable = round($gross / (1 + ($taxRate / 100)), 2);
            $tax = round($gross - $taxable, 2);
            $total = $gross;
        } else {
            $taxable = $gross;
            $tax = round($taxable * ($taxRate / 100), 2);
            $total = $taxable + $tax;
        }
        $staffId = $user?->dashboardScopedStaffId();
        if (! $staffId) {
            $staffId = Staff::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('user_id', (int) $user->id)
                ->where('is_active', true)
                ->value('id');
        }
        if (! $staffId) {
            $staffId = Staff::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
        }
        if (! $staffId) {
            return back()->withErrors(['status' => 'No active staff member found for this sale.'])->withInput();
        }

        $paymentMethod = match ($data['payment_method']) {
            'bank_transfer' => 'account',
            default => $data['payment_method'],
        };

        $transaction = DB::transaction(function () use ($resolvedItems, $salon, $subtotal, $discount, $tax, $total, $staffId, $paymentMethod, $data) {
            $tx = PosTransaction::create([
                'salon_id'        => $salon->id,
                'client_id'       => $data['client_id'] ?? null,
                'staff_id'        => (int) $staffId,
                'payment_method'  => $paymentMethod,
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'tax_amount'      => $tax,
                'total'           => $total,
                'status'          => 'completed',
                'notes'           => $data['notes'] ?? null,
                'completed_at'    => now(),
            ]);

            foreach ($resolvedItems as $item) {
                $itemableClass = $item['type'] === 'service' ? Service::class : InventoryItem::class;
                $tx->items()->create([
                    'itemable_id'   => $item['id'],
                    'itemable_type' => $itemableClass,
                    'name'          => $item['name'],
                    'type'          => $item['type'],
                    'quantity'      => $item['qty'],
                    'unit_price'    => $item['price'],
                    'total'         => $item['qty'] * $item['price'],
                ]);

                if ($item['type'] === 'product') {
                    $row = InventoryItem::withoutGlobalScopes()
                        ->where('salon_id', $salon->id)
                        ->whereKey($item['id'])
                        ->lockForUpdate()
                        ->first();
                    if ($row) {
                        $row->decrement('stock_quantity', $item['qty']);
                    }
                }
            }

            if ($tx->client_id) {
                Client::withoutGlobalScopes()
                    ->whereKey($tx->client_id)
                    ->increment('total_spent', $tx->total);
            }

            return $tx;
        });

        if ($transaction && $transaction->client_id) {
            $transaction->load(['client', 'items', 'salon']);
            $recipient = $transaction->client?->email;
            if (is_string($recipient) && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($recipient)->send(new PosTransactionInvoiceMail($transaction));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->route('pos.index')->with('success', 'Sale recorded successfully.');
    }

    public function show(PosTransaction $transaction)
    {
        $this->authorize('view', $transaction);

        $transaction->load(['client', 'items']);

        return view('pos.show', compact('transaction'));
    }
}
