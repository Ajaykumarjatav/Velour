@php
    $options = \App\Support\LanguageProficiency::options();
    $selectedList = isset($selected) && is_array($selected) ? $selected : [];
    $selectedFlip = array_fill_keys($selectedList, true);
    $hint = $hint ?? 'Select all languages you can use with clients. Values are saved as standard codes (e.g. en, hi).';
@endphp
<div>
    <label class="form-label">Languages spoken with clients</label>
    <p class="form-hint mb-2">{{ $hint }}</p>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 p-3 sm:p-4 max-h-56 overflow-y-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($options as $code => $label)
                <label class="flex items-center gap-2.5 text-sm text-body cursor-pointer py-1 px-1.5 rounded-lg hover:bg-white/80 dark:hover:bg-gray-900/50 transition-colors">
                    <input type="checkbox"
                           name="{{ $name }}"
                           value="{{ $code }}"
                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600 shrink-0"
                           @checked(isset($selectedFlip[$code]))>
                    <span>{{ $label }} <span class="text-muted text-xs font-normal">({{ $code }})</span></span>
                </label>
            @endforeach
        </div>
    </div>
</div>
