@php
    $clientPickerValue = old('client_id', $selectedClientId ?? '');
    $isStylistScoped = auth()->check() && auth()->user()->dashboardScopedStaffId() !== null;
    $pickerHint = $isStylistScoped
        ? null
        : 'No match? Use + to add a new client.';
@endphp

<x-searchable-select
    :id="$selectId"
    name="client_id"
    label="Client"
    :required="true"
    error-name="client_id"
    :search-url="route('lookup.clients')"
    search-placeholder="Search by name or mobile…"
    :hint="$pickerHint"
>
    <option value="">Select a client…</option>
    @foreach($clients as $client)
        <option value="{{ $client->id }}" {{ (string) $clientPickerValue === (string) $client->id ? 'selected' : '' }}>
            {{ $client->first_name }} {{ $client->last_name }}{{ $client->phone ? ' — '.$client->phone : '' }}
        </option>
    @endforeach
</x-searchable-select>
