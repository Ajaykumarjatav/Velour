<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\TenantProjectFeedback;
use App\Support\AuthPanel;
use App\Support\TenantFeedbackNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TenantFeedbackController extends Controller
{
    use ResolvesActiveSalon;

    public function status(Request $request): JsonResponse
    {
        if (! $this->shouldOfferFeedback($request)) {
            return response()->json(['eligible' => false]);
        }

        $salon = $this->activeSalon();
        $exists = TenantProjectFeedback::query()
            ->where('salon_id', $salon->id)
            ->exists();

        return response()->json([
            'eligible' => ! $exists,
            'salon_id' => $salon->id,
            'user_id' => $request->user()->id,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->shouldOfferFeedback($request)) {
            abort(403, 'Feedback is not available for this account.');
        }

        $allowedTopics = [
            'easy_to_use',
            'performance',
            'design',
            'booking',
            'reports',
            'feature_request',
        ];

        $data = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'message' => ['required', 'string', 'min:10', 'max:500'],
            'topics' => ['nullable', 'array', 'max:6'],
            'topics.*' => ['string', 'in:'.implode(',', $allowedTopics)],
        ]);

        $salon = $this->activeSalon();
        $user = $request->user();

        try {
            $feedback = DB::transaction(function () use ($salon, $user, $data) {
                if (TenantProjectFeedback::query()->where('salon_id', $salon->id)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages([
                        'message' => 'Feedback has already been submitted for this store.',
                    ]);
                }

                return TenantProjectFeedback::query()->create([
                    'salon_id' => $salon->id,
                    'user_id' => $user->id,
                    'rating' => $data['rating'] ?? null,
                    'topics' => array_values(array_unique($data['topics'] ?? [])),
                    'message' => trim($data['message']),
                    'status' => 'new',
                ]);
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Unique constraint race
            throw ValidationException::withMessages([
                'message' => 'Feedback has already been submitted for this store.',
            ]);
        }

        TenantFeedbackNotifier::notify($feedback);

        return response()->json([
            'ok' => true,
            'message' => 'Thank you — your feedback was submitted.',
        ]);
    }

    private function shouldOfferFeedback(Request $request): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        if (AuthPanel::isAdminStoreBrowse()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return false;
        }

        // Salon admin panel only (exclude stylist-scoped staff dashboards).
        return AuthPanel::typeFor($user) === AuthPanel::TENANT;
    }
}
