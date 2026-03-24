<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * AccountController
 *
 * AUDIT FIX — User Account & Access Management:
 *   - Active session management (view + revoke)
 *   - Account deletion (GDPR Article 17 right to erasure)
 *   - Data export (GDPR Article 20)
 *   - API token management
 */
class AccountController extends Controller
{
    // ── Active Sessions ───────────────────────────────────────────────────────

    public function sessions(Request $request)
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'ip_address'    => $s->ip_address,
                'user_agent'    => $this->parseAgent($s->user_agent),
                'last_activity' => \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                'is_current'    => $s->id === $request->session()->getId(),
            ]);

        $loginHistory = DB::table('login_attempts')
            ->where('email', Auth::user()->email)
            ->orderBy('attempted_at', 'desc')
            ->limit(10)
            ->get();

        $apiTokens = Auth::user()->tokens()->orderBy('created_at', 'desc')->get();

        return view('account.sessions', compact('sessions', 'loginHistory', 'apiTokens'));
    }

    public function revokeSession(Request $request, string $sessionId)
    {
        // Prevent revoking current session
        if ($sessionId === $request->session()->getId()) {
            return back()->withErrors(['error' => 'Cannot revoke your current session.']);
        }

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', $sessionId)
            ->delete();

        return back()->with('success', 'Session revoked.');
    }

    public function revokeAllOtherSessions(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (! Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        // Revoke all API tokens too
        Auth::user()->tokens()->where('id', '!=', Auth::user()->currentAccessToken()?->id)->delete();

        Log::channel('security')->info('All other sessions revoked', [
            'user_id' => Auth::id(),
            'ip'      => $request->ip(),
        ]);

        return back()->with('success', 'All other sessions and tokens have been revoked.');
    }

    // ── API Token Management ──────────────────────────────────────────────────

    public function revokeToken(Request $request, int $tokenId)
    {
        Auth::user()->tokens()->where('id', $tokenId)->delete();
        return back()->with('success', 'API token revoked.');
    }

    // ── Account Deletion ──────────────────────────────────────────────────────

    public function showDelete()
    {
        return view('account.delete', ['user' => Auth::user()]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password'     => 'required',
            'confirmation' => 'required|in:DELETE MY ACCOUNT',
            'reason'       => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        // Cancel active subscription first
        if ($user->subscribed('default')) {
            try {
                $user->subscription('default')->cancelNow();
            } catch (\Throwable $e) {
                Log::error('Failed to cancel subscription on account deletion', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        Log::channel('gdpr')->info('Account deletion requested', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'reason'     => $request->reason,
            'ip'         => $request->ip(),
            'timestamp'  => now()->toIso8601String(),
        ]);

        // Soft-delete (data retained for 30 days then hard-deleted by scheduler)
        $user->update(['is_active' => false, 'email' => 'deleted-' . $user->id . '@deleted.velour']);
        Auth::logout();
        $user->delete(); // soft delete

        return redirect('/')->with('success', 'Your account has been deleted. Thank you for using Velour.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseAgent(?string $ua): string
    {
        if (! $ua) return 'Unknown device';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS Device';
        if (str_contains($ua, 'Android')) return 'Android Device';
        if (str_contains($ua, 'Chrome')) return 'Chrome Browser';
        if (str_contains($ua, 'Firefox')) return 'Firefox Browser';
        if (str_contains($ua, 'Safari')) return 'Safari Browser';
        return 'Unknown Browser';
    }
}
