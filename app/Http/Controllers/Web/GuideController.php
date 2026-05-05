<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class GuideController extends Controller
{
    use ResolvesActiveSalon;

    public function index(): View
    {
        $salon = $this->activeSalon();
        $screenDefs = [
            [
                'path' => 'guide/dashboard-overview.png',
                'title' => 'Dashboard overview',
                'caption' => 'Daily KPIs and quick action cards.',
                'link' => route('dashboard'),
            ],
            [
                'path' => 'guide/appointments-create.png',
                'title' => 'Create appointment',
                'caption' => 'Search client, pick staff, choose services and slot.',
                'link' => route('appointments.create'),
            ],
            [
                'path' => 'guide/calendar-view.png',
                'title' => 'Calendar view',
                'caption' => 'Track today and upcoming bookings.',
                'link' => route('calendar'),
            ],
            [
                'path' => 'guide/pos-checkout.png',
                'title' => 'POS checkout',
                'caption' => 'Create bills for services and products.',
                'link' => route('pos.index'),
            ],
            [
                'path' => 'guide/marketing-campaign.png',
                'title' => 'Marketing campaign',
                'caption' => 'Select audience and launch campaigns.',
                'link' => route('marketing.growth'),
            ],
        ];

        $screenshots = collect($screenDefs)
            ->filter(fn (array $item) => Storage::disk('public')->exists($item['path']))
            ->map(fn (array $item) => [
                ...$item,
                'url' => asset('storage/' . $item['path']),
            ])
            ->values()
            ->all();

        return view('guide.index', compact('salon', 'screenshots'));
    }
}

