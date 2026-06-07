<?php

namespace App\Logging;

use Monolog\LogRecord;

/**
 * AddContextProcessor — AUDIT FIX
 *
 * Injects request-level context into every log record:
 *   - request_id  (X-Request-ID from SecurityHeaders middleware)
 *   - salon_id    (current tenant, if any)
 *   - user_id     (authenticated user, if any)
 *   - environment
 *   - app_version
 *
 * This makes distributed tracing across log aggregators trivial.
 */
class AddContextProcessor
{
    public function __invoke(array $logger): array
    {
        foreach ($logger['processors'] ?? [] as $processor) {
            if ($processor instanceof self) {
                return $logger;
            }
        }

        array_unshift($logger['processors'], new class {
            public function __invoke(LogRecord $record): LogRecord
            {
                $request = request();

                return $record->with(extra: array_merge($record->extra, array_filter([
                    'request_id'  => $request?->header('X-Request-ID'),
                    'user_id'     => auth()->id(),
                    'salon_id'    => $request?->attributes->get('salon_id'),
                    'ip'          => $request?->ip(),
                    'url'         => $request?->url(),
                    'method'      => $request?->method(),
                    'environment' => app()->environment(),
                    'version'     => config('velour.version', '1.0.0'),
                ])));
            }
        });

        return $logger;
    }
}
