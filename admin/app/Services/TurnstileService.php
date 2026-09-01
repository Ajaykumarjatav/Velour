<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    public function isEnabled(): bool
    {
        return (bool) config('captcha.turnstile.enabled')
            && filled(config('captcha.turnstile.site_key'))
            && filled(config('captcha.turnstile.secret_key'));
    }

    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        if (! filled($token)) {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret'   => config('captcha.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (! $response->successful()) {
                Log::warning('[Turnstile] Verification request failed', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            return (bool) $response->json('success', false);
        } catch (\Throwable $e) {
            Log::error('[Turnstile] Verification error', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
