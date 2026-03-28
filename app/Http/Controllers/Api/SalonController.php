<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Review;
use App\Models\Staff;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SalonController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $salon = \App\Models\Salon::with('owner')->findOrFail($request->attributes->get('salon_id'));
        return response()->json($salon);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email',
            'website'      => 'nullable|url',
            'address_line1'=> 'nullable|string|max:255',
            'address_line2'=> 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'postcode'     => 'nullable|string|max:20',
            'country'      => 'nullable|string|size:2',
            'timezone'     => 'nullable|string|max:50',
            'currency'     => 'nullable|string|size:3',
            'social_links' => 'nullable|array',
        ]);

        $salon = \App\Models\Salon::findOrFail($request->attributes->get('salon_id'));
        $salon->update($data);
        return response()->json(['message' => 'Salon updated.', 'salon' => $salon]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate(['logo' => 'required|mimes:jpg,jpeg,png,webp,svg|max:3072']);
        $path = $request->file('logo')->store('salons/logos', 'public');
        \App\Models\Salon::findOrFail($request->attributes->get('salon_id'))->update(['logo' => $path]);
        return response()->json(['logo' => $path]);
    }

    public function uploadCover(Request $request): JsonResponse
    {
        $request->validate(['cover' => 'required|mimes:jpg,jpeg,png,webp|max:5120|dimensions:min_width=400']);
        $path = $request->file('cover')->store('salons/covers', 'public');
        \App\Models\Salon::findOrFail($request->attributes->get('salon_id'))->update(['cover_image' => $path]);
        return response()->json(['cover_image' => $path]);
    }

    public function settings(Request $request): JsonResponse
    {
        $settings = \App\Models\SalonSetting::where('salon_id', $request->attributes->get('salon_id'))->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->casted_value]);
        return response()->json($settings);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        foreach ($request->all() as $key => $value) {
            \App\Models\SalonSetting::updateOrCreate(
                ['salon_id' => $salonId, 'key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value,
                 'type'  => is_bool($value) ? 'boolean' : (is_array($value) ? 'json' : 'string')]
            );
        }
        return response()->json(['message' => 'Settings saved.']);
    }

    public function openingHours(Request $request): JsonResponse
    {
        $salon = \App\Models\Salon::findOrFail($request->attributes->get('salon_id'));
        return response()->json($salon->opening_hours ?? $this->defaultHours());
    }

    public function updateHours(Request $request): JsonResponse
    {
        $data  = $request->validate(['hours' => 'required|array']);
        $salon = \App\Models\Salon::findOrFail($request->attributes->get('salon_id'));
        $salon->update(['opening_hours' => $data['hours']]);
        return response()->json(['message' => 'Opening hours updated.']);
    }

    private function defaultHours(): array
    {
        $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        return collect($days)->mapWithKeys(fn($d) => [$d => [
            'open'  => !in_array($d, ['Sunday']),
            'start' => '09:00',
            'end'   => $d === 'Saturday' ? '17:00' : '19:00',
        ]])->toArray();
    }
}
