@extends('layouts.app')
@section('title', 'Website Traffic')
@section('page-title', 'Traffic')

@php
    $periodLinks = [
        '7d' => '7 days',
        '1m' => '30 days',
        '3m' => '3 months',
        '12m' => '12 months',
    ];
    $deltaClass = function (?float $delta): string {
        if ($delta === null) {
            return 'text-muted';
        }
        return $delta >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400';
    };
    $deltaText = function (?float $delta): string {
        if ($delta === null) {
            return 'vs previous';
        }
        $sign = $delta >= 0 ? '+' : '';
        return $sign.$delta.'% vs previous';
    };
    $trend = collect($trendRows ?? []);
    $maxTrend = max(1, (int) $trend->flatMap(fn ($r) => [(int) $r['humans'], (int) $r['bots']])->max());
    $humanDeg = min(360, (int) round(($humanShare / 100) * 360));
    $clickRows = $clickRows ?? collect();
    $clickTotal = (int) ($clickTotal ?? 0);
@endphp

@section('content')
<div class="space-y-5">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-velour-600 dark:text-velour-300">Analytics</p>
            <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-heading mt-0.5">Website traffic</h1>
            <p class="text-sm text-muted mt-1">Visitors, sources, and button taps on {{ $salon->name }} — no Google Analytics.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
                @foreach($periodLinks as $key => $label)
                    <a href="{{ route('reports.traffic', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $period === $key ? 'bg-velour-600 text-white' : 'text-muted hover:text-body' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="btn-outline btn-sm">Open website</a>
        </div>
    </div>

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Visitors</p>
            <p class="text-3xl font-semibold text-heading tabular-nums mt-1 leading-none">{{ number_format($human) }}</p>
            <p class="text-xs text-muted mt-2">{{ $humanShare }}% of {{ number_format($total) }} visits</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Button clicks</p>
            <p class="text-3xl font-semibold text-heading tabular-nums mt-1 leading-none">{{ number_format($clickTotal) }}</p>
            <p class="text-xs text-muted mt-2">Book, call, WhatsApp</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Direct</p>
            <p class="text-3xl font-semibold text-heading tabular-nums mt-1 leading-none">{{ number_format($direct) }}</p>
            <p class="text-xs text-muted mt-2">{{ $directShare }}% · {{ number_format($humanDirect) }} human</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Bots</p>
            <p class="text-3xl font-semibold text-heading tabular-nums mt-1 leading-none">{{ number_format($bot) }}</p>
            <p class="text-xs text-muted mt-2">{{ $botShare }}% filtered out</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
        <div class="xl:col-span-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <div class="flex items-start justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-base font-semibold text-heading">Human vs bot</h2>
                    <p class="text-xs text-muted mt-0.5">Who can actually book</p>
                </div>
                <p class="text-xs text-muted tabular-nums {{ $deltaClass($humanDelta) }}">{{ $deltaText($humanDelta) }}</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative w-28 h-28 sm:w-32 sm:h-32 shrink-0">
                    <div class="absolute inset-0 rounded-full"
                         style="background: conic-gradient(#34d399 0deg {{ $humanDeg }}deg, #f59e0b {{ $humanDeg }}deg 360deg);"></div>
                    <div class="absolute inset-4 rounded-full bg-white dark:bg-gray-900 flex flex-col items-center justify-center">
                        <p class="text-xl font-semibold tabular-nums text-heading leading-none">{{ $total > 0 ? $humanShare : 0 }}%</p>
                        <p class="text-[10px] text-muted mt-1">human</p>
                    </div>
                </div>
                <div class="flex-1 min-w-0 space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 text-heading"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Humans</span>
                            <span class="font-semibold tabular-nums">{{ number_format($human) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full bg-emerald-400" style="width: {{ $humanShare }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-2 text-heading"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Bots</span>
                            <span class="font-semibold tabular-nums">{{ number_format($bot) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full bg-amber-400" style="width: {{ $botShare }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted">From a source</span>
                            <span class="font-semibold tabular-nums text-heading">{{ number_format($fromSource) }}</span>
                        </div>
                        <p class="text-[11px] text-muted mt-1">{{ number_format($humanFromSource) }} human via Google, Instagram, WhatsApp…</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="xl:col-span-7 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-base font-semibold text-heading">Visits over time</h2>
                <div class="flex items-center gap-3 text-[11px] text-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Humans</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Bots</span>
                </div>
            </div>
            @if($trend->sum(fn ($r) => $r['humans'] + $r['bots']) > 0)
                <div class="h-40 flex items-end gap-[3px] overflow-x-auto pb-1">
                    @foreach($trend as $row)
                        @php
                            $h = (int) $row['humans'];
                            $b = (int) $row['bots'];
                            $hPct = $h > 0 ? max(10, (int) round(($h / $maxTrend) * 100)) : 0;
                            $bPct = $b > 0 ? max(8, (int) round(($b / $maxTrend) * 100)) : 0;
                        @endphp
                        <div class="group relative h-full flex flex-col justify-end items-center w-2.5 sm:w-3 shrink-0">
                            <div class="w-full flex flex-col justify-end gap-px" style="height: 100%">
                                @if($h > 0)
                                    <div class="w-full bg-emerald-400 rounded-t-sm" style="height: {{ $hPct }}%"></div>
                                @endif
                                @if($b > 0)
                                    <div class="w-full bg-amber-400 {{ $h > 0 ? '' : 'rounded-t-sm' }}" style="height: {{ $bPct }}%"></div>
                                @endif
                                @if($h === 0 && $b === 0)
                                    <div class="w-full h-1 rounded-sm bg-gray-200 dark:bg-gray-700"></div>
                                @endif
                            </div>
                            <div class="absolute -top-9 left-1/2 -translate-x-1/2 hidden group-hover:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-[11px] shadow z-10 whitespace-nowrap">
                                {{ $row['label'] }} · {{ $h }} human · {{ $b }} bot
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between mt-2">
                    <span class="text-[10px] text-muted">{{ $trend->first()['label'] ?? '' }}</span>
                    <span class="text-[10px] text-muted">{{ $trend->last()['label'] ?? '' }}</span>
                </div>
            @else
                <div class="h-40 flex items-center justify-center rounded-xl border border-dashed border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-muted text-center px-4">No visits in this period yet. Share your website link to start seeing a trend.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <h2 class="text-base font-semibold text-heading mb-4">Where they came from</h2>
            @if($sourceRows->count() > 0)
                <div class="space-y-3.5">
                    @foreach($sourceRows as $row)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-heading">{{ $row['label'] }}</span>
                                <span class="text-muted tabular-nums text-xs">{{ number_format($row['visits']) }} · {{ $row['share'] }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden flex">
                                <div class="h-2 bg-emerald-400" style="width: {{ max(0, (int) round(($row['humans'] / max(1, $total)) * 100)) }}%"></div>
                                <div class="h-2 bg-amber-400" style="width: {{ max(0, (int) round(($row['bots'] / max(1, $total)) * 100)) }}%"></div>
                            </div>
                            <p class="text-[11px] text-muted mt-1">{{ number_format($row['humans']) }} human · {{ number_format($row['bots']) }} bot</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 text-center">
                    <p class="text-sm text-muted">No source data yet.</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-5">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-base font-semibold text-heading">Button clicks</h2>
                    <p class="text-xs text-muted mt-0.5">Taps on Book, Call, WhatsApp, and more</p>
                </div>
                <p class="text-sm font-semibold tabular-nums text-heading">{{ number_format($clickTotal) }}</p>
            </div>
            @if($clickRows->count() > 0)
                <div class="space-y-3.5">
                    @foreach($clickRows as $row)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1.5">
                                <span class="font-medium text-heading">{{ $row['label'] }}</span>
                                <span class="text-muted tabular-nums text-xs">{{ number_format($row['clicks']) }} · {{ $row['share'] }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-2 bg-cyan-500 rounded-full" style="width: {{ max(0, min(100, (int) round($row['share']))) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 px-4 py-8 text-center">
                    <p class="text-sm text-heading font-medium">No taps yet</p>
                    <p class="text-xs text-muted mt-1 max-w-xs mx-auto">Clicks appear when someone uses Book, Call, or WhatsApp on your website.</p>
                </div>
            @endif
        </div>
    </div>

    <p class="text-[11px] text-muted leading-relaxed">
        Direct means no known referring site. Bots are scanners and search engines. Same visitor within 30 seconds is counted once.
        @if($totalDelta !== null)
            <span class="{{ $deltaClass($totalDelta) }}"> Total visits {{ $deltaText($totalDelta) }}.</span>
        @endif
    </p>
</div>
@endsection
