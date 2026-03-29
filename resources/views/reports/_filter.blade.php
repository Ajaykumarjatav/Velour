{{-- resources/views/reports/_filter.blade.php --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <form action="{{ route('reports.show', $type) }}" method="GET" class="flex flex-wrap items-center gap-3 min-w-0">
        <input type="date" name="from" value="{{ $from }}" class="form-input w-full sm:w-auto min-w-[160px]">
        <input type="date" name="to" value="{{ $to }}" class="form-input w-full sm:w-auto min-w-[160px]">
        <button type="submit" class="btn-primary">Apply</button>
    </form>
    <a href="{{ route('reports.index') }}" class="text-sm text-link font-medium whitespace-nowrap">← All reports</a>
</div>
