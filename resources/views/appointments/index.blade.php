@extends('layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('content')
@php
    $isScopedStaffPanel = auth()->user()?->dashboardScopedStaffId() !== null;
    $currency = $salon->currency ?? 'GBP';
    $appointmentRows = $appointments->getCollection()->map(function (\App\Models\Appointment $apt) use ($salon, $currency, $isScopedStaffPanel) {
        $st = $apt->status;
        $pay = $apt->payment_status ?? \App\Models\Appointment::PAYMENT_UNPAID;

        return [
            'id' => (int) $apt->id,
            'client_name' => trim(($apt->client?->first_name ?? '') . ' ' . ($apt->client?->last_name ?? '')),
            'reference' => (string) $apt->reference,
            'service_summary' => $apt->services->first()?->service?->name ?? '—',
            'service_extra' => max(0, $apt->services->count() - 1),
            'staff_name' => $apt->staff?->name ?? '—',
            'starts_clock' => \App\Support\DisplayFormatter::businessClock($salon, $apt->starts_at),
            'starts_date' => \App\Support\DisplayFormatter::businessDate($salon, $apt->starts_at),
            'time_range' => \App\Support\DisplayFormatter::businessTimeRange($salon, $apt->starts_at, $apt->ends_at),
            'amount' => \App\Helpers\CurrencyHelper::format((float) $apt->total_price, $currency),
            'amount_paid' => \App\Helpers\CurrencyHelper::format((float) $apt->amount_paid, $currency),
            'status' => $st,
            'status_label' => ucfirst(str_replace('_', ' ', (string) $st)),
            'source' => (string) ($apt->source ?? 'manual'),
            'source_label' => \App\Models\Appointment::sourceLabel($apt->source),
            'payment_status' => $pay,
            'payment_label' => \App\Models\Appointment::paymentStatusLabel($pay),
            'booked_at' => \App\Support\DisplayFormatter::businessDateTime($salon, $apt->created_at),
            'duration_minutes' => (int) $apt->duration_minutes,
            'client_notes' => $apt->client_notes,
            'internal_notes' => $isScopedStaffPanel ? null : $apt->internal_notes,
            'show_url' => route('appointments.show', $apt->id),
            'services' => $apt->services->map(fn ($svc) => [
                'name' => $svc->service_name,
                'price' => \App\Helpers\CurrencyHelper::format((float) $svc->price, $currency),
                'duration' => (int) $svc->duration_minutes,
            ])->values()->all(),
        ];
    })->values();
    $firstAppointmentId = $initialSelectedAppointmentId ?? optional($appointments->first())->id;
@endphp

<div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 px-4 py-3.5 sm:px-5 sm:py-4 mb-7 shadow-sm dark:shadow-none">
    <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-5">
        <form action="{{ route('appointments.index') }}" method="GET"
              class="flex flex-1 flex-wrap items-center gap-2.5 sm:gap-3 min-w-0">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search client or reference…"
                   class="form-input w-full min-w-0 sm:flex-1 sm:min-w-[12rem] lg:max-w-md">
            <input type="date" name="date" value="{{ $date }}"
                   title="Filter by date"
                   class="form-input w-full min-w-[10.5rem] sm:w-[11.5rem] sm:flex-initial shrink-0">
            <x-searchable-select
                id="appt-ix-status"
                name="status"
                wrapper-class="w-full min-w-0 sm:w-[10.5rem] shrink-0"
                :search-url="null"
                search-placeholder="Status…"
                trigger-class="form-select w-full min-w-0 sm:w-[10.5rem] shrink-0">
                <option value="">All statuses</option>
                @foreach(['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </x-searchable-select>
            <x-searchable-select
                id="appt-ix-staff"
                name="staff_id"
                wrapper-class="w-full min-w-0 sm:min-w-[10.5rem] sm:max-w-[14rem] shrink-0"
                :search-url="route('lookup.staff')"
                search-placeholder="Staff…"
                trigger-class="form-select w-full min-w-0 sm:min-w-[10.5rem] sm:max-w-[14rem] shrink-0">
                <option value="">All staff</option>
                @foreach($staff as $s)
                <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </x-searchable-select>
            <div class="flex w-full sm:w-auto gap-2 shrink-0">
                <button type="submit" class="btn-secondary flex-1 sm:flex-initial min-w-0">Filter</button>
                <a href="{{ route('appointments.index') }}" class="btn-outline flex-1 sm:flex-initial min-w-0 text-center">Clear</a>
            </div>
        </form>
        @if(!$isScopedStaffPanel)
            <a href="{{ route('appointments.create') }}"
               class="btn-primary shrink-0 w-full lg:w-auto text-center whitespace-nowrap lg:min-w-[11rem]">
                + New Appointment
            </a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 xl:gap-6"
     x-data="{
        selectedAppointmentId: @json($firstAppointmentId ? (int) $firstAppointmentId : null),
        isScopedStaff: {{ $isScopedStaffPanel ? 'true' : 'false' }},
        appointments: @js($appointmentRows),
        selectedAppointment() {
            return this.appointments.find(a => a.id === this.selectedAppointmentId) || this.appointments[0] || null;
        },
        selectAppointment(id) {
            this.selectedAppointmentId = id;
        },
        statusPillClass(s) {
            const m = {
                confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                checked_in: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                in_progress: 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200',
                completed: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                cancelled: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                no_show: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                pending: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            };
            return m[s] || 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        },
     }">
    <div class="table-wrap [&_thead_th]:py-3 [&_thead_th]:px-5 [&_tbody_td]:py-2.5 [&_tbody_td]:px-5 min-w-0">
        <table class="data-table">
            <thead>
            <tr>
                <th>Client</th>
                <th class="hidden md:table-cell">Service</th>
                <th class="hidden sm:table-cell">Staff</th>
                <th>Date &amp; time</th>
                <th class="hidden lg:table-cell text-right"><abbr title="@currencyLabel">Amount</abbr></th>
                <th class="text-center">Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($appointments as $apt)
            <tr @click="selectAppointment({{ (int) $apt->id }})"
                :class="selectedAppointmentId === {{ (int) $apt->id }}
                    ? 'bg-velour-50/95 dark:bg-velour-950/35 ring-1 ring-inset ring-velour-200/80 dark:ring-velour-500/25 hover:bg-velour-100/85 dark:hover:bg-velour-950/45'
                    : 'hover:bg-gray-50/90 dark:hover:bg-gray-800/40'"
                class="cursor-pointer transition-colors">
                <td>
                    <div class="flex flex-col gap-0 leading-snug">
                        <p class="font-semibold text-heading">{{ $apt->client?->first_name }} {{ $apt->client?->last_name }}</p>
                        <p class="text-xs text-muted">{{ $apt->reference }}</p>
                    </div>
                </td>
                <td class="hidden md:table-cell text-body max-w-[150px]">
                    <div class="flex flex-col gap-0 leading-snug max-w-full">
                        <span class="truncate">{{ $apt->services->first()?->service?->name ?? '—' }}</span>
                        @if($apt->services->count() > 1)<span class="text-xs text-muted">+{{ $apt->services->count() - 1 }}</span>@endif
                    </div>
                </td>
                <td class="hidden sm:table-cell text-body">{{ $apt->staff?->name ?? '—' }}</td>
                <td>
                    <div class="flex flex-col gap-0 leading-snug">
                        <p class="font-medium text-body">@bizclock($apt->starts_at)</p>
                        <p class="text-xs text-muted">@bizdate($apt->starts_at)</p>
                    </div>
                </td>
                <td class="hidden lg:table-cell font-semibold text-heading text-right tabular-nums">@money($apt->total_price)</td>
                <td class="text-center">
                    @php
                        $colors = [
                            'confirmed' => 'badge-blue',
                            'completed' => 'badge-green',
                            'cancelled' => 'badge-red',
                            'no_show'   => 'badge-yellow',
                            'pending'   => 'badge-gray',
                        ];
                        $cls = $colors[$apt->status] ?? 'badge-gray';
                    @endphp
                    <span class="{{ $cls }}">{{ ucfirst(str_replace('_',' ',$apt->status)) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-6 py-14 text-center text-sm text-muted">No appointments found</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-6 min-h-[18rem] shadow-sm dark:shadow-none min-w-0" x-show="selectedAppointment()" x-cloak>
        <template x-if="selectedAppointment()">
            <div class="space-y-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 space-y-1">
                        <p class="font-semibold text-heading text-lg leading-snug" x-text="selectedAppointment().client_name"></p>
                        <p class="text-xs text-muted font-mono" x-text="selectedAppointment().reference"></p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                  :class="statusPillClass(selectedAppointment().status)"
                                  x-text="selectedAppointment().status_label"></span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200" x-text="selectedAppointment().source_label"></span>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                  :class="{
                                    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300': selectedAppointment().payment_status === 'unpaid',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200': selectedAppointment().payment_status === 'partial',
                                    'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200': selectedAppointment().payment_status === 'paid',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-200': selectedAppointment().payment_status === 'refunded',
                                  }"
                                  x-text="selectedAppointment().payment_label"></span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 shrink-0 items-end">
                        <a :href="selectedAppointment().show_url" class="btn-outline btn-sm whitespace-nowrap">View</a>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5 sm:gap-3">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Total</p>
                        <p class="mt-1.5 text-lg font-semibold tabular-nums text-heading" x-text="selectedAppointment().amount"></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Paid</p>
                        <p class="mt-1.5 text-lg font-semibold tabular-nums text-heading" x-text="selectedAppointment().amount_paid"></p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 p-3.5 text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Duration</p>
                        <p class="mt-1.5 text-sm font-semibold text-heading"><span x-text="selectedAppointment().duration_minutes"></span> min</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Appointment time</p>
                        <p class="mt-1 font-medium text-heading" x-text="selectedAppointment().starts_date"></p>
                        <p class="text-xs text-muted" x-text="selectedAppointment().time_range"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Staff</p>
                        <p class="mt-1 text-body" x-text="selectedAppointment().staff_name"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Source of booking</p>
                        <p class="mt-1 text-body" x-text="selectedAppointment().source_label"></p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-muted">Payment status</p>
                        <p class="mt-1 text-body" x-text="selectedAppointment().payment_label"></p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Time of booking</p>
                        <p class="mt-1 text-body text-sm" x-text="selectedAppointment().booked_at"></p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="px-5 py-3.5 bg-gray-50/90 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <h4 class="text-sm font-semibold tracking-tight text-heading">Services</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-[11px] font-semibold uppercase tracking-wider text-muted bg-gray-50/80 dark:bg-gray-900/45">
                                <tr>
                                    <th class="px-5 py-2.5 text-left">Service</th>
                                    <th class="px-5 py-2.5 text-left">Duration</th>
                                    <th class="px-5 py-2.5 text-left">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(svc, idx) in (selectedAppointment().services || [])" :key="idx">
                                    <tr class="border-t border-gray-100 dark:border-gray-800/80">
                                        <td class="px-5 py-2.5 text-body" x-text="svc.name"></td>
                                        <td class="px-5 py-2.5 text-muted"><span x-text="svc.duration"></span> min</td>
                                        <td class="px-5 py-2.5 text-body tabular-nums" x-text="svc.price"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm" x-show="selectedAppointment().client_notes || selectedAppointment().internal_notes">
                    <div x-show="selectedAppointment().client_notes">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Client notes</p>
                        <p class="mt-1 text-body whitespace-pre-line" x-text="selectedAppointment().client_notes"></p>
                    </div>
                    <div x-show="!isScopedStaff && selectedAppointment().internal_notes">
                        <p class="text-[11px] uppercase tracking-wide text-muted">Internal notes</p>
                        <p class="mt-1 text-body whitespace-pre-line" x-text="selectedAppointment().internal_notes"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<div class="mt-6 flex justify-center sm:justify-end">{{ $appointments->links() }}</div>
@endsection
