<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Salon;
use App\Models\UserActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecuritySupportController extends Controller
{
    use ResolvesActiveSalon;

    private function salon(): Salon
    {
        return $this->activeSalon();
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $user = Auth::user();

        $twoFactorOn = $user->hasTwoFactorEnabled();
        $httpsOn = $request->secure();
        $sessionMinutes = (int) config('session.lifetime', 120);
        $auditOn = true;
        $cardsNotStored = true;
        $ipWhitelistOn = false;
        $fullDbEncryptionOn = (bool) config('session.encrypt', false);

        $checks = [
            $twoFactorOn,
            $httpsOn,
            (bool) $user->email_verified_at,
            $auditOn,
            $cardsNotStored,
        ];
        $securityScore = (int) round((collect($checks)->filter()->count() / max(1, count($checks))) * 100);

        $lastActivity = UserActivityLog::query()
            ->where('user_id', $user->id)
            ->latest('occurred_at')
            ->value('occurred_at');

        $rows = [
            [
                'key' => 'two_factor',
                'label' => 'Two-factor authentication (2FA)',
                'hint' => $twoFactorOn
                    ? 'OTP is required on this login ('.strtoupper((string) $user->two_factor_method).').'
                    : 'Not enabled on your account. Login does not ask for OTP until you set it up.',
                'on' => $twoFactorOn,
                'href' => route('two-factor.setup'),
                'action' => $twoFactorOn ? 'Manage 2FA' : 'Enable 2FA',
            ],
            [
                'key' => 'https',
                'label' => 'HTTPS connection',
                'hint' => $httpsOn
                    ? 'This page is served over HTTPS.'
                    : 'This page is HTTP (typical on local XAMPP). Production should use HTTPS.',
                'on' => $httpsOn,
                'href' => null,
                'action' => null,
            ],
            [
                'key' => 'session',
                'label' => 'Session timeout',
                'hint' => 'Laravel logs you out after '.$sessionMinutes.' minutes of inactivity (SESSION_LIFETIME).',
                'on' => $sessionMinutes > 0,
                'href' => null,
                'action' => null,
            ],
            [
                'key' => 'ip_whitelist',
                'label' => 'IP whitelist',
                'hint' => 'Not configured. Admin access is not restricted by IP in this build.',
                'on' => $ipWhitelistOn,
                'href' => null,
                'action' => null,
            ],
            [
                'key' => 'audit',
                'label' => 'Audit logs',
                'hint' => 'Panel actions are written to the activity log.',
                'on' => $auditOn,
                'href' => $user->can('view-activity-log') ? route('activity.index') : null,
                'action' => $user->can('view-activity-log') ? 'View log' : null,
            ],
            [
                'key' => 'encryption',
                'label' => 'Sensitive field encryption',
                'hint' => $fullDbEncryptionOn
                    ? 'Session encryption is on. 2FA secrets are encrypted at rest.'
                    : '2FA secrets are encrypted. Full database encryption at rest is not enabled.',
                'on' => true,
                'href' => null,
                'action' => null,
            ],
            [
                'key' => 'pci',
                'label' => 'Card data (PCI)',
                'hint' => 'Card numbers are not stored in EasyGrox. Checkout goes through Cashfree.',
                'on' => $cardsNotStored,
                'href' => null,
                'action' => null,
            ],
        ];

        return view('security-support.index', [
            'salon' => $salon,
            'user' => $user,
            'rows' => $rows,
            'securityScore' => $securityScore,
            'lastActivity' => $lastActivity,
            'httpsOn' => $httpsOn,
            'twoFactorOn' => $twoFactorOn,
        ]);
    }

    public function updateSecurity(): RedirectResponse
    {
        return redirect()->route('security-support.index')
            ->with('info', 'Security status is live. Enable 2FA from the 2FA page — toggles here were not connected to login.');
    }
}
