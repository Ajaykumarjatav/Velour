@extends('layouts.app')
@section('title', isset($service) ? 'Edit Service' : 'New Service')
@section('page-title', isset($service) ? 'Edit Service' : 'Add Service')
@section('content')

<div class="max-w-xl">
    <div class="card p-6">
        @php $action = isset($service) ? route('services.update', $service->id) : route('services.store'); @endphp
        <form action="{{ $action }}" method="POST" class="space-y-5">
            @csrf
            @if(isset($service)) @method('PUT') @endif

            <div>
                <label class="form-label">Service name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $service->name ?? '') }}" required class="form-input">
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="service_category_id" class="form-select">
                    <option value="">No category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('service_category_id', $service->service_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Duration (min) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" min="5" max="480" value="{{ old('duration_minutes', $service->duration_minutes ?? 60) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Price (£) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" min="0" step="0.01" value="{{ old('price', $service->price ?? '') }}" required class="form-input">
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea">{{ old('description', $service->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="form-label">Calendar colour</label>
                <input type="color" name="color" value="{{ old('color', $service->color ?? '#7C3AED') }}"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="online_booking" value="1" {{ old('online_booking', $service->online_booking ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Online booking</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">{{ isset($service) ? 'Save Changes' : 'Add Service' }}</button>
                <a href="{{ route('services.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
