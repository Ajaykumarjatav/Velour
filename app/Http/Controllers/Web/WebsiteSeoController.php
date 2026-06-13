<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonSetting;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebsiteSeoController extends Controller
{
    use ResolvesActiveSalon;

    private function salon(): Salon
    {
        return $this->activeSalon();
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $websiteUrl = StorefrontUrl::website($salon);
        $bookingUrl = StorefrontUrl::booking($salon);
        $widgetUrl = rtrim(config('app.url'), '/') . '/widget/' . $salon->slug;

        $published = (bool) SalonSetting::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->where('key', 'website_published')
            ->value('value');
        $themeSlug = StorefrontTheme::forSalon($salon);
        $themeLabel = StorefrontTheme::label($themeSlug);
        $themes = StorefrontTheme::all();

        $reviewsCount = (int) Review::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_public', true)->count();
        $avgRating = (float) Review::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_public', true)->avg('rating');

        $stats = [
            'theme' => $themeLabel,
            'theme_slug' => $themeSlug,
            'domain_status' => ($salon->domain || $salon->subdomain) ? 'Connected' : 'Not connected',
            'pages' => 6,
            'mobile' => 'Optimized',
            'published' => $published,
            'reviews_count' => $reviewsCount,
            'avg_rating' => $avgRating > 0 ? round($avgRating, 1) : null,
        ];

        return view('website-seo.index', compact('salon', 'websiteUrl', 'bookingUrl', 'widgetUrl', 'stats', 'themes', 'themeSlug'));
    }

    public function updateTheme(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $allowed = array_keys(StorefrontTheme::all());

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($allowed)],
        ]);

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'website_theme'],
            ['value' => $validated['theme'], 'type' => 'string']
        );

        return redirect()
            ->route('website-seo.index')
            ->with('success', 'Website theme updated to ' . StorefrontTheme::label($validated['theme']) . '.');
    }

    public function publish(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $value = $request->boolean('published');

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'website_published'],
            ['value' => $value ? '1' : '0', 'type' => 'boolean']
        );

        return redirect()->route('website-seo.index')->with('success', $value ? 'Website published.' : 'Website unpublished.');
    }
}

