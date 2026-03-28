<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
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
        $progress = $this->getProgress($user->id, $salon?->id);

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

        return view("onboarding.steps.{$step}", compact('user', 'salon'));
    }

    public function completeStep(Request $request, string $step)
    {
        $user  = Auth::user();
        $salon = Salon::where('owner_id', $user->id)->firstOrFail();

        $column = $this->stepColumn($step);
        if ($column) {
            DB::table('onboarding_progress')
                ->updateOrInsert(
                    ['user_id' => $user->id, 'salon_id' => $salon->id],
                    [$column => true, 'updated_at' => now()]
                );
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
}
