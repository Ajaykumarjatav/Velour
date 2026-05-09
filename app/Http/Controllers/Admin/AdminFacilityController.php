<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\View\View;

/**
 * Cross-tenant facilities list for platform super-admins.
 */
class AdminFacilityController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::withoutTenantScope()
            ->with(['salon' => fn ($q) => $q->select('id', 'name', 'slug')])
            ->orderBy('salon_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(40)
            ->withQueryString();

        return view('admin.facilities.index', compact('facilities'));
    }
}
