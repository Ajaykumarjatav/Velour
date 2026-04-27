@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('content')

<div class="max-w-2xl">

  <div class="flex items-center justify-between mb-5">
    <div class="flex gap-2">
      <a href="{{ route('notifications.index') }}"
         class="px-4 py-2 text-sm font-medium rounded-xl transition-colors
                {{ !$filter ? 'bg-velour-600 text-white' : 'btn-outline' }}">All</a>
      <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
         class="px-4 py-2 text-sm font-medium rounded-xl transition-colors
                {{ $filter === 'unread' ? 'bg-velour-600 text-white' : 'btn-outline' }}">
        Unread
        @if($unreadCount > 0)
        <span class="ml-1.5 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $unreadCount }}</span>
        @endif
      </a>
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
      @csrf
      <button type="submit" class="text-sm font-medium text-link">Mark all read</button>
    </form>
    @endif
  </div>

  <div class="table-wrap">
    @forelse($notifications as $notification)
    @php
      $iconMap = [
        'appointment'=>['📅','bg-blue-100 dark:bg-blue-900/30'],
        'payment'=>['💳','bg-green-100 dark:bg-green-900/30'],
        'review'=>['⭐','bg-amber-100 dark:bg-amber-900/30'],
        'client'=>['👤','bg-purple-100 dark:bg-purple-900/30'],
        'system'=>['⚙️','bg-gray-100 dark:bg-gray-800'],
        'marketing'=>['📢','bg-pink-100 dark:bg-pink-900/30'],
        'low_stock'=>['📦','bg-orange-100 dark:bg-orange-900/30'],
      ];
      [$icon, $iconBg] = $iconMap[$notification->type] ?? ['🔔','bg-gray-100 dark:bg-gray-800'];
    @endphp
    <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 dark:border-gray-800 last:border-0
                {{ !$notification->is_read ? 'bg-velour-50/40 dark:bg-velour-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-800/40' }} transition-colors">
      <div class="w-10 h-10 rounded-xl {{ $iconBg }} flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
        {{ $icon }}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm {{ $notification->is_read ? 'font-normal text-body' : 'font-semibold text-heading' }}">
          {{ $notification->title }}
        </p>
        @if($notification->body)
        <p class="text-xs text-muted mt-0.5 leading-relaxed">{{ $notification->body }}</p>
        @endif
        <p class="text-xs text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</p>
      </div>
      <div class="flex items-center gap-2 flex-shrink-0">
        @if(!$notification->is_read)
        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
          @csrf
          <button type="submit" class="text-xs text-link font-medium whitespace-nowrap">Mark read</button>
        </form>
        @else
        <span class="text-xs text-muted">Read</span>
        @endif
        @if($notification->resolved_action_url)
        <a href="{{ $notification->resolved_action_url }}" class="btn-secondary btn-sm whitespace-nowrap">{{ data_get($notification->data, 'action_label', 'View') }}</a>
        @endif
      </div>
    </div>
    @empty
    <div class="empty-state">
      <p class="text-4xl mb-3">🔔</p>
      <p class="empty-state-title">
        {{ $filter === 'unread' ? "No unread notifications — you're all caught up!" : 'No notifications yet.' }}
      </p>
    </div>
    @endforelse
  </div>

  @if($notifications->hasPages())
  <div class="mt-4">{{ $notifications->links() }}</div>
  @endif

</div>

@endsection
