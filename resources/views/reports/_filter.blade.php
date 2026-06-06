{{-- resources/views/reports/_filter.blade.php --}}
@php
    $salonToday = isset($salon)
        ? \App\Support\SalonTime::todayDateString($salon)
        : now()->toDateString();
@endphp
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6 overflow-visible">
    <form action="{{ route('reports.show', $type) }}" method="GET" class="flex flex-wrap items-end gap-3 min-w-0 overflow-visible">
        <div class="flex flex-col gap-1.5 w-full sm:w-auto sm:min-w-[14rem] sm:max-w-xs shrink-0">
            <label class="form-label text-xs mb-0">Date range</label>
            <x-date-range-picker
                :from-value="$from"
                :to-value="$to"
                :salon-today="$salonToday"
                class="w-full" />
        </div>
        <button type="submit" class="btn-primary shrink-0">Apply</button>
    </form>
    <a href="{{ route('reports.index') }}" class="text-sm text-link font-medium whitespace-nowrap pb-2.5">← All reports</a>
</div>
