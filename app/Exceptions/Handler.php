<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

/**
 * Handler — AUDIT FIX: Code Architecture
 *
 * Centralised exception renderer with:
 *  - Consistent JSON envelope (success: false, message, code, errors)
 *  - X-Request-ID propagation on error responses
 *  - Sentry/error tracker integration hook
 *  - No stack trace leakage in production
 */
class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Hook: forward to Sentry/Bugsnag if configured
            if (app()->bound('sentry') && app()->isProduction()) {
                app('sentry')->captureException($e);
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->renderApiException($e, $request);
            }
        });
    }

    private function renderApiException(Throwable $e, Request $request): JsonResponse
    {
        $requestId = $request->header('X-Request-ID', 'unknown');

        [$status, $message, $code, $errors] = match (true) {
            $e instanceof ValidationException => [
                422, 'Validation failed.', 'VALIDATION_ERROR', $e->errors(),
            ],
            $e instanceof ModelNotFoundException => [
                404, class_basename($e->getModel()) . ' not found.', 'NOT_FOUND', [],
            ],
            $e instanceof AuthenticationException => [
                401, 'Unauthenticated.', 'UNAUTHENTICATED', [],
            ],
            $e instanceof AuthorizationException => [
                403, 'This action is unauthorised.', 'FORBIDDEN', [],
            ],
            $e instanceof HttpException => [
                $e->getStatusCode(), $e->getMessage() ?: 'HTTP error.', 'HTTP_ERROR', [],
            ],
            default => [
                500, app()->isProduction()
                    ? 'An unexpected error occurred. Reference: ' . $requestId
                    : $e->getMessage(),
                'SERVER_ERROR', [],
            ],
        };

        if ($status === 500) {
            Log::channel('critical')->error('Unhandled exception', [
                'exception'  => get_class($e),
                'message'    => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'request_id' => $requestId,
                'url'        => $request->fullUrl(),
                'user_id'    => auth()->id(),
            ]);
        }

        $body = ['success' => false, 'message' => $message, 'code' => $code];
        if ($errors) $body['errors'] = $errors;
        if (! app()->isProduction()) $body['exception'] = get_class($e);

        return response()->json($body, $status)
            ->header('X-Request-ID', $requestId);
    }
}
