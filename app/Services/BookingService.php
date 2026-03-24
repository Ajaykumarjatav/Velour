<?php

namespace App\Services;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BookingService
{
    /**
     * Generate all available time slots for a service on a given date.
     *
     * @return array  [['time' => '09:00', 'staff' => [...], 'available' => true], ...]
     */
    public function getAvailableSlots(
        int     $salonId,
        Service $service,
        Carbon  $date,
        ?int    $staffId = null
    ): array {
        $salon        = Salon::findOrFail($salonId);
        $duration     = $service->duration_minutes + $service->buffer_minutes;
        $openingHours = $salon->opening_hours ?? [];
        $dayName      = $date->format('l');           // "Monday"
        $dayConfig    = $openingHours[$dayName] ?? null;

        if (! $dayConfig || ! ($dayConfig['open'] ?? true)) {
            return [];
        }

        $open  = Carbon::parse($date->toDateString() . ' ' . ($dayConfig['start'] ?? '09:00'));
        $close = Carbon::parse($date->toDateString() . ' ' . ($dayConfig['end']   ?? '19:00'));

        // Get relevant staff
        $staffQuery = Staff::where('salon_id', $salonId)
            ->where('is_active', true)
            ->where('bookable_online', true)
            ->whereJsonContains('working_days', $date->format('D'));   // "Mon"

        if ($staffId) {
            $staffQuery->where('id', $staffId);
        } else {
            // Only staff who can perform this service
            $staffQuery->whereHas('services', fn($q) => $q->where('service_id', $service->id));
        }

        $staffList = $staffQuery->get();

        if ($staffList->isEmpty()) {
            return [];
        }

        // Load existing bookings for the day
        $existingAppts = Appointment::where('salon_id', $salonId)
            ->whereDate('starts_at', $date->toDateString())
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get(['staff_id', 'starts_at', 'ends_at']);

        // Also factor in holds
        $holdKeys = Cache::getMultiple(
            $this->holdKeyPattern($salonId, $date->toDateString()),
            []
        );

        $slots = [];
        $interval  = 15; // 15-minute slot intervals
        $current   = $open->copy();

        while ($current->copy()->addMinutes($duration)->lte($close)) {
            $slotEnd     = $current->copy()->addMinutes($duration);
            $availableStaff = [];

            foreach ($staffList as $staff) {
                // Check start/end match staff hours
                $staffStart = Carbon::parse($date->toDateString() . ' ' . ($staff->start_time ?? '09:00'));
                $staffEnd   = Carbon::parse($date->toDateString() . ' ' . ($staff->end_time   ?? '18:00'));

                if ($current->lt($staffStart) || $slotEnd->gt($staffEnd)) {
                    continue;
                }

                // Check for conflicts with existing appointments
                $conflictExists = $existingAppts
                    ->where('staff_id', $staff->id)
                    ->first(fn($a) =>
                        Carbon::parse($a->starts_at)->lt($slotEnd) &&
                        Carbon::parse($a->ends_at)->gt($current)
                    );

                if (! $conflictExists) {
                    $availableStaff[] = $staff->only(['id','first_name','last_name','initials','color','avatar']);
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

        return $slots;
    }

    /**
     * Hold a slot for 10 minutes while the client fills in their details.
     */
    public function holdSlot(int $salonId, array $data): string
    {
        $token   = Str::uuid()->toString();
        $cacheKey = "hold:{$salonId}:{$token}";

        Cache::put($cacheKey, [
            'salon_id'    => $salonId,
            'service_ids' => $data['service_ids'],
            'staff_id'    => $data['staff_id'] ?? null,
            'starts_at'   => $data['starts_at'],
            'token'       => $token,
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

        // Resolve staff
        $staffId = $hold['staff_id'];
        if (! $staffId) {
            // Auto-assign first available
            $services = Service::whereIn('id', $hold['service_ids'])->where('salon_id', $salon->id)->get();
            $duration = $services->sum('duration_minutes') + $services->sum('buffer_minutes');
            $startsAt = Carbon::parse($hold['starts_at']);
            $endsAt   = $startsAt->copy()->addMinutes($duration);

            $staff = Staff::where('salon_id', $salon->id)
                ->where('is_active', true)
                ->where('bookable_online', true)
                ->whereHas('services', fn($q) => $q->whereIn('service_id', $hold['service_ids']))
                ->get();

            foreach ($staff as $s) {
                $apptSvc = app(AppointmentService::class);
                if ($apptSvc->isAvailable($s->id, $startsAt, $endsAt)) {
                    $staffId = $s->id;
                    break;
                }
            }

            if (! $staffId) {
                throw new \InvalidArgumentException('No staff available for that time. Please select another slot.');
            }
        }

        $appointment = app(AppointmentService::class)->create($salon->id, [
            'client_id'    => $client->id,
            'staff_id'     => $staffId,
            'service_ids'  => $hold['service_ids'],
            'starts_at'    => $hold['starts_at'],
            'source'       => 'online',
            'client_notes' => $data['notes'] ?? null,
            'deposit_required' => $salon->deposit_required,
            'stripe_payment_intent_id' => $data['stripe_payment_intent_id'] ?? null,
        ]);

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
        return app(AppointmentService::class)->reschedule($appointment, $data);
    }

    /* ── Private helpers ──────────────────────────────────────────────────── */

    private function findOrCreateClient(int $salonId, array $data): Client
    {
        // Look for existing client by email or phone
        $client = null;

        if (! empty($data['email'])) {
            $client = Client::where('salon_id', $salonId)->where('email', $data['email'])->first();
        }

        if (! $client && ! empty($data['phone'])) {
            $client = Client::where('salon_id', $salonId)->where('phone', $data['phone'])->first();
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
