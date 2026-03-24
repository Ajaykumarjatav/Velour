<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $salon = Auth::user()->salons()->firstOrFail();

        $view     = $request->get('view', 'week');  // day | week | month
        $dateStr  = $request->get('date', now()->toDateString());
        $date     = \Carbon\Carbon::parse($dateStr);

        // Date range based on view
        match ($view) {
            'day'   => [$start, $end] = [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'month' => [$start, $end] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$start, $end] = [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
        };

        $appointments = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$start, $end])
            ->with(['client', 'staff', 'services'])
            ->get()
            ->map(fn($a) => [
                'id'         => $a->id,
                'title'      => $a->client?->first_name . ' ' . $a->client?->last_name,
                'start'      => $a->starts_at->toIso8601String(),
                'end'        => $a->ends_at->toIso8601String(),
                'status'     => $a->status,
                'staff'      => $a->staff?->name,
                'staff_id'   => $a->staff_id,
                'reference'  => $a->reference,
                'url'        => route('appointments.show', $a->id),
                'color'      => $this->statusColor($a->status),
            ]);

        $staff = Staff::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->withName()
            ->get();

        $calendarData = json_encode($appointments);
        $staffData    = json_encode($staff);

        return view('calendar.index', compact(
            'salon', 'view', 'date', 'calendarData', 'staffData',
            'appointments', 'start', 'end'
        ));
    }

    private function statusColor(string $status): string
    {
        return match($status) {
            'confirmed'  => '#7C3AED',
            'completed'  => '#059669',
            'cancelled'  => '#DC2626',
            'no_show'    => '#D97706',
            default      => '#6B7280',
        };
    }
}
