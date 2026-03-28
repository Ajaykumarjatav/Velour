{{-- resources/views/reports/_filter.blade.php --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
    <form action="{{ route('reports.show', $type) }}" method="GET" class="flex gap-2 flex-wrap">
        <input type="date" name="from" value="{{ $from }}"
               class="px-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">
        <input type="date" name="to" value="{{ $to }}"
               class="px-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">
        <button type="submit"
                class="px-4 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
            Apply
        </button>
    </form>
    <a href="{{ route('reports.index') }}" class="text-sm text-muted hover:text-gray-600">â† All Reports</a>
</div>

