@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-semibold mb-4">Payment cancelled</h1>
    <p class="text-gray-700">Your payment was not completed. You can try again later.</p>
    <a href="{{ url()->previous() }}" class="mt-4 inline-block text-indigo-600">Back</a>
</div>
@endsection
