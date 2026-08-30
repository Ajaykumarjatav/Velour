@extends('layouts.admin')
@section('title', 'Contact query #'.$query->id)
@section('page-title', 'Contact query #'.$query->id)

@section('content')
<div class="max-w-3xl space-y-5">
  <a href="{{ route('admin.contact-queries.index') }}" class="inline-flex text-sm text-gray-400 hover:text-white">← Back to list</a>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
      <div>
        <h2 class="text-lg font-semibold text-white">{{ $query->full_name }}</h2>
        <a href="mailto:{{ $query->email }}" class="text-sm text-velour-400 hover:text-velour-300">{{ $query->email }}</a>
      </div>
      <p class="text-xs text-gray-500">{{ $query->created_at?->format('l, j F Y · H:i') }}</p>
    </div>

    <dl class="grid sm:grid-cols-2 gap-4 text-sm">
      <div>
        <dt class="text-xs uppercase tracking-wider text-gray-500 mb-1">Business</dt>
        <dd class="text-gray-200">{{ $query->business_name ?: '—' }}</dd>
      </div>
      <div>
        <dt class="text-xs uppercase tracking-wider text-gray-500 mb-1">Help topics</dt>
        <dd class="flex flex-wrap gap-1.5">
          @forelse($query->topicList() as $t)
            <span class="inline-flex px-2.5 py-1 rounded-lg text-xs bg-gray-800 text-gray-200 border border-gray-700">{{ $t }}</span>
          @empty
            <span class="text-gray-500">—</span>
          @endforelse
        </dd>
      </div>
    </dl>

    <div>
      <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Message</p>
      <div class="rounded-xl bg-gray-950/60 border border-gray-800 px-4 py-3 text-sm text-gray-200 whitespace-pre-wrap">{{ $query->message ?: '—' }}</div>
    </div>

    <div>
      <a href="mailto:{{ $query->email }}?subject={{ rawurlencode('Re: EasyGrox contact') }}"
         class="inline-flex px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Reply by email
      </a>
    </div>
  </div>
</div>
@endsection
