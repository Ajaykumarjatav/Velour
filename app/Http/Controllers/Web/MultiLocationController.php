<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MultiLocationController extends Controller
{
    public function index(Request $request): View
    {
        $owner = Auth::user();
        $locations = Salon::where('owner_id', $owner->id)
            ->orderBy('created_at')
            ->get();

        $locationIds = $locations->pluck('id');
        $today = today();
        $monthStart = now()->startOfMonth();
        $monthEnd = now();

        $staffBySalon = Staff::whereIn('salon_id', $locationIds)
            ->where('is_active', true)
            ->select('salon_id', DB::raw('count(*) as c'))
            ->groupBy('salon_id')
            ->pluck('c', 'salon_id');

        $todayApptBySalon = Appointment::whereIn('salon_id', $locationIds)
            ->whereDate('starts_at', $today)
            ->select('salon_id', DB::raw('count(*) as c'))
            ->groupBy('salon_id')
            ->pluck('c', 'salon_id');

        $monthlyRevenueBySalon = PosTransaction::whereIn('salon_id', $locationIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->select('salon_id', DB::raw('COALESCE(sum(total),0) as t'))
            ->groupBy('salon_id')
            ->pluck('t', 'salon_id');

        $branchManagers = SalonSetting::whereIn('salon_id', $locationIds)
            ->where('key', 'branch_manager_name')
            ->pluck('value', 'salon_id');

        $cards = $locations->map(function (Salon $salon) use ($staffBySalon, $todayApptBySalon, $monthlyRevenueBySalon, $branchManagers) {
            $card = [
                'id' => $salon->id,
                'name' => $salon->name,
                'address' => trim(implode(', ', array_filter([$salon->address_line1, $salon->city]))),
                'address_line1' => (string) ($salon->address_line1 ?? ''),
                'city' => (string) ($salon->city ?? ''),
                'timezone' => (string) ($salon->timezone ?? 'Asia/Kolkata'),
                'phone' => (string) ($salon->phone ?? ''),
                'online_booking_enabled' => (bool) $salon->online_booking_enabled,
                'status' => $salon->is_active ? 'active' : 'opening_soon',
                'staff_count' => (int) ($staffBySalon[$salon->id] ?? 0),
                'today_appointments' => (int) ($todayApptBySalon[$salon->id] ?? 0),
                'monthly_revenue' => (float) ($monthlyRevenueBySalon[$salon->id] ?? 0),
                'branch_manager' => (string) ($branchManagers[$salon->id] ?? ''),
            ];
            $card['report'] = $this->buildBranchReport($salon->id);
            return $card;
        });

        $summary = [
            'total_locations' => $locations->count(),
            'total_staff' => (int) $cards->sum('staff_count'),
            'today_appointments' => (int) $cards->sum('today_appointments'),
            'combined_revenue' => (float) $cards->sum('monthly_revenue'),
        ];

        $consolidated = $this->buildConsolidatedReport($cards);

        return view('multi-location.index', [
            'summary' => $summary,
            'cards' => $cards,
            'consolidated' => $consolidated,
            'timezones' => $this->timezoneOptions(),
            'switchedTo' => $request->session()->get('switched_location_name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $owner = Auth::user();
        $baseSalon = Salon::where('owner_id', $owner->id)->orderBy('id')->first();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'branch_manager' => ['nullable', 'string', 'max:100'],
            'online_booking_enabled' => ['nullable', 'boolean'],
            'notify_team_when_created' => ['nullable', 'boolean'],
        ]);

        $slug = $this->uniqueSlugForOwner($owner->id, $data['name']);

        $salon = Salon::create([
            'owner_id' => $owner->id,
            'name' => $data['name'],
            'slug' => $slug,
            'subdomain' => $slug,
            'address_line1' => $data['address_line1'],
            'city' => $data['city'],
            'timezone' => $data['timezone'],
            'phone' => $data['phone'],
            'currency' => $baseSalon?->currency ?? 'GBP',
            'country' => $baseSalon?->country,
            'locale' => $baseSalon?->locale ?? 'en',
            'is_active' => true,
            'online_booking_enabled' => (bool) ($data['online_booking_enabled'] ?? false),
            'new_client_booking_enabled' => (bool) ($data['online_booking_enabled'] ?? false),
        ]);

        if (! empty($data['branch_manager'])) {
            SalonSetting::updateOrCreate(
                ['salon_id' => $salon->id, 'key' => 'branch_manager_name'],
                ['value' => $data['branch_manager'], 'type' => 'string']
            );
        }

        if ($request->boolean('notify_team_when_created') && $baseSalon) {
            SalonNotification::create([
                'salon_id' => $baseSalon->id,
                'type' => 'info',
                'title' => 'New branch added',
                'body' => $salon->name . ' has been created.',
                'data' => ['created_salon_id' => $salon->id],
            ]);
        }

        return redirect()->route('multi-location.index')->with('success', 'Location added successfully.');
    }

    public function update(Request $request, Salon $location): RedirectResponse
    {
        $this->authorise($location);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'timezone' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'branch_manager' => ['nullable', 'string', 'max:100'],
            'online_booking_enabled' => ['nullable', 'boolean'],
        ]);

        $location->update([
            'name' => $data['name'],
            'address_line1' => $data['address_line1'],
            'city' => $data['city'],
            'timezone' => $data['timezone'],
            'phone' => $data['phone'],
            'online_booking_enabled' => (bool) ($data['online_booking_enabled'] ?? false),
            'new_client_booking_enabled' => (bool) ($data['online_booking_enabled'] ?? false),
        ]);

        SalonSetting::updateOrCreate(
            ['salon_id' => $location->id, 'key' => 'branch_manager_name'],
            ['value' => (string) ($data['branch_manager'] ?? ''), 'type' => 'string']
        );

        return redirect()->route('multi-location.index')->with('success', 'Location updated.');
    }

    public function destroy(Salon $location): RedirectResponse
    {
        $this->authorise($location);

        $count = Salon::where('owner_id', Auth::id())->count();
        abort_unless($count > 1, 422, 'At least one location is required.');

        $location->delete();

        return redirect()->route('multi-location.index')->with('success', 'Location removed.');
    }

    public function switch(Salon $location): RedirectResponse
    {
        $this->authorise($location);
        session(['active_salon_id' => $location->id]);
        session(['switched_location_name' => $location->name]);

        return redirect()->route('multi-location.index')
            ->with('success', 'Switched to ' . $location->name . '.');
    }

    private function authorise(Salon $location): void
    {
        abort_unless($location->owner_id === Auth::id(), 403);
    }

    private function uniqueSlugForOwner(int $ownerId, string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'branch';
        $slug = $base;
        $i = 2;
        while (Salon::where('owner_id', $ownerId)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }

    private function buildBranchReport(int $salonId): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now();

        $monthlyRevenue = (float) PosTransaction::where('salon_id', $salonId)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('total');

        $staffCount = (int) Staff::where('salon_id', $salonId)->where('is_active', true)->count();
        $todayAppointments = (int) Appointment::where('salon_id', $salonId)->whereDate('starts_at', today())->count();
        $avgRating = (float) Review::where('salon_id', $salonId)->avg('rating');
        $newClientsMtd = (int) Client::where('salon_id', $salonId)->whereBetween('created_at', [$monthStart, $monthEnd])->count();

        $topService = Service::where('salon_id', $salonId)
            ->leftJoin('appointment_services as aps', 'services.id', '=', 'aps.service_id')
            ->select('services.name', DB::raw('count(aps.id) as c'))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('c')
            ->value('name') ?? 'N/A';

        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $value = (float) PosTransaction::where('salon_id', $salonId)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->sum('total');
            $trend[] = ['label' => $m->format('M'), 'value' => $value];
        }
        $maxTrend = max(1, (float) collect($trend)->max('value'));
        foreach ($trend as &$row) {
            $row['percent'] = (int) round(($row['value'] / $maxTrend) * 100);
        }

        return [
            'monthly_revenue' => $monthlyRevenue,
            'staff_count' => $staffCount,
            'today_appointments' => $todayAppointments,
            'avg_rating' => $avgRating > 0 ? round($avgRating, 1) : null,
            'new_clients_mtd' => $newClientsMtd,
            'top_service' => $topService,
            'trend' => $trend,
        ];
    }

    private function buildConsolidatedReport(Collection $cards): array
    {
        $totals = [
            'revenue' => (float) $cards->sum(fn ($c) => (float) ($c['report']['monthly_revenue'] ?? 0)),
            'staff' => (int) $cards->sum(fn ($c) => (int) ($c['report']['staff_count'] ?? 0)),
            'appointments' => (int) $cards->sum(fn ($c) => (int) ($c['report']['today_appointments'] ?? 0)),
            'new_clients' => (int) $cards->sum(fn ($c) => (int) ($c['report']['new_clients_mtd'] ?? 0)),
        ];

        return $totals;
    }

    private function timezoneOptions(): array
    {
        return [
            'Asia/Kolkata', 'Asia/Dubai', 'Europe/London', 'Europe/Paris',
            'America/New_York', 'America/Los_Angeles', 'Australia/Sydney',
        ];
    }
}

