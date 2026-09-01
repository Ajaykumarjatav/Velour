@php
    /** @var array<string, mixed> $group */
    $layout = $group['layout'] ?? 'default';
@endphp
<section class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-950/20 p-4 sm:p-5">
  <h3 class="text-sm font-semibold text-heading mb-3">{{ $group['label'] }}</h3>

  @if($layout === 'tabs_row')
    <div class="overflow-x-auto -mx-1 px-1 pb-1">
      <div class="flex gap-2 min-w-max items-stretch">
        @if(! empty($group['general']))
        <div class="shrink-0 w-[8.5rem] rounded-lg border border-gray-200/80 dark:border-gray-700/80 bg-white/70 dark:bg-gray-900/50 p-2.5">
          <p class="text-xs font-semibold text-heading text-center mb-2 leading-tight">General</p>
          <div class="flex flex-col gap-1.5">
            @foreach($group['general'] as $perm)
              @include('admin.tenant.partials.permission-chip', ['perm' => $perm, 'compact' => true])
            @endforeach
          </div>
        </div>
        @endif
        @foreach($group['tabs'] ?? [] as $tab)
        <div class="shrink-0 w-[7.25rem] rounded-lg border border-gray-200/80 dark:border-gray-700/80 bg-white/70 dark:bg-gray-900/50 p-2.5">
          <p class="text-xs font-semibold text-heading text-center mb-2 leading-tight min-h-[2rem] flex items-center justify-center">{{ $tab['label'] }}</p>
          <div class="flex flex-col gap-1.5">
            @foreach($tab['permissions'] as $perm)
              @include('admin.tenant.partials.permission-chip', ['perm' => $perm, 'compact' => true])
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
  @else
    <div class="flex flex-wrap gap-2">
      @foreach($group['permissions'] ?? [] as $perm)
        @include('admin.tenant.partials.permission-chip', ['perm' => $perm])
      @endforeach
    </div>
  @endif
</section>
