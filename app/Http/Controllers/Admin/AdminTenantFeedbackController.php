<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantProjectFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminTenantFeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = TenantProjectFeedback::query()
            ->with(['user:id,name,email', 'salon:id,name,slug'])
            ->latest('id');

        if ($search = $request->string('search')->trim()->toString()) {
            $query->search($search);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            if (in_array($status, ['new', 'reviewed'], true)) {
                $query->where('status', $status);
            }
        }

        if ($rating = $request->integer('rating')) {
            if ($rating >= 1 && $rating <= 5) {
                $query->where('rating', $rating);
            }
        }

        $rows = $query->paginate(25)->withQueryString();

        return view('admin.tenant-feedback.index', [
            'rows' => $rows,
            'total' => TenantProjectFeedback::query()->count(),
            'newCount' => TenantProjectFeedback::query()->where('status', 'new')->count(),
        ]);
    }

    public function show(TenantProjectFeedback $tenantFeedback): View
    {
        $tenantFeedback->load(['user', 'salon.owner']);

        return view('admin.tenant-feedback.show', [
            'feedback' => $tenantFeedback,
        ]);
    }

    public function markReviewed(TenantProjectFeedback $tenantFeedback): RedirectResponse
    {
        $tenantFeedback->markReviewed();

        return back()->with('success', 'Feedback marked as reviewed.');
    }
}
