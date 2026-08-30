@extends('layouts.auth')
@section('title', 'Leave a Review')
@section('auth_container_class', 'max-w-sm')
@section('content')

@php
    $who = $staff?->name;
    $salonName = $salon->name;
    $templates = array_values(array_filter([
        $who
            ? "Had a great experience with {$who} at {$salonName}. Highly recommended!"
            : "Had a great experience at {$salonName}. Highly recommended!",
        $who
            ? "{$who} did an amazing job. Thank you, {$salonName}!"
            : "Amazing service at {$salonName}. Will visit again!",
        'Friendly staff and excellent results. 5 stars!',
        'Clean place, on time, and very professional.',
        'Loved it — will definitely book again.',
    ]));
    $defaultComment = old('comment', $templates[0]);
    $selectedRating = (int) old('rating', 5);
    $serverNameError = $errors->first('reviewer_name');
@endphp

<div style="text-align:center; margin-bottom:1.25rem;">
    <h2 class="auth-title" style="font-size:1.25rem; margin:0 0 0.25rem;">{{ $salonName }}</h2>
    <p class="auth-subtitle" style="margin:0; font-size:0.875rem;">
        @if($who)
            Review for {{ $who }}
        @else
            Quick feedback
        @endif
    </p>
</div>

<form
    action="{{ route('reviews.public.submit', ['store' => $store, 'token' => $reviewLink->token]) }}"
    method="POST"
    class="review-simple-form"
    novalidate
    x-data="{
        rating: {{ $selectedRating }},
        name: @js(old('reviewer_name', '')),
        comment: @js($defaultComment),
        nameError: @js($serverNameError ?: ''),
        templates: @js($templates),
        pick(t) { this.comment = t; },
        submit() {
            if (!String(this.name || '').trim()) {
                this.nameError = 'Your name is required.';
                this.$nextTick(() => this.$refs.nameInput?.focus());
                return;
            }
            this.nameError = '';
            this.$el.submit();
        }
    }"
    @submit.prevent="submit()"
>
    @csrf
    <input type="hidden" name="rating" :value="rating">

    {{-- Stars --}}
    <div class="review-block review-block--center">
        <div class="review-stars" role="radiogroup" aria-label="Rating">
            @foreach([1,2,3,4,5] as $star)
                <button
                    type="button"
                    class="review-star"
                    :class="rating >= {{ $star }} ? 'is-on' : ''"
                    @click="rating = {{ $star }}"
                    :aria-checked="rating === {{ $star }}"
                    role="radio"
                    aria-label="{{ $star }} stars"
                >
                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Name --}}
    <div class="review-block">
        <input
            type="text"
            name="reviewer_name"
            x-ref="nameInput"
            x-model="name"
            autocomplete="name"
            class="auth-input"
            :class="nameError ? 'is-invalid' : ''"
            placeholder="Your name *"
            @input="nameError = ''"
        >
        <p class="review-error-msg" x-show="nameError" x-text="nameError" x-cloak></p>
    </div>

    {{-- Service (optional, compact) --}}
    @if($services->isNotEmpty())
    <div class="review-block">
        <select name="service_id" class="auth-input">
            <option value="">Service (optional)</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>
    @endif

    {{-- Quick templates --}}
    <div class="review-block">
        <p class="review-hint">Pick a message</p>
        <div class="review-templates">
            <template x-for="(t, i) in templates" :key="i">
                <button
                    type="button"
                    class="review-chip"
                    :class="comment === t ? 'is-active' : ''"
                    @click="pick(t)"
                    x-text="t.length > 42 ? t.slice(0, 42) + '…' : t"
                ></button>
            </template>
        </div>
        <textarea
            name="comment"
            rows="3"
            class="auth-input"
            x-model="comment"
            placeholder="Or write your own…"
        ></textarea>
    </div>

    <button type="submit" class="auth-btn">
        <span>Submit Review</span>
    </button>
</form>

<style>
[x-cloak] { display: none !important; }

.review-simple-form {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.review-block { display: flex; flex-direction: column; gap: 0.45rem; }
.review-block--center { align-items: center; }

.review-hint {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: none;
    letter-spacing: normal;
}
.dark .review-hint { color: #94a3b8; }

.review-templates {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.review-chip {
    appearance: none;
    text-align: left;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    border-radius: 0.75rem;
    padding: 0.55rem 0.75rem;
    font-size: 0.8rem;
    line-height: 1.35;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
}
.dark .review-chip {
    border-color: #374151;
    background: rgba(31, 41, 55, 0.7);
    color: #e5e7eb;
}
.review-chip:hover { border-color: #a78bfa; }
.review-chip.is-active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #5b21b6;
    font-weight: 600;
}
.dark .review-chip.is-active {
    border-color: #8b5cf6;
    background: rgba(91, 33, 182, 0.25);
    color: #ddd6fe;
}

.review-error-msg {
    color: #dc2626 !important;
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    text-transform: none !important;
    letter-spacing: normal !important;
    margin: 0 !important;
}
.dark .review-error-msg { color: #f87171 !important; }

.review-stars { display: flex; gap: 0.25rem; }
.review-star {
    appearance: none;
    border: 0;
    background: transparent;
    padding: 0.1rem;
    cursor: pointer;
    color: #cbd5e1;
    line-height: 0;
}
.dark .review-star { color: #4b5563; }
.review-star svg { width: 2.1rem; height: 2.1rem; }
.review-star.is-on { color: #f59e0b; }

.auth-input.is-invalid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18) !important;
}
</style>

@endsection
