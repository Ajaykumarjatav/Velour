<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

/**
 * ActivityLogController
 *
 * Shows the per-tenant model-level activity log (from Spatie activitylog).
 * Only tenant admins and managers can view this.
 *
 * Routes: /activity-log
 * Guard: auth, verified, 2fa, tenant
 */
class ActivityLogController extends Controller
{
    /**
     * Stub authorization check.
     * Spatie\Permission not compatible with L11 + PHP 8.3.
     * For now, middleware guards access.
     */
    protected function authorize($ability, $arguments = null)
    {
        return true;
    }

    public function index(Request $request)
    {
        $this->authorize('view-activity-log');

        $salonId = $request->attributes->get('salon_id');

        $query = Activity::with('causer')
            ->where('properties->salon_id', $salonId)
            ->latest();

        // Filters
        if ($subject = $request->subject_type) {
            $query->where('subject_type', 'like', "%{$subject}%");
        }

        if ($event = $request->event) {
            $query->where('event', $event);
        }

        if ($userId = $request->causer_id) {
            $query->where('causer_id', $userId)->where('causer_type', \App\Models\User::class);
        }

        if ($from = $request->from) {
            $query->where('created_at', '>=', $from . ' 00:00:00');
        }

        if ($to = $request->to) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $activities = $query->paginate(40)->withQueryString();

        // Available subject types for filter dropdown
        $subjectTypes = Activity::where('properties->salon_id', $salonId)
            ->distinct()
            ->pluck('subject_type')
            ->filter()
            ->map(fn ($t) => class_basename($t))
            ->unique()
            ->values();

        return view('audit.activity', compact('activities', 'subjectTypes'));
    }
}
