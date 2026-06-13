<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Support\StorefrontTheme;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorefrontController extends Controller
{
    public function show(string $slug): Response|BinaryFileResponse
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
                return response()->file($fallback);
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

        return response()->file($index);
    }
}
