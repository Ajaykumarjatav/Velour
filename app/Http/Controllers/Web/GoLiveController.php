<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\SalonPhoto;
use App\Models\SalonSetting;
use App\Models\SalonThemeAsset;
use App\Support\AuthPanel;
use App\Support\SalonSetupProgress;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use App\Support\ThemeBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * GoLiveController — Go Live & Share page (web)
 *
 * Route:      GET /go-live
 * Middleware: auth, verified, subscription:active
 * Name:       go-live
 *
 * Serves the server-rendered shell with critical data pre-loaded.
 * All chart data is fetched client-side via the API to enable live
 * reload without a full page refresh (Alpine.js fetch + polling).
 *
 * Server-side pre-loads (avoids initial flash of empty state):
 *  - Salon + settings
 *  - Go-live checklist
 *  - This month's visit + conversion counts
 *  - QR code URL
 *  - Embed code snippets
 *  - Last 7 social share clicks by platform
 */
class GoLiveController extends Controller
{
    use ResolvesActiveSalon;

    private function abortIfAdminStoreBrowse(): void
    {
        if (AuthPanel::isAdminStoreBrowse()) {
            abort(403, 'Read-only admin view: changes are not allowed.');
        }
    }

    public function index(Request $request)
    {
        $salon = $this->activeSalon();
        $salon->load([
            'staff' => fn ($q) => $q->withoutGlobalScopes()->where('staff.salon_id', $salon->id),
            'services' => fn ($q) => $q->withoutGlobalScopes()->where('services.salon_id', $salon->id),
        ]);

        $salonId    = $salon->id;
        $themeSlug  = StorefrontTheme::forSalon($salon);
        $themeLabel = StorefrontTheme::label($themeSlug);
        $themes     = StorefrontTheme::all();
        $websiteUrl = StorefrontUrl::website($salon);
        $bookingUrl = StorefrontUrl::booking($salon);

        // ── Checklist ──────────────────────────────────────────────────────
        $checklist = $this->buildChecklist($salon);

        // ── QR ────────────────────────────────────────────────────────────
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data='
            . urlencode($bookingUrl) . '&size=300x300&ecc=M&margin=10';

        // ── This month stats (server-side to avoid flash) ─────────────────
        $from         = now()->startOfMonth();
        $thisMonthVisits = LinkVisit::withoutGlobalScopes()->where('salon_id', $salonId)
            ->whereBetween('created_at', [$from, now()])->count();
        $thisMonthConversions = LinkVisit::withoutGlobalScopes()->where('salon_id', $salonId)
            ->whereBetween('created_at', [$from, now()])
            ->where('converted', true)->count();
        $onlineBookings = Appointment::withoutGlobalScopes()->where('salon_id', $salonId)
            ->whereIn('source', ['online', 'widget', 'qr', 'whatsapp', 'instagram', 'facebook', 'google'])
            ->whereBetween('starts_at', [$from, now()])->count();

        // ── Embed snippets ────────────────────────────────────────────────
        $widgetUrl = rtrim(config('app.url'), '/') . '/widget/' . $salon->slug;
        $appUrl    = rtrim(config('app.url'), '/');
        $embedCodes = [
            'iframe' => "<iframe src=\"{$widgetUrl}\" width=\"100%\" height=\"700\" frameborder=\"0\" loading=\"lazy\" style=\"border-radius:16px;border:none;\" title=\"{$salon->name} — Online Booking\"></iframe>",
            'js'     => "<script src=\"{$appUrl}/sdk.js\" defer></script>\n<div data-velour-booking=\"{$salon->slug}\" data-theme=\"light\" data-primary-color=\"#B8943A\"></div>",
            'react'  => "import { EasyGroxBooking } from '@velour/react';\n\nexport default function BookingPage() {\n  return (\n    <EasyGroxBooking\n      salon=\"{$salon->slug}\"\n      theme=\"light\"\n      primaryColor=\"#B8943A\"\n    />\n  );\n}",
        ];

        // ── Social share click history (this month, by platform) ──────────
        $shareclicks = DB::table('social_share_clicks')
            ->where('salon_id', $salonId)
            ->where('clicked_at', '>=', now()->startOfMonth())
            ->selectRaw('platform, COUNT(*) as clicks')
            ->groupBy('platform')
            ->orderByDesc('clicks')
            ->pluck('clicks', 'platform');

        // ── Photos ───────────────────────────────────────────────────────────
        $photos = SalonPhoto::where('salon_id', $salonId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($p) => ['id' => $p->id, 'url' => asset('storage/' . $p->path)])
            ->values();

        return view('dashboard.go-live', compact(
            'salon',
            'websiteUrl',
            'bookingUrl',
            'qrUrl',
            'checklist',
            'thisMonthVisits',
            'thisMonthConversions',
            'onlineBookings',
            'embedCodes',
            'shareclicks',
            'photos',
            'themes',
            'themeSlug',
            'themeLabel',
        ));
    }

