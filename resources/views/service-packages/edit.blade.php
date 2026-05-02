@extends('layouts.app')
@section('title', 'Edit service package')
@section('page-title', 'Edit service package')
@section('content')

<div class="max-w-4xl">
    <div class="card p-6">
        <form action="{{ route('service-packages.update', $servicePackage) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            @include('service-packages.partials.package-service-picker', [
                'servicesPayload' => $servicesPayload,
                'initialSelectedIds' => $initialSelectedIds,
            ])

            <div>
                <label class="form-label">Package name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $servicePackage->name) }}" required maxlength="150"
                       class="form-input @error('name') form-input-error @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea @error('description') form-input-error @enderror">{{ old('description', $servicePackage->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Package price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', $servicePackage->price) }}" required min="0" step="0.01"
                       class="form-input @error('price') form-input-error @enderror">
                @error('price')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Allowed staff roles</label>
                <p class="form-hint mb-2">Optional. Leave all unchecked to allow any role (where booking supports packages).</p>
                @php
                    $allowedRoles = old('allowed_roles', $servicePackage->allowed_roles ?? []);
                    $roleOptions = \App\Models\Service::supportedStaffRoles();
                @endphp
                <div class="grid grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-gray-800">
                    @foreach($roleOptions as $roleOption)
                        <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20">
                            <input type="checkbox" name="allowed_roles[]" value="{{ $roleOption }}"
                                   {{ in_array($roleOption, $allowedRoles, true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span class="text-sm text-body">{{ ucfirst($roleOption) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('allowed_roles')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $servicePackage->status === 'active') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="online_bookable" value="1" {{ old('online_bookable', $servicePackage->online_bookable) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Eligible for online booking (when integrated)</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" @if($services->count() < 2) disabled @endif>Save changes</button>
                <a href="{{ route('service-packages.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
