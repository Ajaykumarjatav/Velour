@extends('layouts.app')
@section('title', 'Edit Staff Member')
@section('page-title', 'Edit Staff Member')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('staff.update', $staff->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Full name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}"
                           class="form-input @error('email') form-input-error @enderror">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $staff->phone) }}"
                           class="form-input @error('phone') form-input-error @enderror">
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select name="role" required class="form-select @error('role') form-input-error @enderror">
                        @foreach(['stylist','therapist','manager','receptionist','junior','owner'] as $r)
                        <option value="{{ $r }}" {{ old('role', $staff->role) === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Commission %</label>
                    <input type="number" name="commission_rate" min="0" max="100" step="0.1"
                           value="{{ old('commission_rate', $staff->commission_rate) }}"
                           class="form-input @error('commission_rate') form-input-error @enderror">
                    @error('commission_rate')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Calendar colour</label>
                    <input type="color" name="color" value="{{ old('color', $staff->color ?? '#7C3AED') }}"
                           class="w-full h-10 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" rows="3" class="form-textarea @error('bio') form-input-error @enderror">{{ old('bio', $staff->bio) }}</textarea>
                    @error('bio')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($services->count())
            <div>
                <label class="form-label">Services offered</label>
                <div class="grid grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 max-h-40 overflow-y-auto bg-white dark:bg-gray-800">
                    @foreach($services as $svc)
                    <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20">
                        <input type="checkbox" name="services[]" value="{{ $svc->id }}"
                               {{ in_array($svc->id, old('services', $assigned)) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="text-sm text-body truncate">{{ $svc->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active (shows in calendar and booking)</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('staff.show', $staff->id) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
