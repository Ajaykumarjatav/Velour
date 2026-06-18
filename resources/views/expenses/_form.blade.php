@php
    $expense = $expense ?? null;
    $prefill = $prefill ?? [];
    $action = $expense ? route('expenses.update', $expense) : route('expenses.store');
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($expense) @method('PUT') @endif
    <input type="hidden" name="status" value="{{ old('status', $expense->status ?? 'recorded') }}">

    <div>
        <label class="form-label">Title <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $expense->title ?? '') }}" required
               class="form-input @error('title') form-input-error @enderror">
        @error('title')<p class="form-error">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Category <span class="text-red-500">*</span></label>
            <select name="category_id" required class="form-select @error('category_id') form-input-error @enderror">
                @foreach($categories as $cat)
                    @php $m = \App\Support\ExpenseCategoryUi::meta($cat->slug, $cat->name); @endphp
                    <option value="{{ $cat->id }}" @selected((string) old('category_id', $expense->category_id ?? '') === (string) $cat->id)>
                        {{ $m['icon'] }} {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Amount ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
            <input type="number" name="amount" min="0.01" step="0.01" required
                   value="{{ old('amount', $expense->amount ?? '') }}"
                   class="form-input @error('amount') form-input-error @enderror">
            @error('amount')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Expense date <span class="text-red-500">*</span></label>
            <input type="date" name="expense_date" required
                   value="{{ old('expense_date', isset($expense) ? $expense->expense_date->toDateString() : now()->toDateString()) }}"
                   class="form-input @error('expense_date') form-input-error @enderror">
            @error('expense_date')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label">Payment method <span class="text-red-500">*</span></label>
            <select name="payment_method" required class="form-select">
                @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                    @php $pm = \App\Support\ExpensePaymentUi::meta($key); @endphp
                    <option value="{{ $key }}" @selected(old('payment_method', $expense->payment_method ?? 'cash') === $key)>
                        {{ $pm['icon'] }} {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label">Staff</label>
            <select name="staff_id" class="form-select">
                <option value="">No staff linked</option>
                @foreach($staffList as $st)
                    <option value="{{ $st->id }}" @selected((string) old('staff_id', $expense->staff_id ?? '') === (string) $st->id)>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Vendor / paid to</label>
            <input type="text" name="vendor" value="{{ old('vendor', $expense->vendor ?? '') }}" class="form-input">
        </div>
    </div>

    <div>
        <label class="form-label">Additional details</label>
        <textarea name="notes" rows="3" class="form-input resize-y">{{ old('notes', $expense->notes ?? '') }}</textarea>
    </div>

    <div>
        <label class="form-label">Receipt (JPG, PNG, PDF)</label>
        <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" class="form-input">
        @if($expense?->receipt_path)
            <p class="text-xs text-muted mt-1">Current file on record</p>
        @endif
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary">Save changes</button>
        <a href="{{ route('expenses.index') }}" class="btn-outline">Cancel</a>
    </div>
</form>
