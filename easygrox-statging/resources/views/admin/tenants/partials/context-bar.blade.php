@php
    use App\Support\AdminTenantModuleRegistry;
    $modules = AdminTenantModuleRegistry::hubModules();
    $active = $activeModule ?? null;
@endphp
<div class="mb-5 bg-gray-900 border border-gray-800 rounded-2xl p-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="min-w-0">
            <a href="{{ route('admin.tenants.stores', $salon->owner_id) }}" class="text-lg font-bold text-white hover:text-velour-300 truncate block">{{ $salon->name }}</a>
            <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $salon->slug }}.easygrox.com</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if($salon->slug)
            <a href="{{ url('/book/'.$salon->slug) }}" target="_blank" rel="noopener"
               class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800">
                Booking page
            </a>
            @endif
            <a href="{{ route('admin.tenants.stores', $salon->owner_id) }}"
               class="text-xs px-3 py-1.5 rounded-lg bg-velour-600/20 text-velour-300 border border-velour-800/50">
                All stores
            </a>
            <form method="POST" action="{{ route('admin.tenants.stores.enter', $salon->id) }}">
                @csrf
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800">
                    Open panel
                </button>
            </form>
        </div>
    </div>
    <div class="mt-4 flex flex-wrap gap-1.5">
        @foreach($modules as $key => $meta)
            @php
                $routeName = 'admin.tenants.'.$meta['route'];
                $isActive = $active === $key || ($active === null && request()->routeIs($routeName));
            @endphp
            @if(Route::has($routeName))
            <a href="{{ route($routeName, $salon->id) }}"
               class="px-2.5 py-1 rounded-lg text-[11px] font-medium transition-colors
                      {{ $isActive ? 'bg-velour-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                {{ $meta['label'] }}
            </a>
            @endif
        @endforeach
    </div>
</div>
