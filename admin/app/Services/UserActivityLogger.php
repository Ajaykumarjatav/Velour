<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UserActivityLogger
{
    /** Keep at least ~3 months; UI defaults to 30 days. */
    public const RETENTION_DAYS = 90;

    /** @var list<string> */
    private const SKIP_ROUTE_PREFIXES = [
        'notifications.dropdown',
        'ui.',
        'livewire.',
        'debugbar.',
        'ignition.',
    ];

    /** @var list<string> */
    private const SKIP_PATH_FRAGMENTS = [
        'notifications/dropdown',
        'ui/hide-profile-bar',
        'chatbot/message',
        '_debugbar',
        'livewire',
    ];

    public function logRequest(Request $request, Response $response): void
    {
        if (! $request->user()) {
            return;
        }

        $status = $response->getStatusCode();
        if ($status >= 400) {
            return;
        }

        $method = strtoupper($request->method());
        if ($method === 'OPTIONS' || $method === 'HEAD') {
            return;
        }

        $routeName = $request->route()?->getName();
        $path = '/'.ltrim($request->path(), '/');

        if ($this->shouldSkip($routeName, $path)) {
            return;
        }

        $user = $request->user();
        $isWrite = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);

        // Soft-debounce identical GET page views (2 min)
        if (! $isWrite && $routeName) {
            $key = 'ual:'.$user->id.':'.($routeName).':'.md5($path);
            if (Cache::has($key)) {
                return;
            }
            Cache::put($key, 1, now()->addMinutes(2));
        }

        $label = $this->labelFor($routeName, $method, $path);
        $action = $isWrite ? 'action.write' : 'page.view';

        $this->write(
            $user,
            $action,
            $label,
            [
                'route_name' => $routeName,
                'method' => $method,
                'path' => substr($path, 0, 500),
                'salon_id' => $this->resolveSalonId($request),
                'ip_address' => $request->ip(),
                'meta' => [
                    'status' => $status,
                    'store' => $request->route('store'),
                ],
            ]
        );
    }

    public function write(User $user, string $action, string $label, array $extra = []): void
    {
        try {
            UserActivityLog::create([
                'user_id' => $user->id,
                'salon_id' => $extra['salon_id'] ?? null,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'action' => $action,
                'label' => $label,
                'route_name' => $extra['route_name'] ?? null,
                'method' => $extra['method'] ?? null,
                'path' => $extra['path'] ?? null,
                'ip_address' => $extra['ip_address'] ?? null,
                'meta' => $extra['meta'] ?? null,
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[UserActivity] write failed', ['error' => $e->getMessage()]);
        }
    }

    public function prune(): int
    {
        return UserActivityLog::query()
            ->where('occurred_at', '<', now()->subDays(self::RETENTION_DAYS))
            ->delete();
    }

    private function shouldSkip(?string $routeName, string $path): bool
    {
        foreach (self::SKIP_PATH_FRAGMENTS as $frag) {
            if (str_contains($path, $frag)) {
                return true;
            }
        }

        if (! $routeName) {
            return false;
        }

        foreach (self::SKIP_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix) || $routeName === rtrim($prefix, '.')) {
                return true;
            }
        }

        return false;
    }

    private function resolveSalonId(Request $request): ?int
    {
        $fromAttr = (int) $request->attributes->get('salon_id', 0);
        if ($fromAttr > 0) {
            return $fromAttr;
        }

        $fromSession = (int) $request->session()->get('active_salon_id', 0);

        return $fromSession > 0 ? $fromSession : null;
    }

    private function labelFor(?string $routeName, string $method, string $path): string
    {
        if ($routeName) {
            $map = [
                'dashboard' => 'Opened dashboard',
                'calendar' => 'Opened calendar',
                'tasks.index' => 'Opened tasks',
                'appointments.index' => 'Opened appointments',
                'appointments.create' => 'Started new appointment',
                'appointments.store' => 'Created appointment',
                'appointments.update' => 'Updated appointment',
                'appointments.destroy' => 'Deleted appointment',
                'clients.index' => 'Opened clients',
                'clients.store' => 'Created client',
                'clients.update' => 'Updated client',
                'staff.index' => 'Opened staff',
                'staff.store' => 'Added staff member',
                'staff.update' => 'Updated staff',
                'services.index' => 'Opened services',
                'services.store' => 'Created service',
                'inventory.index' => 'Opened inventory',
                'expenses.index' => 'Opened expenses',
                'expenses.store' => 'Recorded expense',
                'availability.index' => 'Opened availability',
                'availability.attendance.store' => 'Updated attendance',
                'availability.leave.store' => 'Submitted leave request',
                'availability.leave.approve' => 'Approved leave',
                'pos.index' => 'Opened POS',
                'pos.store' => 'Completed POS sale',
                'marketing.index' => 'Opened marketing',
                'reports.index' => 'Opened reports',
                'settings.index' => 'Opened settings',
                'settings.salon' => 'Updated salon settings',
                'go-live' => 'Opened Go Live',
                'activity.index' => 'Opened activity log',
                'notifications.index' => 'Opened notifications',
                'login.submit' => 'Logged in',
                'admin.dashboard' => 'Opened platform admin',
                'admin.tenants' => 'Browsed tenants',
                'admin.audit.index' => 'Opened platform audit',
            ];

            if (isset($map[$routeName])) {
                return $map[$routeName];
            }

            // Generic from route name: appointments.status → Updated appointment status
            $parts = explode('.', $routeName);
            $resource = str_replace(['-', '_'], ' ', $parts[0] ?? 'page');
            $verb = $parts[1] ?? 'view';
            $actionWord = match ($verb) {
                'index', 'show' => 'Opened',
                'create' => 'Opened create',
                'edit' => 'Opened edit',
                'store' => 'Created',
                'update' => 'Updated',
                'destroy' => 'Deleted',
                'export' => 'Exported',
                default => ucfirst(str_replace(['-', '_'], ' ', $verb)),
            };

            return trim($actionWord.' '.$resource);
        }

        return $method.' '.$path;
    }
}
