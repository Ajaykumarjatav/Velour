@props([
    'label',
    'name',
    'required' => false,
    'type' => 'client',
    'selectId',
    'selectClass' => 'form-select',
    'errorName' => null,
])

@php
    $errorName = $errorName ?? $name;
@endphp

<div class="space-y-0">
    <div class="flex items-end gap-2">
        <div class="flex-1 min-w-0">
            <label class="form-label" for="{{ $selectId }}">
                {{ $label }}
                @if($required)<span class="text-red-500">*</span>@endif
            </label>
            <select id="{{ $selectId }}"
                    name="{{ $name }}"
                    @if($required) required @endif
                    {{ $attributes->merge(['class' => $selectClass . ($errors->has($errorName) ? ' form-input-error' : '')]) }}>
                {{ $slot }}
            </select>
            @error($errorName)
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>
        <x-relation-quick-create-trigger :type="$type" :select-id="$selectId" />
    </div>
</div>
