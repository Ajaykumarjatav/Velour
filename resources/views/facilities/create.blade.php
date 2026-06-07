@extends('layouts.app')
@section('title', 'Add facility')
@section('page-title', 'Add facility')

@section('content')
<div class="max-w-3xl">
    <div class="card p-6 sm:p-7">
        <p class="text-sm text-muted mb-5">Track rooms, stations, and areas — occupancy and maintenance at a glance.</p>
        <form action="{{ route('facilities.store') }}" method="POST" class="space-y-0">
            @csrf
            @include('facilities._form', ['facility' => $facility])
        </form>
    </div>
</div>
@endsection
