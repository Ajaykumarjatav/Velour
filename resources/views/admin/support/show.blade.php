@extends('layouts.admin')
@section('title', $ticket->ticket_number . ' — ' . $ticket->subject)
@section('page-title', $ticket->ticket_number)
@section('content')

<div class="max-w-4xl space-y-5" x-data="{ replyTab: 'reply' }">

  {{-- Flash --}}
  @if(session('success'))
  <div class="px-4 py-3 bg-green-900/30 border border-green-800/50 rounded-xl text-sm text-green-300">{{ session('success') }}</div>
  @endif

  {{-- Header --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="flex flex-col sm:flex-row items-start gap-4 justify-between">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 mb-1">
          <span class="font-mono text-xs text-gray-500">{{ $ticket->ticket_number }}</span>
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold border {{ $ticket->priorityColor() }} capitalize">
            {{ $ticket->priority }}
          </span>
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $ticket->statusColor() }} capitalize">
            {{ str_replace('_',' ',$ticket->status) }}
          </span>
          <span class="text-xs text-gray-500 capitalize">{{ str_replace('_',' ',$ticket->category) }}</span>
        </div>
        <h2 class="text-lg font-bold text-white">{{ $ticket->subject }}</h2>
        <div class="flex flex-wrap gap-3 mt-1.5 text-xs text-gray-500">
          @if($ticket->user)
            <span>From: <span class="text-gray-300">{{ $ticket->user->name }}</span> ({{ $ticket->user->email }})</span>
          @endif
          @if($ticket->salon)
            <span>Salon:
              <a href="{{ route('admin.tenants.show', $ticket->salon_id) }}" class="text-velour-400 hover:text-velour-300">
                {{ $ticket->salon->name }}
              </a>
            </span>
          @endif
          <span>Opened {{ $ticket->created_at->diffForHumans() }}</span>
          @if($ticket->responseTime())
            <span>First reply: {{ $ticket->responseTime() }}</span>
          @endif
        </div>
      </div>

      {{-- Quick actions --}}
      <div class="flex flex-wrap gap-2 flex-shrink-0">
        {{-- Assign --}}
        <form method="POST" action="{{ route('admin.support.assign', $ticket) }}" class="flex gap-2">
          @csrf @method('PATCH')
          <select name="assigned_to"
                  class="px-3 py-2 text-xs bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
            <option value="">Unassigned</option>
            @foreach($admins as $admin)
            <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
              {{ $admin->name }}
            </option>
            @endforeach
          </select>
          <button type="submit"
                  class="px-3 py-2 text-xs font-medium rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 transition-colors">
            Assign
          </button>
        </form>
      </div>
    </div>

    {{-- Status / priority controls --}}
    <form method="POST" action="{{ route('admin.support.status', $ticket) }}"
          class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-800">
      @csrf @method('PATCH')
      <select name="status"
              class="px-3 py-2 text-xs bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        @foreach(\App\Models\SupportTicket::STATUSES as $s)
        <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>
          {{ ucwords(str_replace('_',' ',$s)) }}
        </option>
        @endforeach
      </select>
      <select name="priority"
              class="px-3 py-2 text-xs bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        @foreach(\App\Models\SupportTicket::PRIORITIES as $p)
        <option value="{{ $p }}" {{ $ticket->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
        @endforeach
      </select>
      <button type="submit"
              class="px-3 py-2 text-xs font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Update
      </button>
    </form>
  </div>

  {{-- Thread --}}
  <div class="space-y-3">

    {{-- Original message --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div class="flex items-center justify-between px-5 py-3 border-b border-gray-800 bg-gray-800/30">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg bg-velour-800 flex items-center justify-center text-velour-200 text-xs font-bold">
            {{ strtoupper(substr($ticket->user?->name ?? 'U', 0, 1)) }}
          </div>
          <span class="text-sm font-medium text-gray-200">{{ $ticket->user?->name ?? 'Unknown user' }}</span>
          <span class="text-xs text-gray-500">· Customer</span>
        </div>
        <span class="text-xs text-gray-500">{{ $ticket->created_at->format('d M Y H:i') }}</span>
      </div>
      <div class="px-5 py-4 text-sm text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $ticket->body }}</div>
    </div>

    {{-- Replies --}}
    @foreach($ticket->replies as $reply)
    @php
      $isAdmin = $reply->is_admin_reply;
      $isNote  = $reply->is_internal;
      $bg = $isNote
          ? 'bg-amber-950/20 border-amber-800/30'
          : ($isAdmin ? 'bg-velour-950/20 border-velour-800/30' : 'bg-gray-900 border-gray-800');
    @endphp
    <div class="border rounded-2xl overflow-hidden {{ $bg }}">
      <div class="flex items-center justify-between px-5 py-3 border-b border-gray-800/50">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-lg {{ $isAdmin ? 'bg-velour-700' : 'bg-gray-700' }} flex items-center justify-center text-white text-xs font-bold">
            {{ strtoupper(substr($reply->author?->name ?? 'A', 0, 1)) }}
          </div>
          <span class="text-sm font-medium text-gray-200">{{ $reply->author?->name ?? 'Admin' }}</span>
          @if($isAdmin && !$isNote)
            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-velour-800/60 text-velour-300 rounded">SUPPORT</span>
          @endif
          @if($isNote)
            <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-800/40 text-amber-400 rounded">INTERNAL NOTE</span>
          @endif
        </div>
        <span class="text-xs text-gray-500">{{ $reply->created_at->format('d M Y H:i') }}</span>
      </div>
      <div class="px-5 py-4 text-sm text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $reply->body }}</div>
    </div>
    @endforeach
  </div>

  {{-- Reply box --}}
  @if($ticket->isOpen())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="flex border-b border-gray-800">
      <button @click="replyTab='reply'"
              :class="replyTab==='reply' ? 'bg-gray-800 text-white' : 'text-gray-500 hover:text-gray-300'"
              class="px-5 py-3 text-sm font-medium transition-colors">
        Reply to customer
      </button>
      <button @click="replyTab='note'"
              :class="replyTab==='note' ? 'bg-amber-900/30 text-amber-300' : 'text-gray-500 hover:text-gray-300'"
              class="px-5 py-3 text-sm font-medium transition-colors">
        Internal note
      </button>
    </div>

    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="p-5">
      @csrf
      <input type="hidden" name="is_internal" :value="replyTab==='note' ? 1 : 0" x-bind:value="replyTab==='note' ? 1 : 0">

      <textarea name="body" rows="5" required
                :placeholder="replyTab==='reply' ? 'Write your reply to the customer…' : 'Internal note — only visible to admins…'"
                class="w-full px-4 py-3 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                       focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-500 resize-none"
                x-bind:class="replyTab==='note' ? 'border-amber-800/50' : 'border-gray-700'"></textarea>

      <div class="flex items-center gap-3 mt-3">
        <select name="status"
                class="px-3 py-2 text-xs bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
          <option value="">Keep current status</option>
          @foreach(\App\Models\SupportTicket::STATUSES as $s)
          <option value="{{ $s }}">→ {{ ucwords(str_replace('_',' ',$s)) }}</option>
          @endforeach
        </select>

        <button type="submit"
                :class="replyTab==='note' ? 'bg-amber-700 hover:bg-amber-600' : 'bg-velour-600 hover:bg-velour-700'"
                class="px-5 py-2 text-sm font-semibold rounded-xl text-white transition-colors"
                x-text="replyTab==='note' ? 'Save note' : 'Send reply'">
        </button>
      </div>
    </form>
  </div>
  @else
  <div class="px-5 py-4 bg-gray-900 border border-gray-800 rounded-2xl text-sm text-gray-500 text-center">
    This ticket is {{ $ticket->status }}. Reopen it to reply.
  </div>
  @endif

  <a href="{{ route('admin.support.index') }}" class="inline-block text-sm text-gray-500 hover:text-gray-300">
    ← Back to queue
  </a>

</div>
@endsection
