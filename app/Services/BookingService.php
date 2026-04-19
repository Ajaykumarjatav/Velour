<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Client;
use App\Models\StaffLeaveRequest;
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
            ->where('is_active', true)
            ->where('bookable_online', true)
            ->where(function ($q) use ($dowTag) {
                $q->whereNull('working_days')
                    ->orWhereJsonContains('working_days', $dowTag);
            });

        if ($staffId) {
            $staffQuery->where('id', $staffId);
        } else {
            foreach ($collection as $svc) {
                $staffQuery->whereHas('services', fn ($q) => $q->where('services.id', $svc->id));
            }
        }

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
            $slotEnd     = $current->copy()->addMinutes($duration);
            $availableStaff = [];

            foreach ($staffList as $staff) {
                if (StaffLeaveRequest::approvedBlockingLeaveExists($salonId, $staff->id, $ymd)) {
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

        $apptSvc = app(AppointmentService::class);
        $staffId = isset($data['staff_id']) ? (int) $data['staff_id'] : null;

        if ($staffId) {
            if (! $apptSvc->isAvailable($salonId, $staffId, $startsAt, $endsAt, null, true)) {
                throw new \InvalidArgumentException('That time is no longer available. Please choose another slot.');
            }
        } else {
            $staffQuery = Staff::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salonId)
                ->where('is_active', true)
                ->where('bookable_online', true);

            foreach ($ids as $sid) {
                $staffQuery->whereHas('services', fn ($q) => $q->where('services.id', (int) $sid));
            }

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
    public function confirmFromHold(Salon $salon, array $data): Appointment
    {
        $cacheKey = "hold:{$salon->id}:{$data['hold_token']}";
        $hold     = Cache::get($cacheKey);

        if (! $hold) {
            throw new \InvalidArgumentException('Your hold has expired. Please select a time again.');
        }

        // Find or create client
        $client = $this->findOrCreateClient($salon->id, $data);
        if ($client->wasRecentlyCreated) {
            app(NotificationService::class)->notifyTenantNewClientRegistered($salon, $client);
        }

        // Resolve staff
        $staffId = $hold['staff_id'];
        if (! $staffId) {
            // Auto-assign first available (must offer every service on the booking)
            $ids = array_map('intval', $hold['service_ids']);
            $snapshot = Service::summarizeForAppointment($salon->id, $ids, $hold['service_options'] ?? []);
            $startsAt = SalonTime::parseAppointmentStartsAt($salon, $hold['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($snapshot['total_span_minutes']);

            $staffQuery = Staff::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salon->id)
                ->where('is_active', true)
                ->where('bookable_online', true);

            foreach ($hold['service_ids'] as $sid) {
                $staffQuery->whereHas('services', fn ($q) => $q->where('services.id', (int) $sid));
            }

            $staff = $staffQuery
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
                'client_notes'    => $data['notes'] ?? null,
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
        $client = null;

        if (! empty($data['email'])) {
            $client = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salonId)->where('email', $data['email'])->first();
        }

        if (! $client && ! empty($data['phone'])) {
            $client = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $salonId)->where('phone', $data['phone'])->first();
        }

        if ($client) {
            // Update missing info
            $updates = [];
            if (empty($client->email) && !empty($data['email'])) $updates['email'] = $data['email'];
            if (empty($client->phone) && !empty($data['phone'])) $updates['phone'] = $data['phone'];
            if (! empty($updates)) $client->update($updates);
            return $client;
        }

        // Create new client
        $colors = ['#C4556B','#B8943A','#5A8A72','#3B82F6','#8B5CF6','#D97706','#059669'];

        return Client::create([
            'salon_id'          => $salonId,
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'] ?? null,
            'phone'             => $data['phone'],
            'marketing_consent' => $data['marketing_consent'] ?? false,
            'email_consent'     => true,
            'sms_consent'       => true,
            'source'            => 'online_booking',
            'color'             => $colors[array_rand($colors)],
        ]);
    }

    private function holdKeyPattern(int $salonId, string $date): string
    {
        return "hold:{$salonId}:*";
    }
}
