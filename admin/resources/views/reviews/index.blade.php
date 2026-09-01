@extends('layouts.app')
@section('title', 'Reviews')
@section('page-title', 'Reviews')

@push('styles')
<style>
.reviews-page { max-width: 1400px; margin: 0 auto; padding: 0 1rem 1.5rem; }
@media (min-width: 640px) { .reviews-page { padding-left: 2rem; padding-right: 2rem; } }
.reviews-section-title { font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgb(107 114 128); }
.dark .reviews-section-title { color: rgb(156 163 175); }
.reviews-tenant-card {
    border-radius: 1rem;
    border: 1px solid rgba(168, 26, 70, 0.25);
    background: linear-gradient(135deg, rgba(168, 26, 70, 0.06) 0%, rgba(168, 26, 70, 0.02) 100%);
    padding: 1.25rem 1.5rem;
}
.dark .reviews-tenant-card {
    border-color: rgba(168, 26, 70, 0.35);
    background: linear-gradient(135deg, rgba(168, 26, 70, 0.12) 0%, rgba(17, 24, 39, 0.4) 100%);
}
.reviews-link-path { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.8125rem; color: rgb(75 85 99); word-break: break-all; }
.dark .reviews-link-path { color: rgb(156 163 175); }
.reviews-copy-btn {
    display: inline-flex; align-items: center; gap: 0.375rem;
    padding: 0.4375rem 0.75rem; border-radius: 0.5rem;
    font-size: 0.8125rem; font-weight: 500;
    border: 1px solid rgb(209 213 219); background: white; color: rgb(55 65 81);
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.reviews-copy-btn:hover { background: rgb(249 250 251); border-color: rgb(168 26 70 / 0.4); color: rgb(168 26 70); }
.dark .reviews-copy-btn { background: rgb(31 41 55); border-color: rgb(55 65 81); color: rgb(209 213 219); }
.dark .reviews-copy-btn:hover { background: rgb(55 65 81); border-color: rgb(168 26 70 / 0.5); color: rgb(251 191 36); }
.reviews-copy-btn.is-copied { border-color: rgb(16 185 129); color: rgb(16 185 129); }
.reviews-staff-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.reviews-staff-table th {
    text-align: left; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.06em;
    text-transform: uppercase; color: rgb(107 114 128); padding: 0.625rem 1rem;
    border-bottom: 1px solid rgb(229 231 235);
}
.dark .reviews-staff-table th { color: rgb(156 163 175); border-bottom-color: rgb(55 65 81); }
.reviews-staff-table td {
    padding: 0.875rem 1rem; border-bottom: 1px solid rgb(243 244 246); vertical-align: middle;
}
.dark .reviews-staff-table td { border-bottom-color: rgb(31 41 55); }
.reviews-staff-table tr:last-child td { border-bottom: 0; }
.reviews-staff-table tbody tr:hover td { background: rgb(249 250 251 / 0.8); }
.dark .reviews-staff-table tbody tr:hover td { background: rgb(31 41 55 / 0.5); }
.reviews-rating-bar { height: 6px; border-radius: 9999px; background: rgb(243 244 246); overflow: hidden; }
.dark .reviews-rating-bar { background: rgb(31 41 55); }
.reviews-rating-bar-fill { height: 100%; border-radius: 9999px; background: rgb(251 191 36); transition: width 0.3s ease; }
.reviews-avatar {
    width: 2.5rem; height: 2.5rem; border-radius: 0.75rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.02em;
    background: linear-gradient(135deg, rgb(168 26 70 / 0.15), rgb(168 26 70 / 0.08));
    color: rgb(168 26 70); flex-shrink: 0;
}
.dark .reviews-avatar { background: linear-gradient(135deg, rgb(168 26 70 / 0.35), rgb(168 26 70 / 0.15)); color: rgb(251 191 36); }
.reviews-reply-block {
    margin-top: 1rem; padding: 0.875rem 1rem 0.875rem 1rem;
    border-left: 3px solid rgb(168 26 70); border-radius: 0 0.5rem 0.5rem 0;
    background: rgb(168 26 70 / 0.04);
}
.dark .reviews-reply-block { background: rgb(168 26 70 / 0.1); border-left-color: rgb(168 26 70); }
.reviews-status-pill {
    display: inline-flex; align-items: center; gap: 0.375rem;
    font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase;
    padding: 0.25rem 0.625rem; border-radius: 9999px;
    background: rgb(16 185 129 / 0.12); color: rgb(5 150 105);
}
.dark .reviews-status-pill { background: rgb(16 185 129 / 0.18); color: rgb(52 211 153); }
.reviews-status-pill::before {
    content: ''; width: 6px; height: 6px; border-radius: 9999px; background: currentColor;
}
.reviews-staff-mobile-card { border-radius: 0.75rem; border: 1px solid rgb(229 231 235); padding: 1rem; }
.dark .reviews-staff-mobile-card { border-color: rgb(55 65 81); }
</style>
@endpush

@section('content')
@php
    $storeKey = \App\Support\SalonUrl::key($salon);
    $totalReviews = $reviews->total();

    $shortReviewPath = static function (string $token): string {
        $token = trim($token);
        if ($token === '') {
            return 'reviews/share/…';
        }
        $visible = strlen($token) > 14 ? substr($token, 0, 14).'…' : $token;

        return 'reviews/share/'.$visible;
    };

    $reviewerDisplayName = static function ($review): string {
        if ($review->client) {
            return trim($review->client->first_name.' '.$review->client->last_name);
        }

        return trim((string) ($review->reviewer_name ?: 'Anonymous'));
    };

    $reviewerInitials = static function (string $name): string {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : '?';
    };
@endphp

<div class="reviews-page space-y-6">
    {{-- Page header --}}
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-muted leading-relaxed">Manage customer feedback and shared review links</p>
        </div>
        <p class="text-xs text-muted shrink-0">{{ $totalReviews }} {{ Str::plural('review', $totalReviews) }} total</p>
    </div>

    {{-- Shareable Review Links --}}
    <div class="card rounded-2xl p-5 sm:p-6">
        <div class="mb-5">
            <h2 class="text-base font-semibold text-heading">Shareable Review Links</h2>
            <p class="text-xs text-muted mt-1">Permanent links — always active unless manually disabled</p>
        </div>

        <div class="space-y-5">
            @if(!($isScopedStaff ?? false) && $tenantReviewLink)
            @php
                $tenantUrl = route('reviews.public', ['store' => $storeKey, 'token' => $tenantReviewLink->token]);
            @endphp
            <div class="reviews-tenant-card">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="reviews-section-title flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Tenant Review Link
                            </span>
                            <span class="reviews-status-pill">Active</span>
                        </div>
                        <p class="reviews-link-path">{{ $shortReviewPath($tenantReviewLink->token) }}</p>
                        <p class="text-xs text-muted mt-1">Permanent link for your business</p>
                    </div>
                    <button type="button"
                            class="reviews-copy-btn shrink-0 self-start sm:self-center copy-review-link-btn"
                            data-copy-url="{{ $tenantUrl }}"
                            title="Copy link">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span class="copy-label">Copy link</span>
                    </button>
                </div>
            </div>
            @endif

            {{-- Staff Review Links --}}
            <div>
                <p class="reviews-section-title mb-3">{{ ($isScopedStaff ?? false) ? 'Your Review Link' : 'Staff Review Links' }}</p>

                @if($staffReviewLinks->isEmpty())
                <p class="text-sm text-muted py-2">No active staff members found.</p>
                @else
                {{-- Desktop table --}}
                <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="reviews-staff-table">
                        <thead>
                            <tr>
                                <th style="width:28%">Staff</th>
                                <th style="width:52%">Review Link</th>
                                <th style="width:20%; text-align:right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staffReviewLinks as $row)
                            @php
                                $staff = $row['staff'];
                                $link = $row['link'];
                                $staffUrl = route('reviews.public', ['store' => $storeKey, 'token' => $link->token]);
                            @endphp
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <div class="reviews-avatar text-xs" style="width:2rem;height:2rem;border-radius:0.5rem;font-size:0.6875rem;">
                                            {{ $reviewerInitials($staff->name) }}
                                        </div>
                                        <span class="text-sm font-medium text-heading">{{ $staff->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="reviews-link-path">{{ $shortReviewPath($link->token) }}</span>
                                </td>
                                <td style="text-align:right">
                                    <button type="button"
                                            class="reviews-copy-btn copy-review-link-btn"
                                            data-copy-url="{{ $staffUrl }}"
                                            title="Copy link">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span class="copy-label">Copy</span>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="md:hidden space-y-2 mt-2">
                    @foreach($staffReviewLinks as $row)
                    @php
                        $staff = $row['staff'];
                        $link = $row['link'];
                        $staffUrl = route('reviews.public', ['store' => $storeKey, 'token' => $link->token]);
                    @endphp
                    <div class="reviews-staff-mobile-card flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2.5 mb-1">
                                <div class="reviews-avatar text-xs" style="width:2rem;height:2rem;border-radius:0.5rem;font-size:0.6875rem;">
                                    {{ $reviewerInitials($staff->name) }}
                                </div>
                                <span class="text-sm font-medium text-heading truncate">{{ $staff->name }}</span>
                            </div>
                            <p class="reviews-link-path pl-10">Public review link</p>
                            <p class="reviews-link-path pl-10 mt-0.5">{{ $shortReviewPath($link->token) }}</p>
                        </div>
                        <button type="button"
                                class="reviews-copy-btn shrink-0 copy-review-link-btn"
                                data-copy-url="{{ $staffUrl }}"
                                title="Copy link">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span class="copy-label">Copy</span>
                        </button>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Rating Overview --}}
    <div class="card rounded-2xl p-5 sm:p-6">
        <h2 class="text-base font-semibold text-heading mb-5">Rating Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-[220px_1fr] gap-6 md:gap-10 items-center">
            <div class="text-center md:text-left">
                <p class="text-5xl font-black text-heading leading-none">{{ number_format($averageRating, 1) }}</p>
                <div class="flex gap-0.5 justify-center md:justify-start mt-2">
                    @for($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    @endfor
                </div>
                <p class="text-sm text-muted mt-2">{{ $totalReviews }} {{ Str::plural('review', $totalReviews) }}</p>
            </div>
            <div class="space-y-2.5 w-full max-w-xl md:ml-auto">
                @foreach([5,4,3,2,1] as $star)
                @php
                    $count = $ratingCounts[$star] ?? 0;
                    $pct = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                @endphp
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-8 shrink-0 text-xs font-medium text-muted text-right">{{ $star }} ★</span>
                    <div class="reviews-rating-bar flex-1">
                        <div class="reviews-rating-bar-fill" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="w-6 shrink-0 text-xs text-muted text-right tabular-nums">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="reviews-section-title mb-2">Filter Reviews</p>
            <form action="{{ route('reviews.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="rating" onchange="this.form.submit()" class="form-select w-auto text-sm" aria-label="Filter by rating">
                    <option value="">All ratings</option>
                    @foreach([5,4,3,2,1] as $r)
                    <option value="{{ $r }}" {{ (string) $rating === (string) $r ? 'selected' : '' }}>{{ $r }} stars</option>
                    @endforeach
                </select>
                @if(!($isScopedStaff ?? false))
                <select name="staff_id" onchange="this.form.submit()" class="form-select w-auto text-sm" aria-label="Filter by staff">
                    <option value="">All staff</option>
                    @foreach($staffReviewLinks as $row)
                    <option value="{{ $row['staff']->id }}" {{ (int) ($staffId ?? 0) === (int) $row['staff']->id ? 'selected' : '' }}>{{ $row['staff']->name }}</option>
                    @endforeach
                </select>
                @endif
                <select name="service_id" onchange="this.form.submit()" class="form-select w-auto text-sm" aria-label="Filter by service">
                    <option value="">All services</option>
                    @foreach($filterServices ?? [] as $service)
                    <option value="{{ $service->id }}" {{ (int) ($serviceId ?? 0) === (int) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                    @endforeach
                </select>
                <select name="replied" onchange="this.form.submit()" class="form-select w-auto text-sm" aria-label="Filter by reply status">
                    <option value="">All replies</option>
                    <option value="0" {{ $replied === '0' ? 'selected' : '' }}>Awaiting reply</option>
                    <option value="1" {{ $replied === '1' ? 'selected' : '' }}>Replied</option>
                </select>
            </form>
        </div>
        <p class="text-sm text-muted shrink-0">{{ $reviews->count() }} shown · {{ $totalReviews }} total</p>
    </div>

    {{-- Review cards --}}
    <div class="space-y-4">
        @forelse($reviews as $review)
        @php
            $displayName = $reviewerDisplayName($review);
            $initials = $reviewerInitials($displayName);
        @endphp
        <div class="card rounded-2xl p-5 sm:p-6" x-data="{ replying: false }">
            <div class="flex items-start gap-4">
                <div class="reviews-avatar" aria-hidden="true">{{ $initials }}</div>
                <div class="flex-1 min-w-0">
                    {{-- Header --}}
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4 mb-3">
                        <div>
                            <h3 class="font-semibold text-heading capitalize">{{ $displayName }}</h3>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">
                                <span class="flex gap-0.5" aria-label="{{ $review->rating }} out of 5 stars">
                                    @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    @endfor
                                </span>
                                <span class="text-xs text-muted">• {{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Review text --}}
                    @if($review->comment)
                    <p class="text-sm text-body leading-relaxed mb-3">&ldquo;{{ $review->comment }}&rdquo;</p>
                    @endif

                    {{-- Service metadata --}}
                    @if($review->service)
                    <p class="text-xs text-muted mb-4">Service: <span class="text-body">{{ $review->service->name }}</span></p>
                    @endif

                    {{-- Reply --}}
                    @if($review->owner_reply)
                    <div class="reviews-reply-block">
                        <p class="text-xs font-bold uppercase tracking-wide text-velour-600 dark:text-velour-400 mb-1.5">Your reply</p>
                        <p class="text-sm text-body leading-relaxed">{{ $review->owner_reply }}</p>
                    </div>
                    @else
                    <x-unless-admin-browse>
                    <div class="flex items-center gap-2 mt-1">
                        <button type="button" @click="replying=!replying" class="text-xs text-link font-medium salon-write-ui hover:underline">Reply</button>
                    </div>
                    <div x-show="replying" x-cloak class="mt-4">
                        <form action="{{ route('reviews.reply', $review->id) }}" method="POST">
                            @csrf
                            <textarea name="reply" rows="3" required placeholder="Write a reply…" class="form-textarea text-sm"></textarea>
                            <div class="flex gap-2 mt-2">
                                <button type="submit" class="btn-primary btn-sm">Post Reply</button>
                                <button type="button" @click="replying=false" class="btn-outline btn-sm">Cancel</button>
                            </div>
                        </form>
                    </div>
                    </x-unless-admin-browse>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card rounded-2xl">
            <div class="empty-state py-12">
                <p class="empty-state-title">No reviews yet</p>
                <p class="text-sm text-muted mt-1">Share your review links to start collecting feedback.</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($reviews->hasPages())
    <div class="pt-2">{{ $reviews->links() }}</div>
    @endif
</div>

<script>
document.addEventListener('click', async function (e) {
    var btn = e.target.closest('.copy-review-link-btn');
    if (!btn) return;

    var url = btn.getAttribute('data-copy-url');
    if (!url) return;

    var label = btn.querySelector('.copy-label');
    var original = label ? label.textContent : 'Copy';

    try {
        await navigator.clipboard.writeText(url);
    } catch (err) {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
    }

    btn.classList.add('is-copied');
    if (label) label.textContent = 'Copied ✓';
    setTimeout(function () {
        btn.classList.remove('is-copied');
        if (label) label.textContent = original;
    }, 1800);
});
</script>
@endsection
