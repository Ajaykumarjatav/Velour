@extends('layouts.app')
@section('title', 'Add Expense')
@section('page-title', 'Add Expense')

@php
    use App\Support\ExpensePaymentUi;
    $sym = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'INR');
    $payments = ExpensePaymentUi::all();
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();
@endphp

@push('styles')
<style>
    .expense-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 1024px) { .expense-grid { grid-template-columns: minmax(0, 7fr) minmax(0, 3fr); } }
    .amount-input-wrap { position: relative; }
    .amount-input-wrap .currency-prefix {
        position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%);
        font-weight: 600; color: #8b5cf6; pointer-events: none;
    }
    .amount-input-wrap input { padding-left: 2rem; text-align: right; font-variant-numeric: tabular-nums; }
    .expense-dropzone { border: 2px dashed #d1d5db; transition: border-color .15s, background .15s; }
    .dark .expense-dropzone { border-color: #374151; }
    .expense-dropzone.dragover { border-color: #8b5cf6; background: rgba(139,92,246,.06); }
    .exp-amount-float { font-size: clamp(1.75rem, 4vw, 2.5rem); line-height: 1.1; }
    @keyframes exp-check-pop { 0% { transform: scale(.6); opacity: 0; } 60% { transform: scale(1.1); } 100% { transform: scale(1); opacity: 1; } }
    .exp-success-check { animation: exp-check-pop .45s ease-out; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto pb-10"
     x-data="expenseForm()"
     x-init="init()"
     @keydown.window.ctrl.s.prevent="submitForm('recorded')"
     @keydown.enter.window="handleEnter($event)">

    {{-- Top summary strip --}}
    <div class="card p-4 mb-5 border border-velour-200/50 dark:border-velour-800/40 bg-gradient-to-r from-velour-50/80 to-transparent dark:from-velour-950/30 dark:to-transparent">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-[10px] uppercase tracking-wide text-muted font-semibold">Total expense</p>
                <p class="exp-amount-float font-bold text-velour-600 dark:text-velour-400 mt-0.5" x-text="formatMoney(amount) || '—'"></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-muted font-semibold">Category</p>
                <p class="font-semibold text-heading mt-1 flex items-center gap-1.5">
                    <span x-text="selectedCategory?.meta?.icon || '—'"></span>
                    <span x-text="selectedCategory?.name || '—'"></span>
                </p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-muted font-semibold">Payment</p>
                <p class="font-semibold text-heading mt-1" x-text="paymentLabel"></p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide text-muted font-semibold">Date</p>
                <p class="font-semibold text-heading mt-1" x-text="formatDisplayDate(expenseDate)"></p>
            </div>
        </div>
    </div>

    <div class="expense-grid">
        {{-- LEFT: Form --}}
        <div class="space-y-5">
            <form x-ref="expenseFormEl" id="expense-create-form" action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data"
                  class="card p-6 space-y-5" @submit.prevent="submitForm(submitAction)">
                @csrf
                <input type="hidden" name="status" :value="submitAction">
                <input type="hidden" name="category_id" :value="categoryId">
                <input type="hidden" name="payment_method" :value="paymentMethod">
                <input type="hidden" name="expense_date" :value="expenseDate">
                <input type="hidden" name="recurring_interval" :value="recurring ? recurringInterval : ''">

                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" x-model="title" required data-field
                           placeholder="e.g. March salary, Shampoo stock purchase" class="form-input @error('title') form-input-error @enderror">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Category picker --}}
                    <div class="relative">
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <button type="button" @click="categoryOpen = !categoryOpen"
                                class="form-select w-full text-left flex items-center justify-between gap-2 @error('category_id') form-input-error @enderror">
                            <span class="flex items-center gap-2 truncate" x-show="selectedCategory">
                                <span x-text="selectedCategory?.meta?.icon"></span>
                                <span class="inline-flex text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                      :class="selectedCategory?.meta?.badge" x-text="selectedCategory?.name"></span>
                            </span>
                            <span x-show="!selectedCategory" class="text-muted">Select category</span>
                            <svg class="w-4 h-4 shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="categoryOpen" x-cloak @click.outside="categoryOpen = false"
                             class="absolute z-40 mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl py-1 max-h-64 overflow-y-auto">
                            <template x-for="cat in categories" :key="cat.id">
                                <button type="button" @click="selectCategory(cat)"
                                        class="w-full px-3 py-2.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2">
                                    <span x-text="cat.meta.icon"></span>
                                    <span class="font-medium" x-text="cat.name"></span>
                                    <span class="ml-auto text-[10px] font-semibold px-2 py-0.5 rounded-full" :class="cat.meta.badge" x-text="cat.name"></span>
                                </button>
                            </template>
                            <div x-show="categories.length === 0" class="px-3 py-4 text-center text-sm text-muted">
                                <p>No categories yet?</p>
                                <button type="button" @click="createCategory()" class="text-velour-600 font-semibold mt-1">+ Create category</button>
                            </div>
                        </div>
                        @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                        <p x-show="categories.length > 0" class="text-[11px] text-muted mt-1">
                            <button type="button" @click="createCategory()" class="text-velour-600 hover:underline">+ Create category</button>
                        </p>
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <div class="amount-input-wrap">
                            <span class="currency-prefix">{{ $sym }}</span>
                            <input type="number" name="amount" x-model="amount" min="0.01" step="0.01" required data-field
                                   placeholder="0.00" class="form-input w-full @error('amount') form-input-error @enderror">
                        </div>
                        @error('amount')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Date quick picks --}}
                    <div>
                        <label class="form-label">Expense date <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-1.5 mb-2">
                            <button type="button" @click="setDate('today')"
                                    :class="dateMode === 'today' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Today</button>
                            <button type="button" @click="setDate('yesterday')"
                                    :class="dateMode === 'yesterday' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Yesterday</button>
                            <button type="button" @click="dateMode = 'custom'"
                                    :class="dateMode === 'custom' ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                                    class="px-3 py-1 rounded-full text-xs font-semibold border transition-colors">Custom</button>
                        </div>
                        <input x-show="dateMode === 'custom'" type="date" x-model="expenseDate" data-field
                               class="form-input w-full @error('expense_date') form-input-error @enderror">
                        @error('expense_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Payment method --}}
                    <div class="relative">
                        <label class="form-label">Payment method <span class="text-red-500">*</span></label>
                        <button type="button" @click="paymentOpen = !paymentOpen"
                                class="form-select w-full text-left flex items-center justify-between gap-2">
                            <span class="flex items-center gap-2">
                                <span x-text="payments[paymentMethod]?.icon"></span>
                                <span x-text="paymentLabel"></span>
                            </span>
                            <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="paymentOpen" x-cloak @click.outside="paymentOpen = false"
                             class="absolute z-40 mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl py-1">
                            <template x-for="(pm, key) in payments" :key="key">
                                <button type="button" @click="paymentMethod = key; paymentOpen = false"
                                        class="w-full px-3 py-2.5 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center gap-2">
                                    <span x-text="pm.icon"></span>
                                    <span x-text="pm.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">
                            Staff
                            <span class="text-xs font-normal" :class="staffRequired ? 'text-rose-500' : 'text-muted'"
                                  x-text="staffRequired ? '(required)' : '(optional)'"></span>
                        </label>
                        <select name="staff_id" x-model="staffId" data-field class="form-select @error('staff_id') form-input-error @enderror">
                            <option value="">No staff linked</option>
                            @foreach($staffList as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="relative">
                        <label class="form-label">Vendor / paid to</label>
                        <input type="text" name="vendor" x-model="vendor" @focus="vendorOpen = true" @input="vendorOpen = true" data-field
                               placeholder="Supplier, landlord, etc."
                               :class="highlightVendor ? 'ring-2 ring-velour-500 border-velour-500' : ''"
                               class="form-input @error('vendor') form-input-error @enderror" autocomplete="off">
                        <div x-show="vendorOpen && filteredVendors.length" x-cloak @click.outside="vendorOpen = false"
                             class="absolute z-40 mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl py-1">
                            <template x-for="v in filteredVendors" :key="v">
                                <button type="button" @click="vendor = v; vendorOpen = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-800" x-text="v"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">Invoice / reference</label>
                    <input type="text" name="reference" x-model="reference" data-field class="form-input">
                </div>

                <div>
                    <label class="form-label">Additional details</label>
                    <textarea name="notes" x-model="notes" rows="3" data-field
                              placeholder="Purchased shampoo stock for June."
                              class="form-input resize-y min-h-[5rem] @error('notes') form-input-error @enderror"></textarea>
                </div>

                {{-- Recurring --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="recurring" class="rounded border-gray-300 text-velour-600">
                        <span class="text-sm font-medium text-heading">Repeat expense</span>
                    </label>
                    <div x-show="recurring" x-cloak class="flex flex-wrap gap-2">
                        <template x-for="(label, key) in recurringOptions" :key="key">
                            <button type="button" @click="recurringInterval = key"
                                    :class="recurringInterval === key ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-200 dark:border-gray-700 text-muted'"
                                    class="px-3 py-1.5 rounded-full text-xs font-semibold border" x-text="label"></button>
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" @click="submitForm('draft')" :disabled="submitting"
                            class="btn-outline inline-flex items-center gap-2">
                        <span x-show="!submitting || submitAction !== 'draft'">Save draft</span>
                        <span x-show="submitting && submitAction === 'draft'">Saving…</span>
                    </button>
                    <button type="button" @click="submitForm('recorded')" :disabled="submitting"
                            class="btn-primary inline-flex items-center gap-2 min-w-[9rem] justify-center">
                        <span x-show="!submitting || submitAction !== 'recorded'">Record expense</span>
                        <span x-show="submitting && submitAction === 'recorded'">Recording…</span>
                    </button>
                    <a href="{{ route('expenses.index') }}" class="btn-outline">Cancel</a>
                    <span class="text-[10px] text-muted ml-auto hidden sm:inline">Ctrl+S to save · Enter for next field</span>
                </div>
            </form>
        </div>

        {{-- RIGHT: Sidebar --}}
        <div class="space-y-4">
            {{-- Live preview --}}
            <div class="card p-5 sticky top-20">
                <h3 class="text-sm font-semibold text-heading mb-4">Expense summary</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Title</dt>
                        <dd class="font-medium text-heading text-right" x-text="title || '—'"></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Category</dt>
                        <dd class="font-medium text-right flex items-center gap-1 justify-end">
                            <span x-text="selectedCategory?.meta?.icon"></span>
                            <span x-text="selectedCategory?.name || '—'"></span>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-3 items-center">
                        <dt class="text-muted">Amount</dt>
                        <dd class="font-bold text-velour-600 dark:text-velour-400 text-lg" x-text="formatMoney(amount) || '—'"></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Payment</dt>
                        <dd class="font-medium text-heading" x-text="paymentLabel"></dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Vendor</dt>
                        <dd class="font-medium text-heading text-right" x-text="vendor || '—'"></dd>
                    </div>
                    <div x-show="recurring" class="flex justify-between gap-3">
                        <dt class="text-muted">Repeats</dt>
                        <dd class="font-medium text-heading" x-text="recurringOptions[recurringInterval]"></dd>
                    </div>
                </dl>
            </div>

            {{-- Receipt upload --}}
            <div class="card p-5">
                @include('expenses.partials.receipt-upload')
            </div>

            {{-- Recent expenses --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-heading mb-3">Recent expenses</h3>
                @if($recentExpenses->isEmpty())
                    <p class="text-sm text-muted">No expenses yet. Your recent entries will appear here.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($recentExpenses as $recent)
                            @php $rm = \App\Support\ExpenseCategoryUi::meta($recent->category?->slug); @endphp
                            <li>
                                <button type="button"
                                        @click="prefillFromRecent(@js([
                                            'title' => $recent->title,
                                            'category_id' => $recent->category_id,
                                            'amount' => (float) $recent->amount,
                                            'payment_method' => $recent->payment_method,
                                            'vendor' => $recent->vendor,
                                        ]))"
                                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/80 flex items-center justify-between gap-2 group">
                                    <span class="flex items-center gap-2 min-w-0">
                                        <span>{{ $rm['icon'] }}</span>
                                        <span class="text-sm text-body truncate group-hover:text-heading">{{ $recent->title }}</span>
                                    </span>
                                    <span class="text-sm font-bold text-heading shrink-0">@money($recent->amount)</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Tips --}}
            <div class="card p-4 bg-velour-50/50 dark:bg-velour-950/20 border-velour-200/40 dark:border-velour-800/30">
                <p class="text-xs font-semibold text-velour-700 dark:text-velour-300 mb-1">Tips</p>
                <ul class="text-xs text-muted space-y-1 list-disc list-inside">
                    <li>Link staff for salary entries</li>
                    <li>Upload receipts for tax records</li>
                    <li>Use recurring for rent &amp; bills</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function expenseForm() {
    return {
        title: @json(old('title', '')),
        categoryId: @json(old('category_id', $prefill['category_id'] ?? '')),
        amount: @json(old('amount', '')),
        expenseDate: @json(old('expense_date', $prefill['expense_date'] ?? $today)),
        dateMode: 'today',
        paymentMethod: @json(old('payment_method', 'cash')),
        staffId: @json(old('staff_id', $prefill['staff_id'] ?? '')),
        vendor: @json(old('vendor', '')),
        reference: @json(old('reference', '')),
        notes: @json(old('notes', '')),
        recurring: false,
        recurringInterval: 'monthly',
        recurringOptions: @json(\App\Models\Expense::RECURRING_INTERVALS),
        categories: @json($categoriesJson),
        payments: @json($payments),
        vendors: @json($vendorSuggestions),
        sym: @json($sym),
        categoryOpen: false,
        paymentOpen: false,
        vendorOpen: false,
        highlightVendor: false,
        staffRequired: false,
        submitting: false,
        submitAction: 'recorded',
        dragOver: false,
        receiptName: '',

        init() {
            this.syncCategory();
            if (this.expenseDate === @json($today)) this.dateMode = 'today';
            else if (this.expenseDate === @json($yesterday)) this.dateMode = 'yesterday';
            else this.dateMode = 'custom';
        },

        get selectedCategory() {
            return this.categories.find(c => String(c.id) === String(this.categoryId)) || null;
        },

        get paymentLabel() {
            return this.payments[this.paymentMethod]?.label || 'Cash';
        },

        get filteredVendors() {
            const q = (this.vendor || '').toLowerCase();
            return this.vendors.filter(v => !q || v.toLowerCase().includes(q)).slice(0, 8);
        },

        selectCategory(cat) {
            this.categoryId = cat.id;
            this.categoryOpen = false;
            this.applySmartDefaults(cat.slug);
        },

        applySmartDefaults(slug) {
            const map = {
                salary: { payment: 'bank_transfer', staff: true, vendor: false },
                inventory: { payment: null, staff: false, vendor: true },
                rent: { payment: 'bank_transfer', staff: false, vendor: true },
                utilities: { payment: 'upi', staff: false, vendor: true },
            };
            const d = map[slug] || { payment: null, staff: false, vendor: false };
            if (d.payment) this.paymentMethod = d.payment;
            this.staffRequired = d.staff;
            this.highlightVendor = d.vendor;
        },

        syncCategory() {
            if (this.selectedCategory) this.applySmartDefaults(this.selectedCategory.slug);
        },

        setDate(mode) {
            this.dateMode = mode;
            if (mode === 'today') this.expenseDate = @json($today);
            if (mode === 'yesterday') this.expenseDate = @json($yesterday);
        },

        formatMoney(val) {
            const n = parseFloat(val);
            if (!n || isNaN(n)) return '';
            return this.sym + ' ' + n.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        formatDisplayDate(d) {
            if (!d) return '—';
            const dt = new Date(d + 'T12:00:00');
            return dt.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        prefillFromRecent(row) {
            this.title = row.title;
            this.categoryId = row.category_id;
            this.amount = row.amount;
            this.paymentMethod = row.payment_method;
            this.vendor = row.vendor || '';
            this.syncCategory();
        },

        async createCategory() {
            const name = prompt('Category name');
            if (!name?.trim()) return;
            const res = await fetch(@json(route('quick-create.expense-category')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                        || document.querySelector('input[name="_token"]')?.value,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: name.trim() }),
            });
            if (!res.ok) { alert('Could not create category'); return; }
            const cat = await res.json();
            this.categories.push(cat);
            this.selectCategory(cat);
        },

        handleEnter(e) {
            if (e.target.tagName === 'TEXTAREA' || e.target.type === 'submit') return;
            const form = this.$refs.expenseFormEl;
            if (!form) return;
            const fields = [...form.querySelectorAll('[data-field]')];
            const i = fields.indexOf(e.target);
            if (i >= 0 && i < fields.length - 1) {
                e.preventDefault();
                fields[i + 1].focus();
            }
        },

        onReceipt(e) {
            const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
            if (file) this.receiptName = file.name;
        },

        submitForm(action) {
            this.submitAction = action;
            const form = this.$refs.expenseFormEl || document.getElementById('expense-create-form');
            if (!form) return;
            if (!this.categoryId) {
                alert('Please select a category.');
                return;
            }
            this.submitting = true;
            form.submit();
        },
    };
}
</script>
@endpush
@endsection
