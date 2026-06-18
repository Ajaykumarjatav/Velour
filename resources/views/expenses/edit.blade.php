@extends('layouts.app')
@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('content')
<div class="max-w-2xl">
    <div class="card p-6">
        @include('expenses._form')
    </div>
</div>
@endsection
