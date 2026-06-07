<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Services\SalonWebsitePayloadService;
use Illuminate\Http\JsonResponse;

class SalonWebsiteController extends Controller
{
    public function show(string $salonSlug, SalonWebsitePayloadService $payload): JsonResponse
    {
        $salon = Salon::query()
            ->where('slug', $salonSlug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($payload->build($salon));
    }
}
