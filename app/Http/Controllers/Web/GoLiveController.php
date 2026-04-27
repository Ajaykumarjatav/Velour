<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\SalonPhoto;
use App\Models\Service;
use App\Models\Staff;
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
    public function index(Request $request)
    {
        $user  = Auth::user();
        $salon = $this->getSalon();
        $salon->load(['staff', 'services']);

        $salonId    = $salon->id;
        $bookingUrl = rtrim(config('app.url'), '/') . '/book/' . $salon->slug;

        // ── Checklist ──────────────────────────────────────────────────────
        $checklist = $this->buildChecklist($salon);

        // ── QR ────────────────────────────────────────────────────────────
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data='
            . urlencode($bookingUrl) . '&size=300x300&ecc=M&margin=10';

        // ── This month stats (server-side to avoid flash) ─────────────────
        $from         = now()->startOfMonth();
        $thisMonthVisits = LinkVisit::where('salon_id', $salonId)
            ->whereBetween('created_at', [$from, now()])->count();
        $thisMonthConversions = LinkVisit::where('salon_id', $salonId)
            ->whereBetween('created_at', [$from, now()])
            ->where('converted', true)->count();
        $onlineBookings = Appointment::where('salon_id', $salonId)
            ->whereIn('source', ['online', 'widget', 'qr', 'whatsapp', 'instagram', 'facebook', 'google'])
            ->whereBetween('starts_at', [$from, now()])->count();

        // ── Embed snippets ────────────────────────────────────────────────
        $widgetUrl = rtrim(config('app.url'), '/') . '/widget/' . $salon->slug;
        $appUrl    = rtrim(config('app.url'), '/');
        $embedCodes = [
            'iframe' => "<iframe src=\"{$widgetUrl}\" width=\"100%\" height=\"700\" frameborder=\"0\" loading=\"lazy\" style=\"border-radius:16px;border:none;\" title=\"{$salon->name} — Online Booking\"></iframe>",
            'js'     => "<script src=\"{$appUrl}/sdk.js\" defer></script>\n<div data-velour-booking=\"{$salon->slug}\" data-theme=\"light\" data-primary-color=\"#B8943A\"></div>",
            'react'  => "import { VelourBooking } from '@velour/react';\n\nexport default function BookingPage() {\n  return (\n    <VelourBooking\n      salon=\"{$salon->slug}\"\n      theme=\"light\"\n      primaryColor=\"#B8943A\"\n    />\n  );\n}",
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
            'bookingUrl',
            'qrUrl',
            'checklist',
            'thisMonthVisits',
            'thisMonthConversions',
            'onlineBookings',
            'embedCodes',
            'shareclicks',
            'photos',
        ));
    }

    public function uploadPhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        $salon = $this->getSalon();

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
        $salon = $this->getSalon();

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
        $salon = $this->getSalon();

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
        $salon = $this->getSalon();
        $photo = SalonPhoto::where('salon_id', $salon->id)->findOrFail($photoId);

        Storage::disk($photo->disk)->delete($photo->path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    private function getSalon(): Salon
    {
        return Salon::where('owner_id', Auth::id())->firstOrFail();
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildChecklist(Salon $salon): array
    {
        $salonId = $salon->id;

        $items = [
            ['key' => 'address',  'label' => 'Address set',             'done' => (bool) $salon->address_line1, 'priority' => 'high',   'link' => route('settings.index'), 'tip' => 'Clients need to know where you are.'],
            ['key' => 'phone',    'label' => 'Phone number added',      'done' => (bool) $salon->phone,         'priority' => 'high',   'link' => route('settings.index'), 'tip' => 'Required for booking confirmations.'],
            ['key' => 'hours',    'label' => 'Opening hours set',       'done' => ! empty($salon->opening_hours), 'priority' => 'high', 'link' => route('settings.index'), 'tip' => 'Without hours, no booking slots appear.'],
            ['key' => 'services', 'label' => 'Bookable service added',  'done' => Service::where('salon_id', $salonId)->where('status', 'active')->where('online_bookable', true)->exists(), 'priority' => 'high', 'link' => route('services.index'), 'tip' => 'Enable online booking on at least one service.'],
            ['key' => 'staff',    'label' => 'Staff bookable online',   'done' => Staff::where('salon_id', $salonId)->where('is_active', true)->where('bookable_online', true)->exists(),    'priority' => 'high', 'link' => route('staff.index'),    'tip' => 'Toggle bookable online in each staff profile.'],
            ['key' => 'logo',     'label' => 'Logo uploaded',           'done' => (bool) $salon->logo,          'priority' => 'medium', 'link' => route('go-live') . '#logo-upload', 'tip' => 'Makes your booking page look professional.'],
            ['key' => 'desc',     'label' => 'Salon description added', 'done' => (bool) $salon->description,   'priority' => 'medium', 'link' => route('settings.index'), 'tip' => 'Helps new clients choose your salon.'],
            ['key' => 'stripe',   'label' => 'Stripe payments linked',  'done' => (bool) $salon->stripe_account_id, 'priority' => 'low', 'link' => route('settings.index'), 'tip' => 'Required to take deposits or online payments.'],
        ];

        $done  = collect($items)->where('done', true)->count();
        $total = count($items);

        return [
            'items' => $items,
            'done'  => $done,
            'total' => $total,
            'score' => (int) round(($done / $total) * 100),
            'ready' => $done >= 5, // at least all high-priority done
        ];
    }
}
