<?php

namespace App\Services;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExpenseDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $salonId, Builder $filteredQuery, Carbon $from, Carbon $to): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $prevMonthStart = $now->copy()->subMonth()->startOfMonth();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $base = Expense::withoutGlobalScopes()->where('expenses.salon_id', $salonId)
            ->where('expenses.status', 'recorded');

        $monthTotal = (float) (clone $base)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthCount = (int) (clone $base)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->count();

        $prevMonthTotal = (float) (clone $base)
            ->whereBetween('expense_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        $trendPct = $prevMonthTotal > 0
            ? round((($monthTotal - $prevMonthTotal) / $prevMonthTotal) * 100, 1)
            : ($monthTotal > 0 ? 100.0 : 0.0);

        $daysInMonth = max(1, $now->day);
        $avgDaily = $monthTotal / $daysInMonth;

        $pendingTotal = (float) (clone $base)
            ->where('payment_method', 'cheque')
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $categoryBreakdown = $this->categoryBreakdown($salonId, $from, $to, $filteredQuery);

        $topRow = $categoryBreakdown->first();
        $topCategoryName = $topRow['name'] ?? '—';
        $topCategoryTotal = (float) ($topRow['total'] ?? 0);

        $filteredTotal = (float) (clone $filteredQuery)->sum('expenses.amount');

        $trendPoints = $this->trendPoints($salonId, $from, $to);

        $insights = $this->insights($salonId, $from, $to, $filteredQuery);

        return [
            'month_total' => $monthTotal,
            'month_count' => $monthCount,
            'month_label' => $now->format('F Y'),
            'prev_month_label' => $prevMonthStart->format('F Y'),
            'prev_month_total' => $prevMonthTotal,
            'trend_pct' => $trendPct,
            'trend_up' => $monthTotal >= $prevMonthTotal,
            'avg_daily' => $avgDaily,
            'pending_total' => $pendingTotal,
            'top_category_name' => $topCategoryName,
            'top_category_total' => $topCategoryTotal,
            'category_breakdown' => $categoryBreakdown,
            'trend_points' => $trendPoints,
            'filtered_total' => $filteredTotal,
            'insights' => $insights,
        ];
    }

    private function categoryBreakdown(int $salonId, Carbon $from, Carbon $to, Builder $filteredQuery): Collection
    {
        $sub = (clone $filteredQuery)
            ->select('expenses.category_id')
            ->selectRaw('SUM(expenses.amount) as total')
            ->groupBy('expenses.category_id');

        $rows = Expense::withoutGlobalScopes()
            ->fromSub($sub, 'agg')
            ->join('expense_categories', 'expense_categories.id', '=', 'agg.category_id')
            ->where('expense_categories.salon_id', $salonId)
            ->select([
                'expense_categories.name',
                'expense_categories.slug',
                'agg.total',
            ])
            ->orderByDesc('agg.total')
            ->get();

        $grand = max(0.01, (float) $rows->sum('total'));

        return $rows->map(fn ($r) => [
            'name' => $r->name,
            'slug' => $r->slug,
            'total' => (float) $r->total,
            'pct' => (int) round(((float) $r->total / $grand) * 100),
        ]);
    }

    /**
     * @return list<array{label: string, x: float, y: float}>
     */
    private function trendPoints(int $salonId, Carbon $from, Carbon $to): array
    {
        $days = max(1, $from->diffInDays($to) + 1);
        $buckets = min($days, 30);
        $step = max(1, (int) ceil($days / $buckets));

        $points = [];
        $max = 0.01;
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $end = $cursor->copy()->addDays($step - 1)->min($to);
            $sum = (float) Expense::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->where('status', 'recorded')
                ->whereBetween('expense_date', [$cursor, $end])
                ->sum('amount');
            $max = max($max, $sum);
            $points[] = [
                'label' => $cursor->format('j M'),
                'sum' => $sum,
            ];
            $cursor->addDays($step);
        }

        $count = count($points);
        if ($count === 0) {
            return [];
        }

        return collect($points)->values()->map(function ($p, $i) use ($count, $max) {
            $x = $count === 1 ? 50 : ($i / ($count - 1)) * 100;
            $y = 100 - (($p['sum'] / $max) * 90);

            return [
                'label' => $p['label'],
                'x' => round($x, 2),
                'y' => round($y, 2),
            ];
        })->all();
    }

    /**
     * @return array{highest_category: string, highest_category_amount: float, most_active_staff: string, average_expense: float, last_expense_label: string}
     */
    private function insights(int $salonId, Carbon $from, Carbon $to, Builder $filteredQuery): array
    {
        $q = (clone $filteredQuery);

        $highest = (clone $q)
            ->join('expense_categories', 'expense_categories.id', '=', 'expenses.category_id')
            ->selectRaw('expense_categories.name, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.name')
            ->orderByDesc('total')
            ->first();

        $staffRow = (clone $q)
            ->whereNotNull('expenses.staff_id')
            ->join('staff', 'staff.id', '=', 'expenses.staff_id')
            ->selectRaw("CONCAT(staff.first_name, ' ', staff.last_name) as name, COUNT(*) as cnt")
            ->groupBy('staff.id', 'staff.first_name', 'staff.last_name')
            ->orderByDesc('cnt')
            ->first();

        $avg = (float) (clone $q)->avg('expenses.amount');

        $last = (clone $q)
            ->orderByDesc('expenses.expense_date')
            ->orderByDesc('expenses.id')
            ->first();

        $lastLabel = '—';
        if ($last) {
            $lastLabel = $last->expense_date->format('j M Y').' · '.$last->title;
        }

        return [
            'highest_category' => $highest->name ?? '—',
            'highest_category_amount' => (float) ($highest->total ?? 0),
            'most_active_staff' => $staffRow->name ?? '—',
            'average_expense' => $avg,
            'last_expense_label' => $lastLabel,
        ];
    }
}
