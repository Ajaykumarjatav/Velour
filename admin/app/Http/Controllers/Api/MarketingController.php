<?php
// ════════════════════════════════════════════════════════════════════════════
// MarketingController
// ════════════════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\Client;
use App\Services\MarketingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function __construct(private MarketingService $marketingService) {}

    public function index(Request $request): JsonResponse
    {
        $campaigns = MarketingCampaign::with('creator')
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->when($request->type,   fn($q) => $q->where('type', $request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);
        return response()->json($campaigns);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'subject'        => 'nullable|string|max:255',
            'type'           => 'required|in:email,sms,push,offer,recall,birthday,win_back',
            'content'        => 'nullable|string',
            'template'       => 'nullable|string|max:100',
            'offer_details'  => 'nullable|array',
            'target'         => 'required|in:all,vip,lapsed,new,birthday,custom,segment',
            'target_filters' => 'nullable|array',
            'scheduled_at'   => 'nullable|date|after:now',
        ]);

        $data['salon_id']   = $request->attributes->get('salon_id');
        $data['created_by'] = $request->attributes->get('staff_id') ?? 1;
        $data['status']     = 'draft';

        $recipientCount = $this->marketingService->countRecipients(
            $data['salon_id'], $data['target'], $data['target_filters'] ?? []
        );
        $data['recipient_count'] = $recipientCount;

        $campaign = MarketingCampaign::create($data);
        return response()->json(['message' => 'Campaign created.', 'campaign' => $campaign], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::with('creator')
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);
        return response()->json($campaign);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'subject'       => 'nullable|string|max:255',
            'content'       => 'nullable|string',
            'offer_details' => 'nullable|array',
            'target'        => 'nullable|in:all,vip,lapsed,new,birthday,custom,segment',
            'target_filters'=> 'nullable|array',
            'scheduled_at'  => 'nullable|date',
        ]);

        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (in_array($campaign->status, ['sending','sent'])) {
            return response()->json(['message' => 'Cannot edit a sent campaign.'], 422);
        }

        $campaign->update($data);
        return response()->json(['message' => 'Campaign updated.', 'campaign' => $campaign]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        if ($campaign->status === 'sent') {
            return response()->json(['message' => 'Cannot delete a sent campaign.'], 422);
        }
        $campaign->delete();
        return response()->json(['message' => 'Campaign deleted.']);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (!in_array($campaign->status, ['draft','scheduled','paused'])) {
            return response()->json(['message' => "Cannot send a {$campaign->status} campaign."], 422);
        }

        $result = $this->marketingService->dispatch($campaign);
        return response()->json(['message' => "Campaign sent to {$result['sent']} recipients.", 'result' => $result]);
    }

    public function schedule(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['scheduled_at' => 'required|date|after:now']);
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $campaign->update(['status' => 'scheduled', 'scheduled_at' => $data['scheduled_at']]);
        return response()->json(['message' => 'Campaign scheduled.']);
    }

    public function pause(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $campaign->update(['status' => 'paused']);
        return response()->json(['message' => 'Campaign paused.']);
    }

    public function duplicate(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $copy = $campaign->replicate();
        $copy->name = $campaign->name . ' (Copy)';
        $copy->status = 'draft';
        $copy->sent_count = $copy->opened_count = $copy->clicked_count = $copy->booking_count = 0;
        $copy->revenue_generated = 0;
        $copy->sent_at = $copy->scheduled_at = null;
        $copy->save();
        return response()->json(['message' => 'Campaign duplicated.', 'campaign' => $copy], 201);
    }

    public function stats(Request $request, int $id): JsonResponse
    {
        $campaign = MarketingCampaign::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        return response()->json([
            'campaign'        => $campaign->only(['id','name','type','status','sent_at']),
            'recipient_count' => $campaign->recipient_count,
            'sent_count'      => $campaign->sent_count,
            'open_rate'       => $campaign->open_rate,
            'click_rate'      => $campaign->click_rate,
            'conversion_rate' => $campaign->conversion_rate,
            'bookings'        => $campaign->booking_count,
            'revenue'         => $campaign->revenue_generated,
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'content'   => 'required|string',
            'client_id' => 'nullable|integer',
        ]);
        $salonId = $request->attributes->get('salon_id');
        $client  = $data['client_id']
            ? Client::where('salon_id', $salonId)->find($data['client_id'])
            : Client::where('salon_id', $salonId)->first();

        $preview = $this->marketingService->renderPreview($data['content'], $client);
        return response()->json(['html' => $preview]);
    }

    public function segments(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->marketingService->getSegments($salonId));
    }

    public function templates(Request $request): JsonResponse
    {
        return response()->json($this->marketingService->getTemplates());
    }
}
