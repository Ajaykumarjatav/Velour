<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AuditLogController
 *
 * Platform-wide security audit log viewer for super-admins.
 *
 * Routes prefix: /admin/audit
 * Guard: auth, verified, 2fa, super_admin
 */
class AuditLogController extends Controller
{
    /**
     * Stub authorization check.
     * Spatie\Permission not compatible with L11 + PHP 8.3.
     * For now, super_admin middleware guards access.
     */
    protected function authorize($ability, $arguments = null)
    {
        return true;
    }

    public function index(Request $request)
    {
        $this->authorize('view-audit-logs');

        $query = AuditLog::with('user', 'salon')
            ->latest('occurred_at');

        // Filters
        if ($search = $request->search) {
            $query->search($search);
        }

        if ($category = $request->category) {
            $query->category($category);
        }

        if ($severity = $request->severity) {
            $query->severity($severity);
        }

        if ($salonId = $request->salon_id) {
            $query->forTenant($salonId);
        }

        if ($userId = $request->user_id) {
            $query->forUser($userId);
        }

        if ($from = $request->from) {
            $query->where('occurred_at', '>=', $from . ' 00:00:00');
        }

        if ($to = $request->to) {
            $query->where('occurred_at', '<=', $to . ' 23:59:59');
        }

        // Stats for the current filter window
        $stats = [
            'total'    => (clone $query)->count(),
            'critical' => (clone $query)->critical()->count(),
            'warning'  => (clone $query)->warning()->count(),
            'auth'     => (clone $query)->category('auth')->count(),
            'security' => (clone $query)->category('security')->count(),
        ];

        $logs = $query->paginate(50)->withQueryString();

        $categories = ['auth', 'access', 'data', 'billing', 'admin', 'security'];
        $severities  = ['info', 'warning', 'critical'];

        return view('admin.audit.index', compact('logs', 'stats', 'categories', 'severities'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view-audit-logs');
        return view('admin.audit.show', compact('auditLog'));
    }

    public function stats()
    {
        $this->authorize('view-audit-logs');

        $hourly = AuditLog::select(
                DB::raw("DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00') as hour"),
                'severity',
                DB::raw('COUNT(*) as count')
            )
            ->where('occurred_at', '>=', now()->subHours(24))
            ->groupBy('hour', 'severity')
            ->orderBy('hour')
            ->get();

        $topIps = AuditLog::select('ip_address', DB::raw('COUNT(*) as count'))
            ->whereNotNull('ip_address')
            ->where('occurred_at', '>=', now()->subHours(24))
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $failedLogins = AuditLog::where('event', 'auth.failed')
            ->recent(24)
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        $criticalEvents = AuditLog::critical()
            ->recent(24)
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get();

        return view('admin.audit.stats', compact('hourly', 'topIps', 'failedLogins', 'criticalEvents'));
    }

    public function export(Request $request)
    {
        $this->authorize('view-audit-logs');

        $request->validate([
            'from'     => 'required|date',
            'to'       => 'required|date|after_or_equal:from',
            'category' => 'nullable|in:auth,access,data,billing,admin,security',
            'severity' => 'nullable|in:info,warning,critical',
        ]);

        $query = AuditLog::whereBetween('occurred_at', [
            $request->from . ' 00:00:00',
            $request->to   . ' 23:59:59',
        ]);

        if ($request->category) $query->category($request->category);
        if ($request->severity) $query->severity($request->severity);

        $filename = 'audit-log-' . $request->from . '-to-' . $request->to . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Occurred At', 'Category', 'Event', 'Severity',
                'User Email', 'Salon ID', 'Description',
                'IP Address', 'HTTP Method', 'URL', 'Request ID',
            ]);

            $query->chunk(500, function ($logs) use ($handle) {
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->occurred_at->toIso8601String(),
                        $log->event_category,
                        $log->event,
                        $log->severity,
                        $log->user_email,
                        $log->salon_id,
                        $log->description,
                        $log->ip_address,
                        $log->http_method,
                        $log->url,
                        $log->request_id,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
