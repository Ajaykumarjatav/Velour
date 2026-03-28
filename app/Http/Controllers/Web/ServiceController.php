<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)
            ->with(['services' => fn($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        $uncategorised = Service::where('salon_id', $salon->id)
            ->whereNull('category_id')
            ->active()
            ->get();

        return view('services.index', compact('salon', 'categories', 'uncategorised'));
    }

    public function create()
    {
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)->orderBy('sort_order')->get(['id','name']);

        return view('services.create', compact('salon', 'categories'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'category_id'     => ['nullable', 'exists:service_categories,id'],
            'duration_minutes'=> ['required', 'integer', 'min:5', 'max:480'],
            'price'           => ['required', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'online_booking'  => ['boolean'],
            'color'           => ['nullable', 'string', 'max:7'],
        ]);

        $data['salon_id']       = $salon->id;
        $data['status']         = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : 'active';
        $data['online_bookable'] = $data['online_booking'] ?? false;
        unset($data['is_active'], $data['online_booking']);
        Service::create($data);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $this->authorise($service);
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)->orderBy('sort_order')->get(['id','name']);

        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorise($service);

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'description'     => ['nullable', 'string', 'max:1000'],
            'category_id'     => ['nullable', 'exists:service_categories,id'],
            'duration_minutes'=> ['required', 'integer', 'min:5', 'max:480'],
            'price'           => ['required', 'numeric', 'min:0'],
            'is_active'       => ['boolean'],
            'online_booking'  => ['boolean'],
            'color'           => ['nullable', 'string', 'max:7'],
        ]);

        if (array_key_exists('is_active', $data)) {
            $data['status'] = $data['is_active'] ? 'active' : 'inactive';
            unset($data['is_active']);
        }

        if (array_key_exists('online_booking', $data)) {
            $data['online_bookable'] = $data['online_booking'];
            unset($data['online_booking']);
        }

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $this->authorise($service);
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service deleted.');
    }

    private function authorise(Service $service): void
    {
        abort_unless($service->salon_id === $this->salon()->id, 403);
    }
}
