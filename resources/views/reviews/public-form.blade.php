@extends('layouts.auth')
@section('title', 'Leave a Review')
@section('auth_container_class', 'auth-container--review')
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
    $defaultComment = old('comment', '');
    $selectedRating = (int) old('rating', 0);
    $serverNameError = $errors->first('reviewer_name');
    $serverRatingError = $errors->first('rating');
@endphp

<div class="review-page-header">
    <h1 class="review-salon-name">{{ $salonName }}</h1>
    <p class="review-salon-lead">How was your experience?</p>
    @if($who)
    <p class="review-salon-sub">Review for {{ $who }}</p>
    @else
    <p class="review-salon-sub">Your feedback helps us improve.</p>
    @endif
</div>

<form
    action="{{ route('reviews.public.submit', ['store' => $store, 'token' => $reviewLink->token]) }}"
    method="POST"
    class="review-simple-form"
    novalidate
    x-data="{
        rating: {{ $selectedRating }},
        hoverRating: 0,
        name: @js(old('reviewer_name', '')),
        comment: @js($defaultComment),
        nameError: @js($serverNameError ?: ''),
        ratingError: @js($serverRatingError ?: ''),
        submitting: false,
        templates: @js($templates),
        ratingLabels: { 1: 'Poor', 2: 'Fair', 3: 'Good', 4: 'Very good', 5: 'Excellent' },
        activeRating() {
            return this.hoverRating || this.rating;
        },
        ratingLabel() {
            var r = this.activeRating();
            return r ? (this.ratingLabels[r] || '') : '';
        },
        pick(t) {
            this.comment = t;
        },
        isSelected(t) {
            return String(this.comment || '').trim() === String(t || '').trim();
        },
        submitForm() {
            var trimmedName = String(this.name || '').trim();
            if (!trimmedName) {
                this.nameError = 'Your name is required.';
                this.$nextTick(() => this.$refs.nameInput?.focus());
                return;
            }
            this.nameError = '';
            if (!this.rating || this.rating < 1) {
                this.ratingError = 'Please select a star rating.';
                return;
            }
            this.ratingError = '';
            this.submitting = true;
            this.$el.submit();
        }
    }"
    @submit.prevent="submitForm()"
>
    @csrf
    <input type="hidden" name="rating" :value="rating || ''">

    {{-- Star rating --}}
    <div class="review-block review-block--center">
        <div
            class="review-stars"
            role="radiogroup"
            aria-label="Rating"
            @mouseleave="hoverRating = 0"
        >
            @foreach([1,2,3,4,5] as $star)
            <button
                type="button"
                class="review-star"
                :class="activeRating() >= {{ $star }} ? 'is-on' : ''"
                @click="rating = {{ $star }}; ratingError = ''"
                @mouseenter="hoverRating = {{ $star }}"
                :aria-checked="rating === {{ $star }}"
                role="radio"
                aria-label="{{ $star }} stars"
            >
                <svg class="review-star-outline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                </svg>
                <svg class="review-star-filled" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </button>
            @endforeach
        </div>
        <p class="review-rating-label" x-text="ratingLabel()" x-show="ratingLabel()" x-cloak></p>
        <p class="review-error-msg review-error-msg--center" x-show="ratingError" x-text="ratingError" x-cloak></p>
    </div>

    {{-- Name + Service --}}
    <div class="review-fields-row {{ $services->isEmpty() ? 'review-fields-row--single' : '' }}">
        <div class="review-block">
            <label for="reviewer-name" class="review-label">Your name <span class="review-required">*</span></label>
            <input
                id="reviewer-name"
                type="text"
                name="reviewer_name"
                x-ref="nameInput"
                x-model="name"
                autocomplete="name"
                class="auth-input"
                :class="nameError ? 'is-invalid' : ''"
                placeholder="Enter your name"
                @input="nameError = ''"
            >
            <p class="review-error-msg" x-show="nameError" x-text="nameError" x-cloak></p>
        </div>

        @if($services->isNotEmpty())
        <div class="review-block">
            <label for="review-service" class="review-label">Service <span class="review-optional">(optional)</span></label>
            <select id="review-service" name="service_id" class="auth-input">
                <option value="">Select a service</option>
                @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Quick templates --}}
    <div class="review-block">
        <p class="review-label">Pick a message</p>
        <div class="review-templates">
            <template x-for="(t, i) in templates" :key="i">
                <button
                    type="button"
                    class="review-chip"
                    :class="isSelected(t) ? 'is-active' : ''"
                    @click="pick(t)"
                >
                    <span class="review-chip-text" x-text="t"></span>
                    <svg class="review-chip-check" x-show="isSelected(t)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
            </template>
        </div>
    </div>

    {{-- Review textarea --}}
    <div class="review-block">
        <label for="review-comment" class="review-label">Your review</label>
        <textarea
            id="review-comment"
            name="comment"
            rows="3"
            class="auth-input review-textarea"
            x-model="comment"
            placeholder="Write your own review or edit the message above…"
        ></textarea>
        <p class="review-helper">Write your own review or edit the selected message.</p>
    </div>

    <button type="submit" class="auth-btn review-submit-btn" :disabled="submitting">
        <span x-show="!submitting">Submit review →</span>
        <span x-show="submitting" x-cloak>Submitting…</span>
    </button>
</form>

<style>
[x-cloak] { display: none !important; }

