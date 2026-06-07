@extends('layouts.app')
@section('title', 'Security & Support')
@section('page-title', 'Security & Support')

@section('content')
<div class="space-y-6" x-data="{ tab: 'security' }">
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">Security &amp; Support</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Data protection, privacy &amp; onboarding</p>
            </div>
        </div>

        <div class="mt-6 inline-flex flex-wrap gap-1 p-1.5 rounded-full bg-stone-100 dark:bg-gray-800">
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='security' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='security'">Security</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='privacy' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='privacy'">Privacy</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='support' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='support'">Support</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='migration' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='migration'">Migration</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        <div class="card p-4">
            <p class="text-sm text-muted">Security Score</p>
            <p class="text-2xl font-bold text-heading">{{ $securityScore }}/100</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-muted">Last Audit</p>
            <p class="text-2xl font-bold text-heading">{{ \Carbon\Carbon::parse($auditDate)->format('j M Y') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-muted">SSL Certificate</p>
            <p class="text-2xl font-bold text-heading">Valid · {{ $sslDays }} days</p>
        </div>
    </div>

    <div x-show="tab==='security'" x-cloak class="card p-0 overflow-hidden"
         x-data="{
            s: {
                two_factor_required: {{ $security['two_factor_required'] ? 'true' : 'false' }},
                session_timeout: {{ $security['session_timeout'] ? 'true' : 'false' }},
                ip_whitelist: {{ $security['ip_whitelist'] ? 'true' : 'false' }},
                audit_logs: {{ $security['audit_logs'] ? 'true' : 'false' }},
                encryption_at_rest: {{ $security['encryption_at_rest'] ? 'true' : 'false' }},
                pci_dss: {{ $security['pci_dss'] ? 'true' : 'false' }},
            },
            toggle(k){ this.s[k] = !this.s[k] }
         }">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-heading">Security Settings</h2>
        </div>
        <form method="POST" action="{{ route('security-support.security.update') }}">
            @csrf
            @method('PUT')
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                    $rows = [
                        ['two_factor_required','Two-Factor Authentication (2FA)','Require OTP for all admin logins'],
                        ['session_timeout','Session Timeout','Auto-logout after 30 minutes of inactivity'],
                        ['ip_whitelist','IP Whitelist','Restrict admin access to specific IP addresses'],
                        ['audit_logs','Audit Logs','Track all user actions & data changes'],
                        ['encryption_at_rest','Data Encryption at Rest','AES-256 encryption for all stored data'],
                        ['pci_dss','PCI-DSS Compliance','Payment card data security standards'],
                    ];
                @endphp
                @foreach($rows as [$key,$label,$desc])
                    <div class="px-6 py-4 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-heading">{{ $label }}</p>
                            <p class="text-sm text-muted">{{ $desc }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="{{ $key }}" :value="s.{{ $key }} ? 1 : 0">
                            <button type="button"
                                    @click="toggle('{{ $key }}')"
                                    class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-velour-400"
                                    :class="s.{{ $key }} ? 'bg-velour-600' : 'bg-gray-300 dark:bg-gray-600'"
                                    :aria-checked="s.{{ $key }} ? 'true' : 'false'"
                                    role="switch"
                                    aria-label="{{ $label }}">
                                <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition"
                                      :class="s.{{ $key }} ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex justify-end">
                <button type="submit" class="btn-primary">Save Security Settings</button>
            </div>
        </form>
    </div>

    <div x-show="tab==='privacy'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Privacy Controls</h2>
        <p class="text-sm text-muted mb-4">Manage customer data export, retention and request handling.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.export') }}" class="btn-outline btn-sm">Export client data</a>
            <a href="{{ route('settings.index') }}" class="btn-outline btn-sm">Data retention settings</a>
        </div>
    </div>

    <div x-show="tab==='support'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Support</h2>
        <p class="text-sm text-muted mb-4">Fast links for account help and operational support.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <a class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('settings.index') }}">
                <p class="font-semibold text-heading">Account Settings</p>
                <p class="text-xs text-muted mt-1">Profile, password and business settings</p>
            </a>
            <a class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('notifications.index') }}">
                <p class="font-semibold text-heading">Notifications</p>
                <p class="text-xs text-muted mt-1">View alerts and system activity</p>
            </a>
        </div>
    </div>

    <div x-show="tab==='migration'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Migration</h2>
        <p class="text-sm text-muted mb-4">Move data from your current tools with CSV import/export.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.index') }}" class="btn-outline btn-sm">Clients import/export</a>
            <a href="{{ route('services.index') }}" class="btn-outline btn-sm">Review services data</a>
            <a href="{{ route('staff.index') }}" class="btn-outline btn-sm">Review team setup</a>
        </div>
    </div>
</div>
@endsection

