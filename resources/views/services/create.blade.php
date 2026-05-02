@extends('layouts.app')
@section('title', isset($service) ? 'Edit Service' : 'New Service')
@section('page-title', isset($service) ? 'Edit Service' : 'Add Service')
@section('content')

<div class="max-w-xl">
    <div class="card p-6">
        @php $action = isset($service) ? route('services.update', $service->id) : route('services.store'); @endphp
        <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @if(isset($service)) @method('PUT') @endif

            <div>
                <label class="form-label">Service name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $service?->name ?? '') }}" required
                       class="form-input @error('name') form-input-error @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            @if($assignedBusinessTypes->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800 px-4 py-3 text-sm text-amber-900 dark:text-amber-100">
                    Add at least one business type for this location under <a href="{{ route('settings.index') }}?tab=salon" class="font-medium underline">Settings → Salon</a> before you can add service categories and services.
                </div>
            @else
            @if($categories->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800 px-4 py-3 text-sm text-amber-900 dark:text-amber-100 mb-2">
                    You need at least one service category. Use <span class="font-medium">+ New</span> below or <a href="{{ route('service-categories.index') }}" class="font-medium underline">Manage Categories</a>.
                </div>
            @endif
            <div>
                <label class="form-label">Photo <span class="text-muted font-normal">(optional)</span></label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="form-input text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-velour-50 file:text-velour-700 dark:file:bg-velour-900/40 dark:file:text-velour-200">
                <p class="form-hint">JPG, PNG or WebP · max 2&nbsp;MB</p>
                @error('image')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <p class="form-hint mb-2">Business type is set on the category — pick the category that matches this service.</p>
                <div class="flex items-center gap-2">
                    <select name="category_id" required class="form-select @error('category_id') form-input-error @enderror flex-1" id="category-select">
                        <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Select a category</option>
                        @foreach($assignedBusinessTypes as $bt)
                            @php $catsForBt = $categories->where('business_type_id', $bt->id); @endphp
                            @if($catsForBt->isNotEmpty())
                            <optgroup label="{{ $bt->name }}">
                                @foreach($catsForBt as $cat)
                                <option value="{{ $cat->id }}" {{ (string) old('category_id', $service?->category_id ?? '') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </optgroup>
                            @endif
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
                           value="{{ old('duration_minutes', $service?->duration_minutes ?? 60) }}" required
                           class="form-input @error('duration_minutes') form-input-error @enderror">
                    @error('duration_minutes')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
                    <input type="number" name="price" min="0" step="0.01"
                           value="{{ old('price', $service?->price ?? '') }}" required
                           class="form-input @error('price') form-input-error @enderror">
                    @error('price')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea @error('description') form-input-error @enderror">{{ old('description', $service?->description ?? '') }}</textarea>
                @error('description')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Where is this service performed? <span class="text-red-500">*</span></label>
                <p class="form-hint mb-3">On-site = at your salon. Home = you travel to the client (offered online only when <strong>Enable home visits</strong> is on under Settings → Salon).</p>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 has-[:checked]:border-velour-400 dark:has-[:checked]:border-velour-600">
                        <input type="radio" name="service_location" value="onsite" class="mt-1 text-velour-600" {{ old('service_location', 'onsite') === 'onsite' ? 'checked' : '' }} required>
                        <span class="text-sm"><span class="font-medium text-heading">At the salon (on-site)</span><span class="block text-muted text-xs mt-0.5">Client visits your business address.</span></span>
                    </label>
                    <label class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 has-[:checked]:border-velour-400 dark:has-[:checked]:border-velour-600">
                        <input type="radio" name="service_location" value="home" class="mt-1 text-velour-600" {{ old('service_location') === 'home' ? 'checked' : '' }}>
                        <span class="text-sm"><span class="font-medium text-heading">Home visit (client’s location)</span><span class="block text-muted text-xs mt-0.5">You or your team travel to the client. @if(!($currentSalon->home_services_enabled ?? false))<span class="text-amber-700 dark:text-amber-300"> Not shown on public booking until home visits are enabled in Settings.</span>@endif</span></span>
                    </label>
                </div>
                @error('service_location')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Allowed staff roles</label>
                <p class="form-hint mb-2">The level of service dependency is determined based on the roles and responsibilities of the staff. Service dependency is defined according to each staff member’s role—select which roles may perform this service. If none are selected, any role may be assigned when linking staff to this service.</p>
                @php
                    $allowedRoles = old('allowed_roles', $service?->allowed_roles ?? []);
                    $roleOptions = ['owner', 'manager', 'stylist', 'therapist', 'receptionist', 'junior'];
                @endphp
                <div class="grid grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 bg-white dark:bg-gray-800">
                    @foreach($roleOptions as $roleOption)
                        <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20">
                            <input type="checkbox" name="allowed_roles[]" value="{{ $roleOption }}"
                                   {{ in_array($roleOption, $allowedRoles, true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span class="text-sm text-body">{{ ucfirst($roleOption) }}</span>
                        </label>
                    @endforeach
                </div>
                @error('allowed_roles')<p class="form-error">{{ $message }}</p>@enderror
                @error('allowed_roles.*')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Calendar colour</label>
                <input type="color" name="color" value="{{ old('color', $service?->color ?? '#7C3AED') }}"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $service?->is_active ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Active</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="online_booking" value="1" {{ old('online_booking', $service?->online_booking ?? true) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                    <span class="text-sm text-body">Online booking</span>
                </label>
            </div>

            @include('services.partials.form-extras', ['service' => null])

            @endif
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none" @if($assignedBusinessTypes->isEmpty()) disabled @endif>{{ isset($service) ? 'Save Changes' : 'Add Service' }}</button>
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
                <label class="form-label">Business type <span class="text-red-500">*</span></label>
                <select id="inline-cat-business-type" class="form-select">
                    @foreach($assignedBusinessTypes as $bt)
                        <option value="{{ $bt->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $bt->name }}</option>
                    @endforeach
                </select>
                <p class="form-hint mt-1">Categories are scoped to a business type; services inherit it via the category.</p>
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
function rebuildCategorySelect(categories, selectedId) {
    const select = document.getElementById('category-select');
    if (!select) return;
    select.innerHTML = '';
    const ph = document.createElement('option');
    ph.value = '';
    ph.disabled = true;
    ph.textContent = 'Select a category';
    if (!selectedId) ph.selected = true;
    select.appendChild(ph);

    let currentBt = null;
    let og = null;
    for (const c of categories) {
        const bid = String(c.business_type_id);
        if (bid !== currentBt) {
            currentBt = bid;
            og = document.createElement('optgroup');
            og.label = c.business_type_name || ('Type ' + c.business_type_id);
            select.appendChild(og);
        }
        const opt = document.createElement('option');
        opt.value = c.id;
        opt.textContent = c.name;
        if (String(selectedId) === String(c.id)) {
            opt.selected = true;
            ph.selected = false;
        }
        og.appendChild(opt);
    }
}

async function saveCategory() {
    const name  = document.getElementById('new-cat-name').value.trim();
    const color = document.getElementById('new-cat-color').value;
    const err   = document.getElementById('cat-error');
    const btEl  = document.getElementById('inline-cat-business-type');
    const business_type_id = btEl && btEl.value ? parseInt(btEl.value, 10) : null;
    if (!name) { err.textContent = 'Name is required.'; err.classList.remove('hidden'); return; }
    if (!business_type_id) { err.textContent = 'Select a business type for this category.'; err.classList.remove('hidden'); return; }
    err.classList.add('hidden');

    const res  = await fetch('{{ route('service-categories.store') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ name, color, business_type_id }),
    });
    const data = await res.json();
    if (!res.ok) { err.textContent = data.message ?? 'Error saving category.'; err.classList.remove('hidden'); return; }

    const newCat = data.categories.find(c => c.name === name && parseInt(c.business_type_id, 10) === business_type_id);
    const selectedId = newCat ? newCat.id : null;
    rebuildCategorySelect(data.categories, selectedId);

    document.getElementById('inline-cat-modal').classList.add('hidden');
    document.getElementById('new-cat-name').value = '';
}
</script>
