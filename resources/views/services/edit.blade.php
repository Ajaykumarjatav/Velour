@extends('layouts.app')
@section('title', 'Edit Service')
@section('page-title', 'Edit Service')
@section('content')

<div class="max-w-xl">
    <div class="card p-6">
        <form action="{{ route('services.update', $service->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Service name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $service->name) }}" required
                       class="form-input @error('name') form-input-error @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Category</label>
                <div class="flex items-center gap-2">
                    <select name="category_id" class="form-select @error('category_id') form-input-error @enderror flex-1" id="category-select">
                        <option value="">No category</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $service->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="document.getElementById('inline-cat-modal').classList.remove('hidden')"
                            class="text-sm text-velour-600 dark:text-velour-400 font-medium whitespace-nowrap hover:underline">+ New</button>
                </div>
                @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Duration (min) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" min="5" max="480"
                           value="{{ old('duration_minutes', $service->duration_minutes) }}" required
                           class="form-input @error('duration_minutes') form-input-error @enderror">
                    @error('duration_minutes')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" min="0" step="0.01"
                           value="{{ old('price', $service->price) }}" required
                           class="form-input @error('price') form-input-error @enderror">
                    @error('price')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea @error('description') form-input-error @enderror">{{ old('description', $service->description) }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Calendar colour</label>
                <input type="color" name="color" value="{{ old('color', $service->color ?? '#7C3AED') }}"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', ($service->status ?? 'active') === 'active') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="online_booking" value="1" {{ old('online_booking', $service->online_booking ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Online booking</span>
                </label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Save Changes</button>
                <a href="{{ route('services.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection

{{-- Inline Add Category Modal --}}
<div id="inline-cat-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-heading text-lg mb-4">Add Category</h3>
        <div class="space-y-4">
            <div>
                <label class="form-label">Name <span class="text-red-500">*</span></label>
                <input type="text" id="new-cat-name" class="form-input" placeholder="e.g. Hair, Nails, Skin">
            </div>
            <div>
                <label class="form-label">Colour</label>
                <input type="color" id="new-cat-color" value="#7c3aed"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div id="cat-error" class="hidden text-sm text-red-500"></div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="saveCategory()" class="btn-primary flex-1">Add Category</button>
                <button type="button" onclick="document.getElementById('inline-cat-modal').classList.add('hidden')" class="btn-outline">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
async function saveCategory() {
    const name  = document.getElementById('new-cat-name').value.trim();
    const color = document.getElementById('new-cat-color').value;
    const err   = document.getElementById('cat-error');
    if (!name) { err.textContent = 'Name is required.'; err.classList.remove('hidden'); return; }
    err.classList.add('hidden');

    const res  = await fetch('{{ route('service-categories.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ name, color }),
    });
    const data = await res.json();
    if (!res.ok) { err.textContent = data.message ?? 'Error saving category.'; err.classList.remove('hidden'); return; }

    const select = document.getElementById('category-select');
    select.innerHTML = '<option value="">No category</option>';
    data.categories.forEach(cat => {
        const opt = new Option(cat.name, cat.id);
        select.add(opt);
    });
    const last = data.categories[data.categories.length - 1];
    if (last) select.value = last.id;

    document.getElementById('inline-cat-modal').classList.add('hidden');
    document.getElementById('new-cat-name').value = '';
}
</script>
