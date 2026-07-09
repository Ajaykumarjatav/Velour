<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SalonWebsitePayloadService;
use App\Support\PublicSalonAccess;
use App\Support\StorefrontAssets;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function show(string $slug, ?string $path = null): Response|View
    {
        $salon = PublicSalonAccess::findBySlug($slug);

        if (! $salon || ! PublicSalonAccess::isAccessible($salon)) {
            if ($salon) {
                return response()
                    ->view('booking.unavailable', [
                        'salon'   => $salon,
                        'reasons' => PublicSalonAccess::unavailableReasons($salon),
                    ], 503)
                    ->header('Content-Type', 'text/html');
            }

            abort(404);
        }

        $theme = StorefrontTheme::forSalon($salon);

        if (! StorefrontTheme::hasBladeView($theme)) {
            return response(
                '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem">'
                . '<h1>Salon website theme not ready</h1>'
                . '<p>Theme: <code>' . e($theme) . '</code></p>'
                . '<p>Missing Blade view. Run: <code>php scripts/sync-storefront-assets.php</code></p>'
                . '</body></html>',
                503
            )->header('Content-Type', 'text/html');
        }

        $payload = app(SalonWebsitePayloadService::class)->build($salon);

        return view(StorefrontTheme::viewName($theme), [
            'salon'    => $salon,
            'theme'    => $theme,
            'data'     => $payload,
            'apiBase'  => StorefrontUrl::laravelBaseUrl(),
            'asset'    => fn (string $file) => StorefrontAssets::assetUrl($theme, $file),
        ]);
    }

    /** Serve theme static assets (images, theme.css). */
    public function themeAsset(string $theme, string $asset): Response
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $asset = str_replace(['..', '\\'], '', $asset);

        $path = StorefrontTheme::staticAssetPath($theme, $asset);
        if ($path === null) {
            $path = public_path('storefront/'.$theme.'/'.$asset);
        }

        abort_unless(is_file($path), 404, 'Theme asset missing. Run: php scripts/sync-storefront-assets.php');

        $headers = ['Cache-Control' => 'public, max-age=604800'];
        if (str_ends_with(strtolower($path), '.css')) {
            $headers['Content-Type'] = 'text/css';
        }

        return response()->file($path, $headers);
    }
}
