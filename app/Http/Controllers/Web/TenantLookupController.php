<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Client;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantLookupController extends Controller
{
    use ResolvesActiveSalon;

    public function clients(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $salon = $this->activeSalon();
        $term = trim((string) ($data['q'] ?? ''));

        $query = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->select(['id', 'first_name', 'last_name', 'phone']);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $results = $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(30)
            ->get()
            ->map(fn (Client $client) => [
                'id' => (int) $client->id,
                'label' => trim($client->first_name . ' ' . $client->last_name)
                    . ($client->phone ? ' — ' . $client->phone : ''),
            ])
            ->values();

        return response()->json(['results' => $results]);
    }

    public function staff(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $salon = $this->activeSalon();
        $term = trim((string) ($data['q'] ?? ''));

        $query = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->select(['id', 'first_name', 'last_name', 'email', 'phone']);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $results = $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(30)
            ->get()
            ->map(fn (Staff $staff) => [
                'id' => (int) $staff->id,
                'label' => trim($staff->first_name . ' ' . $staff->last_name)
                    . ($staff->phone ? ' — ' . $staff->phone : ($staff->email ? ' — ' . $staff->email : '')),
            ])
            ->values();

        return response()->json(['results' => $results]);
    }
}
