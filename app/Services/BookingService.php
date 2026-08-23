<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Client;
use App\Models\StaffLeaveRequest;
use App\Services\StaffAttendanceService;
use App\Services\NotificationService;
use App\Services\Scheduling\AvailabilityRejectedException;
use App\Scopes\TenantScope;
use App\Support\SalonTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Combined appointment window (minutes) for one or more services.
     * Must match {@see Service::summarizeForAppointment} `total_span_minutes` (duration + buffers per line).
     *
     * @param  int|null  $salonId  When set, span is computed via summarize (same as booking validation).
     */
    public static function combinedDurationMinutes(Collection|Service $services, ?int $salonId = null): int
    {
        $c = $services instanceof Service ? collect([$services]) : $services;
        if ($c->isEmpty()) {
            return 0;
        }

        if ($salonId !== null) {
            $orderedIds = $c->map(fn (Service $s) => (int) $s->id)->all();

            return (int) Service::summarizeForAppointment($salonId, $orderedIds, [])['total_span_minutes'];
        }

        return (int) $c->sum(fn (Service $s) => (int) $s->duration_minutes + (int) ($s->buffer_minutes ?? 0));
    }

    /**
     * Generate all available time slots for one or more services on a given date.
     * Staff must be bookable online and (when no staff filter) qualified for every selected service.
     *
     * @param  Service|\Illuminate\Support\Collection<int, Service>  $services
     * @return array  [['time' => '09:00', 'available_staff' => [...], 'available' => true], ...]
     */
    public function getAvailableSlots(
        int                    $salonId,
        Service|Collection     $services,
        Carbon                 $date,
        ?int                   $staffId = null
    ): array {
        $collection = $services instanceof Service ? collect([$services]) : $services;
        if ($collection->isEmpty()) {
            return [];
        }

        $salon    = Salon::findOrFail($salonId);
        $duration = self::combinedDurationMinutes($collection, $salonId);
        $tz       = SalonTime::timezone($salon);
        $ymd      = $date->format('Y-m-d');
        $todayYmd = SalonTime::todayDateString($salon);
        $nowLocal = SalonTime::now($salon);

        // Weekday + opening hours for this calendar day in the salon (not app UTC).
        $localDay = Carbon::createFromFormat('Y-m-d', $ymd, $tz)->startOfDay();
        $dayKey   = strtolower($localDay->locale('en')->format('l'));
        $dowTag   = $localDay->format('D'); // Mon, Tue, … for working_days JSON

        $dayConfig = $salon->openingHoursForWeekdayKey($dayKey);

        if (! $dayConfig || empty($dayConfig['open'])) {
            Log::debug('Day is closed', ['day' => $dayKey, 'config' => $dayConfig]);

            return [];
        }

        $openTime  = $dayConfig['from'] ?? $dayConfig['start'] ?? '09:00';
        $closeTime = $dayConfig['to'] ?? $dayConfig['end'] ?? '18:00';

        try {
            $open  = Carbon::createFromFormat('Y-m-d H:i', $ymd . ' ' . substr((string) $openTime, 0, 5), $tz);
            $close = Carbon::createFromFormat('Y-m-d H:i', $ymd . ' ' . substr((string) $closeTime, 0, 5), $tz);
        } catch (\Exception $e) {
            Log::error('Invalid time format in opening hours', ['open' => $openTime, 'close' => $closeTime]);

            return [];
        }

        if ($open->gte($close)) {
            return [];
        }

        // Get relevant staff
        $staffQuery = Staff::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->onlineBookable()
            ->where(function ($q) use ($dowTag) {
                $q->whereNull('working_days')
                    ->orWhereJsonContains('working_days', $dowTag);
            });

        if ($staffId) {
            $staffQuery->where('id', $staffId);
        }

        // Online booking: any active, bookable staff may perform any service.
        $staffList = $staffQuery
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($staffList->isEmpty()) {
            Log::debug('No staff available', ['service_ids' => $collection->pluck('id')->all(), 'date' => $ymd]);

            return [];
        }

        $apptAvailability = app(AppointmentService::class);

        $slots = [];
        $interval  = 15; // 15-minute slot intervals
        $current   = $open->copy();

        while ($current->copy()->addMinutes($duration)->lte($close)) {
            // For today's date, only show slots that start after the current salon time.
            if ($ymd === $todayYmd && $current->lte($nowLocal)) {
                $current->addMinutes($interval);
                continue;
            }

            $slotEnd     = $current->copy()->addMinutes($duration);
            $availableStaff = [];

            foreach ($staffList as $staff) {
                if (app(StaffAttendanceService::class)->daySchedulingBlockReason($salon, $staff, $ymd) !== null) {
                    continue;
                }

                $staffStartTime = substr($staff->start_time ?? '09:00', 0, 5);
                $staffEndTime   = substr($staff->end_time   ?? '18:00', 0, 5);

                try {
                    $staffStart = Carbon::createFromFormat('Y-m-d H:i', $ymd . ' ' . $staffStartTime, $tz);
                    $staffEnd   = Carbon::createFromFormat('Y-m-d H:i', $ymd . ' ' . $staffEndTime, $tz);
                } catch (\Exception $e) {
                    Log::error('Invalid staff time format', ['staff' => $staff->id, 'error' => $e->getMessage()]);
                    continue;
                }

                if ($current->lt($staffStart) || $slotEnd->gt($staffEnd)) {
                    continue;
                }

                $startsAt = $current->copy();
                $endsAt   = $current->copy()->addMinutes($duration);
                // Same validation path as confirm (AvailabilityService overlap + hours + shift).
                if ($apptAvailability->isAvailable($salonId, (int) $staff->id, $startsAt, $endsAt, null, true)) {
                    $availableStaff[] = $staff->only(['id','first_name','last_name','initials','color','role']);
                }
            }

            if (! empty($availableStaff)) {
                $slots[] = [
                    'time'            => $current->format('H:i'),
                    'datetime'        => $current->toIso8601String(),
                    'available'       => true,
                    'available_staff' => $availableStaff,
                ];
            }

            $current->addMinutes($interval);
        }

        Log::debug('Slots generated', ['count' => count($slots), 'date' => $ymd, 'tz' => $tz]);

        return $slots;
    }

    /**
     * Hold a slot for 10 minutes while the client fills in their details.
     */
    public function holdSlot(int $salonId, array $data): string
    {
        $token   = Str::uuid()->toString();
        $cacheKey = "hold:{$salonId}:{$token}";

        $serviceOptions = [];
        if (! empty($data['service_options']) && is_array($data['service_options'])) {
            foreach ($data['service_options'] as $k => $entry) {
                if (! is_array($entry)) {
                    continue;
                }
                $serviceOptions[(int) $k] = [
                    'variant' => isset($entry['variant']) && $entry['variant'] !== ''
                        ? trim((string) $entry['variant'])
                        : null,
                    'addons' => isset($entry['addons']) && is_array($entry['addons'])
                        ? array_values(array_filter(array_map('strval', $entry['addons'])))
                        : [],
                ];
            }
        }

        $salon = Salon::findOrFail($salonId);
        $ids    = array_values(array_map('intval', $data['service_ids']));
        $byId = Service::where('salon_id', $salonId)->whereIn('id', array_unique($ids))->get()->keyBy('id');
        foreach ($ids as $sid) {
            if (! $byId->has($sid)) {
                throw new \InvalidArgumentException('Invalid services for this booking.');
            }
        }

        $snapshot = Service::summarizeForAppointment($salonId, $ids, $serviceOptions);
        $startsAt = SalonTime::parseAppointmentStartsAt($salon, $data['starts_at']);
        $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);
        if ($startsAt->lte(SalonTime::now($salon))) {
            throw new \InvalidArgumentException('Please choose a time later than the current time.');
        }

        $apptSvc = app(AppointmentService::class);
        $staffId = isset($data['staff_id']) ? (int) $data['staff_id'] : null;

        if ($staffId) {
            if (! $apptSvc->isAvailable($salonId, $staffId, $startsAt, $endsAt, null, true)) {
                throw new \InvalidArgumentException('That time is no longer available. Please choose another slot.');
            }
        } else {
            $staffQuery = Staff::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salonId)
                ->onlineBookable();

            $candidates = $staffQuery->orderBy('sort_order')->orderBy('id')->get();
            $assigned   = null;
            foreach ($candidates as $s) {
                if ($apptSvc->isAvailable($salonId, (int) $s->id, $startsAt, $endsAt, null, true)) {
                    $assigned = (int) $s->id;
                    break;
                }
            }
            if ($assigned === null) {
                throw new \InvalidArgumentException('No staff available for that time. Please choose another slot.');
            }
            $data['staff_id'] = $assigned;
        }

        Cache::put($cacheKey, [
            'salon_id'         => $salonId,
            'service_ids'      => $data['service_ids'],
            'service_options'  => $serviceOptions,
            'staff_id'         => $data['staff_id'] ?? null,
            'starts_at'        => $data['starts_at'],
            'token'            => $token,
        ], now()->addMinutes(10));

        return $token;
    }

    /**
     * Confirm the appointment from a hold token.
     */
    public function confirmFromHold(Salon $salon, array $data, ?Client $authenticatedClient = null): Appointment
    {
        $cacheKey = "hold:{$salon->id}:{$data['hold_token']}";
        $hold     = Cache::get($cacheKey);

        if (! $hold) {
            throw new \InvalidArgumentException('Your hold has expired. Please select a time again.');
        }

        if ($authenticatedClient) {
            if ((int) $authenticatedClient->salon_id !== (int) $salon->id) {
                throw new \InvalidArgumentException('This account does not belong to this salon.');
            }
            $client = $authenticatedClient;
        } else {
            $client = $this->findOrCreateClient($salon->id, $data);
            if ($client->wasRecentlyCreated) {
                app(NotificationService::class)->notifyTenantNewClientRegistered($salon, $client);
            }
        }

        // Resolve staff
        $staffId = $hold['staff_id'];
        if (! $staffId) {
            // Auto-assign first available bookable staff (any service).
            $ids = array_map('intval', $hold['service_ids']);
            $snapshot = Service::summarizeForAppointment($salon->id, $ids, $hold['service_options'] ?? []);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $hold['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

            $staff = Staff::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salon->id)
                ->onlineBookable()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($staff as $s) {
                $apptSvc = app(AppointmentService::class);
                if ($apptSvc->isAvailable($salon->id, (int) $s->id, $startsAt, $endsAt, null, true)) {
                    $staffId = $s->id;
                    break;
                }
            }

            if (! $staffId) {
                throw new \InvalidArgumentException('No staff available for that time. Please select another slot.');
            }
        }

        try {
            $appointment = app(AppointmentService::class)->create($salon->id, [
                'client_id'       => $client->id,
                'staff_id'        => $staffId,
                'service_ids'     => $hold['service_ids'],
                'service_options' => $hold['service_options'] ?? [],
                'starts_at'       => $hold['starts_at'],
                'source'          => 'online',
                'status'          => 'pending',
                'client_notes'    => $data['notes'] ?? null,
            ], [
                'enforce_staff_services' => false,
            ]);
        } catch (AvailabilityRejectedException $e) {
            throw new \InvalidArgumentException($e->result->firstMessage());
        }

        // Update client marketing consent
        if (isset($data['marketing_consent'])) {
            $client->update(['marketing_consent' => $data['marketing_consent']]);
        }

        // Clear the hold
        Cache::forget($cacheKey);

        return $appointment;
    }

    /**
     * Reschedule a confirmed appointment.
     */
    public function reschedule(Appointment $appointment, array $data): Appointment
    {
        return app(AppointmentService::class)->rescheduleForOnlineBooking($appointment, $data);
    }

    /* ── Private helpers ──────────────────────────────────────────────────── */

    private function findOrCreateClient(int $salonId, array $data): Client
    {
        $first = trim((string) ($data['first_name'] ?? ''));
        $last  = trim((string) ($data['last_name'] ?? ''));
        $email = $this->normalizeClientEmail($data['email'] ?? null);
        $phone = trim((string) ($data['phone'] ?? ''));

        $client = null;

        if ($email !== '') {
            $client = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salonId)
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();
        }

        if (! $client && $phone !== '') {
            $byPhone = $this->findClientByPhone($salonId, $phone);
            if ($byPhone && $this->isSameBookingClient($byPhone, $first, $last, $email)) {
                $client = $byPhone;
            }
        }

        if ($client) {
            $updates = [];
            if (empty($client->email) && $email !== '') {
                $updates['email'] = $email;
            }
            if (empty($client->phone) && $phone !== '') {
                $updates['phone'] = $phone;
            }
            if ($first !== '' && trim((string) $client->first_name) === '') {
                $updates['first_name'] = $first;
            }
            if ($last !== '' && trim((string) $client->last_name) === '') {
                $updates['last_name'] = $last;
            }
            if ($updates !== []) {
                $client->update($updates);
                $client->refresh();
            }

            return $client;
        }

        $colors = ['#C4556B','#B8943A','#5A8A72','#3B82F6','#8B5CF6','#D97706','#059669'];

        return Client::create([
            'salon_id'          => $salonId,
            'first_name'        => $first !== '' ? $first : 'Guest',
            'last_name'         => $last,
            'email'             => $email !== '' ? $email : null,
            'phone'             => $phone !== '' ? $phone : null,
            'marketing_consent' => $data['marketing_consent'] ?? false,
            'email_consent'     => true,
            'sms_consent'       => true,
            'source'            => 'online_booking',
            'color'             => $colors[array_rand($colors)],
        ]);
    }

    private function findClientByPhone(int $salonId, string $phone): ?Client
    {
        $digits = $this->phoneDigits($phone);
        if ($digits === '') {
            return null;
        }

        $exact = Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('phone', $phone)
            ->first();
        if ($exact) {
            return $exact;
        }

        return Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get(['id', 'salon_id', 'first_name', 'last_name', 'email', 'phone'])
            ->first(function (Client $client) use ($digits) {
                $existing = $this->phoneDigits((string) $client->phone);
                if ($existing === $digits) {
                    return true;
                }
                $len = min(10, strlen($digits), strlen($existing));

                return $len >= 10 && substr($existing, -10) === substr($digits, -10);
            });
    }

    /** Same person: matching email, or same phone with compatible email and the same name. */
    private function isSameBookingClient(Client $client, string $first, string $last, string $email): bool
    {
        $existingEmail = $this->normalizeClientEmail($client->email);
        if ($email !== '' && $existingEmail !== '' && $email === $existingEmail) {
            return true;
        }
        if ($email !== '' && $existingEmail !== '' && $email !== $existingEmail) {
            return false;
        }

        return $this->bookingNamesMatch($client, $first, $last);
    }

    private function bookingNamesMatch(Client $client, string $first, string $last): bool
    {
        $incoming = $this->nameKey($first, $last);
        $existing = $this->nameKey((string) $client->first_name, (string) $client->last_name);

        return $incoming !== '' && $incoming === $existing;
    }

    private function nameKey(string $first, string $last): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($first.' '.$last)) ?? '');
    }

    private function normalizeClientEmail(mixed $email): string
    {
        return strtolower(trim((string) $email));
    }

    private function phoneDigits(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }

    private function holdKeyPattern(int $salonId, string $date): string
    {
        return "hold:{$salonId}:*";
    }
}
