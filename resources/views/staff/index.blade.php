@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff')
@section('content')

<div class="flex justify-end mb-6">
    <a href="{{ route('staff.create') }}" class="btn-primary">+ Add Staff</a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($staff as $member)
    <div class="card p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0"
                 style="background-color: {{ $member->color ?? '#7C3AED' }}">
                {{ strtoupper(substr($member->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-heading truncate">{{ $member->name }}</h3>
                <p class="text-xs text-muted capitalize mt-0.5">{{ str_replace('_',' ',$member->role) }}</p>
                @if($member->email)<p class="text-xs text-muted mt-1 truncate">{{ $member->email }}</p>@endif
            </div>
            <span class="flex-shrink-0 {{ $member->is_active ? 'badge-green' : 'badge-gray' }}">
                {{ $member->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <div class="text-center">
                <p class="text-lg font-bold text-heading">{{ $member->total_appointments ?? 0 }}</p>
                <p class="text-xs text-muted">Total</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-heading">{{ $member->completed_appointments ?? 0 }}</p>
                <p class="text-xs text-muted">Completed</p>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <a href="{{ route('staff.show', $member->id) }}" class="btn-outline flex-1 text-center text-sm py-2">View</a>
            <a href="{{ route('staff.edit', $member->id) }}" class="btn-secondary flex-1 text-center text-sm py-2">Edit</a>
        </div>
    </div>
    @empty
    <div class="col-span-full empty-state">
        <p class="empty-state-title">No staff members yet</p>
        <a href="{{ route('staff.create') }}" class="btn-primary mt-4">Add your first staff member</a>
    </div>
    @endforelse
</div>
@endsection
