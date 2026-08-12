<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accept absolute OR relative signed URLs (subdir / proxy / APP_URL safe).
 */
class ValidateFlexibleSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isValid($request)) {
            return $next($request);
        }

        throw new InvalidSignatureException;
    }

    private function isValid(Request $request): bool
    {
        if ($request->hasValidSignature(absolute: true) || $request->hasValidSignature(absolute: false)) {
            return true;
        }

        return $this->matchesRebuiltUrl($request);
    }

    /**
     * Recompute signature against APP_URL + path (fixes http↔https / host drift).
     */
    private function matchesRebuiltUrl(Request $request): bool
    {
        $provided = (string) $request->query('signature', '');
        if ($provided === '') {
            return false;
        }

        $expires = $request->query('expires');
        if ($expires !== null && (int) $expires < now()->getTimestamp()) {
            return false;
        }

        $ignore = array_merge(['signature'], $this->ignoredQueryParams());
        $queryString = Arr::query(Arr::except($request->query(), $ignore));

        $path = '/'.$request->path();
        $root = rtrim((string) config('app.url'), '/');
        $rootPath = rtrim((string) (parse_url($root, PHP_URL_PATH) ?: ''), '/');

        $pathForApp = $path;
        if ($rootPath !== '' && str_starts_with($path, $rootPath.'/')) {
            $pathForApp = substr($path, strlen($rootPath)) ?: '/';
        }

        $candidates = array_unique(array_filter([
            // Absolute under APP_URL
            $root.$pathForApp.($queryString !== '' ? '?'.$queryString : ''),
            // Relative (Laravel relative signing style)
            $path.($queryString !== '' ? '?'.$queryString : ''),
            $pathForApp.($queryString !== '' ? '?'.$queryString : ''),
            // Absolute if request already included subdirectory in path
            $root.$path.($queryString !== '' ? '?'.$queryString : ''),
        ]));

        $key = $this->appKey();
        foreach ($candidates as $original) {
            $expected = hash_hmac('sha256', $original, $key);
            if (hash_equals($expected, $provided)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function ignoredQueryParams(): array
    {
        // Same Cashfree / extra params excluded in bootstrap validateSignatures(except: …)
        return [
            'cf_subReferenceId',
            'cf_subscriptionId',
            'cf_authAmount',
            'cf_referenceId',
            'cf_status',
            'cf_message',
            'cf_checkoutStatus',
            'cf_mode',
            'cf_subscriptionPaymentId',
            'cf_umrn',
            'cf_umn',
        ];
    }

    private function appKey(): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        return $key;
    }
}
