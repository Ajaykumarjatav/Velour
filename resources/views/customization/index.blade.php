@extends('layouts.app')
@section('title', 'Customization & Flexibility')
@section('page-title', 'Customization')

@section('content')
<div class="space-y-6" x-data="{ tab: 'white-label' }">
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">Customization &amp; Flexibility</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">White-label, multi-language, custom forms &amp; scalability</p>
            </div>
            <button type="button" class="btn-primary btn-sm">Request Custom Feature</button>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mt-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3"><p class="text-xs text-muted">White Label</p><p class="font-semibold text-heading">Active</p></div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3"><p class="text-xs text-muted">Languages</p><p class="font-semibold text-heading">{{ count($data['languages']) }} enabled</p></div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3"><p class="text-xs text-muted">Custom Forms</p><p class="font-semibold text-heading">{{ $data['custom_forms_count'] }} forms</p></div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3"><p class="text-xs text-muted">Custom Features</p><p class="font-semibold text-heading">{{ $data['custom_features_live'] }} live</p></div>
            <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3"><p class="text-xs text-muted">Plan</p><p class="font-semibold text-heading">{{ $data['plan_label'] }}</p></div>
        </div>

        <div class="mt-6 inline-flex flex-wrap gap-1 p-1.5 rounded-full bg-stone-100 dark:bg-gray-800">
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='white-label' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='white-label'">White Label</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='multi-language' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='multi-language'">Multi-Language</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='forms' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='forms'">Custom Forms</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='features' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='features'">Custom Features</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='scalability' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='scalability'">Scalability</button>
        </div>
    </div>

    <div x-show="tab==='white-label'" x-cloak class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-heading">Brand Identity</h2>
            <button form="brand-form" type="submit" class="btn-outline btn-sm">+ Save &amp; Publish</button>
        </div>
        <form id="brand-form" method="POST" action="{{ route('customization.brand.update') }}" enctype="multipart/form-data" class="p-6">
            @csrf
            <div class="grid lg:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div><label class="form-label">Business Name</label><input name="business_name" class="form-input" value="{{ old('business_name', $salon->name) }}"></div>
                    <div><label class="form-label">Tagline</label><input name="tagline" class="form-input" value="{{ old('tagline', $data['tagline']) }}"></div>
                    <div><label class="form-label">Custom Domain</label><input name="custom_domain" class="form-input" value="{{ old('custom_domain', $salon->domain) }}"></div>
                    <div><label class="form-label">Support Email</label><input name="support_email" class="form-input" value="{{ old('support_email', $data['support_email']) }}"></div>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="form-label">Logo Upload</label>
                        <label class="block rounded-xl border-2 border-dashed border-stone-300 dark:border-gray-700 p-6 text-center cursor-pointer hover:bg-stone-50 dark:hover:bg-gray-800/50">
                            <input type="file" name="logo" accept="image/*" class="hidden">
                            <p class="text-sm text-muted">Upload Logo (PNG, SVG, JPEG)</p>
                            @if($salon->logo)
                                <img src="{{ asset('storage/'.$salon->logo) }}" alt="Logo" class="mx-auto mt-3 h-12">
                            @endif
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-2">
                        <label class="flex items-center justify-between rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5 text-sm">Primary Color <span class="flex items-center gap-2"><input type="color" name="primary_color" value="{{ old('primary_color', $data['primary_color']) }}" class="w-6 h-6 border-0 p-0 bg-transparent"><span>{{ old('primary_color', $data['primary_color']) }}</span></span></label>
                        <label class="flex items-center justify-between rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5 text-sm">Secondary <span class="flex items-center gap-2"><input type="color" name="secondary_color" value="{{ old('secondary_color', $data['secondary_color']) }}" class="w-6 h-6 border-0 p-0 bg-transparent"><span>{{ old('secondary_color', $data['secondary_color']) }}</span></span></label>
                        <label class="flex items-center justify-between rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5 text-sm">Accent <span class="flex items-center gap-2"><input type="color" name="accent_color" value="{{ old('accent_color', $data['accent_color']) }}" class="w-6 h-6 border-0 p-0 bg-transparent"><span>{{ old('accent_color', $data['accent_color']) }}</span></span></label>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div x-show="tab==='white-label'" x-cloak class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-xl font-semibold text-heading">White-Label Options</h2>
        </div>
        <form method="POST" action="{{ route('customization.options.update') }}" class="p-6">
            @csrf
            @method('PUT')
            @php
                $wlRows = [
                    ['wl_remove_branding','Remove GlowSuite branding','No "Powered by" on client pages'],
                    ['wl_custom_email_sender','Custom email sender name','Emails sent from your salon name'],
                    ['wl_custom_sms_sender','Custom SMS sender ID','SMS show your salon name as sender'],
                    ['wl_custom_booking_url','Custom booking URL','book.yourdomain.com domain'],
                    ['wl_mobile_app','White-label mobile app','App Store listing under your brand'],
                    ['wl_custom_invoice_footer','Custom invoice footer','Your address & GST on all invoices'],
                ];
            @endphp
            <div class="grid md:grid-cols-2 gap-3">
                @foreach($wlRows as [$key,$label,$desc])
                    <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 p-3 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-heading">{{ $label }}</p>
                            <p class="text-xs text-muted">{{ $desc }}</p>
                        </div>
                        <label class="relative inline-flex h-7 w-12 items-center rounded-full {{ $data[$key] ? 'bg-velour-600' : 'bg-gray-300 dark:bg-gray-600' }}">
                            <input type="hidden" name="{{ $key }}" value="0">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ $data[$key] ? 'checked' : '' }} class="peer sr-only">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition {{ $data[$key] ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-primary">Save White-Label Options</button>
            </div>
        </form>
    </div>

    <div x-show="tab==='multi-language'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Multi-Language</h2>
        <form method="POST" action="{{ route('customization.options.update') }}">
            @csrf
            @method('PUT')
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach(['English','Hindi','Arabic','French','Spanish','German'] as $lang)
                    <label class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2">
                        <input type="checkbox" name="languages[]" value="{{ $lang }}" {{ in_array($lang, $data['languages'], true) ? 'checked' : '' }} class="rounded">
                        <span class="text-sm text-body">{{ $lang }}</span>
                    </label>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-primary">Save Languages</button>
            </div>
        </form>
    </div>

    <div x-show="tab==='forms'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-2">Custom Forms</h2>
        <p class="text-sm text-muted">You currently have {{ $data['custom_forms_count'] }} custom forms configured.</p>
        <div class="mt-4">
            <a href="{{ route('settings.index') }}" class="btn-outline btn-sm">Manage via Settings</a>
        </div>
    </div>

    <div x-show="tab==='features'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-2">Custom Features</h2>
        <p class="text-sm text-muted">{{ $data['custom_features_live'] }} features are live for your account.</p>
        <div class="mt-4">
            <button type="button" class="btn-primary btn-sm">Request Feature</button>
        </div>
    </div>

    <div x-show="tab==='scalability'" x-cloak class="card p-6">
        <h2 class="text-xl font-semibold text-heading mb-2">Scalability</h2>
        <p class="text-sm text-muted">Plan: <span class="font-semibold text-heading">{{ $data['plan_label'] }}</span></p>
        <p class="text-sm text-muted mt-1">Need higher limits? Upgrade your plan to unlock enterprise scale controls.</p>
        <div class="mt-4">
            <a href="{{ route('billing.plans') }}" class="btn-outline btn-sm">View Plans</a>
        </div>
    </div>
</div>
@endsection

