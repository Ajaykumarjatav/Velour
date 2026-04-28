@extends('layouts.app')
@section('title', 'Reviews')
@section('page-title', 'Reviews')
@section('content')

<div class="card p-6 mb-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-heading">Shareable Review Links</h2>
        <span class="text-xs text-muted">Permanent links — always active unless manually disabled</span>
    </div>

    <div class="space-y-3">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm font-semibold text-heading mb-2">Tenant Review Link</p>
            <div class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    readonly
                    value="{{ route('reviews.public', $tenantReviewLink->token) }}"
                    class="form-input flex-1"
                    id="tenant-review-link"
                >
                <button type="button" class="btn-outline whitespace-nowrap copy-review-link-btn" data-target="tenant-review-link">Copy Link</button>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-sm font-semibold text-heading mb-2">Staff Review Links</p>
            <div class="space-y-2">
                @forelse($staffReviewLinks as $row)
                @php
                    $staff = $row['staff'];
                    $link = $row['link'];
                    $inputId = 'staff-review-link-' . $staff->id;
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-[180px_1fr_auto] gap-2 items-center">
                    <p class="text-sm text-body">{{ $staff->name }}</p>
                    <input
                        type="text"
                        readonly
                        value="{{ route('reviews.public', $link->token) }}"
                        class="form-input"
                        id="{{ $inputId }}"
                    >
                    <button type="button" class="btn-outline copy-review-link-btn whitespace-nowrap" data-target="{{ $inputId }}">Copy Link</button>
                </div>
                @empty
                <p class="text-sm text-muted">No active staff members found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card p-6 mb-6 flex flex-col sm:flex-row items-center gap-6">
    <div class="text-center flex-shrink-0">
        <p class="text-5xl font-black text-heading">{{ number_format($averageRating, 1) }}</p>
        <div class="flex gap-0.5 justify-center mt-1">
            @for($i = 1; $i <= 5; $i++)
            <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            @endfor
        </div>
        <p class="text-xs text-muted mt-1">{{ $reviews->total() }} reviews</p>
    </div>
    <div class="flex-1 w-full space-y-1.5">
        @foreach([5,4,3,2,1] as $star)
        @php $count = $ratingCounts[$star] ?? 0; $pct = $reviews->total() > 0 ? ($count / $reviews->total()) * 100 : 0; @endphp
        <div class="flex items-center gap-2 text-sm">
            <span class="w-3 text-muted text-xs text-right">{{ $star }}</span>
            <svg class="w-3.5 h-3.5 text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
            <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                <div class="bg-amber-400 h-2 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <span class="w-6 text-xs text-muted">{{ $count }}</span>
        </div>
        @endforeach
    </div>
</div>

<div class="flex gap-2 mb-5 flex-wrap">
    <form action="{{ route('reviews.index') }}" method="GET" class="flex gap-2">
        <select name="rating" onchange="this.form.submit()" class="form-select w-auto">
            <option value="">All ratings</option>
            @foreach([5,4,3,2,1] as $r)
            <option value="{{ $r }}" {{ $rating == $r ? 'selected' : '' }}>{{ $r }} stars</option>
            @endforeach
        </select>
        <select name="replied" onchange="this.form.submit()" class="form-select w-auto">
            <option value="">All</option>
            <option value="0" {{ $replied === '0' ? 'selected' : '' }}>Awaiting reply</option>
            <option value="1" {{ $replied === '1' ? 'selected' : '' }}>Replied</option>
        </select>
    </form>
</div>

<div class="space-y-4">
    @forelse($reviews as $review)
    <div class="card p-5" x-data="{ replying: false }">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <span class="font-semibold text-heading">
                        {{ $review->client ? $review->client->first_name.' '.$review->client->last_name : ($review->reviewer_name ?: 'Anonymous') }}
                    </span>
                    <span class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        @endfor
                    </span>
                    <span class="text-xs text-muted">{{ $review->created_at->diffForHumans() }}</span>
                </div>
                @if($review->comment)
                <p class="text-sm text-body">{{ $review->comment }}</p>
                @endif
                @if($review->service)
                <p class="text-xs text-muted mt-1">Service: {{ $review->service->name }}</p>
                @endif

                @if($review->owner_reply)
                <div class="mt-3 ml-4 pl-4 border-l-2 border-velour-300 dark:border-velour-700">
                    <p class="text-xs font-semibold text-velour-600 dark:text-velour-400 mb-1">Your reply</p>
                    <p class="text-sm text-body">{{ $review->owner_reply }}</p>
                </div>
                @else
                <button @click="replying=!replying" class="mt-3 text-xs text-link font-medium">Reply</button>
                <div x-show="replying" x-cloak class="mt-3">
                    <form action="{{ route('reviews.reply', $review->id) }}" method="POST">
                        @csrf
                        <textarea name="reply" rows="3" required placeholder="Write a reply…" class="form-textarea"></textarea>
                        <div class="flex gap-2 mt-2">
                            <button type="submit" class="btn-primary btn-sm">Post Reply</button>
                            <button type="button" @click="replying=false" class="btn-outline btn-sm">Cancel</button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="card">
        <div class="empty-state">
            <p class="empty-state-title">No reviews yet</p>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-5">{{ $reviews->links() }}</div>

<script>
document.addEventListener('click', async function (e) {
    var btn = e.target.closest('.copy-review-link-btn');
    if (!btn) return;
    var inputId = btn.getAttribute('data-target');
    var input = document.getElementById(inputId);
    if (!input) return;
    try {
        await navigator.clipboard.writeText(input.value);
        var old = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(function () { btn.textContent = old; }, 1500);
    } catch (err) {
        input.select();
        document.execCommand('copy');
    }
});
</script>

@endsection
