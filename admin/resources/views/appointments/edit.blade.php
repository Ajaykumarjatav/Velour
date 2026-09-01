@extends('layouts.app')
@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')
@section('content')

@php
    $occupiedSlotsUrl = route('appointments.occupied-slots');
    $editOccupiedServiceIds = $appointment->services->pluck('service_id')->map(fn ($id) => (int) $id)->values()->all();
@endphp

@include('partials.appointment-scheduler-alpine')

<div class="max-w-4xl">
    <div class="card p-6">
        <form action="{{ route('appointments.update', $appointment->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="space-y-0">
                <div class="flex items-end gap-2">
                    @include('partials.appointment-client-picker', [
                        'selectId' => 'appt-edit-client',
                        'clients' => $clients,
                        'selectedClientId' => old('client_id', $appointment->client_id),
                    ])
                    @if(auth()->user()->dashboardScopedStaffId() === null)
                    <x-relation-quick-create-trigger type="client" select-id="appt-edit-client" :client-loyalty-tiers="$clientQuickCreateLoyaltyTiers ?? collect()" />
                    @endif
                </div>
            </div>

            <div x-data="timeslotPickerEdit(@js($occupiedSlotsUrl), {{ $appointment->id }}, @js($editOccupiedServiceIds))" x-init="init()">
                <div class="flex items-end gap-2">
                    <x-searchable-select
                        id="appt-edit-staff"
                        name="staff_id"
                        label="Staff member"
                        :required="true"
                        error-name="staff_id"
                        :search-url="route('lookup.staff')"
                        search-placeholder="Search staff…"
                        hint="No match? Use + to add new."
                        trigger-class="form-select w-full"
                        x-model="staffId">
                        @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ (string) old('staff_id', $appointment->staff_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-searchable-select>
                    <x-relation-quick-create-trigger type="staff" select-id="appt-edit-staff" />
                </div>

                <div class="mt-5">
                    <label class="form-label mb-2">Date &amp; time <span class="text-red-500">*</span></label>
                    <p class="text-xs text-muted mb-3">
                        Same rules as new bookings. This appointment is excluded from overlap checks.
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
                            <option value="{{ $val }}" @selected(old('source', $appointment->source) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('source')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Payment status <span class="text-red-500">*</span></label>
                    <select name="payment_status" class="form-select @error('payment_status') form-input-error @enderror" required>
                        @foreach(\App\Models\Appointment::paymentStatusOptions() as $val => $label)
                            <option value="{{ $val }}" @selected(old('payment_status', $appointment->payment_status ?? \App\Models\Appointment::PAYMENT_UNPAID) === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Client notes</label>
                    <textarea name="client_notes" rows="3" class="form-textarea">{{ old('client_notes', $appointment->client_notes) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Internal notes</label>
                    <textarea name="internal_notes" rows="3" class="form-textarea">{{ old('internal_notes', $appointment->internal_notes) }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('appointments.show', $appointment->id) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function timeslotPickerEdit(occupiedUrl, excludeAppointmentId, serviceIds) {
    const oldStarts = @json(old('starts_at'));
    let dateInit = '{{ $appointment->starts_at->format('Y-m-d') }}';
    let timeInit = '{{ $appointment->starts_at->format('H:i') }}';
    if (oldStarts && oldStarts.length >= 16) {
        dateInit = oldStarts.substring(0, 10);
        timeInit = oldStarts.substring(11, 16);
    }
    return {
        ...appointmentSchedulerMixin(),
        occupiedUrl,
        excludeAppointmentId,
        serviceIds: Array.isArray(serviceIds) ? serviceIds : [],
        today: @js($todayYmd),
        staffId: '{{ old('staff_id', $appointment->staff_id) }}',
        selectedDate: dateInit,
        selectedTime: timeInit,
        blocked: [],
        blockedDetails: {},
        blockedReasonMessages: [],
        loadingSlots: false,
        init() {
            this.initScheduler();
            if (this.selectedDate < this.today) {
                this.selectedDate = this.today;
            }
            this.syncViewMonthFromDate();
            this.buildWeekDays();
            this.$watch('staffId', () => {
                this.selectedTime = '';
                this.fetchBlocked();
            });
            this.$watch('selectedDate', () => {
                if (this.selectedDate) this.fetchBlocked();
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
                u.searchParams.set('exclude_appointment_id', String(this.excludeAppointmentId));
                this.serviceIds.forEach((id) => u.searchParams.append('service_ids[]', String(id)));
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
