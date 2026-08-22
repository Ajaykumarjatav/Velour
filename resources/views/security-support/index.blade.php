@extends('layouts.app')
@section('title', 'Security & Support')
@section('page-title', 'Security & Support')

@section('content')
<div class="space-y-6" x-data="{ tab: 'security' }">
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div>
            <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-heading leading-tight">Security &amp; Support</h1>
            <p class="text-sm text-muted mt-1">Live status for this login — not placeholder toggles.</p>
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
            <p class="text-sm text-muted">Security score</p>
            <p class="text-2xl font-bold text-heading">{{ $securityScore }}/100</p>
            <p class="text-xs text-muted mt-1">From 2FA, HTTPS, verified email, audit log, and card handling.</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-muted">Last account activity</p>
            <p class="text-2xl font-bold text-heading">
                {{ $lastActivity ? \Carbon\Carbon::parse($lastActivity)->format('j M Y') : '—' }}
            </p>
            <p class="text-xs text-muted mt-1">
                Last login: {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('j M Y, H:i') : 'Unknown' }}
            </p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-muted">This connection</p>
            <p class="text-2xl font-bold text-heading">{{ $httpsOn ? 'HTTPS' : 'HTTP' }}</p>
            <p class="text-xs text-muted mt-1">{{ $httpsOn ? 'Encrypted in transit' : 'Local / not using SSL' }}</p>
        </div>
    </div>

    <div x-show="tab==='security'" x-cloak class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-heading">Security status</h2>
            <p class="text-sm text-muted mt-1">On means it is actually in effect. Off means it is not.</p>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($rows as $row)
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-semibold text-heading">{{ $row['label'] }}</p>
                    <p class="text-sm text-muted">{{ $row['hint'] }}</p>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    @if($row['on'])
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">On</span>
                    @else
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-800 text-muted">Off</span>
                    @endif
                    @if($row['href'] && $row['action'])
                    <a href="{{ $row['href'] }}" class="btn-outline btn-sm">{{ $row['action'] }}</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex flex-wrap justify-end gap-2">
            <a href="{{ route('two-factor.setup') }}" class="btn-primary">
                {{ $twoFactorOn ? 'Manage 2FA' : 'Enable 2FA' }}
            </a>
            @if($twoFactorOn)
            <a href="{{ route('two-factor.recovery') }}" class="btn-outline">Recovery codes</a>
            @endif
        </div>
    </div>

    <div x-show="tab==='privacy'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Privacy</h2>
        <p class="text-sm text-muted mb-4">Export client data or review retention in Settings.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.export') }}" class="btn-outline btn-sm admin-browse-allow">Export client data</a>
            <a href="{{ route('settings.index') }}" class="btn-outline btn-sm">Settings</a>
        </div>
    </div>

    <div x-show="tab==='support'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Support</h2>
        <p class="text-sm text-muted mb-4">Account help and operational links.</p>
        <div class="grid sm:grid-cols-2 gap-3">
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('settings.index') }}">
                <p class="font-semibold text-heading">Account settings</p>
                <p class="text-xs text-muted mt-1">Profile, password, business</p>
            </a>
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('notifications.index') }}">
                <p class="font-semibold text-heading">Notifications</p>
                <p class="text-xs text-muted mt-1">Alerts and system activity</p>
            </a>
            <a class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" href="{{ route('guide.index') }}">
                <p class="font-semibold text-heading">Guide &amp; Setup</p>
                <p class="text-xs text-muted mt-1">How the salon panel works</p>
            </a>
        </div>
    </div>

    <div x-show="tab==='migration'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Migration</h2>
        <p class="text-sm text-muted mb-4">Move data with CSV import/export.</p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.index') }}" class="btn-outline btn-sm">Clients</a>
            <a href="{{ route('services.index') }}" class="btn-outline btn-sm">Services</a>
            <a href="{{ route('staff.index') }}" class="btn-outline btn-sm">Staff &amp; HR</a>
        </div>
    </div>
</div>
@endsection
