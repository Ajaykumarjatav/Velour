@extends('layouts.auth')
@section('title', 'Leave a Review')
@section('auth_container_class', 'max-w-md sm:max-w-xl')
@section('content')

<div>
    <h2 class="text-xl font-semibold text-gray-900 mb-1">{{ $salon->name }}</h2>
    @if($staff)
    <p class="text-sm text-gray-500 mb-4">You are reviewing services offered by {{ $staff->name }}.</p>
    @else
    <p class="text-sm text-gray-500 mb-4">You are reviewing your experience with this business.</p>
    @endif

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('reviews.public.submit', $reviewLink->token) }}" method="POST" class="space-y-4 mt-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Your name</label>
            <input type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
            @error('reviewer_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Service</label>
            <select name="service_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
                <option value="">Select service (optional)</option>
                @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) old('service_id') === (string) $service->id ? 'selected' : '' }}>
                    {{ $service->name }}
                </option>
                @endforeach
            </select>
            @error('service_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <select name="rating" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
                <option value="">Select rating</option>
                @foreach([5,4,3,2,1] as $r)
                <option value="{{ $r }}" {{ (string) old('rating') === (string) $r ? 'selected' : '' }}>{{ $r }} star{{ $r > 1 ? 's' : '' }}</option>
                @endforeach
            </select>
            @error('rating')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
            <textarea name="comment" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500" placeholder="Share your experience">{{ old('comment') }}</textarea>
            @error('comment')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="inline-flex items-center rounded-xl bg-velour-600 px-4 py-2 text-sm font-semibold text-white hover:bg-velour-700">Submit Review</button>
    </form>
</div>

@endsection

