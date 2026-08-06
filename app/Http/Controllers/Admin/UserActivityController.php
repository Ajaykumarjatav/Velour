<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\UserActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserActivityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('view-audit-logs');

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $query = UserActivityLog::query()
            ->with('salon')
            ->whereBetween('occurred_at', [$from, $to])
            ->orderByDesc('occurred_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->query('user_id'));
        }
        if ($request->filled('salon_id')) {
            $query->where('salon_id', (int) $request->query('salon_id'));
        }
        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(function ($b) use ($q) {
                $b->where('label', 'like', "%{$q}%")
                    ->orWhere('user_email', 'like', "%{$q}%")
                    ->orWhere('user_name', 'like', "%{$q}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();
        $grouped = $logs->getCollection()->groupBy(fn (UserActivityLog $r) => $r->occurred_at->toDateString());
        $users = User::query()->orderBy('name')->limit(500)->get(['id', 'name', 'email']);

        return view('admin.user-activity.index', [
            'logs' => $logs,
            'grouped' => $grouped,
            'users' => $users,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'retentionDays' => UserActivityLogger::RETENTION_DAYS,
        ]);
    }
}
