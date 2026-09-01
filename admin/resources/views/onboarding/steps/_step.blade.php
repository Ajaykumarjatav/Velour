@php
    $title = $meta['title'] ?? 'Business profile';
    $description = $meta['description'] ?? 'Complete this step to continue setting up your business.';
    $actionUrl = $meta['action_url'] ?? route('settings.index', ['tab' => 'business']);
    $actionLabel = $meta['action_label'] ?? 'Open Business Settings';
    $done = ! empty($meta['done']);
@endphp

<div class="auth-header">
    <p class="auth-eyebrow">Setup</p>
    <h2 class="auth-title">{{ $title }}</h2>
    <p class="auth-subtitle">{{ $description }}</p>
</div>

<a href="{{ $actionUrl }}"
   class="w-full mb-4 inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 bg-white/70 dark:bg-slate-800/60 px-4 py-3 text-sm font-semibold text-slate-800 dark:text-slate-100 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
    {{ $actionLabel }}
</a>

<form method="POST" action="{{ route('onboarding.complete-step', ['step' => $stepKey]) }}" class="auth-form" style="gap:1rem">
    @csrf
    <div class="rounded-xl border px-4 py-3 text-sm {{ $done
        ? 'border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200'
        : 'border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200' }}">
        @if($done)
            Step completed. Continue to the next onboarding step.
        @else
            Configure this step in Settings first, then click continue.
        @endif
    </div>

    <div class="flex flex-col-reverse sm:flex-row gap-3">
        <a href="{{ route('onboarding.skip') }}"
           class="sm:shrink-0 inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-medium text-slate-500 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
            Skip
        </a>
        <button type="submit"
                class="auth-btn flex-1 {{ $done ? '' : 'opacity-50 cursor-not-allowed' }}"
                style="margin:0"
                {{ $done ? '' : 'disabled' }}>
            <span>Continue →</span>
        </button>
    </div>
</form>
