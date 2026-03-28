<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    private function period(Request $request): array
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        return [
            'from' => $request->from ?? now()->startOfMonth()->toDateString(),
            'to'   => $request->to   ?? now()->endOfMonth()->toDateString(),
        ];
    }

    public function revenue(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->revenue($salonId, $from, $to));
    }

    public function appointments(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->appointments($salonId, $from, $to));
    }

    public function staff(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->staff($salonId, $from, $to));
    }

    public function clients(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->clients($salonId, $from, $to));
    }

    public function services(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->services($salonId, $from, $to));
    }

    public function inventory(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->inventory($salonId));
    }

    public function marketing(Request $request): JsonResponse
    {
        ['from' => $from, 'to' => $to] = $this->period($request);
        $salonId = $request->attributes->get('salon_id');
        return response()->json($this->reportService->marketing($salonId, $from, $to));
    }

    public function payroll(Request $request): JsonResponse
    {
        $request->validate(['month' => 'nullable|date_format:Y-m']);
        $salonId = $request->attributes->get('salon_id');
        $month   = $request->month ?? now()->format('Y-m');
        return response()->json($this->reportService->payroll($salonId, $month));
    }

    public function export(Request $request, string $type): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        ['from' => $from, 'to' => $to] = $this->period($request);

        $allowed = ['revenue','appointments','clients','staff','inventory','payroll'];
        if (! in_array($type, $allowed)) {
            return response()->json(['message' => 'Invalid report type.'], 422);
        }

        $data = $this->reportService->$type($salonId, $from, $to);
        return response()->json(['type' => $type, 'period' => compact('from','to'), 'data' => $data]);
    }
}
