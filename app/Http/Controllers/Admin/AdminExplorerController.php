<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminExplorerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $salonId = $request->filled('salon_id') ? (int) $request->salon_id : null;
        $type = $request->get('type', 'clients');

        $salons = Salon::withoutGlobalScopes()->orderBy('name')->get(['id', 'name', 'slug']);

        $results = collect();
        if ($search !== '') {
            $results = match ($type) {
                'appointments' => $this->searchAppointments($search, $salonId),
                default => $this->searchClients($search, $salonId),
            };
        }

        return view('admin.explorer.index', compact('search', 'salonId', 'type', 'salons', 'results'));
    }

    private function searchClients(string $search, ?int $salonId)
    {
        return Client::withoutGlobalScopes()
            ->with('salon:id,name')
            ->when($salonId, fn ($q) => $q->where('salon_id', $salonId))
            ->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(50)
            ->get();
    }

    private function searchAppointments(string $search, ?int $salonId)
    {
        return Appointment::withoutGlobalScopes()
            ->with(['salon:id,name', 'client:id,first_name,last_name'])
            ->when($salonId, fn ($q) => $q->where('salon_id', $salonId))
            ->where('reference', 'like', "%{$search}%")
            ->limit(50)
            ->get();
    }
}
