@props([
    'name' => 'phone',
    'id' => null,
    'value' => null,
    'required' => false,
    'errorName' => null,
    'alpineModel' => null,
    'alpineSync' => null,
    'alpineErrorField' => null,
    'detectTarget' => false,
    'simpleSelect' => false,
    'inputClass' => 'form-input flex-1 min-w-0',
    'selectTriggerClass' => 'form-select w-full',
    'selectWrapperClass' => 'w-[5.25rem] shrink-0 min-w-0 relative',
    'placeholder' => null,
    'nationalClass' => '',
    'fullClass' => '',
])

@php
    $id = $id ?: preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $name);
    $errorName = $errorName ?: $name;
    $countryName = preg_replace('/\[([^\]]+)\]$/', '[$1_country]', (string) $name);
    if ($countryName === $name) {
        $countryName = $name.'_country';
    }
    $split = \App\Support\PhoneCountry::split(old($name, $value));
    $phoneIso = old(str_replace(['[', ']'], ['.', ''], $countryName), $split['iso']);
    if (is_array($phoneIso) || ! is_string($phoneIso) || $phoneIso === '') {
        $phoneIso = $split['iso'];
    }
    $phoneIso = strtoupper($phoneIso);
    if (! isset(\App\Support\PhoneCountry::all()[$phoneIso])) {
        $phoneIso = \App\Support\PhoneCountry::DEFAULT_ISO;
    }
    $phoneNational = $split['national'];
    $meta = \App\Support\PhoneCountry::all()[$phoneIso];
    $hasError = $errors->has($errorName);
    $fullValue = $phoneNational !== '' ? \App\Support\PhoneCountry::toE164($phoneIso, $phoneNational) : '';
    $syncExpr = $alpineSync ?: $alpineModel;
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0']) }}
     data-phone-country-field
     @if($required) data-phone-required="1" @endif
     @if($detectTarget) data-phone-detect-target="1" @endif
     @if($syncExpr) x-effect="if (window.syncPhoneCountryField) window.syncPhoneCountryField($el, {{ $syncExpr }})" @endif>
    <div class="flex gap-2 min-w-0 items-stretch">
        @if($simpleSelect)
            <select id="{{ $id }}-country" name="{{ $countryName }}" data-phone-country
                    class="{{ $selectTriggerClass }} {{ $selectWrapperClass }}">
                @foreach(\App\Support\PhoneCountry::selectList() as $iso => $row)
                    <option value="{{ $iso }}" data-dial="{{ $row['dial'] }}" {{ $phoneIso === $iso ? 'selected' : '' }}>+{{ $row['dial'] }}</option>
                @endforeach
            </select>
        @else
            <x-searchable-select
                :id="$id.'-country'"
                :name="$countryName"
                wrapper-class="{{ $selectWrapperClass }}"
                :search-url="null"
                search-placeholder="Search code…"
                trigger-class="{{ $selectTriggerClass }}"
                data-phone-country>
                @foreach(\App\Support\PhoneCountry::selectList() as $iso => $row)
                    <option value="{{ $iso }}" data-dial="{{ $row['dial'] }}" data-short="+{{ $row['dial'] }}" data-search="{{ $iso }} {{ $row['name'] }}" {{ $phoneIso === $iso ? 'selected' : '' }}>+{{ $row['dial'] }} {{ $iso }}</option>
                @endforeach
            </x-searchable-select>
        @endif
        <input id="{{ $id }}-national" type="tel" inputmode="numeric" autocomplete="tel-national"
               data-phone-national
               value="{{ $phoneNational }}"
               maxlength="{{ $meta['max'] }}"
               @if($required) required @endif
               class="{{ $inputClass }} {{ $nationalClass }} {{ $hasError ? 'form-input-error' : '' }}"
               @if($alpineErrorField)
                   @input="clearDetailsError('{{ $alpineErrorField }}')"
                   :class="detailsFieldClass('{{ $alpineErrorField }}')"
               @endif
               placeholder="{{ $placeholder ?: ($meta['min'] === $meta['max'] ? $meta['max'].' digits' : $meta['min'].'–'.$meta['max'].' digits') }}">
        <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $fullValue }}" data-phone-full autocomplete="off"
               class="{{ $fullClass }}"
               @if($alpineModel) x-model="{{ $alpineModel }}" @endif>
    </div>
    <p data-phone-error class="form-error {{ $hasError ? '' : 'hidden' }}" role="alert">{{ $errors->first($errorName) }}</p>
</div>
