<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Facility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacilityController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct()
    {
        $this->authorizeResource(Facility::class, 'facility');
    }

    public function index(Request $request): View
    {
        $salon = $this->activeSalon();
        $search = $request->get('search');

        $query = Facility::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%');
            });
        }

        $facilities = $query->get();

        $total = $facilities->count();
        $operational = $facilities->filter(fn (Facility $f) => $f->isOperational())->count();
        $underMaintenance = $facilities->where('status', Facility::STATUS_MAINTENANCE)->count();

        $occSamples = $facilities->filter(fn (Facility $f) => $f->occupancy_capacity > 0);
        $avgOccupancy = $occSamples->isEmpty()
            ? null
            : round($occSamples->avg(fn (Facility $f) => $f->occupancyPercent() ?? 0), 0);

        return view('facilities.index', compact(
            'salon',
            'facilities',
            'search',
            'total',
            'operational',
            'underMaintenance',
            'avgOccupancy'
        ));
    }

    public function create(): View
    {
        $salon = $this->activeSalon();

        return view('facilities.create', [
            'salon' => $salon,
            'facility' => new Facility([
                'status' => Facility::STATUS_OPERATIONAL,
                'kind' => 'other',
                'category' => 'General',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $salon = $this->activeSalon();
        $data = $this->validated($request);
        $data['salon_id'] = $salon->id;
        $data['equipment_features'] = $this->normalizeFeatures($data['equipment_features'] ?? null);

        Facility::create($data);

        return redirect()->route('facilities.index')->with('success', 'Facility added.');
    }

    public function show(Facility $facility): View
    {
        return view('facilities.show', [
            'salon' => $this->activeSalon(),
            'facility' => $facility,
        ]);
    }

    public function edit(Facility $facility): View
    {
        return view('facilities.edit', [
            'salon' => $this->activeSalon(),
            'facility' => $facility,
        ]);
    }

    public function update(Request $request, Facility $facility): RedirectResponse
    {
        $data = $this->validated($request);
        $data['equipment_features'] = $this->normalizeFeatures($data['equipment_features'] ?? null);
        unset($data['salon_id']);

        $facility->update($data);

        return redirect()->route('facilities.show', $facility)->with('success', 'Facility updated.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        $facility->delete();

        return redirect()->route('facilities.index')->with('success', 'Facility removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $kinds = implode(',', array_keys(Facility::kindOptions()));
        $statuses = implode(',', array_keys(Facility::statusOptions()));

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:64'],
            'kind' => ['required', 'in:'.$kinds],
            'status' => ['required', 'in:'.$statuses],
            'occupancy_current' => ['required', 'integer', 'min:0'],
            'occupancy_capacity' => ['required', 'integer', 'min:0'],
            'equipment_features' => ['nullable', 'string', 'max:5000'],
            'last_maintenance_on' => ['nullable', 'date'],
            'next_maintenance_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);
    }

    private function normalizeFeatures(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $out = [];
        foreach ($lines as $line) {
            $t = trim($line);
            if ($t !== '') {
                $out[] = $t;
            }
        }

        return $out === [] ? null : array_values(array_unique($out));
    }
}
