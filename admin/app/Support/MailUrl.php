<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\User;

/**
 * Absolute links for outbound email — always derived from the current
 * environment's APP_URL (and optional APP_FRONTEND_URL), so local / staging /
 * production mails never mix hosts.
 */
final class MailUrl
{
    /** Public site root for this environment (no trailing slash). */
    public static function publicRoot(): string
    {
        $configured = trim((string) config('app.frontend_url', ''));
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        return StorefrontUrl::publicAppUrl();
    }

    /** Salon admin panel home for this environment. */
    public static function dashboard(?Salon $salon = null, ?User $user = null): string
    {
        if ($salon) {
            return route('dashboard', ['store' => SalonUrl::key($salon)]);
        }

        if ($user) {
            return SalonUrl::dashboardUrl($user);
        }

        return rtrim((string) config('app.url'), '/');
    }

    /** Public online-booking link for the salon (never Vite/dev overrides). */
    public static function booking(Salon $salon): string
    {
        return self::publicRoot().'/s/'.$salon->slug.'#book';
    }

    /** Client-facing appointment / manage link. */
    public static function clientAppointment(Salon $salon, Appointment|string $appointmentOrRef): string
    {
        $ref = $appointmentOrRef instanceof Appointment
            ? (string) $appointmentOrRef->reference
            : (string) $appointmentOrRef;

        return self::publicRoot().'/s/'.$salon->slug.'#appointment/'.rawurlencode($ref);
    }

    /** Tenant panel appointment detail. */
    public static function tenantAppointment(Salon $salon, Appointment|int|string $appointment): string
    {
        $id = $appointment instanceof Appointment ? $appointment->id : $appointment;

        return route('appointments.show', [
            'store' => SalonUrl::key($salon),
            'appointment' => $id,
        ]);
    }

    /** Tenant appointments list. */
    public static function tenantAppointments(Salon $salon): string
    {
        return route('appointments.index', ['store' => SalonUrl::key($salon)]);
    }

    /** Billing plans page for the salon. */
    public static function billingPlans(Salon $salon): string
    {
        return route('billing.plans', ['store' => SalonUrl::key($salon)]);
    }

    /** Billing plans for a user (queued mail — no session/auth). */
    public static function billingPlansForUser(User $user): string
    {
        $key = SalonUrl::keyForUser($user);
        if ($key) {
            return route('billing.plans', ['store' => $key]);
        }

        return self::dashboard(user: $user);
    }

    /** Customer billing portal for a user. */
    public static function billingPortalForUser(User $user): string
    {
        $key = SalonUrl::keyForUser($user);
        if ($key) {
            return route('billing.portal', ['store' => $key]);
        }

        return self::billingPlansForUser($user);
    }

    public static function login(): string
    {
        return route('login');
    }

    public static function passwordRequest(): string
    {
        return route('password.request');
    }

    public static function help(): string
    {
        return url('/help');
    }
}
