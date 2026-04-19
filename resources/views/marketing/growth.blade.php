@extends('layouts.app')
@section('title', 'Marketing & Growth')
{{-- Top bar: short label; full title lives in the hero (avoids duplicate + theme clash) --}}
@section('page-title', 'Marketing')

@section('content')
@php
    $audienceLabels = [
        'all' => 'All clients', 'active' => 'Active (90d)', 'lapsed' => 'Lapsed (90d+)',
        'birthday' => 'Birthday this month', 'new' => 'New clients (30d)',
    ];
    $hubStatus = function ($c) {
        if (in_array($c->status, ['draft', 'scheduled', 'sending'], true)) {
            return ['label' => 'Active', 'cls' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/35 dark:text-emerald-200'];
        }
        if ($c->status === 'sent') {
            return ['label' => 'Completed', 'cls' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/35 dark:text-violet-200'];
        }
        return ['label' => ucfirst($c->status), 'cls' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'];
    };
    $tabLinks = fn (string $t) => route('marketing.growth', array_filter([
        'tab' => $t !== 'campaigns' ? $t : null,
        'status' => request('status'),
        'thread' => $t === 'communications' ? request('thread') : null,
    ]));
@endphp

<div class="space-y-6 text-body"
     x-data="{
        menuOpen: null,
        tierModal: null,
        referralOpen: false,
        templateModal: null,
        closeMenus() { this.menuOpen = null },
        openTier(m) { this.tierModal = m },
        openTemplate(m) { this.templateModal = m }
     }"
     x-on:keydown.escape.window="closeMenus(); tierModal=null; referralOpen=false; templateModal=null">

    {{-- Hero: light = cream + dark type; dark mode = dark card + light type (never light-on-cream) --}}
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-700 overflow-hidden shadow-sm
                bg-[#FFF9F2] dark:bg-gray-900 dark:shadow-gray-950/50">
        <div class="p-6 sm:p-8 pb-5 sm:pb-6">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6">
                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight leading-tight
                               text-gray-900 dark:text-white">
                        Marketing &amp; Growth
                    </h1>
                    <p class="mt-2 text-sm sm:text-base max-w-xl text-gray-600 dark:text-gray-400">
                        Campaigns, loyalty &amp; referral programs.
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 shrink-0 sm:pt-1">
                    <a href="{{ route('marketing.index') }}"
                       class="inline-flex items-center justify-center rounded-full px-4 py-2.5 text-sm font-semibold whitespace-nowrap transition-colors
                              border border-stone-300 bg-white text-gray-800 hover:bg-stone-50
                              dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                        Classic campaign list
                    </a>
                    <a href="{{ route('marketing.create') }}"
                       class="inline-flex items-center justify-center rounded-full bg-velour-600 hover:bg-velour-700 text-white px-4 py-2.5 text-sm font-semibold shadow-sm transition-colors whitespace-nowrap">
                        + New Campaign
                    </a>
                </div>
            </div>

            <nav class="mt-8 flex justify-start" aria-label="Marketing sections">
                <div class="inline-flex flex-wrap gap-1 p-1.5 rounded-full max-w-full
                            bg-stone-900 dark:bg-black/55
                            ring-1 ring-black/10 dark:ring-white/10">
                    @foreach([
                        'campaigns' => 'Campaigns',
                        'loyalty' => 'Loyalty',
                        'referrals' => 'Referrals',
                        'communications' => 'SMS & Email',
                    ] as $tk => $tl)
                        <a href="{{ $tabLinks($tk) }}"
                           class="px-4 py-2 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap
                               {{ $tab === $tk
                                   ? 'bg-velour-600 text-white shadow-md'
                                   : 'text-stone-300 hover:text-white dark:text-gray-300 dark:hover:text-white' }}">
                            {{ $tl }}
                        </a>
                    @endforeach
                </div>
            </nav>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 dark:bg-emerald-900/20 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Campaigns tab --}}
    @if($tab === 'campaigns')
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="card p-4 flex items-center gap-3">
                <div class="rounded-xl bg-velour-100 dark:bg-velour-900/40 p-2 text-velour-700 dark:text-velour-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-heading">{{ number_format($totalSent) }}</p>
                    <p class="text-xs text-muted font-medium uppercase tracking-wide">Total sent</p>
                </div>
            </div>
            <div class="card p-4 flex items-center gap-3">
                <div class="rounded-xl bg-violet-100 dark:bg-violet-900/30 p-2 text-violet-700 dark:text-violet-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-heading">{{ $avgOpenRate }}%</p>
                    <p class="text-xs text-muted font-medium uppercase tracking-wide">Avg open rate</p>
                </div>
            </div>
            <div class="card p-4 flex items-center gap-3">
                <div class="rounded-xl bg-amber-100 dark:bg-amber-900/30 p-2 text-amber-700 dark:text-amber-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-heading">{{ number_format($conversions) }}</p>
                    <p class="text-xs text-muted font-medium uppercase tracking-wide">Conversions</p>
                </div>
            </div>
            <div class="card p-4 flex items-center gap-3">
                <div class="rounded-xl bg-emerald-100 dark:bg-emerald-900/30 p-2 text-emerald-700 dark:text-emerald-300">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-heading">@money($revenueTotal)</p>
                    <p class="text-xs text-muted font-medium uppercase tracking-wide">Revenue</p>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
            <form action="{{ route('marketing.growth') }}" method="GET" class="flex flex-wrap gap-2 items-center">
                <input type="hidden" name="tab" value="campaigns">
                <label class="text-xs font-semibold text-muted uppercase">Status</label>
                <select name="status" onchange="this.form.submit()" class="form-select w-auto min-w-[160px]">
                    <option value="">All</option>
                    @foreach(['draft','scheduled','sending','sent'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="space-y-4">
            @forelse($hubCampaigns as $c)
                @php $hs = $hubStatus($c); $seg = $c->segment ?? 'all'; @endphp
                <div class="card p-5 sm:p-6 relative border-stone-200/80 dark:border-gray-800 shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-semibold text-heading">{{ $c->name }}</h2>
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $hs['cls'] }}">{{ $hs['label'] }}</span>
                            </div>
                            <p class="text-sm text-muted mt-1">
                                Channel: <span class="text-body font-medium">{{ strtoupper($c->type) }}</span>
                                <span class="mx-1">·</span>
                                Audience: <span class="text-body font-medium">{{ $audienceLabels[$seg] ?? ucfirst($seg) }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <p class="text-lg font-bold text-heading">@money((float) $c->revenue_generated)</p>
                            <div class="relative" x-on:click.outside="if(menuOpen === {{ $c->id }}) menuOpen = null">
                                <button type="button"
                                        class="p-2 rounded-lg border border-stone-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-stone-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white"
                                        title="Campaign actions"
                                        x-on:click.stop="menuOpen = menuOpen === {{ $c->id }} ? null : {{ $c->id }}">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                    </svg>
                                </button>
                                <div x-show="menuOpen === {{ $c->id }}" x-cloak
                                     class="absolute right-0 mt-1 w-48 rounded-xl border border-stone-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg z-50 py-1 text-sm text-gray-800 dark:text-gray-100">
                                    <a href="{{ route('marketing.show', $c) }}" class="flex items-center gap-2 px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">View details</a>
                                    <form action="{{ route('marketing.duplicate', $c) }}" method="POST" class="block">
                                        @csrf
                                        <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">Duplicate</button>
                                    </form>
                                    @if(in_array($c->status, ['draft','scheduled'], true))
                                        <a href="{{ route('marketing.edit', $c) }}" class="flex items-center gap-2 px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">Edit</a>
                                    @endif
                                    @if($c->status === 'draft')
                                        <form action="{{ route('marketing.destroy', $c) }}" method="POST" onsubmit="return confirm('Delete this campaign?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-full text-left text-red-600 dark:text-red-400 px-3 py-2 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">
                        <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-3">
                            <p class="text-xs text-muted uppercase font-medium">Sent</p>
                            <p class="text-lg font-bold text-heading mt-0.5">{{ number_format((int) $c->sent_count) }}</p>
                        </div>
                        <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-3">
                            <p class="text-xs text-muted uppercase font-medium">Opened</p>
                            <p class="text-lg font-bold text-heading mt-0.5">{{ number_format((int) $c->opened_count) }} @if($c->sent_count > 0)<span class="text-sm font-normal text-muted">({{ $c->open_rate }}%)</span>@endif</p>
                        </div>
                        <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-3">
                            <p class="text-xs text-muted uppercase font-medium">Converted</p>
                            <p class="text-lg font-bold text-heading mt-0.5">{{ number_format((int) $c->booking_count) }} @if($c->sent_count > 0)<span class="text-sm font-normal text-muted">({{ $c->conversion_rate }}%)</span>@endif</p>
                        </div>
                        <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-3">
                            <p class="text-xs text-muted uppercase font-medium">Revenue</p>
                            <p class="text-lg font-bold text-heading mt-0.5">@money((float) $c->revenue_generated)</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card p-12 text-center text-muted text-sm">No campaigns yet. <a href="{{ route('marketing.create') }}" class="text-link font-medium">Create one</a>.</div>
            @endforelse
        </div>
    @endif

    {{-- Loyalty tab --}}
    @if($tab === 'loyalty')
        <div class="flex flex-wrap justify-between gap-3 items-center">
            <p class="text-sm text-muted">Membership tiers and benefits. Service discount % is available for booking &amp; POS integrations.</p>
            <button type="button" class="btn-primary btn-sm" x-on:click="openTier({ id: null, name: '', price_monthly: '', service_discount_percent: 0, benefits: '' })">+ Add plan</button>
        </div>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach($loyaltyTiers as $tier)
                <div class="card p-6 border-stone-200/80 dark:border-gray-800 flex flex-col">
                    <h3 class="text-xl text-heading mb-1">{{ $tier->name }}</h3>
                    <p class="text-2xl font-bold text-heading">@money((float) $tier->price_monthly)<span class="text-sm font-normal text-muted">/mo</span></p>
                    <p class="text-sm text-muted mt-2">{{ $tier->member_count }} active {{ Str::plural('member', $tier->member_count) }}</p>
                    @if($tier->service_discount_percent > 0)
                        <p class="text-xs font-semibold text-velour-700 dark:text-velour-300 mt-1">{{ $tier->service_discount_percent }}% off services (at checkout)</p>
                    @endif
                    <ul class="mt-4 space-y-2 text-sm flex-1">
                        @foreach($tier->benefits ?? [] as $line)
                            <li class="flex gap-2"><span class="text-emerald-600 dark:text-emerald-400">✓</span> {{ $line }}</li>
                        @endforeach
                    </ul>
                    <div class="flex flex-wrap gap-2 mt-6">
                        <button type="button" class="btn-primary btn-sm flex-1"
                                x-on:click="openTier({ id: {{ $tier->id }}, name: @json($tier->name), price_monthly: @json((string) $tier->price_monthly), service_discount_percent: {{ (int) $tier->service_discount_percent }}, benefits: @json(implode("\n", $tier->benefits ?? [])) })">Edit plan</button>
                        <a href="{{ route('marketing.loyalty.tiers.members', $tier) }}" class="btn-outline btn-sm flex-1 text-center">View members</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div x-show="tierModal !== null" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/40" x-on:click.self="tierModal=null">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6 border border-stone-200 dark:border-gray-700" x-show="tierModal !== null">
                <form x-show="tierModal && tierModal.id != null" x-cloak :action="'{{ url('marketing/loyalty/tiers') }}/' + tierModal.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <h3 class="font-semibold text-heading text-lg">Edit plan</h3>
                    <div><label class="form-label">Name</label><input type="text" name="name" x-model="tierModal.name" class="form-input w-full" required></div>
                    <div><label class="form-label">Monthly price</label><input type="number" step="0.01" name="price_monthly" x-model="tierModal.price_monthly" class="form-input w-full" required></div>
                    <div><label class="form-label">Service discount %</label><input type="number" name="service_discount_percent" min="0" max="100" x-model="tierModal.service_discount_percent" class="form-input w-full"></div>
                    <div><label class="form-label">Benefits (one per line)</label><textarea name="benefits" rows="4" x-model="tierModal.benefits" class="form-textarea w-full"></textarea></div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="btn-outline" x-on:click="tierModal=null">Cancel</button>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
                <form x-show="tierModal && tierModal.id === null" x-cloak action="{{ route('marketing.loyalty.tiers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <h3 class="font-semibold text-heading text-lg">New plan</h3>
                    <div><label class="form-label">Name</label><input type="text" name="name" class="form-input w-full" required></div>
                    <div><label class="form-label">Monthly price</label><input type="number" step="0.01" name="price_monthly" class="form-input w-full" required></div>
                    <div><label class="form-label">Service discount %</label><input type="number" name="service_discount_percent" value="0" min="0" max="100" class="form-input w-full"></div>
                    <div><label class="form-label">Benefits (one per line)</label><textarea name="benefits" rows="4" class="form-textarea w-full"></textarea></div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="btn-outline" x-on:click="tierModal=null">Cancel</button>
                        <button type="submit" class="btn-primary">Create</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Referrals tab --}}
    @if($tab === 'referrals')
        <div class="card p-6 border-stone-200/80 dark:border-gray-800">
            <div class="flex flex-wrap justify-between gap-3 mb-6">
                <h2 class="text-xl text-heading">Referral program</h2>
                <button type="button" class="btn-outline btn-sm" x-on:click="referralOpen = true">Edit program</button>
            </div>
            <div class="grid sm:grid-cols-3 gap-4 mb-8">
                <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-4">
                    <p class="text-xs text-muted uppercase font-medium">Total referrals</p>
                    <p class="text-2xl font-bold text-heading mt-1">{{ number_format($totalReferrals) }}</p>
                </div>
                <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-4">
                    <p class="text-xs text-muted uppercase font-medium">Earned credits (est.)</p>
                    <p class="text-2xl font-bold text-heading mt-1">@money($earnedCreditsEstimate)</p>
                </div>
                <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-4">
                    <p class="text-xs text-muted uppercase font-medium">Conversion rate</p>
                    <p class="text-2xl font-bold text-heading mt-1">{{ $referralConversionRate }}%</p>
                </div>
            </div>
            <h3 class="text-sm font-semibold text-heading mb-3">Current referral settings</h3>
            <dl class="divide-y divide-stone-200 dark:divide-gray-700 text-sm">
                <div class="flex justify-between py-3"><dt class="text-muted">Referrer reward</dt><dd class="font-medium text-heading">@money((float) $referralSettings->referrer_reward_amount) credit on referral booking</dd></div>
                <div class="flex justify-between py-3"><dt class="text-muted">Referee reward</dt><dd class="font-medium text-heading">@money((float) $referralSettings->referee_reward_amount) off first visit</dd></div>
                <div class="flex justify-between py-3"><dt class="text-muted">Minimum spend</dt><dd class="font-medium text-heading">@money((float) $referralSettings->minimum_spend) to unlock referral credit</dd></div>
                <div class="flex justify-between py-3"><dt class="text-muted">Expiry</dt><dd class="font-medium text-heading">Credits valid for {{ $referralSettings->credit_expiry_days }} days</dd></div>
            </dl>
        </div>

        <div x-show="referralOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/40" x-on:click.self="referralOpen=false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full p-6 border border-stone-200 dark:border-gray-700">
                <form action="{{ route('marketing.referral-settings.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <h3 class="font-semibold text-heading text-lg">Edit referral program</h3>
                    <div><label class="form-label">Referrer reward amount</label><input type="number" step="0.01" name="referrer_reward_amount" value="{{ old('referrer_reward_amount', $referralSettings->referrer_reward_amount) }}" class="form-input w-full" required></div>
                    <div><label class="form-label">Referee reward amount</label><input type="number" step="0.01" name="referee_reward_amount" value="{{ old('referee_reward_amount', $referralSettings->referee_reward_amount) }}" class="form-input w-full" required></div>
                    <div><label class="form-label">Minimum spend</label><input type="number" step="0.01" name="minimum_spend" value="{{ old('minimum_spend', $referralSettings->minimum_spend) }}" class="form-input w-full" required></div>
                    <div><label class="form-label">Credit expiry (days)</label><input type="number" name="credit_expiry_days" value="{{ old('credit_expiry_days', $referralSettings->credit_expiry_days) }}" class="form-input w-full" required></div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="btn-outline" x-on:click="referralOpen=false">Cancel</button>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Communications tab --}}
    @if($tab === 'communications')
        <div class="card p-6 border-stone-200/80 dark:border-gray-800">
            <div class="flex flex-wrap justify-between gap-3 mb-4">
                <h2 class="text-xl text-heading">SMS &amp; Email templates</h2>
            </div>
            <p class="text-sm text-muted mb-4">Toggles and copy are stored per salon. Connect Twilio/Mailgun to send automatically from booking and POS events.</p>
            <ul class="divide-y divide-stone-200 dark:divide-gray-700">
                @foreach($automationTemplates as $tpl)
                    <li class="py-4 flex flex-col sm:flex-row sm:items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-heading">{{ $tpl->name }}</p>
                            <p class="text-sm text-muted">{{ $tpl->channels_label }} · Trigger: {{ $tpl->trigger_label }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <form action="{{ route('marketing.automation-templates.toggle', $tpl) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors {{ $tpl->is_active ? 'bg-velour-600' : 'bg-gray-300 dark:bg-gray-600' }}" aria-label="Toggle {{ $tpl->name }}">
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition {{ $tpl->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                            <button type="button" class="p-2 rounded-lg border border-stone-200 dark:border-gray-700 hover:bg-stone-50 dark:hover:bg-gray-800"
                                    x-on:click="openTemplate({ id: {{ $tpl->id }}, name: @js($tpl->name), sms_body: @js($tpl->sms_body), email_subject: @js($tpl->email_subject), email_body: @js($tpl->email_body) })">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="card p-6 border-stone-200/80 dark:border-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="text-xl text-heading">Two-way SMS inbox</h2>
                @if($unreadSms > 0)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200">{{ $unreadSms }} unread</span>
                @endif
            </div>
            <p class="text-sm text-muted mb-4">Reply is logged for your team (SMS gateway integration can dispatch these in production).</p>
            @if($smsThreads->isEmpty())
                <p class="text-sm text-muted">No conversations yet.</p>
            @else
                <div class="grid lg:grid-cols-5 gap-4 min-h-[280px]">
                    <div class="lg:col-span-2 border border-stone-200 dark:border-gray-700 rounded-xl overflow-hidden max-h-[420px] overflow-y-auto">
                        @foreach($smsThreads as $th)
                            <a href="{{ route('marketing.growth', ['tab' => 'communications', 'thread' => $th->id]) }}"
                               class="block px-4 py-3 border-b border-stone-100 dark:border-gray-800 hover:bg-stone-50 dark:hover:bg-gray-800/50 {{ $activeSmsThread && $activeSmsThread->id === $th->id ? 'bg-velour-50 dark:bg-velour-900/20' : '' }}">
                                <div class="flex items-center gap-2">
                                    <span class="w-8 h-8 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-xs font-bold text-velour-800 dark:text-velour-200">{{ strtoupper(mb_substr($th->display_name, 0, 1)) }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-medium text-heading text-sm truncate">{{ $th->display_name }}</p>
                                        <p class="text-xs text-muted truncate">{{ $th->last_preview }}</p>
                                    </div>
                                    @if($th->unread_inbound > 0)
                                        <span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="lg:col-span-3 border border-stone-200 dark:border-gray-700 rounded-xl p-4 flex flex-col min-h-[320px]">
                        @if($activeSmsThread)
                            <div class="mb-3 pb-3 border-b border-stone-200 dark:border-gray-700">
                                <p class="font-semibold text-heading">{{ $activeSmsThread->display_name }}</p>
                                @if($activeSmsThread->phone)<p class="text-xs text-muted">{{ $activeSmsThread->phone }}</p>@endif
                            </div>
                            <div class="flex-1 overflow-y-auto space-y-3 mb-4 max-h-[240px]">
                                @foreach($activeSmsThread->messages as $msg)
                                    <div class="flex {{ $msg->direction === 'out' ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[85%] rounded-2xl px-3 py-2 text-sm {{ $msg->direction === 'out' ? 'bg-velour-600 text-white' : 'bg-amber-50 dark:bg-amber-900/25 text-heading' }}">
                                            {{ $msg->body }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <form action="{{ route('marketing.sms.reply', $activeSmsThread) }}" method="POST" class="flex gap-2 items-end">
                                @csrf
                                <input type="text" name="body" class="form-input flex-1" placeholder="Type a reply…" required autocomplete="off">
                                <button type="submit" class="btn-primary shrink-0" aria-label="Send">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div x-show="templateModal !== null" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/40" x-on:click.self="templateModal=null">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-lg w-full p-6 border border-stone-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto" x-show="templateModal !== null">
                <form x-show="templateModal" x-cloak :action="'{{ url('marketing/automation-templates') }}/' + templateModal.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <h3 class="font-semibold text-heading text-lg" x-text="templateModal.name"></h3>
                    <div><label class="form-label">SMS body</label><textarea name="sms_body" rows="3" x-model="templateModal.sms_body" class="form-textarea w-full font-mono text-sm"></textarea></div>
                    <div><label class="form-label">Email subject</label><input type="text" name="email_subject" x-model="templateModal.email_subject" class="form-input w-full"></div>
                    <div><label class="form-label">Email body</label><textarea name="email_body" rows="5" x-model="templateModal.email_body" class="form-textarea w-full font-mono text-sm"></textarea></div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="btn-outline" x-on:click="templateModal=null">Cancel</button>
                        <button type="submit" class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
