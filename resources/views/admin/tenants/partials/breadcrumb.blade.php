<nav class="text-xs text-gray-500 flex flex-wrap items-center gap-1.5">
    <a href="{{ route('admin.tenants') }}" class="hover:text-gray-300">Tenants</a>
    <span>/</span>
    <a href="{{ route('admin.tenants.stores', $salon->owner_id) }}" class="hover:text-gray-300">{{ $salon->name }}</a>
    @foreach($items ?? [] as $item)
        <span>/</span>
        @if(!empty($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-gray-300">{{ $item['label'] }}</a>
        @else
            <span class="text-gray-300">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
