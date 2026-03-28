@extends('layouts.app')
@section('title', 'Edit Client')
@section('page-title', 'Edit Client')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">First name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}" required
                           class="form-input @error('first_name') form-input-error @enderror">
                    @error('first_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Last name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}" required
                           class="form-input @error('last_name') form-input-error @enderror">
                    @error('last_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="form-input @error('email') form-input-error @enderror">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="form-input @error('phone') form-input-error @enderror">
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-textarea @error('notes') form-input-error @enderror">{{ old('notes', $client->notes) }}</textarea>
                @error('notes')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_consent" value="1" {{ old('marketing_consent', $client->marketing_consent) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Marketing consent</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('clients.show', $client->id) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
