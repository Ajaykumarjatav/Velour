<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\InventoryItem;
use App\Models\Voucher;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\SalonNotification;
use App\Models\MarketingCampaign;
use App\Models\LinkVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MarketingService
{
    /**
     * Count how many clients match a target segment.
     */
    public function countRecipients(int $salonId, string $target, array $filters = []): int
    {
        return $this->buildAudience($salonId, $target, $filters)->count();
    }

    /**
     * Dispatch a campaign (queued in production).
     */
    public function dispatch(\App\Models\MarketingCampaign $campaign): array
    {
        $audience = $this->buildAudience(
            $campaign->salon_id,
            $campaign->target,
            $campaign->target_filters ?? []
        )->get();

        $campaign->update([
            'status'          => 'sent',
            'sent_at'         => now(),
            'recipient_count' => $audience->count(),
            'sent_count'      => $audience->count(),
        ]);

        // In production: dispatch \App\Jobs\SendMarketingCampaign::
        foreach ($audience as $client) {
            \App\Jobs\SendMarketingCampaign::dispatch($campaign, $client)
                ->onQueue('campaigns');
        }

        return [
            'sent'       => $campaign->sent_count,
            'recipients' => $audience->count(),
        ];
    }

    /**
     * Get segment counts for campaign targeting UI.
     */
    public function getSegments(int $salonId): array
    {
        $base = \App\Models\Client::where('salon_id', $salonId)->where('status', 'active');

        return [
            ['key' => 'all',      'label' => 'All Active Clients',   'count' => $base->clone()->count()],
            ['key' => 'vip',      'label' => 'VIP Clients',          'count' => $base->clone()->where('is_vip', true)->count()],
            ['key' => 'new',      'label' => 'New Clients (30 days)', 'count' => $base->clone()->where('created_at', '>=', now()->subDays(30))->count()],
            ['key' => 'lapsed',   'label' => 'Lapsed (90+ days)',    'count' => $base->clone()->where(fn($q) => $q->whereNull('last_visit_at')->orWhere('last_visit_at', '<', now()->subDays(90)))->count()],
            ['key' => 'birthday', 'label' => 'Birthdays This Month', 'count' => $base->clone()->whereMonth('date_of_birth', now()->month)->count()],
        ];
    }

    private function buildAudience(int $salonId, string $target, array $filters = []): \Illuminate\Database\Eloquent\Builder
    {
        $q = \App\Models\Client::where('salon_id', $salonId)
            ->where('status', 'active')
            ->where('marketing_consent', true);

        return match ($target) {
            'vip'      => $q->where('is_vip', true),
            'lapsed'   => $q->where(fn($sq) => $sq->whereNull('last_visit_at')->orWhere('last_visit_at', '<', now()->subDays(90))),
            'new'      => $q->where('created_at', '>=', now()->subDays(30)),
            'birthday' => $q->whereMonth('date_of_birth', now()->month)->whereDay('date_of_birth', now()->day),
            default    => $q,
        };
    }
}
