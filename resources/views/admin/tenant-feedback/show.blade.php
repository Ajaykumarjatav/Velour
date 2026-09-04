@extends('layouts.admin')
@section('title', 'Tenant feedback #'.$feedback->id)
@section('page-title', 'Tenant feedback #'.$feedback->id)

@section('content')
<div class="max-w-3xl space-y-5">
  <a href="{{ route('admin.tenant-feedback.index') }}" class="inline-flex text-sm text-gray-400 hover:text-white">← Back to list</a>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-white">{{ $feedback->salon?->name ?? 'Unknown store' }}</h2>
        <p class="text-sm text-gray-500">{{ $feedback->salon?->slug }}</p>
      </div>
      <div class="text-right space-y-1">
        @if($feedback->status === 'new')
          <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">New</span>
        @else
          <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Reviewed</span>
        @endif
        <p class="text-xs text-gray-500">{{ $feedback->created_at?->format('l, j F Y · H:i') }}</p>
      </div>
    </div>

    <dl class="grid sm:grid-cols-2 gap-4 text-sm">
      <div>
        <dt class="text-xs uppercase tracking-wider text-gray-500 mb-1">Submitted by</dt>
        <dd class="text-gray-200">{{ $feedback->user?->name ?? '—' }}</dd>
        @if($feedback->user?->email)
          <a href="mailto:{{ $feedback->user->email }}" class="text-xs text-velour-400 hover:text-velour-300">{{ $feedback->user->email }}</a>
        @endif
      </div>
      <div>
        <dt class="text-xs uppercase tracking-wider text-gray-500 mb-1">Rating</dt>
        <dd class="text-amber-400 font-semibold text-base">
          {{ $feedback->rating ? $feedback->rating.'/5' : 'Not provided' }}
        </dd>
      </div>
      <div class="sm:col-span-2">
        <dt class="text-xs uppercase tracking-wider text-gray-500 mb-1">Topics</dt>
        <dd class="flex flex-wrap gap-1.5">
          @forelse($feedback->topicLabels() as $label)
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs bg-gray-800 text-gray-200 border border-gray-700">{{ $label }}</span>
          @empty
            <span class="text-gray-500">—</span>
          @endforelse
        </dd>
      </div>
    </dl>

    <div>
      <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Feedback</p>
      <div class="rounded-xl bg-gray-950/60 border border-gray-800 px-4 py-3 text-sm text-gray-200 whitespace-pre-wrap">{{ $feedback->message }}</div>
    </div>

    <div class="flex flex-wrap gap-2">
      @if($feedback->user?->email)
        <a href="mailto:{{ $feedback->user->email }}?subject={{ rawurlencode('Re: EasyGrox project feedback') }}"
           class="inline-flex px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Reply by email
        </a>
      @endif
      @if($feedback->status === 'new')
        <form method="POST" action="{{ route('admin.tenant-feedback.reviewed', $feedback) }}">
          @csrf
          <button type="submit"
                  class="inline-flex px-4 py-2 text-sm font-semibold rounded-xl border border-gray-700 text-gray-200 hover:bg-gray-800 transition-colors">
            Mark reviewed
          </button>
        </form>
      @endif
    </div>
  </div>
</div>
@endsection
