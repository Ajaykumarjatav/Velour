@extends('layouts.app')
@section('title', 'Edit Client')
@section('page-title', 'Edit Client')
@section('content')

<div class="max-w-2xl">
    <div class="card p-6">
        <form action="{{ route('clients.update', $client->id) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Name</label>
                <input type="text" name="name"
                       value="{{ old('name', trim($client->first_name.' '.$client->last_name)) }}"
                       class="form-input @error('name') form-input-error @enderror" autocomplete="name"
                       placeholder="Client name (optional)">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Mobile <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $client->phone) }}" required
                           class="form-input @error('phone') form-input-error @enderror" autocomplete="tel">
                    @error('phone')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="form-input @error('email') form-input-error @enderror">
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-textarea @error('notes') form-input-error @enderror">{{ old('notes', $client->notes) }}</textarea>
                @error('notes')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @if(isset($loyaltyTiers) && $loyaltyTiers->isNotEmpty())
            <div>
                <label class="form-label" for="client-edit-loyalty-trigger">Loyalty plan</label>
                <x-searchable-select
                    id="client-edit-loyalty"
                    name="loyalty_tier_id"
                    wrapper-class="w-full min-w-0"
                    :search-url="null"
                    search-placeholder="Search plan…"
                    trigger-class="form-select w-full">
                    <option value="">— None —</option>
                    @foreach($loyaltyTiers as $tier)
                        <option value="{{ $tier->id }}" {{ (string) old('loyalty_tier_id', $client->loyalty_tier_id) === (string) $tier->id ? 'selected' : '' }}>{{ $tier->name }}</option>
                    @endforeach
                </x-searchable-select>
                <p class="form-hint">Manage plans under <a href="{{ route('service-packages.index', ['section' => 'loyalty']) }}" class="text-velour-600 dark:text-velour-400 font-medium hover:underline">Plans/Packages → Loyalty plans</a>.</p>
            </div>
            @endif
            <div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="marketing_consent" value="1" {{ old('marketing_consent', $client->marketing_consent) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600 mt-1 shrink-0">
                    <span class="flex-1 min-w-0">
                        <span class="inline-flex items-center gap-1.5 text-sm font-medium text-body">
                            Marketing consent
                            <x-marketing-consent-help mode="tooltip" />
                        </span>
                        <x-marketing-consent-help mode="hint" class="mt-1.5" />
                    </span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('clients.show', $client->id) }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
