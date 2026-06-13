<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginActivityService
{
    public function __construct(private AuditLogService $audit) {}

    public function recordSuccess(User $user, Request $request, bool $via2fa = false): void
    {
        $this->audit->login($user, $via2fa);
        $this->insertAttempt($request, $user->email, true);
    }

    public function recordFailure(Request $request, string $email, string $reason = 'invalid_credentials'): void
    {
        $this->audit->failedLogin($email);
        $this->insertAttempt($request, $email, false, $reason);
    }

    public function recordLogout(User $user): void
    {
        $this->audit->logout($user);
    }

    private function insertAttempt(Request $request, string $email, bool $succeeded, ?string $reason = null): void
    {
        try {
            DB::table('login_attempts')->insert([
                'email'          => strtolower(trim($email)),
                'ip_address'     => $request->ip(),
                'user_agent'     => substr($request->userAgent() ?? '', 0, 255),
                'succeeded'      => $succeeded,
                'failure_reason' => $succeeded ? null : $reason,
                'attempted_at'   => now(),
            ]);
        } catch (\Throwable) {
            // Never break login flow due to logging failure
        }
    }
}
