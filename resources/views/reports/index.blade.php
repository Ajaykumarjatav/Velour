@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('content')

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach([
        ['revenue',      '💰', 'Revenue',      'Track daily, weekly and monthly income'],
        ['appointments', '📅', 'Appointments', 'Booking volumes and completion rates'],
        ['staff',        '👤', 'Staff',        'Performance, revenue and bookings per team member'],
        ['clients',      '🧑', 'Clients',      'New vs returning, top spenders'],
        ['services',     '✂️', 'Services',     'Most popular and highest earning services'],
    ] as [$type, $icon, $title, $desc])
    <a href="{{ route('reports.show', $type) }}"
       class="card p-6 hover:shadow-md hover:border-velour-400 dark:hover:border-velour-600 transition-all group">
        <div class="text-3xl mb-3">{{ $icon }}</div>
        <h3 class="font-semibold text-heading group-hover:text-velour-600 dark:group-hover:text-velour-400 transition-colors">{{ $title }}</h3>
        <p class="text-sm text-muted mt-1">{{ $desc }}</p>
        <p class="text-xs text-link font-medium mt-4">View report →</p>
    </a>
    @endforeach
</div>

@endsection
