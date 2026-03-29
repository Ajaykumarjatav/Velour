@extends('layouts.app')
@section('title', 'Marketing')
@section('page-title', 'Marketing Campaigns')
@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach(['total' => ['label'=>'Total','cls'=>'text-heading'], 'sent' => ['label'=>'Sent','cls'=>'text-green-600 dark:text-green-400'], 'scheduled' => ['label'=>'Scheduled','cls'=>'text-blue-600 dark:text-blue-400'], 'draft' => ['label'=>'Drafts','cls'=>'text-muted']] as $key => $cfg)
    <div class="stat-card text-center">
        <p class="text-2xl font-bold {{ $cfg['cls'] }}">{{ $stats[$key] }}</p>
        <p class="stat-label mt-1">{{ $cfg['label'] }}</p>
    </div>
    @endforeach
</div>

<div class="flex flex-col sm:flex-row gap-4 mb-6 items-center justify-between">
    <form action="{{ route('marketing.index') }}" method="GET" class="flex flex-wrap items-center gap-3 min-w-0">
        <select name="status" onchange="this.form.submit()" class="form-select w-full sm:w-auto min-w-[180px]">
            <option value="">All statuses</option>
            @foreach(['draft','scheduled','sending','sent'] as $s)
            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('marketing.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">+ New Campaign</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Campaign</th>
            <th class="hidden sm:table-cell">Type</th>
            <th class="hidden md:table-cell">Segment</th>
            <th>Status</th>
            <th class="hidden lg:table-cell">Created</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($campaigns as $campaign)
        <tr>
            <td>
                <p class="font-semibold text-heading">{{ $campaign->name }}</p>
                @if($campaign->subject)<p class="text-xs text-muted truncate max-w-[200px]">{{ $campaign->subject }}</p>@endif
            </td>
            <td class="hidden sm:table-cell">
                <span class="badge-gray uppercase">{{ $campaign->type }}</span>
            </td>
            <td class="hidden md:table-cell text-muted capitalize">{{ $campaign->segment ?? '—' }}</td>
            <td>
                @php $sc = ['draft'=>'badge-gray','scheduled'=>'badge-blue','sending'=>'badge-yellow','sent'=>'badge-green']; @endphp
                <span class="{{ $sc[$campaign->status] ?? 'badge-gray' }}">{{ ucfirst($campaign->status) }}</span>
            </td>
            <td class="hidden lg:table-cell text-muted text-xs">{{ $campaign->created_at->format('d M Y') }}</td>
            <td>
                <div class="flex justify-end gap-2">
                    <a href="{{ route('marketing.show', $campaign->id) }}" class="text-xs text-link font-medium">View</a>
                    @if(in_array($campaign->status, ['draft','scheduled']))
                    <form action="{{ route('marketing.send', $campaign->id) }}" method="POST" onsubmit="return confirm('Send this campaign now?')">
                        @csrf
                        <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:text-green-700 font-medium">Send</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-muted">No campaigns yet</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $campaigns->links() }}</div>

@endsection
