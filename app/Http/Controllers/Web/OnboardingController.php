<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * OnboardingController
 *
 * AUDIT FIX — SaaS-Specific Essential: First-time setup wizard.
 *
 * Routes: /onboarding/*
 * Middleware: auth, verified
 *
 * 6-step wizard:
 *   1. welcome         — welcome screen + trial info
 *   2. salon-profile   — name, address, phone, timezone
 *   3. opening-hours   — schedule builder
 *   4. first-service   — add first service + category
 *   5. invite-staff    — invite team members
 *   6. go-live         — summary + booking link
 */
class OnboardingController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $salon   = Salon::where('owner_id', $user->id)->first();
        if (! $salon) {
            return redirect()->route('dashboard');
        }
        $this->syncProgressFromData($user->id, $salon);
        $progress = $this->getProgress($user->id, $salon->id);

        // If onboarding complete, redirect to dashboard
        if ($progress && $progress->completed) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index', compact('user', 'salon', 'progress'));
    }

    public function step(Request $request, string $step)
    {
        $user  = Auth::user();
        $salon = Salon::where('owner_id', $user->id)->firstOrFail();
        $allowed = ['salon-profile', 'opening-hours', 'first-service', 'invite-staff'];
        abort_unless(in_array($step, $allowed, true), 404);

        $this->syncProgressFromData($user->id, $salon);
        $progress = $this->getProgress($user->id, $salon->id);

        $meta = $this->stepMeta($step, $salon, $progress);

        return view("onboarding.steps.{$step}", compact('user', 'salon', 'meta', 'progress'));
    }

    public function completeStep(Request $request, string $step)
    {
        $user  = Auth::user();
        $salon = Salon::where('owner_id', $user->id)->firstOrFail();
        $allowed = ['salon-profile', 'opening-hours', 'first-service', 'invite-staff'];
        abort_unless(in_array($step, $allowed, true), 404);

        $this->syncProgressFromData($user->id, $salon);
        $progress = $this->getProgress($user->id, $salon->id);
        $column = $this->stepColumn($step);

        if (! $column || ! $progress?->{$column}) {
            $meta = $this->stepMeta($step, $salon, $progress);
            return redirect($meta['action_url'])
                ->with('warning', 'Complete this setup step first, then continue onboarding.');
        }

        // Check if all steps complete
        $progress = $this->getProgress($user->id, $salon->id);
        $allDone  = $progress &&
                    $progress->step_salon_profile &&
                    $progress->step_first_service &&
                    $progress->step_first_staff;

        if ($allDone) {
            DB::table('onboarding_progress')
                ->where('user_id', $user->id)
                ->update(['completed' => true, 'completed_at' => now()]);

            return redirect()->route('onboarding.complete');
        }

        return redirect()->route('onboarding.step', $this->nextStep($step));
    }

    public function complete()
    {
        $user  = Auth::user();
        $salon = Salon::where('owner_id', $user->id)->first();

        return view('onboarding.complete', compact('user', 'salon'));
    }

    public function skip()
    {
        $user  = Auth::user();
        $salon = Salon::where('owner_id', $user->id)->first();

        if ($salon) {
            DB::table('onboarding_progress')
                ->updateOrInsert(
                    ['user_id' => $user->id, 'salon_id' => $salon->id],
                    ['completed' => true, 'completed_at' => now()]
                );
        }

        return redirect()->route('dashboard');
    }

    private function getProgress(int $userId, ?int $salonId)
    {
        if (! $salonId) return null;
        return DB::table('onboarding_progress')
            ->where('user_id', $userId)
            ->where('salon_id', $salonId)
            ->first();
    }

    private function stepColumn(string $step): ?string
    {
        return match($step) {
            'salon-profile'  => 'step_salon_profile',
            'opening-hours'  => 'step_opening_hours',
            'first-service'  => 'step_first_service',
            'invite-staff'   => 'step_first_staff',
            default          => null,
        };
    }

    private function nextStep(string $current): string
    {
        $steps = ['salon-profile', 'opening-hours', 'first-service', 'invite-staff'];
        $idx   = array_search($current, $steps);
        return $steps[$idx + 1] ?? 'complete';
    }

    private function syncProgressFromData(int $userId, Salon $salon): void
    {
        $hasSalonProfile = ! empty($salon->name) && (! empty($salon->phone) || ! empty($salon->address_line1));
        $hasOpeningHours = ! empty($salon->opening_hours);
        $hasFirstService = Service::query()
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->exists();
        $hasFirstStaff = Staff::query()
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->exists();

        $allDone = $hasSalonProfile && $hasOpeningHours && $hasFirstService && $hasFirstStaff;

        DB::table('onboarding_progress')->updateOrInsert(
            ['user_id' => $userId, 'salon_id' => $salon->id],
            [
                'step_salon_profile' => $hasSalonProfile,
                'step_opening_hours' => $hasOpeningHours,
                'step_first_service' => $hasFirstService,
                'step_first_staff' => $hasFirstStaff,
                'completed' => $allDone,
                'completed_at' => $allDone ? now() : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function stepMeta(string $step, Salon $salon, ?object $progress): array
    {
        return match ($step) {
            'salon-profile' => [
                'title' => 'Salon profile',
                'description' => 'Add core business details so customers can identify and contact your salon.',
                'done' => (bool) ($progress?->step_salon_profile ?? false),
                'action_url' => route('settings.index', ['tab' => 'salon', 'return_to' => route('onboarding.step', ['step' => 'salon-profile'])]),
                'action_label' => 'Open Business Settings',
            ],
            'opening-hours' => [
                'title' => 'Opening hours',
                'description' => 'Set opening hours so the booking URL can generate valid slots.',
                'done' => (bool) ($progress?->step_opening_hours ?? false),
                'action_url' => route('settings.index', ['tab' => 'hours', 'return_to' => route('onboarding.step', ['step' => 'opening-hours'])]),
                'action_label' => 'Set Opening Hours',
            ],
            'first-service' => [
                'title' => 'Service selection',
                'description' => 'Select business type, categories, and configure at least one active service.',
                'done' => (bool) ($progress?->step_first_service ?? false),
                'action_url' => route('settings.index', ['tab' => 'services', 'return_to' => route('onboarding.step', ['step' => 'first-service'])]),
                'action_label' => 'Configure Services',
            ],
            'invite-staff' => [
                'title' => 'Team setup',
                'description' => 'Add at least one active team member so clients can be assigned correctly.',
                'done' => (bool) ($progress?->step_first_staff ?? false),
                'action_url' => route('settings.index', ['tab' => 'profile', 'return_to' => route('onboarding.step', ['step' => 'invite-staff'])]),
                'action_label' => 'Add Team Member',
            ],
            default => [
                'title' => 'Setup',
                'description' => 'Complete your setup steps.',
                'done' => false,
                'action_url' => route('settings.index'),
                'action_label' => 'Open Settings',
            ],
        };
    }
}
