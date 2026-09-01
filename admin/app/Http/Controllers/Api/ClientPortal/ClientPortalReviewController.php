<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Review;
use App\Scopes\TenantScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPortalReviewController extends Controller
{
    public function store(Request $request, string $salonSlug, string $ref): JsonResponse
    {
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        /** @var Client $client */
        $client = $request->user();
        $salon = $request->attributes->get('salon');

        $appointment = Appointment::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('client_id', $client->id)
            ->where('reference', $ref)
            ->where('status', 'completed')
            ->firstOrFail();

        if (Review::where('appointment_id', $appointment->id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this appointment.'], 422);
        }

        $review = Review::create([
            'salon_id'       => $appointment->salon_id,
            'client_id'      => $client->id,
            'appointment_id' => $appointment->id,
            'staff_id'       => $appointment->staff_id,
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
            'source'         => 'client_portal',
            'reviewer_name'  => $client->full_name,
            'is_verified'    => true,
            'is_public'      => true,
        ]);

        return response()->json([
            'message' => 'Thank you for your review!',
            'review'  => $review->only(['id', 'rating', 'comment', 'created_at']),
        ], 201);
    }

    public function update(Request $request, string $salonSlug, int $id): JsonResponse
    {
        $data = $request->validate([
            'rating'  => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        /** @var Client $client */
        $client = $request->user();
        $salon = $request->attributes->get('salon');

        $review = Review::where('salon_id', $salon->id)
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $review->update($data);

        return response()->json([
            'message' => 'Review updated.',
            'review'  => $review->only(['id', 'rating', 'comment', 'created_at']),
        ]);
    }

    public function destroy(Request $request, string $salonSlug, int $id): JsonResponse
    {
        /** @var Client $client */
        $client = $request->user();
        $salon = $request->attributes->get('salon');

        $review = Review::where('salon_id', $salon->id)
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $review->delete();

        return response()->json(['message' => 'Review removed.']);
    }
}
