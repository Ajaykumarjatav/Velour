@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')

<div class="max-w-3xl" x-data="{ tab: '{{ session('tab', request()->get('tab', 'salon')) }}' }">

    {{-- Tab bar --}}
    <div class="flex flex-wrap gap-1 mb-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-2xl w-fit">
        @foreach(['salon' => 'Business', 'hours' => 'Hours', 'social' => 'Social Links', 'notifications' => 'Notifications', 'profile' => 'Profile', 'security' => 'Security'] as $key => $label)
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
            <form action="{{ route('settings.salon') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="form-label">Salon name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $salon->name) }}" required class="form-input">
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Business types <span class="text-red-500">*</span></label>
                        <p class="form-hint mb-2">Services can only be tagged with types you enable here.</p>
                        <div class="flex flex-wrap gap-x-4 gap-y-2">
                            @foreach($businessTypes as $type)
                                @php $checked = in_array((int) $type->id, array_map('intval', old('business_type_ids', $selectedBusinessTypeIds ?? [])), true); @endphp
                                <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                    <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}" data-bt-slug="{{ $type->slug }}" class="rounded border-gray-300 text-velour-600" {{ $checked ? 'checked' : '' }}>
                                    {{ $type->name }}
                                </label>
                            @endforeach
                        </div>
                        @error('business_type_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div id="settings-starter-categories-block" class="col-span-2 space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                        <label class="form-label mb-0">Predefined service categories <span class="text-gray-400 font-normal">(optional)</span></label>
                        <p class="form-hint">Pick starter categories first to filter suggested services.</p>
                        @php $starterCategoryOld = old('starter_categories', $selectedStarterCategories ?? []); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
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
                                    <label class="settings-starter-category flex items-start gap-2 text-sm text-body cursor-pointer hidden" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catVal }}">
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
                    <div id="settings-starter-services-block" class="col-span-2 space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                        <label class="form-label mb-0">Predefined services <span class="text-gray-400 font-normal">(optional)</span></label>
                        <p class="form-hint">Suggested services are filtered by selected business types and categories.</p>
                        @php $starterServiceOld = old('starter_services', $selectedStarterServices ?? []); @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                            @foreach($starterCatalog as $slug => $items)
                                @foreach($items as $item)
                                    @php $val = $slug . ':' . $item['key']; @endphp
                                    @php
                                        $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                        if ($catSlug === '') {
                                            $catSlug = 'general';
                                        }
                                        $catId = $slug . ':' . $catSlug;
                                    @endphp
                                    <label class="settings-starter-service flex items-start gap-2 text-sm text-body cursor-pointer hidden" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catId }}">
                                        <input type="checkbox" name="starter_services[]" value="{{ $val }}"
                                               class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                                               {{ in_array($val, $starterServiceOld, true) ? 'checked' : '' }}>
                                        <span>{{ $item['name'] }} <span class="text-gray-400">({{ $item['duration_minutes'] }} min - {{ strtoupper($salon->currency ?? 'GBP') }} {{ number_format((float) $item['price'], 2) }})</span></span>
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                        @error('starter_services')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
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
                        <select name="currency" class="form-select">
                            @foreach(\App\Helpers\CurrencyHelper::selectList() as $code => $lbl)
                            <option value="{{ $code }}" {{ old('currency', $salon->currency ?? 'GBP') === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Timezone</label>
                        <select name="timezone" class="form-select">
                            @foreach(\App\Helpers\TimezoneHelper::grouped() as $region => $zones)
                            <optgroup label="{{ $region }}">
                                @foreach($zones as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', $salon->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        <p class="form-hint">Dashboard, revenue, and calendar “days” follow this clock.</p>
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

    {{-- ── Opening Hours ── --}}
    <div x-show="tab==='hours'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Opening Hours</h2>
            <form action="{{ route('settings.hours') }}" method="POST" class="space-y-3">
                @csrf @method('PUT')
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
    <div x-show="tab==='profile'" x-cloak class="space-y-5">
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">My Profile</h2>
            <form action="{{ route('settings.profile') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
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
            <h2 class="font-semibold text-heading mb-1">Team members <span class="text-gray-400 font-normal">(optional)</span></h2>
            <p class="form-hint mb-4">Add or update team members from your profile settings.</p>
            <form action="{{ route('settings.team-members') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
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
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Change Password</h2>
            <form action="{{ route('settings.password') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="form-label">Current password</label>
                    <input type="password" name="current_password" required class="form-input">
                    @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">New password</label>
                    <input type="password" name="password" required autocomplete="new-password" class="form-input">
                </div>
                <div>
                    <label class="form-label">Confirm new password</label>
                    <input type="password" name="password_confirmation" required class="form-input">
                </div>
                <button type="submit" class="btn-primary">Change Password</button>
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

</div>

<script>
(function () {
    function syncSettingsStarterBlocks() {
        var checked = Array.prototype.slice.call(document.querySelectorAll('input[name="business_type_ids[]"]:checked'));
        var slugs = {};
        checked.forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            if (slug) {
                slugs[slug] = true;
            }
        });

        var anyCategoryVisible = false;
        var selectedCategoryIds = {};
        var selectedBySlug = {};
        document.querySelectorAll('.settings-starter-category').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var show = slug && slugs[slug];
            el.classList.toggle('hidden', !show);
            if (show) anyCategoryVisible = true;
            var chk = el.querySelector('input[type="checkbox"]');
            if (chk) {
                if (!show) chk.checked = false;
                if (show && chk.checked) {
                    var catId = el.getAttribute('data-cat-id');
                    selectedCategoryIds[catId] = true;
                    selectedBySlug[slug] = (selectedBySlug[slug] || 0) + 1;
                }
            }
        });

        var categoriesBlock = document.getElementById('settings-starter-categories-block');
        if (categoriesBlock) categoriesBlock.classList.toggle('hidden', !anyCategoryVisible);

        var anyServiceVisible = false;
        document.querySelectorAll('.settings-starter-service').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var catId = el.getAttribute('data-cat-id');
            var show = !!(slug && slugs[slug]);
            if (show) {
                var filterByCat = (selectedBySlug[slug] || 0) > 0;
                if (filterByCat) show = !!selectedCategoryIds[catId];
            }
            el.classList.toggle('hidden', !show);
            if (show) anyServiceVisible = true;
            var chk = el.querySelector('input[type="checkbox"]');
            if (!show && chk) chk.checked = false;
        });
        var servicesBlock = document.getElementById('settings-starter-services-block');
        if (servicesBlock) servicesBlock.classList.toggle('hidden', !anyServiceVisible);
    }

    document.addEventListener('change', function (e) {
        if (e.target && (e.target.matches('input[name="business_type_ids[]"]') || e.target.closest('.settings-starter-category'))) {
            syncSettingsStarterBlocks();
        }
    });
    document.addEventListener('DOMContentLoaded', syncSettingsStarterBlocks);
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
