<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceCategoryController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)
            ->with(['businessType'])
            ->withCount('services')
            ->get()
            ->sortBy(fn ($c) => [(int) ($c->businessType?->sort_order ?? 0), (int) $c->sort_order])
            ->values();

        $assignedBusinessTypes = $salon->businessTypes()->orderBy('business_types.sort_order')->get();

        return view('services.categories', compact('categories', 'assignedBusinessTypes'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'business_type_id' => [
                'required',
                'integer',
                Rule::exists('salon_business_types', 'business_type_id')->where(fn ($q) => $q->where('salon_id', $salon->id)),
            ],
            'name'  => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $data['salon_id'] = $salon->id;
        $data['slug']     = Str::slug($data['name']) . '-' . Str::random(4);
        $data['sort_order'] = (int) ServiceCategory::where('salon_id', $salon->id)
            ->where('business_type_id', $data['business_type_id'])
            ->max('sort_order') + 1;

        ServiceCategory::create($data);

        if ($request->expectsJson()) {
            $categories = ServiceCategory::where('salon_id', $salon->id)
                ->with('businessType')
                ->orderBy('business_type_id')
                ->orderBy('sort_order')
                ->get()
                ->map(fn (ServiceCategory $c) => [
                    'id'                 => $c->id,
                    'name'               => $c->name,
                    'business_type_id'   => $c->business_type_id,
                    'business_type_name' => $c->businessType?->name ?? '',
                ])
                ->values();

            return response()->json(['success' => true, 'categories' => $categories]);
        }

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $salon = $this->salon();
        abort_unless($serviceCategory->salon_id === $salon->id, 403);

        $data = $request->validate([
            'business_type_id' => [
                'required',
                'integer',
                Rule::exists('salon_business_types', 'business_type_id')->where(fn ($q) => $q->where('salon_id', $salon->id)),
            ],
            'name'  => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        if ((int) $serviceCategory->business_type_id !== (int) $data['business_type_id']) {
            $data['sort_order'] = (int) ServiceCategory::where('salon_id', $salon->id)
                ->where('business_type_id', $data['business_type_id'])
                ->max('sort_order') + 1;
        }

        $serviceCategory->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(ServiceCategory $serviceCategory)
    {
        abort_unless($serviceCategory->salon_id === $this->salon()->id, 403);

        // Unlink services from this category
        $serviceCategory->services()->update(['category_id' => null]);
        $serviceCategory->delete();

        return back()->with('success', 'Category deleted.');
    }
}
