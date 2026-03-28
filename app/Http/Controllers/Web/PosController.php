<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PosTransaction;
use App\Models\Client;
use App\Models\Service;
use App\Models\InventoryItem;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $search = $request->get('search');
        $from   = $request->get('from');
        $to     = $request->get('to');
        $method = $request->get('payment_method');

        $query = PosTransaction::where('salon_id', $salon->id)
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
        $todayRevenue  = PosTransaction::where('salon_id', $salon->id)->whereDate('created_at', today())->where('status','completed')->sum('total');
        $todayCount    = PosTransaction::where('salon_id', $salon->id)->whereDate('created_at', today())->count();

        return view('pos.index', compact('salon', 'transactions', 'search', 'from', 'to', 'method', 'todayRevenue', 'todayCount'));
    }

    public function create()
    {
        $salon    = $this->salon();
        $clients  = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id','first_name','last_name','phone']);
        $services = Service::where('salon_id', $salon->id)->active()->get(['id','name','price']);
        $products = InventoryItem::where('salon_id', $salon->id)->where('stock_quantity', '>', 0)->get(['id','name','retail_price as price','stock_quantity as quantity']);

        return view('pos.create', compact('salon', 'clients', 'services', 'products'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'client_id'       => ['nullable', 'exists:clients,id'],
            'items'           => ['required', 'array', 'min:1'],
            'items.*.type'    => ['required', 'in:service,product'],
            'items.*.id'      => ['required', 'integer'],
            'items.*.qty'     => ['required', 'integer', 'min:1'],
            'items.*.price'   => ['required', 'numeric', 'min:0'],
            'payment_method'  => ['required', 'in:cash,card,bank_transfer,voucher'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ]);

        $subtotal = collect($data['items'])->sum(fn($i) => $i['qty'] * $i['price']);
        $discount = $data['discount_amount'] ?? 0;
        $total    = max(0, $subtotal - $discount);

        DB::transaction(function () use ($data, $salon, $subtotal, $discount, $total) {
            $transaction = PosTransaction::create([
                'salon_id'        => $salon->id,
                'client_id'       => $data['client_id'] ?? null,
                'payment_method'  => $data['payment_method'],
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'total'           => $total,
                'status'          => 'completed',
                'notes'           => $data['notes'] ?? null,
                'completed_at'    => now(),
            ]);

            foreach ($data['items'] as $item) {
                $transaction->items()->create([
                    'item_type'  => $item['type'],
                    'item_id'    => $item['id'],
                    'quantity'   => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal'   => $item['qty'] * $item['price'],
                ]);
            }
        });

        return redirect()->route('pos.index')->with('success', 'Sale recorded successfully.');
    }

    public function show(PosTransaction $transaction)
    {
        abort_unless($transaction->salon_id === $this->salon()->id, 403);
        $transaction->load(['client', 'items']);

        return view('pos.show', compact('transaction'));
    }
}
