<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Salon;
use App\Models\Staff;
use App\Services\ExpenseCategoryDefaults;
use App\Services\ExpenseDashboardService;
use App\Support\ExpenseCategoryUi;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct(
        private readonly ExpenseDashboardService $dashboard,
    ) {}

    private function salon(): Salon
    {
        return $this->activeSalon();
    }

    private function ensureCategories(int $salonId)
    {
        ExpenseCategoryDefaults::ensureForSalon($salonId);

        return $this->salonScoped(ExpenseCategory::class)->orderBy('sort_order')->orderBy('name')->get();
    }

    public function index(Request $request): View
    {
        $salon = $this->salon();
        $categories = $this->ensureCategories($salon->id);
        [$from, $to, $activePeriod] = $this->resolveDateRange($request);

        $query = $this->filteredExpenseQuery($salon->id, $request, $from, $to);

        $expenses = (clone $query)
            ->with(['category', 'staff'])
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.id')
            ->paginate(25)
            ->withQueryString();

        $staffList = $this->staffList($salon->id);
        $dashboardData = $this->dashboard->build($salon->id, $query, $from, $to);

        return view('expenses.index', compact(
            'salon', 'expenses', 'categories', 'staffList',
            'from', 'to', 'activePeriod', 'dashboardData'
        ));
    }

    public function create(Request $request): View
    {
        $salon = $this->salon();
        $categories = $this->ensureCategories($salon->id);
        $staffList = $this->staffList($salon->id);
        $vendorSuggestions = $this->vendorSuggestions($salon->id);
        $recentExpenses = $this->recentExpenses($salon->id);

        $prefill = array_filter([
            'category_id' => $request->query('category_id'),
            'staff_id' => $request->query('staff_id'),
            'expense_date' => $request->query('expense_date'),
        ]);

        return view('expenses.create', compact(
            'salon', 'categories', 'staffList',
            'vendorSuggestions', 'recentExpenses', 'prefill'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $data = $this->validatedExpense($request, $salon->id);
        $data['salon_id'] = $salon->id;
        $data['created_by'] = $request->user()?->id;
        $data['receipt_path'] = $this->storeReceipt($request, $salon->id);

        Expense::create($data);

        $message = ($data['status'] ?? 'recorded') === 'draft'
            ? 'Expense saved as draft.'
            : 'Expense recorded successfully.';

        return redirect()
            ->route('expenses.index')
            ->with('success', $message)
            ->with('expense_saved', true);
    }

    public function edit(Expense $expense): View
    {
        $this->authorise($expense);
        $salon = $this->salon();
        $categories = $this->ensureCategories($salon->id);
        $staffList = $this->staffList($salon->id);

        return view('expenses.edit', compact('expense', 'categories', 'staffList'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorise($expense);
        $salon = $this->salon();
        $data = $this->validatedExpense($request, $salon->id, $expense);

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('local')->delete($expense->receipt_path);
            }
            $data['receipt_path'] = $this->storeReceipt($request, $salon->id);
        }

        $expense->update($data);

        return redirect()
            ->route('expenses.index')
            ->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorise($expense);
        if ($expense->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $salon = $this->salon();
        [$from, $to] = $this->resolveDateRange($request);
        $query = $this->filteredExpenseQuery($salon->id, $request, $from, $to);
        $rows = (clone $query)->with(['category', 'staff'])
            ->orderByDesc('expenses.expense_date')
            ->get();

        $format = $request->query('format', 'csv');
        $ext = $format === 'excel' ? 'xls' : 'csv';
        $filename = 'expenses-'.now()->format('Y-m-d').'.'.$ext;
        $delimiter = $format === 'excel' ? "\t" : ',';

        return response()->streamDownload(function () use ($rows, $salon, $delimiter) {
            $out = fopen('php://output', 'w');
            if ($delimiter === ',') {
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            }
            fputcsv($out, ['Date', 'Title', 'Category', 'Staff', 'Vendor', 'Amount', 'Payment', 'Reference', 'Notes', 'Status'], $delimiter);

            foreach ($rows as $e) {
                fputcsv($out, [
                    $e->expense_date->format('Y-m-d'),
                    $e->title,
                    $e->category?->name ?? '',
                    $e->staff?->name ?? '',
                    $e->vendor ?? '',
                    $e->amount,
                    Expense::PAYMENT_METHODS[$e->payment_method] ?? $e->payment_method,
                    $e->reference ?? '',
                    $e->notes ?? '',
                    $e->status,
                ], $delimiter);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => $format === 'excel'
                ? 'application/vnd.ms-excel'
                : 'text/csv; charset=UTF-8',
        ]);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $salon = $this->salon();
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $category = ExpenseCategoryDefaults::createCustom($salon->id, $data['name']);
        $meta = ExpenseCategoryUi::meta($category->slug, $category->name);

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'meta' => $meta,
        ]);
    }

    private function authorise(Expense $expense): void
    {
        abort_unless((int) $expense->salon_id === (int) $this->salon()->id, 403);
    }

    /** @return array{0: Carbon, 1: Carbon, 2: ?string} */
    private function resolveDateRange(Request $request): array
    {
        $period = $request->query('period');
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'today'],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'week'],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'month'],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                'last_month',
            ],
            '90d' => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay(), '90d'],
            default => [
                Carbon::parse($request->query('from', $now->copy()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->query('to', $now->copy()->endOfMonth()->toDateString()))->endOfDay(),
                null,
            ],
        };
    }

    private function filteredExpenseQuery(int $salonId, Request $request, Carbon $from, Carbon $to)
    {
        $query = Expense::withoutGlobalScopes()
            ->where('expenses.salon_id', $salonId)
            ->where('expenses.status', 'recorded')
            ->whereBetween('expenses.expense_date', [$from->toDateString(), $to->toDateString()]);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('expenses.title', 'like', "%{$search}%")
                    ->orWhere('expenses.vendor', 'like', "%{$search}%")
                    ->orWhere('expenses.reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('expenses.category_id', $request->category_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('expenses.staff_id', $request->staff_id);
        }

        return $query;
    }

    private function staffList(int $salonId)
    {
        return Staff::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);
    }

    private function vendorSuggestions(int $salonId)
    {
        return Expense::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->whereNotNull('vendor')
            ->where('vendor', '!=', '')
            ->select('vendor')
            ->distinct()
            ->orderBy('vendor')
            ->limit(20)
            ->pluck('vendor');
    }

    private function recentExpenses(int $salonId)
    {
        return Expense::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('status', 'recorded')
            ->with('category')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }

    /** @return array<string, mixed> */
    private function validatedExpense(Request $request, int $salonId, ?Expense $expense = null): array
    {
        $categoryIds = $this->salonScoped(ExpenseCategory::class)->pluck('id')->all();
        $staffIds = Staff::withoutGlobalScopes()->where('salon_id', $salonId)->pluck('id')->all();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'in:'.implode(',', $categoryIds)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:'.implode(',', array_keys(Expense::PAYMENT_METHODS))],
            'staff_id' => ['nullable', 'integer', 'in:'.implode(',', $staffIds)],
            'vendor' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'string', 'in:draft,recorded'],
            'recurring_interval' => ['nullable', 'string', 'in:'.implode(',', array_keys(Expense::RECURRING_INTERVALS))],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];

        $data = $request->validate($rules);
        $data['status'] = $data['status'] ?? 'recorded';
        $data['staff_id'] = $data['staff_id'] ?? null;
        $data['recurring_interval'] = ! empty($data['recurring_interval']) ? $data['recurring_interval'] : null;

        if ($data['status'] === 'recorded') {
            $category = ExpenseCategory::withoutGlobalScopes()->find($data['category_id']);
            $defaults = ExpenseCategoryUi::smartDefaults($category?->slug);
            if ($defaults['staff_required'] && empty($data['staff_id'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'staff_id' => 'Staff is required for salary expenses.',
                ]);
            }
        }

        return $data;
    }

    private function storeReceipt(Request $request, int $salonId): ?string
    {
        if (! $request->hasFile('receipt')) {
            return null;
        }

        return $request->file('receipt')->store("expenses/{$salonId}", 'local');
    }
}
