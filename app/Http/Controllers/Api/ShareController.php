<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Appointment;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ShareController — Go Live & Share API
 *
 * All endpoints are authenticated (auth:sanctum) and salon-scoped.
 * The public trackVisit endpoint is unauthenticated.
 *
 * Routes:
 *   GET  /api/v1/salon/share/stats         — KPI summary for this month
 *   GET  /api/v1/salon/share/sources       — Traffic sources breakdown (30 days)
 *   GET  /api/v1/salon/share/trend         — Daily visit + booking trend (30 days)
 *   GET  /api/v1/salon/share/devices       — Mobile vs desktop split
 *   GET  /api/v1/salon/share/qr            — QR code URL
 *   GET  /api/v1/salon/share/embed-code    — Embed snippets (iframe/JS/React)
 *   GET  /api/v1/salon/share/checklist     — Go-live readiness checklist
 *   POST /api/v1/salon/share/customise     — Update booking page settings
 *   POST /api/v1/salon/share/track-click   — Record a social share click
 *   POST /api/v1/track/visit               — Public: record a booking page visit
 */
class ShareController extends Controller
{
    use ApiResponse;

    // ── Stats: KPI summary for current month ──────────────────────────────────

    public function stats(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');

        $data = Cache::remember("share:stats:{$salonId}:" . now()->format('Y-m'), 300, function () use ($salonId) {
            $from = now()->startOfMonth();
            $to   = now()->endOfMonth();

            $visits    = LinkVisit::where('salon_id', $salonId)->whereBetween('created_at', [$from, $to]);
            $total     = (clone $visits)->count();
            $converted = (clone $visits)->where('converted', true)->count();

            $prevFrom = now()->subMonth()->startOfMonth();
            $prevTo   = now()->subMonth()->endOfMonth();
            $prevTotal = LinkVisit::where('salon_id', $salonId)->whereBetween('created_at', [$prevFrom, $prevTo])->count();
            $visitTrend = $prevTotal > 0 ? round((($total - $prevTotal) / $prevTotal) * 100, 1) : 0;

            $bookings = Appointment::where('salon_id', $salonId)
                ->whereIn('source', ['online', 'widget', 'qr', 'whatsapp', 'instagram', 'facebook', 'google'])
                ->whereBetween('starts_at', [$from, $to])
                ->count();

            $revenue = PosTransaction::where('salon_id', $salonId)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$from, $to])
                ->sum('total');

            $onlineRevenue = Appointment::where('salon_id', $salonId)
                ->whereIn('source', ['online', 'widget', 'qr'])
                ->whereBetween('starts_at', [$from, $to])
                ->join('pos_transactions', 'appointments.id', '=', 'pos_transactions.appointment_id')
                ->where('pos_transactions.status', 'completed')
                ->sum('pos_transactions.total');

            return [
                'link_visits'       => $total,
                'visit_trend'       => $visitTrend,
                'conversions'       => $converted,
                'conversion_rate'   => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
                'online_bookings'   => $bookings,
                'online_revenue'    => round($onlineRevenue, 2),
                'total_revenue'     => round($revenue, 2),
                'period'            => now()->format('F Y'),
            ];
        });

        return $this->success($data);
    }

    // ── Sources: traffic breakdown by channel (30 days) ───────────────────────

    public function sources(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');

        $sources = Cache::remember("share:sources:{$salonId}", 300, function () use ($salonId) {
            $rows = LinkVisit::where('salon_id', $salonId)
                ->last30Days()
                ->selectRaw('source, COUNT(*) as visits, SUM(CASE WHEN converted THEN 1 ELSE 0 END) as conversions')
                ->groupBy('source')
                ->orderByDesc('visits')
                ->get();

            $total = $rows->sum('visits') ?: 1;

            $icons = [
                'whatsapp'  => '💬',
                'instagram' => '📸',
                'facebook'  => '👍',
                'google'    => '🔍',
                'qr'        => '📱',
                'direct'    => '🔗',
                'email'     => '✉️',
                'tiktok'    => '🎵',
                'embed'     => '🖥',
                'other'     => '🌐',
            ];

            return $rows->map(fn ($s) => [
                'source'          => $s->source,
                'label'           => ucfirst($s->source),
                'icon'            => $icons[$s->source] ?? '🌐',
                'visits'          => (int) $s->visits,
                'conversions'     => (int) $s->conversions,
                'conversion_rate' => $s->visits > 0 ? round(($s->conversions / $s->visits) * 100, 1) : 0,
                'percentage'      => round(($s->visits / $total) * 100, 1),
            ])->values();
        });

        return $this->success(['sources' => $sources, 'total_visits' => $sources->sum('visits')]);
    }

    // ── Trend: daily visits + bookings for last 30 days ──────────────────────

    public function trend(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');

        $data = Cache::remember("share:trend:{$salonId}", 300, function () use ($salonId) {
            $days = collect(range(29, 0))->map(function ($daysAgo) use ($salonId) {
                $date = now()->subDays($daysAgo)->toDateString();

                $visits = LinkVisit::where('salon_id', $salonId)
                    ->whereDate('created_at', $date)
                    ->count();

                $bookings = Appointment::where('salon_id', $salonId)
                    ->whereIn('source', ['online', 'widget', 'qr', 'whatsapp', 'instagram', 'facebook', 'google'])
                    ->whereDate('starts_at', $date)
                    ->count();

                return [
                    'date'     => $date,
                    'label'    => now()->subDays($daysAgo)->format('j M'),
                    'visits'   => $visits,
                    'bookings' => $bookings,
                ];
            });

            return $days->values();
        });

        return $this->success(['trend' => $data]);
    }

    // ── Devices: mobile vs desktop split ─────────────────────────────────────

    public function devices(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');

        $data = Cache::remember("share:devices:{$salonId}", 300, function () use ($salonId) {
            $rows = LinkVisit::where('salon_id', $salonId)
                ->last30Days()
                ->selectRaw('device, COUNT(*) as count')
                ->groupBy('device')
                ->get();

            $total = $rows->sum('count') ?: 1;

            return $rows->map(fn ($r) => [
                'device'     => $r->device ?? 'unknown',
                'count'      => (int) $r->count,
                'percentage' => round(($r->count / $total) * 100, 1),
            ])->values();
        });

        return $this->success(['devices' => $data]);
    }

    // ── QR Code ───────────────────────────────────────────────────────────────

    public function generateQr(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');
        $salon   = Salon::findOrFail($salonId);
        $url     = rtrim(config('app.url'), '/') . '/book/' . $salon->slug;

        return $this->success([
            'booking_url' => $url,
            'qr_url'      => 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($url) . '&size=400x400&ecc=M&margin=10',
            'qr_url_sm'   => 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($url) . '&size=200x200&ecc=M&margin=6',
        ]);
    }

    // ── Embed code snippets ───────────────────────────────────────────────────

    public function embedCode(Request $request): JsonResponse
    {
        $salonId   = (int) $request->attributes->get('salon_id');
        $salon     = Salon::findOrFail($salonId);
        $widgetUrl = rtrim(config('app.url'), '/') . '/widget/' . $salon->slug;
        $appUrl    = rtrim(config('app.url'), '/');

        return $this->success([
            'iframe' => implode("\n", [
                "<!-- Velour Booking Widget -->",
                "<iframe",
                "  src=\"{$widgetUrl}\"",
                "  width=\"100%\"",
                "  height=\"700\"",
                "  frameborder=\"0\"",
                "  loading=\"lazy\"",
                "  style=\"border-radius:16px;border:none;\"",
                "  title=\"{$salon->name} — Online Booking\"",
                "></iframe>",
            ]),
            'js' => implode("\n", [
                "<!-- Velour Booking SDK -->",
                "<script src=\"{$appUrl}/sdk.js\" defer></script>",
                "<div",
                "  data-velour-booking=\"{$salon->slug}\"",
                "  data-theme=\"light\"",
                "  data-primary-color=\"#B8943A\"",
                "></div>",
            ]),
            'react' => implode("\n", [
                "import { VelourBooking } from '@velour/react';",
                "",
                "// Install: npm install @velour/react",
                "",
                "export default function BookingPage() {",
                "  return (",
                "    <VelourBooking",
                "      salon=\"{$salon->slug}\"",
                "      theme=\"light\"",
                "      primaryColor=\"#B8943A\"",
                "    />",
                "  );",
                "}",
            ]),
        ]);
    }

    // ── Go-live readiness checklist ───────────────────────────────────────────

    public function checklist(Request $request): JsonResponse
    {
        $salonId = (int) $request->attributes->get('salon_id');
        $salon   = Salon::with(['staff', 'services', 'settings'])->findOrFail($salonId);

        $hasLogo     = (bool) $salon->logo;
        $hasHours    = ! empty($salon->opening_hours);
        $hasServices = Service::where('salon_id', $salonId)->where('status', 'active')->exists();
        $hasStaff    = Staff::where('salon_id', $salonId)->where('is_active', true)->where('bookable_online', true)->exists();
        $hasAddress  = (bool) $salon->address_line1;
        $hasStripe   = (bool) $salon->stripe_account_id;
        $hasPhone    = (bool) $salon->phone;
        $hasDesc     = (bool) $salon->description;

        $items = [
            ['key' => 'address',   'label' => 'Salon address set',              'done' => $hasAddress,  'link' => route('settings.index'), 'priority' => 'high'],
            ['key' => 'phone',     'label' => 'Phone number added',             'done' => $hasPhone,    'link' => route('settings.index'), 'priority' => 'high'],
            ['key' => 'hours',     'label' => 'Opening hours configured',       'done' => $hasHours,    'link' => route('settings.index'), 'priority' => 'high'],
            ['key' => 'services',  'label' => 'At least one bookable service',  'done' => $hasServices, 'link' => route('services.index'), 'priority' => 'high'],
            ['key' => 'staff',     'label' => 'Staff member bookable online',   'done' => $hasStaff,    'link' => route('staff.index'),    'priority' => 'high'],
            ['key' => 'logo',      'label' => 'Logo uploaded',                  'done' => $hasLogo,     'link' => route('settings.index'), 'priority' => 'medium'],
            ['key' => 'desc',      'label' => 'Salon description written',      'done' => $hasDesc,     'link' => route('settings.index'), 'priority' => 'medium'],
            ['key' => 'stripe',    'label' => 'Stripe payments connected',      'done' => $hasStripe,   'link' => route('settings.index'), 'priority' => 'low'],
        ];

        $done  = collect($items)->where('done', true)->count();
        $total = count($items);
        $score = round(($done / $total) * 100);

        return $this->success([
            'items'        => $items,
            'score'        => $score,
            'done'         => $done,
            'total'        => $total,
            'ready'        => $score >= 75,
            'booking_live' => $salon->online_booking_enabled,
        ]);
    }

    // ── Update booking page settings ──────────────────────────────────────────

    public function customise(Request $request): JsonResponse
    {
        $data = $request->validate([
            'online_booking_enabled'     => 'nullable|boolean',
            'new_client_booking_enabled' => 'nullable|boolean',
            'deposit_required'           => 'nullable|boolean',
            'deposit_percentage'         => 'nullable|numeric|min:1|max:100',
            'instant_confirmation'       => 'nullable|boolean',
            'booking_advance_days'       => 'nullable|integer|min:1|max:365',
            'cancellation_hours'         => 'nullable|integer|min:0|max:168',
        ]);

        $salonId = (int) $request->attributes->get('salon_id');
        $salon   = Salon::findOrFail($salonId);
        $salon->update(array_filter($data, fn ($v) => ! is_null($v)));

        // Bust share-related caches
        Cache::forget("share:checklist:{$salonId}");

        Log::info('Booking page settings updated', [
            'salon_id' => $salonId,
            'user_id'  => $request->user()->id,
            'changes'  => array_keys(array_filter($data, fn ($v) => ! is_null($v))),
        ]);

        return $this->success(['salon' => $salon->fresh()], 'Booking page settings updated.');
    }

    // ── Track a social share click (authenticated) ────────────────────────────

    public function trackClick(Request $request): JsonResponse
    {
        $data = $request->validate([
            'platform' => 'required|string|max:40|in:whatsapp,instagram,facebook,google,tiktok,email,copy_link,qr_download,embed',
        ]);

        $salonId = (int) $request->attributes->get('salon_id');

        DB::table('social_share_clicks')->insert([
            'salon_id'   => $salonId,
            'user_id'    => $request->user()->id,
            'platform'   => $data['platform'],
            'ip_address' => $request->ip(),
            'device'     => str_contains($request->userAgent() ?? '', 'Mobile') ? 'mobile' : 'desktop',
            'clicked_at' => now(),
        ]);

        return $this->success(null, 'Click tracked.');
    }

    // ── Public: record a booking page visit ───────────────────────────────────

    public function trackVisit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'salon_slug'   => 'required|string|max:100',
            'source'       => 'required|string|max:50',
            'page'         => 'nullable|string|max:100',
            'utm_source'   => 'nullable|string|max:100',
            'utm_medium'   => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
        ]);

        $salon = Salon::where('slug', $data['salon_slug'])->where('is_active', true)->first();

        if (! $salon) {
            return response()->json(['tracked' => false]);
        }

        LinkVisit::create([
            'salon_id'     => $salon->id,
            'source'       => $data['source'],
            'page'         => $data['page'] ?? null,
            'ip_address'   => $request->ip(),
            'device'       => str_contains($request->userAgent() ?? '', 'Mobile') ? 'mobile' : 'desktop',
            'utm_source'   => $data['utm_source'] ?? null,
            'utm_medium'   => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'referrer'     => $request->header('Referer'),
        ]);

        return response()->json(['tracked' => true]);
    }
}
