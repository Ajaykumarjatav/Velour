@extends('layouts.app')
@section('title', 'Edit '.$facility->name)
@section('page-title', 'Edit facility')

@section('content')
<div class="max-w-3xl">
    <div class="card p-6 sm:p-7">
        <form action="{{ route('facilities.update', $facility) }}" method="POST" class="space-y-0">
            @csrf
            @method('PUT')
            @include('facilities._form', ['facility' => $facility])
        </form>
    </div>
</div>
@endsection
