<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\ExpenseCategory;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Services\ExpenseCategoryDefaults;
use App\Services\StaffAttendanceService;
use App\Services\StaffPayrollCalculator;
use App\Support\LanguageProficiency;
use App\Support\SalonTime;
use App\Support\StaffJobRoles;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Support\PublicStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct(
        private readonly StaffAttendanceService $attendanceService,
        private readonly StaffPayrollCalculator $payrollCalculator,
    ) {}

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index(Request $request)
    {
        $salon = $this->salon();
        [$rangeFrom, $rangeTo, $monthStart, $monthKey, $salonToday] = $this->resolveHubRange($request, $salon);
        $rangeLabel = $rangeFrom->equalTo($rangeTo)
            ? $rangeFrom->format('j M Y')
            : $rangeFrom->format('j M Y').' – '.$rangeTo->format('j M Y');
        $todayStr = $salonToday;
        $taxRate = 0.10;

        $staff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->withCount([
                'appointments as total_appointments' => fn ($q) => $q
                    ->withoutGlobalScopes()
                    ->where('salon_id', $salon->id),
                'appointments as completed_appointments' => fn ($q) => $q
                    ->withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->where('status', 'completed'),
            ])
            ->withAvg('reviews', 'rating')
            ->orderBy('first_name')
            ->get();

        [$rangeFromUtc, $rangeToUtc] = SalonTime::ymdRangeUtcInclusive(
            $salon,
            $rangeFrom->toDateString(),
            $rangeTo->toDateString()
        );

        $posByStaff = PosTransaction::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereNotNull('staff_id')
            ->whereRaw('COALESCE(completed_at, created_at) BETWEEN ? AND ?', [$rangeFromUtc, $rangeToUtc])
            ->selectRaw('staff_id, COALESCE(SUM(total),0) as pos_total, COUNT(*) as pos_count')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        $reviewsMonthByStaff = Review::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereNotNull('staff_id')
            ->whereBetween('created_at', [$rangeFromUtc, $rangeToUtc])
            ->selectRaw('staff_id, COUNT(*) as c, AVG(rating) as avg_rating')
            ->groupBy('staff_id')
            ->get()
            ->keyBy('staff_id');

        $revenueByStaff = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereBetween('starts_at', [$rangeFromUtc, $rangeToUtc])
            ->selectRaw('staff_id, COALESCE(SUM(total_price),0) as rev')
            ->groupBy('staff_id')
            ->pluck('rev', 'staff_id');

        $apptsMonthByStaff = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [$rangeFromUtc, $rangeToUtc])
            ->selectRaw('staff_id, COUNT(*) as c')
            ->groupBy('staff_id')
            ->pluck('c', 'staff_id');

        $payrollByStaff = $this->payrollCalculator->forStaffCollection($salon, $staff, $monthStart, $taxRate);

        $payrollRows = [];
        $chart = [];

        foreach ($staff as $m) {
            $rev = (float) ($revenueByStaff[$m->id] ?? 0);
            $apptM = (int) ($apptsMonthByStaff[$m->id] ?? 0);
            $posRow = $posByStaff->get($m->id);
            $reviewRow = $reviewsMonthByStaff->get($m->id);
            $todayAttendance = $this->attendanceService->todayStatus($salon, $m);
            $onLeave = StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $m->id, $todayStr)
                || $todayAttendance === \App\Models\StaffAttendanceRecord::STATUS_ON_LEAVE;

            $pay = $payrollByStaff[(int) $m->id] ?? $this->payrollCalculator->forStaff($salon, $m, $monthStart, $taxRate);
            $commPct = (float) ($m->commission_rate ?? 0);
            $commissionEarned = round($rev * $commPct / 100, 2);

            $payrollRows[] = [
                'staff' => $m,
                'base' => $pay['base_salary'],
                'base_payable' => $pay['base_payable'],
                'commission' => $pay['commission'],
                'tax' => $pay['tax'],
                'net' => $pay['net'],
                'worked_days' => $pay['worked_days'],
                'scheduled_days' => $pay['scheduled_days'],
                'appointments' => $pay['appointments'],
                'revenue' => $pay['revenue'],
                'suggested_title' => $pay['suggested_title'],
                'suggested_amount' => $pay['suggested_amount'],
            ];
            $chart[] = ['name' => $m->name, 'revenue' => $rev];

            $m->setAttribute('hub_revenue_month', $rev);
            $m->setAttribute('hub_appts_month', $apptM);
            $m->setAttribute('hub_commission_month', $commissionEarned);
            $m->setAttribute('hub_pos_month', (float) ($posRow?->pos_total ?? 0));
            $m->setAttribute('hub_pos_count', (int) ($posRow?->pos_count ?? 0));
            $m->setAttribute('hub_reviews_month', (int) ($reviewRow?->c ?? 0));
            $m->setAttribute(
                'hub_rating_month',
                $reviewRow !== null && $reviewRow->avg_rating !== null
                    ? round((float) $reviewRow->avg_rating, 1)
                    : null
            );
            $m->setAttribute('hub_on_leave_today', $onLeave);
            $m->setAttribute('hub_attendance_today', $todayAttendance);
        }

        $maxRev = $chart === [] ? 1 : max(1, ...array_column($chart, 'revenue'));
        $totalTeam = $staff->count();
        $onDuty = $staff->filter(fn ($m) => $m->is_active && $this->attendanceService->isOnDutyToday($salon, $m))->count();

        ExpenseCategoryDefaults::ensureForSalon($salon->id);
        $salaryCategoryId = ExpenseCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('slug', 'salary')
            ->value('id');

        $monthFrom = $rangeFrom->toDateString();
        $monthTo = $rangeTo->toDateString();
        $monthEnd = $monthStart->copy()->endOfMonth();

        return view('staff.index', compact(
            'salon',
            'staff',
            'payrollRows',
            'chart',
            'maxRev',
            'monthStart',
            'monthEnd',
            'monthKey',
            'monthFrom',
            'monthTo',
            'rangeLabel',
            'salonToday',
            'totalTeam',
            'onDuty',
            'taxRate',
            'salaryCategoryId'
        ));
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: Carbon, 3: string, 4: string}
     */
    private function resolveHubRange(Request $request, $salon): array
    {
        $tz = SalonTime::timezone($salon);
        $salonToday = SalonTime::todayDateString($salon);
        $nowLocal = SalonTime::now($salon);

        // Legacy month=Y-m still supported.
        if ($request->filled('month') && ! $request->filled('from') && ! $request->filled('to')) {
            $month = (string) $request->query('month');
            if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                try {
                    $monthStart = Carbon::createFromFormat('Y-m-d', $month.'-01', $tz)->startOfMonth()->startOfDay();
                    $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();
                    $to = $monthEnd->toDateString() > $salonToday && $month === $nowLocal->format('Y-m')
                        ? $salonToday
                        : $monthEnd->toDateString();

                    return [
                        $monthStart->copy(),
                        Carbon::createFromFormat('Y-m-d', $to, $tz)->startOfDay(),
                        $monthStart->copy(),
                        $month,
                        $salonToday,
                    ];
                } catch (\Throwable) {
                    // fall through
                }
            }
        }

        $from = (string) $request->query('from', $nowLocal->copy()->startOfMonth()->toDateString());
        $to = (string) $request->query('to', $salonToday);

        try {
            $rangeFrom = Carbon::createFromFormat('Y-m-d', $from, $tz)->startOfDay();
        } catch (\Throwable) {
            $rangeFrom = $nowLocal->copy()->startOfMonth()->startOfDay();
            $from = $rangeFrom->toDateString();
        }

        try {
            $rangeTo = Carbon::createFromFormat('Y-m-d', $to, $tz)->startOfDay();
        } catch (\Throwable) {
            $rangeTo = Carbon::createFromFormat('Y-m-d', $salonToday, $tz)->startOfDay();
            $to = $salonToday;
        }

        if ($rangeTo->lt($rangeFrom)) {
            [$rangeFrom, $rangeTo] = [$rangeTo->copy(), $rangeFrom->copy()];
            [$from, $to] = [$rangeFrom->toDateString(), $rangeTo->toDateString()];
        }

        $monthStart = $rangeTo->copy()->startOfMonth()->startOfDay();
        $monthKey = $monthStart->format('Y-m');

        return [$rangeFrom, $rangeTo, $monthStart, $monthKey, $salonToday];
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

        return redirect()
            ->route('staff.index', array_filter(['month' => $request->input('month', $request->query('month'))]))
            ->with('success', 'Weekly schedule updated.');
    }

    public function updateBaseSalary(Request $request, Staff $staff)
    {
        $this->authorise($staff);

        $data = $request->validate([
            'base_salary' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:99999999'],
            'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);

        $staff->update(['base_salary' => $data['base_salary'] ?? null]);

        return redirect()
            ->route('staff.index', array_filter(['month' => $data['month'] ?? $request->query('month')]))
            ->with('success', 'Base salary saved.');
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

        $staffQuery = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->orderBy('first_name');

        if ($request->filled('staff_id')) {
            $staffQuery->where('id', (int) $request->query('staff_id'));
        }

        $staff = $staffQuery->get();

        if ($request->filled('staff_id') && $staff->isEmpty()) {
            abort(404);
        }

        $payrollByStaff = $this->payrollCalculator->forStaffCollection($salon, $staff, $monthStart, $taxRate);

        $staffSlug = $staff->count() === 1
            ? '-' . \Illuminate\Support\Str::slug($staff->first()->name)
            : '';
        $filename = 'payroll-' . $month . $staffSlug . '.csv';

        return response()->streamDownload(function () use ($staff, $payrollByStaff) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Staff', 'Appointments', 'Revenue', 'Scheduled days', 'Worked days',
                'Base salary', 'Base payable', 'Commission', 'Tax', 'Net pay',
            ]);

            foreach ($staff as $m) {
                $pay = $payrollByStaff[(int) $m->id] ?? null;
                if (! $pay) {
                    continue;
                }

                fputcsv($out, [
                    $m->name,
                    $pay['appointments'],
                    number_format($pay['revenue'], 2, '.', ''),
                    $pay['scheduled_days'],
                    $pay['worked_days'],
                    number_format($pay['base_salary'], 2, '.', ''),
                    number_format($pay['base_payable'], 2, '.', ''),
                    number_format($pay['commission'], 2, '.', ''),
                    number_format($pay['tax'], 2, '.', ''),
                    number_format($pay['net'], 2, '.', ''),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create()
    {
        $salon = $this->salon();

        return view('staff.create', compact('salon'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'              => ['required', 'string', 'max:100'],
            'email'             => ['nullable', 'email', 'max:150'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'role'              => StaffJobRoles::validationRules(),
            'experience'        => ['nullable', 'string', 'max:120'],
            'language_proficiency'   => ['nullable', 'array', 'max:30'],
            'language_proficiency.*' => ['string', Rule::in(LanguageProficiency::allowedCodes())],
            'bio'               => ['nullable', 'string', 'max:1000'],
            'awards_accolades'  => ['nullable', 'string', 'max:5000'],
            'color'             => ['nullable', 'string', 'max:7'],
            'commission_rate'   => ['nullable', 'numeric', 'min:0', 'max:100'],
            'avatar'            => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $nameParts = explode(' ', trim($data['name']), 2);
        $avatarFile = $request->file('avatar');
        unset($data['avatar']);
        $encodedLanguages = LanguageProficiency::encode($data['language_proficiency'] ?? []);

        $staff = Staff::create([
            'salon_id'        => $salon->id,
            'first_name'      => $nameParts[0],
            'last_name'       => $nameParts[1] ?? '',
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'role'            => $data['role'],
            'experience'      => $data['experience'] ?? null,
            'language_proficiency' => $encodedLanguages,
            'bio'             => $data['bio'] ?? null,
            'awards_accolades' => $data['awards_accolades'] ?? null,
            'color'           => $data['color'] ?? '#7C3AED',
            'commission_rate' => $data['commission_rate'] ?? 0,
            'is_active'       => true,
        ]);

        if ($avatarFile) {
            $staff->update([
                'avatar' => $avatarFile->store('salons/'.$salon->id.'/staff', 'public'),
            ]);
        }

        return redirect()->route('staff.index')->with('success', 'Staff member added.');
    }

    public function show(Staff $staff)
    {
        $this->authorise($staff);
        $salon = $this->salon();

        $recentAppointments = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->with(['client', 'services.service'])
            ->latest('starts_at')
            ->paginate(10);

        $completedCount = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->where('status', 'completed')
            ->count();

        $totalRevenue = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->where('status', 'completed')
            ->sum('total_price');

        $upcomingCount = Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->where('starts_at', '>=', now())
            ->where('status', 'confirmed')
            ->count();

        return view('staff.show', compact('staff', 'recentAppointments', 'completedCount', 'totalRevenue', 'upcomingCount'));
    }

    public function edit(Staff $staff)
    {
        $this->authorise($staff);

        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $this->authorise($staff);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['nullable', 'email', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'role'            => StaffJobRoles::validationRules(),
            'experience'      => ['nullable', 'string', 'max:120'],
            'language_proficiency'   => ['nullable', 'array', 'max:30'],
            'language_proficiency.*' => ['string', Rule::in(LanguageProficiency::allowedCodes())],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'awards_accolades' => ['nullable', 'string', 'max:5000'],
            'color'           => ['nullable', 'string', 'max:7'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active'       => ['sometimes', 'boolean'],
            'avatar'          => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        // Split 'name' into first_name / last_name for the Staff model
        if (isset($data['name'])) {
            $nameParts = explode(' ', trim($data['name']), 2);
            $data['first_name'] = $nameParts[0];
            $data['last_name']  = $nameParts[1] ?? '';
            unset($data['name']);
        }

        unset($data['avatar']);

        $data['language_proficiency'] = LanguageProficiency::encode($data['language_proficiency'] ?? []);

        $data['is_active'] = $request->boolean('is_active');

        $staff->update($data);

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
            PublicStorage::delete($staff->avatar);
            $path = $request->file('avatar')->store('salons/'.$staff->salon_id.'/staff', 'public');
            $staff->update(['avatar' => $path]);

            return;
        }

        if ($request->boolean('remove_avatar')) {
            PublicStorage::delete($staff->avatar);
            $staff->update(['avatar' => null]);
        }
    }

}
