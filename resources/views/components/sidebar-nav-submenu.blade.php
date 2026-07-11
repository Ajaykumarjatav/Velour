@props([
    'label',
    'icon',
    'name',
    'open' => false,
    'active' => false,
])

@php
    $name = $name ?? \Illuminate\Support\Str::slug($label);
@endphp

<div class="sidebar-nav-group"
     @if($open) x-init="openMenu = @js($name)" @endif
     @click.outside="if ($root.sidebarCollapsed && openMenu === @js($name)) openMenu = null">
    <button type="button"
            @click.stop="openMenu = openMenu === @js($name) ? null : @js($name)"
            :class="openMenu === @js($name) && $root.sidebarCollapsed ? 'sidebar-flyout-trigger-open' : ''"
            class="sidebar-link w-full {{ $active ? 'active' : '' }}">
        @include('partials.sidebar-nav-icon', ['icon' => $icon])
        <span class="flex-1 text-left">{{ $label }}</span>
        <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
             :class="openMenu === @js($name) ? 'rotate-180' : ''"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="openMenu === @js($name)"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="sidebar-submenu-panel ml-4 mt-0.5 space-y-0.5">
        <p class="sidebar-submenu-flyout-title">{{ $label }}</p>
        <div class="sidebar-submenu-flyout-items space-y-0.5">
            {{ $slot }}
        </div>
    </div>
</div>
