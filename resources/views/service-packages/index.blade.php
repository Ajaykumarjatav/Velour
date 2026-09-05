@extends('layouts.app')
@section('title', 'Plans/Packages')
@section('page-title', 'Plans/Packages')
@section('content')

@php
    $section = $section ?? 'packages';
    $search = $search ?? '';
    $statusFilter = $statusFilter ?? '';
    $priceMin = $priceMin ?? null;
    $priceMax = $priceMax ?? null;
    $totalPackages = $totalPackages ?? $packages->count();
    $activePackageCount = $packages->where('status', 'active')->count();
    $currency = $salon->currency ?? \App\Helpers\CurrencyHelper::defaultCode();
    $hasFilters = $search !== '' || $statusFilter !== '' || $priceMin !== null || $priceMax !== null;
@endphp

@push('styles')
<style>
.pkg-card {
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    border-radius: 0.95rem;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #fff;
    padding: 1.15rem 1.15rem 0.95rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px -16px rgba(15, 23, 42, 0.18);
    overflow: hidden;
    transition: border-color 0.18s, box-shadow 0.18s;
}
.dark .pkg-card {
    border-color: rgba(55, 65, 81, 0.95);
    background: rgba(24, 24, 37, 0.92);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2), 0 12px 28px -18px rgba(0, 0, 0, 0.55);
}
.pkg-card::before {
    content: '';
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, rgba(124, 58, 237, 0.75), rgba(168, 85, 247, 0.35) 45%, transparent 85%);
    opacity: 0.7;
}
.pkg-card:hover {
    border-color: rgba(167, 139, 250, 0.45);
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.08), 0 12px 28px -16px rgba(124, 58, 237, 0.28);
}
.dark .pkg-card:hover {
    border-color: rgba(139, 92, 246, 0.38);
    box-shadow: 0 0 0 1px rgba(139, 92, 246, 0.12), 0 14px 30px -16px rgba(91, 33, 182, 0.35);
}
.pkg-card.is-inactive { opacity: 0.78; }
.pkg-card.is-inactive::before {
    background: linear-gradient(90deg, rgba(148, 163, 184, 0.7), transparent 80%);
    opacity: 0.6;
}
.pkg-card-meta {
    margin-top: 0.45rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem 0.45rem;
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.35;
}
.dark .pkg-card-meta { color: #94a3b8; }
.pkg-card-meta .dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 9999px;
    background: #94a3b8;
    flex-shrink: 0;
}
.pkg-card-meta .dot.is-active { background: #10b981; }
.pkg-card-meta .sep { opacity: 0.45; }
.pkg-card-meta .price {
    margin-left: auto;
    font-size: 1.05rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    color: #7c3aed;
    line-height: 1;
}
.dark .pkg-card-meta .price { color: #c4b5fd; }
.pkg-service-check {
    width: 0.9rem;
    height: 0.9rem;
    margin-top: 0.1rem;
    flex-shrink: 0;
    color: #7c3aed;
}
.dark .pkg-service-check { color: #a78bfa; }
.pkg-menu {
    position: absolute;
    right: 0;
    bottom: calc(100% + 0.4rem);
    min-width: 10.5rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(226, 232, 240, 0.95);
    background: #fff;
    box-shadow: 0 12px 28px -12px rgba(15, 23, 42, 0.35);
    padding: 0.35rem;
    z-index: 20;
}
.dark .pkg-menu {
    border-color: rgba(55, 65, 81, 0.95);
    background: #111827;
    box-shadow: 0 12px 28px -10px rgba(0, 0, 0, 0.55);
}
.pkg-menu button,
.pkg-menu a {
    display: flex;
    width: 100%;
    align-items: center;
    padding: 0.5rem 0.65rem;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 500;
    color: #334155;
    text-align: left;
    background: transparent;
    border: 0;
    cursor: pointer;
}
.dark .pkg-menu button,
.dark .pkg-menu a { color: #e2e8f0; }
.pkg-menu button:hover,
.pkg-menu a:hover { background: rgba(248, 250, 252, 1); }
.dark .pkg-menu button:hover,
.dark .pkg-menu a:hover { background: rgba(31, 41, 55, 0.9); }
.pkg-menu .is-danger { color: #dc2626; }
.dark .pkg-menu .is-danger { color: #f87171; }
.pkg-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(226, 232, 240, 0.95);
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: inherit;
    transition: background 0.15s, border-color 0.15s;
}
.dark .pkg-chip { border-color: rgba(75, 85, 99, 0.95); }
.pkg-chip:hover { background: rgba(249, 250, 251, 0.95); }
.dark .pkg-chip:hover { background: rgba(31, 41, 55, 0.8); }
.pkg-chip.is-active {
    border-color: rgba(124, 58, 237, 0.45);
    background: rgba(124, 58, 237, 0.12);
    color: #5b21b6;
}
.dark .pkg-chip.is-active {
    border-color: rgba(139, 92, 246, 0.45);
    background: rgba(91, 33, 182, 0.22);
    color: #ede9fe;
}
</style>
@endpush

<div class="space-y-6 pb-8" x-data="{ tierModal: null, openTier(m) { this.tierModal = m }, openMenu: null }" x-on:keydown.escape.window="tierModal=null; openMenu=null">

    <p class="text-sm text-muted">
        @if($section === 'loyalty')
            <span class="font-medium text-heading">{{ $loyaltyTiers->count() }} loyalty {{ Str::plural('plan', $loyaltyTiers->count()) }}</span>
            <span class="text-muted"> — membership tiers assigned to clients.</span>
        @else
            <span class="font-medium text-heading">{{ number_format($totalPackages) }} {{ Str::plural('package', $totalPackages) }}</span>
            <span class="text-muted"> — service bundles for booking, POS, and offers.</span>
        @endif
    </p>

    {{-- Toolbar (matches Services page pattern) --}}
    <div class="rounded-2xl border border-gray-200/90 dark:border-gray-700/80 bg-white/85 dark:bg-gray-900/45 backdrop-blur-sm p-4 sm:p-5 shadow-sm dark:shadow-none space-y-4">
        @if($section === 'packages')
            <form method="GET" action="{{ route('service-packages.index') }}">
                <div class="flex flex-wrap xl:flex-nowrap items-end gap-3 xl:overflow-x-auto pb-1 -mx-0.5 px-0.5">
                    <div class="flex-[2] min-w-[11rem] space-y-1.5 shrink-0">
                        <label for="pkg-search" class="form-label text-xs mb-0 uppercase tracking-wide text-muted">Search</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none z-[1]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input id="pkg-search" type="search" name="search" value="{{ $search }}" placeholder="Name or description…"
                                   class="form-input w-full min-h-[2.5rem] !pl-10 pr-3 text-sm">
                        </div>
                    </div>
                    <div class="w-[8.5rem] min-w-[8.5rem] space-y-1.5 shrink-0">
                        <label for="pkg-status" class="form-label text-xs mb-0 uppercase tracking-wide text-muted">Status</label>
                        <select id="pkg-status" name="status" class="form-select w-full min-h-[2.5rem] text-sm">
                            <option value="" @selected($statusFilter === '')>All</option>
                            <option value="active" @selected($statusFilter === 'active')>Active</option>
                            <option value="inactive" @selected($statusFilter === 'inactive')>Inactive</option>
                        </select>
                    </div>
                    <div class="w-[6.5rem] min-w-[6.5rem] space-y-1.5 shrink-0">
                        <label class="form-label text-xs mb-0 uppercase tracking-wide text-muted whitespace-nowrap">Price min</label>
                        <input type="number" name="price_min" value="{{ $priceMin !== null ? $priceMin : '' }}" step="0.01" min="0" placeholder="0"
                               class="form-input w-full min-h-[2.5rem] text-sm tabular-nums">
                    </div>
                    <div class="w-[6.5rem] min-w-[6.5rem] space-y-1.5 shrink-0">
                        <label class="form-label text-xs mb-0 uppercase tracking-wide text-muted whitespace-nowrap">Price max</label>
                        <input type="number" name="price_max" value="{{ $priceMax !== null ? $priceMax : '' }}" step="0.01" min="0" placeholder="∞"
                               class="form-input w-full min-h-[2.5rem] text-sm tabular-nums">
                    </div>
                    <div class="flex items-center gap-2 shrink-0 pb-0.5">
                        <button type="submit" class="btn-primary min-h-[2.5rem] px-5 text-sm whitespace-nowrap">Apply filters</button>
                        @if($hasFilters)
                            <a href="{{ route('service-packages.index') }}" class="btn-outline min-h-[2.5rem] px-4 text-sm whitespace-nowrap">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        @else
            <p class="text-sm text-body leading-relaxed">
                Loyalty plans appear in the client profile dropdown. Create tiers with monthly pricing, discounts, and benefits.
            </p>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('service-packages.index') }}"
                   class="pkg-chip {{ $section === 'packages' ? 'is-active' : 'text-body' }}">
                    <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Packages
                </a>
                <a href="{{ route('service-packages.index', ['section' => 'loyalty']) }}"
                   class="pkg-chip {{ $section === 'loyalty' ? 'is-active' : 'text-body' }}">
                    <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    Loyalty plans
                </a>
                <a href="{{ route('services.index') }}" class="pkg-chip text-body">
                    <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                    Services
                </a>
            </div>

            @if($section === 'packages')
                @can('create', \App\Models\ServicePackage::class)
                    <a href="{{ route('service-packages.create') }}"
                       class="btn-primary inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl shadow-md shadow-velour-600/20 dark:shadow-velour-900/25 active:scale-[0.97] transition-transform duration-150 shrink-0">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Create package</span>
                    </a>
                @endcan
            @else
                <button type="button"
                        class="btn-primary inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl shadow-md shadow-velour-600/20 dark:shadow-velour-900/25 active:scale-[0.97] transition-transform duration-150 shrink-0"
                        x-on:click="openTier({ id: null, name: '', price_monthly: '', service_discount_percent: 0, benefits: '' })">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add loyalty plan</span>
                </button>
            @endif
        </div>
    </div>

    @if($section === 'loyalty')
        @include('service-packages.partials.loyalty-plans')
    @else
        @if($packages->isEmpty())
            <div class="relative overflow-hidden rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30 px-8 py-14 text-center">
                <h3 class="text-lg font-semibold text-heading mb-2">
                    {{ $hasFilters ? 'No packages match your filters' : 'No packages yet' }}
                </h3>
                <p class="text-muted text-sm max-w-md mx-auto mb-8 leading-relaxed">
                    @if($hasFilters)
                        Try adjusting search, status, or price range.
                    @else
                        Create your first package to bundle two or more services with a bundle price.
                    @endif
                </p>
                @if($hasFilters)
                    <a href="{{ route('service-packages.index') }}" class="btn-outline inline-flex rounded-xl">Reset filters</a>
                @else
                    @can('create', \App\Models\ServicePackage::class)
                        <a href="{{ route('service-packages.create') }}" class="btn-primary inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create package
                        </a>
                    @endcan
                @endif
            </div>
        @else
            <p class="text-sm text-muted">
                <span class="font-medium text-heading">{{ $activePackageCount }} active</span>
                <span>· {{ $packages->count() }} showing</span>
                @if($hasFilters)
                    <span class="text-muted"> (filtered)</span>
                @endif
            </p>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 items-stretch">
                @foreach($packages as $pkg)
                    @php
                        $isActive = $pkg->status === 'active';
                        $hasSavings = $pkg->services_sum_price !== null && (float) $pkg->services_sum_price > (float) $pkg->price;
                        $savingsPct = $hasSavings && (float) $pkg->services_sum_price > 0
                            ? round(100 - ((float) $pkg->price / (float) $pkg->services_sum_price) * 100)
                            : null;
                    @endphp
                    <article class="pkg-card {{ $isActive ? '' : 'is-inactive' }}">
                        <div class="min-w-0">
                            <h3 class="font-bold text-heading text-[1.02rem] leading-snug truncate" title="{{ $pkg->name }}">{{ $pkg->name }}</h3>
                            <div class="pkg-card-meta">
                                <span class="dot {{ $isActive ? 'is-active' : '' }}" aria-hidden="true"></span>
                                <span>{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                <span class="sep" aria-hidden="true">·</span>
                                <span>{{ $pkg->services_count }} {{ Str::plural('Service', $pkg->services_count) }}</span>
                                @if($savingsPct)
                                    <span class="sep" aria-hidden="true">·</span>
                                    <span>Save {{ $savingsPct }}%</span>
                                @endif
                                <span class="price">{{ \App\Helpers\CurrencyHelper::format((float) $pkg->price, $currency) }}</span>
                            </div>
                        </div>

                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex-1 flex flex-col min-h-0">
                            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.08em] text-muted mb-2">Included services</p>
                            @if($pkg->services->isNotEmpty())
                                <ul class="space-y-1.5 flex-1">
                                    @foreach($pkg->services as $service)
                                        <li class="flex items-start gap-2 text-[0.8125rem] text-body leading-snug">
                                            <svg class="pkg-service-check" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="min-w-0">{{ $service->name }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-muted flex-1">No services linked.</p>
                            @endif
                        </div>

                        <div class="mt-3.5 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                            <p class="text-[0.6875rem] text-muted truncate">
                                Updated {{ $pkg->updated_at?->diffForHumans() ?? '—' }}
                            </p>
                            <div class="flex items-center gap-1.5 shrink-0">
                                @can('update', $pkg)
                                    <a href="{{ route('service-packages.edit', $pkg) }}" class="btn-primary btn-sm">Edit package</a>
                                @endcan
                                @canany(['update', 'delete'], $pkg)
                                    <div class="relative">
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-700 text-muted hover:text-heading hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                                            @click="openMenu = openMenu === {{ $pkg->id }} ? null : {{ $pkg->id }}"
                                            :aria-expanded="openMenu === {{ $pkg->id }}"
                                            aria-label="More actions"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                            </svg>
                                        </button>
                                        <div
                                            class="pkg-menu"
                                            x-show="openMenu === {{ $pkg->id }}"
                                            x-cloak
                                            @click.outside="openMenu = null"
                                        >
                                            @can('update', $pkg)
                                                <a href="{{ route('service-packages.edit', $pkg) }}">Edit</a>
                                                <form action="{{ route('service-packages.toggle-status', $pkg) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit">{{ $isActive ? 'Deactivate' : 'Activate' }}</button>
                                                </form>
                                            @endcan
                                            @can('delete', $pkg)
                                                <form action="{{ route('service-packages.destroy', $pkg) }}" method="POST" onsubmit="return confirm('Remove this package?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="is-danger">Delete</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                @endcanany
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endif
</div>

@endsection
