<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strips HTML tags and trims whitespace from all string inputs.
 * Prevents XSS by cleaning data before it ever reaches controllers.
 */
class SanitizeInput
{
    /** Fields that may contain intentional HTML (e.g. marketing content) */
    private array $allowedHtmlFields = ['content', 'body', 'description', 'template'];

    public function handle(Request $request, Closure $next): Response
    {
        $this->clean($request);
        return $next($request);
    }

    private function clean(Request $request): void
    {
        $input = $request->all();
        $request->merge($this->cleanArray($input));
    }

    private function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->cleanArray($value);
            } elseif (is_string($value)) {
                if (in_array($key, $this->allowedHtmlFields)) {
                    // Allow basic HTML but strip scripts
                    $data[$key] = $this->cleanHtml($value);
                } else {
                    $data[$key] = trim(strip_tags($value));
                }
            }
        }
        return $data;
    }

    private function cleanHtml(string $value): string
    {
        // Remove <script>, <iframe>, on* event handlers
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        $value = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $value);
        $value = preg_replace('/\bon\w+\s*=/i', '', $value);
        return $value;
    }
}
