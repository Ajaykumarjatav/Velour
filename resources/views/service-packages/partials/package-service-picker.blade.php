{{--
  Clear package builder: catalog (left) → package contents (right), search, add/remove, reorder.
  Expects: $servicesPayload (array), $initialSelectedIds (array of int)
--}}
@php
    $pickerId = 'pkg-svc-' . substr(md5(json_encode($servicesPayload) . json_encode($initialSelectedIds)), 0, 8);
@endphp

<div id="{{ $pickerId }}"
     class="rounded-xl border-2 border-velour-300/80 dark:border-velour-700/80 bg-white dark:bg-gray-900/40 p-4 space-y-4"
     x-data="packageServicePicker(@js($servicesPayload), @js($initialSelectedIds))">

    <div class="flex flex-col gap-1">
        <p class="font-semibold text-heading text-base">Add services to this package</p>
        <p class="text-sm text-muted leading-relaxed">
            Use <span class="font-medium text-body">Add</span> to put services into the bundle. They appear in the <strong>In this package</strong> list in order (first = first step of the visit).
            You need <strong>at least two</strong> services. Search filters the left column only.
        </p>
    </div>

    <div>
        <label class="form-label text-sm mb-1" :for="'{{ $pickerId }}-search'">Search services</label>
        <input type="search"
               :id="'{{ $pickerId }}-search'"
               x-model="q"
               placeholder="Filter by name…"
               class="form-input w-full"
               autocomplete="off">
    </div>

    <div class="grid md:grid-cols-2 gap-4 min-h-[14rem]">
        {{-- Available --}}
        <div class="flex flex-col rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/50 min-h-0">
            <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold uppercase tracking-wide text-muted">
                Your catalog · <span x-text="availableList().length"></span> shown
            </div>
            <div class="flex-1 overflow-y-auto max-h-72 p-2 space-y-1">
                <p x-show="availableList().length === 0" x-cloak class="text-sm text-muted px-2 py-4 text-center">No services match this filter, or all are already in the package.</p>
                <template x-for="s in availableList()" :key="'av-' + s.id">
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-body truncate" x-text="s.name"></p>
                            <p class="text-xs text-muted" x-text="s.priceLabel"></p>
                        </div>
                        <button type="button"
                                @click="add(s.id)"
                                class="shrink-0 text-xs font-semibold px-2.5 py-1.5 rounded-lg bg-velour-600 text-white hover:bg-velour-700">
                            Add
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- In package --}}
        <div class="flex flex-col rounded-xl border-2 border-dashed border-velour-400/60 dark:border-velour-600/50 bg-velour-50/40 dark:bg-velour-950/20 min-h-0">
            <div class="px-3 py-2 border-b border-velour-200/80 dark:border-velour-800 flex items-center justify-between gap-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-velour-800 dark:text-velour-200">In this package</span>
                <span class="text-xs font-bold"
                      :class="selectedIds.length >= 2 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400'"
                      x-text="selectedIds.length + ' / min 2'"></span>
            </div>
            <div class="flex-1 overflow-y-auto max-h-72 p-2 space-y-1">
                <p x-show="selectedIds.length === 0" x-cloak class="text-sm text-muted px-2 py-6 text-center">Nothing added yet. Choose services from the left and click <strong>Add</strong>.</p>
                <template x-for="(id, index) in selectedIds" :key="'in-' + id">
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-gray-800 border border-velour-100 dark:border-velour-900/40">
                        <span class="text-xs text-muted w-5 shrink-0 font-mono" x-text="index + 1"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-body truncate" x-text="nameFor(id)"></p>
                            <p class="text-xs text-muted" x-text="priceFor(id)"></p>
                        </div>
                        <div class="flex flex-col gap-0.5 shrink-0">
                            <button type="button" @click="moveUp(index)" :disabled="index === 0" class="text-xs px-1 rounded disabled:opacity-30" title="Move up">↑</button>
                            <button type="button" @click="moveDown(index)" :disabled="index === selectedIds.length - 1" class="text-xs px-1 rounded disabled:opacity-30" title="Move down">↓</button>
                        </div>
                        <button type="button"
                                @click="remove(id)"
                                class="text-xs font-medium text-red-600 hover:text-red-700 px-2 py-1">
                            Remove
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <template x-for="id in selectedIds" :key="'hidden-' + id">
        <input type="hidden" name="service_ids[]" :value="id">
    </template>

    <p x-show="selectedIds.length > 0 && selectedIds.length < 2" class="text-sm text-amber-700 dark:text-amber-300">
        Add at least one more service before saving.
    </p>
</div>

@push('scripts')
<script>
function packageServicePicker(catalog, initialIds) {
    const byId = Object.fromEntries((catalog || []).map(s => [s.id, s]));
    return {
        q: '',
        catalog: catalog || [],
        selectedIds: Array.isArray(initialIds)
            ? initialIds.map(id => parseInt(id, 10)).filter(id => byId[id] !== undefined)
            : [],
        availableList() {
            const term = (this.q || '').trim().toLowerCase();
            return this.catalog.filter(s => {
                if (this.selectedIds.includes(s.id)) return false;
                if (!term) return true;
                return (s.name || '').toLowerCase().includes(term) || String(s.id).includes(term);
            });
        },
        add(id) {
            id = parseInt(id, 10);
            if (!byId[id] || this.selectedIds.includes(id)) return;
            this.selectedIds.push(id);
        },
        remove(id) {
            id = parseInt(id, 10);
            this.selectedIds = this.selectedIds.filter(i => i !== id);
        },
        moveUp(index) {
            if (index <= 0) return;
            const arr = [...this.selectedIds];
            [arr[index - 1], arr[index]] = [arr[index], arr[index - 1]];
            this.selectedIds = arr;
        },
        moveDown(index) {
            if (index >= this.selectedIds.length - 1) return;
            const arr = [...this.selectedIds];
            [arr[index], arr[index + 1]] = [arr[index + 1], arr[index]];
            this.selectedIds = arr;
        },
        nameFor(id) {
            const s = byId[id];
            return s ? s.name : ('#' + id);
        },
        priceFor(id) {
            const s = byId[id];
            return s ? s.priceLabel : '';
        },
    };
}
</script>
@endpush
