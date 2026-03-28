<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon = $this->salon();
        $staff = Staff::where('salon_id', $salon->id)
            ->withCount(['appointments as total_appointments', 'appointments as completed_appointments' => fn($q) => $q->where('status', 'completed')])
            ->orderBy('first_name')
            ->get();

        return view('staff.index', compact('salon', 'staff'));
    }

    public function create()
    {
        $salon    = $this->salon();
        $services = Service::where('salon_id', $salon->id)->active()->get(['id','name']);

        return view('staff.create', compact('salon', 'services'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'email'             => ['nullable', 'email', 'max:150'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'role'              => ['required', 'in:owner,manager,stylist,therapist,receptionist,junior'],
            'bio'               => ['nullable', 'string', 'max:1000'],
            'color'             => ['nullable', 'string', 'max:7'],
            'commission_rate'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'services'          => ['nullable', 'array'],
            'services.*'        => ['exists:services,id'],
        ]);

        $nameParts = explode(' ', trim($data['name']), 2);
        $staff = Staff::create([
            'salon_id'        => $salon->id,
            'first_name'      => $nameParts[0],
            'last_name'       => $nameParts[1] ?? '',
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'role'            => $data['role'],
            'bio'             => $data['bio'] ?? null,
            'color'           => $data['color'] ?? '#7C3AED',
            'commission_rate' => $data['commission_rate'] ?? 0,
            'is_active'       => true,
        ]);

        if (!empty($data['services'])) {
            $staff->services()->sync($data['services']);
        }

        return redirect()->route('staff.index')->with('success', 'Staff member added.');
    }

    public function show(Staff $staff)
    {
        $this->authorise($staff);

        $completedAppointments = Appointment::where('staff_id', $staff->id)
            ->where('status', 'completed')
            ->with(['client', 'services.service'])
            ->latest('starts_at')
            ->paginate(10);

        $totalRevenue = $staff->appointments()
            ->where('status', 'completed')
            ->sum('total_price');

        $upcomingCount = $staff->appointments()
            ->where('starts_at', '>=', now())
            ->where('status', 'confirmed')
            ->count();

        return view('staff.show', compact('staff', 'completedAppointments', 'totalRevenue', 'upcomingCount'));
    }

    public function edit(Staff $staff)
    {
        $this->authorise($staff);
        $salon    = $this->salon();
        $services = Service::where('salon_id', $salon->id)->active()->get(['id','name']);
        $assigned = $staff->services()->pluck('services.id')->toArray();

        return view('staff.edit', compact('staff', 'services', 'assigned'));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorise($staff);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['nullable', 'email', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'role'            => ['required', 'in:owner,manager,stylist,therapist,receptionist,junior'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'color'           => ['nullable', 'string', 'max:7'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'       => ['boolean'],
            'services'        => ['nullable', 'array'],
            'services.*'      => ['exists:services,id'],
        ]);

        // Split 'name' into first_name / last_name for the Staff model
        if (isset($data['name'])) {
            $nameParts = explode(' ', trim($data['name']), 2);
            $data['first_name'] = $nameParts[0];
            $data['last_name']  = $nameParts[1] ?? '';
            unset($data['name']);
        }

        $staff->update($data);

        if (isset($data['services'])) {
            $staff->services()->sync($data['services']);
        }

        return redirect()->route('staff.show', $staff)->with('success', 'Staff member updated.');
    }

    public function destroy(Staff $staff)
    {
        $this->authorise($staff);
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member removed.');
    }

    private function authorise(Staff $staff): void
    {
        abort_unless($staff->salon_id === $this->salon()->id, 403);
    }
}
