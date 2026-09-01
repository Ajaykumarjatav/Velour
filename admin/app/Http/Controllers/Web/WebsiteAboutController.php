<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Salon;
use App\Support\StorefrontAbout;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use App\Support\PublicStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteAboutController extends Controller
{
    use ResolvesActiveSalon;

    private function salon(): Salon
    {
        return $this->activeSalon();
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $liveTheme = StorefrontTheme::forSalon($salon);
        $themeSlug = $this->editorTheme($request, $liveTheme);
        $themeLabel = StorefrontTheme::label($themeSlug);
        $themes = StorefrontTheme::all();
        $about = StorefrontAbout::resolve($salon, $themeSlug);
        $websiteUrl = StorefrontUrl::website($salon);
        $bodyHtml = \App\Support\AwardsHtml::forEditor(old('body', $about['body'] ?? ''));
        $gallerySlots = StorefrontAbout::gallerySlots($salon, $themeSlug);

        return view('website-about.index', compact(
            'salon',
            'about',
            'websiteUrl',
            'bodyHtml',
            'gallerySlots',
            'themeLabel',
            'themeSlug',
            'liveTheme',
            'themes'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $validated = $request->validate([
            'theme' => ['nullable', 'string', 'max:40'],
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'heading_prefix' => ['nullable', 'string', 'max:160'],
            'heading_highlight' => ['nullable', 'string', 'max:80'],
            'heading_suffix' => ['nullable', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:12000'],
            'stat_one_value' => ['nullable', 'string', 'max:40'],
            'stat_one_label' => ['nullable', 'string', 'max:80'],
            'stat_two_value' => ['nullable', 'string', 'max:40'],
            'stat_two_label' => ['nullable', 'string', 'max:80'],
        ]);

        $themeSlug = $this->editorTheme($request, StorefrontTheme::forSalon($salon));
        $defaults = StorefrontAbout::themeDefaults($themeSlug);
        $previous = StorefrontAbout::savedForTheme($salon, $themeSlug);

        $payload = [];
        foreach (StorefrontAbout::fieldKeys() as $key) {
            $value = (string) ($validated[$key] ?? '');
            if ($key !== 'body') {
                $value = trim($value);
            }
            $html = $key === 'body';
            if ($key === 'body') {
                $clean = \App\Support\AwardsHtml::sanitize($value);
                if ($clean === null && ! StorefrontAbout::isBlank($value, true)) {
                    $clean = \App\Support\AwardsHtml::sanitize(\App\Support\AwardsHtml::forEditor($value));
                }
                $value = $clean ?? '';
            }
            if (StorefrontAbout::isBlank($value, $html)) {
                $prior = (string) ($previous[$key] ?? '');
                $value = StorefrontAbout::isBlank($prior, $html)
                    ? (string) ($defaults[$key] ?? '')
                    : $prior;
            }
            $payload[$key] = $value;
        }
        $payload['gallery'] = StorefrontAbout::savedGallery($salon, $themeSlug);

        StorefrontAbout::persistTheme($salon, $themeSlug, $payload);

        return redirect()->route('website-about.index', ['theme' => $themeSlug])->with('success', 'About section updated for '.StorefrontTheme::label($themeSlug).' only.');
    }

    public function updateGallery(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $request->validate([
            'theme' => ['nullable', 'string', 'max:40'],
            'index' => ['required', 'integer', 'min:0', 'max:'.(StorefrontAbout::GALLERY_SLOTS - 1)],
            'image' => ['required', 'file', 'max:5120'],
        ]);

        $file = $request->file('image');
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return back()->withErrors(['image' => 'Use a JPG, PNG, or WebP image (max 5 MB).']);
        }

        $themeSlug = $this->editorTheme($request, StorefrontTheme::forSalon($salon));
        $index = (int) $request->input('index');
        $saved = StorefrontAbout::savedForTheme($salon, $themeSlug);
        $gallery = StorefrontAbout::savedGallery($salon, $themeSlug);

        if ($gallery[$index] !== '') {
            PublicStorage::delete($gallery[$index]);
        }

        $gallery[$index] = $file->store('salons/'.$salon->id.'/about-gallery/'.$themeSlug, 'public');
        $saved['gallery'] = array_values($gallery);

        StorefrontAbout::persistTheme($salon, $themeSlug, $saved);

        return redirect()->route('website-about.index', ['theme' => $themeSlug])->with('success', 'About image updated for '.StorefrontTheme::label($themeSlug).' only.');
    }

    public function resetGallery(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $validated = $request->validate([
            'theme' => ['nullable', 'string', 'max:40'],
            'index' => ['required', 'integer', 'min:0', 'max:'.(StorefrontAbout::GALLERY_SLOTS - 1)],
        ]);

        $themeSlug = $this->editorTheme($request, StorefrontTheme::forSalon($salon));
        $index = (int) $validated['index'];
        $saved = StorefrontAbout::savedForTheme($salon, $themeSlug);
        $gallery = StorefrontAbout::savedGallery($salon, $themeSlug);

        if ($gallery[$index] !== '') {
            PublicStorage::delete($gallery[$index]);
        }
        $gallery[$index] = '';
        $saved['gallery'] = $gallery;

        StorefrontAbout::persistTheme($salon, $themeSlug, $saved);

        return redirect()->route('website-about.index', ['theme' => $themeSlug])->with('success', 'About image reset to '.StorefrontTheme::label($themeSlug).' default.');
    }

    private function editorTheme(Request $request, string $fallback): string
    {
        $raw = (string) $request->input('theme', $request->query('theme', $fallback));
        $slug = StorefrontTheme::normalizeSlug($raw);
        $known = StorefrontTheme::all();

        return isset($known[$slug]) ? $slug : StorefrontTheme::normalizeSlug($fallback);
    }
}
