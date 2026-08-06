<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    use ResolvesActiveSalon;

    public function index(Request $request): View
    {
        $this->authorize('view-activity-log');

        $salon = $this->activeSalon();
        $user = Auth::user();

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        // Cap range to retention window
        $earliest = now()->subDays(UserActivityLogger::RETENTION_DAYS)->startOfDay();
        if ($from->lt($earliest)) {
            $from = $earliest->copy();
        }
        if ($to->lt($from)) {
            $to = $from->copy()->endOfDay();
        }

        $query = UserActivityLog::query()
            ->where('salon_id', $salon->id)
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at');

        $filterUserId = $request->filled('user_id') ? (int) $request->query('user_id') : null;
        if ($filterUserId) {
            $query->where('user_id', $filterUserId);
        }

        if ($action = $request->query('action')) {
            if ($action === 'writes') {
                $query->where('action', 'action.write');
            } elseif ($action === 'views') {
                $query->where('action', 'page.view');
            }
        }

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($builder) use ($q) {
                $builder->where('label', 'like', "%{$q}%")
                    ->orWhere('user_name', 'like', "%{$q}%")
                    ->orWhere('user_email', 'like', "%{$q}%")
                    ->orWhere('path', 'like', "%{$q}%");
            });
        }

        $activities = $query->paginate(50)->withQueryString();

        $grouped = $activities->getCollection()->groupBy(fn (UserActivityLog $row) => $row->occurred_at->toDateString());

        $teamUsers = User::query()
            ->where(function ($q) use ($salon) {
                $q->whereHas('salons', fn ($s) => $s->where('salons.id', $salon->id))
                    ->orWhereIn('id', function ($sub) use ($salon) {
                        $sub->select('user_id')
                            ->from('staff')
                            ->where('salon_id', $salon->id)
                            ->whereNotNull('user_id');
                    });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        // Model change log (Spatie) — last 30 days, same salon
        $modelChanges = Activity::with('causer')
            ->where('properties->salon_id', $salon->id)
            ->where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->when($filterUserId, fn ($q) => $q->where('causer_id', $filterUserId)->where('causer_type', User::class))
            ->latest()
            ->limit(30)
            ->get();

        $retentionDays = UserActivityLogger::RETENTION_DAYS;

        return view('audit.activity', [
            'salon' => $salon,
            'activities' => $activities,
            'grouped' => $grouped,
            'teamUsers' => $teamUsers,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'filterUserId' => $filterUserId,
            'modelChanges' => $modelChanges,
            'retentionDays' => $retentionDays,
            'viewer' => $user,
        ]);
    }
}