    public function updateTheme(Request $request): RedirectResponse|JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();
        $allowed = array_keys(StorefrontTheme::all());

        $validated = $request->validate([
            'theme' => ['required', 'string', \Illuminate\Validation\Rule::in($allowed)],
        ]);

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'website_theme'],
            ['value' => $validated['theme'], 'type' => 'string']
        );

        $message = 'Website theme updated to ' . StorefrontTheme::label($validated['theme']) . '.';

        if ($request->expectsJson()) {
            return response()->json([
                'ok'    => true,
                'theme' => $validated['theme'],
                'label' => StorefrontTheme::label($validated['theme']),
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('go-live', ['store' => \App\Support\SalonUrl::key($salon)])
            ->with('success', $message);
    }

    /**
     * Effective branding for one theme, plus the theme's own defaults so the
     * panel can show placeholders and Custom/Default badges.
     */
    public function themeBranding(Request $request, string $theme): JsonResponse
    {
        $salon = $this->activeSalon();
        $theme = $this->validTheme($theme);

        return response()->json($this->brandingPayload($salon, $theme));
    }

    public function updateThemeBranding(Request $request): JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();

        $validated = $request->validate([
            'theme'             => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(StorefrontTheme::all()))],
            'logo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'banner'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'heading'           => ['nullable', 'string', 'max:80'],
            'subheading'        => ['nullable', 'string', 'max:220'],
        ]);

        $theme = $validated['theme'];
        $record = SalonThemeAsset::lookup($salon->id, $theme)
            ?? new SalonThemeAsset(['salon_id' => $salon->id, 'theme' => $theme]);

        $warning = null;

        foreach (['logo' => 'logo_path', 'banner' => 'banner_path'] as $field => $column) {
            if (! $request->hasFile($field)) {
                continue;
            }

            if ($field === 'banner') {
                $warning = $this->bannerSizeAdvice($request->file($field));
            }

            $this->deleteBrandingFile($record->{$column});
            $record->{$column} = $request->file($field)->store("salons/{$salon->id}/themes/{$theme}", 'public');
        }

        // A submitted-but-empty text field means "go back to the theme default".
        foreach (['heading' => 'banner_heading', 'subheading' => 'banner_subheading'] as $field => $column) {
            if (! $request->has($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));
            $record->{$column} = $value === '' ? null : $value;
        }

        $record->salon_id = $salon->id;
        $record->theme = $theme;
        $record->save();

        return response()->json([
            'ok'      => true,
            'message' => StorefrontTheme::label($theme).' branding saved.',
            'warning' => $warning,
        ] + $this->brandingPayload($salon, $theme));
    }

    /** Clear one element so the storefront falls back again. */
    public function resetThemeBranding(Request $request, string $theme, string $element): JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();
        $theme = $this->validTheme($theme);

        abort_unless(in_array($element, ThemeBranding::ELEMENTS, true), 404);

        $record = SalonThemeAsset::lookup($salon->id, $theme);

        if ($record) {
            $column = match ($element) {
                'logo'       => 'logo_path',
                'banner'     => 'banner_path',
                'heading'    => 'banner_heading',
                'subheading' => 'banner_subheading',
            };

            if (in_array($element, ['logo', 'banner'], true)) {
                $this->deleteBrandingFile($record->{$column});
            }

            $record->{$column} = null;

            $isEmpty = collect(['logo_path', 'banner_path', 'banner_heading', 'banner_subheading'])
                ->every(fn (string $col) => blank($record->{$col}));

            $isEmpty ? $record->delete() : $record->save();
        }

        return response()->json([
            'ok'      => true,
            'message' => ucfirst($element).' reset to the '.StorefrontTheme::label($theme).' default.',
        ] + $this->brandingPayload($salon, $theme));
    }

    public function uploadPhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $count = SalonPhoto::where('salon_id', $salon->id)->count();
        if ($count >= 15) {
            return response()->json(['error' => 'Maximum 15 photos allowed.'], 422);
        }

        $path = $request->file('photo')->store("salons/{$salon->id}/photos", 'public');

        $photo = SalonPhoto::create([
            'salon_id'   => $salon->id,
            'path'       => $path,
            'disk'       => 'public',
            'sort_order' => $count,
        ]);

        return response()->json([
            'id'  => $photo->id,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function uploadLogo(Request $request)
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();

        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        if ($salon->logo && str_starts_with($salon->logo, 'salons/')) {
            Storage::disk('public')->delete($salon->logo);
        }

        $path = $request->file('logo')->store("salons/{$salon->id}/branding", 'public');
        $salon->update(['logo' => $path]);

        return back()->with('success', 'Logo uploaded successfully.');
    }

    public function updateSettings(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();

        $data = $request->validate([
            'online_booking_enabled'     => ['nullable', 'boolean'],
            'new_client_booking_enabled' => ['nullable', 'boolean'],
            'deposit_required'           => ['nullable', 'boolean'],
            'deposit_percentage'         => ['nullable', 'numeric', 'min:1', 'max:100'],
            'instant_confirmation'       => ['nullable', 'boolean'],
            'booking_advance_days'       => ['nullable', 'integer', 'min:1', 'max:365'],
            'cancellation_hours'         => ['nullable', 'integer', 'min:0', 'max:168'],
        ]);

        $changes = array_filter($data, fn ($value) => ! is_null($value));
        if ($changes !== []) {
            $salon->update($changes);
            Cache::forget("share:checklist:{$salon->id}");
        }

        return response()->json([
            'ok' => true,
            'salon' => $salon->fresh(),
        ]);
    }

    public function deletePhoto(Request $request, int $photoId): \Illuminate\Http\JsonResponse
    {
        $this->abortIfAdminStoreBrowse();

        $salon = $this->activeSalon();
        $photo = SalonPhoto::where('salon_id', $salon->id)->findOrFail($photoId);

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildChecklist(Salon $salon): array
    {
        return SalonSetupProgress::checklistForSalon($salon);
    }

    private function validTheme(string $theme): string
    {
        abort_unless(array_key_exists($theme, StorefrontTheme::all()), 404, 'Unknown theme.');

        return $theme;
    }

    /** @return array<string, mixed> */
    private function brandingPayload(Salon $salon, string $theme): array
    {
        $branding = ThemeBranding::resolve($salon, $theme);
        $defaults = ThemeBranding::defaults($theme);

        return [
            'theme'      => $theme,
            'label'      => StorefrontTheme::label($theme),
            'logo_url'   => $branding['logo_url'],
            'banner_url' => $branding['banner_url'],
            'heading'    => $branding['heading'],
            'subheading' => $branding['subheading'],
            'custom'     => $branding['custom'],
            'source'     => $branding['source'],
            'defaults'   => [
                'logo_url'   => ThemeBranding::defaultLogoUrl($theme),
                'banner_url' => ThemeBranding::defaultBannerUrl($theme),
                'heading'    => $defaults['heading'],
                'subheading' => $defaults['subheading'],
            ],
        ];
    }

    /**
     * Small banners are accepted but stretched across a full-width hero, so say so
     * rather than rejecting an upload the salon deliberately chose.
     */
    private function bannerSizeAdvice(\Illuminate\Http\UploadedFile $file): ?string
    {
        $size = @getimagesize($file->getRealPath());
        $width = $size[0] ?? null;

        if (! $width || $width >= 1200) {
            return null;
        }

        return "Heads up: this image is {$width}px wide. Under 1200px it can look soft on desktop screens.";
    }

    private function deleteBrandingFile(?string $path): void
    {
        if (is_string($path) && str_starts_with($path, 'salons/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
