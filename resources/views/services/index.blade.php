@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')
@section('content')

<div class="flex justify-end mb-6">
    <a href="{{ route('services.create') }}" class="btn-primary">+ Add Service</a>
</div>

<div class="space-y-6">
    @forelse($categories as $cat)
    <div class="table-wrap">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 flex items-center justify-between">
            <h3 class="font-semibold text-heading">{{ $cat->name }}</h3>
            <span class="text-xs text-muted">{{ $cat->services->count() }} services</span>
        </div>
        <table class="data-table">
            <tbody>
            @foreach($cat->services as $svc)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $svc->color ?? '#7C3AED' }}"></div>
                        <p class="font-medium text-heading">{{ $svc->name }}</p>
                    </div>
                    @if($svc->description)<p class="text-xs text-muted mt-0.5 pl-6">{{ Str::limit($svc->description, 80) }}</p>@endif
                </td>
                <td class="text-muted">{{ $svc->duration_minutes }} min</td>
                <td class="font-semibold text-heading">£{{ number_format($svc->price, 2) }}</td>
                <td>
                    <span class="{{ $svc->is_active ? 'badge-green' : 'badge-gray' }}">
                        {{ $svc->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('services.edit', $svc->id) }}" class="text-xs text-link font-medium">Edit</a>
                        <form action="{{ route('services.destroy', $svc->id) }}" method="POST"
                              onsubmit="return confirm('Delete this service?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @empty
    @endforelse

    @if($uncategorised->count())
    <div class="table-wrap">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60">
            <h3 class="font-semibold text-heading">Uncategorised</h3>
        </div>
        <table class="data-table">
            <tbody>
            @foreach($uncategorised as $svc)
            <tr>
                <td class="font-medium text-heading">{{ $svc->name }}</td>
                <td class="text-muted">{{ $svc->duration_minutes }} min</td>
                <td class="font-semibold text-heading">£{{ number_format($svc->price, 2) }}</td>
                <td class="text-right">
                    <a href="{{ route('services.edit', $svc->id) }}" class="text-xs text-link font-medium">Edit</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($categories->isEmpty() && $uncategorised->isEmpty())
    <div class="card">
        <div class="empty-state">
            <p class="empty-state-title">No services yet</p>
            <a href="{{ route('services.create') }}" class="btn-primary mt-4">Add your first service</a>
        </div>
    </div>
    @endif
</div>

@endsection
