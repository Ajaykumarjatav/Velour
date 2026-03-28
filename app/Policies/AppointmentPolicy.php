<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppointmentPolicy
{
    use HandlesAuthorization;

    /**
     * Super-admins bypass all checks via the Gate::before hook in AppServiceProvider.
     */

    private function salonId(User $user): ?int
    {
        return $user->salons()->value('id') ?? $user->staffProfile?->salon_id;
    }

    private function isManagerOrAbove(User $user): bool
    {
        return $user->hasAnyRole(['tenant_admin', 'manager'])
            || $user->salons()->exists();
    }

    public function viewAny(User $user): bool
    {
        return true; // All authenticated salon users can view the calendar
    }

    public function view(User $user, Appointment $appointment): bool
    {
        // Stylists can only see their own appointments
        if ($user->hasRole('stylist')) {
            return $appointment->staff_id === $user->staffProfile?->id
                && $appointment->salon_id === $this->salonId($user);
        }

        return $appointment->salon_id === $this->salonId($user);
    }

    public function create(User $user): bool
    {
        return true; // Any salon user can create appointments
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($appointment->salon_id !== $this->salonId($user)) return false;

        // Stylists can update their own appointments only
        if ($user->hasRole('stylist')) {
            return $appointment->staff_id === $user->staffProfile?->id;
        }

        return true;
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->update($user, $appointment);
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $appointment->salon_id === $this->salonId($user)
            && $this->isManagerOrAbove($user);
    }

    public function sendReminder(User $user, Appointment $appointment): bool
    {
        return $appointment->salon_id === $this->salonId($user)
            && $user->hasAnyRole(['tenant_admin', 'manager', 'receptionist'])
                || $user->salons()->exists();
    }
}
