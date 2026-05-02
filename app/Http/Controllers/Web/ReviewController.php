<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Review;
use App\Models\ReviewLink;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use ResolvesActiveSalon;

    public function index(Request $request)
    {
        $salon  = $this->activeSalon();
        $rating = $request->get('rating');
        $replied = $request->get('replied');

        $query = Review::where('salon_id', $salon->id)
            ->with(['client', 'appointment', 'service'])
            ->latest();

        if ($rating) {
            $query->where('rating', $rating);
        }

        if ($replied === '1') {
            $query->whereNotNull('owner_reply');
        } elseif ($replied === '0') {
            $query->whereNull('owner_reply');
        }

        $reviews = $query->paginate(20)->withQueryString();

        $averageRating = Review::where('salon_id', $salon->id)->avg('rating');
        $ratingCounts  = Review::where('salon_id', $salon->id)
            ->select('rating', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $tenantReviewLink = ReviewLink::query()->firstOrCreate(
            ['salon_id' => $salon->id, 'staff_id' => null]
        );

        $staffMembers = Staff::query()
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        $staffReviewLinks = collect();
        foreach ($staffMembers as $staff) {
            $link = ReviewLink::query()->firstOrCreate(
                ['salon_id' => $salon->id, 'staff_id' => $staff->id]
            );
            $staffReviewLinks->push([
                'staff' => $staff,
                'link' => $link,
            ]);
        }

        return view('reviews.index', compact(
            'salon',
            'reviews',
            'rating',
            'replied',
            'averageRating',
            'ratingCounts',
            'tenantReviewLink',
            'staffReviewLinks'
        ));
    }

    public function reply(Request $request, Review $review)
    {
        abort_unless($review->salon_id === $this->activeSalon()->id, 403);

        $data = $request->validate([
            'reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'owner_reply'   => $data['reply'],
            'replied_at'    => now(),
        ]);

        return back()->with('success', 'Reply posted.');
    }

    public function publicForm(string $token)
    {
        $reviewLink = ReviewLink::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->with(['salon', 'staff'])
            ->firstOrFail();

        $salon = $reviewLink->salon;
        $services = Service::query()
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->orderBy('name');

        if ($reviewLink->staff_id) {
            $staffServiceIds = $reviewLink->staff?->services()->pluck('services.id')->all() ?? [];
            $services->whereIn('id', $staffServiceIds);
        }

        return view('reviews.public-form', [
            'reviewLink' => $reviewLink,
            'salon' => $salon,
            'staff' => $reviewLink->staff,
            'services' => $services->get(['id', 'name']),
        ]);
    }

    public function submitPublicForm(Request $request, string $token)
    {
        $reviewLink = ReviewLink::query()
            ->where('token', $token)
            ->where('is_active', true)
            ->with(['salon', 'staff'])
            ->firstOrFail();

        $data = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:150'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceId = $data['service_id'] ?? null;
        if ($serviceId) {
            $serviceQuery = Service::query()
                ->where('id', $serviceId)
                ->where('salon_id', $reviewLink->salon_id)
                ->where('status', 'active');

            if ($reviewLink->staff_id) {
                $serviceQuery->whereHas('staff', fn ($q) => $q->where('staff.id', $reviewLink->staff_id));
            }
            abort_unless($serviceQuery->exists(), 422);
        }

        Review::query()->create([
            'salon_id' => $reviewLink->salon_id,
            'staff_id' => $reviewLink->staff_id,
            'service_id' => $serviceId,
            'review_link_id' => $reviewLink->id,
            'reviewer_name' => $data['reviewer_name'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'source' => 'velour',
            'is_verified' => false,
            'is_public' => true,
        ]);

        $reviewLink->update(['last_used_at' => now()]);

        return back()->with('success', 'Thanks! Your review has been submitted.');
    }
}
