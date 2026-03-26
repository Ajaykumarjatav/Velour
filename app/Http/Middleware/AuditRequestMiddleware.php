<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * AuditRequestMiddleware
 *
 * Automatically writes to audit_logs for any HTTP request that matches
 * a "sensitive" pattern. Applied selectively — not to every route.
 *
 * What gets logged:
 *   • DELETE requests (destructive operations)
 *   • Bulk operations (contains /bulk in path)
 *   • Data export endpoints (contains /export or /download)
 *   • GDPR requests
 *   • Admin-panel routes (/admin/*)
 *   • Role / permission changes
 *   • Billing operations
 *   • Settings changes (PUT/PATCH /*/settings)
 *
 * What is NOT logged to avoid noise:
 *   • GET requests (read-only, except exports)
 *   • Webhook payloads (separate webhook audit)
 *   • Health check endpoints
 *
 * Apply to route groups via:
 *   ->middleware('audit.request')
 */
class AuditRequestMiddleware
{


    
    public function __construct(
        protected AuditLogService $audit
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log after response (post-middleware) so we know the outcome
        if ($this->shouldLog($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    protected function shouldLog(Request $request): bool
    {
        $path   = $request->path();
        $method = $request->method();

        // Always skip health checks and OPTIONS preflight
        if (in_array($path, ['up', 'health']) || $method === 'OPTIONS') {
            return false;
        }

        // Always log DELETE (destruction)
        if ($method === 'DELETE') {
            return true;
        }

        // Log GET on export/download paths
        if ($method === 'GET' && $this->matchesPattern($path, ['export', 'download', 'gdpr'])) {
            return true;
        }

        // Log write operations on sensitive paths
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            return $this->matchesPattern($path, [
                'admin/', 'billing/', 'settings', 'roles', 'permissions',
                'impersonat', 'bulk', 'transfer', 'promote', 'demote',
            ]);
        }

        return false;
    }

    protected function logRequest(Request $request, Response $response): void
    {
        $statusCode = $response->getStatusCode();
        $path       = $request->path();
        $method     = $request->method();

        // Derive category and event from path/method
        [$category, $event] = $this->categorise($path, $method, $statusCode);

        $severity = match (true) {
            $statusCode >= 500                            => 'critical',
            $statusCode === 403                           => 'warning',
            str_contains($path, 'admin')                 => 'warning',
            str_contains($path, 'impersonat')            => 'warning',
            $method === 'DELETE'                         => 'info',
            default                                      => 'info',
        };

        $description = "{$method} {$path} → {$statusCode}";

        // Sanitise request body for metadata (strip passwords, tokens)
        $body = $this->sanitiseBody($request->except([
            'password', 'password_confirmation', 'token', 'secret',
            'card_number', 'cvv', '_token',
        ]));

        $this->audit->write($category, $event, $severity, $description, null, [
            'method'      => $method,
            'path'        => $path,
            'status'      => $statusCode,
            'body_keys'   => array_keys($body),  // Only log key names, not values
            'query'       => $request->query->all(),
        ]);
    }

    protected function categorise(string $path, string $method, int $status): array
    {
        if (str_contains($path, 'billing'))      return ['billing',  'billing.request'];
        if (str_contains($path, 'admin'))        return ['admin',    'admin.request'];
        if (str_contains($path, 'impersonat'))   return ['admin',    'admin.impersonation'];
        if (str_contains($path, 'export'))       return ['data',     'data.export'];
        if (str_contains($path, 'download'))     return ['data',     'data.download'];
        if (str_contains($path, 'gdpr'))         return ['data',     'data.gdpr'];
        if (str_contains($path, 'settings'))     return ['admin',    'settings.change'];
        if (str_contains($path, 'roles'))        return ['admin',    'admin.roles'];
        if ($method === 'DELETE')                return ['data',     'data.delete'];
        return ['access', 'request'];
    }

    protected function matchesPattern(string $path, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($path, $pattern)) return true;
        }
        return false;
    }

    protected function sanitiseBody(array $body): array
    {
        // Flatten nested arrays to just check keys exist
        return $body;
    }
}
