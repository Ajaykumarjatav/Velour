@props([
    'label',
    'name',
    'required' => false,
    'type' => 'client',
    'selectId',
    'selectClass' => 'form-select',
    'errorName' => null,
    /** Active loyalty tiers for quick-create client modal (optional). */
    'clientLoyaltyTiers' => null,
    /** Override remote search URL; null = default by type; empty string = local filter only. */
    'searchUrl' => false,
    /**
     * Show the + quick-create button. Default: hidden for client fields when the user is stylist-scoped (staff panel).
     */
    'showQuickCreate' => null,
])

@php
    $errorName = $errorName ?? $name;
    $resolvedSearchUrl = $searchUrl !== false
        ? ($searchUrl ?: null)
        : match ($type) {
            'client' => route('lookup.clients'),
            'staff' => route('lookup.staff'),
            default => null,
        };
    $searchPlaceholder = match ($type) {
        'client' => 'Search name or mobile…',
        'staff' => 'Search staff…',
        default => 'Search…',
    };

    $isStylistScoped = auth()->check() && auth()->user()->dashboardScopedStaffId() !== null;
    $showQuickCreate = $showQuickCreate ?? ! ($type === 'client' && $isStylistScoped);

    $hint = match (true) {
        ! $showQuickCreate && $type === 'client' => null,
        in_array($type, ['client', 'staff'], true) && $showQuickCreate => 'No match? Use + to add new.',
        default => null,
    };
@endphp

<div class="space-y-0">
    <div class="flex items-end gap-2">
        <x-searchable-select
            :id="$selectId"
            :name="$name"
            :label="$label"
            :required="$required"
            :error-name="$errorName"
            :search-url="$resolvedSearchUrl"
            :search-placeholder="$searchPlaceholder"
            :hint="$hint"
            :trigger-class="$selectClass"
        >
            {{ $slot }}
        </x-searchable-select>
        @if($showQuickCreate)
            <x-relation-quick-create-trigger :type="$type" :select-id="$selectId" :client-loyalty-tiers="$clientLoyaltyTiers" />
        @endif
    </div>
</div>
