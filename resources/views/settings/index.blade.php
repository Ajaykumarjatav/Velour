@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')

<div class="max-w-3xl" x-data="{ tab: '{{ session('tab', request()->get('tab', 'salon')) }}' }">

    {{-- Tab bar --}}
    <div class="flex flex-wrap gap-1 mb-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-2xl w-fit">
        @foreach(['salon' => 'Salon', 'hours' => 'Hours', 'social' => 'Social Links', 'notifications' => 'Notifications', 'profile' => 'My Profile', 'security' => 'Security'] as $key => $label)
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
                <button type="submit" class="btn-primary">Update Profile</button>
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
                    <strong class="text-heading">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y, H:i') : 'Unknown' }}</strong>
                </span>
            </div>
        </div>

        <div class="card border-red-200 dark:border-red-900/50 p-6">
            <h2 class="font-semibold text-heading mb-1">Danger Zone</h2>
            <p class="text-xs text-muted mb-4">Actions here are irreversible. Proceed with caution.</p>
            <a href="{{ route('billing.cancel') }}"
               class="btn border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                Cancel subscription
            </a>
        </div>
    </div>

</div>

@endsection
