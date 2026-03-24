<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\PosTransaction;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon = $this->salon();

        return view('reports.index', compact('salon'));
    }

    public function show(Request $request, string $type)
    {
        $salon = $this->salon();
        $from  = $request->get('from', now()->startOfMonth()->toDateString());
        $to    = $request->get('to',   now()->toDateString());

        $data = match($type) {
            'revenue'      => $this->revenueReport($salon, $from, $to),
            'appointments' => $this->appointmentsReport($salon, $from, $to),
            'staff'        => $this->staffReport($salon, $from, $to),
            'clients'      => $this->clientsReport($salon, $from, $to),
            'services'     => $this->servicesReport($salon, $from, $to),
            default        => abort(404),
        };

        return view("reports.$type", array_merge($data, compact('salon', 'from', 'to', 'type')));
    }

    private function revenueReport($salon, $from, $to): array
    {
        $daily = PosTransaction::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as transactions'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $byMethod = PosTransaction::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $totalRevenue  = $daily->sum('revenue');
        $totalTransactions = $daily->sum('transactions');

        return compact('daily', 'byMethod', 'totalRevenue', 'totalTransactions');
    }

    private function appointmentsReport($salon, $from, $to): array
    {
        $byStatus = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $daily = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->select(DB::raw('DATE(starts_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $total = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->count();

        return compact('byStatus', 'daily', 'total');
    }

    private function staffReport($salon, $from, $to): array
    {
        $staff = Staff::where('salon_id', $salon->id)
            ->withCount(['appointments as appointment_count' => fn($q) =>
                $q->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ])
            ->withSum(['appointments as total_revenue' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ], 'total_price')
            ->get();

        return compact('staff');
    }

    private function clientsReport($salon, $from, $to): array
    {
        $newClients = Client::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->count();

        $returningClients = Client::where('salon_id', $salon->id)
            ->whereHas('appointments', fn($q) =>
                $q->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
            )
            ->where('created_at', '<', $from)
            ->count();

        $topClients = Client::where('salon_id', $salon->id)
            ->withSum(['transactions as total_spent' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ], 'total')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return compact('newClients', 'returningClients', 'topClients');
    }

    private function servicesReport($salon, $from, $to): array
    {
        $services = Service::where('salon_id', $salon->id)
            ->withCount(['appointmentServices as booking_count' => fn($q) =>
                $q->whereHas('appointment', fn($aq) =>
                    $aq->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
                )
            ])
            ->withSum(['appointmentServices as total_revenue' => fn($q) =>
                $q->whereHas('appointment', fn($aq) =>
                    $aq->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
                )
            ], 'price')
            ->having('booking_count', '>', 0)
            ->orderByDesc('booking_count')
            ->get();

        return compact('services');
    }
}
