@extends('layouts.app')
@section('title', 'New service package')
@section('page-title', 'New service package')
@section('content')

<div class="max-w-4xl">
    <div class="card p-6">
        <p class="text-sm text-muted mb-6">
            Build a bundle from your service catalog: add two or more services below, set the package price, then save.
        </p>

        @if($services->count() < 2)
            <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800 px-4 py-3 text-sm text-amber-900 dark:text-amber-100 mb-6">
                You need at least two services (active or inactive, not archived) before you can create a package.
                <a href="{{ route('services.create') }}" class="font-medium underline">Create a service</a>
                or activate existing ones under <a href="{{ route('services.index') }}" class="font-medium underline">Services</a>.
            </div>
        @endif

        <form action="{{ route('service-packages.store') }}" method="POST" class="space-y-6">
            @csrf

            @include('service-packages.partials.package-service-picker', [
                'servicesPayload' => $servicesPayload,
                'initialSelectedIds' => $initialSelectedIds,
            ])

            <div>
                <label class="form-label">Package name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                       class="form-input @error('name') form-input-error @enderror" placeholder="e.g. Cut &amp; Colour bundle">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea @error('description') form-input-error @enderror" placeholder="What clients get in this bundle…">{{ old('description') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Package price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                           class="form-input @error('price') form-input-error @enderror">
                    @error('price')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Allowed staff roles</label>
                <p class="form-hint mb-2">Optional. If none are selected, any role may be assigned when this package is used in booking.</p>
                @php
                    $allowedRoles = old('allowed_roles', []);
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
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="online_bookable" value="1" {{ old('online_bookable', true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Eligible for online booking (when integrated)</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" @if($services->count() < 2) disabled @endif>Create package</button>
                <a href="{{ route('service-packages.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