/* Wider card so the form feels less tall */
.auth-container.auth-container--review {
    max-width: 720px;
    width: 100%;
}
.auth-container.auth-container--review .auth-brand {
    margin-bottom: 0.4rem;
}
.auth-container.auth-container--review .auth-logo-img {
    height: 2rem;
}
.auth-container.auth-container--review .auth-tagline {
    display: none;
}
.auth-container.auth-container--review .auth-panel-body {
    padding: 1.1rem 1.15rem 1.2rem;
}
.auth-shell:has(.auth-container--review) {
    justify-content: flex-start !important;
    align-items: center;
    min-height: 100vh;
    padding: 0.65rem 1rem 1.15rem;
    padding-top: max(0.65rem, env(safe-area-inset-top));
}
@media (min-width: 640px) {
    .auth-shell:has(.auth-container--review) {
        justify-content: flex-start !important;
        padding: 0.85rem 1.25rem 1.35rem;
        padding-top: max(0.85rem, env(safe-area-inset-top));
    }
    .auth-container.auth-container--review .auth-panel-body {
        padding: 1.25rem 1.5rem 1.35rem;
    }
}
@media (min-width: 900px) {
    .auth-container.auth-container--review {
        max-width: 780px;
    }
}
.auth-container.auth-container--review .auth-meta {
    margin-top: 0.65rem;
}
.auth-container.auth-container--review .auth-meta-rule {
    display: none;
}
.auth-container.auth-container--review .auth-meta-copy,
.auth-container.auth-container--review .auth-meta-credit {
    font-size: 0.7rem;
    line-height: 1.3;
}

/* Subtle background focus */
body:has(.auth-container--review) .auth-glow {
    opacity: 0.72;
}

.review-page-header {
    text-align: center;
    margin-bottom: 0.7rem;
}
.review-salon-name {
    margin: 0 0 0.15rem;
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #0f172a;
    line-height: 1.2;
}
.dark .review-salon-name { color: #f8fafc; }
.review-salon-lead {
    margin: 0 0 0.15rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #334155;
}
.dark .review-salon-lead { color: #e2e8f0; }
.review-salon-sub {
    margin: 0;
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.35;
}
.dark .review-salon-sub { color: #94a3b8; }

.review-simple-form {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.review-fields-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.7rem;
}
@media (min-width: 640px) {
    .review-fields-row {
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
        align-items: start;
    }
    .review-fields-row--single {
        grid-template-columns: 1fr;
        max-width: 50%;
    }
}

.review-block {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    min-width: 0;
}
.review-block--center {
    align-items: center;
}

.review-label {
    margin: 0;
    font-size: 0.75rem;
    font-weight: 600;
    color: #334155;
}
.dark .review-label { color: #e2e8f0; }
.review-required { color: #dc2626; }
.review-optional {
    font-weight: 500;
    color: #94a3b8;
}

.review-helper {
    margin: 0;
    font-size: 0.6875rem;
    color: #94a3b8;
    line-height: 1.35;
}

.review-templates {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.4rem;
}
@media (min-width: 640px) {
    .review-templates {
        grid-template-columns: 1fr 1fr;
        gap: 0.45rem;
    }
}

.review-chip {
    appearance: none;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    width: 100%;
    height: 100%;
    text-align: left;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    border-radius: 0.7rem;
    padding: 0.55rem 0.7rem;
    min-height: 0;
    font-size: 0.76rem;
    line-height: 1.35;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s, box-shadow 0.15s;
}
.dark .review-chip {
    border-color: #374151;
    background: rgba(31, 41, 55, 0.65);
    color: #e5e7eb;
}
.review-chip:hover {
    border-color: #c4b5fd;
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.08);
}
.review-chip.is-active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #5b21b6;
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.15);
}
.dark .review-chip.is-active {
    border-color: #8b5cf6;
    background: rgba(91, 33, 182, 0.22);
    color: #ede9fe;
}
.review-chip-text {
    flex: 1;
    min-width: 0;
    white-space: normal;
    word-break: break-word;
}
.review-chip-check {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    margin-top: 0.1rem;
    color: #7c3aed;
}
.dark .review-chip-check { color: #c4b5fd; }

.review-textarea {
    min-height: 4rem;
    resize: vertical;
    line-height: 1.45;
}

.review-error-msg {
    color: #dc2626 !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    margin: 0 !important;
}
.review-error-msg--center { text-align: center; }
.dark .review-error-msg { color: #f87171 !important; }

.review-stars {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}
.review-star {
    appearance: none;
    border: 0;
    background: transparent;
    padding: 0.1rem;
    cursor: pointer;
    color: #cbd5e1;
    line-height: 0;
    position: relative;
    width: 2rem;
    height: 2rem;
    transition: transform 0.12s ease;
}
.review-star:hover { transform: scale(1.08); }
.dark .review-star { color: #4b5563; }
.review-star svg {
    width: 1.85rem;
    height: 1.85rem;
    position: absolute;
    inset: 0;
    margin: auto;
}
.review-star-filled { opacity: 0; color: #f59e0b; transition: opacity 0.12s ease; }
.review-star-outline { opacity: 1; transition: opacity 0.12s ease; }
.review-star.is-on .review-star-filled { opacity: 1; }
.review-star.is-on .review-star-outline { opacity: 0; }
.review-star.is-on { color: #f59e0b; }

.review-rating-label {
    margin: 0.25rem 0 0;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #f59e0b;
    min-height: 1.1rem;
    text-align: center;
}

.auth-container.auth-container--review .auth-input {
    padding-top: 0.65rem;
    padding-bottom: 0.65rem;
}
.auth-container.auth-container--review .auth-btn {
    margin-top: 0.15rem;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.review-submit-btn:disabled {
    opacity: 0.72;
    cursor: wait;
}

.auth-input.is-invalid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18) !important;
}

@media (max-width: 639px) {
    .auth-container.auth-container--review {
        max-width: none;
    }
    .auth-shell:has(.auth-container--review) {
        padding-left: max(0.875rem, env(safe-area-inset-left));
        padding-right: max(0.875rem, env(safe-area-inset-right));
    }
}
</style>

@endsection
