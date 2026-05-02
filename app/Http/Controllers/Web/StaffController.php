<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon       = $this->salon();
        $monthStart  = now()->startOfMonth();
        $monthEnd    = now()->endOfMonth();
        $todayStr    = now()->toDateString();
        $taxRate     = 0.10;

        $staff = Staff::where('salon_id', $salon->id)
            ->withCount([
                'appointments as total_appointments',
                'appointments as completed_appointments' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->withAvg('reviews', 'rating')
            ->orderBy('first_name')
            ->get();

        $revenueByStaff = Appointment::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->selectRaw('staff_id, COALESCE(SUM(total_price),0) as rev')
            ->groupBy('staff_id')
            ->pluck('rev', 'staff_id');

        $apptsMonthByStaff = Appointment::where('salon_id', $salon->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->selectRaw('staff_id, COUNT(*) as c')
            ->groupBy('staff_id')
            ->pluck('c', 'staff_id');

        $payrollRows = [];
        $chart       = [];

        foreach ($staff as $m) {
            $rev   = (float) ($revenueByStaff[$m->id] ?? 0);
            $apptM = (int) ($apptsMonthByStaff[$m->id] ?? 0);
            $onLeave = StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $m->id, $todayStr);

            $base          = (float) ($m->base_salary ?? 0);
            $commPct       = (float) ($m->commission_rate ?? 0);
            $commissionAmt = round($rev * $commPct / 100, 2);
            $gross         = $base + $commissionAmt;
            $tax           = round($gross * $taxRate, 2);
            $net           = round($gross - $tax, 2);

            $payrollRows[] = [
                'staff'       => $m,
                'base'        => $base,
                'commission'  => $commissionAmt,
                'tax'         => $tax,
                'net'         => $net,
            ];
            $chart[] = ['name' => $m->name, 'revenue' => $rev];

            $m->setAttribute('hub_revenue_month', $rev);
            $m->setAttribute('hub_appts_month', $apptM);
            $m->setAttribute('hub_on_leave_today', $onLeave);
        }

        $maxRev    = $chart === [] ? 1 : max(1, ...array_column($chart, 'revenue'));
        $totalTeam = $staff->count();
        $onDuty    = $staff->filter(fn ($m) => $m->is_active && ! $m->hub_on_leave_today)->count();

        return view('staff.index', compact(
            'salon',
            'staff',
            'payrollRows',
            'chart',
            'maxRev',
            'monthStart',
            'totalTeam',
            'onDuty',
            'taxRate'
        ));
    }

    public function updateWeeklySchedule(Request $request, Staff $staff)
    {
        $this->authorise($staff);

        $data = $request->validate([
            'working_days'   => ['nullable', 'array'],
            'working_days.*' => ['string', 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun'],
            'start_time'     => ['nullable', 'string', 'max:8'],
            'end_time'       => ['nullable', 'string', 'max:8'],
        ]);

        $staff->update([
            'working_days' => $data['working_days'] ?? [],
            'start_time'   => $data['start_time'] ?? $staff->start_time,
            'end_time'     => $data['end_time'] ?? $staff->end_time,
        ]);

        return redirect()->route('staff.index')->with('success', 'Weekly schedule updated.');
    }

    public function updateBaseSalary(Request $request, Staff $staff)
    {
        $this->authorise($staff);

        $data = $request->validate([
            'base_salary' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999'],
        ]);

        $staff->update(['base_salary' => $data['base_salary'] ?? null]);

        return redirect()->route('staff.index')->with('success', 'Base salary saved.');
    }

    public function exportPayroll(Request $request): StreamedResponse
    {
        $salon = $this->salon();

        $month = $request->query('month', now()->format('Y-m'));
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $monthStart = Carbon::parse($month . '-01')->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $taxRate    = 0.10;

        $staff = Staff::where('salon_id', $salon->id)->orderBy('first_name')->get();

        $revenueByStaff = Appointment::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereBetween('starts_at', [$monthStart, $monthEnd])
            ->selectRaw('staff_id, COALESCE(SUM(total_price),0) as rev')
            ->groupBy('staff_id')
            ->pluck('rev', 'staff_id');

        $filename = 'payroll-' . $month . '.csv';

        return response()->streamDownload(function () use ($staff, $revenueByStaff, $taxRate) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Staff', 'Base', 'Commission', 'Tax', 'Net pay']);

            foreach ($staff as $m) {
                $rev           = (float) ($revenueByStaff[$m->id] ?? 0);
                $base          = (float) ($m->base_salary ?? 0);
                $commPct       = (float) ($m->commission_rate ?? 0);
                $commissionAmt = round($rev * $commPct / 100, 2);
                $gross         = $base + $commissionAmt;
                $tax           = round($gross * $taxRate, 2);
                $net           = round($gross - $tax, 2);

                fputcsv($out, [
                    $m->name,
                    number_format($base, 2, '.', ''),
                    number_format($commissionAmt, 2, '.', ''),
                    number_format($tax, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create()
    {
        $salon    = $this->salon();
        $role = old('role', 'therapist');
        $services = $this->eligibleServicesForRole($salon->id, $role);

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
            'services.*'        => [Rule::exists('services', 'id')->where('salon_id', $salon->id)],
            'avatar'            => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $this->assertServicesEligibleForRole($salon->id, (string) $data['role'], $data['services'] ?? []);

        $nameParts = explode(' ', trim($data['name']), 2);
        $avatarFile = $request->file('avatar');
        unset($data['avatar']);

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

        if ($avatarFile) {
            $staff->update([
                'avatar' => $avatarFile->store('salons/'.$salon->id.'/staff', 'public'),
            ]);
        }

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
        $role = old('role', (string) $staff->role);
        $services = $this->eligibleServicesForRole($salon->id, $role);
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
            'services.*'      => [Rule::exists('services', 'id')->where('salon_id', $staff->salon_id)],
            'avatar'          => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $this->assertServicesEligibleForRole($staff->salon_id, (string) $data['role'], $data['services'] ?? []);

        // Split 'name' into first_name / last_name for the Staff model
        if (isset($data['name'])) {
            $nameParts = explode(' ', trim($data['name']), 2);
            $data['first_name'] = $nameParts[0];
            $data['last_name']  = $nameParts[1] ?? '';
            unset($data['name']);
        }

        unset($data['avatar']);

        $syncServices = array_key_exists('services', $data);
        $serviceIds   = $data['services'] ?? [];
        unset($data['services']);

        $staff->update($data);

        if ($syncServices) {
            $staff->services()->sync($serviceIds);
        }

        $this->syncStaffAvatarFromRequest($request, $staff);

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

    /** Replace or remove profile photo; matches API storage path `salons/{id}/staff`. */
    private function syncStaffAvatarFromRequest(Request $request, Staff $staff): void
    {
        if ($request->hasFile('avatar')) {
            if ($staff->avatar) {
                Storage::disk('public')->delete($staff->avatar);
            }
            $path = $request->file('avatar')->store('salons/'.$staff->salon_id.'/staff', 'public');
            $staff->update(['avatar' => $path]);

            return;
        }

        if ($request->boolean('remove_avatar')) {
            if ($staff->avatar) {
                Storage::disk('public')->delete($staff->avatar);
            }
            $staff->update(['avatar' => null]);
        }
    }

    private function eligibleServicesForRole(int $salonId, string $role)
    {
        return Service::where('salon_id', $salonId)
            ->active()
            ->orderBy('sort_order')
            ->get(['id', 'name', 'allowed_roles'])
            ->filter(fn (Service $service) => $service->allowsStaffRole($role))
            ->values();
    }

    /** @param  array<int, mixed>  $serviceIds */
    private function assertServicesEligibleForRole(int $salonId, string $role, array $serviceIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $serviceIds)));
        if ($ids === []) {
            return;
        }

        $services = Service::where('salon_id', $salonId)->whereIn('id', $ids)->get(['id', 'name', 'allowed_roles']);
        $blocked = $services->filter(fn (Service $service) => ! $service->allowsStaffRole($role))->pluck('name')->values();
        if ($blocked->isEmpty()) {
            return;
        }

        throw ValidationException::withMessages([
            'services' => ['Selected role cannot be assigned these services: '.$blocked->implode(', ').'.'],
        ]);
    }
}
