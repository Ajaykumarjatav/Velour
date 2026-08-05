@extends('layouts.app')
@section('title', 'Add Expense')
@section('page-title', 'Add Expense')

@php
    use App\Support\ExpensePaymentUi;
    $sym = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'INR');
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();
    $salaryCatId = (string) ($salaryCategoryId ?? '');
@endphp

@section('content')
<div class="max-w-2xl mx-auto pb-10"
     x-data="expenseCreateForm(@js([
        'expenseDate' => old('expense_date', $prefill['expense_date'] ?? $today),
        'today' => $today,
        'yesterday' => $yesterday,
        'categoryId' => (string) old('category_id', $prefill['category_id'] ?? ''),
        'staffId' => (string) old('staff_id', $prefill['staff_id'] ?? ''),
        'title' => old('title', $prefill['title'] ?? request('title', '')),
        'amount' => old('amount', $prefill['amount'] ?? request('amount', '')),
        'paymentMethod' => old('payment_method', request('payment_method', 'cash')),
        'categoryMeta' => $categoryMeta,
        'payrollPreview' => $payrollPreview,
        'salaryCategoryId' => $salaryCatId,
        'showMore' => (bool) old('vendor') || (bool) old('notes') || (bool) old('reference') || (bool) old('recurring_interval'),
     ]))"
     x-init="init()">

    <p class="text-sm text-muted mb-5">Choose a category first — the form adapts (e.g. staff salary shows who you’re paying and a calculated amount).</p>

    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf
        <input type="hidden" name="status" value="recorded">

        <div>
            <label class="form-label">Category <span class="text-red-500">*</span></label>
            <select name="category_id" x-model="categoryId" @change="onCategoryChange()" required
                    class="form-select @error('category_id') form-input-error @enderror">
                <option value="">Select category</option>
                @foreach($categories as $cat)
                    @php $m = \App\Support\ExpenseCategoryUi::meta($cat->slug, $cat->name); @endphp
                    <option value="{{ $cat->id }}">{{ $m['icon'] }} {{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div x-show="categoryId" x-cloak class="space-y-4">
            <div x-show="isSalary" x-cloak class="space-y-4 rounded-xl border border-amber-200/80 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-950/20 p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <label class="form-label">Staff member <span class="text-red-500">*</span></label>
                        <p class="text-xs text-muted -mt-1 mb-2">Linked to Staff &amp; HR payroll (attendance + commission).</p>
                    </div>
                    <a href="{{ route('staff.index') }}" class="text-xs font-semibold text-velour-600 dark:text-velour-400 hover:underline shrink-0">
                        Open staff payroll →
                    </a>
                </div>
                <select x-model="staffId" @change="onStaffChange()"
                        :required="isSalary"
                        class="form-select @error('staff_id') form-input-error @enderror">
                    <option value="">Select staff</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>
                @error('staff_id')<p class="form-error">{{ $message }}</p>@enderror

                <template x-if="activePayroll">
                    <div class="rounded-lg bg-white dark:bg-gray-900/60 border border-amber-200/60 dark:border-amber-800/40 p-3 text-xs space-y-1.5">
                        <p class="font-semibold text-heading text-sm" x-text="activePayroll.suggested_title"></p>
                        <div class="grid grid-cols-2 gap-2 text-muted">
                            <span>Appointments: <strong class="text-body" x-text="activePayroll.appointments"></strong></span>
                            <span>Revenue: <strong class="text-body" x-text="fmtMoney(activePayroll.revenue)"></strong></span>
                            <span>Worked days: <strong class="text-body" x-text="activePayroll.worked_days + ' / ' + activePayroll.scheduled_days"></strong></span>
                            <span>Commission: <strong class="text-emerald-600 dark:text-emerald-400" x-text="'+' + fmtMoney(activePayroll.commission)"></strong></span>
                            <span>Base payable: <strong class="text-body" x-text="fmtMoney(activePayroll.base_payable)"></strong></span>
                            <span>Net pay: <strong class="text-heading" x-text="fmtMoney(activePayroll.net)"></strong></span>
                        </div>
                        <button type="button" @click="applyPayrollAmount()"
                                class="mt-2 text-xs font-semibold text-velour-600 dark:text-velour-400 hover:underline">
                            Use calculated net as amount
                        </button>
                    </div>
                </template>
            </div>

            <div>
                <label class="form-label">What was this expense for? <span class="text-red-500">*</span></label>
                <input type="text" name="title" x-model="title" required
                       :placeholder="isSalary ? 'e.g. Salary — Rajesh — March 2026' : 'e.g. March rent, shampoo stock'"
                       class="form-input @error('title') form-input-error @enderror">
                @error('title')<p class="form-error">{{ $message }}</p>@enderror
                <p x-show="highlightVendor" x-cloak class="text-xs text-muted mt-1">Tip: add the vendor / paid-to name under extra fields.</p>
            </div>
        </div>

        {{-- Single staff_id for submit --}}
        <input type="hidden" name="staff_id" :value="staffId || ''">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="categoryId" x-cloak>
            <div>
                <label class="form-label">Amount ({{ $sym }}) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" min="0.01" step="0.01" required x-model="amount"
                       placeholder="0.00"
                       class="form-input @error('amount') form-input-error @enderror">
                @error('amount')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Paid via <span class="text-red-500">*</span></label>
                <select name="payment_method" x-model="paymentMethod" required class="form-select @error('payment_method') form-input-error @enderror">
                    @foreach(\App\Models\Expense::PAYMENT_METHODS as $key => $label)
                        @php $pm = ExpensePaymentUi::meta($key); @endphp
                        <option value="{{ $key }}">{{ $pm['icon'] }} {{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_method')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </div>

        <div x-show="categoryId" x-cloak>
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

        <div x-show="categoryId" x-cloak>
            <button type="button" @click="showMore = !showMore"
                    class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline flex items-center gap-1">
                <span x-text="showMore ? 'Hide extra fields' : 'Add vendor, receipt…'"></span>
                <svg class="w-4 h-4 transition-transform" :class="showMore && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div x-show="categoryId && showMore" x-cloak class="space-y-4 pt-1 border-t border-gray-100 dark:border-gray-800">
            <div x-show="!isSalary">
                <label class="form-label">Staff <span class="text-xs font-normal text-muted">(optional)</span></label>
                <select x-model="staffId" class="form-select">
                    <option value="">None</option>
                    @foreach($staffList as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
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

        <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-gray-100 dark:border-gray-800" x-show="categoryId" x-cloak>
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
                    'staff_id' => $recent->staff_id,
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

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('expenseCreateForm', (cfg) => ({
    ...cfg,
    dateMode: 'today',
    currencySymbol: @json($sym),
    init() {
      if (this.expenseDate === this.today) this.dateMode = 'today';
      else if (this.expenseDate === this.yesterday) this.dateMode = 'yesterday';
      else this.dateMode = 'custom';
      this.onCategoryChange(false);
      if (this.staffId) this.onStaffChange(false);
    },
    get meta() {
      return this.categoryMeta[this.categoryId] || null;
    },
    get isSalary() {
      return this.meta?.slug === 'salary' || String(this.categoryId) === String(this.salaryCategoryId);
    },
    get highlightVendor() {
      return !!this.meta?.highlight_vendor;
    },
    get activePayroll() {
      if (!this.isSalary || !this.staffId) return null;
      return this.payrollPreview[this.staffId] || this.payrollPreview[String(this.staffId)] || null;
    },
    setDate(mode) {
      this.dateMode = mode;
      if (mode === 'today') this.expenseDate = this.today;
      if (mode === 'yesterday') this.expenseDate = this.yesterday;
    },
    onCategoryChange(applyDefaults = true) {
      if (!applyDefaults || !this.meta) return;
      if (this.meta.payment_method) this.paymentMethod = this.meta.payment_method;
      if (this.isSalary) {
        this.showMore = false;
        this.onStaffChange(true);
      }
    },
    onStaffChange(fill = true) {
      if (!this.isSalary || !this.activePayroll) return;
      if (fill || !this.title) this.title = this.activePayroll.suggested_title;
      if (fill && (!this.amount || Number(this.amount) === 0)) {
        this.amount = this.activePayroll.suggested_amount || this.activePayroll.net || '';
      }
    },
    applyPayrollAmount() {
      if (!this.activePayroll) return;
      this.amount = this.activePayroll.suggested_amount || this.activePayroll.net;
      this.title = this.activePayroll.suggested_title;
    },
    fmtMoney(n) {
      const v = Number(n || 0);
      return this.currencySymbol + v.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    },
  }));
});
</script>
@endpush
