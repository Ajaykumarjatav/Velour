@php
    $options = \App\Support\LanguageProficiency::options();
    $selectedList = isset($selected) && is_array($selected) ? $selected : [];
    $selectedFlip = array_fill_keys($selectedList, true);
    $hint = $hint ?? 'Select all languages you can use with clients. Values are saved as standard codes (e.g. en, hi).';
@endphp
<div>
    <label class="form-label">Languages spoken with clients</label>
    <p class="form-hint mb-2">{{ $hint }}</p>
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 p-3 sm:p-4 max-h-64 sm:max-h-56 overflow-y-auto overscroll-contain">
        <div class="grid grid-cols-1 min-[480px]:grid-cols-2 xl:grid-cols-3 gap-1.5 sm:gap-2">
            @foreach($options as $code => $label)
                <label class="flex items-start gap-2.5 text-sm text-body cursor-pointer py-1.5 px-1.5 rounded-lg hover:bg-white/80 dark:hover:bg-gray-900/50 transition-colors min-w-0">
                    <input type="checkbox"
                           name="{{ $name }}"
                           value="{{ $code }}"
                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600 shrink-0 mt-0.5"
                           @checked(isset($selectedFlip[$code]))>
                    <span class="min-w-0 break-words leading-snug">{{ $label }} <span class="text-muted text-xs font-normal">({{ $code }})</span></span>
                </label>
            @endforeach
        </div>
    </div>
</div>
