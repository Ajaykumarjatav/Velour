@extends('layouts.app')
@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')
@section('content')

@php $occupiedSlotsUrl = route('appointments.occupied-slots'); @endphp

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('appointments.update', $appointment->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <x-relation-field-with-create
                label="Client"
                name="client_id"
                select-id="appt-edit-client"
                type="client"
                :required="true">
                @foreach($clients as $client)
                <option value="{{ $client->id }}" {{ old('client_id', $appointment->client_id) == $client->id ? 'selected' : '' }}>
                    {{ $client->first_name }} {{ $client->last_name }}
                </option>
                @endforeach
            </x-relation-field-with-create>

            <div x-data="timeslotPickerEdit(@js($occupiedSlotsUrl), {{ $appointment->id }})" x-init="init()">
                <div class="flex items-end gap-2">
                    <div class="flex-1 min-w-0">
                        <label class="form-label" for="appt-edit-staff">Staff member</label>
                        <select name="staff_id" id="appt-edit-staff" required x-model="staffId" class="form-select w-full">
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ (string) old('staff_id', $appointment->staff_id) === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-relation-quick-create-trigger type="staff" select-id="appt-edit-staff" />
                </div>

                <div class="mb-4 mt-5">
                    <label class="form-label uppercase tracking-wide text-xs font-bold text-velour-600 dark:text-velour-400">Date <span class="text-red-500">*</span></label>
                    <input type="date"
                           x-model="selectedDate"
                           @change="onDateChange()"
                           :min="today"
                           required
                           class="form-input">
                </div>

                <div x-show="selectedDate && staffId" x-cloak>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <label class="form-label uppercase tracking-wide text-xs font-bold text-velour-600 dark:text-velour-400 mb-0">Time slot <span class="text-red-500">*</span></label>
                        <span x-show="loadingSlots" class="text-xs text-muted">Checking availability…</span>
                    </div>
                    <p class="text-xs text-muted mb-2">Unavailable times are booked or the staff member is off; this appointment is excluded from conflicts.</p>
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="slot in timeSlots" :key="slot">
                            <button type="button"
                                    @click="pickSlot(slot)"
                                    :disabled="isBlocked(slot) || loadingSlots"
                                    :class="isBlocked(slot)
                                        ? 'bg-gray-100 dark:bg-gray-800/80 text-muted border-gray-200 dark:border-gray-700 cursor-not-allowed opacity-60 line-through'
                                        : (selectedTime === slot
                                            ? 'bg-velour-600 text-white border-velour-600 font-bold shadow-sm'
                                            : 'bg-white dark:bg-gray-800 text-body border-gray-200 dark:border-gray-700 hover:border-velour-400 hover:text-velour-600 dark:hover:text-velour-400')"
                                    class="py-2.5 rounded-xl border text-sm font-medium transition-all disabled:pointer-events-none">
                                <span x-text="slot"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <input type="hidden" name="starts_at" :value="selectedDate && selectedTime ? selectedDate + ' ' + selectedTime + ':00' : ''">
                @error('starts_at')<p class="form-error mt-1">{{ $message }}</p>@enderror
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
function timeslotPickerEdit(occupiedUrl, excludeAppointmentId) {
    const oldStarts = @json(old('starts_at'));
    let dateInit = '{{ $appointment->starts_at->format('Y-m-d') }}';
    let timeInit = '{{ $appointment->starts_at->format('H:i') }}';
    if (oldStarts && oldStarts.length >= 16) {
        dateInit = oldStarts.substring(0, 10);
        timeInit = oldStarts.substring(11, 16);
    }
    return {
        occupiedUrl,
        excludeAppointmentId,
        today: new Date().toISOString().split('T')[0],
        staffId: '{{ old('staff_id', $appointment->staff_id) }}',
        selectedDate: dateInit,
        selectedTime: timeInit,
        blocked: [],
        loadingSlots: false,
        timeSlots: [
            '09:00','09:30','10:00','10:30',
            '11:00','11:30','12:00','12:30',
            '13:00','14:00','14:30','15:00',
            '15:30','16:00','16:30','17:00',
            '17:30','18:00','18:30','19:00',
        ],
        init() {
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
            this.selectedTime = '';
            this.fetchBlocked();
        },
        isBlocked(slot) {
            return this.blocked.includes(slot);
        },
        pickSlot(slot) {
            if (this.isBlocked(slot) || this.loadingSlots) return;
            this.selectedTime = slot;
        },
        async fetchBlocked() {
            if (!this.staffId || !this.selectedDate) {
                this.blocked = [];
                return;
            }
            this.loadingSlots = true;
            try {
                const u = new URL(this.occupiedUrl, window.location.origin);
                u.searchParams.set('date', this.selectedDate);
                u.searchParams.set('staff_id', String(this.staffId));
                u.searchParams.set('exclude_appointment_id', String(this.excludeAppointmentId));
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
                if (this.isBlocked(this.selectedTime)) this.selectedTime = '';
            } catch (e) {
                this.blocked = [];
            } finally {
                this.loadingSlots = false;
            }
        },
    };
}
</script>
@endpush

@endsection
