@extends('layouts.app')
@section('title', 'Edit Appointment')
@section('page-title', 'Edit Appointment')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('appointments.update', $appointment->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Client</label>
                <select name="client_id" required class="form-select">
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $appointment->client_id == $client->id ? 'selected' : '' }}>
                        {{ $client->first_name }} {{ $client->last_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Staff member</label>
                <select name="staff_id" required class="form-select">
                    @foreach($staff as $s)
                    <option value="{{ $s->id }}" {{ $appointment->staff_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date & Time</label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at', $appointment->starts_at->format('Y-m-d\TH:i')) }}" required class="form-input">
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

@endsection
