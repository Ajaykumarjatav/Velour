<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebsiteSeoController extends Controller
{
    private function salon(): Salon
    {
        $user = Auth::user();
        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0 ? $user->salons()->where('id', $activeSalonId)->first() : null;
        return $salon ?: $user->salons()->firstOrFail();
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $bookingUrl = rtrim(config('app.url'), '/') . '/book/' . $salon->slug;
        $widgetUrl = rtrim(config('app.url'), '/') . '/widget/' . $salon->slug;

        $published = (bool) SalonSetting::where('salon_id', $salon->id)
            ->where('key', 'website_published')
            ->value('value');
        $theme = SalonSetting::where('salon_id', $salon->id)->where('key', 'website_theme')->value('value') ?: 'Glow Rose';

        $reviewsCount = (int) Review::where('salon_id', $salon->id)->where('is_public', true)->count();
        $avgRating = (float) Review::where('salon_id', $salon->id)->where('is_public', true)->avg('rating');

        $stats = [
            'theme' => $theme,
            'domain_status' => ($salon->domain || $salon->subdomain) ? 'Connected' : 'Not connected',
            'pages' => 6,
            'mobile' => 'Optimized',
            'published' => $published,
            'reviews_count' => $reviewsCount,
            'avg_rating' => $avgRating > 0 ? round($avgRating, 1) : null,
        ];

        return view('website-seo.index', compact('salon', 'bookingUrl', 'widgetUrl', 'stats'));
    }

    public function publish(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $value = $request->boolean('published');

        SalonSetting::updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'website_published'],
            ['value' => $value ? '1' : '0', 'type' => 'boolean']
        );

        return redirect()->route('website-seo.index')->with('success', $value ? 'Website published.' : 'Website unpublished.');
    }
}

