@php
    $permKey = $permKey ?? ($perm['key'] ?? '');
    $permLabel = $permLabel ?? ($perm['label'] ?? '');
    $compact = $compact ?? false;
@endphp
<button type="button"
        @click="togglePermission(@js($permKey))"
        :disabled="!canEdit || pending === @js($permKey)"
        :title="canEdit ? (hasPermission(@js($permKey)) ? 'Revoke {{ $permLabel }}' : 'Grant {{ $permLabel }}') : @js($permLabel)"
        class="inline-flex items-center gap-1.5 text-xs font-medium rounded-lg border transition-all duration-150 disabled:opacity-60 disabled:cursor-not-allowed {{ $compact ? 'w-full justify-center px-2 py-1.5' : 'px-3 py-1.5' }}"
        :class="hasPermission(@js($permKey))
          ? 'bg-velour-600 border-velour-600 text-white shadow-sm'
          : 'border-gray-200 dark:border-gray-700 text-muted bg-white/60 dark:bg-gray-900/40 hover:border-gray-400 dark:hover:border-gray-500 hover:text-heading'">
  <span class="flex h-4 w-4 items-center justify-center rounded-full shrink-0"
        :class="hasPermission(@js($permKey)) ? 'bg-white/20' : 'bg-gray-100 dark:bg-gray-800'"
        aria-hidden="true">
    <span x-show="pending === @js($permKey)" x-cloak>
      <svg class="h-3 w-3 animate-spin" :class="hasPermission(@js($permKey)) ? 'text-white' : 'text-velour-600'" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
    </span>
    <span x-show="pending !== @js($permKey) && hasPermission(@js($permKey))" x-cloak>
      <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </span>
  </span>
  <span>{{ $permLabel }}</span>
</button>
