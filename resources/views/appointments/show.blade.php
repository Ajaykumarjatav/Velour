@extends('layouts.app')
@section('title', 'Appointment #'.$appointment->reference)
@section('page-title', 'Appointment Details')
@section('content')

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
                <p class="font-semibold text-heading">{{ $appointment->starts_at->format('d M Y') }}</p>
            </div>
            <div>
                <p class="stat-label mb-1">Time</p>
                <p class="font-semibold text-heading">{{ $appointment->starts_at->format('H:i') }} – {{ $appointment->ends_at->format('H:i') }}</p>
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
                <p class="stat-label mb-1">Source</p>
                <p class="font-semibold text-heading capitalize">{{ str_replace('_', ' ', $appointment->source ?? 'walk_in') }}</p>
            </div>
            @if($appointment->client?->email)
            <div>
                <p class="stat-label mb-1">Client Email</p>
                <p class="font-semibold text-heading truncate">{{ $appointment->client->email }}</p>
            </div>
            @endif
            @if($appointment->client?->phone)
            <div>
                <p class="stat-label mb-1">Client Phone</p>
                <p class="font-semibold text-heading">{{ $appointment->client->phone }}</p>
            </div>
            @endif
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
    </div>

    {{-- Services --}}
    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Services</h3>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($appointment->services as $svc)
            <div class="flex items-center justify-between px-6 py-3.5">
                <div>
                    <p class="font-medium text-heading">{{ $svc->service?->name ?? $svc->service_name }}</p>
                    <p class="text-xs text-muted">{{ $svc->duration_minutes }} min</p>
                </div>
                <p class="font-semibold text-heading">@money($svc->price)</p>
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
    <div class="card p-6 space-y-4" x-data="{ showCancel: false, showReschedule: false }">
        <h3 class="font-semibold text-heading">Actions</h3>

        <div class="flex flex-wrap gap-2">

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

            {{-- Complete (confirmed / checked_in / in_progress) --}}
            @if(in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress']))
            <form action="{{ route('appointments.complete', $appointment->id) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-4 py-2 text-sm font-semibold rounded-xl bg-green-600 hover:bg-green-700 text-white transition-colors"
                        onclick="return confirm('Mark this appointment as completed?')">
                    ✓ Mark Complete
                </button>
            </form>
            @endif

            {{-- Reschedule toggle (not terminal) --}}
            @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
            <button @click="showReschedule = !showReschedule; showCancel = false"
                    class="px-4 py-2 text-sm font-semibold rounded-xl border border-amber-400 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 transition-colors">
                🔄 Reschedule
            </button>
            @endif

            {{-- Cancel toggle (not terminal) --}}
            @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
            <button @click="showCancel = !showCancel; showReschedule = false"
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
                    <div>
                        <label class="form-label">Reassign staff (optional)</label>
                        <select name="staff_id" class="form-select">
                            <option value="">Keep current ({{ $appointment->staff?->name }})</option>
                            @foreach($staff as $s)
                            <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
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
        @if(! in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
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
            <a href="{{ route('appointments.index') }}" class="btn text-sm text-muted hover:text-body">← Back to list</a>
        </div>
    </div>

</div>

@endsection
