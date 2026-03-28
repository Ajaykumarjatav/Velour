@extends('layouts.app')
@section('title', $campaign->name)
@section('page-title', 'Campaign Details')
@section('content')

<div class="max-w-2xl space-y-5">
    <div class="card p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h2 class="text-xl font-bold text-heading">{{ $campaign->name }}</h2>
                <p class="text-sm text-muted mt-0.5">Created {{ $campaign->created_at->format('d M Y') }}</p>
            </div>
            @php $sc = ['draft'=>'badge-gray','scheduled'=>'badge-blue','sending'=>'badge-yellow','sent'=>'badge-green']; @endphp
            <span class="{{ $sc[$campaign->status] ?? 'badge-gray' }} px-3 py-1.5 text-sm font-semibold rounded-xl">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
            <div>
                <p class="stat-label mb-1">Type</p>
                <p class="font-semibold text-heading uppercase">{{ $campaign->type }}</p>
            </div>
            <div>
                <p class="stat-label mb-1">Segment</p>
                <p class="font-semibold text-heading capitalize">{{ $campaign->segment ?? '—' }}</p>
            </div>
            @if($campaign->scheduled_at)
            <div>
                <p class="stat-label mb-1">Scheduled</p>
                <p class="font-semibold text-heading">{{ \Carbon\Carbon::parse($campaign->scheduled_at)->format('d M Y H:i') }}</p>
            </div>
            @endif
            @if($campaign->sent_at)
            <div>
                <p class="stat-label mb-1">Sent at</p>
                <p class="font-semibold text-heading">{{ $campaign->sent_at->format('d M Y H:i') }}</p>
            </div>
            @endif
        </div>
    </div>

    @if($campaign->subject)
    <div class="card p-6">
        <p class="stat-label mb-2">Subject</p>
        <p class="font-semibold text-heading">{{ $campaign->subject }}</p>
    </div>
    @endif

    <div class="card p-6">
        <p class="stat-label mb-3">Message</p>
        <pre class="text-sm text-body font-sans whitespace-pre-wrap">{{ $campaign->body }}</pre>
    </div>

    @if(in_array($campaign->status, ['draft','scheduled']))
    <div class="flex gap-3">
        <form action="{{ route('marketing.send', $campaign->id) }}" method="POST"
              onsubmit="return confirm('Send this campaign now to all eligible clients?')">
            @csrf
            <button type="submit" class="btn bg-green-600 hover:bg-green-700 text-white focus:ring-green-500">Send Now</button>
        </form>
        <form action="{{ route('marketing.destroy', $campaign->id) }}" method="POST"
              onsubmit="return confirm('Delete this campaign?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Delete</button>
        </form>
        <a href="{{ route('marketing.index') }}" class="btn-outline">Back</a>
    </div>
    @else
    <a href="{{ route('marketing.index') }}" class="btn-outline">Back to campaigns</a>
    @endif
</div>

@endsection
