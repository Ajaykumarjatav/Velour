@extends('layouts.app')
@section('title', 'Edit Staff Member')
@section('page-title', 'Edit Staff Member')
@section('content')

<div class="max-w-2xl pb-16">
    <div class="card p-6">
        <form action="{{ route('staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Photo</label>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <x-staff-avatar :staff="$staff" size="lg" />
                        <div class="flex-1 min-w-0 space-y-2">
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                                   class="form-input text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-velour-50 file:text-velour-700 dark:file:bg-velour-900/40 dark:file:text-velour-200">
                            <p class="form-hint">JPG, PNG or WebP · max 2&nbsp;MB</p>
                            @if($staff->avatar)
                                <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                    Remove current photo
                                </label>
                            @endif
                        </div>
                    </div>
                    @error('avatar')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="form-label">Full name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required
                           class="form-input @error('name') form-input-error @enderror">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}"
                           class="form-input @error('email') form-input-error @enderror">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" value="{{ old('phone', $staff->phone) }}"
                           class="form-input @error('phone') form-input-error @enderror">
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label" for="staff-edit-role-trigger">Role <span class="text-red-500">*</span></label>
                    <x-searchable-select
                        id="staff-edit-role"
                        name="role"
                        :required="true"
                        error-name="role"
                        wrapper-class="w-full min-w-0"
                        :search-url="null"
                        search-placeholder="Search role…"
                        trigger-class="form-select w-full @error('role') form-input-error @enderror">
                        @foreach(\App\Support\StaffJobRoles::options() as $slug => $label)
                        <option value="{{ $slug }}" {{ old('role', $staff->role) === $slug ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-searchable-select>
                </div>
                <div>
                    <label class="form-label">Commission %</label>
                    <input type="number" name="commission_rate" min="0" max="100" step="0.1"
                           value="{{ old('commission_rate', $staff->commission_rate) }}"
                           class="form-input @error('commission_rate') form-input-error @enderror">
                    @error('commission_rate')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Experience</label>
                    <input type="text" name="experience" value="{{ old('experience', $staff->experience) }}"
                           class="form-input @error('experience') form-input-error @enderror" placeholder="e.g. 5 years">
                    @error('experience')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    @php
                        $staffLangSelected = old('language_proficiency');
                        if (! is_array($staffLangSelected)) {
                            $staffLangSelected = \App\Support\LanguageProficiency::codesFromStored($staff->language_proficiency ?? '');
                        }
                    @endphp
                    @include('settings.partials.language-proficiency-field', [
                        'name' => 'language_proficiency[]',
                        'selected' => $staffLangSelected,
                    ])
                    @error('language_proficiency')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Calendar colour</label>
                    <input type="color" name="color" value="{{ old('color', $staff->color ?? '#7C3AED') }}"
                           class="w-full h-10 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer">
                </div>
                <div class="col-span-2">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" rows="3" class="form-textarea @error('bio') form-input-error @enderror">{{ old('bio', $staff->bio) }}</textarea>
                    @error('bio')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <label class="form-label">Awards &amp; accolades</label>
                    <textarea name="awards_accolades" rows="3" class="form-textarea @error('awards_accolades') form-input-error @enderror" placeholder="Optional — certifications, press, awards…">{{ old('awards_accolades', $staff->awards_accolades) }}</textarea>
                    @error('awards_accolades')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active (shows in calendar and booking)</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('staff.show', $staff->id) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
