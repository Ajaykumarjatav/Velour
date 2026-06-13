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
        $meta = '<meta name="api-base" content="' . e($apiBase) . '">';

        if (str_contains($html, 'name="api-base"')) {
            $html = preg_replace(
                '#<meta name="api-base" content="[^"]*">#',
                $meta,
                $html
            ) ?? $html;
        } else {
            $html = str_replace('<head>', '<head>' . $meta, $html);
        }

        return response($html)->header('Content-Type', 'text/html');
    }
}
