@extends('layouts.app')
@section('title', 'Security & Support')
@section('page-title', 'Security & Support')

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ tab: 'security' }">
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-5 sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-widest text-velour-700 dark:text-velour-300">Account</p>
        <h1 class="section-title mt-1 text-heading">Security &amp; Support</h1>
        <p class="page-subtitle mt-1">Live status for this login — not placeholder toggles.</p>
        <div class="mt-4 inline-flex flex-wrap gap-1 p-1 rounded-full bg-stone-100 dark:bg-gray-800">
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold" :class="tab==='security' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='security'">Security</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold" :class="tab==='privacy' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='privacy'">Privacy</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold" :class="tab==='support' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='support'">Support</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold" :class="tab==='migration' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='migration'">Migration</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
        <div class="card p-4">
            <p class="stat-label">Security score</p>
            <p class="text-xl font-bold text-heading mt-0.5">{{ $securityScore }}/100</p>
            <p class="text-xs text-muted mt-1">From 2FA, HTTPS, verified email, audit log, and card handling.</p>
        </div>
        <div class="card p-4">
            <p class="stat-label">Last account activity</p>
            <p class="text-xl font-bold text-heading mt-0.5">
                {{ $lastActivity ? \Carbon\Carbon::parse($lastActivity)->format('j M Y') : '—' }}
            </p>
            <p class="text-xs text-muted mt-1">
                Last login: {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('j M Y, H:i') : 'Unknown' }}
            </p>
        </div>
        <div class="card p-4">
            <p class="stat-label">This connection</p>
            <p class="text-xl font-bold text-heading mt-0.5">{{ $httpsOn ? 'HTTPS' : 'HTTP' }}</p>
            <p class="text-xs text-muted mt-1">{{ $httpsOn ? 'Encrypted in transit' : 'Local / not using SSL' }}</p>
        </div>
    </div>

    <div x-show="tab==='security'" x-cloak class="card p-0 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
            <h2 class="section-title">Security status</h2>
            <p class="text-xs text-muted mt-0.5">On means it is actually in effect. Off means it is not.</p>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($rows as $row)
            <div class="px-5 py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-heading">{{ $row['label'] }}</p>
                    <p class="text-xs text-muted mt-0.5">{{ $row['hint'] }}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($row['on'])
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">On</span>
                    @else
                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-lg bg-gray-100 dark:bg-gray-800 text-muted">Off</span>
                    @endif
                    @if($row['href'] && $row['action'])
                    <a href="{{ $row['href'] }}" class="btn-outline btn-sm">{{ $row['action'] }}</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="card-footer flex flex-wrap justify-end gap-2">
            <a href="{{ route('two-factor.setup') }}" class="btn-primary btn-sm">
                {{ $twoFactorOn ? 'Manage 2FA' : 'Enable 2FA' }}
            </a>
            @if($twoFactorOn)
            <a href="{{ route('two-factor.recovery') }}" class="btn-outline btn-sm">Recovery codes</a>
            @endif
        </div>
    </div>

    <div x-show="tab==='privacy'" x-cloak class="card p-5">
        <h2 class="section-title mb-1">Privacy</h2>
        <p class="text-xs text-muted mb-4">Export client data or review retention in Settings.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.export') }}" class="btn-outline btn-sm admin-browse-allow">Export client data</a>
            <a href="{{ route('settings.index') }}" class="btn-outline btn-sm">Settings</a>
        </div>
    </div>

    <div x-show="tab==='support'" x-cloak class="card p-5">
        <h2 class="section-title mb-1">Support</h2>
        <p class="text-xs text-muted mb-4">Account help and operational links.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('settings.index') }}">
                <p class="text-sm font-semibold text-heading">Account settings</p>
                <p class="text-xs text-muted mt-1">Profile, password, business</p>
            </a>
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('notifications.index') }}">
                <p class="text-sm font-semibold text-heading">Notifications</p>
                <p class="text-xs text-muted mt-1">Alerts and system activity</p>
            </a>
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('guide.index') }}">
                <p class="text-sm font-semibold text-heading">Guide &amp; Setup</p>
                <p class="text-xs text-muted mt-1">How the salon panel works</p>
            </a>
        </div>
    </div>

    <div x-show="tab==='migration'" x-cloak class="card p-5">
        <h2 class="section-title mb-1">Migration</h2>
        <p class="text-xs text-muted mb-4">Move data with CSV import/export.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.index') }}" class="btn-outline btn-sm">Clients</a>
            <a href="{{ route('services.index') }}" class="btn-outline btn-sm">Services</a>
            <a href="{{ route('staff.index') }}" class="btn-outline btn-sm">Staff &amp; HR</a>
        </div>
    </div>
</div>
@endsection
