<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $rating = $request->get('rating');
        $replied = $request->get('replied');

        $query = Review::where('salon_id', $salon->id)
            ->with(['client', 'appointment'])
            ->latest();

        if ($rating) {
            $query->where('rating', $rating);
        }

        if ($replied === '1') {
            $query->whereNotNull('reply');
        } elseif ($replied === '0') {
            $query->whereNull('reply');
        }

        $reviews = $query->paginate(20)->withQueryString();

        $averageRating = Review::where('salon_id', $salon->id)->avg('rating');
        $ratingCounts  = Review::where('salon_id', $salon->id)
            ->select('rating', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return view('reviews.index', compact('salon', 'reviews', 'rating', 'replied', 'averageRating', 'ratingCounts'));
    }

    public function reply(Request $request, Review $review)
    {
        abort_unless($review->salon_id === $this->salon()->id, 403);

        $data = $request->validate([
            'reply' => ['required', 'string', 'max:1000'],
        ]);

        $review->update([
            'reply'         => $data['reply'],
            'replied_at'    => now(),
        ]);

        return back()->with('success', 'Reply posted.');
    }
}
