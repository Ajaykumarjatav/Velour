@extends('layouts.app')
@section('title', 'New Appointment')
@section('page-title', 'New Appointment')
@section('content')

@php
    $occupiedSlotsUrl = route('appointments.occupied-slots');
    $scopedStaffId = $scopedStaffId ?? auth()->user()->dashboardScopedStaffId();
    $defaultStaffId = (string) ($defaultStaffId ?? old('staff_id', ''));
    $lockedStaff = $scopedStaffId ? $staff->firstWhere('id', (int) $scopedStaffId) : null;
@endphp

@include('partials.appointment-scheduler-alpine')

<div class="max-w-4xl">
    <div class="card p-6">
        <form id="appt-create-form" action="{{ route('appointments.store') }}" method="POST" class="space-y-5"
              x-data="{ dirty: {{ $errors->any() ? 'true' : 'false' }} }"
              @input="dirty = true"
              @change="dirty = true">
            @csrf
            <div class="space-y-0">
                <div class="flex items-end gap-2">
                    @include('partials.appointment-client-picker', [
                        'selectId' => 'appt-create-client',
                        'clients' => $clients,
                        'selectedClientId' => old('client_id'),
                    ])
                    @if(auth()->user()->dashboardScopedStaffId() === null)
                    <x-relation-quick-create-trigger type="client" select-id="appt-create-client" :client-loyalty-tiers="$clientQuickCreateLoyaltyTiers ?? collect()" />
                    @endif
                </div>
            </div>

            <div x-data="timeslotPicker(@js($occupiedSlotsUrl))" x-init="init()">
                <div class="flex items-end gap-2">
                    @if($scopedStaffId !== null)
                        <div class="flex-1 min-w-0">
                            <label class="form-label">Staff member <span class="text-red-500">*</span></label>
                            <input type="text" class="form-input bg-gray-100 dark:bg-gray-800/70 cursor-not-allowed" value="{{ $lockedStaff?->name ?? 'Staff #' . $scopedStaffId }}" readonly>
                            <input type="hidden" id="appt-create-staff" name="staff_id" value="{{ $scopedStaffId }}">
                            <p class="text-xs text-muted mt-1">Staff panel bookings are locked to your own profile.</p>
                        </div>
                    @else
                        <x-searchable-select
                            id="appt-create-staff"
                            name="staff_id"
                            label="Staff member"
                            :required="true"
                            error-name="staff_id"
                            :search-url="route('lookup.staff')"
                            search-placeholder="Search staff…"
                            hint="No match? Use + to add new."
                            trigger-class="form-select w-full"
                            x-model="staffId">
                            <option value="">Select staff…</option>
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ (string) $defaultStaffId === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </x-searchable-select>
                        <x-relation-quick-create-trigger type="staff" select-id="appt-create-staff" />
                    @endif
                </div>

                <div class="mt-5">
                    <div class="flex items-end gap-2">
                        <div class="flex-1 min-w-0">
                            <label class="form-label">Services <span class="text-red-500">*</span></label>
                        </div>
                        <x-service-quick-create-trigger list-id="appt-services-list" />
                    </div>
                    <div id="appt-services-list" class="space-y-3 max-h-[28rem] overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-gray-800 @error('services') border-red-400 dark:border-red-500 @enderror">
                    @foreach($services as $svc)
                        @php
                            $vOpts = $svc->normalizedVariants();
                            $aOpts = $svc->normalizedAddons();
                        @endphp
                        <div class="rounded-lg border border-gray-100 dark:border-gray-800 p-2">
                            <label class="flex items-center gap-3 p-1 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer">
                                <input type="checkbox" name="services[]" value="{{ $svc->id }}"
                                       {{ in_array($svc->id, old('services', [])) ? 'checked' : '' }}
                                       class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                <span class="flex-1 text-sm font-medium text-body">{{ $svc->name }}</span>
                                <span class="text-xs text-muted whitespace-nowrap">{{ $svc->duration_minutes }} min</span>
                                <span class="text-sm font-semibold text-heading whitespace-nowrap">{{ \App\Helpers\CurrencyHelper::format($svc->price, $currentSalon->currency ?? 'GBP') }}</span>
                            </label>
                            @if(count($vOpts) || count($aOpts))
                                <div class="mt-2 ml-8 space-y-2 text-xs border-t border-gray-100 dark:border-gray-800 pt-2">
                                    @if(count($vOpts))
                                        <div>
                                            <span class="text-muted block mb-1">Variant</span>
                                            <select name="service_variant[{{ $svc->id }}]" class="form-select text-xs py-1.5">
                                                <option value="">Base price ({{ \App\Helpers\CurrencyHelper::format($svc->price, $currentSalon->currency ?? 'GBP') }})</option>
                                                @foreach($vOpts as $vo)
                                                    <option value="{{ $vo['name'] }}" {{ old('service_variant.'.$svc->id) === $vo['name'] ? 'selected' : '' }}>
                                                        {{ $vo['name'] }} — {{ \App\Helpers\CurrencyHelper::format($vo['price'], $currentSalon->currency ?? 'GBP') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif
                                    @if(count($aOpts))
                                        <div>
                                            <span class="text-muted block mb-1">Add-ons</span>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($aOpts as $ao)
                                                    <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-gray-50 dark:bg-gray-800/80 cursor-pointer">
                                                        <input type="checkbox" name="service_addons[{{ $svc->id }}][]" value="{{ $ao['name'] }}"
                                                               {{ is_array(old('service_addons.'.$svc->id, [])) && in_array($ao['name'], old('service_addons.'.$svc->id, []), true) ? 'checked' : '' }}
                                                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                                        <span>{{ $ao['name'] }} +{{ \App\Helpers\CurrencyHelper::format($ao['price'], $currentSalon->currency ?? 'GBP') }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                    </div>
                    @error('services')<p class="form-error mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="mt-5">
                    <label class="form-label mb-2">Date &amp; time <span class="text-red-500">*</span></label>
                    <p class="text-xs text-muted mb-3">
                        Based on
                        <a href="{{ route('settings.index') }}?tab=hours" class="text-link">salon hours</a>,
                        staff shifts,
                        <a href="{{ route('availability.index', ['tab' => 'leave']) }}" class="text-link">leave</a>,
                        <a href="{{ route('availability.index', ['tab' => 'attendance']) }}" class="text-link">attendance</a>,
                        and
                        <a href="{{ route('availability.index') }}" class="text-link">weekly availability</a>.
                    </p>
                    @include('partials.appointment-scheduler')
                </div>

                <input type="hidden" name="starts_at" :value="selectedDate && selectedTime ? selectedDate + ' ' + selectedTime + ':00' : ''">
                @error('starts_at')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Source of booking <span class="text-red-500">*</span></label>
                    <select name="source" class="form-select @error('source') form-input-error @enderror" required>
                        @foreach(\App\Models\Appointment::bookingSourceOptions() as $val => $label)
                            <option value="{{ $val }}" @selected(old('source', 'walk_in') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('source')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Payment status <span class="text-red-500">*</span></label>
                    <select name="payment_status" class="form-select @error('payment_status') form-input-error @enderror" required>
                        @foreach(\App\Models\Appointment::paymentStatusOptions() as $val => $label)
                            <option value="{{ $val }}" @selected(old('payment_status', \App\Models\Appointment::PAYMENT_UNPAID) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Client notes</label>
                    <textarea name="client_notes" rows="3" placeholder="Visible to client…"
                              class="form-textarea @error('client_notes') form-input-error @enderror">{{ old('client_notes') }}</textarea>
                    @error('client_notes')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Internal notes</label>
                    <textarea name="internal_notes" rows="3" placeholder="Staff only…"
                              class="form-textarea @error('internal_notes') form-input-error @enderror">{{ old('internal_notes') }}</textarea>
                    @error('internal_notes')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Book Appointment</button>
                <a href="{{ route('appointments.index') }}" class="btn-outline"
                   @click="if (dirty && ! confirm('Discard changes? Any unsaved information will be lost.')) { $event.preventDefault() }">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
window.syncApptQuickCreateServiceRows = function () {
    document.querySelectorAll('#appt-services-list .appt-service-quick-row').forEach((wrap) => {
        wrap.style.display = '';
    });
};
function timeslotPicker(occupiedUrl) {
    return {
        ...appointmentSchedulerMixin(),
        occupiedUrl,
        today: @js($todayYmd),
        staffId: '{{ $defaultStaffId }}',
        selectedDate: '{{ old('starts_at') ? substr(old('starts_at'), 0, 10) : '' }}',
        selectedTime: '{{ old('starts_at') ? substr(old('starts_at'), 11, 5) : '' }}',
        blocked: [],
        blockedDetails: {},
        blockedReasonMessages: [],
        loadingSlots: false,
        init() {
            this.initScheduler();
            this.$watch('staffId', () => {
                this.selectedTime = '';
                this.fetchBlocked();
            });
            this.$watch('selectedDate', () => {
                if (this.selectedDate) this.fetchBlocked();
            });
            const form = document.getElementById('appt-create-form');
            if (form) {
                form.addEventListener('change', (e) => {
                    const t = e.target;
                    if (t && t.matches && t.matches('#appt-services-list input[name="services[]"]')) {
                        this.selectedTime = '';
                        this.fetchBlocked();
                    }
                });
            }
            window.addEventListener('appt-services-changed', () => {
                this.selectedTime = '';
                this.fetchBlocked();
            });
            if (this.staffId && this.selectedDate) this.fetchBlocked();
        },
        onDateChange() {
            if (this.selectedDate < this.today) {
                this.selectedDate = this.today;
            }
            this.syncViewMonthFromDate();
            this.buildWeekDays();
            this.selectedTime = '';
            this.fetchBlocked();
        },
        isBlocked(slot) {
            return this.blocked.includes(slot);
        },
        slotBlockTitle(slot) {
            if (!this.isBlocked(slot) || this.loadingSlots) {
                return '';
            }
            return this.blockedDetails[slot] || 'Unavailable';
        },
        collectBlockedReasonMessages() {
            const seen = new Set();
            const out = [];
            for (const t of this.blocked) {
                const m = this.blockedDetails[t];
                if (m && !seen.has(m)) {
                    seen.add(m);
                    out.push(m);
                }
            }
            return out;
        },
        pickSlot(slot) {
            if (this.isBlocked(slot) || this.loadingSlots) return;
            this.selectedTime = slot;
        },
        async fetchBlocked() {
            if (!this.staffId || !this.selectedDate) {
                this.blocked = [];
                this.blockedDetails = {};
                this.blockedReasonMessages = [];
                return;
            }
            this.loadingSlots = true;
            try {
                const u = new URL(this.occupiedUrl, window.location.origin);
                u.searchParams.set('date', this.selectedDate);
                u.searchParams.set('staff_id', String(this.staffId));
                document.querySelectorAll('#appt-services-list input[name="services[]"]:checked').forEach((el) => {
                    u.searchParams.append('service_ids[]', el.value);
                });
                const r = await fetch(u.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                if (!r.ok) throw new Error('slots');
                const data = await r.json();
                this.blocked = data.blocked || [];
                this.blockedDetails = data.blocked_details || {};
                this.blockedReasonMessages = this.collectBlockedReasonMessages();
                if (this.isBlocked(this.selectedTime)) this.selectedTime = '';
            } catch (e) {
                this.blocked = [];
                this.blockedDetails = {};
                this.blockedReasonMessages = [];
            } finally {
                this.loadingSlots = false;
            }
        },
    };
}
</script>
@endpush

@endsection
