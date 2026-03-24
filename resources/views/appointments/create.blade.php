@extends('layouts.app')
@section('title', 'New Appointment')
@section('page-title', 'New Appointment')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="form-label">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required class="form-select">
                    <option value="">Select a client…</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->first_name }} {{ $client->last_name }} {{ $client->phone ? '— '.$client->phone : '' }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Staff member <span class="text-red-500">*</span></label>
                <select name="staff_id" required class="form-select">
                    <option value="">Select staff…</option>
                    @foreach($staff as $s)
                    <option value="{{ $s->id }}" {{ old('staff_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date & Time <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Services <span class="text-red-500">*</span></label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-gray-800">
                    @foreach($services as $svc)
                    <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer">
                        <input type="checkbox" name="services[]" value="{{ $svc->id }}"
                               {{ in_array($svc->id, old('services', [])) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="flex-1 text-sm text-body">{{ $svc->name }}</span>
                        <span class="text-xs text-muted">{{ $svc->duration_minutes }}min</span>
                        <span class="text-sm font-semibold text-heading">£{{ number_format($svc->price, 2) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Client notes</label>
                    <textarea name="client_notes" rows="3" placeholder="Visible to client…" class="form-textarea">{{ old('client_notes') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Internal notes</label>
                    <textarea name="internal_notes" rows="3" placeholder="Staff only…" class="form-textarea">{{ old('internal_notes') }}</textarea>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Book Appointment</button>
                <a href="{{ route('appointments.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
