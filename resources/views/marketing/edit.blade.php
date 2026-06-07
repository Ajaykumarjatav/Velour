@extends('layouts.app')
@section('title', 'Edit Campaign')
@section('page-title', 'Edit Campaign')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('marketing.update', $campaign) }}" method="POST" class="space-y-5"
              x-data="{
                scheduledAt: @js(old('scheduled_at', optional($campaign->scheduled_at)->format('Y-m-d\\TH:i'))),
                segment: @js(old('segment', $campaign->segment)),
                clientCountsBySegment: @js($counts),
                formattedSchedule() {
                    if (!this.scheduledAt) return '';
                    const d = new Date(this.scheduledAt);
                    if (Number.isNaN(d.getTime())) return '';
                    return d.toLocaleString();
                }
              }">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Campaign name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required placeholder="e.g. Spring Promotion 2025" class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label" for="mkt-edit-type-trigger">Type <span class="text-red-500">*</span></label>
                    <x-searchable-select
                        id="mkt-edit-type"
                        name="type"
                        :required="true"
                        wrapper-class="w-full min-w-0"
                        :search-url="null"
                        search-placeholder="Search…"
                        trigger-class="form-select w-full">
                        <option value="email" {{ old('type', $campaign->type) === 'email' ? 'selected' : '' }}>Email</option>
                        <option value="sms"   {{ old('type', $campaign->type) === 'sms'   ? 'selected' : '' }}>SMS</option>
                    </x-searchable-select>
                </div>
                <div>
                    <label class="form-label" for="mkt-edit-segment-trigger">Audience segment <span class="text-red-500">*</span></label>
                    <x-searchable-select
                        id="mkt-edit-segment"
                        name="segment"
                        :required="true"
                        wrapper-class="w-full min-w-0"
                        :search-url="null"
                        search-placeholder="Search segment…"
                        trigger-class="form-select w-full"
                        x-model="segment">
                        @foreach(['all'=>'All clients','active'=>'Active (visited in 90d)','lapsed'=>'Lapsed (no visit 90d+)','birthday'=>'Birthday this month','new'=>'New clients (30d)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('segment', $campaign->segment) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-searchable-select>
                </div>
            </div>
            <div>
                <label class="form-label">Subject line <span class="text-muted">(email only)</span></label>
                <input type="text" name="subject" value="{{ old('subject', $campaign->subject) }}" placeholder="e.g. You deserve a treat" class="form-input">
            </div>
            <div>
                <label class="form-label">Message body <span class="text-red-500">*</span></label>
                <textarea name="body" rows="8" required placeholder="Write your message here…"
                          class="form-textarea font-mono">{{ old('body', $campaign->body) }}</textarea>
                <p class="form-hint">Available variables:
                    <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">@{{first_name}}</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">@{{salon_name}}</code>
                    <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">@{{booking_link}}</code>
                </p>
            </div>
            <div>
                <label class="form-label">Schedule send <span class="text-muted">(leave blank to save as draft)</span></label>
                <input type="datetime-local" name="scheduled_at" x-model="scheduledAt" value="{{ old('scheduled_at', optional($campaign->scheduled_at)->format('Y-m-d\TH:i')) }}" class="form-input w-auto">
                <p class="form-hint mt-1" x-show="scheduledAt">
                    Your post will be sent on: <span class="font-medium text-heading" x-text="formattedSchedule()"></span>
                </p>
            </div>
            <div class="bg-velour-50 dark:bg-velour-900/20 border border-velour-100 dark:border-velour-800 rounded-xl p-4">
                <p class="text-sm text-velour-700 dark:text-velour-300">
                    <strong x-text="clientCountsBySegment[segment] ?? 0"></strong> clients currently opted in to marketing.
                </p>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save changes</button>
                <a href="{{ route('marketing.show', $campaign) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
