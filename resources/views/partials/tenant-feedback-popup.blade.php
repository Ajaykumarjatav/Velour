{{-- Tenant project feedback popup: once/day until the salon has submitted. --}}
@php
    $showTenantFeedbackPrompt = auth()->check()
        && ! \App\Support\AuthPanel::isAdminStoreBrowse()
        && ! auth()->user()->isSuperAdmin()
        && \App\Support\AuthPanel::typeFor(auth()->user()) === \App\Support\AuthPanel::TENANT;

    $feedbackTopics = [
        ['id' => 'easy_to_use', 'label' => 'Easy to use', 'emoji' => '👍'],
        ['id' => 'performance', 'label' => 'Performance', 'emoji' => '⚡'],
        ['id' => 'design', 'label' => 'Design', 'emoji' => '🎨'],
        ['id' => 'booking', 'label' => 'Booking', 'emoji' => '📅'],
        ['id' => 'reports', 'label' => 'Reports', 'emoji' => '📊'],
        ['id' => 'feature_request', 'label' => 'Feature request', 'emoji' => '💡'],
    ];
@endphp

@if($showTenantFeedbackPrompt)
<style>
[x-cloak] { display: none !important; }
.tfb-overlay {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    background: rgba(15, 23, 42, 0.62);
}
.tfb-card {
    width: 100%;
    max-width: 500px;
    border-radius: 1.25rem;
    padding: 1.75rem 1.75rem 1.5rem;
    box-shadow:
        0 0 0 1px rgba(124, 58, 237, 0.12),
        0 24px 48px -12px rgba(15, 23, 42, 0.45);
}
.dark .tfb-card {
    background: rgba(17, 24, 39, 0.96);
    border-color: rgba(55, 65, 81, 0.9);
    box-shadow:
        0 0 0 1px rgba(139, 92, 246, 0.18),
        0 24px 48px -12px rgba(0, 0, 0, 0.55);
}
.tfb-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    background: linear-gradient(135deg, #7c3aed 0%, #a855f7 55%, #6366f1 100%);
    box-shadow: 0 8px 20px -8px rgba(124, 58, 237, 0.75);
}
.tfb-star {
    appearance: none;
    border: 0;
    background: transparent;
    padding: 0.2rem;
    cursor: pointer;
    color: #cbd5e1;
    line-height: 0;
    transition: transform 0.15s ease, color 0.15s ease;
}
.dark .tfb-star { color: #4b5563; }
.tfb-star:hover { transform: scale(1.12); }
.tfb-star.is-on { color: #f59e0b; }
.tfb-star svg { width: 2.15rem; height: 2.15rem; }
.tfb-chip {
    appearance: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    border-radius: 9999px;
    padding: 0.4rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.2;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s, box-shadow 0.15s, transform 0.12s;
}
.dark .tfb-chip {
    border-color: #374151;
    background: rgba(31, 41, 55, 0.7);
    color: #e5e7eb;
}
.tfb-chip:hover {
    border-color: #c4b5fd;
    transform: translateY(-1px);
}
.tfb-chip.is-active {
    border-color: #7c3aed;
    background: #f5f3ff;
    color: #5b21b6;
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.18);
}
.dark .tfb-chip.is-active {
    border-color: #8b5cf6;
    background: rgba(91, 33, 182, 0.28);
    color: #ede9fe;
}
.tfb-textarea-wrap {
    position: relative;
}
.tfb-textarea {
    width: 100%;
    min-height: 7.5rem;
    resize: vertical;
    border-radius: 0.9rem;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 0.85rem 0.9rem 1.85rem;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #0f172a;
    transition: border-color 0.15s, box-shadow 0.15s;
}
.dark .tfb-textarea {
    border-color: #374151;
    background: rgba(17, 24, 39, 0.85);
    color: #f3f4f6;
}
.tfb-textarea:focus {
    outline: none;
    border-color: #8b5cf6;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.18);
}
.tfb-count {
    position: absolute;
    right: 0.75rem;
    bottom: 0.55rem;
    font-size: 0.6875rem;
    color: #94a3b8;
    pointer-events: none;
}
.tfb-submit {
    appearance: none;
    border: 0;
    border-radius: 0.7rem;
    padding: 0.55rem 1.05rem;
    font-size: 0.8125rem;
    font-weight: 700;
    color: #fff;
    background: #7c3aed;
    cursor: pointer;
    transition: background 0.15s, box-shadow 0.15s, transform 0.12s, opacity 0.15s;
}
.tfb-submit:hover:not(:disabled) {
    background: #6d28d9;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.22), 0 10px 24px -10px rgba(124, 58, 237, 0.7);
}
.tfb-submit:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    box-shadow: none;
}
.tfb-section { margin-top: 1.35rem; }
</style>

