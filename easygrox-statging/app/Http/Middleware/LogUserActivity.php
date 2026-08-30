<?php

namespace App\Http\Middleware;

use App\Services\UserActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function __construct(private readonly UserActivityLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $this->logger->logRequest($request, $response);
        }

        return $response;
    }
}
