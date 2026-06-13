<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Support\StorefrontTheme;
use Illuminate\Http\Response;

class StorefrontController extends Controller
{
    public function show(string $slug): Response
    {
        $salon = Salon::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

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

    /**
     * Rewrite baked Vite asset paths and inject API base so production works on any APP_URL.
     */
    private function renderStorefrontHtml(string $html, string $theme): Response
    {
        $assetPath = parse_url(asset('website/' . $theme . '/'), PHP_URL_PATH) ?: StorefrontTheme::assetBase($theme);
        $assetPath = '/' . trim((string) $assetPath, '/') . '/';

        $html = preg_replace(
            '#(?:https?://[^"\'\s]+)?/?[^"\'\s]*/website/' . preg_quote($theme, '#') . '/#',
            $assetPath,
            $html
        ) ?? $html;

        $apiBase = rtrim((string) config('app.url'), '/');
        $assetBase = rtrim(asset('website/' . $theme . '/'), '/') . '/';

        $html = $this->upsertMeta($html, 'api-base', $apiBase);
        $html = $this->upsertMeta($html, 'storefront-asset-base', $assetBase);

        return response($html)->header('Content-Type', 'text/html');
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
