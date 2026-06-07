@extends('layouts.app')
@section('title', 'Staff Report')
@section('page-title', 'Staff Performance Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Staff member</th>
            <th class="hidden sm:table-cell">Role</th>
            <th class="text-right">Appointments</th>
            <th class="text-right">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @forelse($staff as $member)
        <tr>
            <td>
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         style="background-color: {{ $member->color ?? '#7C3AED' }}">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <a href="{{ route('staff.show', $member->id) }}" class="font-semibold text-link hover:underline">{{ $member->name }}</a>
                </div>
            </td>
            <td class="hidden sm:table-cell text-muted capitalize">{{ str_replace('_',' ',$member->role) }}</td>
            <td class="text-right font-semibold text-heading">{{ $member->appointment_count ?? 0 }}</td>
            <td class="text-right font-bold text-heading">@money($member->total_revenue ?? 0)</td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
