@extends('layouts.app')
@section('title', 'Service packages')
@section('page-title', 'Service packages')
@section('content')

<div class="space-y-8 pb-8">
    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl border border-velour-200/60 dark:border-velour-800/80 bg-gradient-to-br from-velour-50 via-white to-violet-50/90 dark:from-gray-900 dark:via-gray-900/95 dark:to-velour-950/40 px-6 py-8 sm:px-10 sm:py-10 shadow-sm dark:shadow-none">
        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-velour-400/10 dark:bg-velour-500/10 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -left-8 bottom-0 h-32 w-32 rounded-full bg-violet-400/10 dark:bg-violet-500/10 blur-2xl pointer-events-none" aria-hidden="true"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/80 dark:bg-gray-800/80 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-velour-700 dark:text-velour-300 ring-1 ring-velour-200/80 dark:ring-velour-700/50">
                    <svg class="w-3.5 h-3.5 text-velour-600 dark:text-velour-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Bundles &amp; promotions
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-heading tracking-tight">Service packages</h2>
                <p class="text-sm sm:text-base text-body leading-relaxed">
                    Combine services into one bundle with its own price. Service dependency follows each staff member’s role—configure allowed roles per package on edit. Perfect for fixed menus, specials, and clearer booking.
                </p>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 shrink-0 lg:pb-0.5">
                @can('create', \App\Models\ServicePackage::class)
                    <a href="{{ route('service-packages.create') }}"
                       class="group inline-flex items-center justify-center gap-2 rounded-xl bg-velour-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-velour-600/25 transition hover:bg-velour-700 hover:shadow-xl hover:shadow-velour-600/30 focus:outline-none focus:ring-2 focus:ring-velour-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                        <svg class="w-5 h-5 transition group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create package
                    </a>
                @endcan
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-gray-200 bg-white/90 px-5 py-3 text-sm font-semibold text-heading transition hover:border-velour-300 hover:bg-white dark:border-gray-600 dark:bg-gray-800/90 dark:hover:border-velour-600 dark:hover:bg-gray-800">
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    Browse services
                </a>
            </div>
        </div>
    </div>

    @if($packages->isEmpty())
        <div class="relative overflow-hidden rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30 px-8 py-14 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-velour-100 dark:bg-velour-900/40 text-velour-600 dark:text-velour-400">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-heading mb-2">No packages yet</h3>
            <p class="text-muted text-sm max-w-md mx-auto mb-8 leading-relaxed">
                Create your first package to bundle two or more services, set a bundle price, and choose which staff roles may perform it—all from the next screen.
            </p>
            @can('create', \App\Models\ServicePackage::class)
                <a href="{{ route('service-packages.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-velour-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-velour-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create your first package
                </a>
            @else
                <p class="text-sm text-muted max-w-sm mx-auto">Only the business owner or a manager can create packages.</p>
            @endcan
        </div>
    @else
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                <p class="text-sm text-muted">
                    <span class="font-medium text-heading">{{ $packages->count() }}</span> {{ Str::plural('package', $packages->count()) }}
                    · Open <span class="text-velour-600 dark:text-velour-400 font-medium">Edit</span> to add or reorder services and set role rules.
                </p>
                @can('create', \App\Models\ServicePackage::class)
                    <a href="{{ route('service-packages.create') }}" class="hidden sm:inline-flex text-sm font-semibold text-velour-600 dark:text-velour-400 hover:underline">
                        + Another package
                    </a>
                @endcan
            </div>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
                @foreach($packages as $pkg)
                    @php
                        $hasSavings = $pkg->services_sum_price !== null && (float) $pkg->services_sum_price > (float) $pkg->price;
                        $savingsPct = $hasSavings && (float) $pkg->services_sum_price > 0
                            ? round(100 - ((float) $pkg->price / (float) $pkg->services_sum_price) * 100)
                            : null;
                    @endphp
                    <article class="group relative flex flex-col rounded-2xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-900/60 p-6 shadow-sm transition duration-300 hover:border-velour-200 dark:hover:border-velour-700 hover:shadow-lg hover:shadow-velour-500/5 dark:hover:shadow-black/20 hover:-translate-y-0.5">
                        <div class="absolute inset-y-0 left-0 w-1 rounded-l-2xl bg-gradient-to-b from-velour-500 to-violet-600 opacity-90" aria-hidden="true"></div>
                        <div class="pl-5 flex flex-col gap-4 flex-1 min-h-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3 min-w-0">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-velour-500/15 to-violet-500/15 text-velour-600 dark:text-velour-400 ring-1 ring-velour-200/50 dark:ring-velour-800/50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112-2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-heading text-lg leading-tight truncate">{{ $pkg->name }}</h3>
                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs font-medium text-body">
                                                {{ $pkg->services_count }} {{ Str::plural('service', $pkg->services_count) }}
                                            </span>
                                            @if($pkg->status !== 'active')
                                                <span class="inline-flex items-center rounded-lg bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 text-xs font-medium text-amber-800 dark:text-amber-200">Inactive</span>
                                            @else
                                                <span class="inline-flex items-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:text-emerald-200">Active</span>
                                            @endif
                                            @if(!$pkg->online_bookable)
                                                <span class="inline-flex items-center rounded-lg bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-xs text-muted">Not online</span>
                                            @endif
                                            @if($hasSavings && $savingsPct !== null && $savingsPct > 0)
                                                <span class="inline-flex items-center rounded-lg bg-velour-100 dark:bg-velour-900/50 px-2 py-0.5 text-xs font-semibold text-velour-800 dark:text-velour-200">Save ~{{ $savingsPct }}%</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xl font-bold tabular-nums text-velour-600 dark:text-velour-400">
                                        {{ \App\Helpers\CurrencyHelper::format((float) $pkg->price, $currentSalon->currency ?? 'GBP') }}
                                    </p>
                                    @if($hasSavings)
                                        <p class="text-xs text-muted line-through mt-0.5 tabular-nums">
                                            {{ \App\Helpers\CurrencyHelper::format((float) $pkg->services_sum_price, $currentSalon->currency ?? 'GBP') }} if booked separately
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($pkg->description)
                                <p class="text-sm text-body leading-relaxed line-clamp-2 pl-0 sm:pl-14">{{ $pkg->description }}</p>
                            @endif

                            @if($pkg->relationLoaded('services') && $pkg->services->isNotEmpty())
                                <div class="pl-0 sm:pl-14">
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-muted mb-2">Included</p>
                                    <ul class="flex flex-wrap gap-1.5">
                                        @foreach($pkg->services as $line)
                                            <li class="inline-flex items-center rounded-lg border border-gray-200/90 dark:border-gray-600/80 bg-gray-50/80 dark:bg-gray-800/50 px-2.5 py-1 text-xs font-medium text-body">
                                                {{ $line->name }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-auto pt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 dark:border-gray-800">
                                @can('update', $pkg)
                                    <a href="{{ route('service-packages.edit', $pkg) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-velour-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-velour-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        Edit package &amp; services
                                    </a>
                                @endcan
                                @can('delete', $pkg)
                                    <form action="{{ route('service-packages.destroy', $pkg) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Remove this package? Your services stay in the catalog.');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center rounded-xl border border-red-200 dark:border-red-900/60 bg-transparent px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-950/40">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection
