@extends('layouts.app')
@section('title', 'Appointment #'.$appointment->reference)
@section('page-title', 'Appointment Details')
@section('content')
@php
    $isScopedStaffPanel = auth()->user()?->dashboardScopedStaffId() !== null;
    $balanceDue = $appointment->balance_due;
    $isCompleted = $appointment->status === 'completed';
    $isPartialPayment = $appointment->payment_status === \App\Models\Appointment::PAYMENT_PARTIAL
        || ($balanceDue > 0 && (float) $appointment->amount_paid > 0);
@endphp

<div class="max-w-2xl space-y-5">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
        {{ $errors->first() }}
    </div>
    @endif

    {{-- Header card --}}
    <div class="card p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-heading">
                    {{ $appointment->client?->first_name }} {{ $appointment->client?->last_name }}
                </h2>
                <p class="text-sm text-muted mt-0.5">Ref: {{ $appointment->reference }}</p>
            </div>
            @php
                $statusColors = [
                    'pending'     => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                    'confirmed'   => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                    'checked_in'  => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
                    'in_progress' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                    'completed'   => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
                    'cancelled'   => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                    'no_show'     => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                ];
            @endphp
            <span class="px-3 py-1.5 text-sm font-semibold rounded-xl {{ $statusColors[$appointment->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
            </span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="stat-label mb-1">Date</p>
                <p class="font-semibold text-heading">@bizdate($appointment->starts_at)</p>
            </div>
            <div>
                <p class="stat-label mb-1">Time</p>
                <p class="font-semibold text-heading">{{ \App\Support\DisplayFormatter::businessTimeRange($salon, $appointment->starts_at, $appointment->ends_at) }}</p>
            </div>
            <div>
                <p class="stat-label mb-1">Staff</p>
                <p class="font-semibold text-heading">{{ $appointment->staff?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="stat-label mb-1">Duration</p>
                <p class="font-semibold text-heading">{{ $appointment->duration_minutes }} min</p>
            </div>
            <div>
                <p class="stat-label mb-1">Total</p>
                <p class="font-bold text-heading text-base">@money($appointment->total_price)</p>
            </div>
            <div>
                <p class="stat-label mb-1">{{ $isPartialPayment ? 'Partial paid' : 'Amount paid' }}</p>
                <p class="font-semibold text-heading">@money($appointment->amount_paid)</p>
            </div>
            <div>
                <p class="stat-label mb-1">Balance due</p>
                <p class="font-semibold {{ $balanceDue > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-heading' }}">
                    @if($balanceDue > 0)@money($balanceDue)@else—@endif
                </p>
            </div>
            <div>
                <p class="stat-label mb-1">Source of booking</p>
                <p class="font-semibold text-heading">{{ \App\Models\Appointment::sourceLabel($appointment->source) }}</p>
            </div>
            <div>
                <p class="stat-label mb-1">Payment status</p>
                <p class="font-semibold text-heading">{{ \App\Models\Appointment::paymentStatusLabel($appointment->payment_status) }}</p>
            </div>
            @if(!$isScopedStaffPanel && $appointment->client?->email)
            <div>
                <p class="stat-label mb-1">Client Email</p>
                <p class="font-semibold text-heading truncate">{{ $appointment->client->email }}</p>
            </div>
            @endif
            @if(!$isScopedStaffPanel && $appointment->client?->phone)
            <div>
                <p class="stat-label mb-1">Client Phone</p>
                <p class="font-semibold text-heading">{{ $appointment->client->phone }}</p>
            </div>
            @endif
            <div class="col-span-2 sm:col-span-3 border-t border-gray-100 dark:border-gray-800 pt-3 mt-1">
                <p class="stat-label mb-1">Time of booking</p>
                <p class="font-semibold text-heading">@bizdatetime($appointment->created_at)</p>
            </div>
            @if($appointment->deposit_required)
            <div>
                <p class="stat-label mb-1">Deposit</p>
                <p class="font-semibold {{ $appointment->deposit_paid_flag ? 'text-green-600' : 'text-amber-600' }}">
                    {{ $appointment->deposit_paid_flag ? 'Paid' : 'Unpaid' }}
                    (@money($appointment->deposit_paid))
                </p>
            </div>
            @endif
        </div>

        @if($isCompleted)
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-2">
            <a href="{{ route('appointments.invoice.pdf', $appointment) }}" target="_blank" rel="noopener" class="btn-primary text-sm">
                Generate invoice (PDF)
            </a>
            <a href="{{ route('appointments.invoice.show', $appointment) }}" class="btn-outline text-sm">
                Email / WhatsApp invoice
            </a>
        </div>
        @elseif($appointment->payment_status !== \App\Models\Appointment::PAYMENT_PAID && in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress'], true))
        <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('pos.create', ['appointment' => $appointment->id]) }}" class="btn-primary text-sm inline-flex items-center gap-1.5">
                Collect payment &amp; invoice
            </a>
        </div>
        @endif
    </div>

    {{-- Services --}}
    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Services</h3>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($displayServiceLines as $line)
            <div class="flex items-center justify-between px-6 py-3.5 gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-heading">
                        {{ $line['name'] }}
                        @if($line['source'] === 'pos')
                            <span class="ml-1.5 text-[10px] font-semibold uppercase text-velour-600 dark:text-velour-400">Added at POS</span>
                        @endif
                    </p>
                    <p class="text-xs text-muted">
                        @if($line['duration'])
                            {{ $line['duration'] }} min
                        @else
                            —
                        @endif
                    </p>
                    @if(! empty($line['line_meta']['variant']) || ! empty($line['line_meta']['addons']))
                        <p class="text-[11px] text-muted mt-1">
                            @if(! empty($line['line_meta']['variant']))
                                <span>Variant: {{ $line['line_meta']['variant'] }}</span>
                            @endif
                            @if(! empty($line['line_meta']['addons']))
                                @if(! empty($line['line_meta']['variant'])) · @endif
                                <span>Add-ons:
                                    @foreach($line['line_meta']['addons'] as $ad)
                                        {{ $ad['name'] ?? '' }}@if(!$loop->last), @endif
                                    @endforeach
                                </span>
                            @endif
                        </p>
                    @endif
                </div>
                <p class="font-semibold text-heading shrink-0">@money($line['price'])</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Notes --}}
    @if($appointment->client_notes || $appointment->internal_notes)
    <div class="card p-6 grid sm:grid-cols-2 gap-5">
        @if($appointment->client_notes)
        <div>
            <p class="stat-label mb-2">Client Notes</p>
            <p class="text-sm text-body">{{ $appointment->client_notes }}</p>
        </div>
        @endif
        @if($appointment->internal_notes)
        <div>
            <p class="stat-label mb-2">Internal Notes</p>
            <p class="text-sm text-body">{{ $appointment->internal_notes }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Context-aware action buttons ── --}}
    <div class="card p-6 space-y-4" x-data="{ showCancel: false, showReschedule: false, showRebook: false }">
        <h3 class="font-semibold text-heading">Actions</h3>

        {{-- Payment required warning --}}
        @if($appointment->status === 'completed' && $appointment->payment_status !== \App\Models\Appointment::PAYMENT_PAID)
        <div class="px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-900/15 border border-amber-200 dark:border-amber-800 text-sm text-amber-800 dark:text-amber-300">
            <p class="font-semibold">Payment pending</p>
            <p class="text-xs mt-0.5 text-amber-700 dark:text-amber-400">This appointment is marked complete but payment has not been collected yet.</p>
        </div>
        @endif

        <div class="flex flex-wrap gap-2">

            {{-- Collect Payment (unpaid appointments) --}}
            @if($appointment->payment_status !== \App\Models\Appointment::PAYMENT_PAID && in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress', 'completed']))
            <a href="{{ route('pos.create', ['appointment' => $appointment->id]) }}"
               class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                Collect Payment
            </a>
            @endif

            {{-- Confirm (pending only) --}}
            @if($appointment->status === 'pending')
            <form action="{{ route('appointments.confirm', $appointment->id) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                        onclick="return confirm('Confirm this appointment?')">
                    ✓ Confirm
                </button>
            </form>
            @endif

            {{-- Complete (confirmed / checked_in / in_progress) — only if paid --}}
            @if(in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress']))
                @if($appointment->payment_status === \App\Models\Appointment::PAYMENT_PAID)
                <form action="{{ route('appointments.complete', $appointment->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors"
                            onclick="return confirm('Mark this appointment as completed?')">
                        ✓ Mark Complete
                    </button>
                </form>
                @else
                <form action="{{ route('appointments.complete', $appointment->id) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors">
                        ✓ Complete &amp; Pay
                    </button>
                </form>
                @endif
            @endif

            {{-- Rebook (completed or cancelled) --}}
            @if(in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
            <button @click="showRebook = !showRebook; showCancel = false; showReschedule = false"
                    class="px-4 py-2 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-colors inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Rebook
            </button>
            @endif

            {{-- Reschedule toggle (not terminal) --}}
            @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
            <button @click="showReschedule = !showReschedule; showCancel = false; showRebook = false"
                    class="px-4 py-2 text-sm font-semibold rounded-xl border border-amber-400 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                🔄 Reschedule
            </button>
            @endif

            {{-- Cancel toggle (not terminal) --}}
            @if(!$isScopedStaffPanel && ! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
            <button @click="showCancel = !showCancel; showReschedule = false; showRebook = false"
                    class="px-4 py-2 text-sm font-semibold rounded-xl border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                ✕ Cancel
            </button>
            @endif

        </div>

        {{-- Reschedule form --}}
        @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
        <div x-show="showReschedule" x-cloak
             class="border border-amber-200 dark:border-amber-800 rounded-xl p-4 bg-amber-50 dark:bg-amber-900/10 space-y-3">
            <p class="text-sm font-semibold text-amber-700 dark:text-amber-400">Reschedule Appointment</p>
            <form action="{{ route('appointments.reschedule', $appointment->id) }}" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">New date & time *</label>
                        <input type="datetime-local" name="starts_at"
                               value="{{ old('starts_at', $appointment->starts_at->format('Y-m-d\TH:i')) }}"
                               min="{{ now()->format('Y-m-d\TH:i') }}"
                               class="form-input">
                        @error('starts_at')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-end gap-2">
                        <x-searchable-select
                            id="appt-reschedule-staff"
                            name="staff_id"
                            label="Reassign staff (optional)"
                            :required="false"
                            :search-url="route('lookup.staff')"
                            search-placeholder="Search staff…"
                            hint="No match? Use + to add new."
                            trigger-class="form-select w-full">
                            <option value="" data-sticky>Keep current ({{ $appointment->staff?->name }})</option>
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </x-searchable-select>
                        <x-relation-quick-create-trigger type="staff" select-id="appt-reschedule-staff" :staff-services-by-role="$staffQuickCreateServicesByRole ?? []" />
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-amber-500 hover:bg-amber-600 text-white transition-colors">
                        Confirm Reschedule
                    </button>
                    <button type="button" @click="showReschedule = false" class="px-4 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-700 text-muted hover:text-body transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Cancel form --}}
        @if(!$isScopedStaffPanel && ! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
        <div x-show="showCancel" x-cloak
             class="border border-red-200 dark:border-red-800 rounded-xl p-4 bg-red-50 dark:bg-red-900/10 space-y-3">
            <p class="text-sm font-semibold text-red-700 dark:text-red-400">Cancel Appointment</p>
            <form action="{{ route('appointments.cancel', $appointment->id) }}" method="POST" class="space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="form-label">Reason (optional)</label>
                    <textarea name="cancellation_reason" rows="2"
                              class="form-textarea"
                              placeholder="e.g. Client requested cancellation…">{{ old('cancellation_reason') }}</textarea>
                    @error('cancellation_reason')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white transition-colors"
                            onclick="return confirm('Are you sure you want to cancel this appointment?')">
                        Confirm Cancellation
                    </button>
                    <button type="button" @click="showCancel = false" class="px-4 py-2 text-sm rounded-xl border border-gray-300 dark:border-gray-700 text-muted hover:text-body transition-colors">
                        Back
                    </button>
                </div>
            </form>
        </div>
        @endif

        {{-- Rebook panel --}}
        @if(in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
        <div x-show="showRebook" x-cloak
             class="border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 bg-indigo-50 dark:bg-indigo-900/10 space-y-3">
            <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-400">Rebook this client</p>
            <p class="text-xs text-indigo-600/80 dark:text-indigo-400/80">Choose what to book for <strong>{{ $appointment->client?->first_name }} {{ $appointment->client?->last_name }}</strong>:</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('appointments.create', ['client_id' => $appointment->client_id, 'services' => $appointment->services->pluck('service_id')->join(','), 'staff_id' => $appointment->staff_id]) }}"
                   class="px-4 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                    Same services
                </a>
                <a href="{{ route('appointments.create', ['client_id' => $appointment->client_id, 'services' => $appointment->services->pluck('service_id')->join(','), 'staff_id' => $appointment->staff_id, 'addons' => 1]) }}"
                   class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-indigo-300 dark:border-indigo-700 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                    Same + add-ons
                </a>
                <a href="{{ route('appointments.create', ['client_id' => $appointment->client_id]) }}"
                   class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-gray-300 dark:border-gray-700 text-body hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    Different services
                </a>
            </div>
            <button type="button" @click="showRebook = false" class="text-xs text-muted hover:text-body mt-1">Cancel</button>
        </div>
        @endif

        {{-- Cancelled / completed info --}}
        @if($appointment->status === 'cancelled')
        <div class="text-sm text-muted">
            Cancelled {{ $appointment->cancelled_at?->format('d M Y \a\t H:i') }}
            @if($appointment->cancellation_reason)
            — <em>{{ $appointment->cancellation_reason }}</em>
            @endif
        </div>
        @endif

        {{-- Navigation --}}
        <div class="flex gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-outline text-sm">Edit Details</a>
            <a href="{{ route('appointments.index', ['selected' => $appointment->id]) }}" class="btn text-sm text-muted hover:text-body">← Back to list</a>
        </div>
    </div>

</div>

@endsection
