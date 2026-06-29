@extends('layouts.app')
@section('title', 'Add Expense')
@section('page-title', 'Add Expense')

@php
    use App\Support\ExpensePaymentUi;
    $sym = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'INR');
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();
@endphp

@section('content')
<div class="max-w-2xl mx-auto pb-10"
     x-data="{
        expenseDate: @json(old('expense_date', $prefill['expense_date'] ?? $today)),
        dateMode: 'today',
        showMore: @json(
            (bool) old('vendor')
            || (bool) old('notes')
            || (bool) old('reference')
            || (bool) old('staff_id')
            || (bool) old('recurring_interval')
            || request()->hasAny(['vendor', 'title', 'amount'])
        ),
        init() {
            if (this.expenseDate === @json($today)) this.dateMode = 'today';
            else if (this.expenseDate === @json($yesterday)) this.dateMode = 'yesterday';
            else { this.dateMode = 'custom'; this.showMore = true; }
        },
        setDate(mode) {
            this.dateMode = mode;
            if (mode === 'today') this.expenseDate = @json($today);
            if (mode === 'yesterday') this.expenseDate = @json($yesterday);
        }
     }"
     x-init="init()">

    <p class="text-sm text-muted mb-5">Record a business expense — title, category, amount, and date are enough to get started.</p>

    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf
        <input type="hidden" name="status" value="recorded">

        <div>
            <label class="form-label">What was this expense for? <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', request('title')) }}" required autofocus
                   placeholder="e.g. March rent, shampoo stock"
                   class="form-input @error('title') form-input-error @enderror">
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Category <span class="text-red-500">*</span></label>
                <select name="category_id" required class="form-select @error('category_id') form-input-error @enderror">
                    <option value="">Select category</option>
                    @foreach($categories as $cat)
                        @php $m = \App\Support\ExpenseCategoryUi::meta($cat->slug, $cat->name); @endphp
                        <option value="{{ $cat->id }}" @selected((string) old('category_id', $prefill['category_id'] ?? '') === (string) $cat->id)>
                            {{ $m['icon'] }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Amount ({{ $sym }}) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" min="0.01" step="0.01" required
                       value="{{ old('amount', request('amount')) }}" placeholder="0.00"
                       class="form-input @error('amount') form-input-error @enderror">
                @error('amount')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Date <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-1.5 mb-2">
                    <button type="button" @click="setDate('today')"
                            :class="dateMode === 'today' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                            class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Today</button>
                    <button type="button" @click="setDate('yesterday')"
                            :class="dateMode === 'yesterday' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                            class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Yesterday</button>
                    <button type="button" @click="dateMode = 'custom'"
                            :class="dateMode === 'custom' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                            class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Pick date</button>
                </div>
                <input type="date" name="expense_date" x-model="expenseDate" required
                       :class="dateMode === 'custom' ? '' : 'sr-only'"
                       class="form-input w-full @error('expense_date') form-input-error @enderror">
                @error('expense_date')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Paid via <span class="text-red-500">*</span></label>
                <select name="payment_method" required class="form-select @error('payment_method') form-input-error @enderror">
                    @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                        @php $pm = ExpensePaymentUi::meta($key); @endphp
                        <option value="{{ $key }}" @selected(old('payment_method', request('payment_method', 'cash')) === $key)>
                            {{ $pm['icon'] }} {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('payment_method')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <button type="button" @click="showMore = !showMore"
                    class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline flex items-center gap-1">
                <span x-text="showMore ? 'Hide extra fields' : 'Add staff, vendor, receipt…'"></span>
                <svg class="w-4 h-4 transition-transform" :class="showMore && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div x-show="showMore" x-cloak class="space-y-4 pt-1 border-t border-gray-100 dark:border-gray-800">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Staff <span class="text-xs font-normal text-muted">(optional)</span></label>
                    <select name="staff_id" class="form-select">
                        <option value="">None</option>
                        @foreach($staffList as $st)
                            <option value="{{ $st->id }}" @selected((string) old('staff_id', $prefill['staff_id'] ?? '') === (string) $st->id)>{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Vendor / paid to</label>
                    <input type="text" name="vendor" value="{{ old('vendor', request('vendor')) }}" list="vendor-suggestions"
                           placeholder="Supplier, landlord…" class="form-input">
                    <datalist id="vendor-suggestions">
                        @foreach($vendorSuggestions as $v)
                            <option value="{{ $v }}">
                        @endforeach
                    </datalist>
                </div>
            </div>

            <div>
                <label class="form-label">Invoice / reference</label>
                <input type="text" name="reference" value="{{ old('reference') }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" placeholder="Any extra details…"
                          class="form-input resize-y">{{ old('notes') }}</textarea>
            </div>

            <div>
                <label class="form-label">Receipt <span class="text-xs font-normal text-muted">(JPG, PNG, PDF)</span></label>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="form-input">
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <label class="form-label mb-2">Repeat expense</label>
                <select name="recurring_interval" class="form-select text-sm">
                    <option value="">Does not repeat</option>
                    @foreach(\App\Models\Expense::RECURRING_INTERVALS as $key => $label)
                        <option value="{{ $key }}" @selected(old('recurring_interval') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <button type="submit" class="btn-primary">Save expense</button>
            <a href="{{ route('expenses.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>

    @if($recentExpenses->isNotEmpty())
    <div class="mt-8">
        <h2 class="text-sm font-semibold text-heading mb-3">Recent — tap to copy</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($recentExpenses as $recent)
                @php $rm = \App\Support\ExpenseCategoryUi::meta($recent->category?->slug); @endphp
                <a href="{{ route('expenses.create', [
                    'title' => $recent->title,
                    'category_id' => $recent->category_id,
                    'amount' => $recent->amount,
                    'payment_method' => $recent->payment_method,
                    'vendor' => $recent->vendor,
                ]) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm hover:border-velour-400 transition-colors">
                    <span>{{ $rm['icon'] }}</span>
                    <span class="text-body truncate max-w-[10rem]">{{ $recent->title }}</span>
                    <span class="font-semibold text-heading">@money($recent->amount)</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
