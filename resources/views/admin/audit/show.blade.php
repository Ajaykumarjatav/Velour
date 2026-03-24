@extends('layouts.admin')
@section('title', 'Audit Event #' . $auditLog->id)
@section('page-title', 'Audit Event Detail')
@section('content')

<div class="max-w-3xl space-y-5">

  {{-- Header card --}}
  @php
    $sevColor = match($auditLog->severity) {
      'critical' => 'bg-red-900/30 border-red-800/60 text-red-300',
      'warning'  => 'bg-amber-900/30 border-amber-800/60 text-amber-300',
      default    => 'bg-blue-900/30 border-blue-800/60 text-blue-300',
    };
  @endphp
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-6 py-5 flex items-start justify-between gap-4 border-b border-gray-800">
      <div>
        <p class="font-mono text-lg font-bold text-gray-100">{{ $auditLog->event }}</p>
        <p class="text-sm text-gray-400 mt-1">{{ $auditLog->description }}</p>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        <span class="text-lg">{{ $auditLog->categoryIcon() }}</span>
        <span class="px-3 py-1.5 text-xs font-bold rounded-xl border {{ $sevColor }} capitalize">
          {{ $auditLog->severity }}
        </span>
      </div>
    </div>

    {{-- Core fields --}}
    <dl class="divide-y divide-gray-800/60">
      @foreach([
        ['Occurred At',   $auditLog->occurred_at->format('d M Y H:i:s') . ' UTC (' . $auditLog->occurred_at->diffForHumans() . ')'],
        ['Category',      ucfirst($auditLog->event_category)],
        ['Event',         $auditLog->event],
        ['Request ID',    $auditLog->request_id ?? '—'],
      ] as [$label, $value])
      <div class="grid grid-cols-3 px-6 py-3">
        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider flex items-center">{{ $label }}</dt>
        <dd class="col-span-2 text-sm text-gray-200 font-mono">{{ $value }}</dd>
      </div>
      @endforeach
    </dl>
  </div>

  {{-- User context --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-800">
      User Context
    </h2>
    <dl class="divide-y divide-gray-800/60">
      @foreach([
        ['User ID',    $auditLog->user_id ?? '— (unauthenticated)'],
        ['Email',      $auditLog->user_email ?? '—'],
        ['Name',       $auditLog->user_name  ?? '—'],
        ['Salon ID',   $auditLog->salon_id   ?? '— (platform event)'],
      ] as [$label, $value])
      <div class="grid grid-cols-3 px-6 py-2.5">
        <dt class="text-xs text-gray-500 font-semibold uppercase tracking-wider">{{ $label }}</dt>
        <dd class="col-span-2 text-sm text-gray-300">{{ $value }}</dd>
      </div>
      @endforeach
    </dl>
  </div>

  {{-- Request fingerprint --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-800">
      Request Fingerprint
    </h2>
    <dl class="divide-y divide-gray-800/60">
      @foreach([
        ['IP Address',   $auditLog->ip_address   ?? '—'],
        ['HTTP Method',  $auditLog->http_method  ?? '—'],
        ['URL',          $auditLog->url          ?? '—'],
        ['Session ID',   $auditLog->session_id   ?? '—'],
        ['User Agent',   $auditLog->user_agent   ?? '—'],
      ] as [$label, $value])
      <div class="grid grid-cols-3 px-6 py-2.5">
        <dt class="text-xs text-gray-500 font-semibold uppercase tracking-wider flex items-center">{{ $label }}</dt>
        <dd class="col-span-2 text-xs text-gray-400 font-mono break-all">{{ $value }}</dd>
      </div>
      @endforeach
    </dl>
  </div>

  {{-- Subject (morphed model) --}}
  @if($auditLog->subject_type)
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Subject</h2>
    <div class="flex items-center gap-3">
      <span class="font-mono text-sm text-gray-200">{{ class_basename($auditLog->subject_type) }}</span>
      <span class="text-gray-600">#</span>
      <span class="font-mono text-sm text-velour-400">{{ $auditLog->subject_id }}</span>
    </div>
  </div>
  @endif

  {{-- Metadata --}}
  @if($auditLog->metadata)
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-800">
      Event Metadata
    </h2>
    <pre class="px-5 py-4 text-xs text-green-400 font-mono overflow-x-auto bg-gray-950/50 leading-relaxed">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
  </div>
  @endif

  {{-- Related events (same IP, same user, recent) --}}
  @php
    $related = \App\Models\AuditLog::where(function($q) use ($auditLog) {
        $q->where('ip_address', $auditLog->ip_address)
          ->orWhere('user_id', $auditLog->user_id);
    })
    ->where('id', '!=', $auditLog->id)
    ->orderByDesc('occurred_at')
    ->limit(6)
    ->get();
  @endphp

  @if($related->count())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-800">
      Related Events (same IP / user)
    </h2>
    <div class="divide-y divide-gray-800/60">
      @foreach($related as $rel)
      <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-800/30">
        <div>
          <span class="font-mono text-xs text-gray-300">{{ $rel->event }}</span>
          <span class="text-gray-600 mx-2">·</span>
          <span class="text-xs text-gray-500">{{ $rel->occurred_at->diffForHumans() }}</span>
        </div>
        <a href="{{ route('admin.audit.show', $rel) }}" class="text-xs text-velour-400 hover:text-velour-300">→</a>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  <div class="flex justify-between">
    <a href="{{ route('admin.audit.index') }}"
       class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">
      ← Back to log
    </a>
    @if($auditLog->user_id)
    <a href="{{ route('admin.users.show', $auditLog->user_id) }}"
       class="px-4 py-2 text-sm text-velour-400 hover:text-velour-300">
      View user profile →
    </a>
    @endif
  </div>

</div>

@endsection
