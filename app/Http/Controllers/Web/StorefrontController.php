<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Support\PublicSalonAccess;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
    public function show(string $slug): Response
    {
        $salon = Salon::query()->where('slug', $slug)->first();

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
        $index = StorefrontTheme::buildPath($theme);

        if (! is_file($index)) {
            $fallback = public_path('website/index.html');
            if (is_file($fallback)) {
                return $this->renderStorefrontHtml((string) file_get_contents($fallback), $theme);
            }

            return response(
                '<!DOCTYPE html><html><body style="font-family:sans-serif;padding:2rem">'
                . '<h1>Salon website not built</h1>'
                . '<p>Theme: <code>' . e($theme) . '</code></p>'
                . '<p>Run: <code>cd salon-website &amp;&amp; npm run build:all</code></p>'
                . '<p>Or set <code>SALON_WEBSITE_DEV_URL=http://localhost:5173</code> in .env for dev preview.</p>'
                . '</body></html>',
                503
            )->header('Content-Type', 'text/html');
        }

        return $this->renderStorefrontHtml((string) file_get_contents($index), $theme);
    }

    /** Serve built theme JS/CSS when the web server does not map /website/* as static files. */
    public function themeAsset(string $theme, string $asset): Response
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $asset = str_replace(['..', '\\'], '', $asset);
        $path = public_path('website/'.$theme.'/assets/'.$asset);

        abort_unless(is_file($path), 404, 'Theme asset missing. Run npm run build:all in salon-website and deploy admin/public/website.');

        return response()->file($path, ['Cache-Control' => 'public, max-age=604800']);
    }

    /**
     * Rewrite baked Vite asset paths and inject API base so production works on any APP_URL.
     */
    private function renderStorefrontHtml(string $html, string $theme): Response
    {
        $apiBase = StorefrontUrl::laravelBaseUrl();
        $assetBase = StorefrontUrl::themeAssetBase($theme);
        $assetPath = parse_url($assetBase, PHP_URL_PATH) ?: StorefrontTheme::assetBase($theme);
        if (! str_ends_with((string) $assetPath, '/')) {
            $assetPath .= '/';
        }

        // Baked builds may use /vellor/admin/website/... or /website/... — normalize for this server.
        $html = preg_replace(
            '#(?:https?://[^"\'\s]+)?/?[^"\'\s]*/website/' . preg_quote($theme, '#') . '/#',
            $assetPath,
            $html
        ) ?? $html;

        $html = $this->upsertMeta($html, 'api-base', $apiBase);
        $html = $this->upsertMeta($html, 'storefront-asset-base', $assetBase);
        $html = $this->injectBootScript($html, $apiBase);

        return response($html)->header('Content-Type', 'text/html');
    }

    /** Ensure API calls hit Laravel /admin even before theme bundles are rebuilt. */
    private function injectBootScript(string $html, string $apiBase): string
    {
        $json = json_encode($apiBase, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $script = '<script>(function(){var b='.$json.';var o=window.fetch;window.fetch=function(i,n){var u=typeof i==="string"?i:(i&&i.url?i.url:"");if(u&&u.indexOf("/api/")!==-1&&u.indexOf(b)!==0){var p=u.replace(/^https?:\\/\\/[^/]+/,"");if(p.charAt(0)==="/"){return o(b+p,n)}}return o(i,n)};})();</script>';

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $script.'</head>', $html);
        }

        return $script.$html;
    }

    private function upsertMeta(string $html, string $name, string $content): string
    {
        $tag = '<meta name="' . $name . '" content="' . e($content) . '">';

        if (str_contains($html, 'name="' . $name . '"')) {
            return preg_replace(
                '#<meta name="' . preg_quote($name, '#') . '" content="[^"]*">#',
                $tag,
                $html
            ) ?? $html;
        }

        return str_replace('<head>', '<head>' . $tag, $html);
    }
}
