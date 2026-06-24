@extends('layouts.app')
@section('title', 'Store Expenses')
@section('page-title', 'Store Expenses')

@section('content')
@php
    use App\Support\ExpenseCategoryUi;
    $d = $dashboardData;
    $filterQuery = request()->except('page');
    $avgExpense = (float) ($d['insights']['average_expense'] ?? 0);
    $quickPeriods = [
        'today' => 'Today',
        'week' => 'This Week',
        'month' => 'This Month',
        'last_month' => 'Last Month',
        '90d' => 'Last 90 Days',
    ];
    $pieGradientParts = [];
    $pieOffset = 0;
    foreach ($d['category_breakdown'] as $row) {
        $color = ExpenseCategoryUi::meta($row['slug'] ?? null)['chart'];
        $end = $pieOffset + $row['pct'];
        $pieGradientParts[] = $color . ' ' . $pieOffset . '% ' . $end . '%';
        $pieOffset = $end;
    }
    $pieGradient = implode(', ', $pieGradientParts);
    $trendPoints = $d['trend_points'];
    $trendLabels = count($trendPoints) > 0
        ? array_values(array_filter([
            $trendPoints[0] ?? null,
            $trendPoints[(int) floor(count($trendPoints) / 2)] ?? null,
            $trendPoints[count($trendPoints) - 1] ?? null,
        ]))
        : [];
@endphp

<div class="space-y-5 pb-20 print:pb-0"
     x-data="{ exportOpen: false, rangeOpen: false }"
     @click.outside="exportOpen = false; rangeOpen = false">

    <div class="sticky top-14 z-30 -mx-4 sm:-mx-6 px-4 sm:px-6 py-2.5
                bg-[#F7F5F2]/90 dark:bg-[#0f0f0f]/90 backdrop-blur-md
                border-b border-gray-200/70 dark:border-gray-800/70 print:static print:border-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm text-muted hidden sm:block">Track salaries, inventory, rent &amp; store costs</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0 print:hidden">
                <div class="relative">
                    <button type="button" @click="exportOpen = !exportOpen" class="btn-outline btn-sm inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export
                    </button>
                    <div x-show="exportOpen" x-cloak x-transition
                         class="absolute right-0 mt-1 w-44 py-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg z-50 text-sm">
                        <a href="{{ route('expenses.export', array_merge($filterQuery, ['format' => 'csv'])) }}"
                           class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">CSV</a>
                        <a href="{{ route('expenses.export', array_merge($filterQuery, ['format' => 'excel'])) }}"
                           class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Excel</a>
                        <button type="button" onclick="window.print()" class="w-full text-left px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">PDF / Print</button>
                    </div>
                </div>
                <x-unless-admin-browse>
                <a href="{{ route('expenses.create') }}" class="btn-primary btn-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add expense
                </a>
                </x-unless-admin-browse>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        <div class="card p-4 col-span-2 lg:col-span-1 xl:col-span-1">
            <p class="text-[11px] text-muted font-semibold uppercase tracking-wide">Total expenses</p>
            <p class="text-2xl font-bold text-heading mt-1">@money($d['month_total'])</p>
            <p class="text-[11px] text-muted mt-1">This month · {{ $d['month_count'] }} entries</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] text-muted font-semibold uppercase tracking-wide">Top category</p>
            <p class="text-lg font-bold text-heading mt-1 truncate">{{ $d['top_category_name'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] text-muted font-semibold uppercase tracking-wide">Avg daily</p>
            <p class="text-2xl font-bold text-heading mt-1">@money($d['avg_daily'])</p>
        </div>
        <div class="card p-4">
            <p class="text-[11px] text-muted font-semibold uppercase tracking-wide">Pending cheques</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">@money($d['pending_total'])</p>
        </div>
        <div class="card p-4 col-span-2">
            <p class="text-[11px] text-muted font-semibold uppercase tracking-wide">{{ $d['month_label'] }} vs {{ $d['prev_month_label'] }}</p>
            <p class="text-lg font-bold {{ $d['trend_up'] ? 'text-rose-500' : 'text-emerald-500' }} mt-1">
                {{ $d['trend_up'] ? '▲' : '▼' }} {{ abs($d['trend_pct']) }}%
            </p>
        </div>
    </div>

    <div class="card p-4 space-y-3 print:hidden">
        <div class="flex flex-wrap gap-2">
            @foreach($quickPeriods as $key => $label)
                <a href="{{ route('expenses.index', array_merge(request()->except(['page', 'from', 'to', 'period']), ['period' => $key])) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors
                          {{ ($activePeriod ?? null) === $key
                              ? 'bg-velour-600 text-white border-velour-600'
                              : 'border-gray-200 dark:border-gray-700 text-muted hover:text-body' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <form method="GET" action="{{ route('expenses.index') }}" class="flex flex-col lg:flex-row gap-3">
            <input type="search" name="search" value="{{ request('search') }}"
                   placeholder="Search title, vendor, reference…" class="form-input text-sm flex-1">
            <select name="category_id" class="form-select text-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary btn-sm">Apply</button>
            <a href="{{ route('expenses.index') }}" class="btn-outline btn-sm">Reset</a>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/95 text-[11px] uppercase tracking-wide text-muted">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Vendor</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        @unless($adminStoreBrowse ?? false)
                        <th class="px-4 py-3 text-right w-28">Actions</th>
                        @endunless
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($expenses as $expense)
                        @php
                            $meta = ExpenseCategoryUi::meta($expense->category?->slug, $expense->category?->name);
                        @endphp
                        <tr class="hover:bg-velour-50/40 dark:hover:bg-velour-950/20">
                            <td class="px-4 py-3 text-muted whitespace-nowrap">{{ $expense->expense_date->format('j M Y') }}</td>
                            <td class="px-4 py-3 font-medium text-heading">{{ $expense->title }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $meta['badge'] }}">
                                    {{ $meta['icon'] }} {{ $expense->category?->name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $expense->vendor ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-bold">@money($expense->amount)</td>
                            @unless($adminStoreBrowse ?? false)
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('expenses.edit', $expense) }}" class="text-velour-600 text-xs font-semibold">Edit</a>
                            </td>
                            @endunless
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($adminStoreBrowse ?? false) ? 5 : 6 }}" class="px-4 py-16 text-center">
                                <h3 class="text-lg font-semibold text-heading">No expenses found</h3>
                                @unless($adminStoreBrowse ?? false)
                                <a href="{{ route('expenses.create') }}" class="btn-primary mt-4 inline-flex">Add expense</a>
                                @endunless
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($expenses->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800">{{ $expenses->links() }}</div>
        @endif
    </div>

    @unless($adminStoreBrowse ?? false)
    <a href="{{ route('expenses.create') }}"
       class="fixed bottom-6 right-6 z-40 w-14 h-14 rounded-full bg-velour-600 hover:bg-velour-700 text-white shadow-lg flex items-center justify-center print:hidden"
       title="Add expense">
        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    </a>
    @endunless
</div>
@endsection
