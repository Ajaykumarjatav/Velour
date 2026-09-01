<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeaders
 *
 * Applies a complete suite of security response headers to every request.
 *
 * Features:
 *   • Content-Security-Policy with per-request nonces (stops inline XSS)
 *   • Strict-Transport-Security (HSTS) with preload
 *   • All X-* legacy headers (Content-Type-Options, Frame-Options, XSS-Protection)
 *   • Cross-Origin isolation headers (CORP, COEP, COOP)
 *   • Permissions-Policy (feature / sensor access)
 *   • Removes server fingerprinting headers (Server, X-Powered-By)
 *   • Adds X-Request-ID for log correlation
 *
 * The CSP nonce is stored on the request as 'csp_nonce' so Blade layouts can
 * reference it via request()->attributes->get('csp_nonce') or the @nonce directive.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a per-request nonce for CSP inline scripts/styles
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp_nonce', $nonce);

        // Generate X-Request-ID if not already set by a load balancer
        $requestId = $request->header('X-Request-ID') ?: (string) Str::uuid();
        $request->headers->set('X-Request-ID', $requestId);

        $response = $next($request);

        // ── Core anti-clickjacking & sniffing ─────────────────────────────
        $response->headers->set('X-Content-Type-Options',  'nosniff');
        $response->headers->set('X-Frame-Options',         'DENY');
        $response->headers->set('X-XSS-Protection',        '1; mode=block');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // ── Referrer control ──────────────────────────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Cross-Origin isolation (enables SharedArrayBuffer, performance) ─
        $response->headers->set('Cross-Origin-Opener-Policy',   'same-origin-allow-popups');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');
        // Note: COEP is set to 'credentialless' (not 'require-corp') so that
        // third-party resources (Stripe, fonts) still load without CORP headers.
        $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');

        // ── HSTS ──────────────────────────────────────────────────────────
        if (config('security.hsts.enabled') && app()->environment('production')) {
            $maxAge    = config('security.hsts.max_age', 31536000);
            $hsts      = "max-age={$maxAge}";
            if (config('security.hsts.include_subdomains')) $hsts .= '; includeSubDomains';
            if (config('security.hsts.preload'))            $hsts .= '; preload';
            $response->headers->set('Strict-Transport-Security', $hsts);
        }

        // ── Permissions-Policy ────────────────────────────────────────────
        $permissions = config('security.permissions_policy', []);
        if ($permissions) {
            $policy = implode(', ', array_map(
                fn($feature, $allowlist) => "{$feature}={$allowlist}",
                array_keys($permissions),
                $permissions
            ));
            $response->headers->set('Permissions-Policy', $policy);
        }

        // ── Content-Security-Policy ───────────────────────────────────────
        if (config('security.csp.enabled', true)) {
            $csp = $this->buildCsp($nonce);

            $headerName = config('security.csp.report_only', false)
                ? 'Content-Security-Policy-Report-Only'
                : 'Content-Security-Policy';

            $response->headers->set($headerName, $csp);
        }

        // ── Cache control (no caching for authenticated responses) ─────────
        // Only set if the response doesn't already have explicit cache headers
        if (! $response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        // ── Remove server fingerprinting headers ──────────────────────────
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        $response->headers->remove('X-AspNet-Version');
        $response->headers->remove('X-AspNetMvc-Version');

        // ── Add X-Request-ID to response for log correlation ───────────────
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }

    // ── CSP builder ───────────────────────────────────────────────────────────

    private function buildCsp(string $nonce): string
    {
        $directives = config('security.csp.directives', []);
        $parts      = [];

        foreach ($directives as $directive => $sources) {
            if (empty($sources)) {
                // Bare directive (no value) e.g. upgrade-insecure-requests
                $parts[] = $directive;
                continue;
            }

            // Replace the placeholder 'nonce' with the real nonce value
            $resolved = array_map(
                fn($s) => $s === "'nonce'" ? "'nonce-{$nonce}'" : $s,
                $sources
            );

            $parts[] = $directive . ' ' . implode(' ', $resolved);
        }

        // Append report-uri if configured
        if ($uri = config('security.csp.report_uri')) {
            $parts[] = "report-uri {$uri}";
            $parts[] = "report-to csp-endpoint";
        }

        return implode('; ', $parts);
    }
}
