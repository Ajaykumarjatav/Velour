@extends('layouts.app')
@section('title', 'Marketing Report')
@section('page-title', 'Marketing Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ $campaigns_sent }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Campaigns sent</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ number_format($total_sent) }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Messages sent</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-velour-600 dark:text-velour-400">{{ $avg_open_rate }}%</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Avg open rate</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-velour-600 dark:text-velour-400">{{ $avg_click_rate }}%</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Avg click rate</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $bookings }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Bookings</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">@money($revenue)</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Attributed revenue</p>
    </div>
</div>

<div class="table-wrap">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
        <h3 class="font-semibold text-heading">Campaigns in period</h3>
        <a href="{{ route('marketing.index') }}" class="text-xs text-link font-medium">Marketing hub →</a>
    </div>
    <table class="data-table">
        <thead>
        <tr>
            <th>Campaign</th>
            <th class="text-right">Sent</th>
            <th class="text-right hidden sm:table-cell">Open</th>
            <th class="text-right hidden md:table-cell">Click</th>
            <th class="text-right">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @forelse($campaigns as $campaign)
        <tr>
            <td>
                <a href="{{ route('marketing.show', $campaign) }}" class="font-semibold text-link hover:underline">
                    {{ $campaign->name }}
                </a>
                <p class="text-xs text-muted mt-0.5">{{ $campaign->sent_at?->format('j M Y') }}</p>
            </td>
            <td class="text-right text-muted">{{ number_format($campaign->sent_count) }}</td>
            <td class="text-right text-muted hidden sm:table-cell">{{ $campaign->open_rate }}%</td>
            <td class="text-right text-muted hidden md:table-cell">{{ $campaign->click_rate }}%</td>
            <td class="text-right font-bold text-heading">@money($campaign->revenue_generated)</td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-muted">No sent campaigns in this period</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
