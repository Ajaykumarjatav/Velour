<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',  'like', "%$search%")
                  ->orWhere('email',      'like', "%$search%")
                  ->orWhere('phone',      'like', "%$search%");
            });
        }

        $clients = $query->orderBy($sort, $dir)->paginate(25)->withQueryString();

        return view('clients.index', compact('salon', 'clients', 'search', 'sort', 'dir'));
    }

    public function create()
    {
        $salon = $this->salon();
        return view('clients.create', compact('salon'));
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
        ]);

        $data['salon_id'] = $salon->id;
        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    public function show(Client $client)
    {
        $this->authorise($client);

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
        return view('clients.edit', compact('client'));
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

        $client->update($data);

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
