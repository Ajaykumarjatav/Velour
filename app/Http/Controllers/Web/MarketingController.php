<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Client;
use App\Jobs\SendMarketingCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon  = $this->salon();
        $status = $request->get('status');

        $query = MarketingCampaign::where('salon_id', $salon->id)->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $campaigns = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => MarketingCampaign::where('salon_id', $salon->id)->count(),
            'sent'      => MarketingCampaign::where('salon_id', $salon->id)->where('status', 'sent')->count(),
            'scheduled' => MarketingCampaign::where('salon_id', $salon->id)->where('status', 'scheduled')->count(),
            'draft'     => MarketingCampaign::where('salon_id', $salon->id)->where('status', 'draft')->count(),
        ];

        return view('marketing.index', compact('salon', 'campaigns', 'status', 'stats'));
    }

    public function create()
    {
        $salon        = $this->salon();
        $clientCount  = Client::where('salon_id', $salon->id)->where('marketing_consent', true)->count();

        return view('marketing.create', compact('salon', 'clientCount'));
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
        $data['created_by'] = \App\Models\Staff::where('salon_id', $salon->id)
            ->where('email', Auth::user()->email)
            ->value('id');

        MarketingCampaign::create($data);

        return redirect()->route('marketing.index')->with('success', 'Campaign created.');
    }

    public function show(MarketingCampaign $campaign)
    {
        $this->authorise($campaign);

        return view('marketing.show', compact('campaign'));
    }

    public function send(MarketingCampaign $campaign)
    {
        $this->authorise($campaign);
        abort_unless(in_array($campaign->status, ['draft', 'scheduled']), 422);

        SendMarketingCampaign::dispatch($campaign);
        $campaign->update(['status' => 'sending', 'sent_at' => now()]);

        return back()->with('success', 'Campaign is being sent.');
    }

    public function destroy(MarketingCampaign $campaign)
    {
        $this->authorise($campaign);
        abort_unless($campaign->status === 'draft', 422);
        $campaign->delete();

        return redirect()->route('marketing.index')->with('success', 'Campaign deleted.');
    }

    private function authorise(MarketingCampaign $campaign): void
    {
        abort_unless($campaign->salon_id === $this->salon()->id, 403);
    }
}
