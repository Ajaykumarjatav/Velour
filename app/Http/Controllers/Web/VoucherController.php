<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $search = $request->get('search');
        $type   = $request->get('type');
        $status = $request->get('status', 'active');

        $query = Voucher::where('salon_id', $salon->id)
            ->with('client')
            ->latest();

        if ($search) {
            $query->where(fn($q) =>
                $q->where('code', 'like', "%$search%")
                  ->orWhereHas('client', fn($q2) =>
                      $q2->where('first_name', 'like', "%$search%")
                         ->orWhere('last_name', 'like', "%$search%")
                  )
            );
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($status === 'active') {
            $query->where('is_active', true)
                  ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()));
        } elseif ($status === 'expired') {
            $query->where('expires_at', '<', today());
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $vouchers = $query->paginate(25)->withQueryString();

        $stats = [
            'total'       => Voucher::where('salon_id', $salon->id)->count(),
            'active'      => Voucher::where('salon_id', $salon->id)->valid()->count(),
            'gift_cards'  => Voucher::where('salon_id', $salon->id)->where('type', 'gift_card')->count(),
            'total_value' => Voucher::where('salon_id', $salon->id)->valid()->sum('remaining_balance'),
        ];

        return view('vouchers.index', compact('salon', 'vouchers', 'stats', 'search', 'type', 'status'));
    }

    public function create()
    {
        $salon   = $this->salon();
        $clients = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('vouchers.create', compact('salon', 'clients'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'type'            => ['required', 'in:discount,gift_card,free_service,percentage'],
            'value'           => ['required', 'numeric', 'min:0.01'],
            'client_id'       => ['nullable', 'exists:clients,id'],
            'usage_limit'     => ['nullable', 'integer', 'min:1'],
            'expires_at'      => ['nullable', 'date', 'after:today'],
            'min_spend'       => ['nullable', 'numeric', 'min:0'],
            'code'            => ['nullable', 'string', 'max:50', 'unique:vouchers,code'],
        ]);

        $data['salon_id']          = $salon->id;
        $data['code']              = strtoupper($data['code'] ?? Str::random(8));
        $data['remaining_balance'] = $data['value'];
        $data['is_active']         = true;

        Voucher::create($data);

        return redirect()->route('vouchers.index')->with('success', 'Voucher created.');
    }

    public function show(Voucher $voucher)
    {
        abort_unless($voucher->salon_id === $this->salon()->id, 403);
        $voucher->load('client');

        return view('vouchers.show', compact('voucher'));
    }

    public function edit(Voucher $voucher)
    {
        abort_unless($voucher->salon_id === $this->salon()->id, 403);
        $salon   = $this->salon();
        $clients = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('vouchers.edit', compact('voucher', 'clients'));
    }

    public function update(Request $request, Voucher $voucher)
    {
        abort_unless($voucher->salon_id === $this->salon()->id, 403);

        $data = $request->validate([
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at'  => ['nullable', 'date'],
            'min_spend'   => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
            'client_id'   => ['nullable', 'exists:clients,id'],
        ]);

        $voucher->update($data);

        return redirect()->route('vouchers.show', $voucher)->with('success', 'Voucher updated.');
    }

    public function toggle(Voucher $voucher)
    {
        abort_unless($voucher->salon_id === $this->salon()->id, 403);
        $voucher->update(['is_active' => !$voucher->is_active]);

        return back()->with('success', $voucher->is_active ? 'Voucher activated.' : 'Voucher deactivated.');
    }

    public function destroy(Voucher $voucher)
    {
        abort_unless($voucher->salon_id === $this->salon()->id, 403);
        $voucher->delete();

        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted.');
    }
}
