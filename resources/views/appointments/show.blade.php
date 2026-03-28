@extends('layouts.app')
@section('title', 'Appointment #'.$appointment->reference)
@section('page-title', 'Appointment Details')
@section('content')

<div class="max-w-2xl space-y-5">

    <div class="card p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-heading">
                    {{ $appointment->client?->first_name }} {{ $appointment->client?->last_name }}
                </h2>
                <p class="text-sm text-muted mt-0.5">{{ $appointment->reference }}</p>
            </div>
            @php
                $colors = ['confirmed'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red','no_show'=>'badge-yellow'];
            @endphp
            <span class="{{ $colors[$appointment->status] ?? 'badge-gray' }} px-3 py-1.5 text-sm font-semibold rounded-xl">
                {{ ucfirst(str_replace('_',' ',$appointment->status)) }}
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
                <p class="font-semibold text-heading">{{ ucfirst(str_replace('_',' ',$appointment->source ?? 'walk_in')) }}</p>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Services</h3>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($appointment->services as $svc)
            <div class="flex items-center justify-between px-6 py-3.5">
                <div>
                    <p class="font-medium text-heading">{{ $svc->service?->name }}</p>
                    <p class="text-xs text-muted">{{ $svc->duration_minutes }} min</p>
                </div>
                <p class="font-semibold text-heading">@money($svc->price)</p>
            </div>
            @endforeach
        </div>
    </div>

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

    <div class="card p-6">
        <h3 class="font-semibold text-heading mb-4">Update Status</h3>
        <form action="{{ route('appointments.status', $appointment->id) }}" method="POST" class="flex flex-wrap gap-2">
            @csrf @method('PATCH')
            @foreach(['confirmed','completed','cancelled','no_show'] as $s)
            <button type="submit" name="status" value="{{ $s }}"
                    class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                    {{ $appointment->status === $s
                        ? 'bg-velour-600 text-white border-velour-600'
                        : 'border-gray-300 dark:border-gray-700 text-body hover:border-velour-400 hover:text-velour-600 dark:hover:text-velour-400 bg-white dark:bg-gray-800' }}">
                {{ ucfirst(str_replace('_',' ',$s)) }}
            </button>
            @endforeach
        </form>
        <div class="flex gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('appointments.edit', $appointment->id) }}" class="btn-outline">Edit Appointment</a>
            <a href="{{ route('appointments.index') }}" class="btn text-muted hover:text-body">← Back to list</a>
        </div>
    </div>

</div>

@endsection
