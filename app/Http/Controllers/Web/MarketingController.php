<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\MarketingAutomationTemplate;
use App\Models\MarketingCampaign;
use App\Models\MarketingSmsMessage;
use App\Models\MarketingSmsThread;
use App\Models\SalonReferralSetting;
use App\Models\Staff;
use App\Services\MarketingGrowthDefaults;
use App\Services\MarketingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $status = $request->get('status');

        $query = $this->salonScoped(MarketingCampaign::class)->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $campaigns = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => $this->salonScoped(MarketingCampaign::class)->count(),
            'sent'      => $this->salonScoped(MarketingCampaign::class)->where('status', 'sent')->count(),
            'scheduled' => $this->salonScoped(MarketingCampaign::class)->where('status', 'scheduled')->count(),
            'draft'     => $this->salonScoped(MarketingCampaign::class)->where('status', 'draft')->count(),
        ];

        return view('marketing.index', compact('salon', 'campaigns', 'status', 'stats'));
    }

    public function growth(Request $request)
    {
        $salon = $this->salon();
        MarketingGrowthDefaults::ensureForSalon($salon);

        $tab = $request->get('tab', 'campaigns');
        if (! in_array($tab, ['campaigns', 'loyalty', 'referrals', 'communications'], true)) {
            $tab = 'campaigns';
        }

        $status = $request->get('status');
        $q      = $this->salonScoped(MarketingCampaign::class)->latest();
        if ($status) {
            $q->where('status', $status);
        }
        $hubCampaigns = $q->limit(50)->get();

        $sentRows = $this->salonScoped(MarketingCampaign::class)
            ->whereIn('status', ['sent', 'sending'])
            ->get(['sent_count', 'opened_count', 'booking_count', 'revenue_generated']);
        $totalSent     = (int) $sentRows->sum('sent_count');
        $totalOpened   = (int) $sentRows->sum('opened_count');
        $conversions   = (int) $sentRows->sum('booking_count');
        $revenueTotal  = (float) $sentRows->sum('revenue_generated');
        $avgOpenRate   = $totalSent > 0 ? round($totalOpened / $totalSent * 100, 1) : 0;

        $loyaltyTiers = LoyaltyTier::where('salon_id', $salon->id)->orderBy('sort_order')->get();
        foreach ($loyaltyTiers as $tier) {
            $tier->member_count = $this->salonScoped(Client::class)->where('loyalty_tier_id', $tier->id)->count();
        }

        $referralSettings = SalonReferralSetting::where('salon_id', $salon->id)->first();
        $totalReferrals   = $this->salonScoped(Client::class)->whereNotNull('referred_by_client_id')->count();
        $referredWhoVisited = $this->salonScoped(Client::class)
            ->whereNotNull('referred_by_client_id')
            ->where('visit_count', '>', 0)
            ->count();
        $referralConversionRate = $totalReferrals > 0
            ? (int) round($referredWhoVisited / $totalReferrals * 100)
            : 0;
        $earnedCreditsEstimate = $totalReferrals > 0
            ? (float) ($referralSettings->referrer_reward_amount ?? 0) * $referredWhoVisited
            : 0;

        $automationTemplates = MarketingAutomationTemplate::where('salon_id', $salon->id)->orderBy('name')->get();
        $smsThreads          = MarketingSmsThread::where('salon_id', $salon->id)
            ->with('messages')
            ->orderByDesc('last_message_at')
            ->limit(30)
            ->get();
        $unreadSms = (int) MarketingSmsThread::where('salon_id', $salon->id)->sum('unread_inbound');

        $clientCount = $this->salonScoped(Client::class)->where('marketing_consent', true)->count();

        $activeSmsThread = null;
        if ($smsThreads->isNotEmpty()) {
            $tid = (int) $request->get('thread');
            $activeSmsThread = $tid ? $smsThreads->firstWhere('id', $tid) : null;
            $activeSmsThread = $activeSmsThread ?? $smsThreads->first();
        }

        return view('marketing.growth', compact(
            'salon',
            'tab',
            'status',
            'hubCampaigns',
            'totalSent',
            'avgOpenRate',
            'conversions',
            'revenueTotal',
            'loyaltyTiers',
            'referralSettings',
            'totalReferrals',
            'referralConversionRate',
            'earnedCreditsEstimate',
            'automationTemplates',
            'smsThreads',
            'unreadSms',
            'clientCount',
            'activeSmsThread'
        ));
    }

    public function create()
    {
        $salon       = $this->salon();
        $base = $this->salonScoped(Client::class)
            ->where('marketing_consent', true)
            ->where('status', 'active');

        $today = now();
        $counts = [
            'all' => (int) $base->clone()->count(),
            // "Active (visited in 90d)"
            'active' => (int) $base->clone()
                ->whereNotNull('last_visit_at')
                ->where('last_visit_at', '>=', $today->copy()->subDays(90))
                ->count(),
            'lapsed' => (int) $base->clone()
                ->where(fn ($q) =>
                    $q->whereNull('last_visit_at')
                      ->orWhere('last_visit_at', '<', $today->copy()->subDays(90))
                )->count(),
            // "Birthday this month"
            'birthday' => (int) $base->clone()
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', $today->month)
                ->count(),
            'new' => (int) $base->clone()
                ->where('created_at', '>=', $today->copy()->subDays(30))
                ->count(),
        ];

        return view('marketing.create', compact('salon', 'counts'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'type'         => ['required', 'in:email,sms'],
            'subject'      => ['nullable', 'string', 'max:200'],
            'body'         => ['required', 'string'],
            'segment'      => ['required', 'in:all,active,lapsed,birthday,new'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        $data['salon_id']   = $salon->id;
        $data['status']     = $data['scheduled_at'] ? 'scheduled' : 'draft';
        $data['created_by'] = $this->salonScoped(Staff::class)
            ->where('email', Auth::user()->email)
            ->value('id');

        MarketingCampaign::create($data);

        return redirect()->route('marketing.index')->with('success', 'Campaign created.');
    }

    public function show(MarketingCampaign $marketing)
    {
        $this->authorise($marketing);

        return view('marketing.show', ['campaign' => $marketing]);
    }

    public function edit(MarketingCampaign $marketing)
    {
        $this->authorise($marketing);
        abort_unless(in_array($marketing->status, ['draft', 'scheduled'], true), 422);

        $salon       = $this->salon();
        $base = $this->salonScoped(Client::class)
            ->where('marketing_consent', true)
            ->where('status', 'active');

        $today = now();
        $counts = [
            'all' => (int) $base->clone()->count(),
            'active' => (int) $base->clone()
                ->whereNotNull('last_visit_at')
                ->where('last_visit_at', '>=', $today->copy()->subDays(90))
                ->count(),
            'lapsed' => (int) $base->clone()
                ->where(fn ($q) =>
                    $q->whereNull('last_visit_at')
                      ->orWhere('last_visit_at', '<', $today->copy()->subDays(90))
                )->count(),
            'birthday' => (int) $base->clone()
                ->whereNotNull('date_of_birth')
                ->whereMonth('date_of_birth', $today->month)
                ->count(),
            'new' => (int) $base->clone()
                ->where('created_at', '>=', $today->copy()->subDays(30))
                ->count(),
        ];

        return view('marketing.edit', [
            'campaign'    => $marketing,
            'salon'       => $salon,
            'counts'      => $counts,
        ]);
    }

    public function update(Request $request, MarketingCampaign $marketing)
    {
        $this->authorise($marketing);
        abort_unless(in_array($marketing->status, ['draft', 'scheduled'], true), 422);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'type'         => ['required', 'in:email,sms'],
            'subject'      => ['nullable', 'string', 'max:200'],
            'body'         => ['required', 'string'],
            'segment'      => ['required', 'in:all,active,lapsed,birthday,new'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $data['status'] = ! empty($data['scheduled_at']) ? 'scheduled' : 'draft';
        $marketing->update($data);

        return redirect()->route('marketing.show', $marketing)->with('success', 'Campaign updated.');
    }

    public function send(MarketingService $marketingService, MarketingCampaign $marketing)
    {
        $this->authorise($marketing);
        abort_unless(in_array($marketing->status, ['draft', 'scheduled']), 422);

        $webSegments = ['all', 'active', 'lapsed', 'birthday', 'new'];
        if (in_array($marketing->segment, $webSegments, true)) {
            $marketing->target = match ($marketing->segment) {
                'active' => 'all',
                default  => $marketing->segment,
            };
            $marketing->save();
        }

        $marketingService->dispatch($marketing);

        return back()->with('success', 'Campaign is being sent.');
    }

    public function destroy(MarketingCampaign $marketing)
    {
        $this->authorise($marketing);
        abort_unless($marketing->status === 'draft', 422);
        $marketing->delete();

        return redirect()->route('marketing.index')->with('success', 'Campaign deleted.');
    }

    public function duplicate(MarketingCampaign $marketing)
    {
        $this->authorise($marketing);

        $copy = $marketing->replicate();
        $copy->name         = $marketing->name . ' (copy)';
        $copy->status       = 'draft';
        $copy->sent_at      = null;
        $copy->scheduled_at = null;
        $copy->sent_count   = 0;
        $copy->opened_count = 0;
        $copy->clicked_count = 0;
        $copy->booking_count = 0;
        $copy->revenue_generated = 0;
        $copy->created_by   = $this->salonScoped(Staff::class)
            ->where('email', Auth::user()->email)
            ->value('id');
        $copy->save();

        return redirect()->route('marketing.edit', $copy)->with('success', 'Campaign duplicated. Review and send when ready.');
    }

    public function storeLoyaltyTier(Request $request)
    {
        $salon = $this->salon();
        $data  = $request->validate([
            'name'                    => ['required', 'string', 'max:120'],
            'price_monthly'           => ['required', 'numeric', 'min:0'],
            'service_discount_percent'=> ['nullable', 'integer', 'min:0', 'max:100'],
            'benefits'                => ['nullable', 'string', 'max:2000'],
        ]);
        $benefitLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($data['benefits'] ?? '')))));
        unset($data['benefits']);
        $data['slug']                   = Str::slug($data['name']) . '-' . Str::random(4);
        $data['salon_id']               = $salon->id;
        $data['benefits']               = $benefitLines;
        $data['service_discount_percent'] = $data['service_discount_percent'] ?? 0;
        $data['sort_order']             = (int) LoyaltyTier::where('salon_id', $salon->id)->max('sort_order') + 1;
        LoyaltyTier::create($data);

        return redirect()->route('marketing.growth', ['tab' => 'loyalty'])->with('success', 'Plan added.');
    }

    public function updateLoyaltyTier(Request $request, LoyaltyTier $loyaltyTier)
    {
        $this->authoriseTier($loyaltyTier);
        $data = $request->validate([
            'name'                    => ['required', 'string', 'max:120'],
            'price_monthly'           => ['required', 'numeric', 'min:0'],
            'service_discount_percent'=> ['nullable', 'integer', 'min:0', 'max:100'],
            'benefits'                => ['nullable', 'string', 'max:2000'],
        ]);
        $benefitLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($data['benefits'] ?? '')))));
        unset($data['benefits']);
        $data['benefits']               = $benefitLines;
        $data['service_discount_percent'] = $data['service_discount_percent'] ?? 0;
        $loyaltyTier->update($data);

        return redirect()->route('marketing.growth', ['tab' => 'loyalty'])->with('success', 'Plan updated.');
    }

    public function destroyLoyaltyTier(LoyaltyTier $loyaltyTier)
    {
        $this->authoriseTier($loyaltyTier);
        $loyaltyTier->delete();

        return redirect()->route('marketing.growth', ['tab' => 'loyalty'])->with('success', 'Plan removed.');
    }

    public function loyaltyTierMembers(LoyaltyTier $loyaltyTier)
    {
        $this->authoriseTier($loyaltyTier);
        $salon   = $this->salon();
        $clients = $this->salonScoped(Client::class)
            ->where('loyalty_tier_id', $loyaltyTier->id)
            ->orderBy('first_name')
            ->paginate(30);

        return view('marketing.loyalty-tier-members', compact('salon', 'loyaltyTier', 'clients'));
    }

    public function updateReferralSettings(Request $request)
    {
        $salon = $this->salon();
        $data  = $request->validate([
            'referrer_reward_amount' => ['required', 'numeric', 'min:0'],
            'referee_reward_amount'  => ['required', 'numeric', 'min:0'],
            'minimum_spend'          => ['required', 'numeric', 'min:0'],
            'credit_expiry_days'     => ['required', 'integer', 'min:1', 'max:3650'],
        ]);
        SalonReferralSetting::updateOrCreate(['salon_id' => $salon->id], $data);

        return redirect()->route('marketing.growth', ['tab' => 'referrals'])->with('success', 'Referral program updated.');
    }

    public function toggleAutomationTemplate(Request $request, MarketingAutomationTemplate $marketingAutomationTemplate)
    {
        $this->authoriseTemplate($marketingAutomationTemplate);
        $marketingAutomationTemplate->update([
            'is_active' => ! $marketingAutomationTemplate->is_active,
        ]);

        return redirect()->route('marketing.growth', ['tab' => 'communications'])->with('success', 'Template updated.');
    }

    public function updateAutomationTemplate(Request $request, MarketingAutomationTemplate $marketingAutomationTemplate)
    {
        $this->authoriseTemplate($marketingAutomationTemplate);
        $data = $request->validate([
            'sms_body'      => ['nullable', 'string', 'max:5000'],
            'email_subject' => ['nullable', 'string', 'max:200'],
            'email_body'    => ['nullable', 'string', 'max:10000'],
        ]);
        $marketingAutomationTemplate->update($data);

        return redirect()->route('marketing.growth', ['tab' => 'communications'])->with('success', 'Template saved.');
    }

    public function storeSmsReply(Request $request, MarketingSmsThread $marketingSmsThread)
    {
        $this->authoriseThread($marketingSmsThread);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        MarketingSmsMessage::create([
            'thread_id'  => $marketingSmsThread->id,
            'direction'  => 'out',
            'body'       => $data['body'],
            'created_at' => now(),
        ]);
        $marketingSmsThread->update([
            'last_preview'    => Str::limit($data['body'], 120),
            'last_message_at' => now(),
        ]);

        return redirect()->route('marketing.growth', [
            'tab'    => 'communications',
            'thread' => $marketingSmsThread->id,
        ])->with('success', 'Reply recorded.');
    }

    private function authorise(MarketingCampaign $marketing): void
    {
        abort_unless($marketing->salon_id === $this->salon()->id, 403);
    }

    private function authoriseTier(LoyaltyTier $loyaltyTier): void
    {
        abort_unless($loyaltyTier->salon_id === $this->salon()->id, 403);
    }

    private function authoriseTemplate(MarketingAutomationTemplate $marketingAutomationTemplate): void
    {
        abort_unless($marketingAutomationTemplate->salon_id === $this->salon()->id, 403);
    }

    private function authoriseThread(MarketingSmsThread $marketingSmsThread): void
    {
        abort_unless($marketingSmsThread->salon_id === $this->salon()->id, 403);
    }
}
