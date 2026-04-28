@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')

@php $returnTo = old('return_to', request()->query('return_to')); @endphp
<div class="max-w-3xl" x-data="{
    tab: '{{ session('tab', request()->get('tab', 'salon')) }}',
    showPasswordModal: {{ $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation') ? 'true' : 'false' }}
}">

    {{-- Tab bar --}}
    <div class="flex flex-wrap gap-1 mb-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-2xl w-fit">
        @foreach(['salon' => 'Business', 'services' => 'Service', 'hours' => 'Hours', 'social' => 'Social Links', 'notifications' => 'Notifications', 'profile' => 'Profile', 'security' => 'Security'] as $key => $label)
        <button @click="tab='{{ $key }}'"
                :class="tab==='{{ $key }}' ? 'bg-white dark:bg-gray-700 text-velour-700 dark:text-velour-400 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="px-4 py-2 text-sm font-medium rounded-xl transition-all">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ── Salon Settings ── --}}
    <div x-show="tab==='salon'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Salon Profile</h2>
            <form id="settings-salon-form" action="{{ route('settings.salon') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="form-label">Salon name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $salon->name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $salon->email) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $salon->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Website</label>
                        <input type="url" name="website" value="{{ old('website', $salon->website) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Currency</label>
                        <select id="settings-salon-currency" name="currency" class="form-select">
                            @foreach(\App\Helpers\CurrencyHelper::selectList() as $code => $lbl)
                            <option value="{{ $code }}" {{ old('currency', $salon->currency ?? 'GBP') === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Timezone</label>
                        <select id="settings-salon-timezone" name="timezone" class="form-select">
                            @foreach(\App\Helpers\TimezoneHelper::grouped() as $region => $zones)
                            <optgroup label="{{ $region }}">
                                @foreach($zones as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', $salon->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        <p class="form-hint">Dashboard, revenue, and calendar “days” follow this clock.</p>
                        <p id="settings-location-autosave-hint" class="form-hint hidden"></p>
                    </div>
                    <div class="col-span-2">
                        <button type="button" id="settings-detect-location-btn" class="btn-outline">
                            Auto detect from current location
                        </button>
                        <p class="form-hint">Click to detect and fill Currency and Timezone from your current location.</p>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Booking confirmations show times in</label>
                        <div class="flex flex-col sm:flex-row gap-3 mt-1">
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="radio" name="booking_time_display" value="business" class="rounded-full border-gray-300 text-velour-600"
                                       {{ old('booking_time_display', $bookingTimeDisplay ?? 'business') === 'business' ? 'checked' : '' }}>
                                Business timezone (above)
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="radio" name="booking_time_display" value="customer" class="rounded-full border-gray-300 text-velour-600"
                                       {{ old('booking_time_display', $bookingTimeDisplay ?? 'business') === 'customer' ? 'checked' : '' }}>
                                Customer’s local timezone (when we can detect it)
                            </label>
                        </div>
                        <p class="form-hint">Used for emails and online booking messages. Internal calendar always uses business time.</p>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-textarea">{{ old('description', $salon->description) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label">Address line 1</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1', $salon->address_line1) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Address line 2</label>
                        <input type="text" name="address_line2" value="{{ old('address_line2', $salon->address_line2) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city', $salon->city) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Postcode</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $salon->postcode) }}" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    {{-- ── Service Setup ── --}}
    <div x-show="tab==='services'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Service Setup</h2>
            <form action="{{ route('settings.services') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @php
                    $selectedTypeIds = array_map('intval', old('business_type_ids', $selectedBusinessTypeIds ?? []));
                    $starterCategoryOld = old('starter_categories', $selectedStarterCategories ?? []);
                    $starterServiceOld = old('starter_services', $selectedStarterServices ?? []);
                    $typeSlugById = collect($businessTypes)->merge($customBusinessTypes)->pluck('slug', 'id')->all();
                    $selectedSlugMap = [];
                    foreach ($selectedTypeIds as $tid) {
                        $slug = $typeSlugById[$tid] ?? null;
                        if (is_string($slug) && $slug !== '') {
                            $selectedSlugMap[$slug] = true;
                        }
                    }
                @endphp
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Business types <span class="text-red-500">*</span></label>
                    <p class="form-hint mb-1">Services can only be tagged with types you enable here.</p>
                    <div id="settings-business-types-list" class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($businessTypes as $type)
                            @php $checked = in_array((int) $type->id, $selectedTypeIds, true); @endphp
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}" data-bt-slug="{{ $type->slug }}" class="rounded border-gray-300 text-velour-600" {{ $checked ? 'checked' : '' }}>
                                {{ $type->name }}
                            </label>
                        @endforeach
                        @foreach($customBusinessTypes as $type)
                            @php $checked = in_array((int) $type->id, $selectedTypeIds, true); @endphp
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer" data-custom-existing="1">
                                <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}" data-bt-slug="{{ $type->slug }}" class="rounded border-gray-300 text-velour-600" {{ $checked ? 'checked' : '' }}>
                                {{ $type->name }}
                                <span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500">custom</span>
                            </label>
                        @endforeach
                    </div>
                    @error('business_type_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Service categories <span class="text-gray-400 font-normal">(optional)</span></label>
                    <p class="form-hint">Pick starter categories first to filter suggested services.</p>
                    <div id="settings-service-categories-list" class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                        @foreach($starterCatalog as $slug => $items)
                            @php
                                $cats = [];
                                foreach ($items as $item) {
                                    $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                    if ($catSlug === '') {
                                        $catSlug = 'general';
                                    }
                                    $catName = (string) ($item['category'] ?? 'General');
                                    if (! isset($cats[$catSlug])) {
                                        $cats[$catSlug] = $catName === '' ? 'General' : $catName;
                                    }
                                }
                            @endphp
                            @foreach($cats as $catSlug => $catName)
                                @php $catVal = $slug . ':' . $catSlug; @endphp
                                <label class="settings-service-category-option flex items-start gap-2 text-sm text-body cursor-pointer hidden" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catVal }}">
                                    <input type="checkbox" name="starter_categories[]" value="{{ $catVal }}"
                                           class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                                           {{ in_array($catVal, $starterCategoryOld, true) ? 'checked' : '' }}>
                                    <span>{{ $catName }}</span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                    @error('starter_categories')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Services <span class="text-gray-400 font-normal">(optional)</span></label>
                    <p class="form-hint">Select services, then enter time and price manually for each selected service.</p>
                    <div id="settings-service-offers-list" class="flex flex-col gap-2">
                        @foreach($starterCatalog as $slug => $items)
                            @foreach($items as $item)
                                @php
                                    $val = $slug . ':' . $item['key'];
                                    $token = str_replace(':', '__', $val);
                                    $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                    if ($catSlug === '') {
                                        $catSlug = 'general';
                                    }
                                    $catId = $slug . ':' . $catSlug;
                                    $checked = in_array($val, $starterServiceOld, true);
                                    $isUnisex = $slug === 'unisex';
                                    $savedMeta = (array) (($selectedStarterServiceMeta[$val] ?? []));
                                    $oldDuration = old("starter_service_meta.$token.duration_minutes", $savedMeta['duration_minutes'] ?? null);
                                    $oldPrice = old("starter_service_meta.$token.price", $savedMeta['price'] ?? null);
                                    $oldMenDuration = old("starter_service_meta.$token.men.duration_minutes", $savedMeta['men']['duration_minutes'] ?? null);
                                    $oldMenPrice = old("starter_service_meta.$token.men.price", $savedMeta['men']['price'] ?? null);
                                    $oldWomenDuration = old("starter_service_meta.$token.women.duration_minutes", $savedMeta['women']['duration_minutes'] ?? null);
                                    $oldWomenPrice = old("starter_service_meta.$token.women.price", $savedMeta['women']['price'] ?? null);
                                @endphp
                                <label class="settings-service-offer-option flex items-center justify-between gap-3 text-sm text-body cursor-pointer hidden rounded-lg border border-transparent px-1 py-1" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catId }}">
                                    <span class="flex items-start gap-2 min-w-[220px]">
                                        <input type="checkbox" name="starter_services[]" value="{{ $val }}"
                                               class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500 settings-service-checkbox"
                                               {{ $checked ? 'checked' : '' }}>
                                        <span>{{ $item['name'] }}</span>
                                    </span>
                                    @if($isUnisex)
                                        <span class="settings-service-meta-grid grid grid-cols-2 gap-2 w-full max-w-xl {{ $checked ? '' : 'hidden' }}">
                                            <input type="number"
                                                   min="1"
                                                   step="1"
                                                   name="starter_service_meta[{{ $token }}][men][duration_minutes]"
                                                   value="{{ $oldMenDuration }}"
                                                   placeholder="Men time (min)"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                            <input type="number"
                                                   min="0.01"
                                                   step="0.01"
                                                   name="starter_service_meta[{{ $token }}][men][price]"
                                                   value="{{ $oldMenPrice }}"
                                                   placeholder="Men price"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                            <input type="number"
                                                   min="1"
                                                   step="1"
                                                   name="starter_service_meta[{{ $token }}][women][duration_minutes]"
                                                   value="{{ $oldWomenDuration }}"
                                                   placeholder="Women time (min)"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                            <input type="number"
                                                   min="0.01"
                                                   step="0.01"
                                                   name="starter_service_meta[{{ $token }}][women][price]"
                                                   value="{{ $oldWomenPrice }}"
                                                   placeholder="Women price"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                        </span>
                                    @else
                                        <span class="settings-service-meta-grid grid grid-cols-2 gap-2 w-full max-w-md {{ $checked ? '' : 'hidden' }}">
                                            <input type="number"
                                                   min="1"
                                                   step="1"
                                                   name="starter_service_meta[{{ $token }}][duration_minutes]"
                                                   value="{{ $oldDuration }}"
                                                   placeholder="Time (min)"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                            <input type="number"
                                                   min="0.01"
                                                   step="0.01"
                                                   name="starter_service_meta[{{ $token }}][price]"
                                                   value="{{ $oldPrice }}"
                                                   placeholder="Price"
                                                   class="form-input text-xs w-full settings-service-meta-input"
                                                   {{ $checked ? 'required' : 'disabled' }}>
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                    @error('starter_services')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('starter_service_meta')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                @if(empty($selectedBusinessTypeSlugs))
                    <p class="text-xs text-amber-600">Select at least one business type in the Business tab first.</p>
                @endif
                <button type="submit" class="btn-primary">Save Service Setup</button>
            </form>
        </div>
    </div>

    {{-- ── Opening Hours ── --}}
    <div x-show="tab==='hours'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Opening Hours</h2>
            <form action="{{ route('settings.hours') }}" method="POST" class="space-y-3">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @php
                    $days    = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    $current = $salon->opening_hours ?? [];
                @endphp
                @foreach($days as $day)
                @php $h = $current[$day] ?? ['open'=>true,'from'=>'09:00','to'=>'18:00']; @endphp
                <div class="flex items-center gap-4 py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                    <div class="w-28">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="hours[{{ $day }}][open]" value="1"
                                   {{ ($h['open'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span class="text-sm font-medium text-body capitalize">{{ $day }}</span>
                        </label>
                    </div>
                    <input type="time" name="hours[{{ $day }}][from]" value="{{ $h['from'] ?? '09:00' }}" class="form-input w-auto">
                    <span class="text-muted text-sm">to</span>
                    <input type="time" name="hours[{{ $day }}][to]" value="{{ $h['to'] ?? '18:00' }}" class="form-input w-auto">
                </div>
                @endforeach
                <div class="pt-2">
                    <button type="submit" class="btn-primary">Save Hours</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Social Links ── --}}
    <div x-show="tab==='social'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-1">Social Links</h2>
            <p class="text-xs text-muted mb-5">Add your profile URLs. Each link redirects clients to your profile and tracks inbound clicks.</p>

            <form action="{{ route('settings.social-links') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                @php
                $platforms = [
                    'instagram' => ['label' => 'Instagram',       'icon' => '📸', 'placeholder' => 'https://instagram.com/yoursalon'],
                    'facebook'  => ['label' => 'Facebook',        'icon' => '👍', 'placeholder' => 'https://facebook.com/yoursalon'],
                    'tiktok'    => ['label' => 'TikTok',          'icon' => '🎵', 'placeholder' => 'https://tiktok.com/@yoursalon'],
                    'whatsapp'  => ['label' => 'WhatsApp',        'icon' => '💬', 'placeholder' => 'https://wa.me/447700000000'],
                    'google'    => ['label' => 'Google Business', 'icon' => '🔍', 'placeholder' => 'https://g.page/yoursalon'],
                    'twitter'   => ['label' => 'X / Twitter',     'icon' => '🐦', 'placeholder' => 'https://x.com/yoursalon'],
                    'youtube'   => ['label' => 'YouTube',         'icon' => '▶️', 'placeholder' => 'https://youtube.com/@yoursalon'],
                    'linkedin'  => ['label' => 'LinkedIn',        'icon' => '💼', 'placeholder' => 'https://linkedin.com/company/yoursalon'],
                    'pinterest' => ['label' => 'Pinterest',       'icon' => '📌', 'placeholder' => 'https://pinterest.com/yoursalon'],
                ];
                $saved = $salon->social_links ?? [];
                @endphp

                @foreach($platforms as $key => $meta)
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-lg flex-shrink-0">
                        {{ $meta['icon'] }}
                    </div>
                    <div class="flex-1">
                        <label class="form-label mb-1">{{ $meta['label'] }}</label>
                        <div class="flex items-center gap-2">
                            <input type="url"
                                   name="social_links[{{ $key }}]"
                                   value="{{ old("social_links.{$key}", $saved[$key] ?? '') }}"
                                   placeholder="{{ $meta['placeholder'] }}"
                                   class="form-input flex-1">
                            @if(!empty($saved[$key]))
                            <a href="{{ $saved[$key] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="flex-shrink-0 px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-muted hover:text-body transition-colors">
                                Visit ↗
                            </a>
                            @endif
                        </div>
                        @error("social_links.{$key}")
                        <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @endforeach

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="btn-primary">Save Social Links</button>
                    <p class="text-xs text-muted">Links appear on your booking page and Go Live &amp; Share panel.</p>
                </div>
            </form>
        </div>

        {{-- Click stats this month --}}
        @php
        $clickStats = \Illuminate\Support\Facades\DB::table('social_share_clicks')
            ->where('salon_id', $salon->id)
            ->where('clicked_at', '>=', now()->startOfMonth())
            ->selectRaw('platform, COUNT(*) as clicks')
            ->groupBy('platform')
            ->orderByDesc('clicks')
            ->get();
        @endphp

        @if($clickStats->isNotEmpty())
        <div class="card p-6 mt-4">
            <h3 class="font-semibold text-heading mb-4">Click Stats — {{ now()->format('F Y') }}</h3>
            <div class="space-y-3">
                @foreach($clickStats as $stat)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-body w-28 capitalize">{{ $stat->platform }}</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                        <div class="bg-velour-500 h-2 rounded-full"
                             style="width: {{ min(100, round(($stat->clicks / max($clickStats->max('clicks'), 1)) * 100)) }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-heading w-8 text-right">{{ $stat->clicks }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── Notifications (rules, timing, templates, quiet hours) ── --}}
    <div x-show="tab==='notifications'" x-cloak x-data="{ open: {} }">
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-1">Notification settings</h2>
            <p class="text-sm text-muted mb-6">Turn channels on or off, set when scheduled reminders go out, and customise message text. Use placeholders in curly braces in your templates.</p>

            <form action="{{ route('settings.notifications') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                @foreach($notificationDefinitions as $id => $def)
                    @php
                        $rule = $notificationConfig['rules'][$id] ?? ['enabled' => false, 'offset_hours' => null];
                        $tpl = $notificationConfig['templates'][$id] ?? [];
                        $timing = $def['timing'] ?? 'instant';
                    @endphp
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 overflow-hidden">
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-heading text-sm sm:text-base">{{ $def['label'] }}</h3>
                                    @if($timing === 'instant')
                                        <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">Instant</span>
                                    @else
                                        <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">Scheduled</span>
                                    @endif
                                </div>
                                <p class="text-xs text-muted leading-relaxed">{{ $def['description'] }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <input type="hidden" name="notification_rules[{{ $id }}][enabled]" value="0">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-body whitespace-nowrap">
                                    <input type="checkbox" name="notification_rules[{{ $id }}][enabled]" value="1"
                                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                           @checked((bool) old("notification_rules.$id.enabled", $rule['enabled'] ?? false))>
                                    <span>On</span>
                                </label>
                            </div>
                        </div>

                        @if($timing === 'scheduled')
                            <div class="px-4 sm:px-5 pb-4 flex flex-wrap items-center gap-2 border-t border-gray-100 dark:border-gray-800 pt-4">
                                <label class="text-xs font-medium text-body">Send</label>
                                <select name="notification_rules[{{ $id }}][offset_hours]" class="form-select text-sm w-auto min-w-[8rem]">
                                    @foreach([1,2,4,6,12,24,48,72,96,168] as $h)
                                        <option value="{{ $h }}" @selected((int) old("notification_rules.$id.offset_hours", $rule['offset_hours'] ?? 24) === $h)>
                                            {{ $h }} hour{{ $h !== 1 ? 's' : '' }} before start
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-800 px-4 sm:px-5 py-3 bg-gray-50/80 dark:bg-gray-800/30">
                            <button type="button"
                                    @click="open['{{ $id }}'] = !open['{{ $id }}']"
                                    class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline">
                                <span x-text="open['{{ $id }}'] ? 'Hide message templates' : 'Edit message templates'"></span>
                            </button>

                            <div x-show="open['{{ $id }}']" x-cloak class="mt-4 space-y-4">
                                @if(in_array('email', $def['channels'] ?? [], true))
                                    <div>
                                        <label class="form-label text-xs">Email subject</label>
                                        <input type="text" name="notification_templates[{{ $id }}][email_subject]"
                                               value="{{ old("notification_templates.$id.email_subject", $tpl['email_subject'] ?? '') }}"
                                               class="form-input text-sm" placeholder="Subject line">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Email body</label>
                                        <textarea name="notification_templates[{{ $id }}][email_body]" rows="5"
                                                  class="form-textarea text-sm font-mono"
                                                  placeholder="Email text">{{ old("notification_templates.$id.email_body", $tpl['email_body'] ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if(in_array('sms', $def['channels'] ?? [], true))
                                    <div>
                                        <label class="form-label text-xs">SMS body <span class="text-muted font-normal">(keep short; ~160 chars recommended)</span></label>
                                        <textarea name="notification_templates[{{ $id }}][sms_body]" rows="3"
                                                  maxlength="640"
                                                  class="form-textarea text-sm font-mono"
                                                  placeholder="SMS text">{{ old("notification_templates.$id.sms_body", $tpl['sms_body'] ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if(!empty($def['variables']))
                                    <p class="text-xs text-muted">
                                        <span class="font-medium text-body">Placeholders:</span>
                                        @foreach($def['variables'] as $v)
                                            <code class="ml-1 px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[11px]">{!! '{{'.$v.'}}' !!}</code>
                                        @endforeach
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @php $qh = $notificationConfig['quiet_hours'] ?? []; @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
                    <h3 class="font-semibold text-heading text-sm mb-1">Quiet hours</h3>
                    <p class="text-xs text-muted mb-4">When enabled, scheduled client reminders are skipped during this window (salon timezone).</p>
                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <input type="hidden" name="qh_enabled" value="0">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-body">
                            <input type="checkbox" name="qh_enabled" value="1" class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                   @checked(old('qh_enabled', $qh['enabled'] ?? false) ? true : false)>
                            Enable quiet hours
                        </label>
                    </div>
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="form-label text-xs">From</label>
                            <input type="time" name="qh_from" value="{{ old('qh_from', $qh['from'] ?? '22:00') }}" class="form-input text-sm w-auto">
                        </div>
                        <div>
                            <label class="form-label text-xs">To</label>
                            <input type="time" name="qh_to" value="{{ old('qh_to', $qh['to'] ?? '07:00') }}" class="form-input text-sm w-auto">
                        </div>
                        <div>
                            <label class="form-label text-xs">Behaviour</label>
                            <select name="qh_mode" class="form-select text-sm w-auto min-w-[10rem]">
                                <option value="skip" @selected(old('qh_mode', $qh['mode'] ?? 'skip') === 'skip')>Skip send (recommended)</option>
                                <option value="delay" @selected(old('qh_mode', $qh['mode'] ?? 'skip') === 'delay')>Delay (reserved)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Save notification settings</button>
            </form>
        </div>
    </div>

    {{-- ── My Profile ── --}}
    <div x-show="tab==='profile'" x-cloak class="space-y-5" x-data="{ profileCardOpen: true, teamCardOpen: true }">
        <div class="card p-6">
            <div class="mb-5 flex items-center justify-between border-b border-gray-200/60 dark:border-gray-800 pb-3">
                <h2 class="font-semibold text-heading">My Profile</h2>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="showPasswordModal = true"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                            title="Change password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.01a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </button>
                    <button type="button"
                            @click="profileCardOpen = !profileCardOpen"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                            :title="profileCardOpen ? 'Minimize section' : 'Expand section'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="profileCardOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>
                </div>
            </div>
            <form x-show="profileCardOpen" x-cloak action="{{ route('settings.profile') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div>
                    <label class="form-label">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" autocomplete="tel">
                </div>
                <div>
                    <label class="form-label">Experience</label>
                    <input type="text" name="experience" value="{{ old('experience', $user->experience) }}" class="form-input" placeholder="e.g. 5 years">
                </div>
                <div>
                    <label class="form-label">Language proficiency</label>
                    <input type="text" name="language_proficiency" value="{{ old('language_proficiency', $user->language_proficiency) }}" class="form-input" placeholder="e.g. English, Hindi">
                </div>
                {{--
                <div>
                    <label class="form-label">Your timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="">Same as salon / browser</option>
                        @foreach(\App\Helpers\TimezoneHelper::grouped() as $region => $zones)
                        <optgroup label="{{ $region }}">
                            @foreach($zones as $tz => $label)
                            <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    <p class="form-hint">Used for account activity and notifications. Salon schedule and calendar use the <a href="{{ route('settings.index') }}?tab=salon" class="text-link">business timezone</a>.</p>
                </div>
                --}}
                <div>
                    <label class="form-label">Display language</label>
                    <select name="locale" class="form-select">
                        <option value="">Default (English)</option>
                        @foreach($localeOptions as $code => $label)
                        <option value="{{ $code }}" {{ old('locale', $user->locale) === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Month and day names in dates follow this setting where supported.</p>
                </div>
                <button type="submit" class="btn-primary">Update Profile</button>
            </form>
        </div>
        <div class="card p-6">
            @php
                $staffRows = old('staff_members');
                if (! is_array($staffRows)) {
                    $staffRows = ($existingTeamMembers ?? collect())->map(function ($member) {
                        return [
                            'id' => $member->id,
                            'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                            'email' => $member->email,
                            'phone' => $member->phone,
                            'role' => $member->role,
                            'experience' => $member->experience,
                            'language_proficiency' => $member->language_proficiency,
                            'commission_rate' => $member->commission_rate,
                            'color' => $member->color ?: '#7C3AED',
                            'bio' => $member->bio,
                            'assign_services' => $member->services()->exists() ? '1' : '0',
                        ];
                    })->all();
                }
                if (count($staffRows) === 0) {
                    $staffRows = [[]];
                }
                $staffRoles = ['stylist', 'therapist', 'manager', 'receptionist', 'junior', 'owner'];
            @endphp
            <div class="mb-4 flex items-start justify-between gap-3 border-b border-gray-200/60 dark:border-gray-800 pb-3">
                <div>
                    <h2 class="font-semibold text-heading mb-1">
                        Team members <span class="text-gray-400 font-normal">(optional)</span>
                        <span class="ml-2 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            Total: {{ count($staffRows) }}
                        </span>
                    </h2>
                    <p class="form-hint">Add or update team members from your profile settings.</p>
                </div>
                <button type="button"
                        @click="teamCardOpen = !teamCardOpen"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                        :title="teamCardOpen ? 'Minimize section' : 'Expand section'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="teamCardOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                    </svg>
                </button>
            </div>
            <form x-show="teamCardOpen" x-cloak action="{{ route('settings.team-members') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div id="settings-staff-rows" class="space-y-4">
                    @foreach($staffRows as $idx => $st)
                        @php $st = is_array($st) ? $st : []; @endphp
                        <div class="settings-staff-member-row rounded-xl border border-gray-200 bg-gray-50/80 dark:bg-gray-900/20 p-4 space-y-3">
                            <div class="flex justify-between items-center gap-2">
                                <span class="settings-staff-row-title text-sm font-medium text-body">Team member {{ $loop->iteration }}</span>
                                <button type="button" class="settings-staff-remove-btn text-xs font-medium text-red-600 hover:text-red-700 {{ count($staffRows) <= 1 ? 'hidden' : '' }}">Remove</button>
                            </div>
                            <input type="hidden" name="staff_members[{{ $idx }}][id]" value="{{ $st['id'] ?? '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-body mb-1">Full name</label>
                                    <input type="text" name="staff_members[{{ $idx }}][name]" value="{{ $st['name'] ?? '' }}"
                                           class="form-input"
                                           placeholder="e.g. Alex Smith">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Email</label>
                                    <input type="email" name="staff_members[{{ $idx }}][email]" value="{{ $st['email'] ?? '' }}" autocomplete="off"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Phone</label>
                                    <input type="tel" name="staff_members[{{ $idx }}][phone]" value="{{ $st['phone'] ?? '' }}"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Role <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(if adding)</span></label>
                                    <select name="staff_members[{{ $idx }}][role]" class="form-select">
                                        <option value="">-</option>
                                        @foreach($staffRoles as $r)
                                            <option value="{{ $r }}" {{ ($st['role'] ?? '') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Commission %</label>
                                    <input type="number" name="staff_members[{{ $idx }}][commission_rate]" min="0" max="100" step="0.1"
                                           value="{{ $st['commission_rate'] ?? '0' }}"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Experience</label>
                                    <input type="text" name="staff_members[{{ $idx }}][experience]" value="{{ $st['experience'] ?? '' }}"
                                           class="form-input" placeholder="e.g. 5 years">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Language proficiency</label>
                                    <input type="text" name="staff_members[{{ $idx }}][language_proficiency]" value="{{ $st['language_proficiency'] ?? '' }}"
                                           class="form-input" placeholder="e.g. English, Hindi">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Calendar colour</label>
                                    <input type="color" name="staff_members[{{ $idx }}][color]" value="{{ $st['color'] ?? '#7C3AED' }}"
                                           class="w-full h-11 px-1 py-1 rounded-xl border border-gray-300 cursor-pointer bg-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-body mb-1">Bio</label>
                                    <textarea name="staff_members[{{ $idx }}][bio]" rows="2" placeholder="Optional"
                                              class="form-textarea">{{ $st['bio'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <input type="hidden" name="staff_members[{{ $idx }}][assign_services]" value="0">
                            @php
                                $as = $st['assign_services'] ?? null;
                                $assignChecked = (string) $as === '1' || $as === true || $as === 1;
                            @endphp
                            <label class="inline-flex items-start gap-2 text-sm text-body cursor-pointer">
                                <input type="checkbox" name="staff_members[{{ $idx }}][assign_services]" value="1" class="mt-0.5 rounded border-gray-300 text-velour-600"
                                       {{ $assignChecked ? 'checked' : '' }}>
                                <span>Offer all services on this menu to this team member</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="settings-add-staff-member" class="mt-2 text-sm font-medium text-velour-600 hover:text-velour-700">+ Add another team member</button>
                @error('staff_members')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                <button type="submit" class="btn-primary">Save Team Members</button>
            </form>
        </div>
    </div>

    {{-- ── Security / 2FA ── --}}
    <div x-show="tab==='security'" x-cloak class="space-y-5">
        <div class="card p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="font-semibold text-heading">Two-Factor Authentication</h2>
                    <p class="text-xs text-muted mt-1">Add an extra layer of security to your account with 2FA.</p>
                </div>
                @if($user->hasTwoFactorEnabled())
                <span class="badge-green px-3 py-1 text-xs font-semibold rounded-xl">Enabled</span>
                @else
                <span class="badge-gray px-3 py-1 text-xs font-semibold rounded-xl">Disabled</span>
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('two-factor.setup') }}" class="btn-primary">
                    {{ $user->hasTwoFactorEnabled() ? 'Manage 2FA' : 'Enable 2FA' }}
                </a>
                @if($user->hasTwoFactorEnabled())
                <a href="{{ route('two-factor.recovery') }}" class="btn-outline">Recovery codes</a>
                @endif
            </div>
        </div>

        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-1">Login history</h2>
            <p class="text-xs text-muted mb-4">Your last recorded sign-in.</p>
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-body">
                    Last login:
                    <strong class="text-heading">{{ $user->last_login_at ? \App\Support\DisplayFormatter::userDateTime($user, $currentSalon ?? null, $user->last_login_at) : 'Unknown' }}</strong>
                </span>
            </div>
        </div>

        @if(config('billing.subscriptions_enabled'))
        <div class="card border-red-200 dark:border-red-900/50 p-6">
            <h2 class="font-semibold text-heading mb-1">Danger Zone</h2>
            <p class="text-xs text-muted mb-4">Actions here are irreversible. Proceed with caution.</p>
            <a href="{{ route('billing.cancel') }}"
               class="btn border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                Cancel subscription
            </a>
        </div>
        @endif
    </div>

{{-- Password Modal (opened from Profile gear icon) --}}
<div x-show="showPasswordModal"
     x-cloak
     x-transition.opacity
     @keydown.escape.window="showPasswordModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60" @click="showPasswordModal = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-heading">Change Password</h3>
            <button type="button"
                    @click="showPasswordModal = false"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-gray-100 dark:hover:bg-gray-800"
                    aria-label="Close password modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('settings.password') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <div>
                <label class="form-label">Current password</label>
                <input type="password" name="current_password" required class="form-input">
                @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">New password</label>
                <input type="password" name="password" required autocomplete="new-password" class="form-input">
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Confirm new password</label>
                <input type="password" name="password_confirmation" required class="form-input">
            </div>
            <div class="pt-1 flex items-center gap-2">
                <button type="submit" class="btn-primary">Change Password</button>
                <button type="button" @click="showPasswordModal = false" class="btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
(function () {
    function detectRegionCode() {
        try {
            if (typeof Intl !== 'undefined' && typeof Intl.Locale === 'function' && navigator.language) {
                var locale = new Intl.Locale(navigator.language);
                if (locale && locale.region) return String(locale.region).toUpperCase();
            }
        } catch (e) {}

        var lang = String(navigator.language || '').toUpperCase();
        var parts = lang.split(/[-_]/);
        return parts.length > 1 ? parts[1] : '';
    }

    function detectCurrencyByRegion(region) {
        var map = {
            IN: 'INR', US: 'USD', GB: 'GBP', EU: 'EUR', DE: 'EUR', FR: 'EUR', ES: 'EUR', IT: 'EUR', NL: 'EUR', PT: 'EUR',
            IE: 'EUR', BE: 'EUR', AT: 'EUR', FI: 'EUR', GR: 'EUR', LU: 'EUR', SI: 'EUR', SK: 'EUR', LV: 'EUR', LT: 'EUR',
            EE: 'EUR', CY: 'EUR', MT: 'EUR', CA: 'CAD', AU: 'AUD', NZ: 'NZD', SG: 'SGD', AE: 'AED', SA: 'SAR', QA: 'QAR',
            KW: 'KWD', BH: 'BHD', OM: 'OMR', JP: 'JPY', KR: 'KRW', CN: 'CNY', HK: 'HKD', TW: 'TWD', TH: 'THB', MY: 'MYR',
            ID: 'IDR', PH: 'PHP', VN: 'VND', PK: 'PKR', BD: 'BDT', NP: 'NPR', LK: 'LKR', ZA: 'ZAR', NG: 'NGN', KE: 'KES',
            GH: 'GHS', CH: 'CHF', SE: 'SEK', NO: 'NOK', DK: 'DKK', PL: 'PLN', CZ: 'CZK', HU: 'HUF', RO: 'RON', TR: 'TRY',
            BR: 'BRL', MX: 'MXN', AR: 'ARS', CL: 'CLP', CO: 'COP', PE: 'PEN', UY: 'UYU'
        };
        return map[region] || '';
    }

    function detectCurrencyByTimezone(timezone) {
        var tz = String(timezone || '');
        if (tz.indexOf('Asia/Kolkata') === 0) return 'INR';
        if (tz.indexOf('Europe/London') === 0) return 'GBP';
        if (tz.indexOf('Europe/') === 0) return 'EUR';
        if (tz.indexOf('America/') === 0) return 'USD';
        if (tz.indexOf('Asia/Dubai') === 0) return 'AED';
        return '';
    }

    function setAutosaveHint(message, tone) {
        var hint = document.getElementById('settings-location-autosave-hint');
        if (!hint) return;
        hint.classList.remove('hidden', 'text-green-600', 'text-red-500', 'text-gray-500');
        hint.classList.add(tone === 'error' ? 'text-red-500' : (tone === 'success' ? 'text-green-600' : 'text-gray-500'));
        hint.textContent = message;
    }

    function detectTimezoneByRegion(region) {
        var map = {
            IN: 'Asia/Kolkata',
            GB: 'Europe/London',
            US: 'America/New_York',
            AE: 'Asia/Dubai',
            SG: 'Asia/Singapore',
            AU: 'Australia/Sydney',
            CA: 'America/Toronto',
            NZ: 'Pacific/Auckland'
        };
        return map[region] || '';
    }

    function pickTimezoneOption(timezoneSelect, region, detectedTimezone) {
        var candidates = [];
        if (detectedTimezone) candidates.push(detectedTimezone);
        if (region === 'IN') {
            // Ensure India always resolves to IST if available.
            candidates.unshift('Asia/Kolkata', 'Asia/Calcutta');
        } else {
            var byRegion = detectTimezoneByRegion(region);
            if (byRegion) candidates.push(byRegion);
        }

        for (var i = 0; i < candidates.length; i++) {
            var tz = candidates[i];
            if (!tz) continue;
            var option = timezoneSelect.querySelector('option[value="' + tz + '"]');
            if (option) return tz;
        }
        return '';
    }

    function autoFillSalonLocationSettings() {
        var form = document.getElementById('settings-salon-form');
        var detectBtn = document.getElementById('settings-detect-location-btn');
        var timezoneSelect = document.getElementById('settings-salon-timezone');
        var currencySelect = document.getElementById('settings-salon-currency');
        if (!form || !detectBtn || !timezoneSelect || !currencySelect) return;

        function applyDetectedLocation() {
            setAutosaveHint('Detecting current location...', 'neutral');

            var timezone = '';
            try {
                timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            } catch (e) {}

            var region = detectRegionCode();
            var pickedTimezone = pickTimezoneOption(timezoneSelect, region, timezone);
            if (pickedTimezone) {
                timezoneSelect.value = pickedTimezone;
                timezone = pickedTimezone;
            }

            var currency = detectCurrencyByTimezone(timezone) || detectCurrencyByRegion(region);
            if (currency) {
                var currencyOption = currencySelect.querySelector('option[value="' + currency + '"]');
                if (currencyOption) {
                    currencySelect.value = currency;
                }
            }

            if (timezone && currency) {
                setAutosaveHint('Detected and selected. Click Save Changes to apply.', 'success');
            } else if (timezone || currency) {
                setAutosaveHint('Partially detected. Please review and click Save Changes.', 'neutral');
            } else {
                setAutosaveHint('Could not detect location automatically. Please select manually.', 'error');
            }
        }

        detectBtn.addEventListener('click', applyDetectedLocation);
    }

    document.addEventListener('DOMContentLoaded', autoFillSalonLocationSettings);
})();

(function () {
    var input = document.getElementById('settings-custom-business-type-input');
    var addBtn = document.getElementById('settings-add-custom-business-type-btn');
    var list = document.getElementById('settings-custom-business-type-list');
    var typeList = document.getElementById('settings-business-types-list');
    if (!input || !addBtn || !list || !typeList) return;

    function hasValue(value) {
        var wanted = value.trim().toLowerCase();
        var exists = false;
        list.querySelectorAll('input[name="custom_business_types[]"]').forEach(function (el) {
            if (String(el.value || '').trim().toLowerCase() === wanted) {
                exists = true;
            }
        });
        return exists;
    }

    function hasRenderedCustomCheckbox(value) {
        var wanted = value.trim().toLowerCase();
        var exists = false;
        typeList.querySelectorAll('label[data-custom-draft="1"], label[data-custom-existing="1"]').forEach(function (label) {
            var nameNode = label.querySelector('.settings-business-type-name');
            var txt = nameNode ? nameNode.textContent : label.textContent;
            if (String(txt || '').trim().toLowerCase() === wanted) {
                exists = true;
            }
        });
        return exists;
    }

    function slugify(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/&/g, 'and')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }

    function addImmediateCheckedCustomCheckbox(value) {
        if (hasRenderedCustomCheckbox(value)) return;

        var baseSlug = slugify(value) || 'custom-type';
        var slug = baseSlug;
        var n = 1;
        while (typeList.querySelector('input[name="business_type_ids[]"][data-bt-slug="' + slug + '"]')) {
            slug = baseSlug + '-' + n;
            n++;
        }

        var label = document.createElement('label');
        label.className = 'inline-flex items-center gap-2 text-sm text-body cursor-pointer';
        label.setAttribute('data-custom-draft', '1');
        label.innerHTML =
            '<input type="checkbox" checked disabled class="rounded border-gray-300 text-velour-600 opacity-70 cursor-not-allowed">' +
            '<span class="settings-business-type-name"></span>' +
            '<span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-velour-100 dark:bg-velour-900/30 text-velour-600 dark:text-velour-300">new</span>';
        label.querySelector('.settings-business-type-name').textContent = value;
        typeList.appendChild(label);
    }

    function addCustomType(raw) {
        var value = String(raw || '').trim();
        if (!value || hasValue(value)) return;

        var pill = document.createElement('span');
        pill.className = 'settings-custom-business-pill inline-flex items-center gap-2 rounded-full bg-velour-100/80 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 px-3 py-1 text-xs';
        pill.innerHTML = '<span></span><button type="button" class="settings-custom-business-remove leading-none">x</button><input type="hidden" name="custom_business_types[]" />';
        pill.querySelector('span').textContent = value;
        pill.querySelector('input[name="custom_business_types[]"]').value = value;
        list.appendChild(pill);
        addImmediateCheckedCustomCheckbox(value);
    }

    addBtn.addEventListener('click', function () {
        addCustomType(input.value);
        input.value = '';
        input.focus();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addBtn.click();
        }
    });

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.settings-custom-business-remove');
        if (!btn) return;
        var pill = btn.closest('.settings-custom-business-pill');
        if (!pill) return;
        var hidden = pill.querySelector('input[name="custom_business_types[]"]');
        var value = hidden ? String(hidden.value || '').trim().toLowerCase() : '';
        pill.remove();

        if (value) {
            typeList.querySelectorAll('label[data-custom-draft="1"]').forEach(function (label) {
                var nameNode = label.querySelector('.settings-business-type-name');
                var txt = nameNode ? nameNode.textContent : '';
                if (String(txt || '').trim().toLowerCase() === value) {
                    label.remove();
                }
            });
        }
    });
})();

(function () {
    function syncServiceSetupVisibility() {
        var selectedSlugs = {};
        var selectedCategoryIds = {};
        var selectedCountBySlug = {};

        document.querySelectorAll('#settings-business-types-list input[name="business_type_ids[]"]:checked').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            if (slug) selectedSlugs[slug] = true;
        });

        var anyCategoryVisible = false;
        document.querySelectorAll('.settings-service-category-option').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var show = !!(slug && selectedSlugs[slug]);
            el.classList.toggle('hidden', !show);
            if (show) anyCategoryVisible = true;

            var chk = el.querySelector('input[type="checkbox"]');
            if (!chk) return;
            if (!show) chk.checked = false;
            if (show && chk.checked) {
                var catId = el.getAttribute('data-cat-id');
                selectedCategoryIds[catId] = true;
                selectedCountBySlug[slug] = (selectedCountBySlug[slug] || 0) + 1;
            }
        });

        var anyServiceVisible = false;
        document.querySelectorAll('.settings-service-offer-option').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var catId = el.getAttribute('data-cat-id');
            var show = !!(slug && selectedSlugs[slug]);

            // Show services only when at least one category is selected for that slug.
            if (show) {
                if ((selectedCountBySlug[slug] || 0) > 0) {
                    show = !!selectedCategoryIds[catId];
                } else {
                    show = false;
                }
            }

            el.classList.toggle('hidden', !show);
            if (show) anyServiceVisible = true;

            var chk = el.querySelector('input[type="checkbox"]');
            var meta = el.querySelector('.settings-service-meta-grid');
            var metaInputs = el.querySelectorAll('.settings-service-meta-input');
            if (!show && chk) chk.checked = false;

            var checkedAndVisible = !!(show && chk && chk.checked);
            if (meta) meta.classList.toggle('hidden', !checkedAndVisible);
            metaInputs.forEach(function (inp) {
                inp.disabled = !checkedAndVisible;
                inp.required = checkedAndVisible;
            });
        });

        var categoriesWrap = document.getElementById('settings-service-categories-list');
        if (categoriesWrap) categoriesWrap.classList.toggle('opacity-50', !anyCategoryVisible);

        var servicesWrap = document.getElementById('settings-service-offers-list');
        if (servicesWrap) servicesWrap.classList.toggle('opacity-50', !anyServiceVisible);
    }

    document.addEventListener('change', function (e) {
        if (!e.target) return;
        if (
            e.target.matches('#settings-business-types-list input[name="business_type_ids[]"]') ||
            e.target.closest('.settings-service-category-option') ||
            e.target.classList.contains('settings-service-checkbox')
        ) {
            syncServiceSetupVisibility();
        }
    });
    document.addEventListener('DOMContentLoaded', syncServiceSetupVisibility);
})();

(function () {
    var maxRows = 10;
    var container = document.getElementById('settings-staff-rows');
    var addBtn = document.getElementById('settings-add-staff-member');
    if (!container || !addBtn) return;

    function renumberStaffRows() {
        var rows = container.querySelectorAll('.settings-staff-member-row');
        rows.forEach(function (row, i) {
            row.querySelectorAll('[name^="staff_members"]').forEach(function (el) {
                el.name = el.name.replace(/staff_members\[\d+\]/, 'staff_members[' + i + ']');
            });
            var title = row.querySelector('.settings-staff-row-title');
            if (title) title.textContent = 'Team member ' + (i + 1);
            var rm = row.querySelector('.settings-staff-remove-btn');
            if (rm) rm.classList.toggle('hidden', rows.length <= 1);
        });
    }

    addBtn.addEventListener('click', function () {
        var rows = container.querySelectorAll('.settings-staff-member-row');
        if (rows.length >= maxRows) return;
        var clone = rows[0].cloneNode(true);
        clone.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea').forEach(function (el) {
            el.value = '';
        });
        clone.querySelectorAll('input[type="color"]').forEach(function (el) {
            el.value = '#7C3AED';
        });
        clone.querySelectorAll('select').forEach(function (el) {
            el.selectedIndex = 0;
        });
        clone.querySelectorAll('input[type="checkbox"]').forEach(function (el) {
            el.checked = true;
        });
        clone.querySelectorAll('input[type="hidden"]').forEach(function (el) {
            if (el.name && el.name.indexOf('[id]') !== -1) el.value = '';
            if (el.name && el.name.indexOf('assign_services') !== -1) el.value = '0';
        });
        container.appendChild(clone);
        renumberStaffRows();
    });

    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.settings-staff-remove-btn');
        if (!btn) return;
        var row = btn.closest('.settings-staff-member-row');
        if (!row || container.querySelectorAll('.settings-staff-member-row').length <= 1) return;
        row.remove();
        renumberStaffRows();
    });

    renumberStaffRows();
})();
</script>

@endsection
