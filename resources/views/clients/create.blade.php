@extends('layouts.app')
@section('title', 'New Client')
@section('page-title', 'Add Client')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('clients.store') }}" method="POST" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">First name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Last name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="form-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" class="form-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Date of birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Prefer not to say</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="non_binary" {{ old('gender') === 'non_binary' ? 'selected' : '' }}>Non-binary</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">Address</label>
                <input type="text" name="address" value="{{ old('address') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-textarea">{{ old('notes') }}</textarea>
            </div>
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_consent" value="1" {{ old('marketing_consent') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Client consents to marketing communications</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Add Client</button>
                <a href="{{ route('clients.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
