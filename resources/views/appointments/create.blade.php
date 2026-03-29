@extends('layouts.app')
@section('title', 'New Appointment')
@section('page-title', 'New Appointment')
@section('content')

@php $occupiedSlotsUrl = route('appointments.occupied-slots'); @endphp

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required class="form-select @error('client_id') form-input-error @enderror">
                    <option value="">Select a client…</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->first_name }} {{ $client->last_name }} {{ $client->phone ? '— '.$client->phone : '' }}
                    </option>
                    @endforeach
                </select>
                @error('client_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div x-data="timeslotPicker(@js($occupiedSlotsUrl))" x-init="init()">
                <div>
                    <label class="form-label">Staff member <span class="text-red-500">*</span></label>
                    <select name="staff_id" required x-model="staffId"
                            class="form-select @error('staff_id') form-input-error @enderror">
                        <option value="">Select staff…</option>
                        @foreach($staff as $s)
                        <option value="{{ $s->id }}" {{ (string) old('staff_id', '') === (string) $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('staff_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                {{-- Date --}}
                <div class="mb-4 mt-5">
                    <label class="form-label uppercase tracking-wide text-xs font-bold text-velour-600 dark:text-velour-400">Date <span class="text-red-500">*</span></label>
                    <input type="date"
                           name="date_picker"
                           x-model="selectedDate"
                           @change="onDateChange()"
                           :min="today"
                           required
                           class="form-input @error('starts_at') form-input-error @enderror">
                </div>

                {{-- Time slots --}}
                <div x-show="selectedDate && staffId" x-cloak>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <label class="form-label uppercase tracking-wide text-xs font-bold text-velour-600 dark:text-velour-400 mb-0">Time slot <span class="text-red-500">*</span></label>
                        <span x-show="loadingSlots" class="text-xs text-muted">Checking availability…</span>
                    </div>
                    <p class="text-xs text-muted mb-2">Unavailable times are already booked for this staff member on this date.</p>
                    <div class="grid grid-cols-4 gap-2">
                        <template x-for="slot in timeSlots" :key="slot">
                            <button type="button"
                                    @click="pickSlot(slot)"
                                    :disabled="isBlocked(slot) || loadingSlots"
                                    :aria-disabled="isBlocked(slot) || loadingSlots"
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
                    <p x-show="!selectedTime && selectedDate && staffId && !loadingSlots" class="text-xs text-amber-600 dark:text-amber-400 mt-2">Please select an available time slot.</p>
                </div>

                <input type="hidden" name="starts_at" :value="selectedDate && selectedTime ? selectedDate + ' ' + selectedTime + ':00' : ''">
                @error('starts_at')<p class="form-error mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Services <span class="text-red-500">*</span></label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-gray-800 @error('services') border-red-400 dark:border-red-500 @enderror">
                    @foreach($services as $svc)
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer">
                        <input type="checkbox" name="services[]" value="{{ $svc->id }}"
                               {{ in_array($svc->id, old('services', [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="flex-1 text-sm text-body">{{ $svc->name }}</span>
                        <span class="text-xs text-muted">{{ $svc->duration_minutes }}min</span>
                        <span class="text-sm font-semibold text-heading">{{ \App\Helpers\CurrencyHelper::format($svc->price, $currentSalon->currency ?? 'GBP') }}</span>
                    </label>
                    @endforeach
                </div>
                @error('services')<p class="form-error">{{ $message }}</p>@enderror
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
                <a href="{{ route('appointments.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function timeslotPicker(occupiedUrl) {
    return {
        occupiedUrl,
        today: new Date().toISOString().split('T')[0],
        staffId: '{{ old('staff_id', '') }}',
        selectedDate: '{{ old('starts_at') ? substr(old('starts_at'), 0, 10) : '' }}',
        selectedTime: '{{ old('starts_at') ? substr(old('starts_at'), 11, 5) : '' }}',
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
