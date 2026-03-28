@extends('layouts.app')
@section('title', 'Service Categories')
@section('page-title', 'Service Categories')
@section('content')

<div class="flex justify-between items-center mb-6">
    <a href="{{ route('services.index') }}" class="text-sm text-muted hover:text-body">← Back to Services</a>
    <button onclick="document.getElementById('add-cat-modal').classList.remove('hidden')" class="btn-primary">+ Add Category</button>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 text-sm">{{ session('success') }}</div>
@endif

<div class="card">
    @forelse($categories as $cat)
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800 last:border-0">
        <div class="flex items-center gap-3">
            <div class="w-4 h-4 rounded-full flex-shrink-0" style="background:{{ $cat->color ?? '#7c3aed' }}"></div>
            <div>
                <p class="font-medium text-heading">{{ $cat->name }}</p>
                <p class="text-xs text-muted">{{ $cat->services_count }} {{ Str::plural('service', $cat->services_count) }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ $cat->color ?? '#7c3aed' }}')"
                    class="text-xs text-link font-medium">Edit</button>
            <form action="{{ route('service-categories.destroy', $cat->id) }}" method="POST"
                  onsubmit="return confirm('Delete this category? Services will become uncategorised.')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 font-medium">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="px-6 py-12 text-center text-muted text-sm">No categories yet. Add one to organise your services.</div>
    @endforelse
</div>

{{-- Add Modal --}}
<div id="add-cat-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-heading text-lg mb-4">Add Category</h3>
        <form action="{{ route('service-categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="form-input" placeholder="e.g. Hair, Nails, Skin">
            </div>
            <div>
                <label class="form-label">Colour</label>
                <input type="color" name="color" value="#7c3aed"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Add Category</button>
                <button type="button" onclick="document.getElementById('add-cat-modal').classList.add('hidden')" class="btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-cat-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6">
        <h3 class="font-semibold text-heading text-lg mb-4">Edit Category</h3>
        <form id="edit-cat-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Name <span class="text-red-500">*</span></label>
                <input type="text" id="edit-cat-name" name="name" required class="form-input">
            </div>
            <div>
                <label class="form-label">Colour</label>
                <input type="color" id="edit-cat-color" name="color"
                       class="h-10 w-20 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 cursor-pointer bg-white dark:bg-gray-800">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1">Save Changes</button>
                <button type="button" onclick="document.getElementById('edit-cat-modal').classList.add('hidden')" class="btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, name, color) {
    document.getElementById('edit-cat-name').value  = name;
    document.getElementById('edit-cat-color').value = color;
    document.getElementById('edit-cat-form').action = '/service-categories/' + id;
    document.getElementById('edit-cat-modal').classList.remove('hidden');
}
</script>
@endsection
