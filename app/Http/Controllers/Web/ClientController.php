<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $search = $request->get('search');
        $sort   = $request->get('sort', 'created_at');
        $dir    = $request->get('dir', 'desc');

        $query = Client::where('salon_id', $salon->id);

        $loyaltyFilterTier = null;
        if ($request->filled('loyalty_tier_id')) {
            $loyaltyFilterTier = LoyaltyTier::where('salon_id', $salon->id)
                ->where('id', $request->integer('loyalty_tier_id'))
                ->first();
            if ($loyaltyFilterTier) {
                $query->where('loyalty_tier_id', $loyaltyFilterTier->id);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',  'like', "%$search%")
                  ->orWhere('email',      'like', "%$search%")
                  ->orWhere('phone',      'like', "%$search%");
            });
        }

        $clients = $query->orderBy($sort, $dir)->paginate(25)->withQueryString();
        $clientIds = $clients->getCollection()->pluck('id')->all();
        $appointmentsByClient = collect();
        if ($clientIds !== []) {
            $appointmentsByClient = Appointment::query()
                ->where('salon_id', $salon->id)
                ->whereIn('client_id', $clientIds)
                ->with([
                    'staff:id,first_name,last_name',
                    'services:id,appointment_id,service_id,service_name',
                ])
                ->orderByDesc('starts_at')
                ->get(['id', 'client_id', 'starts_at', 'total_price', 'status', 'staff_id'])
                ->groupBy('client_id')
                ->map(fn ($rows) => $rows->take(5)->values());
        }

        $clientTotal = Client::where('salon_id', $salon->id)->count();
        $loyaltyTiers = LoyaltyTier::query()
            ->where('salon_id', $salon->id)
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);

        return view('clients.index', compact(
            'salon',
            'clients',
            'search',
            'sort',
            'dir',
            'clientTotal',
            'loyaltyFilterTier',
            'appointmentsByClient',
            'loyaltyTiers'
        ));
    }

    /**
     * CSV export for the current salon (matches columns useful for re-import).
     */
    public function export(): StreamedResponse
    {
        Gate::authorize('export', Client::class);

        $salon = $this->salon();
        $slug   = $salon->slug ?: 'salon';
        $name   = 'clients-' . preg_replace('/[^a-z0-9_-]+/i', '-', $slug) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($salon) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['first_name', 'last_name', 'email', 'phone', 'marketing_consent']);

            Client::query()
                ->where('salon_id', $salon->id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->chunk(200, function ($chunk) use ($out): void {
                    foreach ($chunk as $c) {
                        fputcsv($out, [
                            $c->first_name,
                            $c->last_name,
                            $c->email,
                            $c->phone,
                            $c->marketing_consent ? '1' : '0',
                        ]);
                    }
                });

            fclose($out);
        }, $name, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Import clients from CSV (first row = headers: first_name, last_name, email, phone, marketing_consent).
     */
    public function import(Request $request)
    {
        Gate::authorize('create', Client::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $salon = $this->salon();
        $path  = $request->file('file')->getRealPath();
        if ($path === false || ! is_readable($path)) {
            return redirect()->route('clients.index')->with('error', 'Could not read the uploaded file.');
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return redirect()->route('clients.index')->with('error', 'Could not read the uploaded file.');
        }

        $headerLine = fgetcsv($handle);
        if ($headerLine === false) {
            fclose($handle);

            return redirect()->route('clients.index')->with('error', 'The CSV file is empty.');
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headerLine);
        $col     = array_flip($headers);

        foreach (['first_name', 'last_name'] as $required) {
            if (! isset($col[$required])) {
                fclose($handle);

                return redirect()->route('clients.index')->with(
                    'error',
                    'CSV must include columns: first_name, last_name (optional: email, phone, marketing_consent).'
                );
            }
        }

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $get = function (string $key) use ($row, $col): string {
                if (! isset($col[$key])) {
                    return '';
                }
                $i = $col[$key];

                return isset($row[$i]) ? trim((string) $row[$i]) : '';
            };

            $first = $get('first_name');
            $last  = $get('last_name');
            if ($first === '' || $last === '') {
                $skipped++;

                continue;
            }

            $email = $get('email') ?: null;
            if ($email !== null && $email !== '') {
                $v = Validator::make(['email' => $email], ['email' => ['email', 'max:150']]);
                if ($v->fails()) {
                    $skipped++;

                    continue;
                }
            } else {
                $email = null;
            }

            $phone = $get('phone') ?: null;
            if ($phone !== null && strlen($phone) > 20) {
                $phone = substr($phone, 0, 20);
            }

            $marketingRaw = strtolower($get('marketing_consent'));
            $marketing    = in_array($marketingRaw, ['1', 'true', 'yes', 'y', 'on'], true);

            $duplicate = false;
            if ($email) {
                $duplicate = Client::query()->where('salon_id', $salon->id)->where('email', $email)->exists();
            } elseif ($phone) {
                $duplicate = Client::query()
                    ->where('salon_id', $salon->id)
                    ->where('first_name', $first)
                    ->where('last_name', $last)
                    ->where('phone', $phone)
                    ->exists();
            }

            if ($duplicate) {
                $skipped++;

                continue;
            }

            Client::create([
                'salon_id'            => $salon->id,
                'first_name'          => $first,
                'last_name'           => $last,
                'email'               => $email,
                'phone'               => $phone,
                'marketing_consent'   => $marketing,
            ]);
            $imported++;
        }

        fclose($handle);

        $msg = "Imported {$imported} client" . ($imported === 1 ? '' : 's') . '.';
        if ($skipped > 0) {
            $msg .= " Skipped {$skipped} row(s) (duplicates, invalid, or incomplete).";
        }

        return redirect()->route('clients.index')->with('success', $msg);
    }

    private function csvRowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    public function create()
    {
        $salon         = $this->salon();
        $loyaltyTiers  = LoyaltyTier::where('salon_id', $salon->id)->where('is_active', true)->orderBy('sort_order')->get();

        return view('clients.create', compact('salon', 'loyaltyTiers'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['nullable', 'email', 'max:150'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'date_of_birth'=> ['nullable', 'date'],
            'gender'       => ['nullable', 'in:female,male,non_binary,prefer_not_to_say'],
            'address'      => ['nullable', 'string', 'max:500'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'marketing_consent' => ['boolean'],
            'loyalty_tier_id'   => ['nullable', 'integer', 'exists:loyalty_tiers,id'],
        ]);

        if (! empty($data['loyalty_tier_id'])) {
            abort_unless(
                LoyaltyTier::where('id', $data['loyalty_tier_id'])->where('salon_id', $salon->id)->exists(),
                422
            );
        }

        $data['salon_id'] = $salon->id;
        $client = Client::create($data);
        app(NotificationService::class)->notifyTenantNewClientRegistered($salon, $client);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function show(Client $client)
    {
        $this->authorise($client);
        $client->load('loyaltyTier');

        $appointments = Appointment::where('client_id', $client->id)
            ->with(['staff', 'services.service'])
            ->latest('starts_at')
            ->paginate(10);

        $totalSpent = $client->transactions()->where('status', 'completed')->sum('total');
        $visitCount = $client->appointments()->where('status', 'completed')->count();
        $lastVisit  = $client->appointments()->where('status', 'completed')->latest('starts_at')->first();

        return view('clients.show', compact('client', 'appointments', 'totalSpent', 'visitCount', 'lastVisit'));
    }

    public function edit(Client $client)
    {
        $this->authorise($client);
        $loyaltyTiers = LoyaltyTier::where('salon_id', $this->salon()->id)->where('is_active', true)->orderBy('sort_order')->get();

        return view('clients.edit', compact('client', 'loyaltyTiers'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorise($client);

        $data = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['nullable', 'email', 'max:150'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'date_of_birth'=> ['nullable', 'date'],
            'gender'       => ['nullable', 'in:female,male,non_binary,prefer_not_to_say'],
            'address'      => ['nullable', 'string', 'max:500'],
            'notes'        => ['nullable', 'string', 'max:2000'],
            'marketing_consent' => ['boolean'],
        ]);

        if ($request->has('loyalty_tier_id')) {
            $request->validate(['loyalty_tier_id' => ['nullable', 'integer', 'exists:loyalty_tiers,id']]);
            $tid = $request->input('loyalty_tier_id');
            if ($tid) {
                abort_unless(
                    LoyaltyTier::where('id', $tid)->where('salon_id', $this->salon()->id)->exists(),
                    422
                );
            }
            $data['loyalty_tier_id'] = $tid ?: null;
        }

        $client->update($data);

        if ($request->boolean('inline_edit')) {
            return redirect()->route('clients.index')->with('success', 'Client updated.');
        }

        return redirect()->route('clients.show', $client)->with('success', 'Client updated.');
    }

    public function destroy(Client $client)
    {
        $this->authorise($client);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }

    private function authorise(Client $client): void
    {
        abort_unless($client->salon_id === $this->salon()->id, 403);
    }
}
