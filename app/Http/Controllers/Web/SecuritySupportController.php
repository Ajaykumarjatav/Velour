<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\SalonSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SecuritySupportController extends Controller
{
    private function salon(): Salon
    {
        $user = Auth::user();
        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0 ? $user->salons()->where('id', $activeSalonId)->first() : null;
        return $salon ?: $user->salons()->firstOrFail();
    }

    public function index(): View
    {
        $salon = $this->salon();
        $get = fn (string $key, string $default = '0') => (string) (
            SalonSetting::where('salon_id', $salon->id)->where('key', $key)->value('value') ?? $default
        );

        $security = [
            'two_factor_required' => $get('sec_two_factor_required', '1') === '1',
            'session_timeout' => $get('sec_session_timeout', '1') === '1',
            'ip_whitelist' => $get('sec_ip_whitelist', '0') === '1',
            'audit_logs' => $get('sec_audit_logs', '1') === '1',
            'encryption_at_rest' => $get('sec_encryption_rest', '1') === '1',
            'pci_dss' => $get('sec_pci_dss', '1') === '1',
        ];

        $score = collect($security)->filter()->count();
        $securityScore = (int) round(($score / max(1, count($security))) * 100);
        $auditDate = $get('sec_last_audit_date', now()->startOfMonth()->format('Y-m-d'));
        $sslDays = (int) $get('sec_ssl_valid_days', '340');

        return view('security-support.index', [
            'salon' => $salon,
            'security' => $security,
            'securityScore' => $securityScore,
            'auditDate' => $auditDate,
            'sslDays' => $sslDays,
        ]);
    }

    public function updateSecurity(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $data = $request->validate([
            'two_factor_required' => ['nullable', 'boolean'],
            'session_timeout' => ['nullable', 'boolean'],
            'ip_whitelist' => ['nullable', 'boolean'],
            'audit_logs' => ['nullable', 'boolean'],
            'encryption_at_rest' => ['nullable', 'boolean'],
            'pci_dss' => ['nullable', 'boolean'],
        ]);

        $map = [
            'two_factor_required' => 'sec_two_factor_required',
            'session_timeout' => 'sec_session_timeout',
            'ip_whitelist' => 'sec_ip_whitelist',
            'audit_logs' => 'sec_audit_logs',
            'encryption_at_rest' => 'sec_encryption_rest',
            'pci_dss' => 'sec_pci_dss',
        ];

        foreach ($map as $field => $key) {
            SalonSetting::updateOrCreate(
                ['salon_id' => $salon->id, 'key' => $key],
                ['value' => ($request->boolean($field) ? '1' : '0'), 'type' => 'boolean']
            );
        }
        SalonSetting::updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'sec_last_audit_date'],
            ['value' => now()->toDateString(), 'type' => 'string']
        );

        return redirect()->route('security-support.index')->with('success', 'Security settings updated.');
    }
}

