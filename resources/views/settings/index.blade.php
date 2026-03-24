@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('content')

<div class="max-w-3xl" x-data="{ tab: '{{ request()->get('tab', 'salon') }}' }">

    {{-- Tab bar --}}
    <div class="flex flex-wrap gap-1 mb-6 bg-gray-100 dark:bg-gray-800 p-1 rounded-2xl w-fit">
        @foreach(['salon' => 'Salon', 'hours' => 'Hours', 'notifications' => 'Notifications', 'profile' => 'My Profile', 'security' => 'Security'] as $key => $label)
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
                            @foreach(['GBP'=>'GBP (£)','USD'=>'USD ($)','EUR'=>'EUR (€)','AUD'=>'AUD (A$)','CAD'=>'CAD (C$)'] as $code => $lbl)
                            <option value="{{ $code }}" {{ old('currency', $salon->currency) === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Timezone</label>
                        <select name="timezone" class="form-select">
                            @foreach(['Europe/London','Europe/Paris','America/New_York','America/Chicago','America/Denver','America/Los_Angeles','Australia/Sydney'] as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', $salon->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
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

    {{-- ── Notifications ── --}}
    <div x-show="tab==='notifications'" x-cloak>
        <div class="card p-6">
            <h2 class="font-semibold text-heading mb-5">Notification Settings</h2>
            <form action="{{ route('settings.notifications') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                @foreach([
                    'email_appointment_confirmation' => 'Send email confirmation when appointment is booked',
                    'email_appointment_reminder'     => 'Send email reminder before appointment',
                    'sms_appointment_reminder'       => 'Send SMS reminder before appointment',
                    'email_new_client'               => 'Notify me when a new client registers',
                ] as $key => $label)
                <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    <input type="checkbox" name="{{ $key }}" value="1"
                           {{ ($settings[$key] ?? false) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">{{ $label }}</span>
                </label>
                @endforeach
                <div class="mt-2">
                    <label class="form-label">Send reminder how many hours before?</label>
                    <select name="reminder_hours_before" class="form-select w-auto">
                        @foreach([1,2,4,6,12,24,48] as $h)
                        <option value="{{ $h }}" {{ ($settings['reminder_hours_before'] ?? 24) == $h ? 'selected' : '' }}>
                            {{ $h }} hour{{ $h !== 1 ? 's' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary">Save Notifications</button>
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
