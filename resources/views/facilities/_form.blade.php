@php
    $isEdit = $facility->exists;
@endphp
<div class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="sm:col-span-2">
            <label class="form-label">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $facility->name) }}" required maxlength="120" class="form-input">
            @error('name')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Category <span class="text-red-500">*</span></label>
            <input type="text" name="category" value="{{ old('category', $facility->category) }}" required maxlength="64" class="form-input" placeholder="e.g. Treatment room, Styling floor">
            @error('category')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Type <span class="text-red-500">*</span></label>
            <select name="kind" class="form-select" required>
                @foreach(\App\Models\Facility::kindOptions() as $val => $label)
                    <option value="{{ $val }}" @selected(old('kind', $facility->kind) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('kind')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Status <span class="text-red-500">*</span></label>
            <select name="status" class="form-select" required>
                @foreach(\App\Models\Facility::statusOptions() as $val => $label)
                    <option value="{{ $val }}" @selected(old('status', $facility->status) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Sort order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $facility->sort_order ?? 0) }}" min="0" max="65535" class="form-input">
            @error('sort_order')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Occupancy (current) <span class="text-red-500">*</span></label>
            <input type="number" name="occupancy_current" value="{{ old('occupancy_current', $facility->occupancy_current ?? 0) }}" min="0" required class="form-input">
            @error('occupancy_current')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Occupancy (capacity) <span class="text-red-500">*</span></label>
            <input type="number" name="occupancy_capacity" value="{{ old('occupancy_capacity', $facility->occupancy_capacity ?? 0) }}" min="0" required class="form-input">
            <p class="form-hint">Use 0 if occupancy does not apply — the bar will be hidden.</p>
            @error('occupancy_capacity')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Last maintenance</label>
            <input type="date" name="last_maintenance_on" value="{{ old('last_maintenance_on', $facility->last_maintenance_on?->format('Y-m-d')) }}" class="form-input">
            @error('last_maintenance_on')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Next maintenance</label>
            <input type="date" name="next_maintenance_on" value="{{ old('next_maintenance_on', $facility->next_maintenance_on?->format('Y-m-d')) }}" class="form-input">
            @error('next_maintenance_on')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="form-label">Equipment &amp; features</label>
            <textarea name="equipment_features" rows="4" maxlength="5000" class="form-textarea" placeholder="One item per line, e.g.&#10;Hydraulic chairs (4)&#10;Dryers (2)">{{ old('equipment_features', implode("\n", $facility->equipment_features ?? [])) }}</textarea>
            @error('equipment_features')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="sm:col-span-2">
            <label class="form-label">Internal notes</label>
            <textarea name="notes" rows="2" maxlength="2000" class="form-textarea">{{ old('notes', $facility->notes) }}</textarea>
            @error('notes')<p class="form-error mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="submit" class="btn-primary">{{ $isEdit ? 'Save changes' : 'Create facility' }}</button>
        <a href="{{ $isEdit ? route('facilities.show', $facility) : route('facilities.index') }}" class="btn-outline">Cancel</a>
    </div>
</div>
