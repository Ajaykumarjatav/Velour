<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $search = $request->get('search');
        $status = $request->get('status');
        $date   = $request->get('date');
        $staffId= $request->get('staff_id');

        $query = Appointment::where('salon_id', $salon->id)
            ->with(['client', 'staff', 'services.service'])
            ->latest('starts_at');

        if ($search) {
            $query->whereHas('client', fn($q) =>
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',  'like', "%$search%")
            )->orWhere('reference', 'like', "%$search%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('starts_at', $date);
        }

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        $appointments = $query->paginate(20)->withQueryString();
        $staff        = Staff::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->withName()
            ->get();

        return view('appointments.index', compact('salon', 'appointments', 'staff', 'search', 'status', 'date', 'staffId'));
    }

    public function create()
    {
        $salon    = $this->salon();
        $clients  = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id','first_name','last_name','phone']);
        $staff    = Staff::where('salon_id', $salon->id)->where('is_active', true)
            ->withName()
            ->get();
        $services = Service::where('salon_id', $salon->id)->active()->get(['id','name','duration_minutes','price']);

        return view('appointments.create', compact('salon', 'clients', 'staff', 'services'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'staff_id'       => ['required', 'exists:staff,id'],
            'starts_at'      => ['required', 'date'],
            'services'       => ['required', 'array', 'min:1'],
            'services.*'     => ['exists:services,id'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'client_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $services = Service::whereIn('id', $data['services'])->get();
        $duration = $services->sum('duration_minutes');
        $total    = $services->sum('price');
        $endsAt   = \Carbon\Carbon::parse($data['starts_at'])->addMinutes($duration);

        DB::transaction(function () use ($data, $salon, $duration, $total, $endsAt, $services) {
            $appointment = Appointment::create([
                'salon_id'       => $salon->id,
                'client_id'      => $data['client_id'],
                'staff_id'       => $data['staff_id'],
                'starts_at'      => $data['starts_at'],
                'ends_at'        => $endsAt,
                'duration_minutes' => $duration,
                'total_price'    => $total,
                'status'         => 'confirmed',
                'source'         => 'walk_in',
                'internal_notes' => $data['internal_notes'] ?? null,
                'client_notes'   => $data['client_notes'] ?? null,
            ]);

            foreach ($services as $svc) {
                $appointment->services()->create([
                    'service_id'       => $svc->id,
                    'staff_id'         => $data['staff_id'],
                    'price'            => $svc->price,
                    'duration_minutes' => $svc->duration_minutes,
                ]);
            }
        });

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->load(['client', 'staff', 'services.service', 'transaction', 'review']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $this->authorise($appointment);
        $salon    = $this->salon();
        $clients  = Client::where('salon_id', $salon->id)->orderBy('first_name')->get(['id','first_name','last_name']);
        $staff    = Staff::where('salon_id', $salon->id)->where('is_active', true)
            ->withName()
            ->get();
        $services = Service::where('salon_id', $salon->id)->active()->get(['id','name','duration_minutes','price']);

        return view('appointments.edit', compact('appointment', 'clients', 'staff', 'services'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->authorise($appointment);

        $data = $request->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'staff_id'       => ['required', 'exists:staff,id'],
            'starts_at'      => ['required', 'date'],
            'internal_notes' => ['nullable', 'string', 'max:1000'],
            'client_notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment->update($data);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $this->authorise($appointment);
        $data = $request->validate(['status' => ['required', 'in:confirmed,completed,cancelled,no_show']]);
        $appointment->update(['status' => $data['status']]);

        return back()->with('success', 'Status updated.');
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorise($appointment);
        $appointment->delete();

        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    private function authorise(Appointment $appointment): void
    {
        abort_unless($appointment->salon_id === $this->salon()->id, 403);
    }
}