<div
    id="tenant-feedback-popup"
    class="tfb-overlay fixed inset-0 z-[10050] flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tenant-feedback-title"
    x-data="tenantFeedbackPopup(@js([
        'statusUrl' => \App\Support\AppUrl::path('feedback.status'),
        'storeUrl' => \App\Support\AppUrl::path('feedback.store'),
        'topics' => $feedbackTopics,
    ]))"
    x-cloak
    x-show="open"
    x-transition.opacity.duration.200ms
    @keydown.escape.window="open && dismissForToday()"
>
    <div class="absolute inset-0" @click="dismissForToday()"></div>
    <div
        class="tfb-card relative bg-white border border-gray-200/90 dark:border-gray-700"
        @click.stop
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 min-w-0">
                <div class="tfb-icon flex items-center justify-center flex-shrink-0 text-white" aria-hidden="true">
                    <svg class="w-4.5 h-4.5 w-[1.15rem] h-[1.15rem]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-velour-600 dark:text-velour-400 mb-1.5">Quick feedback</p>
                    <h2 id="tenant-feedback-title" class="text-[1.35rem] sm:text-[1.45rem] font-extrabold tracking-tight text-gray-900 dark:text-white leading-snug">
                        How’s EasyGrox working for you?
                    </h2>
                </div>
            </div>
            <button
                type="button"
                class="p-1 rounded-md text-gray-400/80 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0 -mt-0.5"
                @click="dismissForToday()"
                aria-label="Close"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <p class="mt-2.5 text-sm text-gray-500 dark:text-gray-400 leading-relaxed pl-[3.25rem] -mt-0.5 sm:pl-[3.25rem]">
            Your feedback helps us make EasyGrox better for your business.
        </p>

        <form @submit.prevent="submit()">
            <div class="tfb-section">
                <p class="text-[0.8125rem] font-semibold text-gray-800 dark:text-gray-200 mb-3">Overall rating</p>
                <div class="flex flex-col items-center">
                    <div
                        class="flex items-center gap-1.5"
                        role="radiogroup"
                        aria-label="Rating"
                        @mouseleave="hoverRating = 0"
                    >
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button
                                type="button"
                                class="tfb-star"
                                :class="activeRating() >= star ? 'is-on' : ''"
                                @click="rating = star"
                                @mouseenter="hoverRating = star"
                                :aria-checked="rating === star"
                                role="radio"
                                :aria-label="star + ' stars'"
                            >
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        </template>
                    </div>
                    <div class="mt-2 w-full max-w-[16rem] flex justify-between text-[0.6875rem] text-gray-400 dark:text-gray-500">
                        <span>Poor</span>
                        <span class="font-medium text-amber-500/90" x-show="ratingLabel()" x-text="ratingLabel()" x-cloak></span>
                        <span>Excellent</span>
                    </div>
                </div>
            </div>

            <div class="tfb-section">
                <p class="text-[0.8125rem] font-semibold text-gray-800 dark:text-gray-200 mb-2.5">What would you like to tell us about?</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="topic in topics" :key="topic.id">
                        <button
                            type="button"
                            class="tfb-chip"
                            :class="isTopicSelected(topic.id) ? 'is-active' : ''"
                            @click="toggleTopic(topic.id)"
                        >
                            <span x-text="topic.emoji" aria-hidden="true"></span>
                            <span x-text="topic.label"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="tfb-section">
                <label for="tenant-feedback-message" class="block text-[0.8125rem] font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Your feedback <span class="text-red-500">*</span>
                </label>
                <div class="tfb-textarea-wrap">
                    <textarea
                        id="tenant-feedback-message"
                        rows="4"
                        class="tfb-textarea"
                        x-model="message"
                        maxlength="500"
                        placeholder="Tell us what you think… What’s working well? What’s missing? What could we improve?"
                    ></textarea>
                    <span class="tfb-count" x-text="charCount() + ' / 500'"></span>
                </div>
                <p class="mt-1.5 text-xs text-red-500" x-show="error" x-text="error" x-cloak></p>
            </div>

            <div class="tfb-section flex items-center justify-end gap-3">
                <button
                    type="button"
                    class="px-2 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                    @click="dismissForToday()"
                    :disabled="submitting"
                >
                    Not now
                </button>
                <button
                    type="submit"
                    class="tfb-submit"
                    :disabled="submitting || !canSubmit()"
                >
                    <span x-show="!submitting">Submit feedback →</span>
                    <span x-show="submitting" x-cloak>Sending…</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('tenantFeedbackPopup', function (cfg) {
        return {
            open: false,
            rating: 0,
            hoverRating: 0,
            message: '',
            error: '',
            submitting: false,
            salonId: null,
            userId: null,
            topics: cfg.topics || [],
            selectedTopics: [],
            ratingLabels: { 1: 'Poor', 2: 'Needs work', 3: 'Okay', 4: 'Good', 5: 'Excellent' },
            activeRating: function () {
                return this.hoverRating || this.rating;
            },
            ratingLabel: function () {
                var r = this.activeRating();
                return r ? (this.ratingLabels[r] || '') : '';
            },
            charCount: function () {
                return String(this.message || '').length;
            },
            canSubmit: function () {
                return String(this.message || '').trim().length >= 10;
            },
            isTopicSelected: function (id) {
                return this.selectedTopics.indexOf(id) !== -1;
            },
            toggleTopic: function (id) {
                var i = this.selectedTopics.indexOf(id);
                if (i === -1) this.selectedTopics.push(id);
                else this.selectedTopics.splice(i, 1);
            },
            storageKey: function () {
                var day = new Date().toISOString().slice(0, 10);
                return 'egx-tenant-feedback:' + (this.salonId || '0') + ':' + (this.userId || '0') + ':' + day;
            },
            init: function () {
                var self = this;
                fetch(cfg.statusUrl, {
                    headers: window.EasyGroxHttp
                        ? window.EasyGroxHttp.csrfHeaders({ Accept: 'application/json' })
                        : {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    credentials: 'same-origin',
                }).then(function (res) {
                    if (!res.ok) return null;
                    return res.json();
                }).then(function (data) {
                    if (!data || !data.eligible) return;
                    self.salonId = data.salon_id;
                    self.userId = data.user_id;
                    try {
                        if (localStorage.getItem(self.storageKey()) === '1') return;
                    } catch (e) {}
                    setTimeout(function () { self.open = true; }, 1800);
                }).catch(function () {});
            },
            dismissForToday: function () {
                try {
                    if (this.salonId) localStorage.setItem(this.storageKey(), '1');
                } catch (e) {}
                this.open = false;
            },
            submit: function () {
                var self = this;
                this.error = '';
                var msg = String(this.message || '').trim();
                if (msg.length < 10) {
                    this.error = 'Please write at least 10 characters.';
                    return;
                }
                if (msg.length > 500) {
                    this.error = 'Please keep feedback under 500 characters.';
                    return;
                }
                this.submitting = true;
                var headers = window.EasyGroxHttp
                    ? window.EasyGroxHttp.csrfHeaders({
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                    })
                    : {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    };
                fetch(cfg.storeUrl, {
                    method: 'POST',
                    headers: headers,
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        rating: this.rating || null,
                        message: msg,
                        topics: this.selectedTopics,
                    }),
                }).then(function (res) {
                    return res.json().catch(function () { return {}; }).then(function (data) {
                        return { ok: res.ok, data: data };
                    });
                }).then(function (result) {
                    if (!result.ok) {
                        var data = result.data || {};
                        self.error = (data.errors && data.errors.message && data.errors.message[0])
                            || data.message
                            || 'Could not submit feedback. Please try again.';
                        self.submitting = false;
                        return;
                    }
                    try { localStorage.setItem(self.storageKey(), '1'); } catch (e) {}
                    self.open = false;
                    if (typeof window.showToast === 'function') {
                        window.showToast(result.data.message || 'Thank you for your feedback!', 'success');
                    }
                }).catch(function () {
                    self.error = 'Network error. Please try again.';
                    self.submitting = false;
                });
            },
        };
    });
});
</script>
@endif
