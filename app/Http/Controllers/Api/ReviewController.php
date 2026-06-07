<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Review;
use App\Models\Staff;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with(['client','staff'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->when($request->rating, fn($q) => $q->where('rating', $request->rating))
            ->when($request->source, fn($q) => $q->where('source', $request->source))
            ->when($request->unanswered, fn($q) => $q->whereNull('owner_reply'))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $avg = Review::where('salon_id', $request->attributes->get('salon_id'))->avg('rating');

        return response()->json(['reviews' => $reviews, 'avg_rating' => round($avg ?? 0, 1)]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $review = Review::with(['client','staff','appointment'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);
        return response()->json($review);
    }

    public function reply(Request $request, int $id): JsonResponse
    {
        $data   = $request->validate(['reply' => 'required|string|max:1000']);
        $review = Review::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $review->update(['owner_reply' => $data['reply'], 'replied_at' => now()]);
        return response()->json(['message' => 'Reply posted.', 'review' => $review]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = Review::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $review->update(['is_public' => false]);
        return response()->json(['message' => 'Review hidden.']);
    }

    public function requestReview(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        if ($appointment->status !== 'completed') {
            return response()->json(['message' => 'Can only request reviews for completed appointments.'], 422);
        }
        $this->notificationService->requestReview($appointment);
        $appointment->update(['review_requested' => true]);
        return response()->json(['message' => 'Review request sent.']);
    }

    // Public: GET /api/v1/salons/{slug}/reviews
    public function public(Request $request, string $salonSlug): JsonResponse
    {
        $salon = \App\Models\Salon::where('slug', $salonSlug)->firstOrFail();
        $reviews = Review::with(['client:id,first_name'])
            ->where('salon_id', $salon->id)
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json([
            'avg_rating' => round($reviews->avg('rating') ?? 0, 1),
            'total'      => $reviews->count(),
            'reviews'    => $reviews,
        ]);
    }

    // Public: POST /api/v1/reviews/submit
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'appointment_ref' => 'required|string',
            'rating'          => 'required|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:2000',
        ]);

        $appointment = Appointment::where('reference', $data['appointment_ref'])
            ->where('status', 'completed')
            ->firstOrFail();

        $existing = Review::where('appointment_id', $appointment->id)->exists();
        if ($existing) {
            return response()->json(['message' => 'Review already submitted.'], 422);
        }

        $review = Review::create([
            'salon_id'       => $appointment->salon_id,
            'client_id'      => $appointment->client_id,
            'appointment_id' => $appointment->id,
            'staff_id'       => $appointment->staff_id,
            'rating'         => $data['rating'],
            'comment'        => $data['comment'] ?? null,
            'source'         => 'velour',
            'is_verified'    => true,
        ]);

        return response()->json(['message' => 'Thank you for your review!', 'review' => $review], 201);
    }
}
