<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * LegalController — AUDIT FIX: Legal & Compliance Pages
 *
 * Renders legal pages from docs/ as web routes.
 * Also handles cookie consent recording (GDPR).
 */
class LegalController extends Controller
{
    public function privacy()
    {
        return view('legal.privacy');
    }

    public function terms()
    {
        return view('legal.terms');
    }

    public function cookies()
    {
        return view('legal.cookies');
    }

    public function recordConsent(Request $request)
    {
        $data = $request->validate([
            'analytics'  => 'boolean',
            'marketing'  => 'boolean',
            'functional' => 'boolean',
        ]);

        DB::table('cookie_consents')->insert([
            'user_id'          => auth()->id(),
            'session_id'       => $request->session()->getId(),
            'ip_address'       => $request->ip(),
            'essential'        => true,
            'analytics'        => $data['analytics'] ?? false,
            'marketing'        => $data['marketing'] ?? false,
            'functional'       => $data['functional'] ?? false,
            'consent_version'  => config('velour.cookie_consent_version', '1.0'),
            'consented_at'     => now(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        $request->session()->put('cookie_consent', 'accepted');

        return response()->json(['accepted' => true]);
    }
}
